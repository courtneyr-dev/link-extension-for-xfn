<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_REST_Request;
use WP_UnitTestCase;
use XFN_Content_Scanner;
use XFN_Meta_Mirror;

class RevisionParityTest extends WP_UnitTestCase {

	private const URL = 'https://example.com/xfn-revision';

	private const FRIEND = '<!-- wp:paragraph --><p><a href="https://example.com/xfn-revision" rel="nofollow friend">Fixture source</a></p><!-- /wp:paragraph -->';

	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function tear_down(): void {
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	public function test_restoring_a_revision_rebuilds_the_mirror_from_restored_markup(): void {
		$post_id = $this->publish_friend_then_mirror_colleague();

		wp_restore_post_revision( $this->friend_revision_id( $post_id ) );

		$this->assert_reads_agree_on_friend( $post_id );
	}

	public function test_visual_revisions_restore_rebuilds_the_mirror_from_restored_markup(): void {
		$post_id = $this->publish_friend_then_mirror_colleague();

		$request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$request->set_body_params( array( 'content' => self::FRIEND ) );
		$this->assertSame( 200, rest_get_server()->dispatch( $request )->get_status() );

		$this->assert_reads_agree_on_friend( $post_id );
	}

	private function publish_friend_then_mirror_colleague(): int {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_content' => self::FRIEND,
			)
		);
		_wp_put_post_revision( $post_id );
		self::factory()->post->create(
			array(
				'post_status'  => 'draft',
				'post_content' => self::FRIEND,
			)
		);

		XFN_Meta_Mirror::set_relationships( $post_id, array( array( 'url' => self::URL, 'rels' => array( 'colleague' ) ) ) );
		wp_update_post( array( 'ID' => $post_id, 'post_content' => str_replace( 'nofollow friend', 'nofollow colleague', self::FRIEND ) ) );

		$this->assertSame( $this->agreeing( 'colleague' ), $this->reads( $post_id ) );

		return $post_id;
	}

	private function friend_revision_id( int $post_id ): int {
		foreach ( wp_get_post_revisions( $post_id ) as $revision ) {
			if ( self::FRIEND === $revision->post_content ) {
				return $revision->ID;
			}
		}

		$this->fail( 'The friend revision is missing.' );
	}

	private function assert_reads_agree_on_friend( int $post_id ): void {
		$this->assertSame( self::FRIEND, get_post( $post_id )->post_content );
		$this->assertStringContainsString( 'rel="nofollow friend"', apply_filters( 'the_content', get_post( $post_id )->post_content ) );
		$this->assertSame( $this->agreeing( 'friend' ), $this->reads( $post_id ) );
		$this->assertSame( array( $post_id ), array_values( array_unique( wp_list_pluck( XFN_Content_Scanner::scan_all_posts_for_xfn(), 'post_id' ) ) ) );
	}

	private function agreeing( string $rel ): array {
		return array(
			'markup'    => array( $rel ),
			'directory' => array( $rel ),
			'mirror'    => array( $rel ),
		);
	}

	private function reads( int $post_id ): array {
		$reads = array(
			'markup'    => XFN_Content_Scanner::extract_xfn_links_from_post( get_post( $post_id ) ),
			'directory' => array_filter( XFN_Content_Scanner::scan_all_posts_for_xfn(), static fn( $entry ) => $post_id === $entry['post_id'] ),
			'mirror'    => XFN_Meta_Mirror::get_relationships( $post_id ),
		);

		foreach ( $reads as $source => $read ) {
			$this->assertCount( 1, $read, $source );
			$entry = reset( $read );
			$this->assertSame( self::URL, $entry['url'], $source );
			$reads[ $source ] = $entry['rels'];
		}

		return $reads;
	}
}
