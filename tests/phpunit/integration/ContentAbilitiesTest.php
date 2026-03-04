<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Content_Abilities;
use XFN_Content_Scanner;

class ContentAbilitiesTest extends WP_UnitTestCase {

	private XFN_Content_Abilities $abilities;

	public function set_up(): void {
		parent::set_up();
		$this->abilities = new XFN_Content_Abilities();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
	}

	public function tear_down(): void {
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	// ── add-relationship ────────────────────────────────────────────

	public function test_add_relationship_modifies_post_content(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		$result = $this->abilities->execute_add_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'friend', 'met' ),
		) );

		$this->assertTrue( $result['success'] );
		$updated = get_post( $post_id )->post_content;
		$this->assertStringContainsString( 'friend', $updated );
		$this->assertStringContainsString( 'met', $updated );
	}

	public function test_add_relationship_rejects_invalid_rels(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		$result = $this->abilities->execute_add_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'evil-value', 'not-xfn' ),
		) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'No valid', $result['error'] );
	}

	public function test_add_relationship_fails_for_nonexistent_post(): void {
		$result = $this->abilities->execute_add_relationship( array(
			'post_id' => 999999,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'friend' ),
		) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Post not found', $result['error'] );
	}

	public function test_add_relationship_fails_for_missing_link(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://bob.example.com">Bob</a></p>',
		) );

		$result = $this->abilities->execute_add_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'friend' ),
		) );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'Link not found', $result['error'] );
	}

	public function test_add_relationship_invalidates_cache(): void {
		$post_id = self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		// Prime cache.
		XFN_Content_Scanner::scan_all_posts_for_xfn();

		$this->abilities->execute_add_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'friend' ),
		) );

		// Cache should be cleared, so fresh scan finds the new rel.
		$all = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertNotEmpty( $all );
		$this->assertContains( 'friend', $all[0]['rels'] );
	}

	// ── remove-relationship ─────────────────────────────────────────

	public function test_remove_relationship_removes_specific_rels(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="friend met colleague">Alice</a></p>',
		) );

		$result = $this->abilities->execute_remove_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'met' ),
		) );

		$this->assertTrue( $result['success'] );
		$updated = get_post( $post_id )->post_content;
		$this->assertStringContainsString( 'friend', $updated );
		$this->assertStringNotContainsString( 'met', $updated );
	}

	public function test_remove_relationship_removes_all_when_rels_empty(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="friend met">Alice</a></p>',
		) );

		$result = $this->abilities->execute_remove_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
		) );

		$this->assertTrue( $result['success'] );
	}

	// ── get-relationships ───────────────────────────────────────────

	public function test_get_relationships_for_specific_post(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>',
		) );

		$result = $this->abilities->execute_get_relationships( array(
			'post_id' => $post_id,
		) );

		$this->assertCount( 1, $result['relationships'] );
		$this->assertSame( 'https://alice.example.com', $result['relationships'][0]['url'] );
	}

	public function test_get_relationships_scans_all_when_no_post_id(): void {
		self::factory()->post->create( array(
			'post_status'  => 'publish',
			'post_content' => '<p><a href="https://alice.example.com" rel="friend">Alice</a></p>',
		) );

		$result = $this->abilities->execute_get_relationships( array() );

		$this->assertNotEmpty( $result['relationships'] );
	}

	public function test_get_relationships_fails_for_nonexistent_post(): void {
		$result = $this->abilities->execute_get_relationships( array(
			'post_id' => 999999,
		) );

		$this->assertEmpty( $result['relationships'] );
		$this->assertArrayHasKey( 'error', $result );
	}

	// ── validate-relationships ──────────────────────────────────────

	public function test_validate_passes_for_valid_rels(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'friend', 'met', 'colleague' ),
		) );

		$this->assertTrue( $result['valid'] );
		$this->assertEmpty( $result['errors'] );
	}

	public function test_validate_flags_invalid_values(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'friend', 'bogus-value' ),
		) );

		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['errors'] );
	}

	public function test_validate_flags_exclusivity_violations(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'friend', 'acquaintance' ),
		) );

		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['errors'] );
	}

	public function test_validate_flags_family_exclusivity(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'child', 'parent' ),
		) );

		$this->assertFalse( $result['valid'] );
	}

	// ── suggest-relationship ────────────────────────────────────────

	public function test_suggest_returns_heuristic_source(): void {
		$result = $this->abilities->execute_suggest_relationship( array(
			'url' => 'https://example.com/about',
		) );

		$this->assertSame( 'heuristics', $result['source'] );
		$this->assertIsArray( $result['suggestions'] );
	}

	public function test_suggest_detects_same_domain_as_me(): void {
		$result = $this->abilities->execute_suggest_relationship( array(
			'url' => home_url( '/about-me' ),
		) );

		$me_suggestions = array_filter( $result['suggestions'], function ( $s ) {
			return $s['rel'] === 'me';
		} );

		$this->assertNotEmpty( $me_suggestions );
	}

	public function test_suggest_uses_context_keywords(): void {
		$result = $this->abilities->execute_suggest_relationship( array(
			'url'     => 'https://example.com',
			'context' => 'My colleague and friend from work',
		) );

		$rels = array_column( $result['suggestions'], 'rel' );
		$this->assertContains( 'colleague', $rels );
		$this->assertContains( 'friend', $rels );
	}

	public function test_get_ability_names_returns_five(): void {
		$names = XFN_Content_Abilities::get_ability_names();

		$this->assertCount( 5, $names );
		$this->assertContains( 'xfn/add-relationship', $names );
		$this->assertContains( 'xfn/remove-relationship', $names );
		$this->assertContains( 'xfn/get-relationships', $names );
		$this->assertContains( 'xfn/validate-relationships', $names );
		$this->assertContains( 'xfn/suggest-relationship', $names );
	}
}
