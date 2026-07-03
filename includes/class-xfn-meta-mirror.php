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

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_meta' ] );
		add_action( 'save_post', [ __CLASS__, 'sync_meta_to_content' ], 20, 2 );
	}

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

	public static function get_relationships( int $post_id ): array {
		$meta = get_post_meta( $post_id, self::META_KEY, true );
		return is_array( $meta ) ? $meta : [];
	}

	public static function set_relationships( int $post_id, array $relationships ): void {
		$clean = self::sanitize_relationships( $relationships );
		update_post_meta( $post_id, self::META_KEY, $clean );
		update_post_meta( $post_id, self::SOURCE_KEY, 'meta' );
	}

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

	public static function apply_to_content( string $content, array $relationships ): string {
		foreach ( $relationships as $entry ) {
			$url      = preg_quote( $entry['url'], '/' );
			$xfn_rels = $entry['rels'];

			$content = preg_replace_callback(
				'/<a\s+([^>]*?)href\s*=\s*["\']' . $url . '["\']([^>]*?)>/i',
				function ( $matches ) use ( $xfn_rels ) {
					$before = $matches[1];
					$after  = $matches[2];
					$tag    = $matches[0];

					// Extract existing rel.
					$existing_rel = '';
					if ( preg_match( '/rel=["\']([^"\']*)["\']/', $before . $after, $rel_match ) ) {
						$existing_rel = $rel_match[1];
						$tag          = str_replace( $rel_match[0], '', $tag );
					}

					// Separate non-XFN rels.
					$parsed     = XFN_Link_Extension::parse_rel_attribute( $existing_rel );
					$other_rels = $parsed['other'];

					// Combine.
					$new_rel = XFN_Link_Extension::combine_rel_values( $xfn_rels, $other_rels );

					// Insert rel before closing >.
					return rtrim( $tag, '>' ) . ' rel="' . esc_attr( $new_rel ) . '">';
				},
				$content
			);
		}

		return $content;
	}

	public static function sync_meta_to_content( int $post_id, \WP_Post $post ): void {
		// Only sync if meta was the last writer.
		$source = get_post_meta( $post_id, self::SOURCE_KEY, true );
		if ( 'meta' !== $source ) {
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
					'post_content' => $updated_content,
				]
			);
			add_action( 'save_post', [ __CLASS__, 'sync_meta_to_content' ], 20, 2 );
		}

		// Reset source flag.
		delete_post_meta( $post_id, self::SOURCE_KEY );
	}
}
