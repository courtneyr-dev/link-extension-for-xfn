<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Content_Scanner;
use XFN_Core_Abilities;
use XFN_Meta_Mirror;

class ScannerEligibilityTest extends WP_UnitTestCase {

	private const INELIGIBLE_CASES = array( 'private', 'draft', 'future', 'pending', 'password', 'trash' );
	private const REST_POST_TYPE   = 'n3_xfn_test';
	private const REST_BASE        = 'n3-xfn-tests';

	private array $posts = array();

	public function set_up(): void {
		parent::set_up();
		$GLOBALS['wp_rest_server'] = null;
		register_post_type(
			self::REST_POST_TYPE,
			array(
				'public'       => true,
				'show_in_rest' => true,
				'rest_base'    => self::REST_BASE,
				'supports'     => array( 'title', 'editor', 'custom-fields' ),
			)
		);
		register_post_type(
			'n3_non_public',
			array(
				'public'             => false,
				'publicly_queryable' => false,
			)
		);
		XFN_Meta_Mirror::register_meta();

		$cases = array(
			'publish'  => array( 'post_status' => 'publish' ),
			'private'  => array( 'post_status' => 'private' ),
			'draft'    => array( 'post_status' => 'draft' ),
			'future'   => array(
				'post_status' => 'future',
				'post_date'   => '2030-01-01 12:00:00',
			),
			'pending'  => array( 'post_status' => 'pending' ),
			'password' => array(
				'post_status'   => 'publish',
				'post_password' => 'sentinel',
			),
			'trash'    => array( 'post_status' => 'trash' ),
		);

		foreach ( $cases as $case => $post_args ) {
			$url                    = $this->content_url( $case );
			$post_args['post_type'] = 'post';
			$post_args['post_content'] = sprintf(
				'<a href="%s" rel="friend met">x</a>',
				$url
			);
			$this->posts[ $case ]      = self::factory()->post->create_and_get( $post_args );
			update_post_meta(
				$this->posts[ $case ]->ID,
				'_xfn_relationships',
				array(
					array(
						'url'  => $this->meta_url( $case ),
						'rels' => array( 'friend' ),
					),
				)
			);
		}

		XFN_Content_Scanner::invalidate_cache();
		wp_set_current_user( 0 );
	}

	public function tear_down(): void {
		$GLOBALS['wp_rest_server'] = null;
		XFN_Content_Scanner::invalidate_cache();
		unregister_post_type( self::REST_POST_TYPE );
		unregister_post_type( 'n3_non_public' );
		parent::tear_down();
	}

	public function test_scanner_blocks_and_global_ability_exclude_ineligible_posts(): void {
		$scan = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertSurfaceEligibility( wp_json_encode( $scan ), 'scanner' );

		foreach ( array( 'relationship', 'domain' ) as $group_by ) {
			$this->assertSurfaceEligibility(
				$this->render_block( 'xfn/blogroll', array( 'groupBy' => $group_by ) ),
				'blogroll ' . $group_by
			);
		}

		foreach ( $this->posts as $case => $post ) {
			$badge = $this->render_block(
				'xfn/relationship-badge',
				array( 'url' => $this->content_url( $case ) )
			);
			if ( 'publish' === $case ) {
				$this->assertStringContainsString( 'xfn-relationship-badge__link', $badge );
				$this->assertStringContainsString( $this->content_url( $case ), $badge );
			} else {
				$this->assertStringNotContainsString( 'xfn-relationship-badge__link', $badge, $case );
				$this->assertStringContainsString( 'No XFN relationships found for', $badge, $case );
			}
		}

		$this->assertSurfaceEligibility(
			$this->render_block( 'xfn/relationship-directory', array() ),
			'directory'
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$ability = wp_get_ability( 'xfn/get-relationships' );
		$this->assertNotNull( $ability );
		$result = $ability->execute( array() );
		$this->assertSurfaceEligibility( wp_json_encode( $result ), 'global ability' );
	}

	public function test_status_change_invalidates_the_scanner_cache(): void {
		$first = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringContainsString( $this->content_host( 'publish' ), wp_json_encode( $first ) );

		wp_update_post(
			array(
				'ID'          => $this->posts['publish']->ID,
				'post_status' => 'private',
			)
		);

		$second = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringNotContainsString( $this->content_host( 'publish' ), wp_json_encode( $second ) );
	}

	public function test_cache_hit_drops_a_post_changed_to_a_non_public_post_type(): void {
		$first = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringContainsString( $this->content_host( 'publish' ), wp_json_encode( $first ) );

		$this->assertSame( 1, set_post_type( $this->posts['publish']->ID, 'n3_non_public' ) );

		$second = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringNotContainsString( $this->content_host( 'publish' ), wp_json_encode( $second ) );
		$this->assertStringNotContainsString(
			$this->content_host( 'publish' ),
			$this->render_block( 'xfn/blogroll', array() )
		);
	}

	public function test_cache_hit_drops_a_post_made_private_without_hooks_and_deletes_the_transient(): void {
		global $wpdb;

		$first = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringContainsString( $this->content_host( 'publish' ), wp_json_encode( $first ) );

		$updated = $wpdb->update(
			$wpdb->posts,
			array( 'post_status' => 'private' ),
			array( 'ID' => $this->posts['publish']->ID ),
			array( '%s' ),
			array( '%d' )
		);
		$this->assertSame( 1, $updated );
		clean_post_cache( $this->posts['publish']->ID );

		$second = XFN_Content_Scanner::scan_all_posts_for_xfn();
		$this->assertStringNotContainsString( $this->content_host( 'publish' ), wp_json_encode( $second ) );
		$this->assertFalse( get_transient( 'xfn_rels_all' ) );
	}

	public function test_scanner_calls_the_eligibility_seam_for_non_viewable_post_types(): void {
		$page = self::factory()->post->create_and_get(
			array(
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<a href="https://n3-sentinel-nonviewable.example/" rel="friend met">x</a>',
			)
		);
		$this->assertInstanceOf( \WP_Post::class, $page );

		$not_viewable = static function ( bool $is_viewable, $post_type ): bool {
			return 'page' === $post_type->name ? false : $is_viewable;
		};
		add_filter( 'is_post_type_viewable', $not_viewable, 10, 2 );
		try {
			$scan = XFN_Content_Scanner::scan_all_posts_for_xfn();
		} finally {
			remove_filter( 'is_post_type_viewable', $not_viewable, 10 );
		}

		$encoded = wp_json_encode( $scan );
		$this->assertStringNotContainsString( 'n3-sentinel-nonviewable.example', $encoded );
		$this->assertStringContainsString( $this->content_host( 'publish' ), $encoded );
	}

	public function test_rest_redacts_password_meta_but_keeps_editor_access(): void {
		wp_set_current_user( 0 );

		$item_response = rest_do_request( '/wp/v2/posts/' . $this->posts['password']->ID );
		$item_data     = $item_response->get_data();
		$this->assertSame( array(), $item_data['meta']['_xfn_relationships'] );
		$this->assertStringNotContainsString( 'n3-meta-password.example', wp_json_encode( $item_data ) );

		$collection_response = rest_do_request( '/wp/v2/posts' );
		$collection_data     = $collection_response->get_data();
		$this->assertStringNotContainsString( 'n3-meta-password.example', wp_json_encode( $collection_data ) );
		$this->assertStringContainsString( 'n3-meta-publish.example', wp_json_encode( $collection_data ) );

		$embed_response = rest_do_request( '/wp/v2/posts?_embed=1' );
		$embed_data     = rest_get_server()->response_to_data( $embed_response, true );
		$this->assertStringNotContainsString( 'n3-meta-password.example', wp_json_encode( $embed_data ) );

		$editor_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor_id );
		$edit_request = new \WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->posts['password']->ID );
		$edit_request->set_param( 'context', 'edit' );
		$edit_response = rest_do_request( $edit_request );
		$this->assertStringContainsString( 'n3-meta-password.example', wp_json_encode( $edit_response->get_data() ) );
	}

	public function test_rest_redacts_password_meta_for_pages_and_custom_post_types(): void {
		$page_id    = self::factory()->post->create(
			array(
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'post_password' => 'sentinel',
			)
		);
		$cpt_id     = self::factory()->post->create(
			array(
				'post_status'   => 'publish',
				'post_type'     => self::REST_POST_TYPE,
				'post_password' => 'sentinel',
			)
		);
		$control_id = self::factory()->post->create(
			array(
				'post_status' => 'publish',
				'post_type'   => self::REST_POST_TYPE,
			)
		);

		$this->set_relationship_meta( $page_id, 'https://n3-meta-page.example/' );
		$this->set_relationship_meta( $cpt_id, 'https://n3-meta-cpt.example/' );
		$this->set_relationship_meta( $control_id, 'https://n3-meta-cpt-control.example/' );
		wp_set_current_user( 0 );

		$this->assert_rest_host_redacted( '/wp/v2/pages', $page_id, 'n3-meta-page.example' );
		$this->assert_rest_host_redacted(
			'/wp/v2/' . self::REST_BASE,
			$cpt_id,
			'n3-meta-cpt.example',
			'n3-meta-cpt-control.example'
		);

		$control_request = new \WP_REST_Request( 'GET', '/wp/v2/' . self::REST_BASE . '/' . $control_id );
		$control_request->set_param( '_fields', 'meta' );
		$control_response = rest_do_request( $control_request );
		$this->assertSame( 200, $control_response->get_status() );
		$this->assertStringContainsString( 'n3-meta-cpt-control.example', wp_json_encode( $control_response->get_data() ) );
	}

	/**
	 * @dataProvider readable_roles
	 */
	public function test_per_post_abilities_apply_the_public_gate_for_every_role( string $role ): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => $role ) ) );
		$core_abilities    = new XFN_Core_Abilities();
		$content_ability   = wp_get_ability( 'xfn/get-relationships' );
		$expected_refusal  = array(
			'relationships' => array(),
			'error'         => 'Insufficient permissions for this post.',
		);

		$this->assertNotNull( $content_ability );
		foreach ( self::INELIGIBLE_CASES as $case ) {
			$input = array( 'post_id' => $this->posts[ $case ]->ID );
			$this->assertSame( $expected_refusal, $core_abilities->execute_get_relationships( $input ), $role . ' meta ' . $case );
			$this->assertSame( $expected_refusal, $content_ability->execute( $input ), $role . ' content ' . $case );
		}

		$input          = array( 'post_id' => $this->posts['publish']->ID );
		$meta_result    = $core_abilities->execute_get_relationships( $input );
		$content_result = $content_ability->execute( $input );
		$this->assertStringContainsString( 'n3-meta-publish.example', wp_json_encode( $meta_result ), $role . ' meta control' );
		$this->assertStringContainsString( 'n3-sentinel-publish.example', wp_json_encode( $content_result ), $role . ' content control' );
	}

	public static function readable_roles(): array {
		return array(
			'subscriber'    => array( 'subscriber' ),
			'administrator' => array( 'administrator' ),
		);
	}

	private function assertSurfaceEligibility( string $output, string $surface ): void {
		$this->assertStringContainsString( $this->content_host( 'publish' ), $output, $surface . ' control' );
		foreach ( self::INELIGIBLE_CASES as $case ) {
			$this->assertStringNotContainsString( $this->content_host( $case ), $output, $surface . ' ' . $case );
		}
	}

	private function content_host( string $case ): string {
		return 'n3-sentinel-' . $case . '.example';
	}

	private function content_url( string $case ): string {
		return 'https://n3-sentinel-' . $case . '.example/';
	}

	private function meta_url( string $case ): string {
		return 'https://n3-meta-' . $case . '.example/';
	}

	private function set_relationship_meta( int $post_id, string $url ): void {
		update_post_meta(
			$post_id,
			'_xfn_relationships',
			array(
				array(
					'url'  => $url,
					'rels' => array( 'friend' ),
				),
			)
		);
	}

	private function assert_rest_host_redacted( string $route, int $post_id, string $redacted_host, ?string $control_host = null ): void {
		foreach ( array( false, true ) as $meta_only ) {
			$suffix       = $meta_only ? ' with _fields=meta' : '';
			$item_request = new \WP_REST_Request( 'GET', $route . '/' . $post_id );
			if ( $meta_only ) {
				$item_request->set_param( '_fields', 'meta' );
			}
			$item_response = rest_do_request( $item_request );
			$this->assertSame( 200, $item_response->get_status(), $route . ' item' . $suffix );
			$this->assertStringNotContainsString( $redacted_host, wp_json_encode( $item_response->get_data() ), $route . ' item' . $suffix );

			$collection_request = new \WP_REST_Request( 'GET', $route );
			if ( $meta_only ) {
				$collection_request->set_param( '_fields', 'meta' );
			}
			$collection_response = rest_do_request( $collection_request );
			$this->assertSame( 200, $collection_response->get_status(), $route . ' collection' . $suffix );
			$collection_data = $collection_response->get_data();
			$encoded         = wp_json_encode( $collection_data );
			$this->assertStringNotContainsString( $redacted_host, $encoded, $route . ' collection' . $suffix );
			if ( null !== $control_host ) {
				$this->assertStringContainsString( $control_host, $encoded, $route . ' control' . $suffix );
			}
		}
	}

	private function render_block( string $name, array $attributes ): string {
		return render_block(
			array(
				'blockName'    => $name,
				'attrs'        => $attributes,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}
}
