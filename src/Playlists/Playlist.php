<?php
/**
 * Lista de reproducción: contenidos elegidos en orden, más categorías completas.
 */

namespace MagicLobby\Playlists;

use MagicLobby\Categories\Category;
use MagicLobby\Contents\Content;
use MagicLobby\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class Playlist {

	public const POST_TYPE = 'li_playlist';

	public static function register() {
		add_action( 'init', array( self::class, 'register_post_type' ) );
	}

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'          => array(
				'name'          => __( 'Listas de reproducción', 'magiclobby' ),
				'singular_name' => __( 'Lista de reproducción', 'magiclobby' ),
				'all_items'     => __( 'Listas', 'magiclobby' ),
				'add_new'       => __( 'Añadir lista', 'magiclobby' ),
				'add_new_item'  => __( 'Añadir lista de reproducción', 'magiclobby' ),
				'edit_item'     => __( 'Editar lista de reproducción', 'magiclobby' ),
				'not_found'     => __( 'No hay listas.', 'magiclobby' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => Admin::MENU,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		) );
	}

	public static function chosen_ids( $playlist_id ) {
		return Admin::ids( get_post_meta( $playlist_id, '_li_items', true ) ?: array() );
	}

	public static function category_ids( $playlist_id ) {
		return Admin::ids( get_post_meta( $playlist_id, '_li_categories', true ) ?: array() );
	}

	/**
	 * Contenidos a reproducir: primero los elegidos (en su orden, se pueden repetir
	 * a propósito), luego los de las categorías que no estén ya elegidos.
	 * Solo los visibles hoy.
	 */
	public static function content_ids( $playlist_id ) {
		$chosen = self::chosen_ids( $playlist_id );
		$ids    = array_merge(
			$chosen,
			array_diff( Category::content_ids( self::category_ids( $playlist_id ) ), $chosen )
		);
		return array_values( array_filter( $ids, array( Content::class, 'is_visible' ) ) );
	}
}
