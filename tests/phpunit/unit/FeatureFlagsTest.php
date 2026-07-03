<?php
namespace LinkExtensionForXFN\Tests\Unit;

use WP_UnitTestCase;
use XFN_Feature_Flags;

class FeatureFlagsTest extends WP_UnitTestCase {

	public function tear_down(): void {
		delete_option( 'xfn_feature_flags' );
		remove_all_filters( 'xfn_feature_flag_abilities_api' );
		remove_all_filters( 'xfn_feature_flag_meta_mirror' );
		parent::tear_down();
	}

	public function test_defaults_return_true(): void {
		$this->assertTrue( XFN_Feature_Flags::is_enabled( 'abilities_api' ) );
		$this->assertTrue( XFN_Feature_Flags::is_enabled( 'meta_mirror' ) );
	}

	public function test_unknown_flag_returns_false(): void {
		$this->assertFalse( XFN_Feature_Flags::is_enabled( 'nonexistent_flag' ) );
	}

	public function test_option_overrides_default(): void {
		update_option( 'xfn_feature_flags', array( 'abilities_api' => false ) );
		$this->assertFalse( XFN_Feature_Flags::is_enabled( 'abilities_api' ) );
	}

	public function test_filter_overrides_option(): void {
		update_option( 'xfn_feature_flags', array( 'meta_mirror' => false ) );
		add_filter( 'xfn_feature_flag_meta_mirror', '__return_true' );
		$this->assertTrue( XFN_Feature_Flags::is_enabled( 'meta_mirror' ) );
	}

	public function test_has_abilities_api_requires_function(): void {
		if ( function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'wp_register_ability exists in this WordPress; the absent-function path cannot be exercised.' );
		}
		$this->assertFalse( XFN_Feature_Flags::has_abilities_api() );
	}

	public function test_has_meta_mirror_delegates_to_is_enabled(): void {
		$this->assertSame(
			XFN_Feature_Flags::is_enabled( 'meta_mirror' ),
			XFN_Feature_Flags::has_meta_mirror()
		);

		update_option( 'xfn_feature_flags', array( 'meta_mirror' => false ) );
		$this->assertSame(
			XFN_Feature_Flags::is_enabled( 'meta_mirror' ),
			XFN_Feature_Flags::has_meta_mirror()
		);
	}
}
