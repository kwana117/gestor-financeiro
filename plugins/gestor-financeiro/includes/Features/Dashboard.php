<?php
/**
 * Dashboard shortcode and frontend functionality.
 *
 * @package GestorFinanceiro
 * @subpackage Features
 */

declare(strict_types=1);

namespace GestorFinanceiro\Features;

use GestorFinanceiro\Core\Assets;
use GestorFinanceiro\Security\Nonces;

/**
 * Handle dashboard shortcode and frontend.
 */
class Dashboard {

	/**
	 * Initialize dashboard.
	 *
	 * @return void
	 */
	public function init(): void {
		add_shortcode( 'gestor_financeiro_dashboard', array( $this, 'render_dashboard' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}

	/**
	 * Check if shortcode is present and enqueue assets.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets(): void {
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'gestor_financeiro_dashboard' ) ) {
			$assets = Assets::get_instance();
			$assets->enqueue_frontend_assets();
		}
	}

	/**
	 * Render dashboard shortcode.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return string Dashboard HTML.
	 */
	public function render_dashboard( array $atts = array() ): string {
		// Check permissions.
		if ( ! \GestorFinanceiro\Security\Permissions::can_view() ) {
			return '<p>' . esc_html__( 'Não tem permissão para visualizar este conteúdo.', 'gestor-financeiro' ) . '</p>';
		}

		// Enqueue assets if not already done.
		$assets = Assets::get_instance();
		if ( ! $assets->are_assets_enqueued() ) {
			$assets->enqueue_frontend_assets();
		}

		// Get nonce for API calls.
		$nonce = Nonces::get_rest_nonce();

		// Load template.
		ob_start();
		include GF_PLUGIN_DIR . 'templates/dashboard.php';
		return ob_get_clean();
	}
}

