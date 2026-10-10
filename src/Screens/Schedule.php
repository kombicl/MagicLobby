<?php
/**
 * Programación de una pantalla: qué lista se muestra según el día y la hora.
 */

namespace LobbyInfo\Screens;

use DateTimeImmutable;
use LobbyInfo\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class Schedule {

	/** Días en el orden de la semana chilena; la clave es la de date('w'). */
	public static function days() {
		return array(
			1 => __( 'Lun', 'lobbyinfo' ),
			2 => __( 'Mar', 'lobbyinfo' ),
			3 => __( 'Mié', 'lobbyinfo' ),
			4 => __( 'Jue', 'lobbyinfo' ),
			5 => __( 'Vie', 'lobbyinfo' ),
			6 => __( 'Sáb', 'lobbyinfo' ),
			0 => __( 'Dom', 'lobbyinfo' ),
		);
	}

	public static function rules( $screen_id ) {
		return get_post_meta( $screen_id, '_li_schedule', true ) ?: array();
	}

	public static function default_playlist( $screen_id ) {
		return (int) get_post_meta( $screen_id, '_li_playlist', true );
	}

	/** Limpia las reglas enviadas desde el formulario; descarta las incompletas. */
	public static function sanitize( $rules ) {
		$clean = array();
		foreach ( (array) $rules as $rule ) {
			$days = array_values( array_intersect( array_map( 'intval', (array) ( $rule['days'] ?? array() ) ), array_keys( self::days() ) ) );
			$list = absint( $rule['playlist'] ?? 0 );
			if ( $days && $list ) {
				$clean[] = array(
					'days'     => $days,
					'start'    => Admin::time( $rule['start'] ?? '' ),
					'end'      => Admin::time( $rule['end'] ?? '' ),
					'playlist' => $list,
				);
			}
		}
		return $clean;
	}

	/**
	 * Lista que toca ahora: la primera regla que coincida con el día y la hora,
	 * o la lista por defecto. Una regla de viernes 22:00–02:00 cruza la medianoche:
	 * rige desde el viernes 22:00 hasta el sábado 02:00.
	 */
	public static function current_playlist( $screen_id, ?DateTimeImmutable $now = null ) {
		$now       = $now ?? new DateTimeImmutable( 'now', wp_timezone() );
		$today     = (int) $now->format( 'w' );
		$yesterday = ( $today + 6 ) % 7;
		$time      = $now->format( 'H:i' );

		foreach ( self::rules( $screen_id ) as $rule ) {
			$days  = array_map( 'intval', $rule['days'] );
			$start = $rule['start'] ?: '00:00';
			$end   = $rule['end'] ?: '24:00';
			$match = $start <= $end
				? in_array( $today, $days, true ) && $time >= $start && $time < $end
				: ( in_array( $today, $days, true ) && $time >= $start ) || ( in_array( $yesterday, $days, true ) && $time < $end );
			if ( $match ) {
				return (int) $rule['playlist'];
			}
		}

		return self::default_playlist( $screen_id );
	}
}
