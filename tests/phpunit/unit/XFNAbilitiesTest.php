<?php
namespace LinkExtensionForXFN\Tests\Unit;

use WP_UnitTestCase;
use XFN_Core_Abilities;
use XFN_Meta_Mirror;

class XFNAbilitiesTest extends WP_UnitTestCase {

	private XFN_Core_Abilities $abilities;

	public function set_up(): void {
		parent::set_up();
		$this->abilities = new XFN_Core_Abilities();
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

	public function test_execute_validate_relationships_valid(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'friend', 'met', 'colleague' ),
		) );

		$this->assertTrue( $result['valid'] );
		$this->assertEmpty( $result['warnings'] );
	}

	public function test_execute_validate_relationships_exclusive_violation(): void {
		$result = $this->abilities->execute_validate_relationships( array(
			'rels' => array( 'friend', 'acquaintance' ),
		) );

		$this->assertFalse( $result['valid'] );
		$this->assertNotEmpty( $result['warnings'] );
	}
}
