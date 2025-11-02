# Gestor Financeiro - REST API Documentation

## Base URL

```
/wp-json/gestor-financeiro/v1
```

## Autenticação

Todas as requests requerem:
- Header `X-WP-Nonce`: Nonce do WordPress (obtido via `wpApiSettings.nonce` no frontend)
- Permissões adequadas: `gestor_ver` (visualização) ou `gestor_editar` (edição)

## Endpoints

### Estabelecimentos

#### GET /estabelecimentos
Lista todos os estabelecimentos.

**Query Parameters:**
- `ativo` (int): Filtrar por estado ativo (1 ou 0)
- `tipo` (string): Filtrar por tipo (restaurante, bar, apartamento)
- `page` (int): Número da página (default: 1)
- `per_page` (int): Itens por página (default: 20)

**Response:**
```json
[
  {
    "id": 1,
    "nome": "Restaurante Central",
    "tipo": "restaurante",
    "dia_renda": 5,
    "ativo": 1,
    "created_at": "2025-01-01 10:00:00",
    "updated_at": "2025-01-01 10:00:00"
  }
]
```

#### GET /estabelecimentos/{id}
Obtém um estabelecimento específico.

#### POST /estabelecimentos
Cria um novo estabelecimento.

**Body:**
```json
{
  "nome": "Novo Restaurante",
  "tipo": "restaurante",
  "dia_renda": 10,
  "ativo": 1
}
```

#### PUT /estabelecimentos/{id}
Atualiza um estabelecimento.

#### DELETE /estabelecimentos/{id}
Elimina um estabelecimento.

---

### Fornecedores

#### GET /fornecedores
Lista todos os fornecedores.

**Query Parameters:**
- `categoria` (string): Filtrar por categoria
- `page` (int): Número da página
- `per_page` (int): Itens por página

#### GET /fornecedores/{id}
Obtém um fornecedor específico.

#### POST /fornecedores
Cria um novo fornecedor.

#### PUT /fornecedores/{id}
Atualiza um fornecedor.

#### DELETE /fornecedores/{id}
Elimina um fornecedor.

---

### Funcionários

#### GET /funcionarios
Lista todos os funcionários.

**Query Parameters:**
- `estabelecimento_id` (int): Filtrar por estabelecimento
- `tipo_pagamento` (string): Filtrar por tipo (fixo ou diario)

#### GET /funcionarios/{id}
Obtém um funcionário específico.

#### POST /funcionarios
Cria um novo funcionário.

#### PUT /funcionarios/{id}
Atualiza um funcionário.

#### DELETE /funcionarios/{id}
Elimina um funcionário.

---

### Despesas

#### GET /despesas
Lista todas as despesas.

**Query Parameters:**
- `estabelecimento_id` (int): Filtrar por estabelecimento
- `fornecedor_id` (int): Filtrar por fornecedor
- `tipo` (string): Filtrar por tipo
- `pago` (int): Filtrar por estado de pagamento (1 ou 0)
- `start_date` (string): Data início (YYYY-MM-DD)
- `end_date` (string): Data fim (YYYY-MM-DD)
- `page` (int): Número da página
- `per_page` (int): Itens por página

#### GET /despesas/{id}
Obtém uma despesa específica.

#### POST /despesas
Cria uma nova despesa.

**Body:**
```json
{
  "data": "2025-01-15",
  "estabelecimento_id": 1,
  "fornecedor_id": 1,
  "tipo": "alimentação",
  "descricao": "Compras semanais",
  "valor": 450.00,
  "vencimento": "2025-01-20",
  "pago": 0,
  "notas": "Compras para o restaurante"
}
```

#### PUT /despesas/{id}
Atualiza uma despesa.

#### DELETE /despesas/{id}
Elimina uma despesa.

---

### Receitas

#### GET /receitas
Lista todas as receitas.

**Query Parameters:**
- `estabelecimento_id` (int): Filtrar por estabelecimento
- `start_date` (string): Data início (YYYY-MM-DD)
- `end_date` (string): Data fim (YYYY-MM-DD)

#### GET /receitas/{id}
Obtém uma receita específica.

#### POST /receitas
Cria uma nova receita.

**Body:**
```json
{
  "data": "2025-01-15",
  "estabelecimento_id": 1,
  "bruto": 2500.00,
  "taxas": 125.00,
  "liquido": 2375.00,
  "notas": "Vendas do dia"
}
```

#### PUT /receitas/{id}
Atualiza uma receita.

#### DELETE /receitas/{id}
Elimina uma receita.

---

### Obrigações

#### GET /obrigacoes
Lista todas as obrigações.

#### GET /obrigacoes/{id}
Obtém uma obrigação específica.

#### POST /obrigacoes
Cria uma nova obrigação.

#### PUT /obrigacoes/{id}
Atualiza uma obrigação.

#### DELETE /obrigacoes/{id}
Elimina uma obrigação.

---

### Dashboard

#### GET /dashboard/summary
Obtém resumo do dashboard.

**Query Parameters:**
- `month` (int): Mês (1-12)
- `year` (int): Ano
- `estabelecimento_id` (int): Filtrar por estabelecimento

**Response:**
```json
{
  "receita_mes": 10000.00,
  "despesas_mes": 5000.00,
  "resultado": 5000.00,
  "por_pagar": 1500.00,
  "month": 1,
  "year": 2025
}
```

---

### Calendário

#### GET /calendar
Obtém eventos do calendário.

**Query Parameters:**
- `start` (string): Data início (YYYY-MM-DD)
- `end` (string): Data fim (YYYY-MM-DD)

**Response:**
```json
[
  {
    "id": "despesa_1",
    "title": "Compras semanais",
    "date": "2025-01-20",
    "type": "despesa",
    "value": 450.00
  }
]
```

---

### Salários

#### GET /salaries
Lista todos os funcionários com informação de salários.

**Query Parameters:**
- `estabelecimento_id` (int): Filtrar por estabelecimento
- `tipo_pagamento` (string): Filtrar por tipo (fixo ou diario)

#### PATCH /salaries/{id}/mark-paid
Marca um salário como pago.

**Body:**
```json
{
  "data": "2025-01-15"
}
```

---

### Relatórios

#### GET /reports/monthly
Gera relatório mensal.

**Query Parameters:**
- `month` (int): Mês (1-12)
- `year` (int): Ano
- `estabelecimento_id` (int): Filtrar por estabelecimento

**Response:**
```json
{
  "mes": 1,
  "ano": 2025,
  "receitas": {
    "bruto": 10000.00,
    "taxas": 500.00,
    "liquido": 9500.00
  },
  "despesas": {
    "total": 5000.00
  },
  "salarios": {
    "total": 2500.00
  },
  "impostos": {
    "total": 800.00
  },
  "resultado": 1200.00,
  "folha_salarial": [...],
  "top_fornecedores": [...]
}
```

#### GET /reports/monthly/export
Exporta relatório mensal em CSV.

---

### CSV

#### GET /csv/export
Exporta despesas ou receitas em CSV.

**Query Parameters:**
- `type` (string): Tipo (despesas ou receitas)
- `start_date` (string): Data início (YYYY-MM-DD)
- `end_date` (string): Data fim (YYYY-MM-DD)
- `estabelecimento_id` (int): Filtrar por estabelecimento

#### POST /csv/import
Importa CSV com preview e validação.

**Body:**
```json
{
  "csv_content": "...",
  "type": "despesas"
}
```

**Response:**
```json
{
  "success": true,
  "preview": [...],
  "errors": {...}
}
```

#### POST /csv/import/execute
Executa importação após preview.

**Body:**
```json
{
  "preview_data": [...],
  "type": "despesas"
}
```

---

### Apartamentos

#### GET /apartments/categories
Obtém categorias pré-definidas para apartamentos.

**Response:**
```json
{
  "expense_categories": ["Condomínio", "IMI", "Água", ...],
  "revenue_categories": ["Renda", "Depósito", "Outros"]
}
```

#### POST /apartments/{id}/generate-recurring
Gera transações recorrentes para um apartamento.

**Body:**
```json
{
  "start_date": "2025-01-01",
  "end_date": "2025-12-31"
}
```

---

### Definições

#### GET /settings
Obtém todas as definições.

#### PUT /settings
Atualiza definições.

**Body:**
```json
{
  "alerts_email": "admin@example.com",
  "cron_hour": 8,
  "currency": "EUR"
}
```

---

## Códigos de Erro HTTP

- `200`: Sucesso
- `201`: Criado com sucesso
- `204`: Sem conteúdo (delete bem-sucedido)
- `400`: Erro de validação ou dados inválidos
- `401`: Não autorizado
- `403`: Proibido (sem permissão)
- `404`: Não encontrado
- `500`: Erro interno do servidor

## Formato de Erro

```json
{
  "code": "error_code",
  "message": "Mensagem de erro em PT-PT",
  "data": {
    "status": 400
  }
}
```

