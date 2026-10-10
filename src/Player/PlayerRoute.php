<?php
/**
 * Página pública del reproductor: /pantalla/{slug}/
 */

namespace LobbyInfo\Player;

use LobbyInfo\Screens\Screen;

defined( 'ABSPATH' ) || exit;

final class PlayerRoute {

	public const QUERY_VAR = 'lobbyinfo_screen';

	public static function register() {
		add_action( 'init', array( self::class, 'add_rewrite' ) );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'render' ) );
	}

	public static function add_rewrite() {
		add_rewrite_rule( '^pantalla/([^/]+)/?$', 'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public static function render() {
		$slug = get_query_var( self::QUERY_VAR );
		if ( ! $slug ) {
			return;
		}

		$screen = Screen::find_by_slug( $slug );
		if ( ! $screen ) {
			status_header( 404 );
			nocache_headers();
			wp_die( esc_html__( 'Pantalla no encontrada.', 'lobbyinfo' ), '', array( 'response' => 404 ) );
		}

		nocache_headers();
		$config = array(
			'endpoint' => PlayerApi::endpoint( $screen->post_name ),
			'initial'  => PlayerPayload::for_screen( $screen->ID ),
		);
		$assets = LOBBYINFO_URL . 'src/Player/assets/';
		require LOBBYINFO_DIR . 'src/Player/views/player.php';
		exit;
	}
}
