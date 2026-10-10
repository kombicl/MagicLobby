<?php
/**
 * Ajustes globales del sistema. Se guardan en una sola opción.
 */

namespace MagicLobby\Settings;

defined( 'ABSPATH' ) || exit;

final class Settings {

	public const OPTION = 'magiclobby_settings';

	/** Logo mostrado cuando la organización no ha subido el suyo. */
	public const DEFAULT_LOGO = 'https://media.kombi.cl/img/kombilogo.svg';

	public static function register() {
		// Nada que enganchar: es solo almacenamiento. La edición vive en el panel.
	}

	public static function defaults() {
		return array(
			'brand_name'   => 'MagicLobby',
			'brand_logo'   => 0,          // ID de adjunto, opcional.
			'poll_seconds' => 30,         // Cada cuánto consulta la TV.
			'player_bg'    => '#000000',  // Fondo del reproductor cuando no hay contenido.
		);
	}

	public static function all() {
		return wp_parse_args( get_option( self::OPTION, array() ), self::defaults() );
	}

	public static function get( $key ) {
		$all = self::all();
		return $all[ $key ] ?? null;
	}

	public static function save( array $values ) {
		$current = self::all();
		$clean   = array(
			'brand_name'   => sanitize_text_field( $values['brand_name'] ?? $current['brand_name'] ) ?: 'MagicLobby',
			'brand_logo'   => absint( $values['brand_logo'] ?? $current['brand_logo'] ),
			'poll_seconds' => max( 10, min( 3600, absint( $values['poll_seconds'] ?? $current['poll_seconds'] ) ) ),
			'player_bg'    => sanitize_hex_color( $values['player_bg'] ?? '' ) ?: $current['player_bg'],
		);
		update_option( self::OPTION, $clean );
		return $clean;
	}

	public static function brand_name() {
		return self::get( 'brand_name' );
	}

	public static function logo_url() {
		$id = (int) self::get( 'brand_logo' );
		return $id ? wp_get_attachment_image_url( $id, 'medium' ) : self::DEFAULT_LOGO;
	}

	/** ¿La organización subió su propio logo? (para ofrecer quitarlo). */
	public static function has_custom_logo() {
		return (int) self::get( 'brand_logo' ) > 0;
	}
}
