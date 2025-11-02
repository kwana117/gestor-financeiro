<?php
/**
 * Despesas repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for expenses.
 */
class DespesasRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'despesas' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'despesa';
	}

	/**
	 * Find by date range.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<array<string, mixed>>
	 */
	public function findByDateRange( string $start_date, string $end_date, ?int $estabelecimento_id = null ): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$where = array( 'data >= %s', 'data <= %s' );
		$values = array( $start_date, $end_date );

		if ( null !== $estabelecimento_id ) {
			$where[] = 'estabelecimento_id = %d';
			$values[] = $estabelecimento_id;
		}

		$where_clause = implode( ' AND ', $where );

		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY data DESC, id DESC",
			...$values
		);

		return $wpdb->get_results( $query, ARRAY_A ) ?: array();
	}

	/**
	 * Find pending payments (unpaid with due date).
	 *
	 * @param string|null $max_date Maximum due date (YYYY-MM-DD). Default: today.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<array<string, mixed>>
	 */
	public function findPending( ?string $max_date = null, ?int $estabelecimento_id = null ): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		if ( null === $max_date ) {
			$max_date = current_time( 'Y-m-d' );
		}

		$where = array( 'pago = 0', 'vencimento IS NOT NULL', 'vencimento <= %s' );
		$values = array( $max_date );

		if ( null !== $estabelecimento_id ) {
			$where[] = 'estabelecimento_id = %d';
			$values[] = $estabelecimento_id;
		}

		$where_clause = implode( ' AND ', $where );

		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY vencimento ASC",
			...$values
		);

		return $wpdb->get_results( $query, ARRAY_A ) ?: array();
	}

	/**
	 * Mark as paid.
	 *
	 * @param int $id Expense ID.
	 * @param string|null $metodo Payment method.
	 * @return bool Success.
	 */
	public function markAsPaid( int $id, ?string $metodo = null ): bool {
		$data = array(
			'pago'    => 1,
			'pago_em' => current_time( 'mysql' ),
		);

		if ( null !== $metodo ) {
			$data['metodo'] = $metodo;
		}

		return $this->update( $id, $data );
	}

	/**
	 * Get monthly total.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return float
	 */
	public function getMonthlyTotal( int $month, int $year, ?int $estabelecimento_id = null ): float {
		global $wpdb;
		$table_name = $this->get_table_name();

		$where = array( 'MONTH(data) = %d', 'YEAR(data) = %d' );
		$values = array( $month, $year );

		if ( null !== $estabelecimento_id ) {
			$where[] = 'estabelecimento_id = %d';
			$values[] = $estabelecimento_id;
		}

		$where_clause = implode( ' AND ', $where );

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(valor) FROM {$table_name} WHERE {$where_clause}",
				...$values
			)
		);

		return (float) $total;
	}
}

