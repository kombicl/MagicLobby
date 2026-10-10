<?php
/**
 * Lo que recibe el reproductor de una pantalla en este momento.
 */

namespace LobbyInfo\Player;

use LobbyInfo\Contents\Content;
use LobbyInfo\Playlists\Playlist;
use LobbyInfo\Screens\Schedule;
use LobbyInfo\Screens\Screen;
use LobbyInfo\Settings\Settings;

defined( 'ABSPATH' ) || exit;

final class PlayerPayload {

	public static function for_screen( $screen_id ) {
		$playlist_id = Schedule::current_playlist( $screen_id );
		$items       = $playlist_id
			? array_values( array_filter( array_map( array( Content::class, 'payload' ), Playlist::content_ids( $playlist_id ) ) ) )
			: array();

		$data = array(
			'screen'     => get_the_title( $screen_id ),
			'playlist'   => $playlist_id ? get_the_title( $playlist_id ) : '',
			'transition' => Screen::transition( $screen_id ),
			'items'      => $items,
		);

		// Si el hash no cambia, el reproductor sigue sin interrumpir lo que muestra.
		$data['hash'] = md5( wp_json_encode( $data ) );
		$data['poll'] = (int) Settings::get( 'poll_seconds' );
		$data['bg']   = Settings::get( 'player_bg' );
		return $data;
	}
}
