<?php
/**
 * Interactivity API tooltip system for XFN links.
 *
 * Adds relationship tooltips to XFN-annotated links on the frontend
 * using the WordPress Interactivity API.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers frontend tooltip behavior for XFN-annotated links.
 */
class XFN_Interactivity {

	/**
	 * Whether tooltip assets have been enqueued for the current request.
	 *
	 * @var bool
	 */
	private bool $assets_enqueued = false;

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_filter( 'render_block', [ $this, 'process_block' ], 10, 2 );
	}

	/**
	 * Register script module and stylesheet for lazy enqueuing.
	 */
	public function register_assets(): void {
		wp_register_script_module(
			'xfn-links/tooltip',
			XFN_LINK_EXTENSION_PLUGIN_URL . 'build/interactivity/tooltip.js',
			[ '@wordpress/interactivity' ],
			XFN_LINK_EXTENSION_VERSION
		);

		wp_register_style(
			'xfn-tooltip',
			XFN_LINK_EXTENSION_PLUGIN_URL . 'build/interactivity/tooltip.css',
			[],
			XFN_LINK_EXTENSION_VERSION
		);
	}

	/**
	 * Process a rendered block to add tooltip directives to XFN links.
	 *
	 * Two-pass approach:
	 * 1. WP_HTML_Tag_Processor finds XFN links and adds Interactivity API attributes.
	 * 2. Regex wraps marked links with tooltip containers.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Block data.
	 * @return string Modified block HTML.
	 */
	public function process_block( string $block_content, array $block ): string {
		if ( empty( $block_content ) || false === strpos( $block_content, 'rel=' ) ) {
			return $block_content;
		}

		// Pass 1: Find XFN links and add directives.
		$processor = new WP_HTML_Tag_Processor( $block_content );
		$xfn_links = [];

		while ( $processor->next_tag( 'a' ) ) {
			$rel = $processor->get_attribute( 'rel' );
			if ( ! $rel ) {
				continue;
			}

			$parsed = XFN_Link_Extension::parse_rel_attribute( $rel );
			if ( empty( $parsed['xfn'] ) ) {
				continue;
			}

			$id = count( $xfn_links );
			$processor->set_attribute( 'data-xfn-tooltip-id', (string) $id );
			$processor->add_class( 'xfn-tooltip-anchor' );
			$processor->set_attribute( 'data-wp-on--mouseenter', 'actions.showTooltip' );
			$processor->set_attribute( 'data-wp-on--mouseleave', 'actions.hideTooltip' );
			$processor->set_attribute( 'data-wp-on--focus', 'actions.showTooltip' );
			$processor->set_attribute( 'data-wp-on--blur', 'actions.hideTooltip' );
			$processor->set_attribute( 'data-wp-on--keydown', 'actions.handleKeydown' );

			$xfn_links[ $id ] = $parsed['xfn'];
		}

		if ( empty( $xfn_links ) ) {
			return $block_content;
		}

		$this->enqueue_assets();

		$html = $processor->get_updated_html();

		// Pass 2: Wrap each marked link with its tooltip container.
		foreach ( $xfn_links as $id => $rels ) {
			$tooltip_html = self::build_tooltip_html( $rels );
			$context      = wp_json_encode( [ 'isOpen' => false ] );

			$html = preg_replace_callback(
				'/(<a\s[^>]*data-xfn-tooltip-id="' . $id . '"[^>]*>)(.*?)(<\/a>)/s',
				static function ( $m ) use ( $tooltip_html, $context ) {
					return '<span class="xfn-tooltip-wrap" data-wp-interactive="xfn-links" data-wp-context=\'' . $context . '\'>'
						. $m[1] . $m[2] . $m[3]
						. $tooltip_html
						. '</span>';
				},
				$html,
				1
			);
		}

		// Pass 3: Remove temporary marker attributes.
		$cleanup = new WP_HTML_Tag_Processor( $html );
		while ( $cleanup->next_tag( 'a' ) ) {
			if ( null !== $cleanup->get_attribute( 'data-xfn-tooltip-id' ) ) {
				$cleanup->remove_attribute( 'data-xfn-tooltip-id' );
			}
		}

		return $cleanup->get_updated_html();
	}

	/**
	 * Enqueue tooltip assets (once per request).
	 */
	private function enqueue_assets(): void {
		if ( $this->assets_enqueued ) {
			return;
		}

		wp_enqueue_script_module( 'xfn-links/tooltip' );
		wp_enqueue_style( 'xfn-tooltip' );

		$this->assets_enqueued = true;
	}

	/**
	 * Build the tooltip HTML for a set of XFN relationship values.
	 *
	 * @param string[] $rels Array of XFN relationship values.
	 * @return string Tooltip HTML fragment.
	 */
	private static function build_tooltip_html( array $rels ): string {
		$pills = '';
		foreach ( $rels as $rel ) {
			$pills .= '<span class="xfn-pill xfn-pill-' . esc_attr( $rel ) . '">'
				. esc_html( $rel )
				. '</span>';
		}

		return '<span class="xfn-tooltip" role="tooltip" data-wp-bind--hidden="!context.isOpen">'
			. '<span class="xfn-tooltip__arrow"></span>'
			. '<span class="xfn-tooltip__content xfn-pills">'
			. $pills
			. '</span>'
			. '</span>';
	}
}
