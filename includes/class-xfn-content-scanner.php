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
	 * Extract XFN links from a single post's content.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post The post to scan.
	 * @return array Array of associative arrays with post_id, url, rels keys.
	 */
	public static function extract_xfn_links_from_post( \WP_Post $post ): array {
		$relationships = [];

		if ( ! preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			return $relationships;
		}

		foreach ( $matches as $match ) {
			$full_tag = $match[0];
			$href     = $match[1];

			if ( ! preg_match( '/rel=["\']([^"\']*)["\']/', $full_tag, $rel_match ) ) {
				continue;
			}

			$parsed = XFN_Link_Extension::parse_rel_attribute( $rel_match[1] );
			if ( ! empty( $parsed['xfn'] ) ) {
				$relationships[] = [
					'post_id' => $post->ID,
					'url'     => $href,
					'rels'    => $parsed['xfn'],
				];
			}
		}

		return $relationships;
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

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		// Only fetch posts whose content contains a rel attribute to minimize processing.
		$post_ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_status = 'publish'
			   AND post_type IN ('post', 'page')
			   AND post_content LIKE '%rel=%'
			 ORDER BY ID DESC
			 LIMIT 500"
		);

		$relationships = [];
		foreach ( $post_ids as $pid ) {
			$post = get_post( (int) $pid );
			if ( $post ) {
				$relationships = array_merge( $relationships, self::extract_xfn_links_from_post( $post ) );
			}
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
}
