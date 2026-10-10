<?php
/**
 * Columnas del listado de pantallas: qué muestran ahora, si están en línea y su enlace.
 */

namespace LobbyInfo\Screens;

defined( 'ABSPATH' ) || exit;

final class ScreenList {

	public static function register() {
		add_filter( 'manage_' . Screen::POST_TYPE . '_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_' . Screen::POST_TYPE . '_posts_custom_column', array( self::class, 'column' ), 10, 2 );
	}

	public static function columns( $cols ) {
		return array_slice( $cols, 0, 2 ) + array(
			'li_playlist' => __( 'Mostrando ahora', 'lobbyinfo' ),
			'li_status'   => __( 'Estado', 'lobbyinfo' ),
			'li_link'     => __( 'Enlace', 'lobbyinfo' ),
		) + $cols;
	}

	public static function column( $col, $post_id ) {
		switch ( $col ) {
			case 'li_playlist':
				$list = Schedule::current_playlist( $post_id );
				echo $list ? esc_html( get_the_title( $list ) ) : '—';
				break;
			case 'li_status':
				echo self::status_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput
				break;
			case 'li_link':
				if ( 'publish' === get_post_status( $post_id ) ) {
					$url = Screen::url( $post_id );
					printf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $url ), esc_html( wp_parse_url( $url, PHP_URL_PATH ) ) );
				}
				break;
		}
	}

	/** HTML ya escapado con el estado de conexión. */
	public static function status_html( $screen_id ) {
		$seen = Screen::last_seen( $screen_id );
		if ( ! $seen ) {
			return '<span class="lobbyinfo-status">' . esc_html__( 'Nunca conectada', 'lobbyinfo' ) . '</span>';
		}
		$online = Screen::is_online( $screen_id );
		return sprintf(
			'<span class="lobbyinfo-status %s">%s</span> <small>%s</small>',
			$online ? 'is-online' : 'is-offline',
			$online ? esc_html__( 'En línea', 'lobbyinfo' ) : esc_html__( 'Desconectada', 'lobbyinfo' ),
			/* translators: %s: tiempo transcurrido, p. ej. "5 minutos" */
			esc_html( sprintf( __( 'hace %s', 'lobbyinfo' ), human_time_diff( $seen ) ) )
		);
	}
}
