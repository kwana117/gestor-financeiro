<?php
/**
 * Settings repository.
 *
 * @package GestorFinanceiro
 * @subpackage DB\Repositories
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB\Repositories;

use GestorFinanceiro\DB\Tables;

/**
 * Repository for plugin settings.
 */
class SettingsRepository extends BaseRepository {

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected function get_table_name(): string {
		return Tables::get_table_name( 'settings' );
	}

	/**
	 * Get entity type for logging.
	 *
	 * @return string
	 */
	protected function get_entity_type(): string {
		return 'setting';
	}

	/**
	 * Get setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		global $wpdb;
		$table_name = $this->get_table_name();

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT setting_value FROM {$table_name} WHERE setting_key = %s",
				$key
			)
		);

		if ( null === $value ) {
			return $default;
		}

		// Try to decode JSON, return as-is if not JSON.
		$decoded = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $decoded;
		}

		return $value;
	}

	/**
	 * Set setting value.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $value Setting value.
	 * @param string $group Setting group. Default: 'general'.
	 * @return bool Success.
	 */
	public function set( string $key, $value, string $group = 'general' ): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Encode as JSON if array or object.
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE );
		}

		// Check if setting exists.
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE setting_key = %s",
				$key
			)
		);

		$data = array(
			'setting_key'   => $key,
			'setting_value' => (string) $value,
			'setting_group' => $group,
		);

		if ( $existing ) {
			// Update existing.
			$result = $wpdb->update(
				$table_name,
				$data,
				array( 'setting_key' => $key ),
				null,
				array( '%s' )
			);
		} else {
			// Insert new.
			$result = $wpdb->insert( $table_name, $data );
		}

		return false !== $result;
	}

	/**
	 * Delete setting by ID (from BaseRepository).
	 *
	 * @param int $id Setting ID.
	 * @return bool Success.
	 */
	public function delete( int $id ): bool {
		return parent::delete( $id );
	}

	/**
	 * Delete setting by key.
	 *
	 * @param string $key Setting key.
	 * @return bool Success.
	 */
	public function deleteByKey( string $key ): bool {
		global $wpdb;
		$table_name = $this->get_table_name();

		// Find the ID first for audit logging.
		$setting = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE setting_key = %s LIMIT 1",
				$key
			),
			ARRAY_A
		);

		if ( ! $setting ) {
			return false;
		}

		$id = (int) $setting['id'];

		// Use parent delete for audit logging.
		return parent::delete( $id );
	}

	/**
	 * Get all settings by group.
	 *
	 * @param string $group Setting group.
	 * @return array<string, mixed>
	 */
	public function getByGroup( string $group ): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT setting_key, setting_value FROM {$table_name} WHERE setting_group = %s",
				$group
			),
			ARRAY_A
		);

		$settings = array();
		foreach ( $results as $row ) {
			$value = $row['setting_value'];
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$value = $decoded;
			}
			$settings[ $row['setting_key'] ] = $value;
		}

		return $settings;
	}

	/**
	 * Get all settings.
	 *
	 * @return array<string, mixed>
	 */
	public function getAll(): array {
		global $wpdb;
		$table_name = $this->get_table_name();

		$results = $wpdb->get_results(
			"SELECT setting_key, setting_value, setting_group FROM {$table_name}",
			ARRAY_A
		);

		$settings = array();
		foreach ( $results as $row ) {
			$value = $row['setting_value'];
			$decoded = json_decode( $value, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$value = $decoded;
			}
			$settings[ $row['setting_key'] ] = $value;
		}

		return $settings;
	}
}

