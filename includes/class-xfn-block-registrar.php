<?php
/**
 * Block registration for XFN plugin blocks.
 *
 * Auto-discovers and registers blocks from the build/blocks directory.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's blocks from the build directory.
 */
class XFN_Block_Registrar {

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Auto-discover and register blocks from the build/blocks directory.
	 *
	 * Each subdirectory containing a block.json file is registered as a block.
	 */
	public function register_blocks(): void {
		$blocks_dir = XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/blocks';

		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}

		$block_jsons = glob( $blocks_dir . '/*/block.json' );
		if ( empty( $block_jsons ) ) {
			return;
		}

		foreach ( $block_jsons as $block_json ) {
			register_block_type( dirname( $block_json ) );
		}
	}
}
