<?php
/**
 * Plugin Name:       LobbyInfo
 * Plugin URI:        https://github.com/AlFcl/LobbyInfo
 * Description:       Señalética digital para lobbies: pantallas, contenidos por categoría, listas de reproducción y programación horaria, visibles desde una URL del sitio.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            alfcl
 * Author URI:        https://alf.cl
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lobbyinfo
 */

defined( 'ABSPATH' ) || exit;

define( 'LOBBYINFO_VERSION', '0.1.0' );
define( 'LOBBYINFO_FILE', __FILE__ );
define( 'LOBBYINFO_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOBBYINFO_URL', plugin_dir_url( __FILE__ ) );

// PSR-4: LobbyInfo\Screens\Screen => src/Screens/Screen.php
spl_autoload_register( function ( $class ) {
	if ( str_starts_with( $class, 'LobbyInfo\\' ) ) {
		$file = LOBBYINFO_DIR . 'src/' . str_replace( '\\', '/', substr( $class, 10 ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );

LobbyInfo\Plugin::boot();

register_activation_hook( __FILE__, array( LobbyInfo\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
