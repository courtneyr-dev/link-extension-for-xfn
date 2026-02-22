<?php
/**
 * Feature flags for XFN Link Extension.
 */
final class XFN_Feature_Flags {

	private static array $defaults = array(
		'abilities_api'   => true,
		'meta_mirror'     => true,
	);

	public static function is_enabled( string $flag ): bool {
		if ( ! isset( self::$defaults[ $flag ] ) ) {
			return false;
		}

		$filtered = apply_filters( "xfn_feature_flag_{$flag}", null );
		if ( null !== $filtered ) {
			return (bool) $filtered;
		}

		$options = get_option( 'xfn_feature_flags', array() );
		if ( isset( $options[ $flag ] ) ) {
			return (bool) $options[ $flag ];
		}

		return self::$defaults[ $flag ];
	}

	public static function has_abilities_api(): bool {
		return self::is_enabled( 'abilities_api' ) && function_exists( 'wp_register_ability' );
	}

	public static function has_meta_mirror(): bool {
		return self::is_enabled( 'meta_mirror' );
	}
}
