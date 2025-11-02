<?php
/**
 * Reports generation system.
 *
 * @package GestorFinanceiro
 * @subpackage Features
 */

declare(strict_types=1);

namespace GestorFinanceiro\Features;

use GestorFinanceiro\DB\Repositories\ReceitasRepository;
use GestorFinanceiro\DB\Repositories\DespesasRepository;
use GestorFinanceiro\DB\Repositories\FuncionariosRepository;
use GestorFinanceiro\DB\Repositories\FornecedoresRepository;
use GestorFinanceiro\DB\Repositories\EstabelecimentosRepository;

/**
 * Handle monthly reports generation.
 */
class Reports {

	/**
	 * Generate monthly report.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<string, mixed>
	 */
	public function gerarMensal( int $month, int $year, ?int $estabelecimento_id = null ): array {
		$receitas_repo = new ReceitasRepository();
		$despesas_repo = new DespesasRepository();
		$funcionarios_repo = new FuncionariosRepository();
		$fornecedores_repo = new FornecedoresRepository();

		// Calculate date range for the month.
		$start_date = sprintf( '%04d-%02d-01', $year, $month );
		$days_in_month = (int) date( 't', strtotime( $start_date ) );
		$end_date = sprintf( '%04d-%02d-%02d', $year, $month, $days_in_month );

		// Get revenue totals.
		$receita_bruto = $receitas_repo->getMonthlyTotal( $month, $year, $estabelecimento_id, 'bruto' );
		$receita_taxas = $receitas_repo->getMonthlyTotal( $month, $year, $estabelecimento_id, 'taxas' );
		$receita_liquido = $receitas_repo->getMonthlyTotal( $month, $year, $estabelecimento_id, 'liquido' );

		// Get expenses total.
		$despesa_total = $despesas_repo->getMonthlyTotal( $month, $year, $estabelecimento_id );

		// Get salary expenses.
		$salario_total = $this->calculate_salary_total( $month, $year, $estabelecimento_id );

		// Get tax obligations.
		$impostos_total = $this->calculate_tax_obligations( $month, $year, $estabelecimento_id );

		// Calculate result.
		$resultado = $receita_liquido - $despesa_total - $salario_total - $impostos_total;

		// Generate salary report.
		$folha_salarial = $this->generate_salary_report( $month, $year, $estabelecimento_id );

		// Generate top suppliers ranking.
		$top_fornecedores = $this->generate_suppliers_ranking( $start_date, $end_date, $estabelecimento_id );

		// Build report.
		$report = array(
			'mes' => $month,
			'ano' => $year,
			'estabelecimento_id' => $estabelecimento_id,
			'receitas' => array(
				'bruto' => $receita_bruto,
				'taxas' => $receita_taxas,
				'liquido' => $receita_liquido,
			),
			'despesas' => array(
				'total' => $despesa_total,
			),
			'salarios' => array(
				'total' => $salario_total,
			),
			'impostos' => array(
				'total' => $impostos_total,
			),
			'resultado' => $resultado,
			'folha_salarial' => $folha_salarial,
			'top_fornecedores' => $top_fornecedores,
		);

		return $report;
	}

	/**
	 * Calculate salary total for month.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return float
	 */
	private function calculate_salary_total( int $month, int $year, ?int $estabelecimento_id = null ): float {
		$despesas_repo = new DespesasRepository();

		// Get salary expenses from despesas table (marked as paid).
		$filters = array(
			'pago' => 1,
		);

		if ( $estabelecimento_id ) {
			$filters['estabelecimento_id'] = $estabelecimento_id;
		}

		// Get all salary expenses in the month.
		$despesas = $despesas_repo->findByDateRange(
			sprintf( '%04d-%02d-01', $year, $month ),
			sprintf( '%04d-%02d-%02d', $year, $month, (int) date( 't', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) ) ),
			$estabelecimento_id
		);

		$total = 0.0;
		foreach ( $despesas as $despesa ) {
			if ( isset( $despesa['tipo'] ) && 'salario' === $despesa['tipo'] && isset( $despesa['pago'] ) && $despesa['pago'] == 1 ) {
				$total += (float) ( $despesa['valor'] ?? 0 );
			}
		}

		return $total;
	}

	/**
	 * Calculate tax obligations for month.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return float
	 */
	private function calculate_tax_obligations( int $month, int $year, ?int $estabelecimento_id = null ): float {
		$despesas_repo = new DespesasRepository();

		// Get obligations expenses (IVA, SS, IRS, etc.).
		$filters = array();
		if ( $estabelecimento_id ) {
			$filters['estabelecimento_id'] = $estabelecimento_id;
		}

		// Get all expenses in the month.
		$despesas = $despesas_repo->findByDateRange(
			sprintf( '%04d-%02d-01', $year, $month ),
			sprintf( '%04d-%02d-%02d', $year, $month, (int) date( 't', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) ) ),
			$estabelecimento_id
		);

		$tax_types = array( 'IVA', 'SS', 'IRS', 'IMI', 'obrigacao', 'imposto' );
		$total = 0.0;

		foreach ( $despesas as $despesa ) {
			$tipo = strtolower( $despesa['tipo'] ?? '' );
			$descricao = strtolower( $despesa['descricao'] ?? '' );

			// Check if it's a tax-related expense.
			$is_tax = false;
			foreach ( $tax_types as $tax_type ) {
				if ( false !== strpos( $tipo, $tax_type ) || false !== strpos( $descricao, $tax_type ) ) {
					$is_tax = true;
					break;
				}
			}

			if ( $is_tax ) {
				$total += (float) ( $despesa['valor'] ?? 0 );
			}
		}

		return $total;
	}

	/**
	 * Generate salary report.
	 *
	 * @param int $month Month (1-12).
	 * @param int $year Year.
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<array<string, mixed>>
	 */
	private function generate_salary_report( int $month, int $year, ?int $estabelecimento_id = null ): array {
		$funcionarios_repo = new FuncionariosRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();
		$despesas_repo = new DespesasRepository();

		$filters = array();
		if ( $estabelecimento_id ) {
			$filters['estabelecimento_id'] = $estabelecimento_id;
		}

		$funcionarios = $funcionarios_repo->findAll( $filters );
		$report = array();

		foreach ( $funcionarios as $funcionario ) {
			$funcionario_id = (int) $funcionario['id'];
			$estabelecimento_id_func = $funcionario['estabelecimento_id'] ? (int) $funcionario['estabelecimento_id'] : null;

			// Get salary expenses for this employee in the month.
			$despesas = $despesas_repo->findByDateRange(
				sprintf( '%04d-%02d-01', $year, $month ),
				sprintf( '%04d-%02d-%02d', $year, $month, (int) date( 't', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) ) ),
				$estabelecimento_id_func
			);

			$salario_mes = 0.0;
			$pago_count = 0;

			foreach ( $despesas as $despesa ) {
				if ( isset( $despesa['funcionario_id'] ) && (int) $despesa['funcionario_id'] === $funcionario_id ) {
					if ( isset( $despesa['tipo'] ) && 'salario' === $despesa['tipo'] ) {
						$salario_mes += (float) ( $despesa['valor'] ?? 0 );
						if ( isset( $despesa['pago'] ) && $despesa['pago'] == 1 ) {
							$pago_count++;
						}
					}
				}
			}

			// If no salary expense found, use base value for fixed employees.
			if ( 0.0 === $salario_mes && 'fixo' === $funcionario['tipo_pagamento'] ) {
				$salario_mes = (float) $funcionario['valor_base'];
			}

			$estabelecimento = $estabelecimento_id_func ? $estabelecimentos_repo->find( $estabelecimento_id_func ) : null;

			$report[] = array(
				'funcionario_id' => $funcionario_id,
				'nome' => $funcionario['nome'],
				'tipo_pagamento' => $funcionario['tipo_pagamento'],
				'valor_base' => (float) $funcionario['valor_base'],
				'salario_mes' => $salario_mes,
				'pago_count' => $pago_count,
				'estabelecimento' => $estabelecimento ? $estabelecimento['nome'] : null,
			);
		}

		return $report;
	}

	/**
	 * Generate top suppliers ranking.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return array<array<string, mixed>>
	 */
	private function generate_suppliers_ranking( string $start_date, string $end_date, ?int $estabelecimento_id = null ): array {
		$despesas_repo = new DespesasRepository();
		$fornecedores_repo = new FornecedoresRepository();

		// Get all expenses in date range.
		$despesas = $despesas_repo->findByDateRange( $start_date, $end_date, $estabelecimento_id );

		// Group by supplier and sum totals.
		$suppliers_totals = array();

		foreach ( $despesas as $despesa ) {
			if ( empty( $despesa['fornecedor_id'] ) ) {
				continue;
			}

			$fornecedor_id = (int) $despesa['fornecedor_id'];
			$valor = (float) ( $despesa['valor'] ?? 0 );

			if ( ! isset( $suppliers_totals[ $fornecedor_id ] ) ) {
				$suppliers_totals[ $fornecedor_id ] = array(
					'fornecedor_id' => $fornecedor_id,
					'total' => 0.0,
					'count' => 0,
				);
			}

			$suppliers_totals[ $fornecedor_id ]['total'] += $valor;
			$suppliers_totals[ $fornecedor_id ]['count']++;
		}

		// Fetch supplier details and build ranking.
		$ranking = array();
		foreach ( $suppliers_totals as $data ) {
			$fornecedor = $fornecedores_repo->find( $data['fornecedor_id'] );
			if ( ! $fornecedor ) {
				continue;
			}

			$ranking[] = array(
				'fornecedor_id' => $data['fornecedor_id'],
				'nome' => $fornecedor['nome'],
				'categoria' => $fornecedor['categoria'] ?? null,
				'total' => $data['total'],
				'count' => $data['count'],
			);
		}

		// Sort by total descending.
		usort(
			$ranking,
			function( $a, $b ) {
				return $b['total'] <=> $a['total'];
			}
		);

		// Return top 10.
		return array_slice( $ranking, 0, 10 );
	}

	/**
	 * Export report to CSV format.
	 *
	 * @param array<string, mixed> $report Report data.
	 * @return string CSV content.
	 */
	public function exportToCSV( array $report ): string {
		$lines = array();

		// Header.
		$lines[] = __( 'Relatório Mensal - Gestor Financeiro', 'gestor-financeiro' );
		$lines[] = sprintf(
			/* translators: %1$d: month, %2$d: year */
			__( 'Período: %1$d/%2$d', 'gestor-financeiro' ),
			$report['mes'],
			$report['ano']
		);
		$lines[] = '';

		// Results summary.
		$lines[] = __( 'Resultados', 'gestor-financeiro' );
		$lines[] = sprintf(
			/* translators: %s: amount */
			__( 'Receitas (Líquido): %s', 'gestor-financeiro' ),
			number_format( $report['receitas']['liquido'], 2, ',', ' ' )
		);
		$lines[] = sprintf(
			/* translators: %s: amount */
			__( 'Despesas: %s', 'gestor-financeiro' ),
			number_format( $report['despesas']['total'], 2, ',', ' ' )
		);
		$lines[] = sprintf(
			/* translators: %s: amount */
			__( 'Salários: %s', 'gestor-financeiro' ),
			number_format( $report['salarios']['total'], 2, ',', ' ' )
		);
		$lines[] = sprintf(
			/* translators: %s: amount */
			__( 'Impostos: %s', 'gestor-financeiro' ),
			number_format( $report['impostos']['total'], 2, ',', ' ' )
		);
		$lines[] = sprintf(
			/* translators: %s: amount */
			__( 'Resultado: %s', 'gestor-financeiro' ),
			number_format( $report['resultado'], 2, ',', ' ' )
		);
		$lines[] = '';

		// Salary report.
		$lines[] = __( 'Folha Salarial', 'gestor-financeiro' );
		$lines[] = implode( ';', array( __( 'Funcionário', 'gestor-financeiro' ), __( 'Tipo', 'gestor-financeiro' ), __( 'Valor Base', 'gestor-financeiro' ), __( 'Salário Mês', 'gestor-financeiro' ), __( 'Pago', 'gestor-financeiro' ), __( 'Estabelecimento', 'gestor-financeiro' ) ) );

		foreach ( $report['folha_salarial'] as $salario ) {
			$lines[] = implode(
				';',
				array(
					$salario['nome'],
					$salario['tipo_pagamento'],
					number_format( $salario['valor_base'], 2, ',', ' ' ),
					number_format( $salario['salario_mes'], 2, ',', ' ' ),
					$salario['pago_count'] > 0 ? __( 'Sim', 'gestor-financeiro' ) : __( 'Não', 'gestor-financeiro' ),
					$salario['estabelecimento'] ?? '',
				)
			);
		}
		$lines[] = '';

		// Top suppliers.
		$lines[] = __( 'Top Fornecedores', 'gestor-financeiro' );
		$lines[] = implode( ';', array( __( 'Fornecedor', 'gestor-financeiro' ), __( 'Categoria', 'gestor-financeiro' ), __( 'Total', 'gestor-financeiro' ), __( 'Nº Transações', 'gestor-financeiro' ) ) );

		foreach ( $report['top_fornecedores'] as $fornecedor ) {
			$lines[] = implode(
				';',
				array(
					$fornecedor['nome'],
					$fornecedor['categoria'] ?? '',
					number_format( $fornecedor['total'], 2, ',', ' ' ),
					(string) $fornecedor['count'],
				)
			);
		}

		// Convert to CSV with UTF-8 BOM for Excel.
		$csv = "\xEF\xBB\xBF" . implode( "\n", $lines );

		return $csv;
	}
}

