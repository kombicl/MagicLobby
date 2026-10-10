<?php
/**
 * Categoría: agrupa contenidos (las define cada organización desde el admin).
 */

namespace LobbyInfo\Categories;

use LobbyInfo\Contents\Content;
use LobbyInfo\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class Category {

	public const TAXONOMY = 'li_category';

	public static function register() {
		add_action( 'init', array( self::class, 'register_taxonomy' ) );
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_filter( 'parent_file', array( self::class, 'highlight_menu' ) );
	}

	public static function register_taxonomy() {
		register_taxonomy( self::TAXONOMY, Content::POST_TYPE, array(
			'labels'            => array(
				'name'          => __( 'Categorías', 'lobbyinfo' ),
				'singular_name' => __( 'Categoría', 'lobbyinfo' ),
				'add_new_item'  => __( 'Añadir categoría', 'lobbyinfo' ),
				'edit_item'     => __( 'Editar categoría', 'lobbyinfo' ),
				'not_found'     => __( 'No hay categorías.', 'lobbyinfo' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => false,
		) );
	}

	// Un tipo anidado en otro menú no muestra su taxonomía: se agrega a mano.
	public static function add_menu() {
		add_submenu_page(
			Admin::MENU,
			__( 'Categorías', 'lobbyinfo' ),
			__( 'Categorías', 'lobbyinfo' ),
			'manage_categories',
			'edit-tags.php?taxonomy=' . self::TAXONOMY . '&post_type=' . Content::POST_TYPE
		);
	}

	public static function highlight_menu( $parent_file ) {
		$screen = get_current_screen();
		return ( $screen && self::TAXONOMY === $screen->taxonomy ) ? Admin::MENU : $parent_file;
	}

	/** IDs de los contenidos publicados de estas categorías, en orden. */
	public static function content_ids( array $term_ids ) {
		if ( ! $term_ids ) {
			return array();
		}
		return get_posts( array(
			'post_type'      => Content::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'tax_query'      => array( array( 'taxonomy' => self::TAXONOMY, 'terms' => $term_ids ) ),
		) );
	}
}
