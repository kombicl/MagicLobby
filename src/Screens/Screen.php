<?php
/**
 * Pantalla: una TV o monitor que muestra una lista de reproducción desde su URL.
 */

namespace LobbyInfo\Screens;

defined( 'ABSPATH' ) || exit;

final class Screen {

	public const POST_TYPE = 'li_screen';

	/** Una pantalla se considera en línea si consultó hace menos de esto. */
	public const ONLINE_WINDOW = 3 * MINUTE_IN_SECONDS;

	public static function register() {
		add_action( 'init', array( self::class, 'register_post_type' ) );
	}

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'          => array(
				'name'          => __( 'Pantallas', 'lobbyinfo' ),
				'singular_name' => __( 'Pantalla', 'lobbyinfo' ),
				'menu_name'     => __( 'LobbyInfo', 'lobbyinfo' ),
				'all_items'     => __( 'Pantallas', 'lobbyinfo' ),
				'add_new'       => __( 'Añadir pantalla', 'lobbyinfo' ),
				'add_new_item'  => __( 'Añadir pantalla', 'lobbyinfo' ),
				'edit_item'     => __( 'Editar pantalla', 'lobbyinfo' ),
				'not_found'     => __( 'No hay pantallas.', 'lobbyinfo' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-desktop',
			'menu_position'   => 25,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		) );
	}

	public static function find_by_slug( $slug ) {
		$screen = get_page_by_path( sanitize_title( $slug ), OBJECT, self::POST_TYPE );
		return ( $screen && 'publish' === $screen->post_status ) ? $screen : null;
	}

	public static function url( $screen ) {
		$screen = get_post( $screen );
		return $screen ? home_url( '/pantalla/' . $screen->post_name . '/' ) : '';
	}

	public static function transitions() {
		return array(
			'fade'  => __( 'Fundido', 'lobbyinfo' ),
			'slide' => __( 'Deslizar', 'lobbyinfo' ),
			'none'  => __( 'Sin transición', 'lobbyinfo' ),
		);
	}

	public static function transition( $screen_id ) {
		return get_post_meta( $screen_id, '_li_transition', true ) ?: 'fade';
	}

	/** Registra que la pantalla está conectada (máximo una escritura por minuto). */
	public static function mark_seen( $screen_id ) {
		if ( time() - (int) get_post_meta( $screen_id, '_li_last_seen', true ) >= MINUTE_IN_SECONDS ) {
			update_post_meta( $screen_id, '_li_last_seen', time() );
		}
	}

	public static function last_seen( $screen_id ) {
		return (int) get_post_meta( $screen_id, '_li_last_seen', true );
	}

	public static function is_online( $screen_id ) {
		return time() - self::last_seen( $screen_id ) < self::ONLINE_WINDOW;
	}
}
