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

		// Initialize dark mode
		initDarkMode();

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

		// Initialize establishments tab
		initEstabelecimentosTab(apiUrl, nonce);

		// Initialize suppliers tab
		initFornecedoresTab(apiUrl, nonce);

		// Initialize employees tab
		initFuncionariosTab(apiUrl, nonce);

		// Initialize reports tab
		initReportsTab(apiUrl, nonce);

		// Initialize settings tab
		initSettingsTab(apiUrl, nonce);

		// Initialize help tab
		initHelpTab(apiUrl, nonce);

		// Initialize help link
		initHelpLink(apiUrl, nonce);
	}

	function initDarkMode() {
		const app = document.querySelector('.gf-app');
		const toggle = document.getElementById('gf-dark-mode-toggle');
		
		if (!app || !toggle) {
			return;
		}

		// Function to update body/html background
		const updateBackground = (isDark) => {
			if (isDark) {
				document.body.style.backgroundColor = '#131313';
				document.documentElement.style.backgroundColor = '#131313';
			} else {
				document.body.style.backgroundColor = '#ffffff';
				document.documentElement.style.backgroundColor = '#ffffff';
			}
		};

		// Check localStorage for saved preference
		const savedMode = localStorage.getItem('gf-dark-mode');
		const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		
		// Apply dark mode if saved or if system preference is dark and no saved preference
		const shouldBeDark = savedMode === 'true' || (savedMode === null && prefersDark);
		
		if (shouldBeDark) {
			app.classList.add('dark-mode');
			updateBackground(true);
		} else {
			updateBackground(false);
		}

		// Listen to system preference changes (if no saved preference)
		if (window.matchMedia) {
			const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
			mediaQuery.addEventListener('change', (e) => {
				// Only auto-switch if user hasn't manually set a preference
				if (localStorage.getItem('gf-dark-mode') === null) {
					if (e.matches) {
						app.classList.add('dark-mode');
						updateBackground(true);
					} else {
						app.classList.remove('dark-mode');
						updateBackground(false);
					}
				}
			});
		}

		// Toggle button click handler
		toggle.addEventListener('click', () => {
			const isDark = app.classList.contains('dark-mode');
			
			if (isDark) {
				app.classList.remove('dark-mode');
				localStorage.setItem('gf-dark-mode', 'false');
				updateBackground(false);
			} else {
				app.classList.add('dark-mode');
				localStorage.setItem('gf-dark-mode', 'true');
				updateBackground(true);
			}
		});
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

		// Initialize CSV handlers once (hidden by default)
		initCSVImportHandlers(apiUrl, nonce, content);

		// Toggle CSV import section
		content.querySelector('[data-action="toggle-csv-import"]')?.addEventListener('click', () => {
			const importSection = content.querySelector('#gf-csv-import-section');
			if (importSection) {
				const isVisible = importSection.style.display !== 'none';
				importSection.style.display = isVisible ? 'none' : 'block';
			}
		});
	}

	function initCSVImportHandlers(apiUrl, nonce, content) {
		// Only initialize once per page load
		if (content.dataset.csvHandlersInitialized) {
			return;
		}
		content.dataset.csvHandlersInitialized = 'true';

		// CSV Template download - prevent multiple downloads
		const despesasLink = content.querySelector('#gf-download-csv-template');
		if (despesasLink) {
			despesasLink.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				if (!despesasLink.dataset.downloading) {
					despesasLink.dataset.downloading = 'true';
					downloadCSVTemplate(apiUrl, nonce, 'despesas').finally(() => {
						setTimeout(() => {
							despesasLink.dataset.downloading = '';
						}, 500);
					});
				}
			});
		}

		const receitasLink = content.querySelector('#gf-download-csv-template-receitas');
		if (receitasLink) {
			receitasLink.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				if (!receitasLink.dataset.downloading) {
					receitasLink.dataset.downloading = 'true';
					downloadCSVTemplate(apiUrl, nonce, 'receitas').finally(() => {
						setTimeout(() => {
							receitasLink.dataset.downloading = '';
						}, 500);
					});
				}
			});
		}

		// CSV Import handlers
		content.querySelector('[data-action="preview-csv-import"]')?.addEventListener('click', () => {
			previewCSVImport(apiUrl, nonce);
		});

		content.querySelector('[data-action="execute-csv-import"]')?.addEventListener('click', () => {
			executeCSVImport(apiUrl, nonce);
		});

		// File upload handler
		const fileInput = content.querySelector('#gf-csv-import-file');
		if (fileInput) {
			fileInput.addEventListener('change', (e) => {
				const file = e.target.files[0];
				if (file) {
					const reader = new FileReader();
					reader.onload = (event) => {
						document.querySelector('#gf-csv-import-content').value = event.target.result;
					};
					reader.readAsText(file, 'UTF-8');
				}
			});
		}

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
				
				// Also populate all establishment selects in forms
				document.querySelectorAll('[name="estabelecimento_id"]').forEach(sel => {
					if (sel !== select) {
						populateEstabelecimentoSelect(sel, data);
					}
				});
			})
			.catch(error => {
				console.error('Error loading establishments:', error);
			});
	}

	function populateEstabelecimentoSelect(select, establishmentsData = null) {
		if (!select) return;
		
		// Clear existing options except the first one
		while (select.children.length > 1) {
			select.removeChild(select.lastChild);
		}
		
		// If data is provided, use it; otherwise fetch
		if (establishmentsData && Array.isArray(establishmentsData)) {
			establishmentsData.forEach(est => {
				const option = document.createElement('option');
				option.value = est.id;
				option.textContent = est.nome;
				select.appendChild(option);
			});
		} else {
			// Fetch data
			const apiUrl = window.gestorFinanceiro?.apiUrl || '/wp-json/gestor-financeiro/v1/';
			const nonce = document.querySelector('.gf-app')?.dataset.nonce || '';
			
			fetch(`${apiUrl}estabelecimentos`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					if (Array.isArray(data)) {
						data.forEach(est => {
							const option = document.createElement('option');
							option.value = est.id;
							option.textContent = est.nome;
							select.appendChild(option);
						});
					}
				})
				.catch(error => {
					console.error('Error populating establishment select:', error);
				});
		}
	}

	function populateFornecedorSelect(select, fornecedoresData = null) {
		if (!select) return;
		
		// Clear existing options except the first one
		while (select.children.length > 1) {
			select.removeChild(select.lastChild);
		}
		
		// If data is provided, use it; otherwise fetch
		if (fornecedoresData && Array.isArray(fornecedoresData)) {
			fornecedoresData.forEach(forn => {
				const option = document.createElement('option');
				option.value = forn.id;
				option.textContent = forn.nome;
				select.appendChild(option);
			});
		} else {
			// Fetch data
			const apiUrl = window.gestorFinanceiro?.apiUrl || '/wp-json/gestor-financeiro/v1/';
			const nonce = document.querySelector('.gf-app')?.dataset.nonce || '';
			
			fetch(`${apiUrl}fornecedores`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					if (Array.isArray(data)) {
						data.forEach(forn => {
							const option = document.createElement('option');
							option.value = forn.id;
							option.textContent = forn.nome;
							select.appendChild(option);
						});
					}
				})
				.catch(error => {
					console.error('Error populating supplier select:', error);
				});
		}
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

		// Close modal on X click
		const closeHandler = () => {
			modal.style.display = 'none';
			// Remove event listeners
			document.removeEventListener('keydown', escHandler);
			modal.querySelector('.gf-modal-close')?.removeEventListener('click', closeHandler);
		};
		modal.querySelector('.gf-modal-close')?.addEventListener('click', closeHandler);

		// Close modal on ESC key
		const escHandler = (e) => {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				closeHandler();
			}
		};
		document.addEventListener('keydown', escHandler);

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
			// Populate selects when creating new
			setTimeout(() => {
				populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
				populateFornecedorSelect(form.querySelector('[name="fornecedor_id"]'));
			}, 100);
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

		// Close modal on X click
		const closeHandler = () => {
			modal.style.display = 'none';
			// Remove event listeners
			document.removeEventListener('keydown', escHandler);
			modal.querySelector('.gf-modal-close')?.removeEventListener('click', closeHandler);
		};
		modal.querySelector('.gf-modal-close')?.addEventListener('click', closeHandler);

		// Close modal on ESC key
		const escHandler = (e) => {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				closeHandler();
			}
		};
		document.addEventListener('keydown', escHandler);

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
			// Populate selects when creating new
			setTimeout(() => {
				populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
			}, 100);
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
		
		// Load establishments and suppliers
		populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
		populateFornecedorSelect(form.querySelector('[name="fornecedor_id"]'));
		
		if (data.data) form.querySelector('[name="data"]').value = data.data;
		if (data.descricao) form.querySelector('[name="descricao"]').value = data.descricao;
		if (data.valor) form.querySelector('[name="valor"]').value = data.valor;
		if (data.vencimento) form.querySelector('[name="vencimento"]').value = data.vencimento;
		if (data.estabelecimento_id) form.querySelector('[name="estabelecimento_id"]').value = data.estabelecimento_id;
		if (data.fornecedor_id) form.querySelector('[name="fornecedor_id"]').value = data.fornecedor_id;
	}

	function populateReceitaForm(form, data) {
		form.innerHTML = getReceitaFormHTML();
		
		// Load establishments
		populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
		
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
		const type = prompt('Selecione o tipo:\n1 - Despesas\n2 - Receitas', '1');
		if (!type) return;

		const csvType = type === '1' ? 'despesas' : 'receitas';
		const startDate = prompt('Data início (YYYY-MM-DD) ou deixe vazio para últimos 3 meses:', '');
		const endDate = prompt('Data fim (YYYY-MM-DD) ou deixe vazio para hoje:', '');

		const params = new URLSearchParams();
		params.append('type', csvType);
		if (startDate) params.append('start_date', startDate);
		if (endDate) params.append('end_date', endDate);

		// Use fetch with nonce to download CSV
		fetch(`${apiUrl}csv/export?${params}`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (!response.ok) {
					throw new Error('Erro ao exportar CSV');
				}
				return response.blob();
			})
			.then(blob => {
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url;
				const today = new Date().toISOString().split('T')[0];
				a.download = `export-${csvType}-${today}.csv`;
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				document.body.removeChild(a);
			})
			.catch(error => {
				alert('Erro ao exportar CSV: ' + error.message);
			});
	}

	let csvPreviewData = null;

	function downloadCSVTemplate(apiUrl, nonce, type) {
		return fetch(`${apiUrl}csv/template?type=${type}`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (!response.ok) {
					throw new Error('Erro ao descarregar modelo');
				}
				return response.text();
			})
			.then(csvContent => {
				// Remove BOM if present (UTF-8 BOM is \xEF\xBB\xBF)
				let content = csvContent;
				// Check for UTF-8 BOM at the beginning
				if (content.length > 0 && content.charCodeAt(0) === 0xFEFF) {
					content = content.substring(1);
				}
				// Also check for BOM bytes directly
				if (content.length >= 3 && content.charCodeAt(0) === 0xEF && content.charCodeAt(1) === 0xBB && content.charCodeAt(2) === 0xBF) {
					content = content.substring(3);
				}
				
				// Create blob with proper CSV MIME type
				// Use 'text/csv' without charset for better compatibility with Numbers/Excel
				const blob = new Blob([content], { type: 'text/csv' });
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url;
				a.download = `modelo-${type}.csv`;
				a.style.display = 'none';
				document.body.appendChild(a);
				a.click();
				// Clean up after a short delay to ensure download starts
				setTimeout(() => {
					window.URL.revokeObjectURL(url);
					document.body.removeChild(a);
				}, 100);
			})
			.catch(error => {
				alert('Erro ao descarregar modelo: ' + error.message);
				throw error;
			});
	}

	function previewCSVImport(apiUrl, nonce) {
		const type = document.querySelector('#gf-csv-import-type')?.value || 'despesas';
		const content = document.querySelector('#gf-csv-import-content')?.value;

		if (!content || content.trim() === '') {
			alert('Por favor, cole ou faça upload do conteúdo CSV.');
			return;
		}

		const previewDiv = document.querySelector('#gf-csv-import-preview');
		const previewContent = document.querySelector('#gf-csv-import-preview-content');
		const errorsDiv = document.querySelector('#gf-csv-import-errors');
		const executeBtn = document.querySelector('[data-action="execute-csv-import"]');

		previewDiv.style.display = 'block';
		previewContent.innerHTML = '<div class="gf-loading">A validar CSV...</div>';
		errorsDiv.innerHTML = '';
		executeBtn.style.display = 'none';

		fetch(`${apiUrl}csv/import`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify({
				type: type,
				csv_content: content,
			}),
		})
			.then(r => r.json())
			.then(result => {
				if (result.code) {
					previewContent.innerHTML = `<div class="gf-error">Erro: ${result.message}</div>`;
					return;
				}

				csvPreviewData = result.preview || [];

				if (!result.success) {
					previewContent.innerHTML = `<div class="gf-error">${result.error || 'Erro ao processar CSV'}</div>`;
					return;
				}

				// Show preview
				if (csvPreviewData.length === 0) {
					previewContent.innerHTML = '<p>Nenhum dado válido encontrado no CSV.</p>';
					return;
				}

				let html = `<p><strong>${csvPreviewData.length} registo(s) válido(s) encontrado(s)</strong></p>`;
				html += '<div style="max-height: 400px; overflow-y: auto; margin-top: 15px;">';
				html += '<table class="gf-table" style="width: 100%;">';
				html += '<thead><tr>';

				// Headers
				if (type === 'despesas') {
					html += '<th>Data</th><th>Estabelecimento</th><th>Descrição</th><th>Valor</th><th>Vencimento</th>';
				} else {
					html += '<th>Data</th><th>Estabelecimento</th><th>Bruto</th><th>Taxas</th><th>Líquido</th>';
				}
				html += '</tr></thead><tbody>';

				// Preview rows (limit to 10)
				csvPreviewData.slice(0, 10).forEach(row => {
					html += '<tr>';
					if (type === 'despesas') {
						html += `<td>${row.data || ''}</td>`;
						html += `<td>${row.estabelecimento_id || ''}</td>`;
						html += `<td>${row.descricao || ''}</td>`;
						html += `<td>${formatCurrency(row.valor || 0)}</td>`;
						html += `<td>${row.vencimento || ''}</td>`;
					} else {
						html += `<td>${row.data || ''}</td>`;
						html += `<td>${row.estabelecimento_id || ''}</td>`;
						html += `<td>${formatCurrency(row.bruto || 0)}</td>`;
						html += `<td>${formatCurrency(row.taxas || 0)}</td>`;
						html += `<td>${formatCurrency(row.liquido || 0)}</td>`;
					}
					html += '</tr>';
				});

				html += '</tbody></table></div>';

				if (csvPreviewData.length > 10) {
					html += `<p style="margin-top: 10px; color: var(--gf-text-secondary);">Mostrando primeiros 10 de ${csvPreviewData.length} registos</p>`;
				}

				previewContent.innerHTML = html;

				// Show errors if any
				if (result.errors && Object.keys(result.errors).length > 0) {
					let errorsHtml = '<div class="gf-error" style="padding: 15px; margin-top: 15px;">';
					errorsHtml += '<strong>Erros encontrados:</strong><ul style="margin-top: 10px;">';
					Object.entries(result.errors).forEach(([line, lineErrors]) => {
						if (Array.isArray(lineErrors)) {
							lineErrors.forEach(error => {
								errorsHtml += `<li>Linha ${line}: ${error}</li>`;
							});
						}
					});
					errorsHtml += '</ul></div>';
					errorsDiv.innerHTML = errorsHtml;
				} else {
					errorsDiv.innerHTML = '';
				}

				// Show execute button if preview is valid
				if (csvPreviewData.length > 0) {
					executeBtn.style.display = 'inline-block';
				}
			})
			.catch(error => {
				previewContent.innerHTML = `<div class="gf-error">Erro ao processar CSV: ${error.message}</div>`;
			});
	}

	function executeCSVImport(apiUrl, nonce) {
		if (!csvPreviewData || csvPreviewData.length === 0) {
			alert('Não há dados para importar. Por favor, faça preview primeiro.');
			return;
		}

		if (!confirm(`Tem a certeza que deseja importar ${csvPreviewData.length} registo(s)?`)) {
			return;
		}

		const type = document.querySelector('#gf-csv-import-type')?.value || 'despesas';
		const executeBtn = document.querySelector('[data-action="execute-csv-import"]');

		executeBtn.disabled = true;
		executeBtn.textContent = 'A importar...';

		fetch(`${apiUrl}csv/import/execute`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify({
				type: type,
				preview_data: csvPreviewData,
			}),
		})
			.then(r => r.json())
			.then(result => {
				executeBtn.disabled = false;
				executeBtn.textContent = 'Executar Importação';

				if (result.code) {
					alert('Erro: ' + result.message);
					return;
				}

				if (result.success) {
					alert(`Importação concluída! ${result.imported || 0} registo(s) importado(s) com sucesso.`);
					
					// Clear form
					document.querySelector('#gf-csv-import-content').value = '';
					document.querySelector('#gf-csv-import-file').value = '';
					document.querySelector('#gf-csv-import-preview').style.display = 'none';
					csvPreviewData = null;

					// Reload data
					loadMovements(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				} else {
					alert('Erro na importação. Verifique os dados e tente novamente.');
				}
			})
			.catch(error => {
				executeBtn.disabled = false;
				executeBtn.textContent = 'Executar Importação';
				alert('Erro: ' + error.message);
			});
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

		// Delete all data button
		content.querySelector('[data-action="delete-all-data"]')?.addEventListener('click', () => {
			if (confirm('Tem a CERTEZA ABSOLUTA que deseja apagar TODOS os dados?\n\nEsta ação não pode ser desfeita!\n\nTodos os estabelecimentos, fornecedores, funcionários, despesas, receitas e obrigações serão permanentemente eliminados.')) {
				if (confirm('Última confirmação: Esta ação é IRREVERSÍVEL. Deseja mesmo continuar?')) {
					clearAllData(apiUrl, nonce);
				}
			}
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

	function clearAllData(apiUrl, nonce) {
		const button = document.querySelector('[data-action="delete-all-data"]');
		if (!button) return;

		button.disabled = true;
		button.textContent = 'A apagar dados...';

		fetch(`${apiUrl}admin/clear-all-data`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
		})
			.then(response => {
				if (!response.ok) {
					return response.json().then(err => { throw new Error(err.message || 'Erro ao apagar dados'); });
				}
				return response.json();
			})
			.then(data => {
				alert('Todos os dados foram apagados com sucesso!');
				// Reload page to refresh data
				location.reload();
			})
			.catch(error => {
				alert('Erro ao apagar dados: ' + error.message);
				button.disabled = false;
				button.textContent = 'Apagar Todos os Dados';
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

	function initEstabelecimentosTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="estabelecimentos"]');
		if (!content) return;

		// Load establishments
		loadEstabelecimentos(apiUrl, nonce);

		// Add button
		content.querySelector('[data-action="add-estabelecimento"]')?.addEventListener('click', () => {
			openEstabelecimentoModal(apiUrl, nonce);
		});
	}

	function loadEstabelecimentos(apiUrl, nonce) {
		const list = document.querySelector('.gf-estabelecimentos-list');
		if (!list) return;

		list.innerHTML = '<div class="gf-loading">A carregar...</div>';

		fetch(`${apiUrl}estabelecimentos`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(data => {
				if (!Array.isArray(data)) {
					console.error('Estabelecimentos response is not an array:', data);
					list.innerHTML = '<div class="gf-error">Erro ao carregar estabelecimentos.</div>';
					return;
				}

				if (data.length === 0) {
					list.innerHTML = '<p>Nenhum estabelecimento encontrado. Clique em "Adicionar Estabelecimento" para criar um.</p>';
					return;
				}

				list.innerHTML = data.map(est => `
					<div class="gf-movimento-item">
						<div>
							<strong>${est.nome}</strong> - ${est.tipo}
							${est.ativo ? '<span style="color: #00a32a; margin-left: 10px;">● Ativo</span>' : '<span style="color: #d63638; margin-left: 10px;">● Inativo</span>'}
							${est.valor_renda ? `<span style="margin-left: 10px;">Valor da renda: ${formatCurrency(est.valor_renda)}</span>` : ''}
						</div>
						<div>
							<button class="gf-button" data-action="edit-estabelecimento" data-id="${est.id}">Editar</button>
							<button class="gf-button" data-action="delete-estabelecimento" data-id="${est.id}">Eliminar</button>
						</div>
					</div>
				`).join('');

				// Add event listeners
				list.querySelectorAll('[data-action="edit-estabelecimento"]').forEach(btn => {
					btn.addEventListener('click', () => {
						openEstabelecimentoModal(apiUrl, nonce, btn.dataset.id);
					});
				});

				list.querySelectorAll('[data-action="delete-estabelecimento"]').forEach(btn => {
					btn.addEventListener('click', () => {
						if (confirm('Tem a certeza que deseja eliminar este estabelecimento? Esta ação não pode ser desfeita e eliminará todas as despesas, receitas e funcionários associados.')) {
							deleteEstabelecimento(apiUrl, nonce, btn.dataset.id);
						}
					});
				});
			})
			.catch(error => {
				list.innerHTML = `<div class="gf-error">Erro ao carregar estabelecimentos: ${error.message}</div>`;
			});
	}

	function openEstabelecimentoModal(apiUrl, nonce, id = null) {
		const modal = document.getElementById('gf-modal-estabelecimento');
		const form = document.getElementById('gf-form-estabelecimento');
		if (!modal || !form) return;

		modal.style.display = 'flex';

		// Close modal on X click
		const closeHandler = () => {
			modal.style.display = 'none';
			document.removeEventListener('keydown', escHandler);
			modal.querySelector('.gf-modal-close')?.removeEventListener('click', closeHandler);
		};
		modal.querySelector('.gf-modal-close')?.addEventListener('click', closeHandler);

		// Close modal on ESC key
		const escHandler = (e) => {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				closeHandler();
			}
		};
		document.addEventListener('keydown', escHandler);

		// Load data if editing
		if (id) {
			fetch(`${apiUrl}estabelecimentos/${id}`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					populateEstabelecimentoForm(form, data);
				});
		} else {
			form.innerHTML = getEstabelecimentoFormHTML();
		}

		// Submit form
		form.onsubmit = (e) => {
			e.preventDefault();
			saveEstabelecimento(apiUrl, nonce, id, form);
		};
	}

	function getEstabelecimentoFormHTML() {
		return `
			<div class="gf-form-group">
				<label>Nome</label>
				<input type="text" name="nome" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Tipo</label>
				<select name="tipo" class="gf-input" required>
					<option value="restaurante">Restaurante</option>
					<option value="bar">Bar</option>
					<option value="apartamento">Apartamento</option>
				</select>
			</div>
			<div class="gf-form-group">
				<label>Dia de Renda (opcional, apenas para apartamentos)</label>
				<input type="number" name="dia_renda" class="gf-input" min="1" max="31">
			</div>
			<div class="gf-form-group">
				<label>Valor da Renda Mensal (opcional)</label>
				<input type="number" name="valor_renda" class="gf-input" step="0.01" min="0">
			</div>
			<div class="gf-form-group">
				<label>
					<input type="checkbox" name="ativo" value="1" checked> Ativo
				</label>
			</div>
			<div class="gf-form-group">
				<button type="submit" class="gf-button gf-button-primary">Guardar</button>
			</div>
		`;
	}

	function populateEstabelecimentoForm(form, data) {
		form.innerHTML = getEstabelecimentoFormHTML();
		if (data.nome) form.querySelector('[name="nome"]').value = data.nome;
		if (data.tipo) form.querySelector('[name="tipo"]').value = data.tipo;
		if (data.dia_renda) form.querySelector('[name="dia_renda"]').value = data.dia_renda;
		if (data.valor_renda) form.querySelector('[name="valor_renda"]').value = data.valor_renda;
		if (data.ativo) form.querySelector('[name="ativo"]').checked = data.ativo == 1;
	}

	function saveEstabelecimento(apiUrl, nonce, id, form) {
		const formData = new FormData(form);
		const data = {
			nome: formData.get('nome'),
			tipo: formData.get('tipo'),
			dia_renda: formData.get('dia_renda') ? parseInt(formData.get('dia_renda')) : null,
			valor_renda: formData.get('valor_renda') ? parseFloat(formData.get('valor_renda')) : null,
			ativo: formData.get('ativo') ? 1 : 0,
		};

		const method = id ? 'PUT' : 'POST';
		const url = id ? `${apiUrl}estabelecimentos/${id}` : `${apiUrl}estabelecimentos`;

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
					document.getElementById('gf-modal-estabelecimento').style.display = 'none';
					loadEstabelecimentos(apiUrl, nonce);
					// Reload establishments in filters and forms
					loadEstablishments(apiUrl, nonce);
				}
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	function deleteEstabelecimento(apiUrl, nonce, id) {
		fetch(`${apiUrl}estabelecimentos/${id}`, {
			method: 'DELETE',
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (response.ok || response.status === 204) {
					loadEstabelecimentos(apiUrl, nonce);
					loadEstablishments(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				} else {
					return response.json().then(err => {
						throw new Error(err.message || 'Erro ao eliminar');
					});
				}
			})
			.catch(error => {
				alert('Erro: ' + error.message);
			});
	}

	function initFornecedoresTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="fornecedores"]');
		if (!content) return;

		// Load suppliers
		loadFornecedores(apiUrl, nonce);

		// Add button
		content.querySelector('[data-action="add-fornecedor"]')?.addEventListener('click', () => {
			openFornecedorModal(apiUrl, nonce);
		});
	}

	function loadFornecedores(apiUrl, nonce) {
		const list = document.querySelector('.gf-fornecedores-list');
		if (!list) return;

		list.innerHTML = '<div class="gf-loading">A carregar...</div>';

		fetch(`${apiUrl}fornecedores`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(data => {
				if (!Array.isArray(data)) {
					console.error('Fornecedores response is not an array:', data);
					list.innerHTML = '<div class="gf-error">Erro ao carregar fornecedores.</div>';
					return;
				}

				if (data.length === 0) {
					list.innerHTML = '<p>Nenhum fornecedor encontrado. Clique em "Adicionar Fornecedor" para criar um.</p>';
					return;
				}

				list.innerHTML = data.map(forn => `
					<div class="gf-movimento-item">
						<div>
							<strong>${forn.nome}</strong>
							${forn.categoria ? `<span style="margin-left: 10px;">${forn.categoria}</span>` : ''}
							${forn.nif ? `<span style="margin-left: 10px;">NIF: ${forn.nif}</span>` : ''}
						</div>
						<div>
							<button class="gf-button" data-action="edit-fornecedor" data-id="${forn.id}">Editar</button>
							<button class="gf-button" data-action="delete-fornecedor" data-id="${forn.id}">Eliminar</button>
						</div>
					</div>
				`).join('');

				// Add event listeners
				list.querySelectorAll('[data-action="edit-fornecedor"]').forEach(btn => {
					btn.addEventListener('click', () => {
						openFornecedorModal(apiUrl, nonce, btn.dataset.id);
					});
				});

				list.querySelectorAll('[data-action="delete-fornecedor"]').forEach(btn => {
					btn.addEventListener('click', () => {
						if (confirm('Tem a certeza que deseja eliminar este fornecedor?')) {
							deleteFornecedor(apiUrl, nonce, btn.dataset.id);
						}
					});
				});
			})
			.catch(error => {
				list.innerHTML = `<div class="gf-error">Erro ao carregar fornecedores: ${error.message}</div>`;
			});
	}

	function openFornecedorModal(apiUrl, nonce, id = null) {
		const modal = document.getElementById('gf-modal-fornecedor');
		const form = document.getElementById('gf-form-fornecedor');
		if (!modal || !form) return;

		modal.style.display = 'flex';

		// Close modal on X click
		const closeHandler = () => {
			modal.style.display = 'none';
			document.removeEventListener('keydown', escHandler);
			modal.querySelector('.gf-modal-close')?.removeEventListener('click', closeHandler);
		};
		modal.querySelector('.gf-modal-close')?.addEventListener('click', closeHandler);

		// Close modal on ESC key
		const escHandler = (e) => {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				closeHandler();
			}
		};
		document.addEventListener('keydown', escHandler);

		// Load data if editing
		if (id) {
			fetch(`${apiUrl}fornecedores/${id}`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					populateFornecedorForm(form, data);
				});
		} else {
			form.innerHTML = getFornecedorFormHTML();
		}

		// Submit form
		form.onsubmit = (e) => {
			e.preventDefault();
			saveFornecedor(apiUrl, nonce, id, form);
		};
	}

	function getFornecedorFormHTML() {
		return `
			<div class="gf-form-group">
				<label>Nome</label>
				<input type="text" name="nome" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Categoria</label>
				<input type="text" name="categoria" class="gf-input" placeholder="ex: Alimentação, Bebidas, Serviços">
			</div>
			<div class="gf-form-group">
				<label>NIF</label>
				<input type="text" name="nif" class="gf-input">
			</div>
			<div class="gf-form-group">
				<label>Contacto</label>
				<input type="text" name="contacto" class="gf-input" placeholder="Email ou telefone">
			</div>
			<div class="gf-form-group">
				<label>IBAN</label>
				<input type="text" name="iban" class="gf-input">
			</div>
			<div class="gf-form-group">
				<label>Prazo de Pagamento (dias)</label>
				<input type="number" name="prazo_pagamento" class="gf-input" min="0">
			</div>
			<div class="gf-form-group">
				<label>Notas</label>
				<textarea name="notas" class="gf-input" rows="3"></textarea>
			</div>
			<div class="gf-form-group">
				<button type="submit" class="gf-button gf-button-primary">Guardar</button>
			</div>
		`;
	}

	function populateFornecedorForm(form, data) {
		form.innerHTML = getFornecedorFormHTML();
		if (data.nome) form.querySelector('[name="nome"]').value = data.nome;
		if (data.categoria) form.querySelector('[name="categoria"]').value = data.categoria;
		if (data.nif) form.querySelector('[name="nif"]').value = data.nif;
		if (data.contacto) form.querySelector('[name="contacto"]').value = data.contacto;
		if (data.iban) form.querySelector('[name="iban"]').value = data.iban;
		if (data.prazo_pagamento) form.querySelector('[name="prazo_pagamento"]').value = data.prazo_pagamento;
		if (data.notas) form.querySelector('[name="notas"]').value = data.notas;
	}

	function saveFornecedor(apiUrl, nonce, id, form) {
		const formData = new FormData(form);
		const data = {
			nome: formData.get('nome'),
			categoria: formData.get('categoria') || null,
			nif: formData.get('nif') || null,
			contacto: formData.get('contacto') || null,
			iban: formData.get('iban') || null,
			prazo_pagamento: formData.get('prazo_pagamento') ? parseInt(formData.get('prazo_pagamento')) : null,
			notas: formData.get('notas') || null,
		};

		const method = id ? 'PUT' : 'POST';
		const url = id ? `${apiUrl}fornecedores/${id}` : `${apiUrl}fornecedores`;

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
					document.getElementById('gf-modal-fornecedor').style.display = 'none';
					loadFornecedores(apiUrl, nonce);
					// Update supplier selects in expense forms
					document.querySelectorAll('[name="fornecedor_id"]').forEach(sel => {
						populateFornecedorSelect(sel);
					});
				}
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	function deleteFornecedor(apiUrl, nonce, id) {
		fetch(`${apiUrl}fornecedores/${id}`, {
			method: 'DELETE',
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (response.ok || response.status === 204) {
					loadFornecedores(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				} else {
					return response.json().then(err => {
						throw new Error(err.message || 'Erro ao eliminar');
					});
				}
			})
			.catch(error => {
				alert('Erro: ' + error.message);
			});
	}

	function initFuncionariosTab(apiUrl, nonce) {
		const content = document.querySelector('[data-tab-content="funcionarios"]');
		if (!content) return;

		// Load employees
		loadFuncionarios(apiUrl, nonce);

		// Add button
		content.querySelector('[data-action="add-funcionario"]')?.addEventListener('click', () => {
			openFuncionarioModal(apiUrl, nonce);
		});
	}

	function loadFuncionarios(apiUrl, nonce) {
		const list = document.querySelector('.gf-funcionarios-list');
		if (!list) return;

		list.innerHTML = '<div class="gf-loading">A carregar...</div>';

		fetch(`${apiUrl}funcionarios`, {
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(r => {
				if (!r.ok) {
					throw new Error(`HTTP error! status: ${r.status}`);
				}
				return r.json();
			})
			.then(data => {
				if (!Array.isArray(data)) {
					console.error('Funcionarios response is not an array:', data);
					list.innerHTML = '<div class="gf-error">Erro ao carregar funcionários.</div>';
					return;
				}

				if (data.length === 0) {
					list.innerHTML = '<p>Nenhum funcionário encontrado. Clique em "Adicionar Funcionário" para criar um.</p>';
					return;
				}

				// Load establishments to show names instead of IDs
				fetch(`${apiUrl}estabelecimentos`, {
					headers: { 'X-WP-Nonce': nonce },
				})
					.then(r => r.json())
					.then(estabelecimentos => {
						const estabMap = {};
						if (Array.isArray(estabelecimentos)) {
							estabelecimentos.forEach(e => {
								estabMap[e.id] = e.nome;
							});
						}

						list.innerHTML = data.map(func => {
							const tipoPagamento = func.tipo_pagamento === 'fixo' ? 'Fixo' : func.tipo_pagamento === 'diario' ? 'Diário' : 'Hora';
							const valor = formatCurrency(func.valor_base);
							const estabNome = func.estabelecimento_id && estabMap[func.estabelecimento_id] 
								? estabMap[func.estabelecimento_id] 
								: func.estabelecimento_id ? `ID: ${func.estabelecimento_id}` : 'Sem estabelecimento';
							
							return `
								<div class="gf-movimento-item">
									<div>
										<strong>${func.nome}</strong>
										<span style="margin-left: 10px;">${tipoPagamento}: ${valor}</span>
										<span style="margin-left: 10px; color: var(--gf-text-secondary);">${estabNome}</span>
									</div>
									<div>
										<button class="gf-button" data-action="edit-funcionario" data-id="${func.id}">Editar</button>
										<button class="gf-button" data-action="delete-funcionario" data-id="${func.id}">Eliminar</button>
									</div>
								</div>
							`;
						}).join('');

						// Add event listeners
						list.querySelectorAll('[data-action="edit-funcionario"]').forEach(btn => {
							btn.addEventListener('click', () => {
								openFuncionarioModal(apiUrl, nonce, btn.dataset.id);
							});
						});

						list.querySelectorAll('[data-action="delete-funcionario"]').forEach(btn => {
							btn.addEventListener('click', () => {
								if (confirm('Tem a certeza que deseja eliminar este funcionário?')) {
									deleteFuncionario(apiUrl, nonce, btn.dataset.id);
								}
							});
						});
					})
					.catch(error => {
						console.error('Error loading establishments for funcionarios:', error);
						// Show list anyway without establishment names
						list.innerHTML = data.map(func => {
							const tipoPagamento = func.tipo_pagamento === 'fixo' ? 'Fixo' : func.tipo_pagamento === 'diario' ? 'Diário' : 'Hora';
							const valor = formatCurrency(func.valor_base);
							return `
								<div class="gf-movimento-item">
									<div>
										<strong>${func.nome}</strong>
										<span style="margin-left: 10px;">${tipoPagamento}: ${valor}</span>
									</div>
									<div>
										<button class="gf-button" data-action="edit-funcionario" data-id="${func.id}">Editar</button>
										<button class="gf-button" data-action="delete-funcionario" data-id="${func.id}">Eliminar</button>
									</div>
								</div>
							`;
						}).join('');

						// Add event listeners
						list.querySelectorAll('[data-action="edit-funcionario"]').forEach(btn => {
							btn.addEventListener('click', () => {
								openFuncionarioModal(apiUrl, nonce, btn.dataset.id);
							});
						});

						list.querySelectorAll('[data-action="delete-funcionario"]').forEach(btn => {
							btn.addEventListener('click', () => {
								if (confirm('Tem a certeza que deseja eliminar este funcionário?')) {
									deleteFuncionario(apiUrl, nonce, btn.dataset.id);
								}
							});
						});
					});
			})
			.catch(error => {
				list.innerHTML = `<div class="gf-error">Erro ao carregar funcionários: ${error.message}</div>`;
			});
	}

	function openFuncionarioModal(apiUrl, nonce, id = null) {
		const modal = document.getElementById('gf-modal-funcionario');
		const form = document.getElementById('gf-form-funcionario');
		if (!modal || !form) return;

		modal.style.display = 'flex';

		// Close modal on X click
		const closeHandler = () => {
			modal.style.display = 'none';
			document.removeEventListener('keydown', escHandler);
			modal.querySelector('.gf-modal-close')?.removeEventListener('click', closeHandler);
		};
		modal.querySelector('.gf-modal-close')?.addEventListener('click', closeHandler);

		// Close modal on ESC key
		const escHandler = (e) => {
			if (e.key === 'Escape' && modal.style.display === 'flex') {
				closeHandler();
			}
		};
		document.addEventListener('keydown', escHandler);

		// Load data if editing
		if (id) {
			fetch(`${apiUrl}funcionarios/${id}`, {
				headers: { 'X-WP-Nonce': nonce },
			})
				.then(r => r.json())
				.then(data => {
					populateFuncionarioForm(form, data);
				});
		} else {
			form.innerHTML = getFuncionarioFormHTML();
			// Populate establishment select when creating new
			setTimeout(() => {
				populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
			}, 100);
		}

		// Submit form
		form.onsubmit = (e) => {
			e.preventDefault();
			saveFuncionario(apiUrl, nonce, id, form);
		};
	}

	function getFuncionarioFormHTML() {
		return `
			<div class="gf-form-group">
				<label>Nome</label>
				<input type="text" name="nome" class="gf-input" required>
			</div>
			<div class="gf-form-group">
				<label>Estabelecimento (opcional)</label>
				<select name="estabelecimento_id" class="gf-input">
					<option value="">-- Selecionar --</option>
				</select>
			</div>
			<div class="gf-form-group">
				<label>Tipo de Pagamento</label>
				<select name="tipo_pagamento" class="gf-input" required>
					<option value="fixo">Fixo (Mensal)</option>
					<option value="diario">Diário</option>
					<option value="hora">Por Hora</option>
				</select>
			</div>
			<div class="gf-form-group">
				<label>Valor Base</label>
				<input type="number" step="0.01" name="valor_base" class="gf-input" required>
				<small style="color: var(--gf-text-secondary); margin-top: 5px; display: block;">
					Valor mensal para "Fixo", valor por dia para "Diário", ou valor por hora para "Por Hora"
				</small>
			</div>
			<div class="gf-form-group">
				<label>IBAN (opcional)</label>
				<input type="text" name="iban" class="gf-input" placeholder="PT50001234567890123456789">
			</div>
			<div class="gf-form-group">
				<label>Regra de Pagamento (opcional)</label>
				<input type="text" name="regra_pagamento" class="gf-input" placeholder="ex: Mensal, Semanal">
			</div>
			<div class="gf-form-group">
				<label>Notas (opcional)</label>
				<textarea name="notas" class="gf-input" rows="3"></textarea>
			</div>
			<div class="gf-form-group">
				<button type="submit" class="gf-button gf-button-primary">Guardar</button>
			</div>
		`;
	}

	function populateFuncionarioForm(form, data) {
		form.innerHTML = getFuncionarioFormHTML();
		
		// Load establishments
		populateEstabelecimentoSelect(form.querySelector('[name="estabelecimento_id"]'));
		
		if (data.nome) form.querySelector('[name="nome"]').value = data.nome;
		if (data.estabelecimento_id) form.querySelector('[name="estabelecimento_id"]').value = data.estabelecimento_id;
		if (data.tipo_pagamento) form.querySelector('[name="tipo_pagamento"]').value = data.tipo_pagamento;
		if (data.valor_base) form.querySelector('[name="valor_base"]').value = data.valor_base;
		if (data.iban) form.querySelector('[name="iban"]').value = data.iban;
		if (data.regra_pagamento) form.querySelector('[name="regra_pagamento"]').value = data.regra_pagamento;
		if (data.notas) form.querySelector('[name="notas"]').value = data.notas;
	}

	function saveFuncionario(apiUrl, nonce, id, form) {
		const formData = new FormData(form);
		const data = {
			nome: formData.get('nome'),
			estabelecimento_id: formData.get('estabelecimento_id') ? parseInt(formData.get('estabelecimento_id')) : null,
			tipo_pagamento: formData.get('tipo_pagamento'),
			valor_base: parseFloat(formData.get('valor_base')) || 0,
			iban: formData.get('iban') || null,
			regra_pagamento: formData.get('regra_pagamento') || null,
			notas: formData.get('notas') || null,
		};

		const method = id ? 'PUT' : 'POST';
		const url = id ? `${apiUrl}funcionarios/${id}` : `${apiUrl}funcionarios`;

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
					document.getElementById('gf-modal-funcionario').style.display = 'none';
					loadFuncionarios(apiUrl, nonce);
					// Reload salaries tab
					loadSalaries(apiUrl, nonce);
				}
			})
			.catch(error => {
				alert('Erro ao guardar: ' + error.message);
			});
	}

	function deleteFuncionario(apiUrl, nonce, id) {
		fetch(`${apiUrl}funcionarios/${id}`, {
			method: 'DELETE',
			headers: { 'X-WP-Nonce': nonce },
		})
			.then(response => {
				if (response.ok || response.status === 204) {
					loadFuncionarios(apiUrl, nonce);
					loadSalaries(apiUrl, nonce);
					loadSummary(apiUrl, nonce);
				} else {
					return response.json().then(err => {
						throw new Error(err.message || 'Erro ao eliminar');
					});
				}
			})
			.catch(error => {
				alert('Erro: ' + error.message);
			});
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
