<?php
namespace LinkExtensionForXFN\Tests\Integration;

use PHPUnit\Framework\TestCase;

class BlockStyleCssTest extends TestCase {

	/**
	 * Acceptance 6: the block stylesheets carry the release version as ?ver=.
	 *
	 * @dataProvider block_style_handles
	 */
	public function test_block_style_handle_version_is_the_release_version( string $handle ): void {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$this->markTestSkipped( 'SCRIPT_DEBUG is true, so core versions block styles by filemtime.' );
		}

		$registered = wp_styles()->registered;

		$this->assertArrayHasKey( $handle, $registered );
		$this->assertSame( '1.1.3', $registered[ $handle ]->ver );
	}

	public function block_style_handles(): array {
		return array(
			'blogroll'  => array( 'xfn-blogroll-style' ),
			'badge'     => array( 'xfn-relationship-badge-style' ),
			'directory' => array( 'xfn-relationship-directory-style' ),
		);
	}

	/**
	 * @dataProvider built_block_metadata
	 */
	public function test_built_block_version_matches_the_plugin_version( string $path ): void {
		$metadata = json_decode( (string) file_get_contents( dirname( __DIR__, 3 ) . '/' . $path ), true );

		$this->assertSame( XFN_LINK_EXTENSION_VERSION, $metadata['version'] );
	}

	public function built_block_metadata(): array {
		return array(
			'blogroll'  => array( 'build/blocks/blogroll/block.json' ),
			'badge'     => array( 'build/blocks/relationship-badge/block.json' ),
			'directory' => array( 'build/blocks/relationship-directory/block.json' ),
		);
	}

	public function test_badge_wrapper_is_block_level_flex(): void {
		$css  = $this->read_css( 'build/blocks/relationship-badge/style-index.css' );
		$body = $this->get_rule_body( $css, '.xfn-relationship-badge' );

		$this->assertStringContainsString( 'display:flex', $body );
	}

	/**
	 * @dataProvider plain_style_files
	 */
	public function test_plain_style_selectors_are_scoped_to_their_block( string $path, string $wrapper ): void {
		$css     = $this->read_css( $path );
		$matches = array();
		preg_match_all( '/([^{}]+)\{[^{}]*\}/', $css, $matches );
		$found = 0;

		foreach ( $matches[1] as $selector_list ) {
			foreach ( explode( ',', $selector_list ) as $selector ) {
				$selector = trim( $selector );
				if ( false === strpos( $selector, 'is-style-plain' ) ) {
					continue;
				}

				++$found;
				$this->assertStringStartsWith( $wrapper, $selector, $selector );
			}
		}

		$this->assertGreaterThan( 0, $found, 'No is-style-plain selector found in ' . $path );
	}

	public function plain_style_files(): array {
		return array(
			'blogroll LTR' => array( 'build/blocks/blogroll/style-index.css', '.wp-block-xfn-blogroll' ),
			'blogroll RTL' => array( 'build/blocks/blogroll/style-index-rtl.css', '.wp-block-xfn-blogroll' ),
			'badge LTR'    => array( 'build/blocks/relationship-badge/style-index.css', '.wp-block-xfn-relationship-badge' ),
			'badge RTL'    => array( 'build/blocks/relationship-badge/style-index-rtl.css', '.wp-block-xfn-relationship-badge' ),
		);
	}

	public function test_tooltip_css_contains_no_block_style_selectors(): void {
		$css = $this->read_css( 'build/interactivity/tooltip.css' );

		$this->assertStringNotContainsString( 'is-style-plain', $css );
		$this->assertStringNotContainsString( 'is-style-pills', $css );
	}

	private function read_css( string $relative_path ): string {
		$path = dirname( __DIR__, 3 ) . '/' . $relative_path;

		return strtolower( preg_replace( '/\s+/', '', file_get_contents( $path ) ) );
	}

	private function get_rule_body( string $css, string $selector ): string {
		$matches = array();
		$this->assertSame(
			1,
			preg_match( '/(?:^|})' . preg_quote( $selector, '/' ) . '\{([^{}]*)\}/', $css, $matches ),
			'Selector not found: ' . $selector
		);

		return $matches[1];
	}
}
