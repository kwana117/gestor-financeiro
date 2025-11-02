<?php
/**
 * Asset management.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

/**
 * Handle asset enqueuing.
 */
class Assets {

	/**
	 * Plugin instance.
	 *
	 * @var Assets|null
	 */
	private static ?Assets $instance = null;

	/**
	 * Whether assets are enqueued.
	 *
	 * @var bool
	 */
	private bool $assets_enqueued = false;

	/**
	 * Get plugin instance.
	 *
	 * @return Assets
	 */
	public static function get_instance(): Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Private constructor for singleton.
	}

	/**
	 * Initialize assets.
	 *
	 * @return void
	 */
	public function init(): void {
		// Assets will be enqueued only when shortcode is detected.
		// This is handled by the Dashboard class in Phase 6.
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		if ( $this->assets_enqueued ) {
			return;
		}

		// Enqueue CSS.
		$css_file = GF_PLUGIN_URL . 'assets/css/app.css';
		$css_path = GF_PLUGIN_DIR . 'assets/css/app.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'gestor-financeiro-app',
				$css_file,
				array(),
				GF_VERSION
			);
		}

		// Enqueue JavaScript.
		$js_file = GF_PLUGIN_URL . 'assets/js/app.js';
		$js_path = GF_PLUGIN_DIR . 'assets/js/app.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'gestor-financeiro-app',
				$js_file,
				array(),
				GF_VERSION,
				true
			);

			// Localize script with nonce and REST API data.
			wp_localize_script(
				'gestor-financeiro-app',
				'gestorFinanceiro',
				array(
					'apiUrl'  => rest_url( 'gestor-financeiro/v1/' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'version' => GF_VERSION,
				)
			);
		}

		$this->assets_enqueued = true;
	}

	/**
	 * Check if assets are already enqueued.
	 *
	 * @return bool
	 */
	public function are_assets_enqueued(): bool {
		return $this->assets_enqueued;
	}
}

