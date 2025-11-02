<?php
/**
 * Custom roles and capabilities.
 *
 * @package GestorFinanceiro
 * @subpackage Core
 */

declare(strict_types=1);

namespace GestorFinanceiro\Core;

/**
 * Handle custom roles and capabilities.
 */
class Capabilities {

	/**
	 * Register custom roles.
	 *
	 * @return void
	 */
	public static function register_roles(): void {
		// Owner role - full access.
		add_role(
			'gestor_owner',
			__( 'Gestor Owner', 'gestor-financeiro' ),
			array(
				'read' => true,
			)
		);

		// Manager role - view/edit within assigned establishments.
		add_role(
			'gestor_manager',
			__( 'Gestor Manager', 'gestor-financeiro' ),
			array(
				'read' => true,
			)
		);

		// Viewer role - read-only access.
		add_role(
			'gestor_viewer',
			__( 'Gestor Viewer', 'gestor-financeiro' ),
			array(
				'read' => true,
			)
		);
	}

	/**
	 * Register custom capabilities.
	 *
	 * @return void
	 */
	public static function register_capabilities(): void {
		// Ensure WP_Roles is initialized.
		if ( ! function_exists( 'get_role' ) ) {
			return;
		}

		// Assign capabilities to roles.
		$owner_caps   = array( 'gestor_ver', 'gestor_editar' );
		$manager_caps = array( 'gestor_ver', 'gestor_editar' );
		$viewer_caps  = array( 'gestor_ver' );

		// Owner role - full access.
		$owner_role = get_role( 'gestor_owner' );
		if ( $owner_role ) {
			foreach ( $owner_caps as $cap ) {
				$owner_role->add_cap( $cap );
			}
		}

		// Manager role - view/edit.
		$manager_role = get_role( 'gestor_manager' );
		if ( $manager_role ) {
			foreach ( $manager_caps as $cap ) {
				$manager_role->add_cap( $cap );
			}
		}

		// Viewer role - read-only.
		$viewer_role = get_role( 'gestor_viewer' );
		if ( $viewer_role ) {
			foreach ( $viewer_caps as $cap ) {
				$viewer_role->add_cap( $cap );
			}
		}

		// Also add to administrator role.
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			foreach ( $owner_caps as $cap ) {
				$admin_role->add_cap( $cap );
			}
		}
	}
}

