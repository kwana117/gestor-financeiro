<?php
/**
 * Daily alerts system.
 *
 * @package GestorFinanceiro
 * @subpackage Features
 */

declare(strict_types=1);

namespace GestorFinanceiro\Features;

use GestorFinanceiro\DB\Repositories\DespesasRepository;
use GestorFinanceiro\DB\Repositories\FuncionariosRepository;
use GestorFinanceiro\DB\Repositories\EstabelecimentosRepository;
use GestorFinanceiro\DB\Repositories\ObrigacoesRepository;
use GestorFinanceiro\DB\Repositories\SettingsRepository;

/**
 * Handle daily payment alerts.
 */
class Alerts {

	/**
	 * Initialize alerts system.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'gestor_financeiro_daily_alerts', array( $this, 'send_daily_alerts' ) );
		add_action( 'init', array( $this, 'maybe_reschedule_cron' ) );
	}

	/**
	 * Reschedule cron if settings changed.
	 *
	 * @return void
	 */
	public function maybe_reschedule_cron(): void {
		// Check if cron hour setting changed.
		$settings_repo = new SettingsRepository();
		$cron_hour = (int) $settings_repo->get( 'cron_hour', 8 );
		$stored_hour = (int) get_option( 'gf_cron_hour_stored', 8 );

		if ( $cron_hour !== $stored_hour ) {
			// Clear existing cron.
			wp_clear_scheduled_hook( 'gestor_financeiro_daily_alerts' );

			// Schedule with new hour.
			$timestamp = $this->get_next_cron_timestamp( $cron_hour );
			wp_schedule_event( $timestamp, 'daily', 'gestor_financeiro_daily_alerts' );

			// Store new hour.
			update_option( 'gf_cron_hour_stored', $cron_hour );
		}
	}

	/**
	 * Send daily alerts.
	 *
	 * @return void
	 */
	public function send_daily_alerts(): void {
		$settings_repo = new SettingsRepository();
		$alerts_email = $settings_repo->get( 'alerts_email', '' );

		// Get admin email if not configured.
		if ( empty( $alerts_email ) ) {
			$alerts_email = get_option( 'admin_email' );
		}

		if ( empty( $alerts_email ) ) {
			return; // No email to send to.
		}

		// Gather alert data.
		$alerts_data = $this->gather_alerts_data();

		// Send email.
		$this->send_alert_email( $alerts_email, $alerts_data );
	}

	/**
	 * Gather alerts data (today, next 7 days, overdue).
	 *
	 * @return array<string, mixed>
	 */
	private function gather_alerts_data(): array {
		$despesas_repo = new DespesasRepository();
		$funcionarios_repo = new FuncionariosRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();
		$obrigacoes_repo = new ObrigacoesRepository();
		$settings_repo = new SettingsRepository();

		$today = current_time( 'Y-m-d' );
		$in_7_days = date( 'Y-m-d', strtotime( '+7 days', current_time( 'timestamp' ) ) );

		// Get overdue payments (before today).
		$overdue = $despesas_repo->findPending( date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) ) );

		// Get payments due today.
		$today_payments_all = $despesas_repo->findPending( $today, null );
		$today_payments = array_filter(
			$today_payments_all,
			function( $payment ) use ( $today ) {
				return isset( $payment['vencimento'] ) && $payment['vencimento'] === $today && isset( $payment['pago'] ) && $payment['pago'] == 0;
			}
		);

		// Get payments due in next 7 days.
		$next_7_days_all = $despesas_repo->findPending( $in_7_days );
		$next_7_days = array_filter(
			$next_7_days_all,
			function( $payment ) use ( $today, $in_7_days ) {
				$vencimento = $payment['vencimento'] ?? '';
				return $vencimento > $today && $vencimento <= $in_7_days && isset( $payment['pago'] ) && $payment['pago'] == 0;
			}
		);

		// Get salaries due (based on establishment day_renda).
		$salaries_due = $this->get_salaries_due( $today, $in_7_days );

		// Get obligations due.
		$obligations_due = $this->get_obligations_due( $today, $in_7_days );

		// Group by type.
		$alerts = array(
			'hoje' => array_merge( $today_payments, $salaries_due['today'], $obligations_due['today'] ),
			'proximos_7_dias' => array_merge( $next_7_days, $salaries_due['next_7_days'], $obligations_due['next_7_days'] ),
			'atrasados' => $overdue,
		);

		return $alerts;
	}

	/**
	 * Get salaries due in date range.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	private function get_salaries_due( string $start_date, string $end_date ): array {
		$funcionarios_repo = new FuncionariosRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		$today = new \DateTime( $start_date );
		$end = new \DateTime( $end_date );

		$salaries_today = array();
		$salaries_next = array();

		$funcionarios = $funcionarios_repo->findAll();
		foreach ( $funcionarios as $funcionario ) {
			if ( empty( $funcionario['estabelecimento_id'] ) ) {
				continue;
			}

			$estabelecimento = $estabelecimentos_repo->find( (int) $funcionario['estabelecimento_id'] );
			if ( ! $estabelecimento || empty( $estabelecimento['dia_renda'] ) ) {
				continue;
			}

			$day = (int) $estabelecimento['dia_renda'];
			$current = clone $today;

			// Check today and next 7 days.
			while ( $current <= $end ) {
				$current_day = (int) $current->format( 'j' );
				if ( $current_day === $day ) {
					$salary_data = array(
						'type' => 'salario',
						'date' => $current->format( 'Y-m-d' ),
						'funcionario' => $funcionario['nome'],
						'valor' => (float) $funcionario['valor_base'],
						'estabelecimento' => $estabelecimento['nome'],
					);

					if ( $current->format( 'Y-m-d' ) === $start_date ) {
						$salaries_today[] = $salary_data;
					} else {
						$salaries_next[] = $salary_data;
					}
				}
				$current->modify( '+1 day' );
			}
		}

		return array(
			'today' => $salaries_today,
			'next_7_days' => $salaries_next,
		);
	}

	/**
	 * Get obligations due in date range.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	private function get_obligations_due( string $start_date, string $end_date ): array {
		$obrigacoes_repo = new ObrigacoesRepository();
		$settings_repo = new SettingsRepository();

		$imi_window_start = (int) $settings_repo->get( 'imi_window_start', 1 );
		$imi_window_end = (int) $settings_repo->get( 'imi_window_end', 31 );

		$today = new \DateTime( $start_date );
		$end = new \DateTime( $end_date );

		$obligations_today = array();
		$obligations_next = array();

		$obrigacoes = $obrigacoes_repo->findAll();
		foreach ( $obrigacoes as $obrigacao ) {
			$periodicidade = $obrigacao['periodicidade'];
			$due_day = $obrigacao['dia_fim'] ?: $obrigacao['dia_inicio'] ?: null;

			if ( ! $due_day ) {
				continue;
			}

			// Calculate next occurrence.
			$current = clone $today;
			while ( $current <= $end ) {
				$current_day = (int) $current->format( 'j' );
				$current_month = (int) $current->format( 'n' );
				$current_year = (int) $current->format( 'Y' );

				$is_due = false;

				// Check based on periodicity.
				if ( 'mensal' === $periodicidade ) {
					if ( $current_day === $due_day ) {
						$is_due = true;
					}
				} elseif ( 'trimestral' === $periodicidade ) {
					// Check if in quarter months (Jan, Apr, Jul, Oct) and on due day.
					$quarter_months = array( 1, 4, 7, 10 );
					if ( in_array( $current_month, $quarter_months, true ) && $current_day === $due_day ) {
						$is_due = true;
					}
				} elseif ( 'anual' === $periodicidade ) {
					// Check if January and within IMI window.
					if ( $current_month === 1 && $current_day >= $imi_window_start && $current_day <= $imi_window_end ) {
						$is_due = true;
					}
				}

				if ( $is_due ) {
					$obligation_data = array(
						'type' => 'obrigacao',
						'date' => $current->format( 'Y-m-d' ),
						'nome' => $obrigacao['nome'],
						'periodicidade' => $periodicidade,
					);

					if ( $current->format( 'Y-m-d' ) === $start_date ) {
						$obligations_today[] = $obligation_data;
					} else {
						$obligations_next[] = $obligation_data;
					}
				}

				$current->modify( '+1 day' );
			}
		}

		return array(
			'today' => $obligations_today,
			'next_7_days' => $obligations_next,
		);
	}

	/**
	 * Send alert email.
	 *
	 * @param string $email Email address.
	 * @param array<string, mixed> $alerts_data Alert data.
	 * @return void
	 */
	private function send_alert_email( string $email, array $alerts_data ): void {
		$subject = __( 'Alertas Diários - Gestor Financeiro', 'gestor-financeiro' );

		// Load email template.
		ob_start();
		include GF_PLUGIN_DIR . 'templates/emails/alertas-diarios.php';
		$message = ob_get_clean();

		// Set headers.
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		// Send email.
		$result = wp_mail( $email, $subject, $message, $headers );

		// Log email send result.
		if ( ! $result ) {
			error_log( 'Gestor Financeiro: Failed to send alert email to ' . $email );
		}
	}

	/**
	 * Get next cron timestamp for specified hour.
	 *
	 * @param int $hour Hour of day (0-23).
	 * @return int Unix timestamp.
	 */
	private function get_next_cron_timestamp( int $hour ): int {
		$now = current_time( 'U' );
		$now_date = getdate( $now );
		$today = mktime( $hour, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year'] );

		// If today's time has passed, schedule for tomorrow.
		if ( $today <= $now ) {
			$today = mktime( $hour, 0, 0, $now_date['mon'], $now_date['mday'] + 1, $now_date['year'] );
		}

		return $today;
	}

	/**
	 * Manual trigger for alerts (WP-CLI fallback).
	 *
	 * @return array<string, mixed>
	 */
	public function trigger_alerts_manually(): array {
		$this->send_daily_alerts();

		return array(
			'success' => true,
			'message' => __( 'Alertas enviados com sucesso.', 'gestor-financeiro' ),
		);
	}
}

