<?php
/**
 * Orchestrates Abilities API registration for XFN.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

final class XFN_Abilities_Manager {

	public const CATEGORY_SLUG = 'xfn-relationships';

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! XFN_Feature_Flags::has_abilities_api() ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	private function __clone() {}

	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize singleton' );
	}

	public static function reset(): void {
		self::$instance = null;
	}

	public function register_category(): void {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY_SLUG,
			array(
				'label'       => __( 'XFN Relationships', 'link-extension-for-xfn' ),
				'description' => __( 'Manage XFN (XHTML Friends Network) link relationships.', 'link-extension-for-xfn' ),
			)
		);
	}

	public function register_abilities(): void {
		$abilities = new XFN_Core_Abilities();
		$abilities->register();
	}
}
