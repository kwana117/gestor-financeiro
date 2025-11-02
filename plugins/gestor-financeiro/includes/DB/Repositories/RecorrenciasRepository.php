<?php
/**
 * Recorrencias repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for recurring transactions.
 */
class RecorrenciasRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'recorrencias' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'recorrencia';
	}

	/**
	 * Find active recurrences.
	 *
	 * @param string|null $date Optional date to check. Default: today.
	 * @return array<array<string, mixed>>
	 */
	public function findActive( ?string $date = null ): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		if ( null === $date ) {
			$date = current_time( 'Y-m-d' );
		}

		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE inicio <= %s AND (fim IS NULL OR fim >= %s) ORDER BY inicio ASC",
			$date,
			$date
		);

		return $wpdb->get_results( $query, ARRAY_A ) ?: array();
	}

	/**
	 * Find by entity type.
	 *
	 * @param string $entidade Entity type.
	 * @return array<array<string, mixed>>
	 */
	public function findByEntity( string $entidade ): array {
		return $this->findAll(
			array( 'entidade' => $entidade ),
			array( 'inicio' => 'ASC' )
		);
	}
}

