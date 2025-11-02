<?php
/**
 * Base repository class.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Repositories\LogsRepository;

/**
 * Base repository with common CRUD operations.
 */
abstract class BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	abstract protected function get_table_name(): string;

	/**
	 * Get primary key column name.
	 *
	 * @return string
	 */
	protected function get_primary_key(): string {
		return 'id';
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	abstract protected function get_entity_type(): string;

	/**
	 * Find record by ID.
	 *
	 * @param int $id Record ID.
	 * @return array|null
	 */
	public function find( int $id ): ?array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE {$this->get_primary_key()} = %d",
				$id
			),
			ARRAY_A
		);

		return $result ?: null;
	}

	/**
	 * Find all records with optional filters.
	 *
	 * @param array<string, mixed> $filters Optional filters.
	 * @param array<string, string> $order_by Optional order by clause.
	 * @param int|null $limit Optional limit.
	 * @param int|null $offset Optional offset.
	 * @return array<array<string, mixed>>
	 */
	public function findAll(
		array $filters = array(),
		array $order_by = array(),
		?int $limit = null,
		?int $offset = null
	): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$where = array();
		$where_values = array();

		// Build WHERE clause from filters.
		foreach ( $filters as $key => $value ) {
			// Handle array values (IN clause).
			if ( is_array( $value ) ) {
				if ( ! empty( $value ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
					$where[] = "{$key} IN ({$placeholders})";
					$where_values = array_merge( $where_values, $value );
				}
			} elseif ( is_null( $value ) ) {
				$where[] = "{$key} IS NULL";
			} else {
				// Handle operators like >=, <=, etc.
				if ( str_ends_with( $key, ' >=' ) ) {
					$column = str_replace( ' >=', '', $key );
					$where[] = "{$column} >= %s";
					$where_values[] = $value;
				} elseif ( str_ends_with( $key, ' <=' ) ) {
					$column = str_replace( ' <=', '', $key );
					$where[] = "{$column} <= %s";
					$where_values[] = $value;
				} elseif ( str_ends_with( $key, ' >' ) ) {
					$column = str_replace( ' >', '', $key );
					$where[] = "{$column} > %s";
					$where_values[] = $value;
				} elseif ( str_ends_with( $key, ' <' ) ) {
					$column = str_replace( ' <', '', $key );
					$where[] = "{$column} < %s";
					$where_values[] = $value;
				} else {
					$where[] = "{$key} = %s";
					$where_values[] = $value;
				}
			}
		}

		$where_clause = empty( $where ) ? '1=1' : implode( ' AND ', $where );

		// Build ORDER BY clause.
		$order_clause = '';
		if ( ! empty( $order_by ) ) {
			$order_parts = array();
			foreach ( $order_by as $column => $direction ) {
				$direction = strtoupper( $direction ) === 'DESC' ? 'DESC' : 'ASC';
				$order_parts[] = sanitize_sql_orderby( "{$column} {$direction}" );
			}
			$order_clause = 'ORDER BY ' . implode( ', ', $order_parts );
		}

		// Build LIMIT clause.
		$limit_clause = '';
		if ( null !== $limit ) {
			$limit_clause = 'LIMIT ' . absint( $limit );
			if ( null !== $offset ) {
				$limit_clause .= ' OFFSET ' . absint( $offset );
			}
		}

		// Prepare query with values.
		if ( ! empty( $where_values ) ) {
			$query = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE {$where_clause} {$order_clause} {$limit_clause}",
				...$where_values
			);
		} else {
			$query = "SELECT * FROM {$table_name} WHERE {$where_clause} {$order_clause} {$limit_clause}";
		}

		return $wpdb->get_results( $query, ARRAY_A ) ?: array();
	}

	/**
	 * Create new record.
	 *
	 * @param array<string, mixed> $data Record data.
	 * @return int|false Insert ID or false on failure.
	 */
	public function create( array $data ) {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Remove primary key from data if present.
		unset( $data[ $this->get_primary_key() ] );

		// Add timestamps if columns exist.
		if ( ! isset( $data['created_at'] ) ) {
			$data['created_at'] = current_time( 'mysql' );
		}
		if ( ! isset( $data['updated_at'] ) ) {
			$data['updated_at'] = current_time( 'mysql' );
		}

		$result = $wpdb->insert( $table_name, $data );

		if ( false === $result ) {
			return false;
		}

		$id = $wpdb->insert_id;

		// Log creation.
		$this->log_action( 'create', $id, null, $data );

		return $id;
	}

	/**
	 * Update record.
	 *
	 * @param int $id Record ID.
	 * @param array<string, mixed> $data Update data.
	 * @return bool Success.
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Get old data for logging.
		$old_data = $this->find( $id );
		if ( ! $old_data ) {
			return false;
		}

		// Remove primary key from data if present.
		unset( $data[ $this->get_primary_key() ] );

		// Update timestamp.
		$data['updated_at'] = current_time( 'mysql' );

		$result = $wpdb->update(
			$table_name,
			$data,
			array( $this->get_primary_key() => $id ),
			null,
			array( '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		// Get new data for logging.
		$new_data = $this->find( $id );

		// Log update.
		$this->log_action( 'update', $id, $old_data, $new_data ?: $data );

		return true;
	}

	/**
	 * Delete record.
	 *
	 * @param int $id Record ID.
	 * @return bool Success.
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Get old data for logging.
		$old_data = $this->find( $id );
		if ( ! $old_data ) {
			return false;
		}

		$result = $wpdb->delete(
			$table_name,
			array( $this->get_primary_key() => $id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return false;
		}

		// Log deletion.
		$this->log_action( 'delete', $id, $old_data, null );

		return true;
	}

	/**
	 * Count records with optional filters.
	 *
	 * @param array<string, mixed> $filters Optional filters.
	 * @return int
	 */
	public function count( array $filters = array() ): int {
		global $wpdb;
		$table_name = $this->get_table_name();

		$where = array();
		$where_values = array();

		// Build WHERE clause from filters.
		foreach ( $filters as $key => $value ) {
			// Handle array values (IN clause).
			if ( is_array( $value ) ) {
				if ( ! empty( $value ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
					$where[] = "{$key} IN ({$placeholders})";
					$where_values = array_merge( $where_values, $value );
				}
			} elseif ( is_null( $value ) ) {
				$where[] = "{$key} IS NULL";
			} else {
				$where[] = "{$key} = %s";
				$where_values[] = $value;
			}
		}

		$where_clause = empty( $where ) ? '1=1' : implode( ' AND ', $where );

		// Prepare query with values.
		if ( ! empty( $where_values ) ) {
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}",
				...$where_values
			);
		} else {
			$query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
		}

		$count = $wpdb->get_var( $query );

		return (int) $count;
	}

	/**
	 * Log action to audit log.
	 *
	 * @param string $action Action name (create, update, delete).
	 * @param int|null $entity_id Entity ID.
	 * @param array<string, mixed>|null $old_data Old data.
	 * @param array<string, mixed>|null $new_data New data.
	 * @return void
	 */
	protected function log_action(
		string $action,
		?int $entity_id,
		?array $old_data,
		?array $new_data
	): void {
		$logs_repo = new LogsRepository();
		$logs_repo->create_log(
			$this->get_entity_type(),
			$entity_id,
			$action,
			$old_data,
			$new_data
		);
	}
}

