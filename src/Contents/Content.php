<?php
/**
 * Contenido: una pieza que se muestra en pantalla (imagen, video, texto, YouTube o página web).
 */

namespace MagicLobby\Contents;

use MagicLobby\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class Content {

	public const POST_TYPE = 'li_item';

	/** Segundos que dura un contenido sin duración (salvo videos, que se ven completos). */
	public const DEFAULT_DURATION = 10;

	public static function register() {
		add_action( 'init', array( self::class, 'register_post_type' ) );
	}

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'          => array(
				'name'          => __( 'Contenidos', 'magiclobby' ),
				'singular_name' => __( 'Contenido', 'magiclobby' ),
				'all_items'     => __( 'Contenidos', 'magiclobby' ),
				'add_new'       => __( 'Añadir contenido', 'magiclobby' ),
				'add_new_item'  => __( 'Añadir contenido', 'magiclobby' ),
				'edit_item'     => __( 'Editar contenido', 'magiclobby' ),
				'not_found'     => __( 'No hay contenidos.', 'magiclobby' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => Admin::MENU,
			'supports'        => array( 'title', 'page-attributes' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		) );
	}

	public static function types() {
		return array(
			'image'   => __( 'Imagen', 'magiclobby' ),
			'video'   => __( 'Video', 'magiclobby' ),
			'text'    => __( 'Texto', 'magiclobby' ),
			'youtube' => __( 'YouTube', 'magiclobby' ),
			'url'     => __( 'Página web', 'magiclobby' ),
		);
	}

	public static function type( $id ) {
		return get_post_meta( $id, '_li_type', true ) ?: 'image';
	}

	/** Publicado y dentro de su vigencia (fechas opcionales "desde"/"hasta"). */
	public static function is_visible( $id, $today = null ) {
		if ( self::POST_TYPE !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
			return false;
		}
		$today = $today ?? wp_date( 'Y-m-d' );
		$from  = get_post_meta( $id, '_li_from', true );
		$to    = get_post_meta( $id, '_li_to', true );
		return ( ! $from || $from <= $today ) && ( ! $to || $to >= $today );
	}

	public static function youtube_id( $url ) {
		return preg_match( '~(?:youtu\.be/|v=|embed/|shorts/|live/)([\w-]{11})~', (string) $url, $m ) ? $m[1] : '';
	}

	/** Datos que el reproductor necesita para mostrar el contenido, o null si está incompleto. */
	public static function payload( $id ) {
		$type = self::type( $id );
		$item = array(
			'id'       => (int) $id,
			'type'     => $type,
			'duration' => (int) get_post_meta( $id, '_li_duration', true ),
			'fit'      => get_post_meta( $id, '_li_fit', true ) ?: 'cover',
		);

		switch ( $type ) {
			case 'image':
			case 'video':
				$item['src'] = (string) wp_get_attachment_url( (int) get_post_meta( $id, '_li_media', true ) );
				break;
			case 'text':
				$item['html'] = wpautop( (string) get_post_meta( $id, '_li_text', true ) );
				$item['bg']   = get_post_meta( $id, '_li_bg', true ) ?: '#000000';
				$item['fg']   = get_post_meta( $id, '_li_fg', true ) ?: '#ffffff';
				break;
			case 'youtube':
				$item['youtube'] = self::youtube_id( get_post_meta( $id, '_li_url', true ) );
				break;
			case 'url':
				$item['src'] = (string) get_post_meta( $id, '_li_url', true );
				break;
		}

		if ( ! $item['duration'] && 'video' !== $type ) {
			$item['duration'] = self::DEFAULT_DURATION;
		}

		return ( ! empty( $item['src'] ) || ! empty( $item['html'] ) || ! empty( $item['youtube'] ) ) ? $item : null;
	}
}
