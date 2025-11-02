<?php
/**
 * MU-Plugin: Gestor Financeiro Bootstrap
 *
 * This file ensures the Gestor Financeiro plugin is loaded and seeds demo data.
 *
 * @package GestorFinanceiro
 */

declare(strict_types=1);

/**
 * Bootstrap the Gestor Financeiro plugin.
 */
function gestor_financeiro_bootstrap(): void {
	$plugin_file = WP_PLUGIN_DIR . '/gestor-financeiro/gestor-financeiro.php';

	if ( ! file_exists( $plugin_file ) ) {
		return;
	}

	// Ensure plugin is loaded.
	if ( ! function_exists( 'gestor_financeiro_init' ) ) {
		require_once $plugin_file;
	}
}
add_action( 'muplugins_loaded', 'gestor_financeiro_bootstrap' );

/**
 * Seed demo data for Gestor Financeiro.
 * This function is idempotent - it can be run multiple times safely.
 */
function gestor_financeiro_seed_data(): void {
	// Check if already seeded.
	$seeded = get_option( 'gestor_financeiro_seeded', false );
	if ( $seeded ) {
		return;
	}

	global $wpdb;

	// Check if plugin tables exist.
	$tables = new \GestorFinanceiro\DB\Tables();
	$table_name = $tables->get_table_name( 'estabelecimentos' );

	$table_exists = $wpdb->get_var(
		$wpdb->prepare(
			"SHOW TABLES LIKE %s",
			$table_name
		)
	);

	if ( ! $table_exists ) {
		// Run migrations if tables don't exist.
		\GestorFinanceiro\DB\Migrations::run();
	}

	// Repositories.
	$estabelecimentos_repo = new \GestorFinanceiro\DB\Repositories\EstabelecimentosRepository();
	$fornecedores_repo = new \GestorFinanceiro\DB\Repositories\FornecedoresRepository();
	$funcionarios_repo = new \GestorFinanceiro\DB\Repositories\FuncionariosRepository();
	$despesas_repo = new \GestorFinanceiro\DB\Repositories\DespesasRepository();
	$receitas_repo = new \GestorFinanceiro\DB\Repositories\ReceitasRepository();
	$obrigacoes_repo = new \GestorFinanceiro\DB\Repositories\ObrigacoesRepository();
	$settings_repo = new \GestorFinanceiro\DB\Repositories\SettingsRepository();

	// Check if data already exists (idempotent check).
	$existing = $estabelecimentos_repo->findAll();
	if ( ! empty( $existing ) ) {
		update_option( 'gestor_financeiro_seeded', true );
		return;
	}

	// 1. Create establishments (4 restaurants/bars + 2 apartments).
	$estabelecimentos = array(
		array(
			'nome' => 'Restaurante Central',
			'tipo' => 'restaurante',
			'dia_renda' => 5,
			'ativo' => 1,
		),
		array(
			'nome' => 'Bar do Porto',
			'tipo' => 'bar',
			'dia_renda' => 10,
			'ativo' => 1,
		),
		array(
			'nome' => 'Restaurante Litoral',
			'tipo' => 'restaurante',
			'dia_renda' => 15,
			'ativo' => 1,
		),
		array(
			'nome' => 'Bar Noturno',
			'tipo' => 'bar',
			'dia_renda' => 20,
			'ativo' => 1,
		),
		array(
			'nome' => 'Apartamento T1 - Centro',
			'tipo' => 'apartamento',
			'dia_renda' => 1,
			'ativo' => 1,
		),
		array(
			'nome' => 'Apartamento T2 - Praia',
			'tipo' => 'apartamento',
			'dia_renda' => 5,
			'ativo' => 1,
		),
	);

	$estabelecimento_ids = array();
	foreach ( $estabelecimentos as $estabelecimento ) {
		$id = $estabelecimentos_repo->create( $estabelecimento );
		if ( $id ) {
			$estabelecimento_ids[] = $id;
		}
	}

	// 2. Create suppliers.
	$fornecedores = array(
		array(
			'nome' => 'Supermercado Local',
			'categoria' => 'Alimentação',
			'nif' => '123456789',
			'contacto' => 'contato@supermercado.local',
			'morada' => 'Rua Principal, 123',
		),
		array(
			'nome' => 'Distribuidora de Bebidas',
			'categoria' => 'Bebidas',
			'nif' => '987654321',
			'contacto' => 'vendas@bebidas.pt',
			'morada' => 'Avenida Comercial, 456',
		),
		array(
			'nome' => 'Fornecedor de Carne',
			'categoria' => 'Carnes',
			'nif' => '555666777',
			'contacto' => 'info@carne.pt',
			'morada' => 'Zona Industrial, 789',
		),
		array(
			'nome' => 'Serviços de Limpeza',
			'categoria' => 'Serviços',
			'nif' => '111222333',
			'contacto' => 'limpeza@servicos.pt',
			'morada' => 'Rua dos Serviços, 10',
		),
		array(
			'nome' => 'Empresa de Manutenção',
			'categoria' => 'Manutenção',
			'nif' => '444555666',
			'contacto' => 'manutencao@empresa.pt',
			'morada' => 'Avenida Técnica, 20',
		),
	);

	$fornecedor_ids = array();
	foreach ( $fornecedores as $fornecedor ) {
		$id = $fornecedores_repo->create( $fornecedor );
		if ( $id ) {
			$fornecedor_ids[] = $id;
		}
	}

	// 3. Create employees.
	$funcionarios = array(
		array(
			'nome' => 'João Silva',
			'estabelecimento_id' => $estabelecimento_ids[0] ?? null,
			'tipo_pagamento' => 'fixo',
			'valor_base' => 1200.00,
			'iban' => 'PT50001234567890123456789',
		),
		array(
			'nome' => 'Maria Santos',
			'estabelecimento_id' => $estabelecimento_ids[0] ?? null,
			'tipo_pagamento' => 'fixo',
			'valor_base' => 1100.00,
			'iban' => 'PT50001234567890123456790',
		),
		array(
			'nome' => 'Pedro Costa',
			'estabelecimento_id' => $estabelecimento_ids[1] ?? null,
			'tipo_pagamento' => 'diario',
			'valor_base' => 50.00,
			'iban' => 'PT50001234567890123456791',
		),
		array(
			'nome' => 'Ana Ferreira',
			'estabelecimento_id' => $estabelecimento_ids[2] ?? null,
			'tipo_pagamento' => 'fixo',
			'valor_base' => 1250.00,
			'iban' => 'PT50001234567890123456792',
		),
		array(
			'nome' => 'Carlos Oliveira',
			'estabelecimento_id' => $estabelecimento_ids[3] ?? null,
			'tipo_pagamento' => 'diario',
			'valor_base' => 60.00,
			'iban' => 'PT50001234567890123456793',
		),
	);

	$funcionario_ids = array();
	foreach ( $funcionarios as $funcionario ) {
		$id = $funcionarios_repo->create( $funcionario );
		if ( $id ) {
			$funcionario_ids[] = $id;
		}
	}

	// 4. Create obligations.
	$obrigacoes = array(
		array(
			'nome' => 'IVA - Trimestral',
			'descricao' => 'Pagamento de IVA trimestral',
			'valor' => 2500.00,
			'periodicidade' => 'trimestral',
			'vencimento' => date( 'Y-m-d', strtotime( '+30 days' ) ),
		),
		array(
			'nome' => 'SS - Mensal',
			'descricao' => 'Segurança Social dos funcionários',
			'valor' => 800.00,
			'periodicidade' => 'mensal',
			'vencimento' => date( 'Y-m-d', strtotime( '+15 days' ) ),
		),
		array(
			'nome' => 'IRS - Anual',
			'descricao' => 'IRS anual',
			'valor' => 5000.00,
			'periodicidade' => 'anual',
			'vencimento' => date( 'Y-m-d', strtotime( '+6 months' ) ),
		),
	);

	$obrigacao_ids = array();
	foreach ( $obrigacoes as $obrigacao ) {
		$id = $obrigacoes_repo->create( $obrigacao );
		if ( $id ) {
			$obrigacao_ids[] = $id;
		}
	}

	// 5. Create sample expenses for current month.
	$current_month = (int) current_time( 'n' );
	$current_year = (int) current_time( 'Y' );
	$current_date = current_time( 'Y-m-d' );

	$sample_despesas = array(
		array(
			'data' => $current_date,
			'estabelecimento_id' => $estabelecimento_ids[0] ?? null,
			'fornecedor_id' => $fornecedor_ids[0] ?? null,
			'tipo' => 'alimentação',
			'descricao' => 'Compras semanais',
			'valor' => 450.00,
			'vencimento' => date( 'Y-m-d', strtotime( '+7 days' ) ),
			'pago' => 0,
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-5 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[1] ?? null,
			'fornecedor_id' => $fornecedor_ids[1] ?? null,
			'tipo' => 'bebidas',
			'descricao' => 'Stock de bebidas',
			'valor' => 320.00,
			'vencimento' => date( 'Y-m-d', strtotime( '+10 days' ) ),
			'pago' => 0,
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-3 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[0] ?? null,
			'fornecedor_id' => $fornecedor_ids[2] ?? null,
			'tipo' => 'carnes',
			'descricao' => 'Fornecimento de carnes',
			'valor' => 280.00,
			'vencimento' => date( 'Y-m-d', strtotime( '+5 days' ) ),
			'pago' => 1,
			'pago_em' => current_time( 'mysql' ),
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-10 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[4] ?? null,
			'tipo' => 'condomínio',
			'descricao' => 'Condomínio mensal',
			'valor' => 150.00,
			'vencimento' => date( 'Y-m-d', strtotime( '+2 days' ) ),
			'pago' => 0,
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-8 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[5] ?? null,
			'tipo' => 'IMI',
			'descricao' => 'IMI mensal',
			'valor' => 85.00,
			'vencimento' => date( 'Y-m-d', strtotime( '+5 days' ) ),
			'pago' => 0,
		),
	);

	foreach ( $sample_despesas as $despesa ) {
		$despesas_repo->create( $despesa );
	}

	// 6. Create sample revenues for current month.
	$sample_receitas = array(
		array(
			'data' => $current_date,
			'estabelecimento_id' => $estabelecimento_ids[0] ?? null,
			'bruto' => 2500.00,
			'taxas' => 125.00,
			'liquido' => 2375.00,
			'notas' => 'Vendas do dia',
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-1 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[1] ?? null,
			'bruto' => 1800.00,
			'taxas' => 90.00,
			'liquido' => 1710.00,
			'notas' => 'Vendas do dia anterior',
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-3 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[2] ?? null,
			'bruto' => 3200.00,
			'taxas' => 160.00,
			'liquido' => 3040.00,
			'notas' => 'Vendas do fim de semana',
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-2 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[4] ?? null,
			'bruto' => 650.00,
			'taxas' => 0.00,
			'liquido' => 650.00,
			'notas' => 'Renda mensal',
		),
		array(
			'data' => date( 'Y-m-d', strtotime( '-5 days' ) ),
			'estabelecimento_id' => $estabelecimento_ids[5] ?? null,
			'bruto' => 850.00,
			'taxas' => 0.00,
			'liquido' => 850.00,
			'notas' => 'Renda mensal',
		),
	);

	foreach ( $sample_receitas as $receita ) {
		$receitas_repo->create( $receita );
	}

	// 7. Create default settings.
	$default_settings = array(
		'alerts_email' => get_option( 'admin_email' ),
		'cron_hour' => 8,
		'currency' => 'EUR',
	);

	foreach ( $default_settings as $key => $value ) {
		$settings_repo->set( $key, $value, 'general' );
	}

	// Mark as seeded.
	update_option( 'gestor_financeiro_seeded', true );
}

// Run seed data on admin init (only in admin area).
if ( is_admin() ) {
	add_action( 'admin_init', 'gestor_financeiro_seed_data', 20 );
}

