<?php
namespace LinkExtensionForXFN\Tests\Unit;

use WP_UnitTestCase;
use XFN_Core_Abilities;
use XFN_Meta_Mirror;

class XFNAbilitiesTest extends WP_UnitTestCase {

	private XFN_Core_Abilities $abilities;

	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->abilities = new XFN_Core_Abilities();
		\XFN_Test_AI_Client_Stub::reset();
	}

	public function test_execute_set_relationships(): void {
		$post_id = self::factory()->post->create( array(
			'post_content' => '<p><a href="https://alice.example.com">Alice</a></p>',
		) );

		$result = $this->abilities->execute_set_relationships( array(
			'post_id'       => $post_id,
			'relationships' => array(
				array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend', 'met' ) ),
			),
		) );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $result['applied'] );
	}

	public function test_execute_get_relationships(): void {
		$post_id = self::factory()->post->create();

		XFN_Meta_Mirror::set_relationships( $post_id, array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
			array( 'url' => 'https://bob.example.com', 'rels' => array( 'colleague' ) ),
		) );

		$result = $this->abilities->execute_get_relationships( array(
			'post_id' => $post_id,
		) );

		$this->assertCount( 2, $result['relationships'] );
	}

	public function test_execute_add_relationship(): void {
		$post_id = self::factory()->post->create();

		$result = $this->abilities->execute_add_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
			'rels'    => array( 'friend', 'met' ),
		) );

		$this->assertTrue( $result['success'] );

		$stored = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertCount( 1, $stored );
		$this->assertSame( 'https://alice.example.com', $stored[0]['url'] );
	}

	public function test_execute_remove_relationship(): void {
		$post_id = self::factory()->post->create();

		XFN_Meta_Mirror::set_relationships( $post_id, array(
			array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
		) );

		$result = $this->abilities->execute_remove_relationship( array(
			'post_id' => $post_id,
			'url'     => 'https://alice.example.com',
		) );

		$this->assertTrue( $result['success'] );

		$stored = XFN_Meta_Mirror::get_relationships( $post_id );
		$this->assertEmpty( $stored );
	}

	// Exclusivity validation lives on XFN_Content_Abilities now; its coverage is
	// in ContentAbilitiesTest. The meta-backed duplicate took the same `rels`
	// input and ran a strict subset of the same checks, so it was removed rather
	// than renamed alongside the other four.

	public function test_execute_set_relationships_nonexistent_post(): void {
		$result = $this->abilities->execute_set_relationships( array(
			'post_id'       => 999999,
			'relationships' => array(
				array( 'url' => 'https://alice.example.com', 'rels' => array( 'friend' ) ),
			),
		) );
		$this->assertFalse( $result['success'] );
		$this->assertArrayHasKey( 'error', $result );
	}

	public function test_execute_get_relationships_nonexistent_post(): void {
		$result = $this->abilities->execute_get_relationships( array(
			'post_id' => 999999,
		) );
		$this->assertEmpty( $result['relationships'] );
		$this->assertArrayHasKey( 'error', $result );
	}

	public function test_suggest_relationship_requires_edit_posts() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$ability = wp_get_ability( 'xfn/suggest-relationship' );
		$this->assertNotNull( $ability );
		$this->assertFalse( $ability->check_permissions( array( 'url' => 'https://example.test/' ) ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
		$this->assertTrue( $ability->check_permissions( array( 'url' => 'https://example.test/' ) ) );
	}

	public function test_suggest_relationship_is_rate_limited_per_user() {
		$user = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user );
		$ability = wp_get_ability( 'xfn/suggest-relationship' );

		for ( $i = 1; $i <= 20; $i++ ) {
			$result = $ability->execute( array( 'url' => 'https://example.test/' . $i ) );
			$this->assertIsArray( $result, "call {$i} of 20 should succeed" );
		}

		$result = $ability->execute( array( 'url' => 'https://example.test/21' ) );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'xfn_rate_limited', $result->get_error_code() );
		$error_data = $result->get_error_data();
		$this->assertSame( 429, $error_data['status'] );

		// A second user has their own counter and is unaffected by the first
		// user's cap.
		$other_user = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $other_user );
		$other_result = $ability->execute( array( 'url' => 'https://example.test/other' ) );
		$this->assertIsArray( $other_result, "a second user's calls should not be throttled by the first user's cap" );
	}

	public function test_suggest_relationship_uses_ai_source_when_client_returns_suggestions() {
		$user = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user );
		\XFN_Test_AI_Client_Stub::$response = wp_json_encode( array(
			array(
				'rel'        => 'friend',
				'confidence' => 0.9,
				'reason'     => 'stub',
			),
		) );

		$ability = wp_get_ability( 'xfn/suggest-relationship' );
		$result  = $ability->execute( array( 'url' => 'https://example.test/' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 'ai', $result['source'] );
		$this->assertSame( 'friend', $result['suggestions'][0]['rel'] );
	}

	public function test_suggest_relationship_quotes_url_and_context_in_prompt() {
		$user = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user );
		$ability = wp_get_ability( 'xfn/suggest-relationship' );

		$ability->execute( array(
			'url'     => 'https://example.test/page',
			'context' => "a quote \" here\r\nand a newline\nand another",
		) );

		$this->assertNotEmpty( \XFN_Test_AI_Client_Stub::$prompts );
		$prompt = \XFN_Test_AI_Client_Stub::$prompts[0];

		$this->assertStringNotContainsString( "\r", $prompt );
		$this->assertStringNotContainsString( "\n", $prompt );
		$this->assertMatchesRegularExpression(
			'/Given the URL "https:\/\/example\.test\/page" and context "[^"]*"/',
			$prompt
		);
	}

	public function test_suggest_relationship_context_truncates_by_character_not_byte() {
		$user = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user );
		$ability = wp_get_ability( 'xfn/suggest-relationship' );

		// 300 copies of a 3-byte character: 900 bytes but only 300
		// characters. A byte-based substr( $context, 0, 500 ) would cut this
		// well before the 300th character (mid-character, since 500 is not
		// a multiple of 3); mb_substr() must keep it whole, since it is
		// under the 500-character cap.
		$context = str_repeat( '日', 300 );

		$ability->execute( array(
			'url'     => 'https://example.test/multibyte',
			'context' => $context,
		) );

		$prompt = \XFN_Test_AI_Client_Stub::$prompts[0];
		$this->assertStringContainsString( $context, $prompt );
	}
}
