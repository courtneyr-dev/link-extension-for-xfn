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

		if ( class_exists( 'WP_Pinch\\Abilities' ) ) {
			self::register_mcp_hooks();
		}
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

	/**
	 * Get all XFN ability names.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Ability name strings.
	 */
	public static function get_ability_names(): array {
		return array(
			'xfn/set_relationships',
			'xfn/get_relationships',
			'xfn/add_relationship',
			'xfn/remove_relationship',
			'xfn/validate_relationships',
		);
	}

	/**
	 * Register WP Pinch MCP server integration hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_mcp_hooks(): void {
		add_filter( 'wp_pinch_mcp_server_abilities', array( self::class, 'filter_mcp_server_abilities' ) );
		add_filter( 'wp_register_ability_args', array( self::class, 'filter_ability_args' ), 10, 2 );
	}

	/**
	 * Append XFN abilities to the MCP server abilities list.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $abilities Existing ability name strings.
	 * @return string[] Modified ability name strings.
	 */
	public static function filter_mcp_server_abilities( array $abilities ): array {
		return array_merge( $abilities, self::get_ability_names() );
	}

	/**
	 * Add MCP public meta to XFN abilities.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args Ability registration arguments.
	 * @param string $name Ability name.
	 * @return array Modified arguments.
	 */
	public static function filter_ability_args( array $args, string $name ): array {
		if ( ! str_starts_with( $name, 'xfn/' ) ) {
			return $args;
		}

		if ( ! isset( $args['meta'] ) ) {
			$args['meta'] = array();
		}
		if ( ! isset( $args['meta']['mcp'] ) ) {
			$args['meta']['mcp'] = array();
		}
		$args['meta']['mcp']['public'] = true;

		return $args;
	}
}
