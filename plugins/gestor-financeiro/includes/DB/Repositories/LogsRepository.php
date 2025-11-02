<?php
/**
 * Logs repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for audit logs.
 */
class LogsRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'logs' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'log';
	}

	/**
	 * Create a log entry.
	 *
	 * @param string $entidade_tipo Entity type.
	 * @param int|null $entidade_id Entity ID.
	 * @param string $acao Action (create, update, delete).
	 * @param array<string, mixed>|null $dados_antigos Old data.
	 * @param array<string, mixed>|null $dados_novos New data.
	 * @return int|false Log ID or false on failure.
	 */
	public function create_log(
		string $entidade_tipo,
		?int $entidade_id,
		string $acao,
		?array $dados_antigos = null,
		?array $dados_novos = null
	) {
		global $wpdb;
		$table_name = $this->get_table_name();

		$user_id = get_current_user_id();
		$ip_address = $this->get_ip_address();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null;

		$data = array(
			'user_id'       => $user_id ?: null,
			'entidade_tipo' => $entidade_tipo,
			'entidade_id'   => $entidade_id,
			'acao'          => $acao,
			'dados_antigos' => $dados_antigos ? wp_json_encode( $dados_antigos, JSON_UNESCAPED_UNICODE ) : null,
			'dados_novos'   => $dados_novos ? wp_json_encode( $dados_novos, JSON_UNESCAPED_UNICODE ) : null,
			'ip_address'    => $ip_address,
			'user_agent'    => $user_agent ? substr( $user_agent, 0, 255 ) : null,
			'created_at'     => current_time( 'mysql' ),
		);

		$result = $wpdb->insert( $table_name, $data );

		if ( false === $result ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get IP address.
	 *
	 * @return string|null
	 */
	private function get_ip_address(): ?string {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return null;
	}

	/**
	 * Get logs with filters.
	 *
	 * @param array<string, mixed> $filters Filters.
	 * @param array<string, string> $order_by Order by.
	 * @param int|null $limit Limit.
	 * @param int|null $offset Offset.
	 * @return array<array<string, mixed>>
	 */
	public function get_logs(
		array $filters = array(),
		array $order_by = array( 'created_at' => 'DESC' ),
		?int $limit = null,
		?int $offset = null
	): array {
		return $this->findAll( $filters, $order_by, $limit, $offset );
	}
}

