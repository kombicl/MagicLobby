<?php
/**
 * API pública que consulta el reproductor: GET /wp-json/lobbyinfo/v1/screens/{slug}
 */

namespace LobbyInfo\Player;

use LobbyInfo\Screens\Screen;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

final class PlayerApi {

	public const NAMESPACE = 'lobbyinfo/v1';

	public static function register() {
		add_action( 'rest_api_init', array( self::class, 'routes' ) );
	}

	public static function routes() {
		register_rest_route( self::NAMESPACE, '/screens/(?P<slug>[^/]+)', array(
			'methods'             => 'GET',
			'callback'            => array( self::class, 'screen' ),
			'permission_callback' => '__return_true', // Las pantallas son públicas, igual que su URL.
		) );
	}

	public static function endpoint( $slug ) {
		return rest_url( self::NAMESPACE . '/screens/' . $slug );
	}

	public static function screen( WP_REST_Request $request ) {
		$screen = Screen::find_by_slug( $request['slug'] );
		if ( ! $screen ) {
			return new WP_Error( 'lobbyinfo_not_found', __( 'Pantalla no encontrada.', 'lobbyinfo' ), array( 'status' => 404 ) );
		}
		Screen::mark_seen( $screen->ID );

		$response = rest_ensure_response( PlayerPayload::for_screen( $screen->ID ) );
		$response->header( 'Cache-Control', 'no-store' );
		return $response;
	}
}
