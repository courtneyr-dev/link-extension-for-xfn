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
		parent::tear_down();
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

	public function test_get_ability_names_returns_ten(): void {
		$names = XFN_Abilities_Manager::get_ability_names();

		$this->assertCount( 10, $names );
		$this->assertContains( 'xfn/set_relationships', $names );
		$this->assertContains( 'xfn/get_relationships', $names );
		$this->assertContains( 'xfn/add-relationship', $names );
		$this->assertContains( 'xfn/suggest-relationship', $names );
	}

	public function test_filter_mcp_server_abilities_appends_xfn(): void {
		$existing = array( 'other/ability' );

		$result = XFN_Abilities_Manager::filter_mcp_server_abilities( $existing );

		$this->assertContains( 'other/ability', $result );
		$this->assertCount( 11, $result ); // 1 existing + 10 XFN.
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
