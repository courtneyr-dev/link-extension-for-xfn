<?php
/**
 * XFN content-based abilities for the Abilities API.
 *
 * Unlike XFN_Core_Abilities which works through post meta,
 * these abilities operate directly on post_content HTML rel attributes.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers abilities that read and write XFN rels in post content.
 */
class XFN_Content_Abilities {

	/**
	 * Register all content-based XFN abilities.
	 *
	 * @since 1.0.0
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_add_relationship();
		$this->register_remove_relationship();
		$this->register_get_relationships();
		$this->register_validate_relationships();
		$this->register_suggest_relationship();
	}

	/**
	 * Get all content-based ability names.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function get_ability_names(): array {
		return [
			'xfn/add-relationship',
			'xfn/remove-relationship',
			'xfn/get-relationships',
			'xfn/validate-relationships',
			'xfn/suggest-relationship',
		];
	}

	/**
	 * Register the xfn/add-relationship ability.
	 */
	private function register_add_relationship(): void {
		wp_register_ability(
			'xfn/add-relationship',
			[
				'label'               => __( 'Add XFN Relationship (Content)', 'link-extension-for-xfn' ),
				'description'         => __( 'Parse post_content, find a matching link by URL, and add XFN rel values.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'type'                => 'tool',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'The post ID containing the link.',
						],
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The href URL to match in post_content.',
						],
						'rels'    => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'XFN relationship values to add.',
						],
					],
					'required'   => [ 'post_id', 'url', 'rels' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'error'   => [ 'type' => 'string' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_add_relationship' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/remove-relationship ability.
	 */
	private function register_remove_relationship(): void {
		wp_register_ability(
			'xfn/remove-relationship',
			[
				'label'               => __( 'Remove XFN Relationship (Content)', 'link-extension-for-xfn' ),
				'description'         => __( 'Parse post_content, find a matching link by URL, and remove specified XFN rel values.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'type'                => 'tool',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'The post ID containing the link.',
						],
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The href URL to match in post_content.',
						],
						'rels'    => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'XFN relationship values to remove. If empty, removes all XFN rels from the link.',
						],
					],
					'required'   => [ 'post_id', 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'error'   => [ 'type' => 'string' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_remove_relationship' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/get-relationships ability.
	 */
	private function register_get_relationships(): void {
		wp_register_ability(
			'xfn/get-relationships',
			[
				'label'               => __( 'Get XFN Relationships (Content)', 'link-extension-for-xfn' ),
				'description'         => __( 'Scan post_content for links with XFN rel attributes. Optionally filter by post.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'type'                => 'resource',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'Optional post ID. If omitted, scans all published posts.',
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'relationships' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'post_id' => [ 'type' => 'integer' ],
									'url'     => [ 'type' => 'string' ],
									'rels'    => [
										'type'  => 'array',
										'items' => [ 'type' => 'string' ],
									],
								],
							],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute_get_relationships' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/validate-relationships ability.
	 */
	private function register_validate_relationships(): void {
		wp_register_ability(
			'xfn/validate-relationships',
			[
				'label'               => __( 'Validate XFN Relationships (Content)', 'link-extension-for-xfn' ),
				'description'         => __( 'Check if a set of XFN rel values respects XFN 1.1 exclusivity rules.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'type'                => 'resource',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'rels' => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Array of XFN relationship values to validate.',
						],
					],
					'required'   => [ 'rels' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'valid'  => [ 'type' => 'boolean' ],
						'errors' => [
							'type'  => 'array',
							'items' => [ 'type' => 'string' ],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute_validate_relationships' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/suggest-relationship ability.
	 */
	private function register_suggest_relationship(): void {
		wp_register_ability(
			'xfn/suggest-relationship',
			[
				'label'               => __( 'Suggest XFN Relationship', 'link-extension-for-xfn' ),
				'description'         => __( 'Suggest appropriate XFN rel values for a URL using AI or heuristics.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'type'                => 'resource',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The URL to analyze for relationship suggestions.',
						],
						'context' => [
							'type'        => 'string',
							'description' => 'Optional surrounding text or context for better suggestions.',
						],
					],
					'required'   => [ 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'suggestions' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'rel'        => [ 'type' => 'string' ],
									'confidence' => [ 'type' => 'number' ],
									'reason'     => [ 'type' => 'string' ],
								],
							],
						],
						'source'      => [ 'type' => 'string' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_suggest_relationship' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	// ── Execute callbacks ────────────────────────────────────────────────

	/**
	 * Add XFN rel values to a link in post_content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Input parameters.
	 * @return array Result with success status.
	 */
	public function execute_add_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];
		$rels    = array_filter(
			(array) $input['rels'],
			function ( $r ) {
				return in_array( $r, XFN_Content_Scanner::VALID_XFN, true );
			}
		);

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'error'   => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return [
				'success' => false,
				'error'   => 'Insufficient permissions for this post.',
			];
		}

		if ( empty( $rels ) ) {
			return [
				'success' => false,
				'error'   => 'No valid XFN rel values provided.',
			];
		}

		$updated = $this->modify_link_rel( $post->post_content, $url, $rels, 'add' );

		if ( $updated === $post->post_content ) {
			return [
				'success' => false,
				'error'   => 'Link not found in post content.',
			];
		}

		wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => $updated,
			]
		);

		XFN_Content_Scanner::invalidate_cache();

		return [ 'success' => true ];
	}

	/**
	 * Remove XFN rel values from a link in post_content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Input parameters.
	 * @return array Result with success status.
	 */
	public function execute_remove_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];
		$rels    = isset( $input['rels'] ) ? (array) $input['rels'] : [];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'error'   => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return [
				'success' => false,
				'error'   => 'Insufficient permissions for this post.',
			];
		}

		$updated = $this->modify_link_rel( $post->post_content, $url, $rels, 'remove' );

		if ( $updated === $post->post_content ) {
			return [
				'success' => false,
				'error'   => 'Link not found in post content.',
			];
		}

		wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => $updated,
			]
		);

		XFN_Content_Scanner::invalidate_cache();

		return [ 'success' => true ];
	}

	/**
	 * Get XFN relationships from post_content HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Input parameters.
	 * @return array Relationships found.
	 */
	public function execute_get_relationships( array $input ): array {
		if ( ! empty( $input['post_id'] ) ) {
			$post_id = (int) $input['post_id'];
			$post    = get_post( $post_id );

			if ( ! $post ) {
				return [
					'relationships' => [],
					'error'         => 'Post not found.',
				];
			}

			if ( ! current_user_can( 'read_post', $post_id ) ) {
				return [
					'relationships' => [],
					'error'         => 'Insufficient permissions for this post.',
				];
			}

			return [
				'relationships' => XFN_Content_Scanner::extract_xfn_links_from_post( $post ),
			];
		}

		// Scan all published posts with transient caching.
		return [
			'relationships' => XFN_Content_Scanner::scan_all_posts_for_xfn(),
		];
	}

	/**
	 * Validate XFN rel values against XFN 1.1 exclusivity rules.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Input parameters.
	 * @return array Validation result with errors array.
	 */
	public function execute_validate_relationships( array $input ): array {
		$rels   = (array) $input['rels'];
		$errors = [];

		// Check for invalid values.
		$invalid = array_diff( $rels, XFN_Content_Scanner::VALID_XFN );
		if ( ! empty( $invalid ) ) {
			$errors[] = sprintf(
				/* translators: %s: comma-separated list of invalid values */
				__( 'Invalid XFN values: %s', 'link-extension-for-xfn' ),
				implode( ', ', $invalid )
			);
		}

		// Check exclusivity groups.
		foreach ( XFN_Content_Scanner::EXCLUSIVITY_GROUPS as $group_name => $group_values ) {
			$matches = array_intersect( $rels, $group_values );
			if ( count( $matches ) > 1 ) {
				$errors[] = sprintf(
					/* translators: 1: group name, 2: conflicting values */
					__( 'Exclusive group "%1$s" has multiple values: %2$s', 'link-extension-for-xfn' ),
					$group_name,
					implode( ', ', $matches )
				);
			}
		}

		return [
			'valid'  => empty( $errors ),
			'errors' => $errors,
		];
	}

	/**
	 * Suggest XFN relationships for a URL.
	 *
	 * Uses WP AI Client when available, falls back to URL heuristics.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Input parameters.
	 * @return array Suggestions with confidence and source.
	 */
	public function execute_suggest_relationship( array $input ): array {
		$url     = (string) $input['url'];
		$context = isset( $input['context'] ) ? (string) $input['context'] : '';

		// Try AI client first.
		if ( function_exists( 'wp_ai_client' ) ) {
			$ai_result = $this->suggest_with_ai( $url, $context );
			if ( ! empty( $ai_result ) ) {
				return [
					'suggestions' => $ai_result,
					'source'      => 'ai',
				];
			}
		}

		// Fall back to heuristics.
		return [
			'suggestions' => $this->suggest_with_heuristics( $url, $context ),
			'source'      => 'heuristics',
		];
	}

	// ── Helpers ──────────────────────────────────────────────────────────

	/**
	 * Modify XFN rel values on a specific link in HTML content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content  Post HTML content.
	 * @param string $url      URL to match.
	 * @param array  $rels     XFN values to add or remove.
	 * @param string $action   'add' or 'remove'.
	 * @return string Modified content.
	 */
	private function modify_link_rel( string $content, string $url, array $rels, string $action ): string {
		$escaped_url = preg_quote( $url, '/' );

		return preg_replace_callback(
			'/<a\s+([^>]*?)href\s*=\s*["\']' . $escaped_url . '["\']([^>]*?)>/i',
			function ( $matches ) use ( $rels, $action ) {
				$before = $matches[1];
				$after  = $matches[2];
				$tag    = $matches[0];

				// Extract existing rel attribute.
				$existing_rel = '';
				if ( preg_match( '/rel=["\']([^"\']*)["\']/', $before . $after, $rel_match ) ) {
					$existing_rel = $rel_match[1];
					$tag          = str_replace( $rel_match[0], '', $tag );
				}

				$parsed      = XFN_Link_Extension::parse_rel_attribute( $existing_rel );
				$current_xfn = $parsed['xfn'];
				$other_rels  = $parsed['other'];

				if ( 'add' === $action ) {
					$current_xfn = array_values( array_unique( array_merge( $current_xfn, $rels ) ) );
				} elseif ( empty( $rels ) ) {
					// Remove with no specific rels: remove all XFN values.
					$current_xfn = [];
				} else {
					$current_xfn = array_values( array_diff( $current_xfn, $rels ) );
				}

				$new_rel = XFN_Link_Extension::combine_rel_values( $current_xfn, $other_rels );

				if ( ! empty( $new_rel ) ) {
					return rtrim( $tag, '>' ) . ' rel="' . esc_attr( $new_rel ) . '">';
				}

				return rtrim( $tag, '>' ) . '>';
			},
			$content
		);
	}

	/**
	 * Suggest relationships using WP AI Client.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url     The URL to analyze.
	 * @param string $context Surrounding text context.
	 * @return array Suggestions array, empty on failure.
	 */
	private function suggest_with_ai( string $url, string $context ): array {
		try {
			$client = wp_ai_client();
			$prompt = sprintf(
				'Given the URL "%s" and context "%s", suggest appropriate XFN 1.1 relationship values. '
				. 'Valid values are: %s. '
				. 'Return a JSON array of objects with "rel", "confidence" (0-1), and "reason" keys.',
				$url,
				$context,
				implode( ', ', XFN_Content_Scanner::VALID_XFN )
			);

			$response = $client->generate_text( $prompt );

			if ( ! empty( $response ) ) {
				$decoded = json_decode( $response, true );
				if ( is_array( $decoded ) ) {
					return array_filter(
						$decoded,
						function ( $item ) {
							return isset( $item['rel'] ) && in_array( $item['rel'], XFN_Content_Scanner::VALID_XFN, true );
						}
					);
				}
			}
		} catch ( \Exception $e ) {
			// AI unavailable or errored; caller falls back to heuristics.
			return [];
		}

		return [];
	}

	/**
	 * Suggest relationships using URL pattern heuristics.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url     The URL to analyze.
	 * @param string $context Surrounding text context.
	 * @return array Suggestions array.
	 */
	private function suggest_with_heuristics( string $url, string $context ): array {
		$suggestions = [];
		$parsed_url  = wp_parse_url( $url );
		$host        = isset( $parsed_url['host'] ) ? strtolower( $parsed_url['host'] ) : '';
		$path        = isset( $parsed_url['path'] ) ? strtolower( $parsed_url['path'] ) : '';

		// Same domain as site = likely "me".
		$site_host = strtolower( wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( $host === $site_host ) {
			$suggestions[] = [
				'rel'        => 'me',
				'confidence' => 0.8,
				'reason'     => __( 'URL is on the same domain as this site.', 'link-extension-for-xfn' ),
			];
		}

		// Social profile patterns.
		$social_domains = [
			'twitter.com',
			'x.com',
			'facebook.com',
			'linkedin.com',
			'instagram.com',
			'github.com',
			'mastodon.social',
		];
		foreach ( $social_domains as $social ) {
			if ( str_contains( $host, $social ) ) {
				$suggestions[] = [
					'rel'        => 'me',
					'confidence' => 0.6,
					'reason'     => sprintf(
						/* translators: %s: social platform domain */
						__( 'Social profile on %s often indicates identity.', 'link-extension-for-xfn' ),
						$social
					),
				];
				break;
			}
		}

		// Path-based hints.
		$path_hints = [
			'/about'   => [
				'rel'        => 'acquaintance',
				'confidence' => 0.3,
			],
			'/team'    => [
				'rel'        => 'co-worker',
				'confidence' => 0.4,
			],
			'/staff'   => [
				'rel'        => 'co-worker',
				'confidence' => 0.4,
			],
			'/contact' => [
				'rel'        => 'contact',
				'confidence' => 0.4,
			],
		];

		foreach ( $path_hints as $segment => $hint ) {
			if ( str_starts_with( $path, $segment ) ) {
				$suggestions[] = [
					'rel'        => $hint['rel'],
					'confidence' => $hint['confidence'],
					'reason'     => sprintf(
						/* translators: %s: URL path segment */
						__( 'URL path "%s" suggests this relationship.', 'link-extension-for-xfn' ),
						$segment
					),
				];
			}
		}

		// Context-based hints.
		if ( ! empty( $context ) ) {
			$context_lower = strtolower( $context );
			$context_hints = [
				'friend'    => [
					'rel'        => 'friend',
					'confidence' => 0.5,
				],
				'colleague' => [
					'rel'        => 'colleague',
					'confidence' => 0.5,
				],
				'neighbor'  => [
					'rel'        => 'neighbor',
					'confidence' => 0.5,
				],
				'spouse'    => [
					'rel'        => 'spouse',
					'confidence' => 0.6,
				],
				'sibling'   => [
					'rel'        => 'sibling',
					'confidence' => 0.6,
				],
				'parent'    => [
					'rel'        => 'parent',
					'confidence' => 0.5,
				],
				'child'     => [
					'rel'        => 'child',
					'confidence' => 0.5,
				],
			];

			foreach ( $context_hints as $keyword => $hint ) {
				if ( str_contains( $context_lower, $keyword ) ) {
					$suggestions[] = [
						'rel'        => $hint['rel'],
						'confidence' => $hint['confidence'],
						'reason'     => sprintf(
							/* translators: %s: matched keyword */
							__( 'Context contains keyword "%s".', 'link-extension-for-xfn' ),
							$keyword
						),
					];
				}
			}
		}

		return $suggestions;
	}
}
