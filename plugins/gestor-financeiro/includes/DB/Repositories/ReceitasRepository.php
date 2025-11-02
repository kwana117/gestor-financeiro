<?php
/**
 * Receitas repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for revenue.
 */
class ReceitasRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'receitas' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'receita';
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
	 * Get monthly total.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @param string $field Field to sum (bruto, taxas, liquido). Default: liquido.
	 * @return float
	 */
	public function getMonthlyTotal( int $month, int $year, ?int $estabelecimento_id = null, string $field = 'liquido' ): float {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Validate field.
		$allowed_fields = array( 'bruto', 'taxas', 'liquido' );
		if ( ! in_array( $field, $allowed_fields, true ) ) {
			$field = 'liquido';
		}

		$where = array( 'MONTH(data) = %d', 'YEAR(data) = %d' );
		$values = array( $month, $year );

		if ( null !== $estabelecimento_id ) {
			$where[] = 'estabelecimento_id = %d';
			$values[] = $estabelecimento_id;
		}

		$where_clause = implode( ' AND ', $where );

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM({$field}) FROM {$table_name} WHERE {$where_clause}",
				...$values
			)
		);

		return (float) $total;
	}

	/**
	 * Find by channel.
	 *
	 * @param string $canal Channel.
	 * @param string|null $start_date Optional start date.
	 * @param string|null $end_date Optional end date.
	 * @return array<array<string, mixed>>
	 */
	public function findByChannel( string $canal, ?string $start_date = null, ?string $end_date = null ): array {
		$filters = array( 'canal' => $canal );
		if ( $start_date ) {
			$filters['data >='] = $start_date;
		}
		if ( $end_date ) {
			$filters['data <='] = $end_date;
		}
		return $this->findAll( $filters, array( 'data' => 'DESC' ) );
	}
}

