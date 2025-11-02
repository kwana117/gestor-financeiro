<?php
/**
 * Daily alerts email template.
 *
 * @package GestorFinanceiro
 * @var array<string, mixed> $alerts_data Alert data.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings_repo = new \GestorFinanceiro\DB\Repositories\SettingsRepository();
$currency = $settings_repo->get( 'currency', 'EUR' );
$currency_symbol = 'EUR' === $currency ? '€' : ( 'USD' === $currency ? '$' : '£' );

/**
 * Format currency value.
 *
 * @param float $value Value to format.
 * @return string
 */
function format_currency_value( float $value ): string {
	global $currency_symbol;
	return number_format( (float) $value, 2, ',', ' ' ) . ' ' . $currency_symbol;
}

/**
 * Format date in PT-PT format.
 *
 * @param string $date Date string (YYYY-MM-DD).
 * @return string
 */
function format_date_pt( string $date ): string {
	$timestamp = strtotime( $date );
	if ( false === $timestamp ) {
		return $date;
	}
	return date( 'd/m/Y', $timestamp );
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html__( 'Alertas Diários', 'gestor-financeiro' ); ?></title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			max-width: 600px;
			margin: 0 auto;
			padding: 20px;
		}
		.header {
			background: #2271b1;
			color: #fff;
			padding: 20px;
			border-radius: 5px 5px 0 0;
		}
		.content {
			background: #f9f9f9;
			padding: 20px;
			border: 1px solid #ddd;
		}
		.section {
			margin-bottom: 30px;
		}
		.section h2 {
			color: #2271b1;
			border-bottom: 2px solid #2271b1;
			padding-bottom: 10px;
			margin-top: 0;
		}
		.alert-item {
			background: #fff;
			padding: 15px;
			margin-bottom: 10px;
			border-left: 4px solid #2271b1;
			border-radius: 4px;
		}
		.alert-item.overdue {
			border-left-color: #d63638;
		}
		.alert-item.today {
			border-left-color: #f0a000;
		}
		.alert-item .date {
			font-weight: bold;
			color: #666;
		}
		.alert-item .value {
			font-size: 18px;
			font-weight: bold;
			color: #2271b1;
		}
		.alert-item .value.negative {
			color: #d63638;
		}
		.empty {
			color: #666;
			font-style: italic;
		}
		.footer {
			background: #f0f0f1;
			padding: 15px;
			text-align: center;
			color: #666;
			font-size: 12px;
			border-radius: 0 0 5px 5px;
		}
	</style>
</head>
<body>
	<div class="header">
		<h1><?php echo esc_html__( 'Alertas Diários', 'gestor-financeiro' ); ?></h1>
		<p><?php echo esc_html( date_i18n( 'd/m/Y H:i' ) ); ?></p>
	</div>

	<div class="content">
		<!-- Seção: Hoje -->
		<div class="section">
			<h2><?php echo esc_html__( 'Hoje', 'gestor-financeiro' ); ?></h2>
			<?php if ( ! empty( $alerts_data['hoje'] ) ) : ?>
				<?php foreach ( $alerts_data['hoje'] as $alert ) : ?>
					<div class="alert-item today">
						<div class="date"><?php echo esc_html( format_date_pt( $alert['date'] ?? $alert['vencimento'] ?? '' ) ); ?></div>
						<div class="description">
							<?php
							if ( isset( $alert['type'] ) && 'salario' === $alert['type'] ) {
								printf(
									/* translators: %1$s: funcionário name, %2$s: estabelecimento name */
									esc_html__( 'Salário: %1$s (%2$s)', 'gestor-financeiro' ),
									esc_html( $alert['funcionario'] ?? '' ),
									esc_html( $alert['estabelecimento'] ?? '' )
								);
							} elseif ( isset( $alert['type'] ) && 'obrigacao' === $alert['type'] ) {
								echo esc_html( $alert['nome'] ?? '' );
							} else {
								echo esc_html( $alert['descricao'] ?? '' );
							}
							?>
						</div>
						<?php if ( isset( $alert['valor'] ) ) : ?>
							<div class="value"><?php echo esc_html( format_currency_value( (float) $alert['valor'] ) ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="empty"><?php echo esc_html__( 'Nenhum pagamento devido hoje.', 'gestor-financeiro' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Seção: Próximos 7 dias -->
		<div class="section">
			<h2><?php echo esc_html__( 'Próximos 7 dias', 'gestor-financeiro' ); ?></h2>
			<?php if ( ! empty( $alerts_data['proximos_7_dias'] ) ) : ?>
				<?php foreach ( $alerts_data['proximos_7_dias'] as $alert ) : ?>
					<div class="alert-item">
						<div class="date"><?php echo esc_html( format_date_pt( $alert['date'] ?? $alert['vencimento'] ?? '' ) ); ?></div>
						<div class="description">
							<?php
							if ( isset( $alert['type'] ) && 'salario' === $alert['type'] ) {
								printf(
									/* translators: %1$s: funcionário name, %2$s: estabelecimento name */
									esc_html__( 'Salário: %1$s (%2$s)', 'gestor-financeiro' ),
									esc_html( $alert['funcionario'] ?? '' ),
									esc_html( $alert['estabelecimento'] ?? '' )
								);
							} elseif ( isset( $alert['type'] ) && 'obrigacao' === $alert['type'] ) {
								echo esc_html( $alert['nome'] ?? '' );
							} else {
								echo esc_html( $alert['descricao'] ?? '' );
							}
							?>
						</div>
						<?php if ( isset( $alert['valor'] ) ) : ?>
							<div class="value"><?php echo esc_html( format_currency_value( (float) $alert['valor'] ) ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="empty"><?php echo esc_html__( 'Nenhum pagamento nos próximos 7 dias.', 'gestor-financeiro' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Seção: Atrasados -->
		<div class="section">
			<h2><?php echo esc_html__( 'Atrasados', 'gestor-financeiro' ); ?></h2>
			<?php if ( ! empty( $alerts_data['atrasados'] ) ) : ?>
				<?php foreach ( $alerts_data['atrasados'] as $alert ) : ?>
					<div class="alert-item overdue">
						<div class="date"><?php echo esc_html( format_date_pt( $alert['vencimento'] ?? '' ) ); ?></div>
						<div class="description"><?php echo esc_html( $alert['descricao'] ?? '' ); ?></div>
						<?php if ( isset( $alert['valor'] ) ) : ?>
							<div class="value negative"><?php echo esc_html( format_currency_value( (float) $alert['valor'] ) ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="empty"><?php echo esc_html__( 'Nenhum pagamento em atraso.', 'gestor-financeiro' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="footer">
		<p><?php echo esc_html__( 'Este é um e-mail automático do Gestor Financeiro.', 'gestor-financeiro' ); ?></p>
		<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	</div>
</body>
</html>

