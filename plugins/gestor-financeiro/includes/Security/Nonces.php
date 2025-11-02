<?php
/**
 * Nonce handling utilities.
 *
 * @package GestorFinanceiro
 * @subpackage Security
 */

declare(strict_types=1);

namespace GestorFinanceiro\Security;

/**
 * Handle nonce generation and validation.
 */
class Nonces {

	/**
	 * Nonce action prefix.
	 */
	private const NONCE_PREFIX = 'gestor_financeiro_';

	/**
	 * Generate nonce for an action.
	 *
	 * @param string $action Action name.
	 * @return string Nonce.
	 */
	public static function create( string $action ): string {
		return wp_create_nonce( self::NONCE_PREFIX . $action );
	}

	/**
	 * Verify nonce for an action.
	 *
	 * @param string $nonce Nonce to verify.
	 * @param string $action Action name.
	 * @return bool Valid nonce.
	 */
	public static function verify( string $nonce, string $action ): bool {
		return (bool) wp_verify_nonce( $nonce, self::NONCE_PREFIX . $action );
	}

	/**
	 * Verify nonce from request (GET or POST).
	 *
	 * @param string $action Action name.
	 * @param string $nonce_field Field name containing nonce. Default: '_wpnonce'.
	 * @return bool Valid nonce.
	 */
	public static function verify_request( string $action, string $nonce_field = '_wpnonce' ): bool {
		$nonce = '';
		if ( isset( $_REQUEST[ $nonce_field ] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_field ] ) );
		}

		if ( empty( $nonce ) ) {
			return false;
		}

		return self::verify( $nonce, $action );
	}

	/**
	 * Verify AJAX nonce.
	 *
	 * @param string $action Action name.
	 * @param bool   $die_on_failure Die on failure. Default: true.
	 * @return bool Valid nonce.
	 */
	public static function verify_ajax( string $action, bool $die_on_failure = true ): bool {
		check_ajax_referer( self::NONCE_PREFIX . $action, '_wpnonce', $die_on_failure );
		return true;
	}

	/**
	 * Get nonce field HTML.
	 *
	 * @param string $action Action name.
	 * @param string $name Field name. Default: '_wpnonce'.
	 * @param bool   $referer Include referer field. Default: true.
	 * @return string Nonce field HTML.
	 */
	public static function field( string $action, string $name = '_wpnonce', bool $referer = true ): string {
		return wp_nonce_field( self::NONCE_PREFIX . $action, $name, $referer, false );
	}

	/**
	 * Get nonce URL.
	 *
	 * @param string $actionurl URL to add nonce to.
	 * @param string $action Action name.
	 * @param string $name Nonce name. Default: '_wpnonce'.
	 * @return string URL with nonce.
	 */
	public static function url( string $actionurl, string $action, string $name = '_wpnonce' ): string {
		return wp_nonce_url( $actionurl, self::NONCE_PREFIX . $action, $name );
	}

	/**
	 * Verify REST API nonce from header.
	 *
	 * @param string $nonce Nonce from header.
	 * @return bool Valid nonce.
	 */
	public static function verify_rest( string $nonce ): bool {
		return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Get REST API nonce for JavaScript.
	 *
	 * @return string Nonce for REST API.
	 */
	public static function get_rest_nonce(): string {
		return wp_create_nonce( 'wp_rest' );
	}
}

