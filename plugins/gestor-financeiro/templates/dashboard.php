<?php

/**
 * Dashboard template.
 *
 * @package GestorFinanceiro
 * @var string $nonce REST API nonce.
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
	exit;
}

?>
<script>
	// Apply dark mode immediately to prevent flash of light mode
	(function() {
		const savedMode = localStorage.getItem('gf-dark-mode');
		const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		const shouldBeDark = savedMode === 'true' || (savedMode === null && prefersDark);

		if (shouldBeDark) {
			document.documentElement.classList.add('gf-dark-mode-inline');
			// Also add to body/html for immediate application
			if (document.body) {
				document.body.style.backgroundColor = '#131313';
				document.body.style.color = '#e0e0e0';
				document.documentElement.style.backgroundColor = '#131313';
			}
		} else {
			if (document.body) {
				document.body.style.backgroundColor = '#f9f9f9';
				document.documentElement.style.backgroundColor = '#f9f9f9';
			}
		}

		// Add class to .gf-app when page loads
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				const app = document.querySelector('.gf-app');
				if (app && shouldBeDark) {
					app.classList.add('dark-mode');
				}
				if (document.body && shouldBeDark) {
					document.body.style.backgroundColor = '';
					document.body.style.color = '';
				}
				document.documentElement.classList.remove('gf-dark-mode-inline');
			});
		} else {
			// DOM already loaded
			const app = document.querySelector('.gf-app');
			if (app && shouldBeDark) {
				app.classList.add('dark-mode');
			}
			if (document.body && shouldBeDark) {
				document.body.style.backgroundColor = '';
				document.body.style.color = '';
			}
			document.documentElement.classList.remove('gf-dark-mode-inline');
		}
	})();
</script>
<div class="gf-app" data-nonce="<?php echo esc_attr($nonce); ?>">
	<div class="gf-dashboard-header">
		<h1><?php echo esc_html__('Gestor Financeiro', 'gestor-financeiro'); ?></h1>
		<div class="gf-header-actions">
			<button class="gf-dark-mode-toggle" id="gf-dark-mode-toggle" aria-label="<?php echo esc_attr__('Alternar modo escuro', 'gestor-financeiro'); ?>" type="button">
				<span class="gf-dark-mode-toggle-icon">
					<svg class="gf-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="5" />
						<path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
					</svg>
					<svg class="gf-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
					</svg>
				</span>
			</button>
			<a href="#" class="gf-help-link" data-action="show-help" title="<?php echo esc_attr__('Ver Instruções', 'gestor-financeiro'); ?>">
				<span class="gf-help-icon">?</span>
				<?php echo esc_html__('Ajuda', 'gestor-financeiro'); ?>
			</a>
		</div>
	</div>

	<div class="gf-dashboard-tabs">
		<button class="gf-tab-button active" data-tab="resumo">
			<?php echo esc_html__('Resumo', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="movimentos">
			<?php echo esc_html__('Movimentos', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="calendario">
			<?php echo esc_html__('Calendário', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="salarios">
			<?php echo esc_html__('Salários', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="funcionarios">
			<?php echo esc_html__('Funcionários', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="estabelecimentos">
			<?php echo esc_html__('Estabelecimentos', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="fornecedores">
			<?php echo esc_html__('Fornecedores', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="relatorios">
			<?php echo esc_html__('Relatórios', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="definicoes">
			<?php echo esc_html__('Definições', 'gestor-financeiro'); ?>
		</button>
		<button class="gf-tab-button" data-tab="ajuda">
			<?php echo esc_html__('Ajuda', 'gestor-financeiro'); ?>
		</button>
	</div>

	<div class="gf-dashboard-content">
		<!-- Tab: Resumo -->
		<div class="gf-tab-content active" data-tab-content="resumo">
			<h3 style="margin-bottom: 20px;"><?php echo esc_html__('Total', 'gestor-financeiro'); ?></h3>
			<div class="gf-summary-cards">
				<div class="gf-card">
					<h3><?php echo esc_html__('Receita do mês', 'gestor-financeiro'); ?></h3>
					<div class="gf-value" data-summary="receita_mes">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__('Despesas do mês', 'gestor-financeiro'); ?></h3>
					<div class="gf-value" data-summary="despesas_mes">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__('Resultado', 'gestor-financeiro'); ?></h3>
					<div class="gf-value" data-summary="resultado">0,00 €</div>
				</div>
				<div class="gf-card">
					<h3><?php echo esc_html__('Por pagar', 'gestor-financeiro'); ?></h3>
					<div class="gf-value" data-summary="por_pagar">0,00 €</div>
				</div>
			</div>
			<h3 style="margin-top: 40px; margin-bottom: 20px;"><?php echo esc_html__('Por Estabelecimento', 'gestor-financeiro'); ?></h3>
			<div class="gf-summary-por-estabelecimento" id="gf-summary-por-estabelecimento">
				<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Movimentos -->
		<div class="gf-tab-content" data-tab-content="movimentos">
			<div class="gf-movimentos-controls">
				<button class="gf-button gf-button-primary" data-action="add-despesa">
					<?php echo esc_html__('Adicionar Despesa', 'gestor-financeiro'); ?>
				</button>
				<button class="gf-button gf-button-primary" data-action="add-receita">
					<?php echo esc_html__('Adicionar Receita', 'gestor-financeiro'); ?>
				</button>
				<button class="gf-button" data-action="toggle-csv-import">
					<?php echo esc_html__('Importar CSV', 'gestor-financeiro'); ?>
				</button>
				<div class="gf-filters">
					<select id="gf-filter-estabelecimento">
						<option value=""><?php echo esc_html__('Todos os estabelecimentos', 'gestor-financeiro'); ?></option>
					</select>
					<input type="date" id="gf-filter-start-date" placeholder="<?php echo esc_attr__('Data início', 'gestor-financeiro'); ?>">
					<input type="date" id="gf-filter-end-date" placeholder="<?php echo esc_attr__('Data fim', 'gestor-financeiro'); ?>">
				</div>
			</div>

			<div class="gf-csv-import-section" id="gf-csv-import-section" style="display: none;">
				<h3><?php echo esc_html__('Importação CSV', 'gestor-financeiro'); ?></h3>
				<p style="margin-bottom: 15px; color: var(--gf-text-secondary);">
					<?php echo esc_html__('Importe dados de despesas ou receitas através de um ficheiro CSV.', 'gestor-financeiro'); ?>
					<a href="#" id="gf-download-csv-template" data-type="despesas" style="margin-left: 10px; color: var(--gf-link-color, #2271b1);">
						<?php echo esc_html__('Descarregar modelo Despesas', 'gestor-financeiro'); ?>
					</a>
					<a href="#" id="gf-download-csv-template-receitas" data-type="receitas" style="margin-left: 10px; color: var(--gf-link-color, #2271b1);">
						<?php echo esc_html__('Descarregar modelo Receitas', 'gestor-financeiro'); ?>
					</a>
				</p>
				<div class="gf-csv-import-form">
					<div class="gf-form-group">
						<label>Tipo de Dados</label>
						<select id="gf-csv-import-type" class="gf-input">
							<option value="despesas">Despesas</option>
							<option value="receitas">Receitas</option>
						</select>
					</div>
					<div class="gf-form-group">
						<label>Conteúdo CSV</label>
						<textarea id="gf-csv-import-content" class="gf-input" rows="10" placeholder="<?php echo esc_attr__('Cole aqui o conteúdo do ficheiro CSV ou faça upload do ficheiro...', 'gestor-financeiro'); ?>"></textarea>
						<input type="file" id="gf-csv-import-file" accept=".csv" style="margin-top: 10px;">
					</div>
					<div class="gf-form-group">
						<button class="gf-button gf-button-primary" data-action="preview-csv-import">
							<?php echo esc_html__('Pré-visualizar Importação', 'gestor-financeiro'); ?>
						</button>
					</div>
				</div>
				<div id="gf-csv-import-preview" style="display: none;">
					<h4><?php echo esc_html__('Pré-visualização', 'gestor-financeiro'); ?></h4>
					<div id="gf-csv-import-preview-content"></div>
					<div id="gf-csv-import-errors" style="margin-top: 15px;"></div>
					<div style="margin-top: 15px;">
						<button class="gf-button gf-button-primary" data-action="execute-csv-import" style="display: none;">
							<?php echo esc_html__('Executar Importação', 'gestor-financeiro'); ?>
						</button>
					</div>
				</div>
			</div>

			<div class="gf-movimentos-list">
				<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Calendário -->
		<div class="gf-tab-content" data-tab-content="calendario">
			<div class="gf-calendar-container">
				<div class="gf-calendar-loading"><?php echo esc_html__('A carregar calendário...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Salários -->
		<div class="gf-tab-content" data-tab-content="salarios">
			<div class="gf-salarios-section">
				<h3><?php echo esc_html__('Salários Fixos', 'gestor-financeiro'); ?></h3>
				<div class="gf-salarios-list" data-tipo="fixo">
					<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
				</div>
			</div>
			<div class="gf-salarios-section">
				<h3><?php echo esc_html__('Salários Diários', 'gestor-financeiro'); ?></h3>
				<div class="gf-salarios-list" data-tipo="diario">
					<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
				</div>
			</div>
		</div>

		<!-- Tab: Funcionários -->
		<div class="gf-tab-content" data-tab-content="funcionarios">
			<div class="gf-section-header">
				<h3><?php echo esc_html__('Funcionários', 'gestor-financeiro'); ?></h3>
				<button class="gf-button gf-button-primary" data-action="add-funcionario">
					<?php echo esc_html__('Adicionar Funcionário', 'gestor-financeiro'); ?>
				</button>
			</div>
			<div class="gf-funcionarios-list">
				<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Estabelecimentos -->
		<div class="gf-tab-content" data-tab-content="estabelecimentos">
			<div class="gf-section-header">
				<h3><?php echo esc_html__('Estabelecimentos', 'gestor-financeiro'); ?></h3>
				<button class="gf-button gf-button-primary" data-action="add-estabelecimento">
					<?php echo esc_html__('Adicionar Estabelecimento', 'gestor-financeiro'); ?>
				</button>
			</div>
			<div class="gf-estabelecimentos-list">
				<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Fornecedores -->
		<div class="gf-tab-content" data-tab-content="fornecedores">
			<div class="gf-section-header">
				<h3><?php echo esc_html__('Fornecedores', 'gestor-financeiro'); ?></h3>
				<button class="gf-button gf-button-primary" data-action="add-fornecedor">
					<?php echo esc_html__('Adicionar Fornecedor', 'gestor-financeiro'); ?>
				</button>
			</div>
			<div class="gf-fornecedores-list">
				<div class="gf-loading"><?php echo esc_html__('A carregar...', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Relatórios -->
		<div class="gf-tab-content" data-tab-content="relatorios">
			<div class="gf-relatorios-controls">
				<select id="gf-relatorio-mes">
					<?php
					$current_month = (int) current_time('n');
					for ($i = 1; $i <= 12; $i++) {
						$selected = $i === $current_month ? 'selected' : '';
						printf(
							'<option value="%d" %s>%s</option>',
							$i,
							$selected,
							esc_html(wp_date('F', mktime(0, 0, 0, $i, 1)))
						);
					}
					?>
				</select>
				<select id="gf-relatorio-ano">
					<?php
					$current_year = (int) current_time('Y');
					for ($i = $current_year; $i >= $current_year - 5; $i--) {
						$selected = $i === $current_year ? 'selected' : '';
						printf('<option value="%d" %s>%d</option>', $i, $selected, $i);
					}
					?>
				</select>
				<button class="gf-button" data-action="generate-report">
					<?php echo esc_html__('Gerar Relatório', 'gestor-financeiro'); ?>
				</button>
				<button class="gf-button" data-action="export-csv">
					<?php echo esc_html__('Exportar CSV', 'gestor-financeiro'); ?>
				</button>
			</div>
			<div class="gf-relatorios-output">
				<div class="gf-loading"><?php echo esc_html__('Selecione o mês e ano para gerar o relatório', 'gestor-financeiro'); ?></div>
			</div>
		</div>

		<!-- Tab: Definições -->
		<div class="gf-tab-content" data-tab-content="definicoes">
			<div class="gf-settings-form">
				<h3><?php echo esc_html__('Definições do Sistema', 'gestor-financeiro'); ?></h3>
				<div class="gf-form-group">
					<label for="gf-setting-alerts-email">
						<?php echo esc_html__('E-mail para alertas', 'gestor-financeiro'); ?>
					</label>
					<input type="email" id="gf-setting-alerts-email" class="gf-input">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-cron-hour">
						<?php echo esc_html__('Hora do cron (0-23)', 'gestor-financeiro'); ?>
					</label>
					<input type="number" id="gf-setting-cron-hour" class="gf-input" min="0" max="23" value="8">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-currency">
						<?php echo esc_html__('Moeda', 'gestor-financeiro'); ?>
					</label>
					<select id="gf-setting-currency" class="gf-input">
						<option value="EUR">EUR (€)</option>
						<option value="USD">USD ($)</option>
						<option value="GBP">GBP (£)</option>
					</select>
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-imi-window-start">
						<?php echo esc_html__('Janela IMI - Dia início (1-31)', 'gestor-financeiro'); ?>
					</label>
					<input type="number" id="gf-setting-imi-window-start" class="gf-input" min="1" max="31" value="1">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-imi-window-end">
						<?php echo esc_html__('Janela IMI - Dia fim (1-31)', 'gestor-financeiro'); ?>
					</label>
					<input type="number" id="gf-setting-imi-window-end" class="gf-input" min="1" max="31" value="31">
				</div>
				<div class="gf-form-group">
					<label for="gf-setting-csv-batch-limit">
						<?php echo esc_html__('Limite de lote CSV', 'gestor-financeiro'); ?>
					</label>
					<input type="number" id="gf-setting-csv-batch-limit" class="gf-input" min="10" value="100">
				</div>
				<button class="gf-button gf-button-primary" data-action="save-settings">
					<?php echo esc_html__('Guardar Definições', 'gestor-financeiro'); ?>
				</button>
			</div>

			<div class="gf-danger-zone">
				<h3>
					<?php echo esc_html__('Zona de Perigo', 'gestor-financeiro'); ?>
				</h3>
				<p>
					<?php echo esc_html__('Atenção: Esta ação irá apagar TODOS os dados da base de dados (estabelecimentos, fornecedores, funcionários, despesas, receitas, obrigações). Esta ação não pode ser desfeita.', 'gestor-financeiro'); ?>
				</p>
				<button class="gf-button gf-button-danger" data-action="delete-all-data">
					<?php echo esc_html__('Apagar Todos os Dados', 'gestor-financeiro'); ?>
				</button>
			</div>
		</div>

		<!-- Tab: Ajuda -->
		<div class="gf-tab-content" data-tab-content="ajuda">
			<div class="gf-help">
				<div class="gf-help-content" id="gf-help-content">
					<div class="gf-loading"><?php echo esc_html__('A carregar instruções...', 'gestor-financeiro'); ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modals -->
	<div class="gf-modal" id="gf-modal-despesa" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__('Adicionar/Editar Despesa', 'gestor-financeiro'); ?></h2>
			<form id="gf-form-despesa" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>

	<div class="gf-modal" id="gf-modal-receita" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__('Adicionar/Editar Receita', 'gestor-financeiro'); ?></h2>
			<form id="gf-form-receita" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>

	<div class="gf-modal" id="gf-modal-estabelecimento" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__('Adicionar/Editar Estabelecimento', 'gestor-financeiro'); ?></h2>
			<form id="gf-form-estabelecimento" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>

	<div class="gf-modal" id="gf-modal-fornecedor" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__('Adicionar/Editar Fornecedor', 'gestor-financeiro'); ?></h2>
			<form id="gf-form-fornecedor" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>

	<div class="gf-modal" id="gf-modal-funcionario" style="display: none;">
		<div class="gf-modal-content">
			<span class="gf-modal-close">&times;</span>
			<h2><?php echo esc_html__('Adicionar/Editar Funcionário', 'gestor-financeiro'); ?></h2>
			<form id="gf-form-funcionario" class="gf-form">
				<!-- Form fields will be populated by JavaScript -->
			</form>
		</div>
	</div>
</div>