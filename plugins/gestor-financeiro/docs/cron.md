# Gestor Financeiro - Cron Documentation

## Sistema de Alertas Diários

O Gestor Financeiro utiliza WP-Cron para enviar alertas diários sobre pagamentos devidos.

### Funcionamento

- **Hora padrão:** 08:00 (configurável nas Definições)
- **Frequência:** Diariamente
- **Hook:** `gestor_financeiro_daily_alerts`
- **Conteúdo:** Alertas para pagamentos devidos hoje, próximos 7 dias e atrasados

### Configuração

A hora do cron pode ser configurada na aba "Definições" do dashboard:
- Campo: "Hora do cron (0-23)"
- Valor padrão: 8 (08:00)
- Alterações são aplicadas automaticamente após guardar

O e-mail de destino é configurado no campo "E-mail para alertas" nas Definições.

### Resolução de Problemas

#### WP-Cron não está a funcionar

Se o WP-Cron não estiver a funcionar (comum em alguns ambientes), pode usar o WP-CLI como alternativa:

```bash
# Disparar alertas manualmente
wp gestor-financeiro alerts

# Configurar cron do sistema para executar às 08:00
0 8 * * * wp --path=/caminho/para/wordpress gestor-financeiro alerts
```

#### Verificar se o cron está agendado

```bash
# Via WP-CLI
wp cron event list | grep gestor_financeiro_daily_alerts

# Ou verificar no código
wp option get cron | grep gestor_financeiro_daily_alerts
```

#### Testar manualmente

Pode testar os alertas manualmente através de:
1. WP-CLI: `wp gestor-financeiro alerts`
2. REST API: `POST /wp-json/gestor-financeiro/v1/alerts/trigger` (requer permissões)
3. PHP: `do_action('gestor_financeiro_daily_alerts');`

### Recomendações para Produção

Para ambientes de produção, é recomendado:
1. Desabilitar WP-Cron no `wp-config.php`: `define('DISABLE_WP_CRON', true);`
2. Configurar cron do sistema para chamar WP-CLI:
   ```bash
   0 8 * * * /usr/bin/php /caminho/para/wp-cli.phar --path=/caminho/para/wordpress gestor-financeiro alerts
   ```

Isto garante execução mais confiável e pontual dos alertas.

### Conteúdo dos Alertas

Os alertas incluem:
- **Hoje:** Pagamentos devidos hoje (rendas, salários, fornecedores)
- **Próximos 7 dias:** Pagamentos devidos nos próximos 7 dias
- **Atrasados:** Pagamentos em atraso

Cada alerta inclui:
- Data de vencimento
- Descrição/Entidade
- Valor
- Tipo (despesa, salário, obrigação)

