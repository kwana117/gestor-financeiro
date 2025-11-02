<?php
/**
 * Fornecedores repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for suppliers.
 */
class FornecedoresRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'fornecedores' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'fornecedor';
	}

	/**
	 * Find by NIF.
	 *
	 * @param string $nif NIF.
	 * @return array|null
	 */
	public function findByNif( string $nif ): ?array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE nif = %s",
				$nif
			),
			ARRAY_A
		);

		return $result ?: null;
	}

	/**
	 * Find by category.
	 *
	 * @param string $categoria Category.
	 * @return array<array<string, mixed>>
	 */
	public function findByCategory( string $categoria ): array {
		return $this->findAll(
			array( 'categoria' => $categoria ),
			array( 'nome' => 'ASC' )
		);
	}
}

