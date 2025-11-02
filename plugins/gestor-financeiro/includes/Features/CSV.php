<?php
/**
 * CSV import/export system.
 *
 * @package GestorFinanceiro
 * @subpackage Features
 */

declare(strict_types=1);

namespace GestorFinanceiro\Features;

use GestorFinanceiro\DB\Repositories\DespesasRepository;
use GestorFinanceiro\DB\Repositories\ReceitasRepository;
use GestorFinanceiro\DB\Repositories\EstabelecimentosRepository;
use GestorFinanceiro\Helpers;

/**
 * Handle CSV import and export operations.
 */
class CSV {

	/**
	 * Batch size for import operations.
	 */
	private const BATCH_SIZE = 100;

	/**
	 * Export expenses or revenue to CSV.
	 *
	 * @param string $type 'despesas' or 'receitas'.
	 * @param string|null $start_date Start date (YYYY-MM-DD).
	 * @param string|null $end_date End date (YYYY-MM-DD).
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return string CSV content.
	 */
	public function export( string $type, ?string $start_date = null, ?string $end_date = null, ?int $estabelecimento_id = null ): string {
		if ( ! in_array( $type, array( 'despesas', 'receitas' ), true ) ) {
			return '';
		}

		// Default to last 3 months if no dates provided.
		if ( ! $start_date ) {
			$start_date = date( 'Y-m-d', strtotime( '-3 months' ) );
		}
		if ( ! $end_date ) {
			$end_date = current_time( 'Y-m-d' );
		}

		if ( 'despesas' === $type ) {
			return $this->export_despesas( $start_date, $end_date, $estabelecimento_id );
		} else {
			return $this->export_receitas( $start_date, $end_date, $estabelecimento_id );
		}
	}

	/**
	 * Export expenses to CSV.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return string CSV content.
	 */
	private function export_despesas( string $start_date, string $end_date, ?int $estabelecimento_id = null ): string {
		$repo = new DespesasRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		// Get expenses in date range.
		$despesas = $repo->findByDateRange( $start_date, $end_date, $estabelecimento_id );

		// Build CSV headers.
		$headers = array(
			__( 'Data', 'gestor-financeiro' ),
			__( 'Estabelecimento', 'gestor-financeiro' ),
			__( 'Fornecedor', 'gestor-financeiro' ),
			__( 'Funcionário', 'gestor-financeiro' ),
			__( 'Tipo', 'gestor-financeiro' ),
			__( 'Descrição', 'gestor-financeiro' ),
			__( 'Valor', 'gestor-financeiro' ),
			__( 'Vencimento', 'gestor-financeiro' ),
			__( 'Pago', 'gestor-financeiro' ),
			__( 'Pago em', 'gestor-financeiro' ),
			__( 'Notas', 'gestor-financeiro' ),
		);

		$lines = array();
		$lines[] = implode( ';', $headers );

		// Build CSV rows.
		foreach ( $despesas as $despesa ) {
			$estabelecimento = null;
			if ( ! empty( $despesa['estabelecimento_id'] ) ) {
				$estabelecimento = $estabelecimentos_repo->find( (int) $despesa['estabelecimento_id'] );
			}

			$row = array(
				$this->normalize_date_export( $despesa['data'] ?? '' ),
				$estabelecimento ? $estabelecimento['nome'] : '',
				'', // Fornecedor name would need join.
				'', // Funcionário name would need join.
				$despesa['tipo'] ?? '',
				$despesa['descricao'] ?? '',
				$this->normalize_number_export( (float) ( $despesa['valor'] ?? 0 ) ),
				$this->normalize_date_export( $despesa['vencimento'] ?? '' ),
				( isset( $despesa['pago'] ) && $despesa['pago'] == 1 ) ? __( 'Sim', 'gestor-financeiro' ) : __( 'Não', 'gestor-financeiro' ),
				$this->normalize_date_export( $despesa['pago_em'] ?? '' ),
				$despesa['notas'] ?? '',
			);

			$lines[] = implode( ';', array_map( array( $this, 'escape_csv_field' ), $row ) );
		}

		// Convert to CSV with UTF-8 BOM for Excel.
		$csv = "\xEF\xBB\xBF" . implode( "\n", $lines );

		return $csv;
	}

	/**
	 * Export revenue to CSV.
	 *
	 * @param string $start_date Start date (YYYY-MM-DD).
	 * @param string $end_date End date (YYYY-MM-DD).
	 * @param int|null $estabelecimento_id Optional establishment ID.
	 * @return string CSV content.
	 */
	private function export_receitas( string $start_date, string $end_date, ?int $estabelecimento_id = null ): string {
		$repo = new ReceitasRepository();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		// Get revenue in date range.
		$receitas = $repo->findByDateRange( $start_date, $end_date, $estabelecimento_id );

		// Build CSV headers.
		$headers = array(
			__( 'Data', 'gestor-financeiro' ),
			__( 'Estabelecimento', 'gestor-financeiro' ),
			__( 'Bruto', 'gestor-financeiro' ),
			__( 'Taxas', 'gestor-financeiro' ),
			__( 'Líquido', 'gestor-financeiro' ),
			__( 'Notas', 'gestor-financeiro' ),
		);

		$lines = array();
		$lines[] = implode( ';', $headers );

		// Build CSV rows.
		foreach ( $receitas as $receita ) {
			$estabelecimento = null;
			if ( ! empty( $receita['estabelecimento_id'] ) ) {
				$estabelecimento = $estabelecimentos_repo->find( (int) $receita['estabelecimento_id'] );
			}

			$row = array(
				$this->normalize_date_export( $receita['data'] ?? '' ),
				$estabelecimento ? $estabelecimento['nome'] : '',
				$this->normalize_number_export( (float) ( $receita['bruto'] ?? 0 ) ),
				$this->normalize_number_export( (float) ( $receita['taxas'] ?? 0 ) ),
				$this->normalize_number_export( (float) ( $receita['liquido'] ?? 0 ) ),
				$receita['notas'] ?? '',
			);

			$lines[] = implode( ';', array_map( array( $this, 'escape_csv_field' ), $row ) );
		}

		// Convert to CSV with UTF-8 BOM for Excel.
		$csv = "\xEF\xBB\xBF" . implode( "\n", $lines );

		return $csv;
	}

	/**
	 * Import expenses or revenue from CSV.
	 *
	 * @param string $csv_content CSV content.
	 * @param string $type 'despesas' or 'receitas'.
	 * @return array<string, mixed> Result with preview, validation errors, and imported count.
	 */
	public function import( string $csv_content, string $type ): array {
		if ( ! in_array( $type, array( 'despesas', 'receitas' ), true ) ) {
			return array(
				'success' => false,
				'error' => __( 'Tipo inválido. Use "despesas" ou "receitas".', 'gestor-financeiro' ),
				'preview' => array(),
				'errors' => array(),
				'imported' => 0,
			);
		}

		// Parse CSV.
		$rows = $this->parse_csv( $csv_content );
		if ( empty( $rows ) ) {
			return array(
				'success' => false,
				'error' => __( 'CSV vazio ou inválido.', 'gestor-financeiro' ),
				'preview' => array(),
				'errors' => array(),
				'imported' => 0,
			);
		}

		// Remove header row.
		$header = array_shift( $rows );

		// Validate and parse rows.
		$preview = array();
		$errors = array();

		foreach ( $rows as $row_index => $row ) {
			$line_number = $row_index + 2; // +2 because header is line 1 and array is 0-based.

			if ( 'despesas' === $type ) {
				$parsed = $this->parse_despesa_row( $row, $header, $line_number );
			} else {
				$parsed = $this->parse_receita_row( $row, $header, $line_number );
			}

			if ( ! empty( $parsed['errors'] ) ) {
				$errors[ $line_number ] = $parsed['errors'];
			}

			if ( ! empty( $parsed['data'] ) ) {
				$preview[] = $parsed['data'];
			}
		}

		return array(
			'success' => true,
			'preview' => $preview,
			'errors' => $errors,
			'imported' => 0, // Will be set after actual import.
		);
	}

	/**
	 * Execute import (after preview).
	 *
	 * @param array<array<string, mixed>> $preview_data Preview data to import.
	 * @param string $type 'despesas' or 'receitas'.
	 * @return array<string, mixed> Result with imported count and errors.
	 */
	public function execute_import( array $preview_data, string $type ): array {
		if ( ! in_array( $type, array( 'despesas', 'receitas' ), true ) ) {
			return array(
				'success' => false,
				'error' => __( 'Tipo inválido.', 'gestor-financeiro' ),
				'imported' => 0,
				'errors' => array(),
			);
		}

		$repo = 'despesas' === $type ? new DespesasRepository() : new ReceitasRepository();
		$imported = 0;
		$errors = array();

		// Process in batches.
		$batches = array_chunk( $preview_data, self::BATCH_SIZE );

		foreach ( $batches as $batch_index => $batch ) {
			foreach ( $batch as $row_index => $row ) {
				try {
					$id = $repo->create( $row );
					if ( false !== $id ) {
						$imported++;
					} else {
						$errors[] = array(
							'row' => ( $batch_index * self::BATCH_SIZE ) + $row_index + 1,
							'error' => __( 'Erro ao inserir registo.', 'gestor-financeiro' ),
						);
					}
				} catch ( \Exception $e ) {
					$errors[] = array(
						'row' => ( $batch_index * self::BATCH_SIZE ) + $row_index + 1,
						'error' => $e->getMessage(),
					);
				}
			}
		}

		return array(
			'success' => true,
			'imported' => $imported,
			'errors' => $errors,
		);
	}

	/**
	 * Parse CSV content.
	 *
	 * @param string $csv_content CSV content.
	 * @return array<array<string>> Parsed rows.
	 */
	private function parse_csv( string $csv_content ): array {
		// Remove UTF-8 BOM if present.
		if ( strpos( $csv_content, "\xEF\xBB\xBF" ) === 0 ) {
			$csv_content = substr( $csv_content, 3 );
		}

		$lines = explode( "\n", $csv_content );
		$rows = array();

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			// Parse CSV line with semicolon separator.
			$fields = str_getcsv( $line, ';' );
			if ( ! empty( $fields ) ) {
				$rows[] = $fields;
			}
		}

		return $rows;
	}

	/**
	 * Parse expense row from CSV.
	 *
	 * @param array<string> $row CSV row.
	 * @param array<string> $header CSV header.
	 * @param int $line_number Line number for error reporting.
	 * @return array<string, mixed> Parsed data with errors.
	 */
	private function parse_despesa_row( array $row, array $header, int $line_number ): array {
		$data = array();
		$errors = array();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		// Map header to row.
		$mapped = array();
		foreach ( $header as $index => $header_name ) {
			$mapped[ strtolower( trim( $header_name ) ) ] = isset( $row[ $index ] ) ? trim( $row[ $index ] ) : '';
		}

		// Parse required fields.
		$date_key = $this->find_header_key( $mapped, array( 'data', 'date' ) );
		if ( $date_key && ! empty( $mapped[ $date_key ] ) ) {
			$normalized_date = $this->normalize_date_import( $mapped[ $date_key ] );
			if ( $normalized_date ) {
				$data['data'] = $normalized_date;
			} else {
				$errors[] = sprintf( __( 'Data inválida: %s', 'gestor-financeiro' ), $mapped[ $date_key ] );
			}
		} else {
			$errors[] = __( 'Data é obrigatória.', 'gestor-financeiro' );
		}

		// Estabelecimento.
		$estabelecimento_key = $this->find_header_key( $mapped, array( 'estabelecimento', 'establishment' ) );
		if ( $estabelecimento_key && ! empty( $mapped[ $estabelecimento_key ] ) ) {
			$estabelecimento = $estabelecimentos_repo->findByName( $mapped[ $estabelecimento_key ] );
			if ( $estabelecimento ) {
				$data['estabelecimento_id'] = (int) $estabelecimento['id'];
			} else {
				$errors[] = sprintf( __( 'Estabelecimento não encontrado: %s', 'gestor-financeiro' ), $mapped[ $estabelecimento_key ] );
			}
		}

		// Valor.
		$valor_key = $this->find_header_key( $mapped, array( 'valor', 'value', 'amount' ) );
		if ( $valor_key && ! empty( $mapped[ $valor_key ] ) ) {
			$normalized_value = $this->normalize_number_import( $mapped[ $valor_key ] );
			if ( $normalized_value !== null && $normalized_value > 0 ) {
				$data['valor'] = $normalized_value;
			} else {
				$errors[] = sprintf( __( 'Valor inválido: %s', 'gestor-financeiro' ), $mapped[ $valor_key ] );
			}
		} else {
			$errors[] = __( 'Valor é obrigatório.', 'gestor-financeiro' );
		}

		// Optional fields.
		$tipo_key = $this->find_header_key( $mapped, array( 'tipo', 'type' ) );
		if ( $tipo_key && ! empty( $mapped[ $tipo_key ] ) ) {
			$data['tipo'] = sanitize_text_field( $mapped[ $tipo_key ] );
		}

		$descricao_key = $this->find_header_key( $mapped, array( 'descrição', 'descricao', 'description', 'desc' ) );
		if ( $descricao_key && ! empty( $mapped[ $descricao_key ] ) ) {
			$data['descricao'] = sanitize_text_field( $mapped[ $descricao_key ] );
		}

		$vencimento_key = $this->find_header_key( $mapped, array( 'vencimento', 'due date', 'due' ) );
		if ( $vencimento_key && ! empty( $mapped[ $vencimento_key ] ) ) {
			$normalized_date = $this->normalize_date_import( $mapped[ $vencimento_key ] );
			if ( $normalized_date ) {
				$data['vencimento'] = $normalized_date;
			}
		}

		$pago_key = $this->find_header_key( $mapped, array( 'pago', 'paid' ) );
		if ( $pago_key && ! empty( $mapped[ $pago_key ] ) ) {
			$pago_value = strtolower( trim( $mapped[ $pago_key ] ) );
			$data['pago'] = in_array( $pago_value, array( 'sim', 'yes', 's', 'y', '1', 'true' ), true ) ? 1 : 0;
			if ( $data['pago'] == 1 ) {
				$data['pago_em'] = current_time( 'mysql' );
			}
		}

		$notas_key = $this->find_header_key( $mapped, array( 'notas', 'notes', 'note' ) );
		if ( $notas_key && ! empty( $mapped[ $notas_key ] ) ) {
			$data['notas'] = sanitize_textarea_field( $mapped[ $notas_key ] );
		}

		return array(
			'data' => $data,
			'errors' => $errors,
		);
	}

	/**
	 * Parse revenue row from CSV.
	 *
	 * @param array<string> $row CSV row.
	 * @param array<string> $header CSV header.
	 * @param int $line_number Line number for error reporting.
	 * @return array<string, mixed> Parsed data with errors.
	 */
	private function parse_receita_row( array $row, array $header, int $line_number ): array {
		$data = array();
		$errors = array();
		$estabelecimentos_repo = new EstabelecimentosRepository();

		// Map header to row.
		$mapped = array();
		foreach ( $header as $index => $header_name ) {
			$mapped[ strtolower( trim( $header_name ) ) ] = isset( $row[ $index ] ) ? trim( $row[ $index ] ) : '';
		}

		// Parse required fields.
		$date_key = $this->find_header_key( $mapped, array( 'data', 'date' ) );
		if ( $date_key && ! empty( $mapped[ $date_key ] ) ) {
			$normalized_date = $this->normalize_date_import( $mapped[ $date_key ] );
			if ( $normalized_date ) {
				$data['data'] = $normalized_date;
			} else {
				$errors[] = sprintf( __( 'Data inválida: %s', 'gestor-financeiro' ), $mapped[ $date_key ] );
			}
		} else {
			$errors[] = __( 'Data é obrigatória.', 'gestor-financeiro' );
		}

		// Estabelecimento.
		$estabelecimento_key = $this->find_header_key( $mapped, array( 'estabelecimento', 'establishment' ) );
		if ( $estabelecimento_key && ! empty( $mapped[ $estabelecimento_key ] ) ) {
			$estabelecimento = $estabelecimentos_repo->findByName( $mapped[ $estabelecimento_key ] );
			if ( $estabelecimento ) {
				$data['estabelecimento_id'] = (int) $estabelecimento['id'];
			} else {
				$errors[] = sprintf( __( 'Estabelecimento não encontrado: %s', 'gestor-financeiro' ), $mapped[ $estabelecimento_key ] );
			}
		}

		// Bruto.
		$bruto_key = $this->find_header_key( $mapped, array( 'bruto', 'gross' ) );
		if ( $bruto_key && ! empty( $mapped[ $bruto_key ] ) ) {
			$normalized_value = $this->normalize_number_import( $mapped[ $bruto_key ] );
			if ( $normalized_value !== null && $normalized_value >= 0 ) {
				$data['bruto'] = $normalized_value;
			}
		}

		// Taxas.
		$taxas_key = $this->find_header_key( $mapped, array( 'taxas', 'fees', 'tax' ) );
		if ( $taxas_key && ! empty( $mapped[ $taxas_key ] ) ) {
			$normalized_value = $this->normalize_number_import( $mapped[ $taxas_key ] );
			if ( $normalized_value !== null && $normalized_value >= 0 ) {
				$data['taxas'] = $normalized_value;
			}
		}

		// Líquido (calculated if not provided).
		$liquido_key = $this->find_header_key( $mapped, array( 'liquido', 'liquido', 'net' ) );
		if ( $liquido_key && ! empty( $mapped[ $liquido_key ] ) ) {
			$normalized_value = $this->normalize_number_import( $mapped[ $liquido_key ] );
			if ( $normalized_value !== null && $normalized_value >= 0 ) {
				$data['liquido'] = $normalized_value;
			}
		} elseif ( isset( $data['bruto'] ) && isset( $data['taxas'] ) ) {
			$data['liquido'] = $data['bruto'] - $data['taxas'];
		} else {
			$errors[] = __( 'Valor líquido é obrigatório ou bruto e taxas devem ser fornecidos.', 'gestor-financeiro' );
		}

		// Notas.
		$notas_key = $this->find_header_key( $mapped, array( 'notas', 'notes', 'note' ) );
		if ( $notas_key && ! empty( $mapped[ $notas_key ] ) ) {
			$data['notas'] = sanitize_textarea_field( $mapped[ $notas_key ] );
		}

		return array(
			'data' => $data,
			'errors' => $errors,
		);
	}

	/**
	 * Find header key from possible alternatives.
	 *
	 * @param array<string, string> $mapped Mapped headers.
	 * @param array<string> $alternatives Possible key names.
	 * @return string|null Found key or null.
	 */
	private function find_header_key( array $mapped, array $alternatives ): ?string {
		foreach ( $alternatives as $alt ) {
			if ( isset( $mapped[ $alt ] ) ) {
				return $alt;
			}
		}
		return null;
	}

	/**
	 * Normalize date for export (PT-PT format: DD/MM/YYYY).
	 *
	 * @param string $date Date in YYYY-MM-DD format.
	 * @return string Formatted date or empty string.
	 */
	private function normalize_date_export( string $date ): string {
		if ( empty( $date ) || $date === '0000-00-00' ) {
			return '';
		}

		$timestamp = strtotime( $date );
		if ( false === $timestamp ) {
			return '';
		}

		return date( 'd/m/Y', $timestamp );
	}

	/**
	 * Normalize date for import (accepts DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD).
	 *
	 * @param string $date Date string.
	 * @return string|null Normalized date in YYYY-MM-DD format or null.
	 */
	private function normalize_date_import( string $date ): ?string {
		if ( empty( $date ) ) {
			return null;
		}

		// Try YYYY-MM-DD format first.
		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches ) ) {
			if ( checkdate( (int) $matches[2], (int) $matches[3], (int) $matches[1] ) ) {
				return $date;
			}
		}

		// Try DD/MM/YYYY format.
		if ( preg_match( '/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches ) ) {
			if ( checkdate( (int) $matches[2], (int) $matches[1], (int) $matches[3] ) ) {
				return sprintf( '%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1] );
			}
		}

		// Try DD-MM-YYYY format.
		if ( preg_match( '/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches ) ) {
			if ( checkdate( (int) $matches[2], (int) $matches[1], (int) $matches[3] ) ) {
				return sprintf( '%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1] );
			}
		}

		return null;
	}

	/**
	 * Normalize number for export (PT-PT format: 1.234,56).
	 *
	 * @param float $number Number to format.
	 * @return string Formatted number.
	 */
	private function normalize_number_export( float $number ): string {
		return number_format( $number, 2, ',', ' ' );
	}

	/**
	 * Normalize number for import (accepts PT-PT: 1.234,56 or EN: 1,234.56).
	 *
	 * @param string $number Number string.
	 * @return float|null Parsed number or null.
	 */
	private function normalize_number_import( string $number ): ?float {
		if ( empty( $number ) ) {
			return null;
		}

		// Remove spaces.
		$number = trim( str_replace( ' ', '', $number ) );

		// Empty after trimming.
		if ( empty( $number ) ) {
			return null;
		}

		// Try EN format (dot as decimal, comma as thousands) - check for dot and comma pattern.
		if ( preg_match( '/^[\d,]+\.\d+$/', $number ) ) {
			$number = str_replace( ',', '', $number );
			return (float) $number;
		}

		// Try PT-PT format (comma as decimal, space/dot as thousands) - check for comma pattern.
		if ( preg_match( '/^[\d\s\.]+,\d+$/', $number ) ) {
			$number = str_replace( array( ' ', '.' ), '', $number );
			$number = str_replace( ',', '.', $number );
			return (float) $number;
		}

		// Try simple number with comma as decimal.
		if ( strpos( $number, ',' ) !== false && strpos( $number, '.' ) === false ) {
			$number = str_replace( ',', '.', $number );
			if ( is_numeric( $number ) ) {
				return (float) $number;
			}
		}

		// Try simple number.
		if ( is_numeric( $number ) ) {
			return (float) $number;
		}

		return null;
	}

	/**
	 * Escape CSV field.
	 *
	 * @param string $field Field value.
	 * @return string Escaped field.
	 */
	private function escape_csv_field( string $field ): string {
		// If field contains semicolon, newline, or double quote, wrap in quotes and escape quotes.
		if ( strpos( $field, ';' ) !== false || strpos( $field, "\n" ) !== false || strpos( $field, '"' ) !== false ) {
			$field = '"' . str_replace( '"', '""', $field ) . '"';
		}
		return $field;
	}
}

