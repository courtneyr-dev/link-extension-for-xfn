<?php
/**
 * XFN Outpost Adapter — consumes Outpost's `_outpost_xfn` postmeta.
 *
 * Outpost's Micropub bridge captures `mp-xfn` / `mp-xfn-target` properties
 * into a single `_outpost_xfn` postmeta value (a JSON-encoded object:
 * `{"target": "<url>", "rels": ["friend", "met"]}`). This adapter listens
 * for writes to that key and translates them into the plugin's own
 * relationship store via XFN_Meta_Mirror, which then injects the `rel`
 * attribute onto the matching link in the post content.
 *
 * @package LinkExtensionForXFN
 * @since   1.1.0
 */

/**
 * Translates Outpost's `_outpost_xfn` postmeta into XFN relationships.
 */
final class XFN_Outpost_Adapter {

	/**
	 * Meta key Outpost's Micropub bridge writes.
	 */
	public const OUTPOST_META_KEY = '_outpost_xfn';

	/**
	 * Hook the adapter into postmeta writes.
	 */
	public static function init(): void {
		add_action( 'added_post_meta', [ __CLASS__, 'consume' ], 10, 4 );
		add_action( 'updated_post_meta', [ __CLASS__, 'consume' ], 10, 4 );
	}

	/**
	 * Translate an `_outpost_xfn` write into XFN_Meta_Mirror relationships.
	 *
	 * Runs on every postmeta write and returns immediately for other keys.
	 * The payload is validated here only for shape (valid JSON, non-empty
	 * target URL, non-empty rels array); rel values are validated against
	 * the XFN 1.1 list by XFN_Meta_Mirror::sanitize_relationships().
	 *
	 * @param int    $meta_id  Meta row ID (unused).
	 * @param int    $post_id  Post the meta belongs to.
	 * @param string $meta_key Meta key being written.
	 * @param mixed  $value    Meta value being written.
	 */
	public static function consume( $meta_id, $post_id, $meta_key, $value ): void {
		if ( self::OUTPOST_META_KEY !== $meta_key || ! is_string( $value ) ) {
			return;
		}

		$payload = json_decode( $value, true );
		if ( ! is_array( $payload ) || empty( $payload['target'] ) || empty( $payload['rels'] ) ) {
			return;
		}

		$parsed = wp_parse_url( (string) $payload['target'] );
		if ( empty( $parsed['scheme'] ) || empty( $parsed['host'] )
			|| ! in_array( strtolower( $parsed['scheme'] ), [ 'http', 'https' ], true )
		) {
			return;
		}

		$target = esc_url_raw( (string) $payload['target'], [ 'http', 'https' ] );
		if ( '' === $target ) {
			return;
		}

		$rels = array_values( array_filter( array_map( 'strval', (array) $payload['rels'] ) ) );
		if ( empty( $rels ) ) {
			return;
		}

		XFN_Meta_Mirror::add_relationship( (int) $post_id, $target, $rels );

		// Micropub posts get no further save_post, so sync content now.
		$post = get_post( (int) $post_id );
		if ( $post instanceof WP_Post ) {
			XFN_Meta_Mirror::sync_meta_to_content( (int) $post_id, $post );
		}
	}
}
