# Guia de Utilizador - Gestor Financeiro

## Índice

1. [Introdução](#introdução)
2. [Começar a Usar](#começar-a-usar)
3. [Gestão de Estabelecimentos](#gestão-de-estabelecimentos)
4. [Gestão de Fornecedores](#gestão-de-fornecedores)
5. [Gestão de Funcionários](#gestão-de-funcionários)
6. [Gestão de Despesas](#gestão-de-despesas)
7. [Gestão de Receitas](#gestão-de-receitas)
8. [Gestão de Obrigações Fiscais](#gestão-de-obrigações-fiscais)
9. [Dashboard e Relatórios](#dashboard-e-relatórios)
10. [Importação e Exportação CSV](#importação-e-exportação-csv)
11. [Funcionalidades de Apartamentos](#funcionalidades-de-apartamentos)
12. [Alertas e Notificações](#alertas-e-notificações)
13. [Definições](#definições)
14. [Boas Práticas](#boas-práticas)
15. [Resolução de Problemas](#resolução-de-problemas)

---

## Introdução

O **Gestor Financeiro** é um sistema completo de gestão financeira desenvolvido para empresas com múltiplos estabelecimentos. Permite gerir despesas, receitas, salários, fornecedores e obrigações fiscais de forma centralizada e organizada.

### Características Principais

- ✅ Gestão multi-estabelecimento (restaurantes, bares, apartamentos)
- ✅ Dashboard intuitivo e responsivo
- ✅ Alertas automáticos por email
- ✅ Relatórios mensais detalhados (P&L, folha salarial, ranking de fornecedores)
- ✅ Importação e exportação CSV
- ✅ Suporte especial para apartamentos com transações recorrentes
- ✅ Gestão de salários (fixos e diários)
- ✅ Calendário de vencimentos

---

## Começar a Usar

### 1. Acesso ao Dashboard

O dashboard está disponível através do shortcode `[gestor_financeiro_dashboard]` numa página WordPress.

**Passos:**
1. Crie uma nova página no WordPress
2. Adicione o shortcode `[gestor_financeiro_dashboard]`
3. Publique a página
4. Acesse a página - o dashboard será exibido automaticamente

### 2. Permissões e Acesso

O plugin utiliza três níveis de acesso:

- **Gestor Owner**: Acesso total a todos os estabelecimentos
- **Gestor Manager**: Ver e editar dentro dos seus estabelecimentos atribuídos
- **Gestor Viewer**: Apenas visualização (leitura)

Se não conseguir visualizar ou editar dados, verifique as suas permissões com o administrador.

### 3. Primeiros Passos

1. **Configure as Definições**: Aceda à aba "Definições" e configure:
   - E-mail para alertas
   - Hora do cron (padrão: 08:00)
   - Moeda

2. **Crie Estabelecimentos**: Adicione os seus estabelecimentos (restaurantes, bares, apartamentos)

3. **Adicione Fornecedores**: Registe os fornecedores principais

4. **Registe Funcionários**: Adicione os funcionários e configure os salários

5. **Configure Obrigações**: Registe as obrigações fiscais recorrentes (IVA, SS, IRS)

---

## Gestão de Estabelecimentos

### Criar um Estabelecimento

1. Aceda à aba **"Movimentos"** ou utilize o endpoint REST
2. Clique em **"Adicionar Estabelecimento"**
3. Preencha os campos:
   - **Nome**: Nome do estabelecimento
   - **Tipo**: Selecione entre Restaurante, Bar ou Apartamento
   - **Dia de Renda** (opcional): Dia do mês em que recebe a renda (apenas para apartamentos)
   - **Ativo**: Marque se o estabelecimento está ativo

4. Clique em **"Guardar"**

### Tipos de Estabelecimento

- **Restaurante**: Estabelecimento de restauração
- **Bar**: Estabelecimento de bebidas
- **Apartamento**: Propriedade para arrendamento (com funcionalidades especiais)

**Nota:** Estabelecimentos do tipo "Apartamento" têm categorias pré-definidas e suporte para transações recorrentes.

### Editar um Estabelecimento

1. Na lista de estabelecimentos, clique no estabelecimento que pretende editar
2. Modifique os campos necessários
3. Clique em **"Guardar"**

### Eliminar um Estabelecimento

⚠️ **Atenção:** Eliminar um estabelecimento eliminará todas as despesas, receitas e funcionários associados.

1. Clique no estabelecimento que pretende eliminar
2. Clique em **"Eliminar"**
3. Confirme a eliminação

---

## Gestão de Fornecedores

### Adicionar um Fornecedor

1. Aceda à secção de **Fornecedores**
2. Clique em **"Adicionar Fornecedor"**
3. Preencha os dados:
   - **Nome**: Nome do fornecedor
   - **Categoria**: Categoria do fornecedor (ex: Alimentação, Bebidas, Serviços)
   - **NIF**: Número de Identificação Fiscal
   - **Contacto**: E-mail ou telefone
   - **Morada**: Morada completa

4. Clique em **"Guardar"**

### Categorias Recomendadas

- Alimentação
- Bebidas
- Carnes
- Peixe
- Produtos de limpeza
- Serviços
- Manutenção
- Seguros

### Boas Práticas

- ✅ Mantenha os dados atualizados (NIF, contacto)
- ✅ Use categorias consistentes para facilitar relatórios
- ✅ Associe fornecedores às despesas para melhor rastreabilidade

---

## Gestão de Funcionários

### Adicionar um Funcionário

1. Aceda à aba **"Salários"** ou **"Movimentos"**
2. Clique em **"Adicionar Funcionário"**
3. Preencha os dados:
   - **Nome**: Nome completo do funcionário
   - **Estabelecimento**: Selecione o estabelecimento
   - **Tipo de Pagamento**: 
     - **Fixo**: Salário mensal fixo
     - **Diário**: Valor por dia trabalhado
   - **Valor Base**: Valor do salário (mensal para fixo, diário para diário)
   - **IBAN**: Número de conta bancária

4. Clique em **"Guardar"**

### Tipos de Pagamento

#### Fixo
- Salário mensal fixo
- Recomendado para funcionários com horário regular
- O valor base é o salário mensal

#### Diário
- Pagamento por dia trabalhado
- Recomendado para funcionários ocasionais
- O valor base é o valor por dia

### Marcar Salário como Pago

1. Aceda à aba **"Salários"**
2. Na lista de funcionários, localize o funcionário
3. Clique em **"Marcar como Pago"**
4. O sistema criará automaticamente uma despesa do tipo "salário" com a data de pagamento

**Nota:** O sistema mantém histórico de todos os pagamentos de salários.

---

## Gestão de Despesas

### Adicionar uma Despesa

1. Aceda à aba **"Movimentos"**
2. Clique em **"Adicionar Despesa"**
3. Preencha os campos:
   - **Data**: Data da despesa
   - **Estabelecimento**: Estabelecimento relacionado
   - **Fornecedor** (opcional): Fornecedor associado
   - **Funcionário** (opcional): Funcionário relacionado (para salários)
   - **Tipo**: Tipo de despesa
   - **Descrição**: Descrição detalhada
   - **Valor**: Valor da despesa
   - **Vencimento**: Data de vencimento do pagamento
   - **Pago**: Marque se já foi pago
   - **Notas**: Observações adicionais

4. Clique em **"Guardar"**

### Tipos de Despesa

Os tipos de despesa variam consoante o tipo de estabelecimento:

**Restaurantes/Bares:**
- Alimentação
- Bebidas
- Carnes
- Peixe
- Produtos de limpeza
- Manutenção
- Seguros
- Outros

**Apartamentos:**
- Condomínio
- IMI
- Água
- Eletricidade
- Gás
- Internet
- Telefone
- Seguro
- Manutenção
- Limpeza
- Outros

### Filtrar Despesas

Na aba **"Movimentos"**, pode filtrar despesas por:
- **Estabelecimento**: Ver apenas despesas de um estabelecimento
- **Tipo**: Filtrar por tipo de despesa
- **Período**: Selecione intervalo de datas
- **Estado**: Pagas ou por pagar

### Editar uma Despesa

1. Na lista de despesas, clique na despesa que pretende editar
2. Modifique os campos necessários
3. Clique em **"Guardar"**

### Marcar Despesa como Paga

1. Clique na despesa
2. Marque a opção **"Pago"**
3. O campo **"Pago em"** será preenchido automaticamente
4. Clique em **"Guardar"**

---

## Gestão de Receitas

### Adicionar uma Receita

1. Aceda à aba **"Movimentos"**
2. Clique em **"Adicionar Receita"**
3. Preencha os campos:
   - **Data**: Data da receita
   - **Estabelecimento**: Estabelecimento relacionado
   - **Bruto**: Valor bruto (antes de taxas)
   - **Taxas**: Valor das taxas cobradas
   - **Líquido**: Valor líquido (calculado automaticamente: Bruto - Taxas)
   - **Notas**: Observações adicionais

4. Clique em **"Guardar"**

### Tipos de Receita

**Restaurantes/Bares:**
- Vendas do dia
- Eventos
- Catering
- Outros

**Apartamentos:**
- Renda (receita principal)
- Depósito (caução)
- Outros

### Filtrar Receitas

Pode filtrar receitas por:
- **Estabelecimento**: Ver apenas receitas de um estabelecimento
- **Período**: Selecione intervalo de datas

---

## Gestão de Obrigações Fiscais

### Adicionar uma Obrigação

1. Aceda à secção de **Obrigações**
2. Clique em **"Adicionar Obrigação"**
3. Preencha os dados:
   - **Nome**: Nome da obrigação (ex: "IVA - Trimestral")
   - **Descrição**: Descrição detalhada
   - **Valor**: Valor da obrigação
   - **Periodicidade**: Selecione entre:
     - **Mensal**: Todos os meses
     - **Trimestral**: De 3 em 3 meses
     - **Anual**: Uma vez por ano
   - **Vencimento**: Data de vencimento

4. Clique em **"Guardar"**

### Obrigações Comuns

- **IVA**: Imposto sobre o Valor Acrescentado (trimestral ou mensal)
- **SS**: Segurança Social dos funcionários (mensal)
- **IRS**: Imposto sobre o Rendimento das Pessoas Singulares (anual)

### Boas Práticas

- ✅ Configure as obrigações no início do ano
- ✅ Use datas de vencimento realistas
- ✅ Mantenha os valores atualizados
- ✅ Marque como pago após pagamento

---

## Dashboard e Relatórios

### Resumo do Dashboard

A aba **"Resumo"** apresenta indicadores-chave:

- **Receita do Mês**: Total de receitas do mês atual
- **Despesas do Mês**: Total de despesas do mês atual
- **Resultado**: Diferença entre receitas e despesas
- **Por Pagar**: Total de despesas ainda não pagas

### Calendário

A aba **"Calendário"** mostra:
- **Pagamentos devidos hoje**: Despesas e obrigações com vencimento hoje
- **Próximos 7 dias**: Pagamentos que vencem nos próximos 7 dias
- **Atrasados**: Pagamentos em atraso

### Relatórios Mensais

A aba **"Relatórios"** permite gerar relatórios detalhados:

1. Selecione o **Mês** e **Ano**
2. Opcionalmente, selecione um **Estabelecimento** específico
3. Clique em **"Gerar Relatório"**

#### Conteúdo do Relatório

- **Resultados**:
  - Receitas (Bruto, Taxas, Líquido)
  - Despesas
  - Salários
  - Impostos
  - Resultado Final (P&L)

- **Folha Salarial**: Lista detalhada de todos os funcionários com:
  - Nome
  - Tipo de pagamento
  - Valor base
  - Salário do mês
  - Estado de pagamento

- **Top 10 Fornecedores**: Ranking dos maiores fornecedores por valor total

### Exportar Relatório

1. Gere o relatório desejado
2. Clique em **"Exportar CSV"**
3. O ficheiro CSV será descarregado automaticamente

---

## Importação e Exportação CSV

### Exportar Dados

1. Aceda à funcionalidade de **Exportação CSV**
2. Selecione o **Tipo** (Despesas ou Receitas)
3. Selecione o **Período** (datas de início e fim)
4. Opcionalmente, selecione um **Estabelecimento**
5. Clique em **"Exportar"**
6. O ficheiro CSV será descarregado

### Formato do CSV

O CSV exportado utiliza:
- **Separador**: Ponto e vírgula (`;`)
- **Encoding**: UTF-8 com BOM (compatível com Excel)
- **Formato de números**: PT-PT (1.234,56)
- **Formato de datas**: DD/MM/YYYY

### Importar Dados

#### Passo 1: Preparar o Ficheiro

1. Prepare um ficheiro CSV com as colunas corretas
2. Use o separador ponto e vírgula (`;`)
3. Inclua uma linha de cabeçalho

**Colunas para Despesas:**
- Data
- Estabelecimento
- Fornecedor (opcional)
- Funcionário (opcional)
- Tipo
- Descrição
- Valor
- Vencimento (opcional)
- Pago (Sim/Não)
- Notas (opcional)

**Colunas para Receitas:**
- Data
- Estabelecimento
- Bruto
- Taxas
- Líquido
- Notas (opcional)

#### Passo 2: Preview e Validação

1. Selecione o **Tipo** (Despesas ou Receitas)
2. Cole o conteúdo do CSV ou faça upload
3. Clique em **"Importar"**
4. O sistema mostrará um **preview** dos dados e validará:
   - Formato de datas
   - Formato de números
   - Estabelecimentos existentes
   - Valores obrigatórios

#### Passo 3: Revisão

1. Revise o preview dos dados
2. Verifique os erros apontados (se houver)
3. Corrija o ficheiro CSV se necessário
4. Reimporte para novo preview

#### Passo 4: Executar Importação

1. Após validar o preview, clique em **"Executar Importação"**
2. O sistema importará os dados em batches
3. Receberá confirmação do número de registos importados

### Dicas para Importação

- ✅ Verifique sempre o preview antes de executar
- ✅ Use formatos consistentes (datas DD/MM/YYYY ou YYYY-MM-DD)
- ✅ Números podem estar em PT-PT (1.234,56) ou EN (1,234.56)
- ✅ Nomes de estabelecimentos devem corresponder exatamente
- ✅ Teste com uma pequena amostra primeiro

---

## Funcionalidades de Apartamentos

### Características Especiais

Estabelecimentos do tipo **"Apartamento"** têm funcionalidades especiais:

#### Categorias Pré-definidas

**Despesas:**
- Condomínio
- IMI
- Água
- Eletricidade
- Gás
- Internet
- Telefone
- Seguro
- Manutenção
- Limpeza
- Outros

**Receitas:**
- Renda
- Depósito
- Outros

#### Transações Recorrentes

O sistema pode gerar automaticamente transações recorrentes:

1. Aceda ao estabelecimento do tipo "Apartamento"
2. Configure o **Dia de Renda** (dia do mês em que recebe a renda)
3. Use a funcionalidade **"Gerar Transações Recorrentes"**
4. Selecione o período (datas de início e fim)
5. O sistema irá:
   - Detectar despesas recorrentes existentes (condomínio, IMI, etc.)
   - Gerar receitas de renda mensais
   - Criar as transações automaticamente

### Como Funciona

1. **Detecção Automática**: O sistema identifica automaticamente despesas recorrentes baseando-se no tipo e descrição
2. **Extração de Padrões**: Extrai o dia do mês de transações existentes
3. **Geração Mensal**: Gera transações para cada mês no período especificado
4. **Evita Duplicados**: Não cria transações que já existem

---

## Alertas e Notificações

### Alertas Diários

O sistema envia alertas automáticos por email diariamente às **08:00** (configurável).

### Conteúdo dos Alertas

Os alertas incluem:

1. **Hoje**: Pagamentos devidos hoje
2. **Próximos 7 dias**: Pagamentos que vencem nos próximos 7 dias
3. **Atrasados**: Pagamentos em atraso

Para cada alerta é exibido:
- Data de vencimento
- Descrição
- Valor
- Estabelecimento relacionado
- Tipo (despesa, obrigação, salário)

### Configuração de Alertas

1. Aceda à aba **"Definições"**
2. Configure o **"E-mail para alertas"**
3. Configure a **"Hora do cron"** (0-23)
4. Clique em **"Guardar"**

### Desativar Alertas

Para desativar temporariamente os alertas, configure o email para um endereço que não utilize ou desative o cron do WordPress.

---

## Definições

A aba **"Definições"** permite configurar:

### E-mail para Alertas

- E-mail que receberá os alertas diários
- Padrão: E-mail do administrador do WordPress

### Hora do Cron

- Hora do dia em que os alertas são enviados (formato 24h)
- Padrão: 8 (08:00)
- Valores válidos: 0-23

### Moeda

- Moeda utilizada nos relatórios e exportações
- Padrão: EUR

### Guardar Alterações

Após modificar as definições, clique em **"Guardar"** para aplicar as alterações.

---

## Boas Práticas

### Organização

1. **Use Categorias Consistentes**: Mantenha categorias consistentes para facilitar relatórios
2. **Registe Tudo**: Registe todas as despesas e receitas, mesmo pequenas
3. **Mantenha Dados Atualizados**: Atualize informações de fornecedores e funcionários regularmente
4. **Use Notas**: Utilize o campo "Notas" para informações adicionais importantes

### Gestão de Despesas

1. **Registe Imediatamente**: Registe despesas assim que ocorrem
2. **Mantenha Comprovativos**: Guarde comprovativos físicos ou digitais
3. **Marque Pagamentos**: Marque despesas como pagas assim que forem pagas
4. **Use Vencimentos**: Configure datas de vencimento corretas para alertas

### Gestão de Receitas

1. **Registe Diariamente**: Registe receitas diariamente para melhor controlo
2. **Separe Bruto e Taxas**: Registre valor bruto e taxas separadamente
3. **Use Líquido**: O sistema calcula automaticamente o líquido (Bruto - Taxas)

### Relatórios

1. **Gere Mensalmente**: Gere relatórios mensais para análise regular
2. **Compare Períodos**: Compare meses diferentes para identificar tendências
3. **Exporte e Arquive**: Exporte relatórios em CSV e arquive para histórico

### Apartamentos

1. **Configure Dia de Renda**: Configure o dia de renda no estabelecimento
2. **Use Transações Recorrentes**: Use a funcionalidade de transações recorrentes para automatizar
3. **Revise Mensalmente**: Revise transações geradas automaticamente

### Importação CSV

1. **Teste Primeiro**: Teste importação com uma pequena amostra
2. **Revise Preview**: Sempre revise o preview antes de executar
3. **Mantenha Backup**: Mantenha backup dos dados antes de importações grandes
4. **Formato Correto**: Use formatos corretos (datas, números)

---

## Resolução de Problemas

### Não consigo ver o dashboard

**Soluções:**
- Verifique se o shortcode `[gestor_financeiro_dashboard]` está na página
- Verifique as suas permissões (precisa de `gestor_ver`)
- Verifique se o plugin está ativo

### Não recebo alertas por email

**Soluções:**
- Verifique o e-mail configurado nas Definições
- Verifique se o WordPress Cron está a funcionar
- Verifique o spam/lixo eletrónico
- Teste o envio de email do WordPress

### Erro ao importar CSV

**Soluções:**
- Verifique o formato do ficheiro (separador `;`)
- Verifique os formatos de data (DD/MM/YYYY ou YYYY-MM-DD)
- Verifique os formatos de números (PT-PT ou EN)
- Verifique se os nomes de estabelecimentos correspondem exatamente
- Revise os erros no preview antes de executar

### Dados não aparecem nos relatórios

**Soluções:**
- Verifique se os dados estão dentro do período selecionado
- Verifique se os dados estão associados ao estabelecimento correto
- Verifique filtros aplicados

### Erro ao gerar transações recorrentes

**Soluções:**
- Verifique se o estabelecimento é do tipo "Apartamento"
- Verifique se existem transações anteriores para detectar padrões
- Verifique se o período está correto (data início < data fim)

### Performance Lenta

**Soluções:**
- Use filtros para reduzir a quantidade de dados exibidos
- Use paginação para grandes listas
- Exporte dados antigos e arquive
- Considere otimizar a base de dados

### Ajuda Adicional

Se precisar de ajuda adicional:
1. Consulte a documentação técnica em `/docs/API.md`
2. Consulte a documentação de cron em `/docs/cron.md`
3. Contacte o suporte técnico

---

## Conclusão

O Gestor Financeiro é uma ferramenta poderosa para gerir as finanças da sua empresa. Com este guia, deve conseguir utilizar todas as funcionalidades principais.

Lembre-se:
- ✅ Registe tudo regularmente
- ✅ Revise relatórios mensalmente
- ✅ Configure alertas corretamente
- ✅ Mantenha dados atualizados
- ✅ Use filtros para melhor organização

**Boa gestão financeira!** 📊💰

