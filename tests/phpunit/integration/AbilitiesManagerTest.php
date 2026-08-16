<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Abilities_Manager;

class AbilitiesManagerTest extends WP_UnitTestCase {

	public function tear_down(): void {
		XFN_Abilities_Manager::reset();
		remove_all_filters( 'xfn_feature_flag_abilities_api' );
		remove_all_filters( 'wp_pinch_mcp_server_abilities' );
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

	public function test_filter_mcp_server_abilities_appends_xfn(): void {
		$existing = array( 'other/ability' );

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( $existing );

		$this->assertContains( 'other/ability', $result );
		$this->assertCount( 10, $result ); // 1 existing + 9 XFN.
	}

	/**
	 * Regression for issue #19: the filter advertised every declared name
	 * whether or not it was in the abilities registry, and the mcp-adapter
	 * logs one "ability does not exist" error per advertised-but-missing
	 * name when it builds its tool list (on staging, the names had failed
	 * registration outright). With no registered XFN abilities, the filter
	 * must return the incoming list untouched.
	 */
	public function test_filter_advertises_nothing_when_no_xfn_abilities_registered(): void {
		$this->skip_without_abilities_api();

		$this->reset_abilities_registry();
		remove_all_actions( 'wp_abilities_api_init' );

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( array() );

		$this->assertSame( array(), $result );
	}

	/**
	 * Existing non-XFN entries pass through unchanged when the XFN abilities
	 * never registered.
	 */
	public function test_filter_preserves_existing_entries_when_no_xfn_abilities_registered(): void {
		$this->skip_without_abilities_api();

		$this->reset_abilities_registry();
		remove_all_actions( 'wp_abilities_api_init' );

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( array( 'other/ability' ) );

		$this->assertSame( array( 'other/ability' ), $result );
	}

	/**
	 * Once the registry initializes and the plugin's wp_abilities_api_init
	 * listener registers the abilities, the filter advertises all of them.
	 */
	public function test_filter_advertises_registered_abilities(): void {
		$this->skip_without_abilities_api();

		$this->reset_abilities_registry();

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( array( 'other/ability' ) );

		$this->assertContains( 'other/ability', $result );
		foreach ( XFN_Abilities_Manager::get_ability_names() as $name ) {
			$this->assertContains( $name, $result );
			$this->assertTrue( wp_has_ability( $name ), sprintf( 'Ability "%s" should be registered.', $name ) );
		}
	}

	/**
	 * When only part of the declared set is registered, the filter advertises
	 * exactly that registered subset.
	 */
	public function test_filter_advertises_only_registered_subset(): void {
		$this->skip_without_abilities_api();

		$this->reset_abilities_registry();
		wp_get_abilities(); // Force lazy registry init so the XFN abilities register.

		$missing = array( 'xfn/set-meta-relationships', 'xfn/suggest-relationship' );
		foreach ( $missing as $name ) {
			wp_unregister_ability( $name );
		}

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( array() );

		$expected = array_values( array_diff( XFN_Abilities_Manager::get_ability_names(), $missing ) );
		$this->assertSame( $expected, $result );
	}

	public function test_filter_ability_args_adds_mcp_public_to_xfn(): void {
		$args = array( 'label' => 'Test' );

		$result = XFN_Abilities_Manager::filter_ability_args( $args, 'xfn/add-relationship' );

		$this->assertTrue( $result['meta']['mcp']['public'] );
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
