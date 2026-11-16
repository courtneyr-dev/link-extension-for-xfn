// w8-n3-xfn-eligibility.php
global $wpdb;

$starting_user_id = get_current_user_id();
$write_attempts   = 0;
$write_guard      = static function ( $query ) use ( &$write_attempts ) {
	if ( ! preg_match( '/^(SELECT|SHOW)\b/i', ltrim( (string) $query ) ) ) {
		++$write_attempts;
		return '';
	}

	return $query;
};
add_filter( 'query', $write_guard );

$finish = static function ( ?string $failure = null ) use ( $starting_user_id, &$write_attempts, $write_guard ): void {
	remove_filter( 'query', $write_guard );
	wp_set_current_user( $starting_user_id );

	if ( 0 < $write_attempts ) {
		echo 'RESULT FAIL write_attempted:' . (int) $write_attempts . "\n";
		return;
	}

	if ( null !== $failure ) {
		echo 'RESULT FAIL ' . $failure . "\n";
		return;
	}

	echo "RESULT PASS\n";
};

$scanner_methods = [ 'is_eligible', 'filter_eligible', 'can_read_relationships', 'ability_can_read', 'invalidate_cache' ];
foreach ( $scanner_methods as $scanner_method ) {
	if ( ! method_exists( 'XFN_Content_Scanner', $scanner_method ) ) {
		$finish( 'seam_missing' );
		return;
	}
}

$meta_mirror_on = XFN_Feature_Flags::has_meta_mirror();
if ( $meta_mirror_on && ! method_exists( 'XFN_Meta_Mirror', 'redact_rest' ) ) {
	$finish( 'seam_missing' );
	return;
}
if ( ! $meta_mirror_on ) {
	echo "INFO meta_mirror_off\n";
}

$scan_method = new \ReflectionMethod( 'XFN_Content_Scanner', 'scan_all_posts_for_xfn' );
$scan_lines  = file( $scan_method->getFileName() );
$scan_source = implode(
	'',
	array_slice(
		$scan_lines,
		$scan_method->getStartLine() - 1,
		$scan_method->getEndLine() - $scan_method->getStartLine() + 1
	)
);
if ( false === strpos( $scan_source, 'filter_eligible(' ) || false === strpos( $scan_source, "post_password = ''" ) ) {
	$finish( 'scan_bypasses_seam' );
	return;
}

$protected_rel_posts = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$wpdb->posts}
	 WHERE post_status = 'publish'
	   AND post_type IN ('post', 'page')
	   AND post_password <> ''
	   AND post_content LIKE '%rel=%'"
);
echo 'INFO protected_rel_posts=' . $protected_rel_posts . "\n";

$case_definitions = [
	'publish'  => [ 'post_status' => 'publish', 'post_password' => '' ],
	'private'  => [ 'post_status' => 'private', 'post_password' => '' ],
	'draft'    => [ 'post_status' => 'draft', 'post_password' => '' ],
	'future'   => [ 'post_status' => 'future', 'post_password' => '' ],
	'pending'  => [ 'post_status' => 'pending', 'post_password' => '' ],
	'password' => [ 'post_status' => 'publish', 'post_password' => 'sentinel' ],
	'trash'    => [ 'post_status' => 'trash', 'post_password' => '' ],
];
$posts            = [];
$case_names       = array_keys( $case_definitions );
$case_index       = 0;
foreach ( $case_definitions as $case_name => $case_definition ) {
	$posts[] = new \WP_Post(
		(object) [
			'ID'            => PHP_INT_MAX - 301 - $case_index,
			'post_author'   => 0,
			'post_status'   => $case_definition['post_status'],
			'post_password' => $case_definition['post_password'],
			'post_type'     => 'post',
			'post_content'  => '<a href="https://n3-sentinel-' . $case_name . '.example/" rel="friend met">x</a>',
			'filter'        => 'raw',
		]
	);
	++$case_index;
}

$last_links = [];
$leaks      = static function ( callable $filter ) use ( $posts, $case_names, &$last_links ): array {
	$last_links = [];
	foreach ( $filter( $posts ) as $post ) {
		$last_links = array_merge( $last_links, XFN_Content_Scanner::extract_xfn_links_from_post( $post ) );
	}

	$encoded = wp_json_encode( $last_links );
	$found   = [];
	foreach ( array_slice( $case_names, 1 ) as $case_name ) {
		if ( false !== strpos( $encoded, 'n3-sentinel-' . $case_name . '.example' ) ) {
			$found[] = $case_name;
		}
	}

	return $found;
};

$reference_filter = /*N3:S4*/ static fn( array $p ): array => $p /*N3:/S4*/;
$reference_leaks  = $leaks( $reference_filter );
if ( array_slice( $case_names, 1 ) !== $reference_leaks ) {
	$finish( 'checker_blind' );
	return;
}

$real_leaks = $leaks( [ 'XFN_Content_Scanner', 'filter_eligible' ] );
if ( [] !== $real_leaks ) {
	$finish( 'leak:' . implode( ',', $real_leaks ) );
	return;
}
if ( false === strpos( wp_json_encode( $last_links ), 'n3-sentinel-publish.example' ) ) {
	$finish( 'positive_control_missing' );
	return;
}

foreach ( [ 'save_post', 'transition_post_status', 'trashed_post', 'untrashed_post', 'deleted_post' ] as $hook ) {
	if ( false === has_action( $hook, [ 'XFN_Content_Scanner', 'invalidate_cache' ] ) ) {
		$finish( 'hook_missing:' . $hook );
		return;
	}
}

if ( wp_using_ext_object_cache() ) {
	$cached_relationships = wp_cache_get( 'xfn_rels_all', 'transient' );
} else {
	$cached_relationships = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
			'_transient_xfn_rels_all'
		)
	);
	$cached_relationships = null === $cached_relationships ? false : maybe_unserialize( $cached_relationships );
}

if ( ! is_array( $cached_relationships ) ) {
	echo "INFO cache_empty\n";
} else {
	$cached_ineligible = [];
	foreach ( $cached_relationships as $cached_relationship ) {
		$post_id     = (int) ( $cached_relationship['post_id'] ?? 0 );
		$cached_post = get_post( $post_id );
		if ( ! $cached_post instanceof \WP_Post || ! XFN_Content_Scanner::is_eligible( $cached_post ) ) {
			$cached_ineligible[] = $post_id;
		}
	}
	if ( [] !== $cached_ineligible ) {
		$finish( 'cached_ineligible:' . implode( ',', array_unique( $cached_ineligible ) ) );
		return;
	}
}

wp_set_current_user( 0 );
$gate_leaks = [];
foreach ( $posts as $index => $post ) {
	$rest_gate    = XFN_Content_Scanner::can_read_relationships( $post );
	$ability_gate = XFN_Content_Scanner::ability_can_read( $post );
	if ( 0 === $index ) {
		if ( ! $rest_gate || ! $ability_gate ) {
			$finish( 'gate_positive_missing' );
			return;
		}
	} elseif ( $rest_gate || $ability_gate ) {
		$gate_leaks[] = $case_names[ $index ];
	}
}
if ( [] !== $gate_leaks ) {
	$finish( 'gate_leak:' . implode( ',', $gate_leaks ) );
	return;
}

$admin_ids = get_users(
	[
		'role'    => 'administrator',
		'number'  => 1,
		'orderby' => 'ID',
		'order'   => 'ASC',
		'fields'  => 'ID',
	]
);
if ( [] === $admin_ids ) {
	$finish( 'no_admin' );
	return;
}
wp_set_current_user( (int) $admin_ids[0] );
if ( ! current_user_can( 'manage_options' ) ) {
	$finish( 'no_admin' );
	return;
}

$ability_gate       = /*N3:S8*/ [ 'XFN_Content_Scanner', 'ability_can_read' ] /*N3:/S8*/;
$ability_gate_leaks = [];
foreach ( $posts as $index => $post ) {
	$can_read = (bool) $ability_gate( $post );
	if ( ( 0 === $index ) !== $can_read ) {
		$ability_gate_leaks[] = $case_names[ $index ];
	}
}
if ( [] !== $ability_gate_leaks ) {
	$finish( 'ability_gate_leak:' . implode( ',', $ability_gate_leaks ) );
	return;
}

$ineligible_post_id = (int) $wpdb->get_var(
	"SELECT ID FROM {$wpdb->posts}
	 WHERE post_type = 'post'
	   AND ( post_status IN ('private', 'draft', 'pending', 'future')
	      OR ( post_status = 'publish' AND post_password <> '' ) )
	 ORDER BY ID ASC
	 LIMIT 1"
);
if ( 0 === $ineligible_post_id ) {
	echo "INFO no_ineligible_row\n";
} else {
	$eligible_post_id = 0;
	$eligible_ids     = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_type = 'post'
		   AND post_status = 'publish'
		   AND post_password = ''
		 ORDER BY ID ASC"
	);
	foreach ( $eligible_ids as $eligible_id ) {
		$eligible_post = get_post( (int) $eligible_id );
		if ( $eligible_post instanceof \WP_Post && XFN_Content_Scanner::is_eligible( $eligible_post ) ) {
			$eligible_post_id = (int) $eligible_id;
			break;
		}
	}
	if ( 0 === $eligible_post_id ) {
		$finish( 'no_eligible_control' );
		return;
	}

	foreach ( [ 'xfn/get-meta-relationships', 'xfn/get-relationships' ] as $ability_name ) {
		if ( ! function_exists( 'wp_has_ability' ) || ! wp_has_ability( $ability_name ) ) {
			$finish( 'ability_missing' );
			return;
		}

		$ability = wp_get_ability( $ability_name );
		if ( null === $ability ) {
			$finish( 'ability_missing' );
			return;
		}

		$ineligible_result = $ability->execute( [ 'post_id' => $ineligible_post_id ] );
		if ( ! is_array( $ineligible_result )
			|| 'Insufficient permissions for this post.' !== ( $ineligible_result['error'] ?? null ) ) {
			$finish( 'ability_leak:' . $ability_name );
			return;
		}

		$eligible_result = $ability->execute( [ 'post_id' => $eligible_post_id ] );
		if ( is_wp_error( $eligible_result ) ) {
			$finish( 'ability_error' );
			return;
		}
		if ( ! is_array( $eligible_result ) || array_key_exists( 'error', $eligible_result ) ) {
			$finish( 'ability_positive_missing' );
			return;
		}
	}
}
wp_set_current_user( $starting_user_id );

if ( $meta_mirror_on ) {
	wp_set_current_user( 0 );
	$redactor   = /*N3:S9*/ [ 'XFN_Meta_Mirror', 'redact_rest' ] /*N3:/S9*/;
	$rest_leaks = [];
	foreach ( $posts as $index => $post ) {
		$response = new \WP_REST_Response(
			[
				'id'   => $post->ID,
				'meta' => [
					'_xfn_relationships' => [
						[
							'url'  => 'https://n3-meta-' . $case_names[ $index ] . '.example/',
							'rels' => [ 'friend' ],
						],
					],
				],
			]
		);
		$request  = new \WP_REST_Request( 'GET', '/wp/v2/posts/' . $post->ID );
		$response = $redactor( $response, $post, $request );
		$encoded  = wp_json_encode( $response->get_data() );
		if ( 0 === $index ) {
			if ( false === strpos( $encoded, 'n3-meta-publish.example' ) ) {
				$finish( 'rest_positive_missing' );
				return;
			}
		} elseif ( false !== strpos( $encoded, 'n3-meta-' . $case_names[ $index ] . '.example' ) ) {
			$rest_leaks[] = $case_names[ $index ];
		}
	}
	if ( [] !== $rest_leaks ) {
		$finish( 'rest_leak:' . implode( ',', $rest_leaks ) );
		return;
	}

	$type_response = new \WP_REST_Response(
		[
			'meta' => [
				'_xfn_relationships' => [ [ 'url' => 'https://n3-meta-type.example/', 'rels' => [ 'friend' ] ] ],
			],
		]
	);
	try {
		$type_result = $redactor( $type_response, new \stdClass(), new \WP_REST_Request( 'GET', '/wp/v2/posts/0' ) );
	} catch ( \Throwable $throwable ) {
		$finish( 'rest_type_unsafe' );
		return;
	}
	if ( $type_response !== $type_result || $type_response->get_data() !== $type_result->get_data() ) {
		$finish( 'rest_type_unsafe' );
		return;
	}

	rest_get_server();
	foreach ( [ 'post', 'page' ] as $rest_type ) {
		if ( false === has_filter( 'rest_prepare_' . $rest_type, [ 'XFN_Meta_Mirror', 'redact_rest' ] ) ) {
			$finish( 'rest_hook_missing:' . $rest_type );
			return;
		}
	}
}

$finish();
return;
