<?php
/**
 * Plugin Name:       Link Extension for XFN
 * Plugin URI:        https://github.com/courtneyr-dev/xfn-link-extension
 * Description:       Extends the native Gutenberg link interface to include XFN (XHTML Friends Network) relationship options across all blocks that support links. Features floating toolbar access, Inspector Controls integration, and Link Advanced panel support.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * Author:            Courtney Robertson
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       link-extension-for-xfn
 *
 * @package LinkExtensionForXFN
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'XFN_LINK_EXTENSION_VERSION', '1.0.1' );
define( 'XFN_LINK_EXTENSION_PLUGIN_FILE', __FILE__ );
define( 'XFN_LINK_EXTENSION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'XFN_LINK_EXTENSION_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * XFN Link Extension main class
 *
 * Handles plugin initialization, asset enqueuing, and core functionality
 * for extending WordPress link interface with XFN relationship options
 * across multiple interface points: floating toolbar, inspector controls,
 * and link advanced panel.
 *
 * @since 1.0.0
 */
class XFN_Link_Extension {

	/**
	 * Plugin instance
	 *
	 * @var XFN_Link_Extension
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return XFN_Link_Extension
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * Initialize plugin hooks and functionality.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Initialize plugin
	 *
	 * Runs after WordPress has finished loading but before any headers are sent.
	 * Sets up plugin functionality and registers necessary components.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Add REST API endpoint for XFN validation (if needed in future)
		// This would be used for server-side validation of relationship combinations
	}

	/**
	 * Add settings page to WordPress admin
	 *
	 * @since 1.0.2
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Link Extension for XFN Settings', 'link-extension-for-xfn' ),
			__( 'Link Extension for XFN', 'link-extension-for-xfn' ),
			'manage_options',
			'xfn-link-extension',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings
	 *
	 * @since 1.0.2
	 */
	public function register_settings() {
		register_setting(
			'xfn_link_extension_settings',
			'xfn_link_extension_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default' => array(
					'enable_inspector_controls' => false,
					'enable_floating_toolbar' => false,
				),
			)
		);

		add_settings_section(
			'xfn_interface_settings',
			__( 'Interface Options', 'link-extension-for-xfn' ),
			array( $this, 'render_interface_section' ),
			'xfn-link-extension'
		);

		add_settings_field(
			'enable_inspector_controls',
			__( 'Inspector Controls', 'link-extension-for-xfn' ),
			array( $this, 'render_inspector_controls_field' ),
			'xfn-link-extension',
			'xfn_interface_settings'
		);

		add_settings_field(
			'enable_floating_toolbar',
			__( 'Floating Toolbar Button', 'link-extension-for-xfn' ),
			array( $this, 'render_floating_toolbar_field' ),
			'xfn-link-extension',
			'xfn_interface_settings'
		);
	}

	/**
	 * Get plugin settings
	 *
	 * @since 1.0.2
	 * @return array Plugin settings
	 */
	public function get_plugin_settings() {
		$defaults = array(
			'enable_inspector_controls' => false,
			'enable_floating_toolbar' => false,
		);

		$options = get_option( 'xfn_link_extension_options', $defaults );
		return wp_parse_args( $options, $defaults );
	}

	/**
	 * Sanitize settings
	 *
	 * @since 1.0.2
	 * @param array $input Raw input from settings form
	 * @return array Sanitized settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['enable_inspector_controls'] = ! empty( $input['enable_inspector_controls'] );
		$sanitized['enable_floating_toolbar'] = ! empty( $input['enable_floating_toolbar'] );

		return $sanitized;
	}

	/**
	 * Render settings page
	 *
	 * @since 1.0.2
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'xfn_link_extension_settings' );
				do_settings_sections( 'xfn-link-extension' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render interface settings section description
	 *
	 * @since 1.0.2
	 */
	public function render_interface_section() {
		?>
		<p><?php esc_html_e( 'Choose where XFN relationship controls appear in the editor. The Link Advanced Panel is always enabled and cannot be disabled.', 'link-extension-for-xfn' ); ?></p>
		<?php
	}

	/**
	 * Render inspector controls setting field
	 *
	 * @since 1.0.2
	 */
	public function render_inspector_controls_field() {
		$options = $this->get_plugin_settings();
		?>
		<label>
			<input
				type="checkbox"
				name="xfn_link_extension_options[enable_inspector_controls]"
				value="1"
				<?php checked( $options['enable_inspector_controls'], true ); ?>
			/>
			<?php esc_html_e( 'Show XFN panel in Inspector Controls (sidebar) for block-level links like Buttons and Navigation', 'link-extension-for-xfn' ); ?>
		</label>
		<?php
	}

	/**
	 * Render floating toolbar setting field
	 *
	 * @since 1.0.2
	 */
	public function render_floating_toolbar_field() {
		$options = $this->get_plugin_settings();
		?>
		<label>
			<input
				type="checkbox"
				name="xfn_link_extension_options[enable_floating_toolbar]"
				value="1"
				<?php checked( $options['enable_floating_toolbar'], true ); ?>
			/>
			<?php esc_html_e( 'Show XFN button in floating toolbar for block-level links', 'link-extension-for-xfn' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Note: The XFN section in the Link Advanced Panel (for inline links) is always available and cannot be disabled.', 'link-extension-for-xfn' ); ?>
		</p>
		<?php
	}

	/**
	 * Enqueue block editor assets
	 *
	 * Loads JavaScript and CSS files needed for the block editor interface.
	 * Only loads in the block editor context to avoid frontend performance impact.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		$asset_file_path = XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.asset.php';
		$default_deps    = array(
			'wp-blocks',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-hooks',
			'wp-i18n',
			'wp-rich-text',
			'wp-block-editor',
			'wp-compose',
		);

		$asset_meta = file_exists( $asset_file_path )
			? include $asset_file_path
			: array(
				'dependencies' => $default_deps,
				'version'      => filemtime( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.js' ),
			);

		$script_hash = file_exists( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.js' )
			? md5_file( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.js' )
			: time();
		$style_hash  = file_exists( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.css' )
			? md5_file( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.css' )
			: time();

		$script_version = sprintf(
			'%s-%s-%s',
			XFN_LINK_EXTENSION_VERSION,
			isset( $asset_meta['version'] ) ? $asset_meta['version'] : filemtime( XFN_LINK_EXTENSION_PLUGIN_PATH . 'build/index.js' ),
			substr( $script_hash, 0, 8 )
		);

		$style_version = sprintf(
			'%s-%s-%s',
			XFN_LINK_EXTENSION_VERSION,
			substr( $style_hash, 0, 8 ),
			time() // Add timestamp for aggressive cache busting during development
		);

		// Enqueue main JavaScript file
		wp_enqueue_script(
			'link-extension-for-xfn',
			XFN_LINK_EXTENSION_PLUGIN_URL . 'build/index.js',
			! empty( $asset_meta['dependencies'] ) ? $asset_meta['dependencies'] : $default_deps,
			$script_version,
			true
		);

		// Enqueue editor-specific styles
		wp_enqueue_style(
			'xfn-link-extension-editor',
			XFN_LINK_EXTENSION_PLUGIN_URL . 'build/index.css',
			array( 'wp-components' ),
			$style_version
		);

		// Localize script with XFN relationship data and translations
		wp_localize_script(
			'link-extension-for-xfn',
			'linkexfoData',
			array(
				'relationships' => $this->get_xfn_relationships(),
				'version' => XFN_LINK_EXTENSION_VERSION,
				'nonce' => wp_create_nonce( 'xfn_link_extension' ),
				'settings' => $this->get_plugin_settings(),
				'interfaces' => array(
					'toolbar' => __( 'Floating Toolbar', 'link-extension-for-xfn' ),
					'inspector' => __( 'Inspector Controls', 'link-extension-for-xfn' ),
					'advanced' => __( 'Link Advanced Panel', 'link-extension-for-xfn' ),
				),
			)
		);

		// Set script translations for JavaScript
		wp_set_script_translations(
			'link-extension-for-xfn',
			'link-extension-for-xfn',
			XFN_LINK_EXTENSION_PLUGIN_PATH . 'languages'
		);
	}

	/**
	 * Get XFN relationship definitions
	 *
	 * Returns the complete XFN 1.1 specification as a structured array.
	 * Includes all relationship categories with their types and validation rules.
	 *
	 * @since 1.0.0
	 * @return array XFN relationship structure
	 */
	private function get_xfn_relationships() {
		return array(
			'friendship' => array(
				'type' => 'radio',
				'label' => __( 'Friendship', 'link-extension-for-xfn' ),
				'description' => __( 'Your friendship level with this person (choose one)', 'link-extension-for-xfn' ),
				'options' => array(
					'contact' => __( 'Contact', 'link-extension-for-xfn' ),
					'acquaintance' => __( 'Acquaintance', 'link-extension-for-xfn' ),
					'friend' => __( 'Friend', 'link-extension-for-xfn' ),
				),
				'default' => null,
			),
			'physical' => array(
				'type' => 'checkbox',
				'label' => __( 'Physical', 'link-extension-for-xfn' ),
				'description' => __( 'Have you met this person in real life?', 'link-extension-for-xfn' ),
				'options' => array(
					'met' => __( 'Met', 'link-extension-for-xfn' ),
				),
			),
			'professional' => array(
				'type' => 'checkbox',
				'label' => __( 'Professional', 'link-extension-for-xfn' ),
				'description' => __( 'Professional relationships (multiple allowed)', 'link-extension-for-xfn' ),
				'options' => array(
					'co-worker' => __( 'Co-worker', 'link-extension-for-xfn' ),
					'colleague' => __( 'Colleague', 'link-extension-for-xfn' ),
				),
			),
			'geographical' => array(
				'type' => 'radio',
				'label' => __( 'Geographical', 'link-extension-for-xfn' ),
				'description' => __( 'Your geographical relationship (choose one)', 'link-extension-for-xfn' ),
				'options' => array(
					'co-resident' => __( 'Co-resident', 'link-extension-for-xfn' ),
					'neighbor' => __( 'Neighbor', 'link-extension-for-xfn' ),
				),
				'default' => null,
			),
			'family' => array(
				'type' => 'radio',
				'label' => __( 'Family', 'link-extension-for-xfn' ),
				'description' => __( 'Family relationship (choose one)', 'link-extension-for-xfn' ),
				'options' => array(
					'child' => __( 'Child', 'link-extension-for-xfn' ),
					'parent' => __( 'Parent', 'link-extension-for-xfn' ),
					'sibling' => __( 'Sibling', 'link-extension-for-xfn' ),
					'spouse' => __( 'Spouse', 'link-extension-for-xfn' ),
					'kin' => __( 'Kin', 'link-extension-for-xfn' ),
				),
				'default' => null,
			),
			'romantic' => array(
				'type' => 'checkbox',
				'label' => __( 'Romantic', 'link-extension-for-xfn' ),
				'description' => __( 'Romantic relationships (multiple allowed)', 'link-extension-for-xfn' ),
				'options' => array(
					'muse' => __( 'Muse', 'link-extension-for-xfn' ),
					'crush' => __( 'Crush', 'link-extension-for-xfn' ),
					'date' => __( 'Date', 'link-extension-for-xfn' ),
					'sweetheart' => __( 'Sweetheart', 'link-extension-for-xfn' ),
				),
			),
			'identity' => array(
				'type' => 'checkbox',
				'label' => __( 'Identity', 'link-extension-for-xfn' ),
				'description' => __( 'Is this link to your own content?', 'link-extension-for-xfn' ),
				'options' => array(
					'me' => __( 'Me', 'link-extension-for-xfn' ),
				),
			),
		);
	}

	/**
	 * Parse rel attribute to separate XFN and non-XFN values
	 *
	 * Takes a rel attribute string and separates XFN relationships from
	 * other rel values like nofollow, noopener, etc.
	 *
	 * @since 1.0.0
	 * @param string $rel_string The rel attribute value to parse
	 * @return array Array with 'xfn' and 'other' keys containing respective values
	 */
	public static function parse_rel_attribute( $rel_string ) {
		if ( empty( $rel_string ) ) {
			return array( 'xfn' => array(), 'other' => array() );
		}

		// All possible XFN relationship values
		$xfn_values = array(
			'contact', 'acquaintance', 'friend', 'met',
			'co-worker', 'colleague', 'co-resident', 'neighbor',
			'child', 'parent', 'sibling', 'spouse', 'kin',
			'muse', 'crush', 'date', 'sweetheart', 'me'
		);

		$rel_parts = array_filter( array_map( 'trim', explode( ' ', $rel_string ) ) );
		$xfn = array();
		$other = array();

		foreach ( $rel_parts as $part ) {
			if ( in_array( $part, $xfn_values, true ) ) {
				$xfn[] = $part;
			} else {
				$other[] = $part;
			}
		}

		return array(
			'xfn' => $xfn,
			'other' => $other,
		);
	}

	/**
	 * Combine XFN and other rel values into valid rel attribute string
	 *
	 * Takes XFN relationships and other rel values and combines them
	 * into a properly formatted rel attribute string.
	 *
	 * @since 1.0.0
	 * @param array $xfn_values Array of XFN relationship values
	 * @param array $other_values Array of non-XFN rel values
	 * @return string Combined rel attribute value
	 */
	public static function combine_rel_values( $xfn_values, $other_values ) {
		$all_values = array_merge(
			array_filter( (array) $other_values ),
			array_filter( (array) $xfn_values )
		);

		// Remove duplicates and empty values
		$all_values = array_unique( array_filter( $all_values ) );

		return implode( ' ', $all_values );
	}

	/**
	 * Validate XFN relationship combinations
	 *
	 * Ensures that selected XFN relationships are valid according to
	 * the XFN specification. Checks for mutually exclusive relationships.
	 *
	 * @since 1.0.0
	 * @param array $relationships Array of selected XFN relationships
	 * @return bool Whether the relationship combination is valid
	 */
	public static function validate_xfn_relationships( $relationships ) {
		if ( empty( $relationships ) || ! is_array( $relationships ) ) {
			return true;
		}

		// Define mutually exclusive groups
		$exclusive_groups = array(
			array( 'contact', 'acquaintance', 'friend' ),
			array( 'co-resident', 'neighbor' ),
			array( 'child', 'parent', 'sibling', 'spouse', 'kin' ),
		);

		// Check each exclusive group
		foreach ( $exclusive_groups as $group ) {
			$selected_in_group = array_intersect( $relationships, $group );
			if ( count( $selected_in_group ) > 1 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Sanitize XFN rel attribute value
	 *
	 * Cleans and validates a rel attribute value to ensure it contains
	 * only valid XFN and standard rel values.
	 *
	 * @since 1.0.0
	 * @param string $rel_value The rel attribute value to sanitize
	 * @return string Sanitized rel attribute value
	 */
	public static function sanitize_rel_attribute( $rel_value ) {
		if ( empty( $rel_value ) ) {
			return '';
		}

		// Parse the rel attribute
		$parsed = self::parse_rel_attribute( $rel_value );
		
		// Validate XFN relationships
		if ( ! self::validate_xfn_relationships( $parsed['xfn'] ) ) {
			// If invalid, remove all XFN values and keep only other values
			$parsed['xfn'] = array();
		}

		// Combine and return
		return self::combine_rel_values( $parsed['xfn'], $parsed['other'] );
	}
}

/**
 * Initialize the plugin
 */
function xfn_link_extension_init() {
	XFN_Link_Extension::get_instance();
}
add_action( 'plugins_loaded', 'xfn_link_extension_init' );

/**
 * Plugin activation hook
 *
 * Runs when the plugin is activated. Performs any necessary setup.
 *
 * @since 1.0.0
 */
function xfn_link_extension_activate() {
	// Check minimum requirements
	if ( version_compare( get_bloginfo( 'version' ), '6.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Link Extension for XFN requires WordPress 6.4 or higher.', 'link-extension-for-xfn' ),
			esc_html__( 'Plugin Activation Error', 'link-extension-for-xfn' ),
			array( 'back_link' => true )
		);
	}

	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Link Extension for XFN requires PHP 7.4 or higher.', 'link-extension-for-xfn' ),
			esc_html__( 'Plugin Activation Error', 'link-extension-for-xfn' ),
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'xfn_link_extension_activate' );

/**
 * Helper function to get XFN relationship definitions
 *
 * Provides a public interface for accessing XFN relationship structure.
 * Useful for themes or other plugins that need to work with XFN data.
 *
 * @since 1.0.0
 * @return array XFN relationship definitions
 */
function xfn_get_relationships() {
	$instance = XFN_Link_Extension::get_instance();
	return $instance->get_xfn_relationships();
}

/**
 * Helper function to parse rel attributes
 *
 * Provides a public interface for parsing rel attribute strings.
 *
 * @since 1.0.0
 * @param string $rel_string The rel attribute value to parse
 * @return array Array with 'xfn' and 'other' keys
 */
function xfn_parse_rel_attribute( $rel_string ) {
	return XFN_Link_Extension::parse_rel_attribute( $rel_string );
}

/**
 * Helper function to combine rel values
 *
 * Provides a public interface for combining XFN and other rel values.
 *
 * @since 1.0.0
 * @param array $xfn_values Array of XFN relationship values
 * @param array $other_values Array of non-XFN rel values
 * @return string Combined rel attribute value
 */
function xfn_combine_rel_values( $xfn_values, $other_values ) {
	return XFN_Link_Extension::combine_rel_values( $xfn_values, $other_values );
}

/**
 * Helper function to validate XFN relationships
 *
 * Provides a public interface for validating XFN relationship combinations.
 *
 * @since 1.0.0
 * @param array $relationships Array of selected XFN relationships
 * @return bool Whether the relationship combination is valid
 */
function xfn_validate_relationships( $relationships ) {
	return XFN_Link_Extension::validate_xfn_relationships( $relationships );
}

/**
 * Helper function to sanitize rel attributes
 *
 * Provides a public interface for sanitizing rel attribute values.
 *
 * @since 1.0.0
 * @param string $rel_value The rel attribute value to sanitize
 * @return string Sanitized rel attribute value
 */
function xfn_sanitize_rel_attribute( $rel_value ) {
	return XFN_Link_Extension::sanitize_rel_attribute( $rel_value );
}