/**
 * Gestor Financeiro - Frontend JavaScript
 *
 * @package GestorFinanceiro
 */

(function() {
	'use strict';

	// Initialize app when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		const app = document.querySelector('.gf-app');
		if (!app) {
			return;
		}

		const nonce = app.dataset.nonce || '';
		const apiUrl = window.gestorFinanceiro?.apiUrl || '/wp-json/gestor-financeiro/v1/';

		// Initialize tabs
		initTabs();

		// Initialize summary tab
		initSummaryTab(apiUrl, nonce);

		// Initialize movements tab
		initMovementsTab(apiUrl, nonce);

		// Initialize calendar tab
		initCalendarTab(apiUrl, nonce);

		// Initialize salaries tab
		initSalariesTab(apiUrl, nonce);

		// Initialize reports tab
		initReportsTab(apiUrl, nonce);

		// Initialize settings tab
		initSettingsTab(apiUrl, nonce);

		// Initialize help tab
		initHelpTab(apiUrl, nonce);

		// Initialize help link
		initHelpLink(apiUrl, nonce);
	}

	function initTabs() {
		const buttons = document.querySelectorAll('.gf-tab-button');
		const contents = document.querySelectorAll('.gf-tab-content');

		buttons.forEach(button => {
			button.addEventListener('click', () => {
				const tab = button.dataset.tab;

				// Update buttons
				buttons.forEach(b => b.classList.remove('active'));
				button.classList.add('active');

				// Update contents
				contents.forEach(c => c.classList.remove('active'));
				const content = document.querySelector(`[data-tab-content="${tab}"]`);
				if (content) {
					content.classList.add('active');
				}
			});
		});
	}

	function initSummaryTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="resumo"]');
		if (!content) return;

		loadSummary(apiUrl, nonce);
	}

	function loadSummary(apiUrl, nonce) {
		fetch(`${apiUrl}dashboard/summary`, {
			method: 'GET',
			headers: {
				'X-WP-Nonce': nonce,
			},
		})
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				// Check if response is error object
				if (data.code) {
					console.error('Summary API error:', data.message);
					return;
				}

				if (data.receita_mes !== undefined) {
					updateElement('[data-summary="receita_mes"]', formatCurrency(data.receita_mes));
					updateElement('[data-summary="despesas_mes"]', formatCurrency(data.despesas_mes));
					updateElement('[data-summary="resultado"]', formatCurrency(data.resultado));
					updateElement('[data-summary="por_pagar"]', formatCurrency(data.por_pagar));
				}
			})
			.catch(error => {
				console.error('Error loading summary:', error);
			});
	}

	function initMovementsTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="movimentos"]');
		if (!content) return;

		// Load establishments for filter
		loadEstablishments(apiUrl, nonce);

		// Load movements
		loadMovements(apiUrl, nonce);

		// Add buttons
		content.querySelector('[data-action="add-despesa"]')?.addEventListener('click', () => {
			openDespesaModal(apiUrl, nonce);
		});

		content.querySelector('[data-action="add-receita"]')?.addEventListener('click', () => {
			openReceitaModal(apiUrl, nonce);
		});

		// Filter listeners
		content.querySelector('#gf-filter-estabelecimento')?.addEventListener('change', () => {
			loadMovements(apiUrl, nonce);
		});

		content.querySelector('#gf-filter-start-date')?.addEventListener('change', () => {
			loadMovements(apiUrl, nonce);
		});

		content.querySelector('#gf-filter-end-date')?.addEventListener('change', () => {
			loadMovements(apiUrl, nonce);
		});
	}

	function loadEstablishments(apiUrl, nonce) {
		fetch(`${apiUrl}estabelecimentos`, {
			method: 'GET',
			headers: {
				'X-WP-Nonce': nonce,
			},
		})
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				// Check if response is an array or error object
				if (!Array.isArray(data)) {
					console.error('Establishments response is not an array:', data);
					return;
				}

				const select = document.querySelector('#gf-filter-estabelecimento');
				if (select) {
					// Clear existing options except the first one
					while (select.children.length > 1) {
						select.removeChild(select.lastChild);
					}

					data.forEach(est => {
						const option = document.createElement('option');
						option.value = est.id;
						option.textContent = est.nome;
						select.appendChild(option);
					});
				}
			})
			.catch(error => {
				console.error('Error loading establishments:', error);
			});
	}

	function loadMovements(apiUrl, nonce) {
		const list = document.querySelector('.gf-movimentos-list');
		if (!list) return;

		list.innerHTML = '<div class="gf-loading">A carregar...</div>';

		const estabelecimentoId = document.querySelector('#gf-filter-estabelecimento')?.value || '';
		const startDate = document.querySelector('#gf-filter-start-date')?.value || '';
		const endDate = document.querySelector('#gf-filter-end-date')?.value || '';

		const params = new URLSearchParams();
		if (estabelecimentoId) params.append('estabelecimento_id', estabelecimentoId);
		if (startDate) params.append('start_date', startDate);
		if (endDate) params.append('end_date', endDate);

		Promise.all([
			fetch(`${apiUrl}despesas?${params}`, {
				headers: { 'X-WP-Nonce': nonce },
			}).then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			}),
			fetch(`${apiUrl}receitas?${params}`, {
				headers: { 'X-WP-Nonce': nonce },
			}).then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			}),
		])
			.then(([despesas, receitas]) => {
				list.innerHTML = '';

				// Check if responses are arrays or error objects
				if (!Array.isArray(despesas)) {
					console.error('Despesas response is not an array:', despesas);
					despesas = [];
				}
				if (!Array.isArray(receitas)) {
					console.error('Receitas response is not an array:', receitas);
					receitas = [];
				}

				if (despesas.length === 0 && receitas.length === 0) {
					list.innerHTML = '<p>Nenhum movimento encontrado.</p>';
					return;
				}

				// Combine and sort by date
				const allMovements = [
					...despesas.map(d => ({ ...d, type: 'despesa' })),
					...receitas.map(r => ({ ...r, type: 'receita' })),
				].sort((a, b) => new Date(b.data) - new Date(a.data));

				allMovements.forEach(movement => {
					const item = document.createElement('div');
					item.className = 'gf-movimento-item';
					item.innerHTML = `
						<div>
							<strong>${formatDate(movement.data)}</strong> - ${movement.descricao || movement.canal || ''}
							<span style="color: ${movement.type === 'despesa' ? '#d63638' : '#00a32a'}; margin-left: 10px;">
								${movement.type === 'despesa' ? '-' : '+'} ${formatCurrency(movement.valor || movement.liquido || 0)}
							</span>
						</div>
						<div>
							<button class="gf-button" data-action="edit" data-id="${movement.id}" data-type="${movement.type}">Editar</button>
							<button class="gf-button" data-action="delete" data-id="${movement.id}" data-type="${movement.type}">Eliminar</button>
						</div>
					`;
					list.appendChild(item);
				});

				// Add event listeners
				list.querySelectorAll('[data-action="edit"]').forEach(btn => {
					btn.addEventListener('click', () => {
						const id = btn.dataset.id;
						const type = btn.dataset.type;
						if (type === 'despesa') {
							openDespesaModal(apiUrl, nonce, id);
						} else {
							openReceitaModal(apiUrl, nonce, id);
						}
					});
				});

				list.querySelectorAll('[data-action="delete"]').forEach(btn => {
					btn.addEventListener('click', () => {
						if (confirm('Tem a certeza que deseja eliminar?')) {
							const id = btn.dataset.id;
							const type = btn.dataset.type;
							deleteMovement(apiUrl, nonce, id, type);
						}
					});
				});
			})
			.catch(error => {
				list.innerHTML = `<div class="gf-error">Erro ao carregar movimentos: ${error.message}</div>`;
			});
	}

	function openDespesaModal(apiUrl, nonce, id = null) {
		const modal = document.getElementById('gf-modal-despesa');
		const form = document.getElementById('gf-form-despesa');
		if (!modal || !form) return;

		modal.style.display = 'flex';

		// Close modal
		modal.querySelector('.gf-modal-close')?.addEventListener('click', () => {
			modal.style.display = 'none';
		});

		// Load data if editing
		if (id) {
			fetch(`${apiUrl}despesas/${id}`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					populateDespesaForm(form, data);
				});
		} else {
			form.innerHTML = getDespesaFormHTML();
		}

		// Submit form
		form.onsubmit = (e) => {
			e.preventDefault();
			saveDespesa(apiUrl, nonce, id, form);
		};
	}

	function openReceitaModal(apiUrl, nonce, id = null) {
		const modal = document.getElementById('gf-modal-receita');
		const form = document.getElementById('gf-form-receita');
		if (!modal || !form) return;

		modal.style.display = 'flex';

		modal.querySelector('.gf-modal-close')?.addEventListener('click', () => {
			modal.style.display = 'none';
		});

		if (id) {
			fetch(`${apiUrl}receitas/${id}`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					populateReceitaForm(form, data);
				});
		} else {
			form.innerHTML = getReceitaFormHTML();
		}

		form.onsubmit = (e) => {
			e.preventDefault();
			saveReceita(apiUrl, nonce, id, form);
		};
	}

	function getDespesaFormHTML() {
		return `
			<div class="gf-form-group">
				<label>Data</label>
				<input type="date" name="data" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Descrição</label>
				<input type="text" name="descricao" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Valor</label>
				<input type="number" step="0.01" name="valor" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Data de vencimento</label>
				<input type="date" name="vencimento" class="gf-input">
			</div>
			<div class="gf-form-group">
				<label>Estabelecimento</label>
				<select name="estabelecimento_id" class="gf-input">
					<option value="">-- Selecionar --</option>
				</select>
			</div>
			<div class="gf-form-group">
				<button type="submit" class="gf-button gf-button-primary">Guardar</button>
			</div>
		`;
	}

	function getReceitaFormHTML() {
		return `
			<div class="gf-form-group">
				<label>Data</label>
				<input type="date" name="data" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Canal</label>
				<input type="text" name="canal" class="gf-input">
			</div>
			<div class="gf-form-group">
				<label>Bruto</label>
				<input type="number" step="0.01" name="bruto" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Taxas</label>
				<input type="number" step="0.01" name="taxas" class="gf-input" value="0">
			</div>
			<div class="gf-form-group">
				<label>Líquido</label>
				<input type="number" step="0.01" name="liquido" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Estabelecimento</label>
				<select name="estabelecimento_id" class="gf-input">
					<option value="">-- Selecionar --</option>
				</select>
			</div>
			<div class="gf-form-group">
				<button type="submit" class="gf-button gf-button-primary">Guardar</button>
			</div>
		`;
	}

	function populateDespesaForm(form, data) {
		form.innerHTML = getDespesaFormHTML();
		if (data.data) form.querySelector('[name="data"]').value = data.data;
		if (data.descricao) form.querySelector('[name="descricao"]').value = data.descricao;
		if (data.valor) form.querySelector('[name="valor"]').value = data.valor;
		if (data.vencimento) form.querySelector('[name="vencimento"]').value = data.vencimento;
		if (data.estabelecimento_id) form.querySelector('[name="estabelecimento_id"]').value = data.estabelecimento_id;
	}

	function populateReceitaForm(form, data) {
		form.innerHTML = getReceitaFormHTML();
		if (data.data) form.querySelector('[name="data"]').value = data.data;
		if (data.canal) form.querySelector('[name="canal"]').value = data.canal;
		if (data.bruto) form.querySelector('[name="bruto"]').value = data.bruto;
		if (data.taxas) form.querySelector('[name="taxas"]').value = data.taxas;
		if (data.liquido) form.querySelector('[name="liquido"]').value = data.liquido;
		if (data.estabelecimento_id) form.querySelector('[name="estabelecimento_id"]').value = data.estabelecimento_id;
	}

	function saveDespesa(apiUrl, nonce, id, form) {
		const formData = new FormData(form);
		const data = Object.fromEntries(formData);

		const method = id ? 'PUT' : 'POST';
		const url = id ? `${apiUrl}despesas/${id}` : `${apiUrl}despesas`;

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(data),
		})
			.then(response => response.json())
			.then(result => {
				if (result.code) {
					alert('Erro: ' + result.message);
				} else {
					document.getElementById('gf-modal-despesa').style.display = 'none';
					loadMovements(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				}
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	function saveReceita(apiUrl, nonce, id, form) {
		const formData = new FormData(form);
		const data = Object.fromEntries(formData);

		const method = id ? 'PUT' : 'POST';
		const url = id ? `${apiUrl}receitas/${id}` : `${apiUrl}receitas`;

		fetch(url, {
			method: method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(data),
		})
			.then(response => response.json())
			.then(result => {
				if (result.code) {
					alert('Erro: ' + result.message);
				} else {
					document.getElementById('gf-modal-receita').style.display = 'none';
					loadMovements(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				}
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	function deleteMovement(apiUrl, nonce, id, type) {
		fetch(`${apiUrl}${type}s/${id}`, {
			method: 'DELETE',
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (response.ok || response.status === 204) {
					loadMovements(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				} else {
					alert('Erro ao eliminar');
				}
			})
			.catch(error => {
				alert('Erro: ' + error.message);
			});
	}

	function initCalendarTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="calendario"]');
		if (!content) return;

		loadCalendar(apiUrl, nonce);
	}

	function loadCalendar(apiUrl, nonce) {
		const container = document.querySelector('.gf-calendar-container');
		if (!container) return;

		const today = new Date();
		const start = today.toISOString().split('T')[0];
		const end = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

		fetch(`${apiUrl}calendar?start=${start}&end=${end}`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(events => {
				container.innerHTML = '';
				
				// Check if response is an array or error object
				if (!Array.isArray(events)) {
					console.error('Calendar response is not an array:', events);
					if (events.code) {
						container.innerHTML = `<div class="gf-error">Erro: ${events.message || 'Resposta inválida do servidor'}</div>`;
					} else {
						container.innerHTML = '<p>Nenhum evento encontrado.</p>';
					}
					return;
				}

				if (events.length === 0) {
					container.innerHTML = '<p>Nenhum evento encontrado.</p>';
					return;
				}

				const table = document.createElement('table');
				table.className = 'gf-table';
				table.innerHTML = `
					<thead>
						<tr>
							<th>Data</th>
							<th>Tipo</th>
							<th>Descrição</th>
							<th>Valor</th>
						</tr>
					</thead>
					<tbody>
						${events.map(e => `
							<tr>
								<td>${formatDate(e.date)}</td>
								<td>${e.type}</td>
								<td>${e.title}</td>
								<td>${e.value ? formatCurrency(e.value) : '-'}</td>
							</tr>
						`).join('')}
					</tbody>
				`;
				container.appendChild(table);
			})
			.catch(error => {
				container.innerHTML = `<div class="gf-error">Erro ao carregar calendário: ${error.message}</div>`;
			});
	}

	function initSalariesTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="salarios"]');
		if (!content) return;

		loadSalaries(apiUrl, nonce);
	}

	function loadSalaries(apiUrl, nonce) {
		fetch(`${apiUrl}salaries`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(salaries => {
				// Check if response is an array or error object
				if (!Array.isArray(salaries)) {
					console.error('Salaries response is not an array:', salaries);
					['fixo', 'diario'].forEach(tipo => {
						const list = document.querySelector(`.gf-salarios-list[data-tipo="${tipo}"]`);
						if (list) {
							list.innerHTML = '<p>Erro ao carregar salários.</p>';
						}
					});
					return;
				}

				['fixo', 'diario'].forEach(tipo => {
					const list = document.querySelector(`.gf-salarios-list[data-tipo="${tipo}"]`);
					if (!list) return;

					const filtered = salaries.filter(s => s.tipo_pagamento === tipo);
					if (filtered.length === 0) {
						list.innerHTML = '<p>Nenhum salário encontrado.</p>';
						return;
					}

					list.innerHTML = filtered.map(s => `
						<div class="gf-movimento-item">
							<div>
								<strong>${s.nome}</strong> - ${formatCurrency(s.valor_base)}
							</div>
							<div>
								<button class="gf-button gf-button-primary" data-action="mark-paid" data-id="${s.id}">Marcar como pago</button>
							</div>
						</div>
					`).join('');

					list.querySelectorAll('[data-action="mark-paid"]').forEach(btn => {
						btn.addEventListener('click', () => {
							markSalaryPaid(apiUrl, nonce, btn.dataset.id);
						});
					});
				});
			})
			.catch(error => {
				console.error('Error loading salaries:', error);
				['fixo', 'diario'].forEach(tipo => {
					const list = document.querySelector(`.gf-salarios-list[data-tipo="${tipo}"]`);
					if (list) {
						list.innerHTML = `<div class="gf-error">Erro ao carregar salários: ${error.message}</div>`;
					}
				});
			});
	}

	function markSalaryPaid(apiUrl, nonce, funcionarioId) {
		fetch(`${apiUrl}salaries/${funcionarioId}/mark-paid`, {
			method: 'PATCH',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify({ data: new Date().toISOString().split('T')[0] }),
		})
			.then(r => r.json())
			.then(() => {
				loadSalaries(apiUrl, nonce);
				loadSummary(apiUrl, nonce);
				alert('Salário marcado como pago!');
			})
			.catch(error => {
				alert('Erro: ' + error.message);
			});
	}

	function initReportsTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="relatorios"]');
		if (!content) return;

		content.querySelector('[data-action="generate-report"]')?.addEventListener('click', () => {
			generateReport(apiUrl, nonce);
		});

		content.querySelector('[data-action="export-csv"]')?.addEventListener('click', () => {
			exportCSV(apiUrl, nonce);
		});
	}

	function generateReport(apiUrl, nonce) {
		const month = document.querySelector('#gf-relatorio-mes')?.value || new Date().getMonth() + 1;
		const year = document.querySelector('#gf-relatorio-ano')?.value || new Date().getFullYear();
		const output = document.querySelector('.gf-relatorios-output');

		if (output) {
			output.innerHTML = '<div class="gf-loading">A gerar relatório...</div>';
		}

		// Report generation will be implemented with Reports class in Phase 8
		setTimeout(() => {
			if (output) {
				output.innerHTML = '<p>Funcionalidade de relatórios será implementada na Phase 8.</p>';
			}
		}, 500);
	}

	function exportCSV(apiUrl, nonce) {
		// CSV export will be implemented in Phase 9
		alert('Exportação CSV será implementada na Phase 9.');
	}

	function initSettingsTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="definicoes"]');
		if (!content) return;

		// Load settings
		loadSettings(apiUrl, nonce);

		// Save button
		content.querySelector('[data-action="save-settings"]')?.addEventListener('click', () => {
			saveSettings(apiUrl, nonce);
		});
	}

	function loadSettings(apiUrl, nonce) {
		fetch(`${apiUrl}settings`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => r.json())
			.then(settings => {
				if (settings.alerts_email) document.querySelector('#gf-setting-alerts-email').value = settings.alerts_email;
				if (settings.cron_hour) document.querySelector('#gf-setting-cron-hour').value = settings.cron_hour;
				if (settings.currency) document.querySelector('#gf-setting-currency').value = settings.currency;
				if (settings.imi_window_start) document.querySelector('#gf-setting-imi-window-start').value = settings.imi_window_start;
				if (settings.imi_window_end) document.querySelector('#gf-setting-imi-window-end').value = settings.imi_window_end;
				if (settings.csv_batch_limit) document.querySelector('#gf-setting-csv-batch-limit').value = settings.csv_batch_limit;
			})
			.catch(error => {
				console.error('Error loading settings:', error);
			});
	}

	function saveSettings(apiUrl, nonce) {
		const settings = {
			alerts_email: document.querySelector('#gf-setting-alerts-email')?.value || '',
			cron_hour: document.querySelector('#gf-setting-cron-hour')?.value || 8,
			currency: document.querySelector('#gf-setting-currency')?.value || 'EUR',
			imi_window_start: document.querySelector('#gf-setting-imi-window-start')?.value || 1,
			imi_window_end: document.querySelector('#gf-setting-imi-window-end')?.value || 31,
			csv_batch_limit: document.querySelector('#gf-setting-csv-batch-limit')?.value || 100,
		};

		fetch(`${apiUrl}settings`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(settings),
		})
			.then(r => r.json())
			.then(() => {
				alert('Definições guardadas!');
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	// Utility functions
	function formatCurrency(value) {
		return new Intl.NumberFormat('pt-PT', {
			style: 'currency',
			currency: 'EUR',
		}).format(value || 0);
	}

	function formatDate(dateString) {
		if (!dateString) return '';
		const date = new Date(dateString);
		return date.toLocaleDateString('pt-PT');
	}

	function updateElement(selector, content) {
		const el = document.querySelector(selector);
		if (el) el.textContent = content;
	}

	function initHelpTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="ajuda"]');
		if (!content) return;

		const helpContainer = document.querySelector('.gf-help');
		if (!helpContainer) return;

		// Load help content when tab is shown
		const helpTab = document.querySelector('[data-tab="ajuda"]');
		if (helpTab) {
			helpTab.addEventListener('click', () => {
				if (!helpContainer.dataset.loaded) {
					loadHelpGuide(apiUrl, nonce);
				}
			});
		}
	}

	function initHelpLink(apiUrl, nonce) {
		const helpLink = document.querySelector('[data-action="show-help"]');
		if (!helpLink) return;

		helpLink.addEventListener('click', (e) => {
			e.preventDefault();
			
			// Switch to help tab
			const helpTab = document.querySelector('[data-tab="ajuda"]');
			if (helpTab) {
				helpTab.click();
				
				// Load content if not loaded
				const helpContainer = document.querySelector('.gf-help');
				if (helpContainer && !helpContainer.dataset.loaded) {
					loadHelpGuide(apiUrl, nonce);
				}
			}
		});
	}

	function loadHelpGuide(apiUrl, nonce) {
		const helpContainer = document.querySelector('.gf-help');
		if (!helpContainer) return;

		helpContainer.innerHTML = '<div class="gf-loading" style="padding: 40px; text-align: center;">A carregar instruções...</div>';

		fetch(`${apiUrl}help/guide`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(data => {
				if (data.html && data.toc && data.toc.length > 0) {
					// Build sidebar with TOC
					let sidebarHtml = '<div class="gf-help-sidebar">';
					sidebarHtml += '<h3>Índice</h3>';
					sidebarHtml += '<ul>';
					data.toc.forEach((item, index) => {
						// Use anchor as-is from server (already sanitized)
						const anchor = item.anchor;
						sidebarHtml += `<li><a href="#${anchor}" data-section-id="${anchor}">${item.text}</a></li>`;
					});
					sidebarHtml += '</ul>';
					sidebarHtml += '</div>';

					// Build content
					helpContainer.innerHTML = sidebarHtml + 
						'<div class="gf-help-content" id="gf-help-content">' +
						`<div class="gf-help-guide">${data.html}</div>` +
						'</div>';

					// Setup show/hide functionality for sections
					setTimeout(() => {
						// Log available sections for debugging
						const allSections = document.querySelectorAll('.gf-help-section');
						console.log('Available sections in DOM:', Array.from(allSections).map(s => ({
							id: s.id,
							display: s.style.display,
							className: s.className
						})));
						setupHelpSections(data.toc);
					}, 100);
					helpContainer.dataset.loaded = 'true';
				} else if (data.html) {
					helpContainer.innerHTML = 
						'<div class="gf-help-sidebar"><h3>Índice</h3><p style="padding: 20px; color: #666;">Sem índice disponível</p></div>' +
						'<div class="gf-help-content" id="gf-help-content">' +
						`<div class="gf-help-guide">${data.html}</div>` +
						'</div>';
					helpContainer.dataset.loaded = 'true';
				} else {
					helpContainer.innerHTML = '<div class="gf-error">Erro ao carregar o guia de utilizador.</div>';
				}
			})
			.catch(error => {
				console.error('Error loading help guide:', error);
				helpContainer.innerHTML = `<div class="gf-error">Erro ao carregar instruções: ${error.message}</div>`;
			});
	}

	function setupHelpSections(toc) {
		const sidebar = document.querySelector('.gf-help-sidebar');
		if (!sidebar) {
			console.error('Sidebar not found');
			return;
		}

		const links = sidebar.querySelectorAll('a');
		if (links.length === 0) {
			console.error('No links found in sidebar');
			return;
		}

		console.log('Setting up help sections, found', links.length, 'links');

		// Show/hide sections on click
		links.forEach((link, index) => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				
				const sectionIdAttr = link.getAttribute('data-section-id');
				const href = link.getAttribute('href');
				const sectionId = sectionIdAttr || href.replace('#', '');
				const sectionSelector = '#section-' + sectionId;
				
				console.log('Clicked on:', {
					sectionIdAttr,
					href,
					sectionId,
					sectionSelector
				});
				
				const section = document.querySelector(sectionSelector);
				
				if (section) {
					console.log('Section found, showing it');
					
					// Hide all sections
					const allSections = document.querySelectorAll('.gf-help-section');
					allSections.forEach(sec => {
						sec.style.display = 'none';
					});
					
					// Show selected section
					section.style.display = 'block';
					
					// Scroll to top of content area
					const contentArea = document.querySelector('.gf-help-content');
					if (contentArea) {
						contentArea.scrollTop = 0;
					}
					
					// Update active link
					links.forEach(l => l.classList.remove('active'));
					link.classList.add('active');
				} else {
					console.error('Section not found:', sectionSelector);
					// Try to find any section with similar ID
					const allSections = document.querySelectorAll('.gf-help-section');
					console.log('Available sections:', Array.from(allSections).map(s => s.id));
				}
			});

			// Set first link as active initially
			if (index === 0) {
				link.classList.add('active');
			}
		});
	}
})();
