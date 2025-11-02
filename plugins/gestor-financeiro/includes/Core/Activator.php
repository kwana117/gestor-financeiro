<?php
/**
 * Plugin activation handler.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

use GestorFinanceiro\Core\Capabilities;
use GestorFinanceiro\DB\Migrations;
use GestorFinanceiro\DB\Repositories\SettingsRepository;

/**
 * Handle plugin activation.
 */
class Activator {

	/**
	 * Activate plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Register custom roles and capabilities.
		Capabilities::register_roles();
		Capabilities::register_capabilities();

		// Run database migrations.
		Migrations::run();

		// Schedule daily alerts cron.
		if ( ! wp_next_scheduled( 'gestor_financeiro_daily_alerts' ) ) {
			$settings_repo = new \GestorFinanceiro\DB\Repositories\SettingsRepository();
			$cron_hour = (int) $settings_repo->get( 'cron_hour', 8 );
			$timestamp = self::get_next_cron_timestamp( $cron_hour );
			wp_schedule_event( $timestamp, 'daily', 'gestor_financeiro_daily_alerts' );
			
			// Store hour for change detection.
			update_option( 'gf_cron_hour_stored', $cron_hour );
		}

		// Flush rewrite rules if needed (for REST API).
		flush_rewrite_rules();

		// Set activation flag.
		update_option( 'gf_activated', true );
		update_option( 'gf_activation_time', current_time( 'mysql' ) );
	}

	/**
	 * Get next cron timestamp for specified hour.
	 *
	 * @param int $hour Hour of day (0-23).
	 * @return int Unix timestamp.
	 */
	private static function get_next_cron_timestamp( int $hour ): int {
		$now = current_time( 'U' );
		$now_date = getdate( $now );
		$today = mktime( $hour, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year'] );

		// If today's time has passed, schedule for tomorrow.
		if ( $today <= $now ) {
			$today = mktime( $hour, 0, 0, $now_date['mon'], $now_date['mday'] + 1, $now_date['year'] );
		}

		return $today;
	}
}

