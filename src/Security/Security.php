<?php
/**
 * Endurecimiento básico: evita que visitantes anónimos enumeren los usuarios
 * del sitio por la API REST de WordPress (comportamiento por defecto del core).
 */

namespace LobbyInfo\Security;

use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Security {

	public static function register() {
		add_filter( 'rest_endpoints', array( self::class, 'hide_users_endpoint' ) );
		add_filter( 'rest_authentication_errors', array( self::class, 'block_anon_user_queries' ) );
	}

	/** Oculta las rutas /wp/v2/users a quien no pueda listar usuarios. */
	public static function hide_users_endpoint( $endpoints ) {
		if ( is_user_logged_in() && current_user_can( 'list_users' ) ) {
			return $endpoints;
		}
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
			unset( $endpoints[ $route ] );
		}
		return $endpoints;
	}

	/** Respaldo: si alguna ruta de usuarios se alcanza igual, se rechaza al anónimo. */
	public static function block_anon_user_queries( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}
		$route = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
		if ( str_starts_with( (string) $route, '/wp/v2/users' ) && ! is_user_logged_in() ) {
			return new WP_Error( 'rest_user_cannot_view', __( 'No autorizado.', 'lobbyinfo' ), array( 'status' => 401 ) );
		}
		return $result;
	}
}
