<?php
/**
 * Permission checking utilities.
 *
 * @package GestorFinanceiro
 * @subpackage Security
 */

declare(strict_types=1);

namespace GestorFinanceiro\Security;

/**
 * Handle permission checks.
 */
class Permissions {

	/**
	 * Check if current user can view gestor content.
	 *
	 * @return bool
	 */
	public static function can_view(): bool {
		return current_user_can( 'gestor_ver' );
	}

	/**
	 * Check if current user can edit gestor content.
	 *
	 * @return bool
	 */
	public static function can_edit(): bool {
		return current_user_can( 'gestor_editar' );
	}

	/**
	 * Check if current user is owner.
	 *
	 * @return bool
	 */
	public static function is_owner(): bool {
		$user = wp_get_current_user();
		return in_array( 'gestor_owner', $user->roles, true ) || user_can( $user->ID, 'administrator' );
	}

	/**
	 * Check if current user is manager.
	 *
	 * @return bool
	 */
	public static function is_manager(): bool {
		$user = wp_get_current_user();
		return in_array( 'gestor_manager', $user->roles, true ) || self::is_owner();
	}

	/**
	 * Check if current user is viewer.
	 *
	 * @return bool
	 */
	public static function is_viewer(): bool {
		$user = wp_get_current_user();
		return in_array( 'gestor_viewer', $user->roles, true ) || self::is_manager();
	}

	/**
	 * Check if user can access establishment.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @return bool
	 */
	public static function can_access_establishment( int $estabelecimento_id ): bool {
		// Owner can access all.
		if ( self::is_owner() ) {
			return true;
		}

		// Manager and viewer access will be filtered by their assigned establishments.
		// For now, if they have the capability, they can access.
		// This can be extended with establishment assignments in future.
		return self::can_view();
	}

	/**
	 * Check if user can modify establishment data.
	 *
	 * @param int $estabelecimento_id Establishment ID.
	 * @return bool
	 */
	public static function can_modify_establishment( int $estabelecimento_id ): bool {
		// Owner can modify all.
		if ( self::is_owner() ) {
			return true;
		}

		// Manager can modify if they have edit capability and access.
		if ( self::is_manager() && self::can_edit() ) {
			return self::can_access_establishment( $estabelecimento_id );
		}

		return false;
	}

	/**
	 * Require view permission or die.
	 *
	 * @return void
	 */
	public static function require_view(): void {
		if ( ! self::can_view() ) {
			wp_die(
				esc_html__( 'Não tem permissão para visualizar este conteúdo.', 'gestor-financeiro' ),
				esc_html__( 'Permissão negada', 'gestor-financeiro' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Require edit permission or die.
	 *
	 * @return void
	 */
	public static function require_edit(): void {
		if ( ! self::can_edit() ) {
			wp_die(
				esc_html__( 'Não tem permissão para editar este conteúdo.', 'gestor-financeiro' ),
				esc_html__( 'Permissão negada', 'gestor-financeiro' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Require owner permission or die.
	 *
	 * @return void
	 */
	public static function require_owner(): void {
		if ( ! self::is_owner() ) {
			wp_die(
				esc_html__( 'Apenas o proprietário pode realizar esta ação.', 'gestor-financeiro' ),
				esc_html__( 'Permissão negada', 'gestor-financeiro' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Get current user role in gestor system.
	 *
	 * @return string|null Role name or null.
	 */
	public static function get_user_role(): ?string {
		$user = wp_get_current_user();

		if ( self::is_owner() ) {
			return 'gestor_owner';
		}

		if ( self::is_manager() ) {
			return 'gestor_manager';
		}

		if ( self::is_viewer() ) {
			return 'gestor_viewer';
		}

		return null;
	}

	/**
	 * Check if REST API request is authenticated and authorized.
	 *
	 * @param string $capability Required capability. Default: 'gestor_ver'.
	 * @return bool
	 */
	public static function check_rest_permission( string $capability = 'gestor_ver' ): bool {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Check capability.
		return current_user_can( $capability );
	}

	/**
	 * Check if AJAX request is authenticated and authorized.
	 *
	 * @param string $capability Required capability. Default: 'gestor_ver'.
	 * @return bool
	 */
	public static function check_ajax_permission( string $capability = 'gestor_ver' ): bool {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Check capability.
		return current_user_can( $capability );
	}
}

