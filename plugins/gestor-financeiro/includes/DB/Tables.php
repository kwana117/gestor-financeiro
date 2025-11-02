<?php
/**
 * Database table definitions and schema constants.
 *
 * @package GestorFinanceiro
 * @subpackage DB
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB;

/**
 * Database table definitions.
 */
class Tables {

	/**
	 * Get table names with prefix.
	 *
	 * @return array<string, string>
	 */
	public static function get_table_names(): array {
		global $wpdb;
		$prefix = $wpdb->prefix;

		return array(
			'estabelecimentos' => $prefix . 'gf_estabelecimentos',
			'fornecedores'     => $prefix . 'gf_fornecedores',
			'funcionarios'     => $prefix . 'gf_funcionarios',
			'despesas'         => $prefix . 'gf_despesas',
			'receitas'         => $prefix . 'gf_receitas',
			'obrigacoes'       => $prefix . 'gf_obrigacoes',
			'alertas'          => $prefix . 'gf_alertas',
			'recorrencias'     => $prefix . 'gf_recorrencias',
			'settings'         => $prefix . 'gf_settings',
			'logs'             => $prefix . 'gf_logs',
		);
	}

	/**
	 * Get table name by key.
	 *
	 * @param string $key Table key.
	 * @return string
	 */
	public static function get_table_name( string $key ): string {
		$tables = self::get_table_names();
		return $tables[ $key ] ?? '';
	}

	/**
	 * Get current database version.
	 *
	 * @return string
	 */
	public static function get_db_version(): string {
		return get_option( 'gf_db_version', '0.0.0' );
	}

	/**
	 * Update database version.
	 *
	 * @param string $version Version string.
	 * @return void
	 */
	public static function update_db_version( string $version ): void {
		update_option( 'gf_db_version', $version );
	}

	/**
	 * Get required database version.
	 *
	 * @return string
	 */
	public static function get_required_db_version(): string {
		return '1.0.0';
	}

	/**
	 * Get character set and collation.
	 *
	 * @return array<string, string>
	 */
	public static function get_charset_collate(): array {
		global $wpdb;
		return array(
			'charset'  => $wpdb->get_charset_collate(),
			'collation' => $wpdb->has_cap( 'collation' ) ? 'utf8mb4_unicode_ci' : 'utf8_general_ci',
		);
	}
}

