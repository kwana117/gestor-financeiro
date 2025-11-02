# Gestor Financeiro

Sistema de gestão financeira para uma empresa com múltiplos estabelecimentos (restaurantes, bares, apartamentos).

## Descrição

O Gestor Financeiro é um plugin WordPress completo que permite gerir as finanças de uma empresa com múltiplos estabelecimentos. Funciona sem ACF ou CPTs, utilizando tabelas personalizadas e uma interface frontend via shortcode.

## Características

- **Gestão Multi-Estabelecimento**: Suporta restaurantes, bares e apartamentos
- **Dashboard Frontend**: Interface completa via shortcode `[gestor_financeiro_dashboard]`
- **Alertas por Email**: Notificações diárias para pagamentos em atraso
- **Relatórios Mensais**: P&L, folha salarial, ranking de fornecedores
- **Importação/Exportação CSV**: Com preview e validação
- **Gestão de Salários**: Fixos e diários, com marcação de pagamento
- **Sistema de Apartamentos**: Categorias pré-definidas e transações recorrentes
- **Segurança**: Roles personalizados, capabilities, nonces, prepared statements

## Requisitos

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+

## Instalação

1. Coloque a pasta `gestor-financeiro` em `/wp-content/plugins/`
2. Ative o plugin através do ecrã 'Plugins' no WordPress
3. As tabelas da base de dados serão criadas automaticamente
4. Os dados de demonstração serão criados automaticamente (via MU-plugin)

## Uso

### Shortcode

Use o shortcode `[gestor_financeiro_dashboard]` numa página ou post para exibir o dashboard.

### Roles e Capabilities

O plugin cria três roles personalizados:

- **gestor_owner**: Acesso total a todos os estabelecimentos
- **gestor_manager**: Ver/editar dentro dos seus estabelecimentos
- **gestor_viewer**: Apenas leitura

Capabilities:
- `gestor_ver`: Permissão para visualizar dados
- `gestor_editar`: Permissão para editar dados

### API REST

Todas as operações CRUD estão disponíveis via REST API:

```
/wp-json/gestor-financeiro/v1/estabelecimentos
/wp-json/gestor-financeiro/v1/fornecedores
/wp-json/gestor-financeiro/v1/funcionarios
/wp-json/gestor-financeiro/v1/despesas
/wp-json/gestor-financeiro/v1/receitas
/wp-json/gestor-financeiro/v1/obrigacoes
/wp-json/gestor-financeiro/v1/settings
/wp-json/gestor-financeiro/v1/dashboard/summary
/wp-json/gestor-financeiro/v1/calendar
/wp-json/gestor-financeiro/v1/salaries
/wp-json/gestor-financeiro/v1/reports/monthly
/wp-json/gestor-financeiro/v1/csv/export
/wp-json/gestor-financeiro/v1/csv/import
/wp-json/gestor-financeiro/v1/apartments/categories
```

### Alertas Diários

Os alertas são enviados diariamente às 08:00 via WP-Cron. Configure o email de alertas nas definições do plugin.

### Relatórios

Os relatórios mensais incluem:
- P&L (Receitas - Despesas - Salários - Impostos)
- Folha Salarial detalhada
- Top 10 Fornecedores por valor total
- Exportação CSV

### Importação CSV

1. Prepare um ficheiro CSV com as colunas corretas
2. Use o endpoint `/csv/import` para fazer preview
3. Revise os dados e erros
4. Execute a importação com `/csv/import/execute`

Formato suportado:
- Separador: ponto e vírgula (`;`)
- Datas: DD/MM/YYYY ou YYYY-MM-DD
- Números: PT-PT (1.234,56) ou EN (1,234.56)

### Apartamentos

Estabelecimentos do tipo `apartamento` têm:
- Categorias pré-definidas para despesas (Condomínio, IMI, Água, etc.)
- Categorias pré-definidas para receitas (Renda, Depósito)
- Geração automática de transações recorrentes
- Suporte para dia de renda configurável

## Estrutura de Pastas

```
gestor-financeiro/
├── gestor-financeiro.php       # Ficheiro principal
├── composer.json               # Autoloading PSR-4
├── includes/                   # Código PHP
│   ├── Core/                   # Classes principais
│   ├── DB/                     # Repositórios e migrações
│   ├── Features/               # Funcionalidades principais
│   ├── Http/                   # REST API
│   └── Security/               # Segurança e permissões
├── assets/                     # CSS e JavaScript
├── templates/                   # Templates PHP
└── docs/                       # Documentação adicional
```

## Base de Dados

O plugin cria as seguintes tabelas:

- `gf_estabelecimentos`: Estabelecimentos
- `gf_fornecedores`: Fornecedores
- `gf_funcionarios`: Funcionários
- `gf_despesas`: Despesas
- `gf_receitas`: Receitas
- `gf_obrigacoes`: Obrigações fiscais
- `gf_alertas`: Histórico de alertas
- `gf_recorrencias`: Transações recorrentes
- `gf_settings`: Definições do plugin
- `gf_logs`: Logs de auditoria

## Tradução

O plugin está preparado para tradução e usa português de Portugal (pt-PT) como idioma padrão. As traduções devem ser colocadas em:

```
/languages/gestor-financeiro-pt_PT.po
```

## Contribuir

Para contribuir, por favor:

1. Siga os padrões de código PHP 8.0+ strict types
2. Use PSR-4 autoloading
3. Mantenha todas as strings traduzíveis com `__()` ou `_e()`
4. Use prepared statements para todas as queries SQL
5. Teste em WordPress 6.0+

## Changelog

### 1.0.0
- Versão inicial
- Gestão completa de estabelecimentos, fornecedores, funcionários
- Dashboard frontend
- Alertas por email
- Relatórios mensais
- Importação/Exportação CSV
- Suporte especial para apartamentos

## Licença

GPL v2 or later

## Autor

Gestor Financeiro

