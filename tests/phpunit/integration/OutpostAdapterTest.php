<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Meta_Mirror;
use XFN_Outpost_Adapter;

class OutpostAdapterTest extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		XFN_Outpost_Adapter::init();
	}

	private function outpost_payload( string $target, array $rels ): string {
		return (string) wp_json_encode( array( 'target' => $target, 'rels' => $rels ) );
	}

	public function test_outpost_meta_write_creates_mirror_relationship(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( 'https://alice.example.com', array( 'friend', 'met' ) ) );

		$relationships = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 1, $relationships );
		$this->assertSame( 'https://alice.example.com', $relationships[0]['url'] );
		$this->assertSame( array( 'friend', 'met' ), $relationships[0]['rels'] );
	}

	public function test_outpost_meta_write_injects_rel_into_content(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( 'https://alice.example.com', array( 'friend', 'met' ) ) );

		$content = get_post( $post_id )->post_content;
		$this->assertMatchesRegularExpression( '/<a[^>]+rel="[^"]*friend[^"]*"/', $content );
		$this->assertMatchesRegularExpression( '/<a[^>]+rel="[^"]*met[^"]*"/', $content );
	}

	public function test_invalid_rels_are_dropped_by_mirror_sanitizer(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://bob.example.com">Bob</a></p>',
		) );

		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( 'https://bob.example.com', array( 'nemesis', 'colleague' ) ) );

		$relationships = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 1, $relationships );
		$this->assertSame( array( 'colleague' ), $relationships[0]['rels'] );
	}

	public function test_missing_target_is_ignored(): void {
		$post_id = self::factory()->post->create();

		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( '', array( 'friend' ) ) );

		$this->assertSame( array(), XFN_Meta_Mirror::get_relationships( $post_id ) );
	}

	public function test_malformed_json_is_ignored(): void {
		$post_id = self::factory()->post->create();

		update_post_meta( $post_id, '_outpost_xfn', '{not json' );

		$this->assertSame( array(), XFN_Meta_Mirror::get_relationships( $post_id ) );
	}

	public function test_other_meta_keys_are_untouched(): void {
		$post_id = self::factory()->post->create();

		update_post_meta( $post_id, '_some_other_key', $this->outpost_payload( 'https://alice.example.com', array( 'friend' ) ) );

		$this->assertSame( array(), XFN_Meta_Mirror::get_relationships( $post_id ) );
	}

	public function test_repeat_write_merges_rels_idempotently(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( 'https://alice.example.com', array( 'friend' ) ) );
		update_post_meta( $post_id, '_outpost_xfn', $this->outpost_payload( 'https://alice.example.com', array( 'friend', 'met' ) ) );

		$relationships = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 1, $relationships );
		$this->assertSame( array( 'friend', 'met' ), $relationships[0]['rels'] );
	}
}
