<?php
/**
 * Plugin deactivation handler.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

/**
 * Handle plugin deactivation.
 */
class Deactivator {

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Clear scheduled cron events.
		$timestamp = wp_next_scheduled( 'gestor_financeiro_daily_alerts' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'gestor_financeiro_daily_alerts' );
		}

		// Clear any other scheduled hooks.
		wp_clear_scheduled_hook( 'gestor_financeiro_daily_alerts' );

		// Flush rewrite rules.
		flush_rewrite_rules();

		// Set deactivation flag.
		update_option( 'gf_deactivated', true );
		update_option( 'gf_deactivation_time', current_time( 'mysql' ) );
	}
}

