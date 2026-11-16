<?php
namespace LinkExtensionForXFN\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PillCustomPropertiesTest extends TestCase {

	private string $css;

	protected function setUp(): void {
		$path      = dirname( __DIR__, 3 ) . '/build/blocks/blogroll/style-index.css';
		$this->css = strtolower( preg_replace( '/\s+/', '', file_get_contents( $path ) ) );
	}

	public function test_base_pill_rule_uses_custom_properties_and_keeps_inline_flex(): void {
		$body = $this->get_rule_body( '.xfn-pill' );

		$this->assertStringContainsString( 'background:var(--xfn-pill-bg,#f0f0f0)', $body );
		$this->assertStringContainsString( 'border:1pxsolidvar(--xfn-pill-border,#ddd)', $body );
		$this->assertStringContainsString( 'color:var(--xfn-pill-color,#1e1e1e)', $body );
		$this->assertStringContainsString( 'display:inline-flex', $body );
	}

	public function test_each_relationship_class_sets_its_family_custom_properties(): void {
		$families = array(
			array( array( 'friend', 'acquaintance', 'contact' ), '#edfaef', '#00a32a', '#007017' ),
			array( array( 'met' ), '#f5f3f9', '#826eb4', '#50457b' ),
			array( array( 'co-worker', 'colleague' ), '#fcf0f1', '#cc1818', '#cc1818' ),
			array( array( 'co-resident', 'neighbor' ), '#fcf8e8', '#dba617', '#8a6d05' ),
			array( array( 'child', 'parent', 'sibling', 'spouse', 'kin' ), '#fff4ec', '#ff8c00', '#9a3f00' ),
			array( array( 'muse', 'crush', 'date', 'sweetheart' ), '#fce8f1', '#e91e63', '#be0f4c' ),
			array( array( 'me' ), '#f0f0f0', '#1e1e1e', '#1e1e1e' ),
		);

		foreach ( $families as $family ) {
			$relationships = $family[0];
			$background    = $family[1];
			$border        = $family[2];
			$color         = $family[3];

			foreach ( $relationships as $relationship ) {
				$body = $this->get_rule_body_containing( '.xfn-pill-' . $relationship );

				$this->assertStringContainsString( '--xfn-pill-bg:' . $background, $body, $relationship );
				$this->assertStringContainsString( '--xfn-pill-border:' . $border, $body, $relationship );
				$this->assertStringContainsString( '--xfn-pill-color:' . $color, $body, $relationship );
			}
		}
	}

	private function get_rule_body( string $selector ): string {
		$matches = array();
		$this->assertSame(
			1,
			preg_match( '/(?:^|})' . preg_quote( $selector, '/' ) . '\{([^{}]*)\}/', $this->css, $matches ),
			'Selector not found: ' . $selector
		);

		return $matches[1];
	}

	private function get_rule_body_containing( string $selector ): string {
		$matches = array();
		preg_match_all( '/([^{}]+)\{([^{}]*)\}/', $this->css, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			if ( in_array( $selector, array_map( 'trim', explode( ',', $match[1] ) ), true ) ) {
				return $match[2];
			}
		}

		$this->fail( 'Selector not found: ' . $selector );
	}
}
