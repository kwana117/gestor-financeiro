<?php
/**
 * Special handling for apartment-type establishments.
 *
 * @package GestorFinanceiro
 * @subpackage Features
 */

declare(strict_types=1);

namespace GestorFinanceiro\Features;

use GestorFinanceiro\DB\Repositories\EstabelecimentosRepository;
use GestorFinanceiro\DB\Repositories\DespesasRepository;
use GestorFinanceiro\DB\Repositories\ReceitasRepository;

/**
 * Handle apartment-specific features.
 */
class Apartment {

	/**
	 * Pre-defined expense categories for apartments.
	 *
	 * @return array<string> Category list.
	 */
	public static function get_expense_categories(): array {
		return array(
			__( 'Condomínio', 'gestor-financeiro' ),
			__( 'IMI', 'gestor-financeiro' ),
			__( 'Água', 'gestor-financeiro' ),
			__( 'Eletricidade', 'gestor-financeiro' ),
			__( 'Gás', 'gestor-financeiro' ),
			__( 'Internet', 'gestor-financeiro' ),
			__( 'Telefone', 'gestor-financeiro' ),
			__( 'Seguro', 'gestor-financeiro' ),
			__( 'Manutenção', 'gestor-financeiro' ),
			__( 'Limpeza', 'gestor-financeiro' ),
			__( 'Outros', 'gestor-financeiro' ),
		);
	}

	/**
	 * Pre-defined revenue categories for apartments.
	 *
	 * @return array<string> Category list.
	 */
	public static function get_revenue_categories(): array {
		return array(
			__( 'Renda', 'gestor-financeiro' ),
			__( 'Depósito', 'gestor-financeiro' ),
			__( 'Outros', 'gestor-financeiro' ),
		);
	}

	/**
	 * Get apartment establishments.
	 *
	 * @return array<array<string, mixed>> Apartment establishments.
	 */
	public static function get_apartments(): array {
		$repo = new EstabelecimentosRepository();
		return $repo->findByType( 'apartamento' );
	}

	/**
	 * Check if establishment is an apartment.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @return bool True if apartment, false otherwise.
	 */
	public static function is_apartment( int $estabelecimento_id ): bool {
		$repo = new EstabelecimentosRepository();
		$estabelecimento = $repo->find( $estabelecimento_id );

		if ( ! $estabelecimento ) {
			return false;
		}

		return isset( $estabelecimento['tipo'] ) && 'apartamento' === $estabelecimento['tipo'];
	}

	/**
	 * Generate recurring transactions for apartment.
	 *
	 * @param int $estabelecimento_id Apartment establishment ID.
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @return array<string, mixed> Result with generated count and errors.
	 */
	public function generate_recurring_transactions( int $estabelecimento_id, string $start_date, string $end_date ): array {
		if ( ! self::is_apartment( $estabelecimento_id ) ) {
			return array(
				'success' => false,
				'error' => __( 'Estabelecimento não é um apartamento.', 'gestor-financeiro' ),
				'generated' => 0,
				'errors' => array(),
			);
		}

		$repo = new EstabelecimentosRepository();
		$estabelecimento = $repo->find( $estabelecimento_id );

		if ( ! $estabelecimento ) {
			return array(
				'success' => false,
				'error' => __( 'Estabelecimento não encontrado.', 'gestor-financeiro' ),
				'generated' => 0,
				'errors' => array(),
			);
		}

		$despesas_repo = new DespesasRepository();
		$receitas_repo = new ReceitasRepository();

		$generated = 0;
		$errors = array();

		// Get recurring expense templates from existing recurring expenses.
		$recurring_expenses = $this->get_recurring_expenses( $estabelecimento_id );

		// Generate expenses for each recurring template.
		foreach ( $recurring_expenses as $template ) {
			$result = $this->generate_expenses_from_template( $template, $start_date, $end_date, $despesas_repo );
			$generated += $result['count'];
			if ( ! empty( $result['errors'] ) ) {
				$errors = array_merge( $errors, $result['errors'] );
			}
		}

		// Get recurring revenue templates (rent payments).
		$recurring_revenues = $this->get_recurring_revenues( $estabelecimento_id );

		// Generate revenues for each recurring template.
		foreach ( $recurring_revenues as $template ) {
			$result = $this->generate_revenues_from_template( $template, $start_date, $end_date, $receitas_repo );
			$generated += $result['count'];
			if ( ! empty( $result['errors'] ) ) {
				$errors = array_merge( $errors, $result['errors'] );
			}
		}

		return array(
			'success' => true,
			'generated' => $generated,
			'errors' => $errors,
		);
	}

	/**
	 * Get recurring expense templates for apartment.
	 *
	 * @param int $estabelecimento_id Apartment establishment ID.
	 * @return array<array<string, mixed>> Recurring expense templates.
	 */
	private function get_recurring_expenses( int $estabelecimento_id ): array {
		$despesas_repo = new DespesasRepository();

		// Find expenses with recurring patterns (marked as recurring or with specific categories).
		$all_expenses = $despesas_repo->findAll(
			array( 'estabelecimento_id' => $estabelecimento_id ),
			array( 'data' => 'DESC' )
		);

		$templates = array();
		$processed = array();

		foreach ( $all_expenses as $expense ) {
			$tipo = $expense['tipo'] ?? '';
			$descricao = $expense['descricao'] ?? '';

			// Check if it's a recurring type expense.
			if ( ! $this->is_recurring_expense_type( $tipo, $descricao ) ) {
				continue;
			}

			// Create template key.
			$key = $tipo . '|' . $descricao;

			// Skip if already processed.
			if ( isset( $processed[ $key ] ) ) {
				continue;
			}

			// Extract day of month from vencimento or data.
			$vencimento = $expense['vencimento'] ?? $expense['data'] ?? '';
			$day_of_month = $this->extract_day_of_month( $vencimento );

			if ( ! $day_of_month ) {
				continue;
			}

			$templates[] = array(
				'estabelecimento_id' => $estabelecimento_id,
				'tipo' => $tipo,
				'descricao' => $descricao,
				'valor' => (float) ( $expense['valor'] ?? 0 ),
				'day_of_month' => $day_of_month,
				'fornecedor_id' => $expense['fornecedor_id'] ?? null,
				'notas' => $expense['notas'] ?? '',
			);

			$processed[ $key ] = true;
		}

		return $templates;
	}

	/**
	 * Get recurring revenue templates for apartment (rent).
	 *
	 * @param int $estabelecimento_id Apartment establishment ID.
	 * @return array<array<string, mixed>> Recurring revenue templates.
	 */
	private function get_recurring_revenues( int $estabelecimento_id ): array {
		$receitas_repo = new ReceitasRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		$estabelecimento = $estabelecimentos_repo->find( $estabelecimento_id );
		$dia_renda = $estabelecimento['dia_renda'] ?? null;

		if ( ! $dia_renda ) {
			return array();
		}

		// Get last rent payment to determine amount.
		$all_revenues = $receitas_repo->findAll(
			array( 'estabelecimento_id' => $estabelecimento_id ),
			array( 'data' => 'DESC' )
		);

		$rent_amount = 0;
		foreach ( $all_revenues as $revenue ) {
			// Assume rent is the largest regular payment.
			$liquido = (float) ( $revenue['liquido'] ?? 0 );
			if ( $liquido > $rent_amount ) {
				$rent_amount = $liquido;
			}
		}

		if ( $rent_amount <= 0 ) {
			return array();
		}

		return array(
			array(
				'estabelecimento_id' => $estabelecimento_id,
				'day_of_month' => (int) $dia_renda,
				'bruto' => $rent_amount,
				'taxas' => 0,
				'liquido' => $rent_amount,
				'notas' => __( 'Renda mensal', 'gestor-financeiro' ),
			),
		);
	}

	/**
	 * Generate expenses from recurring template.
	 *
	 * @param array<string, mixed> $template Template data.
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param DespesasRepository $repo Expenses repository.
	 * @return array<string, mixed> Result with count and errors.
	 */
	private function generate_expenses_from_template( array $template, string $start_date, string $end_date, DespesasRepository $repo ): array {
		$generated = 0;
		$errors = array();
		$day_of_month = (int) $template['day_of_month'];

		// Generate dates for each month in range.
		$dates = $this->generate_dates_for_month_range( $day_of_month, $start_date, $end_date );

		foreach ( $dates as $date ) {
			// Check if expense already exists for this date.
			$existing = $this->find_existing_expense(
				(int) $template['estabelecimento_id'],
				$template['tipo'],
				$template['descricao'],
				$date,
				$repo
			);

			if ( $existing ) {
				continue; // Skip if already exists.
			}

			// Create expense.
			$expense_data = array(
				'data' => $date,
				'estabelecimento_id' => (int) $template['estabelecimento_id'],
				'tipo' => $template['tipo'],
				'descricao' => $template['descricao'],
				'valor' => (float) $template['valor'],
				'vencimento' => $date,
				'pago' => 0,
			);

			if ( ! empty( $template['fornecedor_id'] ) ) {
				$expense_data['fornecedor_id'] = (int) $template['fornecedor_id'];
			}

			if ( ! empty( $template['notas'] ) ) {
				$expense_data['notas'] = $template['notas'];
			}

			$id = $repo->create( $expense_data );

			if ( false !== $id ) {
				$generated++;
			} else {
				$errors[] = sprintf(
					/* translators: %s: date */
					__( 'Erro ao gerar despesa para %s.', 'gestor-financeiro' ),
					$date
				);
			}
		}

		return array(
			'count' => $generated,
			'errors' => $errors,
		);
	}

	/**
	 * Generate revenues from recurring template.
	 *
	 * @param array<string, mixed> $template Template data.
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param ReceitasRepository $repo Revenue repository.
	 * @return array<string, mixed> Result with count and errors.
	 */
	private function generate_revenues_from_template( array $template, string $start_date, string $end_date, ReceitasRepository $repo ): array {
		$generated = 0;
		$errors = array();
		$day_of_month = (int) $template['day_of_month'];

		// Generate dates for each month in range.
		$dates = $this->generate_dates_for_month_range( $day_of_month, $start_date, $end_date );

		foreach ( $dates as $date ) {
			// Check if revenue already exists for this date.
			$existing = $this->find_existing_revenue(
				(int) $template['estabelecimento_id'],
				$date,
				$repo
			);

			if ( $existing ) {
				continue; // Skip if already exists.
			}

			// Create revenue.
			$revenue_data = array(
				'data' => $date,
				'estabelecimento_id' => (int) $template['estabelecimento_id'],
				'bruto' => (float) $template['bruto'],
				'taxas' => (float) ( $template['taxas'] ?? 0 ),
				'liquido' => (float) $template['liquido'],
				'notas' => $template['notas'] ?? __( 'Renda mensal', 'gestor-financeiro' ),
			);

			$id = $repo->create( $revenue_data );

			if ( false !== $id ) {
				$generated++;
			} else {
				$errors[] = sprintf(
					/* translators: %s: date */
					__( 'Erro ao gerar receita para %s.', 'gestor-financeiro' ),
					$date
				);
			}
		}

		return array(
			'count' => $generated,
			'errors' => $errors,
		);
	}

	/**
	 * Generate dates for month range with specific day of month.
	 *
	 * @param int $day_of_month Day of month (1-31).
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @return array<string> Generated dates.
	 */
	private function generate_dates_for_month_range( int $day_of_month, string $start_date, string $end_date ): array {
		$dates = array();
		$start = strtotime( $start_date );
		$end = strtotime( $end_date );

		if ( false === $start || false === $end ) {
			return $dates;
		}

		$current = $start;
		$current_month = (int) date( 'n', $current );
		$current_year = (int) date( 'Y', $current );

		while ( $current <= $end ) {
			// Get last day of current month.
			$last_day = (int) date( 't', mktime( 0, 0, 0, $current_month, 1, $current_year ) );

			// Use last day if day_of_month exceeds month length.
			$day = min( $day_of_month, $last_day );

			$date = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $day );

			if ( $date >= $start_date && $date <= $end_date ) {
				$dates[] = $date;
			}

			// Move to next month.
			$current_month++;
			if ( $current_month > 12 ) {
				$current_month = 1;
				$current_year++;
			}

			$current = mktime( 0, 0, 0, $current_month, 1, $current_year );
		}

		return $dates;
	}

	/**
	 * Extract day of month from date string.
	 *
	 * @param string $date Date string (YYYY-MM-DD).
	 * @return int|null Day of month or null.
	 */
	private function extract_day_of_month( string $date ): ?int {
		if ( empty( $date ) || $date === '0000-00-00' ) {
			return null;
		}

		$timestamp = strtotime( $date );
		if ( false === $timestamp ) {
			return null;
		}

		return (int) date( 'j', $timestamp );
	}

	/**
	 * Check if expense type is recurring.
	 *
	 * @param string $tipo Expense type.
	 * @param string $descricao Expense description.
	 * @return bool True if recurring, false otherwise.
	 */
	private function is_recurring_expense_type( string $tipo, string $descricao ): bool {
		$recurring_types = array(
			'condomínio',
			'condominio',
			'imi',
			'água',
			'agua',
			'eletricidade',
			'electricidade',
			'gás',
			'gas',
			'internet',
			'telefone',
			'seguro',
			'manutenção',
			'manutencao',
			'limpeza',
		);

		$tipo_lower = strtolower( $tipo );
		$descricao_lower = strtolower( $descricao );

		foreach ( $recurring_types as $recurring_type ) {
			if ( false !== strpos( $tipo_lower, $recurring_type ) || false !== strpos( $descricao_lower, $recurring_type ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Find existing expense.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @param string $tipo Expense type.
	 * @param string $descricao Expense description.
	 * @param string $date Date (YYYY-MM-DD).
	 * @param DespesasRepository $repo Expenses repository.
	 * @return array<string, mixed>|null Existing expense or null.
	 */
	private function find_existing_expense( int $estabelecimento_id, string $tipo, string $descricao, string $date, DespesasRepository $repo ): ?array {
		$expenses = $repo->findAll(
			array(
				'estabelecimento_id' => $estabelecimento_id,
				'data' => $date,
				'tipo' => $tipo,
			)
		);

		foreach ( $expenses as $expense ) {
			if ( isset( $expense['descricao'] ) && $expense['descricao'] === $descricao ) {
				return $expense;
			}
		}

		return null;
	}

	/**
	 * Find existing revenue.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @param string $date Date (YYYY-MM-DD).
	 * @param ReceitasRepository $repo Revenue repository.
	 * @return array<string, mixed>|null Existing revenue or null.
	 */
	private function find_existing_revenue( int $estabelecimento_id, string $date, ReceitasRepository $repo ): ?array {
		$revenues = $repo->findAll(
			array(
				'estabelecimento_id' => $estabelecimento_id,
				'data' => $date,
			)
		);

		if ( ! empty( $revenues ) ) {
			return $revenues[0];
		}

		return null;
	}
}

