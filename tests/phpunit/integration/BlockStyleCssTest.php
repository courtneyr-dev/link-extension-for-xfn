<?php
namespace LinkExtensionForXFN\Tests\Integration;

use PHPUnit\Framework\TestCase;

class BlockStyleCssTest extends TestCase {

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
