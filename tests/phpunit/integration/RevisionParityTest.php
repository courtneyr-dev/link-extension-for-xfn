<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_REST_Request;
use WP_UnitTestCase;
use XFN_Content_Scanner;
use XFN_Meta_Mirror;

class RevisionParityTest extends WP_UnitTestCase {

	private const URL = 'https://example.com/xfn-revision';

	private const FRIEND = '<!-- wp:paragraph --><p><a href="https://example.com/xfn-revision" rel="nofollow friend">Fixture source</a></p><!-- /wp:paragraph -->';

	private const RENDERED_ONLY = array(
		'url'  => 'https://example.com/rendered-only',
		'rels' => array( 'met' ),
	);

	public function set_up(): void {
		parent::set_up();
		XFN_Meta_Mirror::register_meta();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function tear_down(): void {
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	/**
	 * @dataProvider pending_writes
	 */
	public function test_restoring_a_revision_rebuilds_the_mirror_from_restored_markup( ?array $pending ): void {
		$post_id = $this->publish_friend_then_mirror_colleague( $pending );

		wp_restore_post_revision( $this->friend_revision_id( $post_id ) );

		$this->assert_reads_agree_on_friend( $post_id );
	}

	/**
	 * @dataProvider pending_writes
	 */
	public function test_visual_revisions_restore_rebuilds_the_mirror_from_restored_markup( ?array $pending ): void {
		$post_id = $this->publish_friend_then_mirror_colleague( $pending );

		$request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$request->set_body_params( array( 'content' => self::FRIEND ) );
		$this->assertSame( 200, rest_get_server()->dispatch( $request )->get_status() );

		$this->assert_reads_agree_on_friend( $post_id );
	}

	public static function pending_writes(): array {
		return array(
			'no pending write'    => array( null ),
			'pending write'       => array( array( array( 'url' => self::URL, 'rels' => array( 'met' ) ) ) ),
			'empty pending write' => array( array() ),
		);
	}

	public function test_title_only_save_keeps_relationships_missing_from_markup(): void {
		$post_id = $this->publish( self::FRIEND );
		$mirror  = array( array( 'url' => self::URL, 'rels' => array( 'friend' ) ), self::RENDERED_ONLY );

		$request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
		$request->set_body_params( array( 'meta' => array( XFN_Meta_Mirror::META_KEY => $mirror ) ) );
		$this->assertSame( 200, rest_get_server()->dispatch( $request )->get_status() );
		wp_update_post( array( 'ID' => $post_id, 'post_title' => 'Title only' ) );

		$this->assertSame( $mirror, XFN_Meta_Mirror::get_relationships( $post_id ) );
	}

	public function test_content_change_drops_only_links_removed_from_markup(): void {
		$removed = array( 'url' => 'https://example.com/removed', 'rels' => array( 'colleague' ) );
		$friend  = array( 'url' => self::URL, 'rels' => array( 'friend' ) );
		$post_id = $this->publish( self::FRIEND . '<p><a href="https://example.com/removed" rel="colleague">Removed</a></p>' );
		update_post_meta( $post_id, XFN_Meta_Mirror::META_KEY, array( $friend, $removed, self::RENDERED_ONLY ) );

		wp_update_post( array( 'ID' => $post_id, 'post_content' => self::FRIEND ) );

		$this->assertSame( array( $friend, self::RENDERED_ONLY ), XFN_Meta_Mirror::get_relationships( $post_id ) );
	}

	/**
	 * @dataProvider markup_read_by_mirror_and_directory
	 */
	public function test_mirror_and_directory_read_the_same_links( string $markup, array $expected ): void {
		$post_id = $this->publish( self::FRIEND );
		update_post_meta( $post_id, XFN_Meta_Mirror::META_KEY, array() );

		wp_update_post( array( 'ID' => $post_id, 'post_content' => $markup ) );

		$directory = array_filter( XFN_Content_Scanner::scan_all_posts_for_xfn(), static fn( $entry ) => $post_id === $entry['post_id'] );
		$this->assertSame( $expected, XFN_Meta_Mirror::get_relationships( $post_id ), 'mirror' );
		$this->assertSame(
			$expected,
			array_values( array_map( static fn( $entry ) => array( 'url' => $entry['url'], 'rels' => $entry['rels'] ), $directory ) ),
			'directory'
		);
	}

	public static function markup_read_by_mirror_and_directory(): array {
		return array(
			'uppercase REL attribute' => array(
				'<p><a href="https://example.com/upper" REL="friend">Upper</a></p>',
				array( array( 'url' => 'https://example.com/upper', 'rels' => array( 'friend' ) ) ),
			),
			'root-relative href'      => array(
				'<p><a href="/about" rel="me">About</a></p>',
				array( array( 'url' => home_url( '/about' ), 'rels' => array( 'me' ) ) ),
			),
			'repeated URL'            => array(
				'<p><a href="https://example.com/twice" rel="friend">First</a> <a href="https://example.com/twice" rel="colleague">Second</a></p>',
				array( array( 'url' => 'https://example.com/twice', 'rels' => array( 'friend' ) ) ),
			),
		);
	}

	public function test_autosave_update_leaves_the_parent_mirror_alone(): void {
		add_filter(
			'wp_post_revision_meta_keys',
			static fn( array $keys ): array => array_merge( $keys, array( XFN_Meta_Mirror::META_KEY, XFN_Meta_Mirror::SOURCE_KEY ) )
		);
		$post_id = $this->publish( self::FRIEND );
		$pending = array( array( 'url' => self::URL, 'rels' => array( 'met' ) ) );
		XFN_Meta_Mirror::set_relationships( $post_id, $pending );

		$autosave_id = _wp_put_post_revision( get_post( $post_id ), true );
		wp_update_post( array( 'ID' => $autosave_id, 'post_content' => str_replace( 'nofollow friend', 'nofollow colleague', self::FRIEND ) ) );

		$this->assertSame( $pending, XFN_Meta_Mirror::get_relationships( $post_id ) );
		$this->assertSame( 'meta', get_post_meta( $post_id, XFN_Meta_Mirror::SOURCE_KEY, true ) );
	}

	private function publish( string $content ): int {
		return self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
	}

	private function publish_friend_then_mirror_colleague( ?array $pending ): int {
		$post_id = $this->publish( self::FRIEND );
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

		if ( null !== $pending ) {
			XFN_Meta_Mirror::set_relationships( $post_id, $pending );
		}

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
		$this->assertSame( '', get_post_meta( $post_id, XFN_Meta_Mirror::SOURCE_KEY, true ) );
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
