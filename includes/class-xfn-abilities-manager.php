<?php
/**
 * Orchestrates Abilities API registration for XFN.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */
final class XFN_Abilities_Manager {

	public const CATEGORY_SLUG = 'xfn-relationships';

	/**
	 * Read-only abilities exposed on the MCP Adapter's default server.
	 *
	 * @var string[]
	 */
	public const MCP_PUBLIC_ABILITIES = [
		'xfn/get-relationships',
		'xfn/validate-relationships',
		'xfn/get-meta-relationships',
	];

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook ability registration if the Abilities API is available.
	 */
	private function __construct() {
		if ( ! XFN_Feature_Flags::has_abilities_api() ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
		add_filter( 'wp_register_ability_args', [ self::class, 'filter_ability_args' ], 10, 2 );
	}

	/**
	 * Prevent cloning the singleton.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing the singleton.
	 */
	public function __wakeup() {
		throw new \RuntimeException( 'Cannot unserialize singleton' );
	}

	/**
	 * Reset the singleton (used by tests).
	 */
	public static function reset(): void {
		self::$instance = null;
	}

	/**
	 * Register the XFN ability category.
	 */
	public function register_category(): void {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => __( 'XFN Relationships', 'link-extension-for-xfn' ),
				'description' => __( 'Manage XFN (XHTML Friends Network) link relationships.', 'link-extension-for-xfn' ),
			]
		);
	}

	/**
	 * Register core and content abilities.
	 */
	public function register_abilities(): void {
		$abilities = new XFN_Core_Abilities();
		$abilities->register();

		$content_abilities = new XFN_Content_Abilities();
		$content_abilities->register();
	}

	/**
	 * Get all XFN ability names.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Ability name strings.
	 */
	public static function get_ability_names(): array {
		return array_merge(
			[
				'xfn/set-meta-relationships',
				'xfn/get-meta-relationships',
				'xfn/add-meta-relationship',
				'xfn/remove-meta-relationship',
			],
			XFN_Content_Abilities::get_ability_names()
		);
	}

	/**
	 * Set each XFN ability's exposure on the MCP Adapter's default server.
	 *
	 * Read abilities in MCP_PUBLIC_ABILITIES are public. Every other XFN
	 * ability gets an explicit false: the writes, and suggest-relationship,
	 * which can call an AI provider. An explicit value also stops MCP
	 * Adapter 0.6+ from falling back to a top-level meta.public flag.
	 * Permission callbacks still apply to every call.
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
			$args['meta'] = [];
		}
		if ( ! isset( $args['meta']['mcp'] ) || ! is_array( $args['meta']['mcp'] ) ) {
			$args['meta']['mcp'] = [];
		}
		$args['meta']['mcp']['public'] = in_array( $name, self::MCP_PUBLIC_ABILITIES, true );

		return $args;
	}
}
