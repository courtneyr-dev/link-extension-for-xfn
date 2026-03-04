<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Interactivity;

class InteractivityTest extends WP_UnitTestCase {

	private XFN_Interactivity $interactivity;

	public function set_up(): void {
		parent::set_up();
		$this->interactivity = new XFN_Interactivity();
	}

	public function test_process_block_adds_tooltip_to_xfn_link(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend met">Alice</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'xfn-tooltip-wrap', $result );
		$this->assertStringContainsString( 'data-wp-interactive="xfn-links"', $result );
		$this->assertStringContainsString( 'xfn-tooltip', $result );
		$this->assertStringContainsString( 'role="tooltip"', $result );
	}

	public function test_process_block_adds_interactivity_directives(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'data-wp-on--mouseenter', $result );
		$this->assertStringContainsString( 'data-wp-on--mouseleave', $result );
		$this->assertStringContainsString( 'data-wp-on--focus', $result );
		$this->assertStringContainsString( 'data-wp-on--blur', $result );
		$this->assertStringContainsString( 'data-wp-on--keydown', $result );
	}

	public function test_process_block_generates_pills_for_each_rel(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend met colleague">Alice</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'xfn-pill-friend', $result );
		$this->assertStringContainsString( 'xfn-pill-met', $result );
		$this->assertStringContainsString( 'xfn-pill-colleague', $result );
	}

	public function test_process_block_skips_non_xfn_links(): void {
		$content = '<p><a href="https://example.com" rel="nofollow noopener">Link</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringNotContainsString( 'xfn-tooltip', $result );
		$this->assertSame( $content, $result );
	}

	public function test_process_block_skips_empty_content(): void {
		$result = $this->interactivity->process_block( '', array() );

		$this->assertSame( '', $result );
	}

	public function test_process_block_skips_content_without_rel(): void {
		$content = '<p><a href="https://example.com">Plain link</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertSame( $content, $result );
	}

	public function test_process_block_handles_multiple_xfn_links(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>'
			. '<p><a href="https://bob.example.com" rel="colleague">Bob</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'xfn-pill-friend', $result );
		$this->assertStringContainsString( 'xfn-pill-colleague', $result );
		$this->assertSame( 2, substr_count( $result, 'xfn-tooltip-wrap' ) );
	}

	public function test_process_block_removes_marker_attributes(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringNotContainsString( 'data-xfn-tooltip-id', $result );
	}

	public function test_process_block_preserves_existing_link_content(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend">Alice <strong>Smith</strong></a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'Alice <strong>Smith</strong>', $result );
	}

	public function test_process_block_adds_tooltip_anchor_class(): void {
		$content = '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>';

		$result = $this->interactivity->process_block( $content, array() );

		$this->assertStringContainsString( 'xfn-tooltip-anchor', $result );
	}

	public function test_init_registers_hooks(): void {
		$this->interactivity->init();

		$this->assertSame( 10, has_action( 'wp_enqueue_scripts', array( $this->interactivity, 'register_assets' ) ) );
		$this->assertSame( 10, has_filter( 'render_block', array( $this->interactivity, 'process_block' ) ) );
	}
}
