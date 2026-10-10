<?php
/**
 * Panel de gestión de marca blanca en /gestor/.
 *
 * Es una zona del sitio, fuera de wp-admin, para que personas sin conocimientos
 * técnicos administren pantallas, contenidos y listas sin notar que por detrás
 * hay WordPress. Usa los usuarios y capacidades de WordPress, pero sin su interfaz.
 */

namespace LobbyInfo\Panel;

use LobbyInfo\Categories\Category;
use LobbyInfo\Contents\Content;
use LobbyInfo\Playlists\Playlist;
use LobbyInfo\Screens\Schedule;
use LobbyInfo\Screens\Screen;
use LobbyInfo\Settings\Settings;

defined( 'ABSPATH' ) || exit;

final class Panel {

	public const BASE = 'gestor';
	public const QV   = 'lobbyinfo_panel';
	public const QV_PATH = 'lobbyinfo_panel_path';

	/** Capacidad mínima para entrar al panel. */
	public const CAP = 'edit_posts';

	private static $notice = '';
	private static $error  = '';

	public static function register() {
		add_action( 'init', array( self::class, 'add_rewrite' ) );
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'dispatch' ) );
	}

	public static function add_rewrite() {
		add_rewrite_rule( '^' . self::BASE . '/?$', 'index.php?' . self::QV . '=1', 'top' );
		add_rewrite_rule( '^' . self::BASE . '/(.+?)/?$', 'index.php?' . self::QV . '=1&' . self::QV_PATH . '=$matches[1]', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = self::QV;
		$vars[] = self::QV_PATH;
		return $vars;
	}

	public static function url( $path = '' ) {
		return home_url( '/' . self::BASE . '/' . ltrim( $path, '/' ) );
	}

	public static function dispatch() {
		if ( ! get_query_var( self::QV ) ) {
			return;
		}
		nocache_headers();

		$path    = trim( (string) get_query_var( self::QV_PATH ), '/' );
		$segments = $path ? explode( '/', $path ) : array();
		$section = $segments[0] ?? 'inicio';

		// Cerrar sesión.
		if ( 'salir' === $section ) {
			wp_logout();
			wp_safe_redirect( self::url() );
			exit;
		}

		// Sin sesión válida: formulario de acceso propio.
		if ( ! is_user_logged_in() || ! current_user_can( self::CAP ) ) {
			self::handle_login();
			self::render( 'login', array( 'brand' => self::brand() ) );
			exit;
		}

		// Procesa el envío de formularios antes de mostrar la página.
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			self::handle_post( $section );
		}

		switch ( $section ) {
			case 'contenidos':
				self::section_contents( $segments );
				break;
			case 'listas':
				self::section_playlists( $segments );
				break;
			case 'pantallas':
				self::section_screens( $segments );
				break;
			case 'ajustes':
				self::render( 'settings', array( 'settings' => Settings::all() ) );
				break;
			default:
				self::section_home();
		}
		exit;
	}

	/* ---------------- Acceso ---------------- */

	private static function handle_login() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['lobbyinfo_login'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST['_pn'] ?? '' ), 'lobbyinfo_login' ) ) {
			self::$error = __( 'La sesión expiró. Inténtalo de nuevo.', 'lobbyinfo' );
			return;
		}
		$user = wp_signon( array(
			'user_login'    => sanitize_user( wp_unslash( $_POST['usuario'] ?? '' ) ),
			'user_password' => (string) ( $_POST['clave'] ?? '' ),
			'remember'      => ! empty( $_POST['recordar'] ),
		) );
		if ( is_wp_error( $user ) ) {
			self::$error = __( 'Usuario o clave incorrectos.', 'lobbyinfo' );
		} elseif ( ! user_can( $user, self::CAP ) ) {
			wp_logout();
			self::$error = __( 'Esta cuenta no tiene acceso al panel.', 'lobbyinfo' );
		} else {
			wp_set_current_user( $user->ID );
			wp_safe_redirect( self::url() );
			exit;
		}
	}

	/* ---------------- Secciones ---------------- */

	private static function section_home() {
		$screens = get_posts( array( 'post_type' => Screen::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		$cards   = array();
		foreach ( $screens as $s ) {
			$list    = Schedule::current_playlist( $s->ID );
			$cards[] = array(
				'title'   => $s->post_title,
				'url'     => Screen::url( $s ),
				'edit'    => self::url( 'pantallas/editar/' . $s->ID ),
				'online'  => Screen::is_online( $s->ID ),
				'seen'    => Screen::last_seen( $s->ID ),
				'showing' => $list ? get_the_title( $list ) : '',
			);
		}
		self::render( 'home', array(
			'cards'     => $cards,
			'contents'  => wp_count_posts( Content::POST_TYPE )->publish,
			'playlists' => wp_count_posts( Playlist::POST_TYPE )->publish,
		) );
	}

	private static function section_contents( $segments ) {
		$action = $segments[1] ?? 'lista';
		if ( 'nuevo' === $action || 'editar' === $action ) {
			$id   = (int) ( $segments[2] ?? 0 );
			$post = $id ? get_post( $id ) : null;
			self::render( 'content-form', array(
				'id'         => $id,
				'title'      => $post ? $post->post_title : '',
				'published'  => $post ? 'publish' === $post->post_status : true,
				'type'       => $id ? Content::type( $id ) : 'text',
				'types'      => Content::types(),
				'text'       => $id ? get_post_meta( $id, '_li_text', true ) : '',
				'bg'         => $id ? ( get_post_meta( $id, '_li_bg', true ) ?: '#1d2b53' ) : '#1d2b53',
				'fg'         => $id ? ( get_post_meta( $id, '_li_fg', true ) ?: '#ffffff' ) : '#ffffff',
				'media'      => (int) ( $id ? get_post_meta( $id, '_li_media', true ) : 0 ),
				'media_url'  => $id ? wp_get_attachment_image_url( (int) get_post_meta( $id, '_li_media', true ), 'medium' ) : '',
				'url'        => $id ? get_post_meta( $id, '_li_url', true ) : '',
				'duration'   => $id ? get_post_meta( $id, '_li_duration', true ) : '',
				'from'       => $id ? get_post_meta( $id, '_li_from', true ) : '',
				'to'         => $id ? get_post_meta( $id, '_li_to', true ) : '',
				'categories' => get_terms( array( 'taxonomy' => Category::TAXONOMY, 'hide_empty' => false ) ),
				'chosen_cat' => $id ? wp_get_object_terms( $id, Category::TAXONOMY, array( 'fields' => 'ids' ) ) : array(),
			) );
			return;
		}
		$items = get_posts( array( 'post_type' => Content::POST_TYPE, 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ) );
		self::render( 'contents', array( 'items' => $items, 'types' => Content::types() ) );
	}

	private static function section_playlists( $segments ) {
		$action = $segments[1] ?? 'lista';
		if ( 'nuevo' === $action || 'editar' === $action ) {
			$id = (int) ( $segments[2] ?? 0 );
			self::render( 'playlist-form', array(
				'id'         => $id,
				'title'      => $id ? get_the_title( $id ) : '',
				'all_items'  => get_posts( array( 'post_type' => Content::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ),
				'chosen'     => $id ? Playlist::chosen_ids( $id ) : array(),
				'categories' => get_terms( array( 'taxonomy' => Category::TAXONOMY, 'hide_empty' => false ) ),
				'chosen_cat' => $id ? Playlist::category_ids( $id ) : array(),
				'types'      => Content::types(),
			) );
			return;
		}
		$lists = get_posts( array( 'post_type' => Playlist::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		self::render( 'playlists', array( 'lists' => $lists ) );
	}

	private static function section_screens( $segments ) {
		$action = $segments[1] ?? 'lista';
		if ( 'nuevo' === $action || 'editar' === $action ) {
			$id = (int) ( $segments[2] ?? 0 );
			self::render( 'screen-form', array(
				'id'          => $id,
				'title'       => $id ? get_the_title( $id ) : '',
				'url'         => $id ? Screen::url( $id ) : '',
				'playlist'    => $id ? Schedule::default_playlist( $id ) : 0,
				'transition'  => $id ? Screen::transition( $id ) : 'fade',
				'transitions' => Screen::transitions(),
				'playlists'   => get_posts( array( 'post_type' => Playlist::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ),
			) );
			return;
		}
		$screens = get_posts( array( 'post_type' => Screen::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		self::render( 'screens', array( 'screens' => $screens ) );
	}

	/* ---------------- Guardado ---------------- */

	private static function handle_post( $section ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['_pn'] ?? '' ), 'lobbyinfo_panel' ) ) {
			self::$error = __( 'La sesión expiró. Vuelve a intentarlo.', 'lobbyinfo' );
			return;
		}
		$do = sanitize_key( $_POST['do'] ?? '' );
		$in = wp_unslash( $_POST );

		switch ( $do ) {
			case 'guardar_contenido':
				self::save_content( $in );
				break;
			case 'borrar_contenido':
				self::delete_post( absint( $in['id'] ?? 0 ), Content::POST_TYPE, 'contenidos', __( 'Contenido eliminado.', 'lobbyinfo' ) );
				break;
			case 'guardar_lista':
				self::save_playlist( $in );
				break;
			case 'borrar_lista':
				self::delete_post( absint( $in['id'] ?? 0 ), Playlist::POST_TYPE, 'listas', __( 'Lista eliminada.', 'lobbyinfo' ) );
				break;
			case 'guardar_pantalla':
				self::save_screen( $in );
				break;
			case 'guardar_ajustes':
				if ( current_user_can( 'manage_options' ) ) {
					$logo = (int) Settings::get( 'brand_logo' );
					if ( ! empty( $in['quitar_logo'] ) ) {
						$logo = 0;
					}
					if ( ! empty( $_FILES['logo']['name'] ) ) {
						$new = self::handle_upload( 'logo', 0 );
						if ( $new ) {
							$logo = $new;
						}
					}
					$in['brand_logo'] = $logo;
					Settings::save( $in );
					self::redirect_with( 'ajustes', __( 'Ajustes guardados.', 'lobbyinfo' ) );
				}
				self::redirect_with( 'ajustes', __( 'No tienes permiso para cambiar los ajustes.', 'lobbyinfo' ), true );
				break;
		}
	}

	private static function save_content( $in ) {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		$id   = absint( $in['id'] ?? 0 );
		$type = sanitize_key( $in['tipo'] ?? 'text' );
		$type = array_key_exists( $type, Content::types() ) ? $type : 'text';
		$data = array(
			'post_type'   => Content::POST_TYPE,
			'post_title'  => sanitize_text_field( $in['titulo'] ?? '' ) ?: __( 'Sin título', 'lobbyinfo' ),
			'post_status' => empty( $in['publicado'] ) ? 'draft' : 'publish',
		);
		$id = $id ? ( $data['ID'] = $id ) && wp_update_post( $data ) : wp_insert_post( $data );
		if ( ! $id || is_wp_error( $id ) ) {
			self::redirect_with( 'contenidos', __( 'No se pudo guardar.', 'lobbyinfo' ), true );
		}

		update_post_meta( $id, '_li_type', $type );
		update_post_meta( $id, '_li_text', wp_kses_post( $in['texto'] ?? '' ) );
		update_post_meta( $id, '_li_bg', sanitize_hex_color( $in['bg'] ?? '' ) ?: '#1d2b53' );
		update_post_meta( $id, '_li_fg', sanitize_hex_color( $in['fg'] ?? '' ) ?: '#ffffff' );
		update_post_meta( $id, '_li_url', esc_url_raw( $in['enlace'] ?? '' ) );
		update_post_meta( $id, '_li_duration', '' === ( $in['duracion'] ?? '' ) ? '' : absint( $in['duracion'] ) );
		update_post_meta( $id, '_li_fit', 'contain' === ( $in['ajuste'] ?? '' ) ? 'contain' : 'cover' );
		update_post_meta( $id, '_li_from', self::date( $in['desde'] ?? '' ) );
		update_post_meta( $id, '_li_to', self::date( $in['hasta'] ?? '' ) );

		if ( in_array( $type, array( 'image', 'video' ), true ) && ! empty( $_FILES['archivo']['name'] ) ) {
			$att = self::handle_upload( 'archivo', $id );
			if ( $att ) {
				update_post_meta( $id, '_li_media', $att );
			}
		}

		$cats = array_map( 'absint', (array) ( $in['categorias'] ?? array() ) );
		wp_set_object_terms( $id, $cats ?: null, Category::TAXONOMY );

		self::redirect_with( 'contenidos', __( 'Contenido guardado.', 'lobbyinfo' ) );
	}

	private static function save_playlist( $in ) {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		$id   = absint( $in['id'] ?? 0 );
		$data = array(
			'post_type'   => Playlist::POST_TYPE,
			'post_title'  => sanitize_text_field( $in['titulo'] ?? '' ) ?: __( 'Lista sin nombre', 'lobbyinfo' ),
			'post_status' => 'publish',
		);
		if ( $id ) {
			$data['ID'] = $id;
			wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			self::redirect_with( 'listas', __( 'No se pudo guardar.', 'lobbyinfo' ), true );
		}
		// El orden viene en un campo oculto separado por comas (lo arma el JS).
		$order = array_filter( array_map( 'absint', explode( ',', (string) ( $in['orden'] ?? '' ) ) ) );
		update_post_meta( $id, '_li_items', array_values( $order ) );
		update_post_meta( $id, '_li_categories', array_map( 'absint', (array) ( $in['categorias'] ?? array() ) ) );
		self::redirect_with( 'listas', __( 'Lista guardada.', 'lobbyinfo' ) );
	}

	private static function save_screen( $in ) {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		$id   = absint( $in['id'] ?? 0 );
		$data = array(
			'post_type'   => Screen::POST_TYPE,
			'post_title'  => sanitize_text_field( $in['titulo'] ?? '' ) ?: __( 'Pantalla', 'lobbyinfo' ),
			'post_status' => 'publish',
		);
		if ( $id ) {
			$data['ID'] = $id;
			wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			self::redirect_with( 'pantallas', __( 'No se pudo guardar.', 'lobbyinfo' ), true );
		}
		$trans = sanitize_key( $in['transicion'] ?? 'fade' );
		update_post_meta( $id, '_li_playlist', absint( $in['lista'] ?? 0 ) );
		update_post_meta( $id, '_li_transition', array_key_exists( $trans, Screen::transitions() ) ? $trans : 'fade' );
		self::redirect_with( 'pantallas', __( 'Pantalla guardada.', 'lobbyinfo' ) );
	}

	private static function delete_post( $id, $type, $back, $msg ) {
		if ( $id && get_post_type( $id ) === $type && current_user_can( 'delete_post', $id ) ) {
			wp_trash_post( $id );
			self::redirect_with( $back, $msg );
		}
		self::redirect_with( $back, __( 'No se pudo eliminar.', 'lobbyinfo' ), true );
	}

	/* ---------------- Utilidades ---------------- */

	private static function handle_upload( $field, $post_id ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$att = media_handle_upload( $field, $post_id );
		return is_wp_error( $att ) ? 0 : (int) $att;
	}

	private static function date( $value ) {
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ? $value : '';
	}

	private static function redirect_with( $section, $msg, $error = false ) {
		set_transient( 'lobbyinfo_flash_' . get_current_user_id(), array( 'msg' => $msg, 'error' => $error ), 30 );
		wp_safe_redirect( self::url( $section ) );
		exit;
	}

	private static function brand() {
		return array( 'name' => Settings::brand_name(), 'logo' => Settings::logo_url() );
	}

	/* ---------------- Render ---------------- */

	private static function render( $view, array $vars = array() ) {
		$flash = get_transient( 'lobbyinfo_flash_' . get_current_user_id() );
		if ( $flash ) {
			delete_transient( 'lobbyinfo_flash_' . get_current_user_id() );
			if ( ! empty( $flash['error'] ) ) {
				self::$error = $flash['msg'];
			} else {
				self::$notice = $flash['msg'];
			}
		}

		$assets  = LOBBYINFO_URL . 'src/Panel/assets/';
		$brand   = self::brand();
		$notice  = self::$notice;
		$error   = self::$error;
		$section = explode( '/', trim( (string) get_query_var( self::QV_PATH ), '/' ) )[0] ?: 'inicio';
		$content_view = LOBBYINFO_DIR . 'src/Panel/views/' . $view . '.php';
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract -- datos controlados de la vista.

		if ( 'login' === $view ) {
			require LOBBYINFO_DIR . 'src/Panel/views/login.php';
			return;
		}
		require LOBBYINFO_DIR . 'src/Panel/views/layout.php';
	}

	/** Campo oculto de seguridad para los formularios del panel. */
	public static function nonce_field( $action = 'lobbyinfo_panel' ) {
		echo '<input type="hidden" name="_pn" value="' . esc_attr( wp_create_nonce( $action ) ) . '">';
	}

	/** Navegación principal del panel. */
	public static function nav() {
		return array(
			'inicio'     => __( 'Inicio', 'lobbyinfo' ),
			'contenidos' => __( 'Contenidos', 'lobbyinfo' ),
			'listas'     => __( 'Listas', 'lobbyinfo' ),
			'pantallas'  => __( 'Pantallas', 'lobbyinfo' ),
			'ajustes'    => __( 'Ajustes', 'lobbyinfo' ),
		);
	}
}
