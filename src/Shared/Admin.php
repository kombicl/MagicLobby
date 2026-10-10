<?php
/**
 * Utilidades comunes del administrador: menú, estilos, guardado seguro y saneamiento.
 */

namespace LobbyInfo\Shared;

defined( 'ABSPATH' ) || exit;

final class Admin {

	/** Menú principal "LobbyInfo" (lo crea el tipo Pantalla). */
	public const MENU = 'edit.php?post_type=li_screen';

	public const POST_TYPES = array( 'li_screen', 'li_playlist', 'li_item' );

	public static function register() {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
		add_action( 'admin_notices', array( self::class, 'timezone_notice' ) );
	}

	/**
	 * Con un desfase fijo (p. ej. UTC-3) la programación se corre una hora con el
	 * cambio de horario; hace falta una ciudad, como America/Santiago.
	 */
	public static function timezone_notice() {
		if ( get_option( 'timezone_string' ) || ! self::is_editing( self::POST_TYPES ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'LobbyInfo: la zona horaria del sitio es un desfase fijo, así que la programación se correrá una hora con el cambio de horario. Elige una ciudad (por ejemplo, Santiago).', 'lobbyinfo' ),
			esc_url( admin_url( 'options-general.php' ) ),
			esc_html__( 'Ir a Ajustes', 'lobbyinfo' )
		);
	}

	public static function enqueue_styles() {
		if ( self::is_editing( self::POST_TYPES ) ) {
			wp_enqueue_style( 'lobbyinfo-admin', LOBBYINFO_URL . 'src/Shared/assets/admin.css', array(), LOBBYINFO_VERSION );
		}
	}

	/** ¿Estamos en una pantalla del admin de alguno de estos tipos? */
	public static function is_editing( array $post_types ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && in_array( $screen->post_type, $post_types, true );
	}

	/** Script propio de un área, p. ej. script( 'Screens', 'screen-editor' ). */
	public static function script( $feature, $name, array $deps = array( 'jquery' ) ) {
		wp_enqueue_script( 'lobbyinfo-' . $name, LOBBYINFO_URL . "src/{$feature}/assets/{$name}.js", $deps, LOBBYINFO_VERSION, true );
	}

	public static function nonce_field() {
		wp_nonce_field( 'lobbyinfo_save', 'lobbyinfo_nonce' );
	}

	public static function can_save( $post_id ) {
		return isset( $_POST['lobbyinfo_nonce'] )
			&& wp_verify_nonce( sanitize_key( $_POST['lobbyinfo_nonce'] ), 'lobbyinfo_save' )
			&& ! wp_is_post_autosave( $post_id )
			&& ! wp_is_post_revision( $post_id )
			&& current_user_can( 'edit_post', $post_id );
	}

	/** <option> con las entradas publicadas de un tipo. */
	public static function post_options( $post_type, $selected, $empty_label = '' ) {
		$posts = get_posts( array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		$html = $empty_label ? '<option value="0">' . esc_html( $empty_label ) . '</option>' : '';
		foreach ( $posts as $p ) {
			$html .= sprintf( '<option value="%d"%s>%s</option>', $p->ID, selected( (int) $selected, $p->ID, false ), esc_html( $p->post_title ) );
		}
		return $html;
	}

	public static function ids( $value ) {
		return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
	}

	public static function date( $value ) {
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ? $value : '';
	}

	public static function time( $value ) {
		return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', (string) $value ) ? $value : '';
	}
}
