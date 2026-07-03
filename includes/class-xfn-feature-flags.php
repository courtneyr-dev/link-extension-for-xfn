<?php
/**
 * Feature flags for XFN Link Extension.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */
final class XFN_Feature_Flags {

	/**
	 * Default state for each feature flag.
	 *
	 * @var array<string, bool>
	 */
	private static array $defaults = [
		'abilities_api' => true,
		'meta_mirror'   => true,
		'interactivity' => true,
		'blocks'        => true,
	];

	/**
	 * Whether a feature flag is enabled.
	 *
	 * Checks the xfn_feature_flag_{$flag} filter first, then the
	 * xfn_feature_flags option, then the built-in default.
	 *
	 * @param string $flag Flag name.
	 * @return bool
	 */
	public static function is_enabled( string $flag ): bool {
		if ( ! isset( self::$defaults[ $flag ] ) ) {
			return false;
		}

		$filtered = apply_filters( "xfn_feature_flag_{$flag}", null );
		if ( null !== $filtered ) {
			return (bool) $filtered;
		}

		$options = get_option( 'xfn_feature_flags', [] );
		if ( isset( $options[ $flag ] ) ) {
			return (bool) $options[ $flag ];
		}

		return self::$defaults[ $flag ];
	}

	/**
	 * Whether the Abilities API integration is available and enabled.
	 *
	 * @return bool
	 */
	public static function has_abilities_api(): bool {
		return self::is_enabled( 'abilities_api' ) && function_exists( 'wp_register_ability' );
	}

	/**
	 * Whether the post-meta mirror is enabled.
	 *
	 * @return bool
	 */
	public static function has_meta_mirror(): bool {
		return self::is_enabled( 'meta_mirror' );
	}

	/**
	 * Whether Interactivity API features are enabled and supported.
	 *
	 * @return bool
	 */
	public static function has_interactivity(): bool {
		return self::is_enabled( 'interactivity' )
			&& version_compare( get_bloginfo( 'version' ), '7.0', '>=' );
	}

	/**
	 * Whether the PHP-rendered blocks are enabled.
	 *
	 * @return bool
	 */
	public static function has_blocks(): bool {
		return self::is_enabled( 'blocks' );
	}
}
