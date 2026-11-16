<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Content_Scanner;
use XFN_Core_Abilities;
use XFN_Meta_Mirror;

class ScannerEligibilityTest extends WP_UnitTestCase {

	private const INELIGIBLE_CASES = array( 'private', 'draft', 'future', 'pending', 'password', 'trash' );

	private array $posts = array();

	public function set_up(): void {
		parent::set_up();
		$GLOBALS['wp_rest_server'] = null;
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
