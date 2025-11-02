<?php
/**
 * Funcionarios repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for employees.
 */
class FuncionariosRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'funcionarios' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'funcionario';
	}

	/**
	 * Find by establishment.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @return array<array<string, mixed>>
	 */
	public function findByEstablishment( int $estabelecimento_id ): array {
		return $this->findAll(
			array( 'estabelecimento_id' => $estabelecimento_id ),
			array( 'nome' => 'ASC' )
		);
	}

	/**
	 * Find by payment type.
	 *
	 * @param string $tipo_pagamento Payment type.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<array<string, mixed>>
	 */
	public function findByPaymentType( string $tipo_pagamento, ?int $estabelecimento_id = null ): array {
		$filters = array( 'tipo_pagamento' => $tipo_pagamento );
		if ( null !== $estabelecimento_id ) {
			$filters['estabelecimento_id'] = $estabelecimento_id;
		}
		return $this->findAll( $filters, array( 'nome' => 'ASC' ) );
	}
}

