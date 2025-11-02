<?php
/**
 * Plugin Name: Gestor Financeiro
 * Plugin URI: https://example.com/gestor-financeiro
 * Description: Sistema de gestão financeira para uma empresa com múltiplos estabelecimentos (restaurantes, bares, apartamentos).
 * Version: 1.0.0
 * Author: Gestor Financeiro
 * Author URI: https://example.com
 * Text Domain: gestor-financeiro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package GestorFinanceiro
 */

declare(strict_types=1);

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'GF_VERSION', '1.0.0' );
define( 'GF_PLUGIN_FILE', __FILE__ );
define( 'GF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader if available.
if ( file_exists( GF_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once GF_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	// Simple PSR-4 autoloader if Composer isn't used.
	spl_autoload_register(
		function ( string $class ): void {
			$prefix   = 'GestorFinanceiro\\';
			$base_dir = GF_PLUGIN_DIR . 'includes/';

			// Does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			// Get the relative class name.
			$relative_class = substr( $class, $len );

			// Replace namespace separators with directory separators.
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, require it.
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

/**
 * Main plugin class.
 */
final class GestorFinanceiro {

	/**
	 * Plugin instance.
	 *
	 * @var GestorFinanceiro|null
	 */
	private static ?GestorFinanceiro $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return GestorFinanceiro
	 */
	public static function get_instance(): GestorFinanceiro {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	private function init(): void {
		// Load text domain.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Initialize core classes.
		\GestorFinanceiro\Core\Core::get_instance()->init();

		// Register activation and deactivation hooks.
		register_activation_hook( GF_PLUGIN_FILE, array( 'GestorFinanceiro\\Core\\Activator', 'activate' ) );
		register_deactivation_hook( GF_PLUGIN_FILE, array( 'GestorFinanceiro\\Core\\Deactivator', 'deactivate' ) );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'gestor-financeiro',
			false,
			dirname( GF_PLUGIN_BASENAME ) . '/languages'
		);
	}
}

/**
 * Initialize the plugin.
 */
function gestor_financeiro_init(): void {
	GestorFinanceiro::get_instance();
}

// Start the plugin.
gestor_financeiro_init();

