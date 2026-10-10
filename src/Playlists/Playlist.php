<?php
/**
 * Lista de reproducción: contenidos elegidos en orden, más categorías completas.
 */

namespace LobbyInfo\Playlists;

use LobbyInfo\Categories\Category;
use LobbyInfo\Contents\Content;
use LobbyInfo\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class Playlist {

	public const POST_TYPE = 'li_playlist';

	public static function register() {
		add_action( 'init', array( self::class, 'register_post_type' ) );
	}

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels'          => array(
				'name'          => __( 'Listas de reproducción', 'lobbyinfo' ),
				'singular_name' => __( 'Lista de reproducción', 'lobbyinfo' ),
				'all_items'     => __( 'Listas', 'lobbyinfo' ),
				'add_new'       => __( 'Añadir lista', 'lobbyinfo' ),
				'add_new_item'  => __( 'Añadir lista de reproducción', 'lobbyinfo' ),
				'edit_item'     => __( 'Editar lista de reproducción', 'lobbyinfo' ),
				'not_found'     => __( 'No hay listas.', 'lobbyinfo' ),
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
