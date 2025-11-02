<?php
/**
 * Alertas repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for alerts.
 */
class AlertasRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'alertas' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'alerta';
	}

	/**
	 * Find alerts due for execution.
	 *
	 * @param string|null $max_date Maximum execution date. Default: now.
	 * @return array<array<string, mixed>>
	 */
	public function findDueForExecution( ?string $max_date = null ): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		if ( null === $max_date ) {
			$max_date = current_time( 'mysql' );
		}

		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE proxima_execucao IS NOT NULL AND proxima_execucao <= %s AND (estado IS NULL OR estado != 'sent') ORDER BY proxima_execucao ASC",
			$max_date
		);

		return $wpdb->get_results( $query, ARRAY_A ) ?: array();
	}

	/**
	 * Update last sent time.
	 *
	 * @param int $id Alert ID.
	 * @return bool Success.
	 */
	public function markAsSent( int $id ): bool {
		return $this->update(
			$id,
			array(
				'estado'         => 'sent',
				'ultimo_envio'   => current_time( 'mysql' ),
				'proxima_execucao' => null,
			)
		);
	}
}

