<?php
/**
 * Punto de entrada: registra cada área del dominio.
 */

namespace MagicLobby;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** Cada área del dominio expone un register() que engancha sus hooks. */
	private const FEATURES = array(
		Screens\Screen::class,
		Screens\ScreenEditor::class,
		Screens\ScreenList::class,
		Playlists\Playlist::class,
		Playlists\PlaylistEditor::class,
		Contents\Content::class,
		Contents\ContentEditor::class,
		Contents\ContentList::class,
		Categories\Category::class,
		Player\PlayerRoute::class,
		Player\PlayerApi::class,
		Panel\Panel::class,
		Settings\Settings::class,
		Security\Security::class,
		Shared\Admin::class,
	);

	public static function boot() {
		foreach ( self::FEATURES as $feature ) {
			$feature::register();
		}
	}

	public static function activate() {
		Screens\Screen::register_post_type();
		Playlists\Playlist::register_post_type();
		Contents\Content::register_post_type();
		Categories\Category::register_taxonomy();
		Player\PlayerRoute::add_rewrite();
		Panel\Panel::add_rewrite();
		flush_rewrite_rules();
	}
}
