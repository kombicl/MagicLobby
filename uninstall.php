<?php
/**
 * Al desinstalar el plugin se borran pantallas, listas, contenidos y categorías.
 * Los archivos de la biblioteca de medios se conservan.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// 'any' no incluye la papelera ni los borradores automáticos: se listan todos los estados.
$statuses = array_keys( get_post_stati() );
foreach ( array( 'li_screen', 'li_playlist', 'li_item' ) as $post_type ) {
	foreach ( get_posts( array( 'post_type' => $post_type, 'post_status' => $statuses, 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
		wp_delete_post( $id, true );
	}
}

// Al desinstalar no se carga el plugin, así que la taxonomía no existe: se registra para poder borrarla.
register_taxonomy( 'li_category', 'li_item' );
$terms = get_terms( array( 'taxonomy' => 'li_category', 'hide_empty' => false, 'fields' => 'ids' ) );
if ( ! is_wp_error( $terms ) ) {
	foreach ( $terms as $term_id ) {
		wp_delete_term( $term_id, 'li_category' );
	}
}

delete_option( 'magiclobby_settings' );
