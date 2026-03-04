<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Block_Registrar;
use WP_Block_Type_Registry;

class BlockRegistrarTest extends WP_UnitTestCase {

	private XFN_Block_Registrar $registrar;

	public function set_up(): void {
		parent::set_up();
		$this->registrar = new XFN_Block_Registrar();
	}

	public function tear_down(): void {
		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( array( 'xfn/relationship-badge', 'xfn/relationship-directory', 'xfn/blogroll' ) as $name ) {
			if ( $registry->is_registered( $name ) ) {
				$registry->unregister( $name );
			}
		}
		parent::tear_down();
	}

	public function test_init_registers_hook(): void {
		$this->registrar->init();

		$this->assertSame( 10, has_action( 'init', array( $this->registrar, 'register_blocks' ) ) );
	}

	public function test_register_blocks_handles_missing_build_dir(): void {
		// Should not throw when build/ doesn't exist.
		$this->registrar->register_blocks();
		$this->assertTrue( true ); // No exception thrown.
	}

	public function test_register_blocks_registers_blocks_from_build_dir(): void {
		$build_dir = XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/blocks';
		if ( ! is_dir( $build_dir ) ) {
			$this->markTestSkipped( 'Build directory not present. Run npm run build first.' );
		}

		$this->registrar->register_blocks();

		$registry = WP_Block_Type_Registry::get_instance();

		// Check that at least one XFN block is registered.
		$block_jsons = glob( $build_dir . '/*/block.json' );
		if ( ! empty( $block_jsons ) ) {
			$meta = json_decode( file_get_contents( $block_jsons[0] ), true );
			$this->assertTrue(
				$registry->is_registered( $meta['name'] ),
				sprintf( 'Block %s should be registered.', $meta['name'] )
			);
		}
	}
}
