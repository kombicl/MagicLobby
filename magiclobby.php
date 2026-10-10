<?php
/**
 * Plugin Name:       MagicLobby
 * Plugin URI:        https://github.com/kombicl/MagicLobby
 * Description:       Señalética digital para pantallas (estilo MagicInfo), como plugin de WordPress: pantallas, contenidos por categoría, listas de reproducción, programación horaria y un panel de gestión propio. Primera herramienta open source de Kombi.
 * Version:           0.2.0-beta.2
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Kombi
 * Author URI:        https://kombi.cl
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       magiclobby
 */

defined( 'ABSPATH' ) || exit;

define( 'MAGICLOBBY_VERSION', '0.2.0-beta.2' );
define( 'MAGICLOBBY_FILE', __FILE__ );
define( 'MAGICLOBBY_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAGICLOBBY_URL', plugin_dir_url( __FILE__ ) );

// PSR-4: MagicLobby\Screens\Screen => src/Screens/Screen.php
spl_autoload_register( function ( $class ) {
	if ( str_starts_with( $class, 'MagicLobby\\' ) ) {
		$file = MAGICLOBBY_DIR . 'src/' . str_replace( '\\', '/', substr( $class, 11 ) ) . '.php';
		if ( is_file( $file ) ) {
			require $file;
		}
	}
} );

MagicLobby\Plugin::boot();

register_activation_hook( __FILE__, array( MagicLobby\Plugin::class, 'activate' ) );
// Al desactivar, la regla /pantalla/ sigue registrada en esta petición: se borra la caché
// de reglas para que WordPress la regenere sin el plugin en la próxima carga.
register_deactivation_hook( __FILE__, function () {
	delete_option( 'rewrite_rules' );
} );
