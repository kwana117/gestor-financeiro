<?php
/**
 * Core plugin class.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

use GestorFinanceiro\Core\Assets;
use GestorFinanceiro\Core\Router;
use GestorFinanceiro\DB\Migrations;
use GestorFinanceiro\Features\Dashboard;
use GestorFinanceiro\Features\Alerts;

/**
 * Core plugin functionality.
 */
class Core {

	/**
	 * Plugin instance.
	 *
	 * @var Core|null
	 */
	private static ?Core $instance = null;

	/**
	 * Assets instance.
	 *
	 * @var Assets|null
	 */
	private ?Assets $assets = null;

	/**
	 * Router instance.
	 *
	 * @var Router|null
	 */
	private ?Router $router = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Core
	 */
	public static function get_instance(): Core {
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
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		// Run database migrations on init (in case they're needed).
		Migrations::run();

		// Initialize router.
		$this->router = Router::get_instance();
		$this->router->init();

		// Initialize assets.
		$this->assets = Assets::get_instance();
		$this->assets->init();

		// Initialize dashboard.
		$dashboard = new Dashboard();
		$dashboard->init();

		// Initialize alerts.
		$alerts = new Alerts();
		$alerts->init();
	}

	/**
	 * Get assets instance.
	 *
	 * @return Assets|null
	 */
	public function get_assets(): ?Assets {
		return $this->assets;
	}

	/**
	 * Get router instance.
	 *
	 * @return Router|null
	 */
	public function get_router(): ?Router {
		return $this->router;
	}
}

