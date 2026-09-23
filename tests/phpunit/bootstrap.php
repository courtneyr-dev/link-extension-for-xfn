<?php
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( file_exists( dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/' );
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __DIR__, 2 ) . '/link-extension-for-xfn.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

if ( ! function_exists( 'wp_ai_client' ) ) {
	/**
	 * Stand-in for the AI Client WordPress core does not ship (core only
	 * has `wp_ai_client_prompt()`). Without this stub, the AI branch in
	 * XFN_Content_Abilities::execute_suggest_relationship() — and the
	 * throttle guarding it — is unreachable in tests.
	 *
	 * Defining this function makes `function_exists( 'wp_ai_client' )` true
	 * for the whole test run, not just the tests that care about it, so its
	 * stub client returns an empty response by default: `suggest_with_ai()`
	 * treats that as "no result" and falls back to heuristics, which is
	 * exactly what happens with no AI client configured. That keeps
	 * pre-existing heuristics-path tests (e.g. ContentAbilitiesTest) passing
	 * unchanged, while every call still records its prompt and still passes
	 * through the throttle guard immediately in front of it. A test that
	 * wants the AI branch to actually succeed can set
	 * XFN_Test_AI_Client_Stub::$response to a JSON payload first.
	 */
	function wp_ai_client() {
		return XFN_Test_AI_Client_Stub::instance();
	}
}

if ( ! class_exists( 'XFN_Test_AI_Client_Stub' ) ) {
	class XFN_Test_AI_Client_Stub {
		/** @var string[] Every prompt passed to generate_text(), in call order. */
		public static $prompts = array();

		/**
		 * The value generate_text() returns. Empty by default so the AI
		 * branch falls through to heuristics; set to a JSON string of
		 * suggestion objects to exercise the AI branch's success path.
		 *
		 * @var string
		 */
		public static $response = '';

		/** @var self|null */
		private static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/** Clears recorded prompts and the canned response; call from a test's set_up(). */
		public static function reset() {
			self::$prompts  = array();
			self::$response = '';
		}

		/**
		 * @param string $prompt The prompt sent by suggest_with_ai().
		 * @return string self::$response, recorded as a side effect.
		 */
		public function generate_text( $prompt ) {
			self::$prompts[] = $prompt;

			return self::$response;
		}
	}
}
