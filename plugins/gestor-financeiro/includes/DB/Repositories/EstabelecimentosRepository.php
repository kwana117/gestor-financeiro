<?php
/**
 * Estabelecimentos repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for establishments.
 */
class EstabelecimentosRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'estabelecimentos' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'estabelecimento';
	}

	/**
	 * Find active establishments.
	 *
	 * @return array<array<string, mixed>>
	 */
	public function findActive(): array {
		return $this->findAll(
			array( 'ativo' => 1 ),
			array( 'nome' => 'ASC' )
		);
	}

	/**
	 * Find by type.
	 *
	 * @param string $tipo Establishment type.
	 * @return array<array<string, mixed>>
	 */
	public function findByType( string $tipo ): array {
		return $this->findAll(
			array( 'tipo' => $tipo, 'ativo' => 1 ),
			array( 'nome' => 'ASC' )
		);
	}

	/**
	 * Find by name.
	 *
	 * @param string $nome Establishment name.
	 * @return array<string, mixed>|null Establishment data or null if not found.
	 */
	public function findByName( string $nome ): ?array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE nome = %s LIMIT 1",
				$nome
			),
			ARRAY_A
		);

		return $result ?: null;
	}
}

