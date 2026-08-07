<?php
/**
 * Abilities registration integration coverage.
 *
 * @package LinkExtensionForXFN
 */

namespace LinkExtensionForXFN\Tests\Integration;

use WP_UnitTestCase;
use XFN_Abilities_Manager;

/**
 * Verifies every declared ability actually lands in the registry.
 *
 * Core rejects an ability name that doesn't match /^[a-z0-9-]+\/[a-z0-9-]+$/,
 * and separately rejects a name that is already registered. Both failures are
 * a _doing_it_wrong() notice and nothing else, so with WP_DEBUG off they are
 * invisible — which is how five `xfn/*_*` names sat unregistered until the
 * WP 7.1-RC1 audit. AbilitiesManagerTest checks the declared list; only this
 * checks that the declarations survived contact with the registry.
 */
class AbilitiesRegistrationTest extends WP_UnitTestCase {

	/**
	 * Core's ability name grammar, copied from WP_Abilities_Registry::register().
	 */
	private const NAME_PATTERN = '/^[a-z0-9-]+\/[a-z0-9-]+$/';

	/**
	 * Every declared name must satisfy core's grammar.
	 */
	public function test_declared_names_match_core_grammar(): void {
		foreach ( XFN_Abilities_Manager::get_ability_names() as $name ) {
			$this->assertMatchesRegularExpression(
				self::NAME_PATTERN,
				$name,
				sprintf( 'Ability "%s" would be rejected by WP_Abilities_Registry::register().', $name )
			);
		}
	}

	/**
	 * Declared names are unique.
	 *
	 * The meta-backed and content-backed sets share verbs, so a rename that
	 * drops the `-meta` qualifier would collide and lose one registration
	 * silently.
	 */
	public function test_declared_names_are_unique(): void {
		$names = XFN_Abilities_Manager::get_ability_names();

		$this->assertSame(
			$names,
			array_values( array_unique( $names ) ),
			'Duplicate ability names — the second registration would be rejected.'
		);
	}

	/**
	 * Every declared ability is present in the registry after init.
	 */
	public function test_declared_abilities_are_registered(): void {
		$this->skip_without_abilities_api();

		$registered = array_keys( wp_get_abilities() );
		$missing    = array_diff( XFN_Abilities_Manager::get_ability_names(), $registered );

		$this->assertSame(
			array(),
			array_values( $missing ),
			'Declared abilities are missing from the registry: ' . implode( ', ', $missing )
		);
	}

	/**
	 * Nothing registers under xfn/ without being declared.
	 *
	 * get_ability_names() feeds the WP Pinch MCP server list, so an ability
	 * that registers but isn't declared is invisible to MCP callers.
	 */
	public function test_registered_abilities_are_all_declared(): void {
		$this->skip_without_abilities_api();

		$registered = array_keys( wp_get_abilities( array( 'namespace' => 'xfn' ) ) );
		$undeclared = array_diff( $registered, XFN_Abilities_Manager::get_ability_names() );

		$this->assertSame(
			array(),
			array_values( $undeclared ),
			'Abilities registered but not declared in get_ability_names(): ' . implode( ', ', $undeclared )
		);
	}

	/**
	 * The declared set is nine — four meta-backed plus five content-backed.
	 */
	public function test_declared_ability_count(): void {
		$this->assertCount( 9, XFN_Abilities_Manager::get_ability_names() );
	}

	/**
	 * Skip when running against a WordPress without the Abilities API.
	 */
	private function skip_without_abilities_api(): void {
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->markTestSkipped( 'Abilities API not available on this WordPress version (needs 6.9+).' );
		}
	}
}
