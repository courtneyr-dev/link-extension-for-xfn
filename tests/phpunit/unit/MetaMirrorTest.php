<?php
namespace LinkExtensionForXFN\Tests\Unit;

use WP_UnitTestCase;
use XFN_Meta_Mirror;

class MetaMirrorTest extends WP_UnitTestCase {

	public function tear_down(): void {
		parent::tear_down();
	}

	public function test_sanitize_relationships_valid(): void {
		$input = array(
			array( 'url' => 'https://example.com', 'rels' => array( 'friend', 'met' ) ),
		);
		$result = XFN_Meta_Mirror::sanitize_relationships( $input );
		$this->assertCount( 1, $result );
		$this->assertSame( 'https://example.com', $result[0]['url'] );
		$this->assertContains( 'friend', $result[0]['rels'] );
	}

	public function test_sanitize_relationships_strips_invalid_rels(): void {
		$input = array(
			array( 'url' => 'https://example.com', 'rels' => array( 'friend', 'evil-value', 'met' ) ),
		);
		$result = XFN_Meta_Mirror::sanitize_relationships( $input );
		$this->assertCount( 2, $result[0]['rels'] );
		$this->assertNotContains( 'evil-value', $result[0]['rels'] );
	}

	public function test_sanitize_relationships_strips_invalid_urls(): void {
		$input = array(
			array( 'url' => 'not-a-url', 'rels' => array( 'friend' ) ),
		);
		$result = XFN_Meta_Mirror::sanitize_relationships( $input );
		$this->assertCount( 0, $result );
	}

	public function test_apply_to_content_adds_rel(): void {
		$content = '<p><a href="https://alice.example.com">Alice</a></p>';
		$rels    = array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend', 'met' ) ),
		);
		$result = XFN_Meta_Mirror::apply_to_content( $content, $rels );
		$this->assertStringContainsString( 'rel="friend met"', $result );
	}

	public function test_apply_to_content_preserves_existing_non_xfn_rels(): void {
		$content = '<p><a href="https://alice.example.com" rel="nofollow noopener">Alice</a></p>';
		$rels    = array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
		);
		$result = XFN_Meta_Mirror::apply_to_content( $content, $rels );
		$this->assertStringContainsString( 'nofollow', $result );
		$this->assertStringContainsString( 'noopener', $result );
		$this->assertStringContainsString( 'friend', $result );
	}

	public function test_apply_to_content_skips_unmatched_urls(): void {
		$content = '<p><a href="https://bob.example.com">Bob</a></p>';
		$rels    = array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
		);
		$result = XFN_Meta_Mirror::apply_to_content( $content, $rels );
		$this->assertStringNotContainsString( 'friend', $result );
	}

	public function test_get_and_set_relationships(): void {
		$post_id = self::factory()->post->create();
		$rels    = array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend', 'met' ) ),
		);

		XFN_Meta_Mirror::set_relationships( $post_id, $rels );
		$result = XFN_Meta_Mirror::get_relationships( $post_id );

		$this->assertCount( 1, $result );
		$this->assertSame( 'https://alice.example.com', $result[0]['url'] );
	}

	public function test_add_relationship(): void {
		$post_id = self::factory()->post->create();
		XFN_Meta_Mirror::set_relationships( $post_id, array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
		) );

		XFN_Meta_Mirror::add_relationship( $post_id, 'https://bob.example.com', array( 'colleague' ) );

		$result = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 2, $result );
	}

	public function test_remove_relationship(): void {
		$post_id = self::factory()->post->create();
		XFN_Meta_Mirror::set_relationships( $post_id, array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
			array( 'url' => 'https://bob.example.com', 'rels' => array( 'colleague' ) ),
		) );

		XFN_Meta_Mirror::remove_relationship( $post_id, 'https://alice.example.com' );

		$result = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 1, $result );
		$this->assertSame( 'https://bob.example.com', $result[0]['url'] );
	}
}
