<?php
/**
 * REST API router.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

/**
 * Handle REST API routing.
 */
class Router {

	/**
	 * Plugin instance.
	 *
	 * @var Router|null
	 */
	private static ?Router $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Router
	 */
	public static function get_instance(): Router {
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
	 * Initialize router.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		
		// Initialize RestController early so it can hook into rest_api_init.
		$rest_controller = new \GestorFinanceiro\Http\RestController();
		$rest_controller->init();
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$namespace = 'gestor-financeiro/v1';

		// Health check endpoint.
		register_rest_route(
			$namespace,
			'/health',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'health_check' ),
				'permission_callback' => '__return_true',
			)
		);

		// Debug endpoint to list all registered routes.
		register_rest_route(
			$namespace,
			'/debug/routes',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'debug_routes' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Health check endpoint.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response
	 */
	public function health_check( \WP_REST_Request $request ): \WP_REST_Response {
		return new \WP_REST_Response(
			array(
				'status'  => 'ok',
				'version' => GF_VERSION,
				'time'    => current_time( 'mysql' ),
			),
			200
		);
	}

	/**
	 * Debug routes endpoint.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response
	 */
	public function debug_routes( \WP_REST_Request $request ): \WP_REST_Response {
		global $wp_rest_server;
		$routes = array();
		if ( $wp_rest_server ) {
			$all_routes = $wp_rest_server->get_routes();
			foreach ( $all_routes as $route => $handlers ) {
				if ( strpos( $route, '/gestor-financeiro/' ) === 0 ) {
					$routes[] = $route;
				}
			}
		}
		return new \WP_REST_Response(
			array(
				'routes' => $routes,
				'count'  => count( $routes ),
			),
			200
		);
	}
}

