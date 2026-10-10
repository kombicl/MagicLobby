<?php
/**
 * Endurecimiento básico: evita que visitantes anónimos enumeren los usuarios
 * del sitio por la API REST de WordPress (comportamiento por defecto del core),
 * y, de forma opcional, recupera la IP real del visitante cuando el sitio está
 * detrás de Cloudflare.
 */

namespace MagicLobby\Security;

use MagicLobby\Settings\Settings;
use WP_Error;

defined( 'ABSPATH' ) || exit;

final class Security {

	/**
	 * Rangos oficiales de Cloudflare (cloudflare.com/ips). Solo se confía en la
	 * cabecera CF-Connecting-IP cuando la conexión viene de una de estas redes;
	 * así nadie puede falsear su IP enviando la cabecera a mano.
	 */
	private const CF_RANGES = array(
		// IPv4
		'173.245.48.0/20',
		'103.21.244.0/22',
		'103.22.200.0/22',
		'103.31.4.0/22',
		'141.101.64.0/18',
		'108.162.192.0/18',
		'190.93.240.0/20',
		'188.114.96.0/20',
		'197.234.240.0/22',
		'198.41.128.0/17',
		'162.158.0.0/15',
		'104.16.0.0/13',
		'104.24.0.0/14',
		'172.64.0.0/13',
		'131.0.72.0/22',
		// IPv6
		'2400:cb00::/32',
		'2606:4700::/32',
		'2803:f800::/32',
		'2405:b500::/32',
		'2405:8100::/32',
		'2a06:98c0::/29',
		'2c0f:f248::/32',
	);

	public static function register() {
		// Lo antes posible: corrige la IP del visitante para el resto de la petición.
		self::trust_cloudflare();
		add_filter( 'rest_endpoints', array( self::class, 'hide_users_endpoint' ) );
		add_filter( 'rest_authentication_errors', array( self::class, 'block_anon_user_queries' ) );
	}

	/**
	 * Si el sitio está detrás de Cloudflare y la opción está activada, reemplaza
	 * REMOTE_ADDR por la IP real del visitante (CF-Connecting-IP). Solo lo hace
	 * cuando la conexión proviene realmente de un rango de Cloudflare.
	 */
	public static function trust_cloudflare() {
		if ( ! Settings::cf_trust() ) {
			return;
		}
		$remote = $_SERVER['REMOTE_ADDR'] ?? '';
		$real   = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
		if ( ! $remote || ! $real || ! self::is_cloudflare( $remote ) ) {
			return;
		}
		if ( filter_var( $real, FILTER_VALIDATE_IP ) ) {
			$_SERVER['REMOTE_ADDR'] = $real;
		}
	}

	/** ¿La IP pertenece a algún rango publicado por Cloudflare? */
	private static function is_cloudflare( $ip ) {
		foreach ( self::CF_RANGES as $cidr ) {
			if ( self::ip_in_cidr( $ip, $cidr ) ) {
				return true;
			}
		}
		return false;
	}

	/** Comprueba si una IP (v4 o v6) está dentro de un bloque CIDR. */
	private static function ip_in_cidr( $ip, $cidr ) {
		list( $subnet, $bits ) = array_pad( explode( '/', $cidr, 2 ), 2, null );
		$ip_bin     = @inet_pton( $ip );
		$subnet_bin = @inet_pton( $subnet );
		if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
			return false; // Familias distintas (v4 vs v6) o IP inválida.
		}
		$bits  = (int) $bits;
		$bytes = intdiv( $bits, 8 );
		$rem   = $bits % 8;
		if ( $bytes && 0 !== substr_compare( $ip_bin, $subnet_bin, 0, $bytes ) ) {
			return false;
		}
		if ( $rem ) {
			$mask = chr( 0xff << ( 8 - $rem ) & 0xff );
			if ( ( $ip_bin[ $bytes ] & $mask ) !== ( $subnet_bin[ $bytes ] & $mask ) ) {
				return false;
			}
		}
		return true;
	}

	/** Oculta las rutas /wp/v2/users a quien no pueda listar usuarios. */
	public static function hide_users_endpoint( $endpoints ) {
		if ( is_user_logged_in() && current_user_can( 'list_users' ) ) {
			return $endpoints;
		}
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
			unset( $endpoints[ $route ] );
		}
		return $endpoints;
	}

	/** Respaldo: si alguna ruta de usuarios se alcanza igual, se rechaza al anónimo. */
	public static function block_anon_user_queries( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}
		$route = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
		if ( str_starts_with( (string) $route, '/wp/v2/users' ) && ! is_user_logged_in() ) {
			return new WP_Error( 'rest_user_cannot_view', __( 'No autorizado.', 'magiclobby' ), array( 'status' => 401 ) );
		}
		return $result;
	}
}
