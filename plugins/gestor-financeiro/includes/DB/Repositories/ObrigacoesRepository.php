<?php
/**
 * Obrigacoes repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for obligations.
 */
class ObrigacoesRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'obrigacoes' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'obrigacao';
	}

	/**
	 * Find by periodicity.
	 *
	 * @param string $periodicidade Periodicity (mensal, trimestral, anual).
	 * @return array<array<string, mixed>>
	 */
	public function findByPeriodicity( string $periodicidade ): array {
		return $this->findAll(
			array( 'periodicidade' => $periodicidade ),
			array( 'nome' => 'ASC' )
		);
	}
}

