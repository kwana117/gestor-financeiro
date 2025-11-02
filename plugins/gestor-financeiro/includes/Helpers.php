<?php
/**
 * Helper functions.
 *
 * @package GestorFinanceiro
 */

declare(strict_types=1);

namespace GestorFinanceiro;

/**
 * Get plugin version.
 *
 * @return string
 */
function get_plugin_version(): string {
	return GF_VERSION;
}

/**
 * Get plugin directory path.
 *
 * @param string $path Optional path to append.
 * @return string
 */
function get_plugin_dir( string $path = '' ): string {
	$dir = GF_PLUGIN_DIR;
	if ( $path ) {
		$dir = trailingslashit( $dir ) . ltrim( $path, '/' );
	}
	return $dir;
}

/**
 * Get plugin URL.
 *
 * @param string $path Optional path to append.
 * @return string
 */
function get_plugin_url( string $path = '' ): string {
	$url = GF_PLUGIN_URL;
	if ( $path ) {
		$url = trailingslashit( $url ) . ltrim( $path, '/' );
	}
	return $url;
}

/**
 * Check if user has gestor permission.
 *
 * @param string $capability Capability to check.
 * @return bool
 */
function user_can_gestor( string $capability = 'gestor_ver' ): bool {
	return current_user_can( $capability );
}

/**
 * Sanitize text input.
 *
 * @param mixed $value Value to sanitize.
 * @return string
 */
function sanitize_text( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}
	return sanitize_text_field( $value );
}

/**
 * Sanitize email.
 *
 * @param mixed $value Value to sanitize.
 * @return string
 */
function sanitize_email_input( $value ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}
	return sanitize_email( $value );
}

/**
 * Sanitize integer.
 *
 * @param mixed $value Value to sanitize.
 * @return int
 */
function sanitize_int( $value ): int {
	return absint( $value );
}

/**
 * Sanitize float/decimal.
 *
 * @param mixed $value Value to sanitize.
 * @return float
 */
function sanitize_float( $value ): float {
	return (float) filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
}

