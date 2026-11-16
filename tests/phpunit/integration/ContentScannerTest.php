<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Content_Scanner;

class ContentScannerTest extends WP_UnitTestCase {

	public function tear_down(): void {
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	public function test_extract_xfn_links_from_post_finds_xfn_rels(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="friend met">Alice</a></p>',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertCount( 1, $links );
		$this->assertSame( $post->ID, $links[0]['post_id'] );
		$this->assertSame( 'https://alice.example.com', $links[0]['url'] );
		$this->assertContains( 'friend', $links[0]['rels'] );
		$this->assertContains( 'met', $links[0]['rels'] );
	}

	public function test_extract_xfn_links_ignores_non_xfn_rels(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '<p><a href="https://example.com" rel="nofollow noopener">Link</a></p>',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertEmpty( $links );
	}

	public function test_extract_xfn_links_ignores_links_without_rel(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '<p><a href="https://example.com">No rel</a></p>',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertEmpty( $links );
	}

	public function test_extract_xfn_links_handles_mixed_rels(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="nofollow friend met noopener">Alice</a></p>',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertCount( 1, $links );
		$this->assertContains( 'friend', $links[0]['rels'] );
		$this->assertContains( 'met', $links[0]['rels'] );
		$this->assertNotContains( 'nofollow', $links[0]['rels'] );
	}

	public function test_extract_xfn_links_finds_multiple_links(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>'
				. '<p><a href="https://bob.example.com" rel="colleague met">Bob</a></p>',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertCount( 2, $links );
		$this->assertSame( 'https://alice.example.com', $links[0]['url'] );
		$this->assertSame( 'https://bob.example.com', $links[1]['url'] );
	}

	public function test_extract_xfn_links_handles_empty_content(): void {
		$post = self::factory()->post->create_and_get( array(
			'post_content' => '',
		) );

		$links = XFN_Content_Scanner::extract_xfn_links_from_post( $post );

		$this->assertEmpty( $links );
	}

	public function test_scan_all_posts_finds_published_xfn_posts(): void {
		self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>',
		) );
		self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p>No links here.</p>',
		) );

		$all = XFN_Content_Scanner::scan_all_posts_for_xfn();

		$this->assertCount( 1, $all );
		$this->assertSame( 'https://alice.example.com', $all[0]['url'] );
	}

	public function test_scan_all_posts_skips_drafts(): void {
		self::factory()->post->create( array(
			'post_status'  => 'draft',
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>',
		) );

		$all = XFN_Content_Scanner::scan_all_posts_for_xfn();

		$this->assertEmpty( $all );
	}

	public function test_scan_all_posts_uses_transient_cache(): void {
		self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>',
		) );

		// First call populates cache.
		$first = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertCount( 1, $first );

		// Isolate transient reuse from the lifecycle invalidation hook.
		foreach ( array( 'save_post', 'transition_post_status', 'trashed_post', 'untrashed_post', 'deleted_post' ) as $hook ) {
			remove_action( $hook, array( XFN_Content_Scanner::class, 'invalidate_cache' ) );
		}
		self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p><a href="https://bob.example.com" rel="colleague">Bob</a></p>',
		) );
		XFN_Content_Scanner::register_hooks();

		$cached = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertCount( 1, $cached, 'Should return cached result.' );

		// After invalidation, should find both.
		XFN_Content_Scanner::invalidate_cache();
		$fresh = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertCount( 2, $fresh );
	}

	public function test_filter_eligible_applies_the_public_eligibility_truth_table(): void {
		$this->assertTrue( method_exists( XFN_Content_Scanner::class, 'filter_eligible' ) );

		register_post_type(
			'n3_non_public',
			array(
				'public'             => false,
				'publicly_queryable' => false,
			)
		);

		$posts = array(
			self::factory()->post->create_and_get( array( 'post_status' => 'publish' ) ),
			self::factory()->post->create_and_get( array( 'post_status' => 'private' ) ),
			self::factory()->post->create_and_get( array( 'post_status' => 'draft' ) ),
			self::factory()->post->create_and_get(
				array(
					'post_status' => 'future',
					'post_date'   => '2030-01-01 12:00:00',
				)
			),
			self::factory()->post->create_and_get( array( 'post_status' => 'pending' ) ),
			self::factory()->post->create_and_get( array( 'post_status' => 'trash' ) ),
			self::factory()->post->create_and_get(
				array(
					'post_status'   => 'publish',
					'post_password' => 'sentinel',
				)
			),
			self::factory()->post->create_and_get(
				array(
					'post_status' => 'publish',
					'post_type'   => 'n3_non_public',
				)
			),
		);

		$eligible = XFN_Content_Scanner::filter_eligible( $posts );

		$this->assertSame( array( $posts[0] ), $eligible );
		unregister_post_type( 'n3_non_public' );
	}

	public function test_register_hooks_registers_all_cache_invalidation_hooks(): void {
		$this->assertTrue( method_exists( XFN_Content_Scanner::class, 'register_hooks' ) );

		XFN_Content_Scanner::register_hooks();

		foreach ( array( 'save_post', 'transition_post_status', 'trashed_post', 'untrashed_post', 'deleted_post' ) as $hook ) {
			$this->assertNotFalse(
				has_action( $hook, array( XFN_Content_Scanner::class, 'invalidate_cache' ) ),
				$hook
			);
		}
	}

	public function test_valid_xfn_constant_has_18_values(): void {
		$this->assertCount( 18, XFN_Content_Scanner::VALID_XFN );
	}

	public function test_exclusivity_groups_has_3_groups(): void {
		$this->assertCount( 3, XFN_Content_Scanner::EXCLUSIVITY_GROUPS );
		$this->assertArrayHasKey( 'friendship', XFN_Content_Scanner::EXCLUSIVITY_GROUPS );
		$this->assertArrayHasKey( 'geographical', XFN_Content_Scanner::EXCLUSIVITY_GROUPS );
		$this->assertArrayHasKey( 'family', XFN_Content_Scanner::EXCLUSIVITY_GROUPS );
	}
}
