<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Abilities_Manager;

class AbilitiesManagerTest extends WP_UnitTestCase {

	public function tear_down(): void {
		XFN_Abilities_Manager::reset();
		remove_all_filters( 'xfn_feature_flag_abilities_api' );
		remove_all_filters( 'wp_register_ability_args' );
		$this->reset_abilities_registry();
		parent::tear_down();
	}

	/**
	 * Discard the WP_Abilities_Registry singleton so the next abilities call
	 * rebuilds it lazily from whatever wp_abilities_api_init listeners exist.
	 *
	 * WP_UnitTestCase restores $wp_filter between tests, so resetting here
	 * leaves no registry or hook state behind for later tests.
	 */
	private function reset_abilities_registry(): void {
		if ( ! class_exists( 'WP_Abilities_Registry' ) ) {
			return;
		}

		$property = new \ReflectionProperty( \WP_Abilities_Registry::class, 'instance' );
		$property->setValue( null, null );
	}

	/**
	 * Skip when running against a WordPress without the Abilities API.
	 */
	private function skip_without_abilities_api(): void {
		if ( ! function_exists( 'wp_has_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available on this WordPress version (needs 6.9+).' );
		}
	}

	public function test_singleton_returns_same_instance(): void {
		$a = XFN_Abilities_Manager::instance();
		$b = XFN_Abilities_Manager::instance();

		$this->assertSame( $a, $b );
	}

	public function test_reset_clears_singleton(): void {
		$first = XFN_Abilities_Manager::instance();
		XFN_Abilities_Manager::reset();
		$second = XFN_Abilities_Manager::instance();

		$this->assertNotSame( $first, $second );
	}

	public function test_category_slug_constant(): void {
		$this->assertSame( 'xfn-relationships', XFN_Abilities_Manager::CATEGORY_SLUG );
	}

	public function test_get_ability_names_returns_nine(): void {
		$names = XFN_Abilities_Manager::get_ability_names();

		$this->assertCount( 9, $names );
		$this->assertContains( 'xfn/set-meta-relationships', $names );
		$this->assertContains( 'xfn/get-meta-relationships', $names );
		$this->assertContains( 'xfn/add-relationship', $names );
		$this->assertContains( 'xfn/suggest-relationship', $names );
	}

	/**
	 * The MCP exposure filter is hooked on every site, not only when WP Pinch
	 * is installed, and nothing hooks the old WP Pinch server-list filter.
	 */
	public function test_ability_args_filter_hooked_without_wp_pinch(): void {
		$this->skip_without_abilities_api();
		$this->assertFalse( class_exists( 'WP_Pinch\\Abilities' ) );

		remove_all_filters( 'wp_register_ability_args' );
		XFN_Abilities_Manager::reset();
		XFN_Abilities_Manager::instance();

		$this->assertSame( 10, has_filter( 'wp_register_ability_args', array( XFN_Abilities_Manager::class, 'filter_ability_args' ) ) );
		$this->assertFalse( has_filter( 'wp_pinch_mcp_server_abilities' ) );
	}

	/**
	 * Read-only abilities are public on the MCP Adapter's default server.
	 *
	 * @dataProvider read_ability_names
	 */
	public function test_filter_ability_args_makes_read_abilities_public( string $name ): void {
		$result = XFN_Abilities_Manager::filter_ability_args( array( 'label' => 'Test' ), $name );

		$this->assertTrue( $result['meta']['mcp']['public'] );
	}

	public static function read_ability_names(): array {
		return array(
			'get-relationships'      => array( 'xfn/get-relationships' ),
			'validate-relationships' => array( 'xfn/validate-relationships' ),
			'get-meta-relationships' => array( 'xfn/get-meta-relationships' ),
		);
	}

	/**
	 * Writes, and suggest-relationship (it can call an AI provider), stay off
	 * the default server. The value is an explicit false so MCP Adapter 0.6+
	 * can't fall back to a top-level meta.public flag.
	 *
	 * @dataProvider private_ability_names
	 */
	public function test_filter_ability_args_keeps_writes_and_suggest_private( string $name ): void {
		$result = XFN_Abilities_Manager::filter_ability_args( array( 'label' => 'Test' ), $name );

		$this->assertFalse( $result['meta']['mcp']['public'] );
	}

	public static function private_ability_names(): array {
		return array(
			'add-relationship'         => array( 'xfn/add-relationship' ),
			'remove-relationship'      => array( 'xfn/remove-relationship' ),
			'suggest-relationship'     => array( 'xfn/suggest-relationship' ),
			'set-meta-relationships'   => array( 'xfn/set-meta-relationships' ),
			'add-meta-relationship'    => array( 'xfn/add-meta-relationship' ),
			'remove-meta-relationship' => array( 'xfn/remove-meta-relationship' ),
		);
	}

	/**
	 * An explicit public flag on the ability is overridden, so a write can't be
	 * opened up by registration args alone.
	 */
	public function test_filter_ability_args_overrides_public_flag_on_writes(): void {
		$args = array(
			'label' => 'Test',
			'meta'  => array( 'mcp' => array( 'public' => true, 'type' => 'tool' ) ),
		);

		$result = XFN_Abilities_Manager::filter_ability_args( $args, 'xfn/add-relationship' );

		$this->assertFalse( $result['meta']['mcp']['public'] );
		$this->assertSame( 'tool', $result['meta']['mcp']['type'] );
	}

	/**
	 * End to end: the registered abilities carry the exposure the filter set.
	 */
	public function test_registered_abilities_carry_mcp_exposure(): void {
		$this->skip_without_abilities_api();

		// The plugin's own instance hooked the filter at load; rebuild only the registry.
		$this->reset_abilities_registry();
		wp_get_abilities();

		$public = array();
		foreach ( XFN_Abilities_Manager::get_ability_names() as $name ) {
			$ability = wp_get_ability( $name );
			$this->assertNotNull( $ability, sprintf( 'Ability "%s" should be registered.', $name ) );
			if ( true === ( $ability->get_meta()['mcp']['public'] ?? null ) ) {
				$public[] = $name;
			}
		}
		sort( $public );

		$this->assertSame(
			array( 'xfn/get-meta-relationships', 'xfn/get-relationships', 'xfn/validate-relationships' ),
			$public
		);
	}

	public function test_filter_ability_args_skips_non_xfn(): void {
		$args = array( 'label' => 'Test' );

		$result = XFN_Abilities_Manager::filter_ability_args( $args, 'other/ability' );

		$this->assertArrayNotHasKey( 'meta', $result );
	}

	public function test_filter_ability_args_preserves_existing_meta(): void {
		$args = array(
			'label' => 'Test',
			'meta'  => array( 'version' => '1.0.0' ),
		);

		$result = XFN_Abilities_Manager::filter_ability_args( $args, 'xfn/get-relationships' );

		$this->assertSame( '1.0.0', $result['meta']['version'] );
		$this->assertTrue( $result['meta']['mcp']['public'] );
	}
}
