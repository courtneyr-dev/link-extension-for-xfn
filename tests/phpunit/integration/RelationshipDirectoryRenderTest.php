<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_HTML_Tag_Processor;
use WP_UnitTestCase;

class RelationshipDirectoryRenderTest extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		$GLOBALS['wp_interactivity'] = null;

		foreach ( array( 'neighbor', 'colleague', 'friend' ) as $rel ) {
			self::factory()->post->create(
				array(
					'post_status'  => 'publish',
					'post_content' => sprintf( '<p><a href="https://example.com/%1$s" rel="nofollow %1$s">%1$s</a></p>', $rel ),
				)
			);
		}
	}

	public function tear_down(): void {
		$GLOBALS['wp_interactivity'] = null;
		parent::tear_down();
	}

	public function test_each_directory_carries_only_its_own_links(): void {
		$three = $this->directory( $this->render( 3 ) );
		$one   = $this->directory( $this->render( 1 ) );

		$this->assertCount( 3, $three['context']['links'] ?? array() );
		$this->assertCount( 1, $one['context']['links'] ?? array() );
		$this->assertCount( 3, $three['items'] );
		$this->assertCount( 1, $one['items'] );
		$this->assertArrayNotHasKey( 'links', wp_interactivity_state( 'xfn-directory' ) );
		$this->assertNotSame( $three['key'], $one['key'] );
	}

	public function test_items_and_filters_point_into_their_directory_context(): void {
		$directory = $this->directory( $this->render( 3 ) );
		$links     = $directory['context']['links'] ?? array();

		foreach ( $directory['items'] as $index => $item ) {
			$this->assertSame( array( 'linkIndex' => $index ), $item['context'] );
			$this->assertSame( 'state.isHidden', $item['hidden_binding'] );
			$this->assertNull( $item['hidden'], 'Links stay visible before scripts run.' );
			$this->assertSame( $links[ $index ]['url'], $item['href'] );
		}

		$rels = array_unique( array_merge( ...array_column( $links, 'rels' ) ) );
		sort( $rels );
		$this->assertSame( $rels, array_column( array_column( $directory['filters'], 'context' ), 'filterRel' ) );
		foreach ( $directory['filters'] as $filter ) {
			$this->assertSame( 'state.isActiveFilter', $filter['pressed_binding'] );
		}
	}

	public function test_context_parses_when_a_post_title_holds_an_encoded_quote(): void {
		// An administrator's title skips kses, and sites that turn
		// texturizing off serve its &quot; unchanged.
		kses_remove_filters();
		remove_filter( 'the_title', 'wptexturize' );
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'Say &quot;hi&quot;',
				'post_content' => '<p><a href="https://example.com/quoted" rel="friend">quoted</a></p>',
			)
		);

		$directory = $this->directory( $this->render( 50 ) );
		$titles    = array_column( $directory['context']['links'] ?? array(), 'postTitle', 'postId' );

		$this->assertSame( get_the_title( $post_id ), $titles[ $post_id ] ?? null );
	}

	private function render( int $limit ): string {
		return render_block(
			array(
				'blockName'    => 'xfn/relationship-directory',
				'attrs'        => array( 'limit' => $limit ),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	private function directory( string $html ): array {
		$tags = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $tags->next_tag( array( 'class_name' => 'xfn-directory' ) ) );
		$directory = array(
			'key'     => $tags->get_attribute( 'data-wp-key' ),
			'context' => json_decode( (string) $tags->get_attribute( 'data-wp-context' ), true ),
			'items'   => array(),
			'filters' => array(),
		);

		while ( $tags->next_tag() ) {
			if ( $tags->has_class( 'xfn-directory__filter-btn' ) && null !== $tags->get_attribute( 'data-wp-context' ) ) {
				$directory['filters'][] = array(
					'context'         => json_decode( $tags->get_attribute( 'data-wp-context' ), true ),
					'pressed_binding' => $tags->get_attribute( 'data-wp-bind--aria-pressed' ),
				);
			} elseif ( $tags->has_class( 'xfn-directory__item' ) ) {
				$item = array(
					'context'        => json_decode( (string) $tags->get_attribute( 'data-wp-context' ), true ),
					'hidden_binding' => $tags->get_attribute( 'data-wp-bind--hidden' ),
					'hidden'         => $tags->get_attribute( 'hidden' ),
				);
				$tags->next_tag( 'a' );
				$item['href']           = $tags->get_attribute( 'href' );
				$directory['items'][]   = $item;
			}
		}

		return $directory;
	}
}
