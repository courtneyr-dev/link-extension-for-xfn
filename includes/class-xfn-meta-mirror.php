<?php
/**
 * XFN Meta Mirror — stores XFN relationships in post meta and syncs to block HTML on save.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */
final class XFN_Meta_Mirror {

	public const META_KEY   = '_xfn_relationships';
	public const SOURCE_KEY = '_xfn_meta_source';

	/**
	 * Valid XFN 1.1 relationship values.
	 *
	 * @var string[]
	 */
	private static array $valid_xfn = [
		'contact',
		'acquaintance',
		'friend',
		'met',
		'co-worker',
		'colleague',
		'co-resident',
		'neighbor',
		'child',
		'parent',
		'sibling',
		'spouse',
		'kin',
		'muse',
		'crush',
		'date',
		'sweetheart',
		'me',
	];

	/**
	 * Hook meta registration and the save-time content sync.
	 */
	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_meta' ] );
		add_action( 'post_updated', [ __CLASS__, 'sync_content_to_meta' ], 10, 3 );
		add_action( 'save_post', [ __CLASS__, 'sync_meta_to_content' ], 20, 2 );
		add_action(
			'rest_api_init',
			function () {
				foreach ( get_post_types( [ 'show_in_rest' => true ] ) as $post_type ) {
					add_filter( 'rest_prepare_' . $post_type, [ __CLASS__, 'redact_rest' ], 20, 3 );
				}
			}
		);
	}

	/**
	 * Redact relationship meta when a REST caller cannot read the post.
	 *
	 * @since 1.1.3
	 *
	 * @param mixed $response REST response.
	 * @param mixed $post     Prepared object from the REST controller.
	 * @param mixed $request  REST request.
	 * @return mixed REST response, with relationship meta redacted when needed.
	 */
	public static function redact_rest( $response, $post, $request ) {
		unset( $request );

		if ( ! $response instanceof \WP_REST_Response || ! $post instanceof \WP_Post ) {
			return $response;
		}

		$data = $response->get_data();
		if ( ! isset( $data['meta'] )
			|| ! is_array( $data['meta'] )
			|| ! array_key_exists( self::META_KEY, $data['meta'] )
			|| XFN_Content_Scanner::can_read_relationships( $post ) ) {
			return $response;
		}

		$data['meta'][ self::META_KEY ] = [];
		$response->set_data( $data );

		return $response;
	}

	/**
	 * Register the relationships post meta for all post types.
	 */
	public static function register_meta(): void {
		register_post_meta(
			'',
			self::META_KEY,
			[
				'type'              => 'array',
				'single'            => true,
				'default'           => [],
				'show_in_rest'      => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'url'  => [
									'type'   => 'string',
									'format' => 'uri',
								],
								'rels' => [
									'type'  => 'array',
									'items' => [ 'type' => 'string' ],
								],
							],
						],
					],
				],
				'sanitize_callback' => [ __CLASS__, 'sanitize_relationships' ],
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Sanitize a relationships array: valid http(s) URL plus XFN rels only.
	 *
	 * @param mixed $input Raw meta value.
	 * @return array Sanitized list of { url, rels } entries.
	 */
	public static function sanitize_relationships( $input ): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];
		foreach ( $input as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['url'] ) || empty( $entry['rels'] ) ) {
				continue;
			}

			// Scheme + host validation only: this stores a link, it never
			// fetches it, so DNS/SSRF checks (wp_http_validate_url) don't
			// apply — they drop unresolvable hosts and offline saves.
			$parsed = wp_parse_url( (string) $entry['url'] );
			if ( empty( $parsed['scheme'] ) || empty( $parsed['host'] )
				|| ! in_array( strtolower( $parsed['scheme'] ), [ 'http', 'https' ], true ) ) {
				continue;
			}

			$url = esc_url_raw( $entry['url'], [ 'http', 'https' ] );
			if ( empty( $url ) ) {
				continue;
			}

			$rels = array_filter(
				(array) $entry['rels'],
				function ( $rel ) {
					return in_array( $rel, self::$valid_xfn, true );
				}
			);

			if ( ! empty( $rels ) ) {
				$clean[] = [
					'url'  => $url,
					'rels' => array_values( $rels ),
				];
			}
		}

		return $clean;
	}

	/**
	 * Get the stored relationships for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array List of { url, rels } entries.
	 */
	public static function get_relationships( int $post_id ): array {
		$meta = get_post_meta( $post_id, self::META_KEY, true );
		return is_array( $meta ) ? $meta : [];
	}

	/**
	 * Replace the stored relationships for a post.
	 *
	 * @param int   $post_id       Post ID.
	 * @param array $relationships List of { url, rels } entries.
	 */
	public static function set_relationships( int $post_id, array $relationships ): void {
		$clean = self::sanitize_relationships( $relationships );
		update_post_meta( $post_id, self::META_KEY, $clean );
		update_post_meta( $post_id, self::SOURCE_KEY, 'meta' );
	}

	/**
	 * Add or merge a relationship for a URL on a post.
	 *
	 * @param int      $post_id Post ID.
	 * @param string   $url     Target URL.
	 * @param string[] $rels    XFN rel values to add.
	 */
	public static function add_relationship( int $post_id, string $url, array $rels ): void {
		$existing = self::get_relationships( $post_id );

		// Update existing or append.
		$found = false;
		foreach ( $existing as &$entry ) {
			if ( $entry['url'] === $url ) {
				$entry['rels'] = array_values( array_unique( array_merge( $entry['rels'], $rels ) ) );
				$found         = true;
				break;
			}
		}
		unset( $entry );

		if ( ! $found ) {
			$existing[] = [
				'url'  => $url,
				'rels' => $rels,
			];
		}

		self::set_relationships( $post_id, $existing );
	}

	/**
	 * Remove the relationship entry for a URL on a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $url     Target URL.
	 */
	public static function remove_relationship( int $post_id, string $url ): void {
		$existing = self::get_relationships( $post_id );
		$filtered = array_values(
			array_filter(
				$existing,
				function ( $entry ) use ( $url ) {
					return $entry['url'] !== $url;
				}
			)
		);

		self::set_relationships( $post_id, $filtered );
	}

	/**
	 * Inject rel attributes for stored relationships into post content.
	 *
	 * @param string $content       Post content HTML.
	 * @param array  $relationships List of { url, rels } entries.
	 * @return string Updated content.
	 */
	public static function apply_to_content( string $content, array $relationships ): string {
		if ( empty( $relationships ) ) {
			return $content;
		}

		$rels_by_url = [];
		foreach ( $relationships as $entry ) {
			$rels_by_url[ $entry['url'] ] = $entry['rels'];
		}

		$tags = new \WP_HTML_Tag_Processor( $content );
		while ( $tags->next_tag( 'A' ) ) {
			$href = $tags->get_attribute( 'href' );
			if ( ! is_string( $href ) || ! isset( $rels_by_url[ $href ] ) ) {
				continue;
			}

			$rel    = $tags->get_attribute( 'rel' );
			$parsed = XFN_Link_Extension::parse_rel_attribute( is_string( $rel ) ? $rel : '' );
			$merged = XFN_Link_Extension::combine_rel_values( $rels_by_url[ $href ], $parsed['other'] );
			if ( $merged !== $rel ) {
				$tags->set_attribute( 'rel', $merged );
			}
		}

		return $tags->get_updated_html();
	}

	/**
	 * When an update changes content, move the mirror to the new markup.
	 *
	 * A restore rebuilds the mirror from the restored markup and drops any
	 * pending write. It creates the mirror when a post without one restores
	 * markup with XFN links. An ordinary edit leaves a post without a mirror
	 * alone. After an ordinary edit, entries for links the change
	 * added, removed, or re-related follow the new markup. Entries for links
	 * it left alone, or that were never in the saved markup, keep their stored
	 * value, so a pending write still lands through sync_meta_to_content().
	 *
	 * @param int      $post_id     Post ID.
	 * @param \WP_Post $post_after  Post object after the update.
	 * @param \WP_Post $post_before Post object before the update.
	 */
	public static function sync_content_to_meta( int $post_id, \WP_Post $post_after, \WP_Post $post_before ): void {
		if ( wp_is_post_revision( $post_id ) || $post_after->post_content === $post_before->post_content ) {
			return;
		}

		$after    = array_column( XFN_Content_Scanner::extract_xfn_links_from_post( $post_after ), 'rels', 'url' );
		$mirrored = metadata_exists( 'post', $post_id, self::META_KEY );
		$restored = ( $mirrored || [] !== $after ) && self::is_restore( $post_after );
		if ( ! $mirrored && ! $restored ) {
			return;
		}

		$rels_by_url = [];
		if ( ! $restored ) {
			$before = array_column( XFN_Content_Scanner::extract_xfn_links_from_post( $post_before ), 'rels', 'url' );
			foreach ( self::get_relationships( $post_id ) as $entry ) {
				if ( ( $before[ $entry['url'] ] ?? null ) === ( $after[ $entry['url'] ] ?? null ) ) {
					$rels_by_url[ $entry['url'] ] ??= $entry['rels'];
				}
			}
		}

		$relationships = [];
		foreach ( $rels_by_url + $after as $url => $rels ) {
			$relationships[] = [
				'url'  => $url,
				'rels' => $rels,
			];
		}

		update_post_meta( $post_id, self::META_KEY, $relationships );
		if ( $restored ) {
			delete_post_meta( $post_id, self::SOURCE_KEY );
		}
	}

	/**
	 * Determine whether a saved post's content restores one of its revisions.
	 *
	 * Visual Revisions restores through a plain REST save, and the
	 * wp_restore_post_revision action fires only after the update, so the
	 * saved content is the only signal both paths share. Autosaves don't
	 * count: an ordinary save often carries the content of the last autosave.
	 *
	 * @param \WP_Post $post Post object after the update.
	 * @return bool Whether the content equals a stored revision.
	 */
	private static function is_restore( \WP_Post $post ): bool {
		foreach ( wp_get_post_revisions( $post->ID ) as $revision ) {
			if ( ! wp_is_post_autosave( $revision ) && $revision->post_content === $post->post_content ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * On save, write meta-sourced relationships back into post content.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public static function sync_meta_to_content( int $post_id, \WP_Post $post ): void {
		// Only sync if meta was the last writer.
		$source = get_post_meta( $post_id, self::SOURCE_KEY, true );
		if ( 'meta' !== $source || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$relationships = self::get_relationships( $post_id );
		if ( empty( $relationships ) ) {
			return;
		}

		$updated_content = self::apply_to_content( $post->post_content, $relationships );
		if ( $updated_content !== $post->post_content ) {
			remove_action( 'save_post', [ __CLASS__, 'sync_meta_to_content' ], 20 );
			wp_update_post(
				[
					'ID'           => $post_id,
					'post_content' => wp_slash( $updated_content ),
				]
			);
			add_action( 'save_post', [ __CLASS__, 'sync_meta_to_content' ], 20, 2 );
		}

		// Reset source flag.
		delete_post_meta( $post_id, self::SOURCE_KEY );
	}
}
