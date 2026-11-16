<?php
namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Content_Scanner;
use XFN_Meta_Mirror;

class PrivacyEvalControlsTest extends WP_UnitTestCase {

	private const S4 = '/*N3:S4*/ static fn( array $p ): array => $p /*N3:/S4*/';
	private const S8 = "/*N3:S8*/ [ 'XFN_Content_Scanner', 'ability_can_read' ] /*N3:/S8*/";
	private const S9 = "/*N3:S9*/ [ 'XFN_Meta_Mirror', 'redact_rest' ] /*N3:/S9*/";

	private string $fixture;

	public function set_up(): void {
		parent::set_up();
		$this->fixture = dirname( __DIR__ ) . '/fixtures/privacy/w8-n3-xfn-eligibility.php';
		XFN_Meta_Mirror::register_meta();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_content' => '<a href="https://n3-control-eligible.example/" rel="friend">x</a>',
			)
		);
		self::factory()->post->create(
			array(
				'post_status'  => 'private',
				'post_content' => '<a href="https://n3-control-ineligible.example/" rel="friend">x</a>',
			)
		);

		delete_transient( 'xfn_rels_all' );
		$GLOBALS['wp_rest_server'] = null;
	}

	public function tear_down(): void {
		$GLOBALS['wp_rest_server'] = null;
		XFN_Content_Scanner::invalidate_cache();
		parent::tear_down();
	}

	/**
	 * @dataProvider privacy_controls
	 */
	public function test_privacy_eval_negative_controls( string $control, string $expected ): void {
		$text = file_get_contents( $this->fixture );
		$this->assertIsString( $text );
		$this->assertSame( 1, substr_count( $text, self::S4 ) );
		$this->assertSame( 1, substr_count( $text, self::S8 ) );
		$this->assertSame( 1, substr_count( $text, self::S9 ) );

		if ( 'A' === $control ) {
			$text = $this->replace_once(
				$text,
				self::S4,
				"/*N3:S4*/ [ 'XFN_Content_Scanner', 'filter_eligible' ] /*N3:/S4*/"
			);
		} elseif ( 'B' === $control ) {
			$text = $this->replace_once(
				$text,
				self::S8,
				"/*N3:S8*/ static fn( \\WP_Post \$p ): bool => current_user_can( 'manage_options' ) || XFN_Content_Scanner::is_eligible( \$p ) /*N3:/S8*/"
			);
		} elseif ( 'C' === $control ) {
			$text = $this->replace_once(
				$text,
				self::S9,
				'/*N3:S9*/ static fn( $r ) => $r /*N3:/S9*/'
			);
		} elseif ( 'D' === $control ) {
			rest_get_server();
			remove_filter( 'rest_prepare_post', array( XFN_Meta_Mirror::class, 'redact_rest' ), 20 );
		}

		ob_start();
		try {
			eval( $text );
			$output = ob_get_clean();
		} catch ( \Throwable $throwable ) {
			ob_end_clean();
			throw $throwable;
		} finally {
			$GLOBALS['wp_rest_server'] = null;
		}

		$lines = preg_split( '/\R/', trim( $output ) );
		$last_line = end( $lines );
		$this->assertSame( $expected, $last_line );
		if ( 'none' === $control ) {
			$this->assertStringNotContainsString( 'write_attempted', $output );
		}
	}

	public static function privacy_controls(): array {
		return array(
			'none' => array( 'none', 'RESULT PASS' ),
			'A'    => array( 'A', 'RESULT FAIL checker_blind' ),
			'B'    => array( 'B', 'RESULT FAIL ability_gate_leak:private,draft,future,pending,password,trash' ),
			'C'    => array( 'C', 'RESULT FAIL rest_leak:private,draft,future,pending,password,trash' ),
			'D'    => array( 'D', 'RESULT FAIL rest_hook_missing:post' ),
		);
	}

	private function replace_once( string $text, string $search, string $replacement ): string {
		$patched = str_replace( $search, $replacement, $text );
		$this->assertNotSame( $text, $patched );
		$this->assertSame( 0, substr_count( $patched, $search ) );

		return $patched;
	}
}
