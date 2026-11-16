<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_Block_Styles_Registry;
use WP_Block_Type_Registry;
use WP_UnitTestCase;
use XFN_Block_Registrar;
use XFN_Content_Scanner;

class BlockStylesTest extends WP_UnitTestCase {
	private array $interactivity_callbacks = array();

	public function set_up(): void {
		parent::set_up();
		$this->register_blocks_if_needed();
		$this->remove_interactivity_render_filters();

		self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_content' => '<p><a href="https://amara.example/" rel="friend met">Amara</a></p>',
			)
		);
		self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_content' => '<p><a href="https://kit.example/" rel="colleague">Kit</a></p>',
			)
		);

		XFN_Content_Scanner::invalidate_cache();
	}

	public function tear_down(): void {
		foreach ( $this->interactivity_callbacks as $callback ) {
			add_filter( 'render_block', $callback['function'], 10, $callback['accepted_args'] );
		}
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	public function test_block_metadata_declares_only_the_owned_styles(): void {
		$expected = array(
			array(
				'name'      => 'pills',
				'label'     => 'Pills',
				'isDefault' => true,
			),
			array(
				'name'  => 'plain',
				'label' => 'Plain labels',
			),
		);
		$registry = WP_Block_Type_Registry::get_instance();

		$this->assertSame( $expected, $registry->get_registered( 'xfn/blogroll' )->styles );
		$this->assertSame( $expected, $registry->get_registered( 'xfn/relationship-badge' )->styles );
		$this->assertSame( array(), $registry->get_registered( 'xfn/relationship-directory' )->styles );
	}

	public function test_no_xfn_styles_use_the_global_block_style_registry(): void {
		$registry = WP_Block_Styles_Registry::get_instance();

		foreach ( array( 'xfn/blogroll', 'xfn/relationship-badge', 'xfn/relationship-directory' ) as $block_name ) {
			$this->assertSame( array(), $registry->get_registered_styles_for_block( $block_name ) );
		}
	}

	public function test_blogroll_plain_style_preserves_elements_and_attribute_controls(): void {
		$attributes = array(
			'groupBy'          => 'domain',
			'limit'            => 50,
			'showRelationships' => true,
		);
		$default    = $this->render( 'xfn/blogroll', $attributes );

		XFN_Content_Scanner::invalidate_cache();
		$plain = $this->render( 'xfn/blogroll', array_merge( $attributes, array( 'className' => 'is-style-plain' ) ) );

		$this->assertGreaterThan( 0, $this->count_anchors( $default ) );
		$this->assertSame( $this->count_anchors( $default ), $this->count_anchors( $plain ) );
		$this->assertGreaterThan( 0, $this->count_pills( $default ) );
		$this->assertSame( $this->count_pills( $default ), $this->count_pills( $plain ) );
		$this->assertMatchesRegularExpression( '/class="[^"]*\bis-style-plain\b[^"]*"/', $plain );

		XFN_Content_Scanner::invalidate_cache();
		$without_relationships = $this->render(
			'xfn/blogroll',
			array_merge( $attributes, array( 'showRelationships' => false ) )
		);
		$this->assertSame( 0, $this->count_pills( $without_relationships ), $without_relationships );
	}

	public function test_badge_plain_style_preserves_elements_and_attribute_controls(): void {
		$attributes = array(
			'url'     => 'https://amara.example/',
			'showUrl' => true,
		);
		$default    = $this->render( 'xfn/relationship-badge', $attributes );

		XFN_Content_Scanner::invalidate_cache();
		$plain = $this->render(
			'xfn/relationship-badge',
			array_merge( $attributes, array( 'className' => 'is-style-plain' ) )
		);

		$this->assertGreaterThan( 0, $this->count_anchors( $default ) );
		$this->assertSame( $this->count_anchors( $default ), $this->count_anchors( $plain ) );
		$this->assertGreaterThan( 0, $this->count_pills( $default ) );
		$this->assertSame( $this->count_pills( $default ), $this->count_pills( $plain ) );
		$this->assertMatchesRegularExpression( '/class="[^"]*\bis-style-plain\b[^"]*"/', $plain );

		XFN_Content_Scanner::invalidate_cache();
		$without_url = $this->render(
			'xfn/relationship-badge',
			array_merge( $attributes, array( 'showUrl' => false ) )
		);
		$this->assertStringNotContainsString( 'xfn-relationship-badge__link', $without_url );
	}

	private function render( string $block_name, array $attributes ): string {
		return render_block(
			array(
				'blockName'    => $block_name,
				'attrs'        => $attributes,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	private function count_anchors( string $html ): int {
		return preg_match_all( '/<a\b/', $html );
	}

	private function count_pills( string $html ): int {
		return substr_count( $html, 'class="xfn-pill ' );
	}

	private function register_blocks_if_needed(): void {
		$registry    = WP_Block_Type_Registry::get_instance();
		$block_names = array( 'xfn/blogroll', 'xfn/relationship-badge', 'xfn/relationship-directory' );

		foreach ( $block_names as $block_name ) {
			if ( ! $registry->is_registered( $block_name ) ) {
				foreach ( $block_names as $registered_name ) {
					if ( $registry->is_registered( $registered_name ) ) {
						$registry->unregister( $registered_name );
					}
				}
				( new XFN_Block_Registrar() )->register_blocks();
				return;
			}
		}
	}

	private function remove_interactivity_render_filters(): void {
		global $wp_filter;

		if ( empty( $wp_filter['render_block']->callbacks[10] ) ) {
			return;
		}

		foreach ( $wp_filter['render_block']->callbacks[10] as $callback ) {
			$function = $callback['function'];
			if ( ! is_array( $function ) || ! $function[0] instanceof \XFN_Interactivity || 'process_block' !== $function[1] ) {
				continue;
			}

			$this->interactivity_callbacks[] = $callback;
			remove_filter( 'render_block', $function, 10 );
		}
	}
}
