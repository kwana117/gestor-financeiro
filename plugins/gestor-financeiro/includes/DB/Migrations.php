<?php
/**
 * Database migration system.
 *
 * @package GestorFinanceiro
 * @subpackage DB
 */

declare(strict_types=1);

namespace GestorFinanceiro\DB;

/**
 * Handle database migrations.
 */
class Migrations {

	/**
	 * Current migration version.
	 */
	private const CURRENT_VERSION = '1.0.0';

	/**
	 * Run migrations if needed.
	 *
	 * @return void
	 */
	public static function run(): void {
		$current_version = Tables::get_db_version();
		$required_version = self::CURRENT_VERSION;

		if ( version_compare( $current_version, $required_version, '<' ) ) {
			self::migrate( $current_version, $required_version );
			Tables::update_db_version( $required_version );
		}

		// Always ensure valor_renda column exists (for existing installations).
		// This is safe to run multiple times as it checks if column exists first.
		self::add_valor_renda_column();
	}

	/**
	 * Run migration from version to version.
	 *
	 * @param string $from_version From version.
	 * @param string $to_version To version.
	 * @return void
	 */
	private static function migrate( string $from_version, string $to_version ): void {
		// If version is 0.0.0, run initial migration.
		if ( '0.0.0' === $from_version ) {
			self::create_tables();
		} else {
			// For existing installations, ensure valor_renda column exists.
			// This function checks if column exists before adding it.
			self::add_valor_renda_column();
		}

		// Future migrations can be added here.
		// Example: if ( version_compare( $from_version, '1.1.0', '<' ) ) { ... }
	}

	/**
	 * Add valor_renda column to estabelecimentos table.
	 *
	 * @return void
	 */
	private static function add_valor_renda_column(): void {
		global $wpdb;
		$table_name = Tables::get_table_name( 'estabelecimentos' );

		// Check if table exists first.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$table_name
			)
		);

		if ( ! $table_exists ) {
			return;
		}

		// Check if column already exists using SHOW COLUMNS (more compatible).
		$column_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'valor_renda'",
				$wpdb->dbname,
				$table_name
			)
		);

		if ( empty( $column_exists ) || 0 === (int) $column_exists ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN valor_renda decimal(12,2) DEFAULT NULL AFTER dia_renda"
			);

			// If query failed, try alternative method.
			if ( false === $result ) {
				// Try using dbDelta approach.
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$schema = self::get_estabelecimentos_schema();
				dbDelta( $schema );
			}
		}
	}

	/**
	 * Create all database tables.
	 *
	 * @return void
	 */
	private static function create_tables(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tables = array(
			'estabelecimentos' => self::get_estabelecimentos_schema(),
			'fornecedores'     => self::get_fornecedores_schema(),
			'funcionarios'     => self::get_funcionarios_schema(),
			'despesas'         => self::get_despesas_schema(),
			'receitas'         => self::get_receitas_schema(),
			'obrigacoes'       => self::get_obrigacoes_schema(),
			'alertas'          => self::get_alertas_schema(),
			'recorrencias'     => self::get_recorrencias_schema(),
			'settings'         => self::get_settings_schema(),
			'logs'             => self::get_logs_schema(),
		);

		foreach ( $tables as $key => $sql ) {
			$table_name = Tables::get_table_name( $key );
			dbDelta( $sql );

			// Log table creation.
			$wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
		}
	}

	/**
	 * Get estabelecimentos table schema.
	 *
	 * @return string
	 */
	private static function get_estabelecimentos_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'estabelecimentos' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			nome varchar(255) NOT NULL,
			tipo enum('restaurante','bar','apartamento') NOT NULL DEFAULT 'restaurante',
			dia_renda tinyint(3) UNSIGNED DEFAULT NULL,
			valor_renda decimal(12,2) DEFAULT NULL,
			ativo tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_tipo (tipo),
			KEY idx_ativo (ativo)
		) {$charset_collate};";
	}

	/**
	 * Get fornecedores table schema.
	 *
	 * @return string
	 */
	private static function get_fornecedores_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'fornecedores' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			nome varchar(255) NOT NULL,
			nif varchar(20) DEFAULT NULL,
			categoria varchar(100) DEFAULT NULL,
			prazo_pagamento smallint(5) UNSIGNED DEFAULT NULL,
			contacto varchar(255) DEFAULT NULL,
			iban varchar(34) DEFAULT NULL,
			notas text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_categoria (categoria),
			KEY idx_nif (nif),
			KEY idx_categoria_prazo (categoria, prazo_pagamento)
		) {$charset_collate};";
	}

	/**
	 * Get funcionarios table schema.
	 *
	 * @return string
	 */
	private static function get_funcionarios_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'funcionarios' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			nome varchar(255) NOT NULL,
			tipo_pagamento enum('fixo','diario','hora') NOT NULL DEFAULT 'fixo',
			valor_base decimal(12,2) NOT NULL DEFAULT 0.00,
			regra_pagamento varchar(20) DEFAULT NULL,
			estabelecimento_id bigint(20) UNSIGNED DEFAULT NULL,
			iban varchar(34) DEFAULT NULL,
			notas text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_estabelecimento (estabelecimento_id),
			KEY idx_tipo_pagamento (tipo_pagamento),
			KEY idx_estabelecimento_tipo (estabelecimento_id, tipo_pagamento),
			KEY idx_tipo_estabelecimento (tipo_pagamento, estabelecimento_id)
		) {$charset_collate};";
	}

	/**
	 * Get despesas table schema.
	 *
	 * @return string
	 */
	private static function get_despesas_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'despesas' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			data date NOT NULL,
			estabelecimento_id bigint(20) UNSIGNED DEFAULT NULL,
			tipo varchar(100) DEFAULT NULL,
			fornecedor_id bigint(20) UNSIGNED DEFAULT NULL,
			funcionario_id bigint(20) UNSIGNED DEFAULT NULL,
			descricao varchar(500) NOT NULL,
			vencimento date DEFAULT NULL,
			valor decimal(12,2) NOT NULL DEFAULT 0.00,
			pago tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
			pago_em datetime DEFAULT NULL,
			metodo varchar(50) DEFAULT NULL,
			anexo bigint(20) UNSIGNED DEFAULT NULL,
			notas text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_data (data),
			KEY idx_estabelecimento (estabelecimento_id),
			KEY idx_fornecedor (fornecedor_id),
			KEY idx_funcionario (funcionario_id),
			KEY idx_vencimento (vencimento),
			KEY idx_pago (pago),
			KEY idx_tipo (tipo),
			KEY idx_estabelecimento_data (estabelecimento_id, data),
			KEY idx_estabelecimento_vencimento (estabelecimento_id, vencimento),
			KEY idx_tipo_vencimento_pago (tipo, vencimento, pago),
			KEY idx_fornecedor_vencimento (fornecedor_id, vencimento),
			KEY idx_funcionario_vencimento (funcionario_id, vencimento),
			KEY idx_pago_vencimento (pago, vencimento)
		) {$charset_collate};";
	}

	/**
	 * Get receitas table schema.
	 *
	 * @return string
	 */
	private static function get_receitas_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'receitas' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			data date NOT NULL,
			estabelecimento_id bigint(20) UNSIGNED DEFAULT NULL,
			canal varchar(50) DEFAULT NULL,
			bruto decimal(12,2) NOT NULL DEFAULT 0.00,
			taxas decimal(12,2) NOT NULL DEFAULT 0.00,
			liquido decimal(12,2) NOT NULL DEFAULT 0.00,
			notas text DEFAULT NULL,
			anexo bigint(20) UNSIGNED DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_data (data),
			KEY idx_estabelecimento (estabelecimento_id),
			KEY idx_canal (canal),
			KEY idx_estabelecimento_data (estabelecimento_id, data),
			KEY idx_data_estabelecimento (data, estabelecimento_id),
			KEY idx_canal_data (canal, data)
		) {$charset_collate};";
	}

	/**
	 * Get obrigacoes table schema.
	 *
	 * @return string
	 */
	private static function get_obrigacoes_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'obrigacoes' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			nome varchar(255) NOT NULL,
			periodicidade enum('mensal','trimestral','anual') NOT NULL DEFAULT 'mensal',
			dia_inicio smallint(5) UNSIGNED DEFAULT NULL,
			dia_fim smallint(5) UNSIGNED DEFAULT NULL,
			notas text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_periodicidade (periodicidade),
			KEY idx_periodicidade_dias (periodicidade, dia_inicio, dia_fim)
		) {$charset_collate};";
	}

	/**
	 * Get alertas table schema.
	 *
	 * @return string
	 */
	private static function get_alertas_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'alertas' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			entidade_tipo varchar(50) NOT NULL,
			entidade_id bigint(20) UNSIGNED NOT NULL,
			regra text DEFAULT NULL,
			estado varchar(50) DEFAULT NULL,
			proxima_execucao datetime DEFAULT NULL,
			ultimo_envio datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_entidade_tipo (entidade_tipo),
			KEY idx_entidade_id (entidade_id),
			KEY idx_proxima_execucao (proxima_execucao),
			KEY idx_estado (estado),
			KEY idx_entidade_tipo_id (entidade_tipo, entidade_id),
			KEY idx_estado_proxima (estado, proxima_execucao)
		) {$charset_collate};";
	}

	/**
	 * Get recorrencias table schema.
	 *
	 * @return string
	 */
	private static function get_recorrencias_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'recorrencias' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			entidade varchar(50) NOT NULL,
			regra text NOT NULL,
			inicio date NOT NULL,
			fim date DEFAULT NULL,
			valor_padrao decimal(12,2) DEFAULT NULL,
			meta longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_entidade (entidade),
			KEY idx_inicio (inicio),
			KEY idx_fim (fim)
		) {$charset_collate};";
	}

	/**
	 * Get settings table schema.
	 *
	 * @return string
	 */
	private static function get_settings_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'settings' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			setting_key varchar(100) NOT NULL,
			setting_value longtext DEFAULT NULL,
			setting_group varchar(50) DEFAULT 'general',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY uk_setting_key (setting_key),
			KEY idx_setting_group (setting_group)
		) {$charset_collate};";
	}

	/**
	 * Get logs table schema.
	 *
	 * @return string
	 */
	private static function get_logs_schema(): string {
		global $wpdb;
		$table_name = Tables::get_table_name( 'logs' );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE {$table_name} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id bigint(20) UNSIGNED DEFAULT NULL,
			entidade_tipo varchar(50) NOT NULL,
			entidade_id bigint(20) UNSIGNED DEFAULT NULL,
			acao varchar(50) NOT NULL,
			dados_antigos longtext DEFAULT NULL,
			dados_novos longtext DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent varchar(255) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_user_id (user_id),
			KEY idx_entidade_tipo (entidade_tipo),
			KEY idx_entidade_id (entidade_id),
			KEY idx_acao (acao),
			KEY idx_created_at (created_at),
			KEY idx_entidade_tipo_id (entidade_tipo, entidade_id),
			KEY idx_user_created (user_id, created_at),
			KEY idx_acao_created (acao, created_at),
			KEY idx_entidade_acao_created (entidade_tipo, acao, created_at)
		) {$charset_collate};";
	}
}

