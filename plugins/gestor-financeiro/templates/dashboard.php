<?php
/**
 * Dashboard template.
 *
 * @package GestorFinanceiro
 * @var string $nonce REST API nonce.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="gf-app" data-nonce="<?php echo esc_attr( $nonce ); ?>">
	<div class="gf-dashboard-header">
		<h1><?php echo esc_html__( 'Gestor Financeiro', 'gestor-financeiro' ); ?></h1>
		<a href="#" class="gf-help-link" data-action="show-help" title="<?php echo esc_attr__( 'Ver Instruções', 'gestor-financeiro' ); ?>">
			<span class="gf-help-icon">?</span>
			<?php echo esc_html__( 'Ajuda', 'gestor-financeiro' ); ?>
		</a>
	</div>

	<div class="gf-dashboard-tabs">
		<button class="gf-tab-button active" data-tab="resumo">
			<?php echo esc_html__( 'Resumo', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="movimentos">
			<?php echo esc_html__( 'Movimentos', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="calendario">
			<?php echo esc_html__( 'Calendário', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="salarios">
			<?php echo esc_html__( 'Salários', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="relatorios">
			<?php echo esc_html__( 'Relatórios', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="definicoes">
			<?php echo esc_html__( 'Definições', 'gestor-financeiro' ); ?>
		</button>
		<button class="gf-tab-button" data-tab="ajuda">
			<?php echo esc_html__( 'Ajuda', 'gestor-financeiro' ); ?>
		</button>
	</div>

	<div class="gf-dashboard-content">
		<!-- Tab: Resumo -->
		<div class="gf-tab-content active" data-tab-content="resumo">
			<div class="gf-summary-cards">
				<div class="gf-card">
					<h3><?php echo esc_html__( 'Receita do mês', 'gestor-financeiro' ); ?></h3>
					<div class="gf-value" data-summary="receita_mes">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__( 'Despesas do mês', 'gestor-financeiro' ); ?></h3>
					<div class="gf-value" data-summary="despesas_mes">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__( 'Resultado', 'gestor-financeiro' ); ?></h3>
					<div class="gf-value" data-summary="resultado">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__( 'Por pagar', 'gestor-financeiro' ); ?></h3>
					<div class="gf-value" data-summary="por_pagar">0,00 €</div>
				</div>
			</div>
		</div>

		<!-- Tab: Movimentos -->
		<div class="gf-tab-content" data-tab-content="movimentos">
			<div class="gf-movimentos-controls">
				<button class="gf-button gf-button-primary" data-action="add-despesa">
					<?php echo esc_html__( 'Adicionar Despesa', 'gestor-financeiro' ); ?>
				</button>
				<button class="gf-button gf-button-primary" data-action="add-receita">
					<?php echo esc_html__( 'Adicionar Receita', 'gestor-financeiro' ); ?>
				</button>
				<div class="gf-filters">
					<select id="gf-filter-estabelecimento">
						<option value=""><?php echo esc_html__( 'Todos os estabelecimentos', 'gestor-financeiro' ); ?></option>
					</select>
					<input type="date" id="gf-filter-start-date" placeholder="<?php echo esc_attr__( 'Data início', 'gestor-financeiro' ); ?>">
					<input type="date" id="gf-filter-end-date" placeholder="<?php echo esc_attr__( 'Data fim', 'gestor-financeiro' ); ?>">
				</div>
			</div>

			<div class="gf-movimentos-list">
				<div class="gf-loading"><?php echo esc_html__( 'A carregar...', 'gestor-financeiro' ); ?></div>
			</div>
		</div>

		<!-- Tab: Calendário -->
		<div class="gf-tab-content" data-tab-content="calendario">
			<div class="gf-calendar-container">
				<div class="gf-calendar-loading"><?php echo esc_html__( 'A carregar calendário...', 'gestor-financeiro' ); ?></div>
			</div>
		</div>

		<!-- Tab: Salários -->
		<div class="gf-tab-content" data-tab-content="salarios">
			<div class="gf-salarios-section">
				<h3><?php echo esc_html__( 'Salários Fixos', 'gestor-financeiro' ); ?></h3>
				<div class="gf-salarios-list" data-tipo="fixo">
					<div class="gf-loading"><?php echo esc_html__( 'A carregar...', 'gestor-financeiro' ); ?></div>
				</div>
			</div>
			<div class="gf-salarios-section">
				<h3><?php echo esc_html__( 'Salários Diários', 'gestor-financeiro' ); ?></h3>
				<div class="gf-salarios-list" data-tipo="diario">
					<div class="gf-loading"><?php echo esc_html__( 'A carregar...', 'gestor-financeiro' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Tab: Relatórios -->
		<div class="gf-tab-content" data-tab-content="relatorios">
			<div class="gf-relatorios-controls">
				<select id="gf-relatorio-mes">
					<?php
					$current_month = (int) current_time( 'n' );
					for ( $i = 1; $i <= 12; $i++ ) {
						$selected = $i === $current_month ? 'selected' : '';
						printf(
							'<option value="%d" %s>%s</option>',
							$i,
							$selected,
							esc_html( wp_date( 'F', mktime( 0, 0, 0, $i, 1 ) ) )
						);
					}
					?>
				</select>
				<select id="gf-relatorio-ano">
					<?php
					$current_year = (int) current_time( 'Y' );
					for ( $i = $current_year; $i >= $current_year - 5; $i-- ) {
						$selected = $i === $current_year ? 'selected' : '';
						printf( '<option value="%d" %s>%d</option>', $i, $selected, $i );
					}
					?>
				</select>
				<button class="gf-button" data-action="generate-report">
					<?php echo esc_html__( 'Gerar Relatório', 'gestor-financeiro' ); ?>
				</button>
				<button class="gf-button" data-action="export-csv">
					<?php echo esc_html__( 'Exportar CSV', 'gestor-financeiro' ); ?>
				</button>
			</div>
			<div class="gf-relatorios-output">
				<div class="gf-loading"><?php echo esc_html__( 'Selecione o mês e ano para gerar o relatório', 'gestor-financeiro' ); ?></div>
			</div>
		</div>

		<!-- Tab: Definições -->
		<div class="gf-tab-content" data-tab-content="definicoes">
			<div class="gf-settings-form">
				<h3><?php echo esc_html__( 'Definições do Sistema', 'gestor-financeiro' ); ?></h3>
				<div class="gf-form-group">
					<label for="gf-setting-alerts-email">
						<?php echo esc_html__( 'E-mail para alertas', 'gestor-financeiro' ); ?>
					</label>
					<input type="email" id="gf-setting-alerts-email" class="gf-input">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-cron-hour">
						<?php echo esc_html__( 'Hora do cron (0-23)', 'gestor-financeiro' ); ?>
					</label>
					<input type="number" id="gf-setting-cron-hour" class="gf-input" min="0" max="23" value="8">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-currency">
						<?php echo esc_html__( 'Moeda', 'gestor-financeiro' ); ?>
					</label>
					<select id="gf-setting-currency" class="gf-input">
						<option value="EUR">EUR (€)</option>
						<option value="USD">USD ($)</option>
						<option value="GBP">GBP (£)</option>
					</select>
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-imi-window-start">
						<?php echo esc_html__( 'Janela IMI - Dia início (1-31)', 'gestor-financeiro' ); ?>
					</label>
					<input type="number" id="gf-setting-imi-window-start" class="gf-input" min="1" max="31" value="1">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-imi-window-end">
						<?php echo esc_html__( 'Janela IMI - Dia fim (1-31)', 'gestor-financeiro' ); ?>
					</label>
					<input type="number" id="gf-setting-imi-window-end" class="gf-input" min="1" max="31" value="31">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-csv-batch-limit">
						<?php echo esc_html__( 'Limite de lote CSV', 'gestor-financeiro' ); ?>
					</label>
					<input type="number" id="gf-setting-csv-batch-limit" class="gf-input" min="10" value="100">
				</div>
				<button class="gf-button gf-button-primary" data-action="save-settings">
					<?php echo esc_html__( 'Guardar Definições', 'gestor-financeiro' ); ?>
				</button>
			</div>

			<div class="gf-danger-zone">
				<h3>
					<?php echo esc_html__( 'Zona de Perigo', 'gestor-financeiro' ); ?>
				</h3>
				<p>
					<?php echo esc_html__( 'Atenção: Esta ação irá apagar TODOS os dados da base de dados (estabelecimentos, fornecedores, funcionários, despesas, receitas, obrigações). Esta ação não pode ser desfeita.', 'gestor-financeiro' ); ?>
				</p>
				<button class="gf-button gf-button-danger" data-action="delete-all-data">
					<?php echo esc_html__( 'Apagar Todos os Dados', 'gestor-financeiro' ); ?>
				</button>
			</div>
		</div>

		<!-- Tab: Ajuda -->
		<div class="gf-tab-content" data-tab-content="ajuda">
			<div class="gf-help">
				<div class="gf-help-content" id="gf-help-content">
					<div class="gf-loading"><?php echo esc_html__( 'A carregar instruções...', 'gestor-financeiro' ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modals -->
	<div class="gf-modal" id="gf-modal-despesa" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__( 'Adicionar/Editar Despesa', 'gestor-financeiro' ); ?></h2>
			<form id="gf-form-despesa" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>

	<div class="gf-modal" id="gf-modal-receita" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__( 'Adicionar/Editar Receita', 'gestor-financeiro' ); ?></h2>
			<form id="gf-form-receita" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>
</div>

