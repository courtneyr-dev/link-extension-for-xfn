<?php
/**
 * Utility for scanning post content for XFN relationships.
 *
 * Extracts XFN link data from HTML rel attributes in post_content.
 * Used by both the Abilities API and frontend blocks/interactivity.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scans post content for XFN link relationships.
 */
final class XFN_Content_Scanner {

	/**
	 * Valid XFN 1.1 relationship values.
	 *
	 * @var string[]
	 */
	public const VALID_XFN = [
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
	 * XFN 1.1 mutual exclusivity groups.
	 *
	 * Within each group, only one value should be applied per link.
	 *
	 * @var array<string, string[]>
	 */
	public const EXCLUSIVITY_GROUPS = [
		'friendship'   => [ 'contact', 'acquaintance', 'friend' ],
		'geographical' => [ 'co-resident', 'neighbor' ],
		'family'       => [ 'child', 'parent', 'sibling', 'spouse', 'kin' ],
	];

	private const TRANSIENT_PREFIX = 'xfn_rels_';
	private const TRANSIENT_TTL    = 5 * MINUTE_IN_SECONDS;

	/**
	 * Extract XFN links from a single post's content, one per URL.
	 *
	 * The directory and the meta mirror both read links here. URLs resolve
	 * against the home URL and pass the mirror's sanitizer, and the first
	 * anchor for a URL wins.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post The post to scan.
	 * @return array Array of associative arrays with post_id, url, rels keys.
	 */
	public static function extract_xfn_links_from_post( \WP_Post $post ): array {
		$links = [];
		$base  = home_url( '/' );
		$tags  = new \WP_HTML_Tag_Processor( $post->post_content );
		while ( $tags->next_tag( 'A' ) ) {
			$href = $tags->get_attribute( 'href' );
			$rel  = $tags->get_attribute( 'rel' );
			if ( is_string( $href ) && '' !== $href && is_string( $rel ) ) {
				$links[] = [
					'url'  => \WP_Http::make_absolute_url( $href, $base ),
					'rels' => XFN_Link_Extension::parse_rel_attribute( $rel )['xfn'],
				];
			}
		}

		$relationships = [];
		foreach ( XFN_Meta_Mirror::sanitize_relationships( $links ) as $link ) {
			$relationships[ $link['url'] ] ??= [ 'post_id' => $post->ID ] + $link;
		}

		return array_values( $relationships );
	}

	/**
	 * Determine whether a post may expose its XFN relationships publicly.
	 *
	 * @since 1.1.3
	 *
	 * @param \WP_Post $post Post to check.
	 * @return bool Whether the post is publicly eligible.
	 */
	public static function is_eligible( \WP_Post $post ): bool {
		return 'publish' === $post->post_status
			&& '' === (string) $post->post_password
			&& is_post_publicly_viewable( $post );
	}

	/**
	 * Keep only posts whose XFN relationships may be exposed publicly.
	 *
	 * @since 1.1.3
	 *
	 * @param \WP_Post[] $posts Posts to filter.
	 * @return \WP_Post[] Eligible posts in their original order.
	 */
	public static function filter_eligible( array $posts ): array {
		return array_values( array_filter( $posts, [ self::class, 'is_eligible' ] ) );
	}

	/**
	 * Determine whether the current REST user may read relationship meta.
	 *
	 * @since 1.1.3
	 *
	 * @param \WP_Post $post Post to check.
	 * @return bool Whether relationship meta may be read.
	 */
	public static function can_read_relationships( \WP_Post $post ): bool {
		return self::is_eligible( $post ) || current_user_can( 'edit_post', $post->ID );
	}

	/**
	 * Determine whether an ability may return a post's relationships.
	 *
	 * @since 1.1.3
	 *
	 * @param \WP_Post $post Post to check.
	 * @return bool Whether an ability may return the relationships.
	 */
	public static function ability_can_read( \WP_Post $post ): bool {
		return self::is_eligible( $post );
	}

	/**
	 * Scan all published posts for XFN links with transient caching.
	 *
	 * @since 1.0.0
	 *
	 * @return array All relationships found across posts.
	 */
	public static function scan_all_posts_for_xfn(): array {
		$transient_key = self::TRANSIENT_PREFIX . 'all';
		$cached        = get_transient( $transient_key );

		if ( false !== $cached && is_array( $cached ) ) {
			$post_ids = [];
			foreach ( $cached as $relationship ) {
				if ( is_array( $relationship ) && isset( $relationship['post_id'] ) ) {
					$post_ids[] = (int) $relationship['post_id'];
				}
			}
			$post_ids = array_values( array_unique( $post_ids ) );
			_prime_post_caches( $post_ids, false, false );

			$eligible = array_filter(
				$cached,
				static function ( $relationship ): bool {
					$post = is_array( $relationship ) && isset( $relationship['post_id'] )
						? get_post( (int) $relationship['post_id'] )
						: null;

					return $post instanceof \WP_Post && self::is_eligible( $post );
				}
			);

			if ( count( $eligible ) !== count( $cached ) ) {
				self::invalidate_cache();
			}

			return array_values( $eligible );
		}

		global $wpdb;

		// extract_xfn_links_from_post() decides which posts hold a rel attribute, so this only drops posts without "rel".
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Static LIKE prefilter on core columns with no WP_Query equivalent; result set is cached in a transient above.
		$post_ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_status = 'publish'
			   AND post_type IN ('post', 'page')
			   AND post_password = ''
			   AND post_content LIKE '%rel%'
			 ORDER BY ID DESC
			 LIMIT 500"
		);

		$posts = [];
		foreach ( $post_ids as $pid ) {
			$post = get_post( (int) $pid );
			if ( $post instanceof \WP_Post ) {
				$posts[] = $post;
			}
		}
		$posts = self::filter_eligible( $posts );

		$relationships = [];
		foreach ( $posts as $post ) {
			$relationships = array_merge( $relationships, self::extract_xfn_links_from_post( $post ) );
		}

		set_transient( $transient_key, $relationships, self::TRANSIENT_TTL );

		return $relationships;
	}

	/**
	 * Invalidate cached relationship data.
	 *
	 * @since 1.0.0
	 */
	public static function invalidate_cache(): void {
		delete_transient( self::TRANSIENT_PREFIX . 'all' );
	}

	/**
	 * Register cache invalidation hooks for post lifecycle changes.
	 *
	 * @since 1.1.3
	 */
	public static function register_hooks(): void {
		foreach ( [ 'save_post', 'transition_post_status', 'trashed_post', 'untrashed_post', 'deleted_post' ] as $hook ) {
			add_action( $hook, [ self::class, 'invalidate_cache' ] );
		}
	}
}
