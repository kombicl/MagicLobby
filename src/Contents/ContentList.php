<?php
/**
 * Columnas del listado de contenidos.
 */

namespace MagicLobby\Contents;

defined( 'ABSPATH' ) || exit;

final class ContentList {

	public static function register() {
		add_filter( 'manage_' . Content::POST_TYPE . '_posts_columns', array( self::class, 'columns' ) );
		add_action( 'manage_' . Content::POST_TYPE . '_posts_custom_column', array( self::class, 'column' ), 10, 2 );
	}

	public static function columns( $cols ) {
		return array( 'cb' => $cols['cb'], 'li_thumb' => '', 'title' => $cols['title'] ) + array(
			'li_type'     => __( 'Tipo', 'magiclobby' ),
			'li_duration' => __( 'Duración', 'magiclobby' ),
			'li_validity' => __( 'Vigencia', 'magiclobby' ),
		) + $cols;
	}

	public static function column( $col, $post_id ) {
		$type = Content::type( $post_id );
		switch ( $col ) {
			case 'li_thumb':
				if ( 'image' === $type ) {
					echo wp_get_attachment_image( (int) get_post_meta( $post_id, '_li_media', true ), array( 60, 60 ) );
				}
				break;
			case 'li_type':
				echo esc_html( Content::types()[ $type ] ?? '—' );
				break;
			case 'li_duration':
				$d = get_post_meta( $post_id, '_li_duration', true );
				if ( '' === $d ) {
					echo 'video' === $type ? esc_html__( 'Completo', 'magiclobby' ) : esc_html( Content::DEFAULT_DURATION . ' s' );
				} else {
					echo esc_html( $d . ' s' );
				}
				break;
			case 'li_validity':
				$from = get_post_meta( $post_id, '_li_from', true );
				$to   = get_post_meta( $post_id, '_li_to', true );
				echo ( $from || $to ) ? esc_html( ( $from ?: '…' ) . ' → ' . ( $to ?: '…' ) ) : '—';
				if ( ( $from || $to ) && ! Content::is_visible( $post_id ) && 'publish' === get_post_status( $post_id ) ) {
					echo '<br><small>' . esc_html__( 'Fuera de vigencia', 'magiclobby' ) . '</small>';
				}
				break;
		}
	}
}
