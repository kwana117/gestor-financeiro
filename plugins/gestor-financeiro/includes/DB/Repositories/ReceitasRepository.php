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
	 * @param string $field Field to sum (valor, bruto, taxas, liquido). Default: valor.
	 * @return float
	 */
	public function getMonthlyTotal( int $month, int $year, ?int $estabelecimento_id = null, string $field = 'valor' ): float {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Validate field. Support both new format (valor) and old format (liquido, bruto, taxas) for backward compatibility.
		$allowed_fields = array( 'valor', 'bruto', 'taxas', 'liquido' );
		if ( ! in_array( $field, $allowed_fields, true ) ) {
			$field = 'valor';
		}
		
		// If field is 'valor' but column doesn't exist, fall back to 'liquido' for backward compatibility
		if ( 'valor' === $field ) {
			$column_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'valor'", DB_NAME, $table_name ) );
			if ( ! $column_exists ) {
				$field = 'liquido';
			}
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

