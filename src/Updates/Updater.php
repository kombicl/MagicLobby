<?php
/**
 * Actualizaciones del plugin desde GitHub Releases.
 *
 * WordPress no conoce este plugin (no está en wordpress.org), así que aquí le
 * enseñamos a buscar versiones nuevas en los "Releases" del repositorio. Cuando
 * se publica un release con una etiqueta de versión mayor a la instalada, el
 * usuario ve el aviso de actualización en Escritorio → Plugins, igual que con
 * cualquier otro plugin, y puede instalarla con un clic.
 *
 * Flujo de publicación: se hace un release en GitHub (p. ej. v0.2.0) con la
 * misma versión que la cabecera del plugin. Nada se actualiza solo sin que
 * exista ese release.
 */

namespace MagicLobby\Updates;

defined( 'ABSPATH' ) || exit;

final class Updater {

	private const SLUG  = 'magiclobby';
	private const REPO  = 'kombicl/MagicLobby';
	private const CACHE = 'magiclobby_update_release';
	private const TTL   = 6 * HOUR_IN_SECONDS;

	/** Ruta relativa del plugin: magiclobby/magiclobby.php */
	private static function basename() {
		return plugin_basename( MAGICLOBBY_FILE );
	}

	public static function register() {
		add_filter( 'pre_set_site_transient_update_plugins', array( self::class, 'inject_update' ) );
		add_filter( 'plugins_api', array( self::class, 'plugin_details' ), 10, 3 );
		// La descarga de GitHub trae una carpeta con sufijo; la renombramos al slug.
		add_filter( 'upgrader_source_selection', array( self::class, 'fix_source_dir' ), 10, 4 );
		// Botón "Ver detalles / versión" en la fila del plugin.
		add_filter( 'plugin_row_meta', array( self::class, 'row_meta' ), 10, 2 );
	}

	/** Añade nuestra actualización al listado que revisa WordPress. */
	public static function inject_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$release = self::latest_release();
		if ( ! $release ) {
			return $transient;
		}
		$remote  = ltrim( $release['tag'], 'vV' );
		$current = MAGICLOBBY_VERSION;
		$base    = self::basename();

		if ( version_compare( $remote, $current, '>' ) ) {
			$transient->response[ $base ] = (object) array(
				'slug'        => self::SLUG,
				'plugin'      => $base,
				'new_version' => $remote,
				'url'         => 'https://github.com/' . self::REPO,
				'package'     => $release['zip'],
				'tested'      => get_bloginfo( 'version' ),
			);
		} else {
			// Sin novedades: lo marcamos como "al día" para que no reaparezca.
			$transient->no_update[ $base ] = (object) array(
				'slug'        => self::SLUG,
				'plugin'      => $base,
				'new_version' => $current,
				'url'         => 'https://github.com/' . self::REPO,
				'package'     => '',
			);
		}
		return $transient;
	}

	/** Ficha de información cuando el usuario pulsa "Ver detalles". */
	public static function plugin_details( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || self::SLUG !== $args->slug ) {
			return $result;
		}
		$release = self::latest_release();
		$version = $release ? ltrim( $release['tag'], 'vV' ) : MAGICLOBBY_VERSION;

		return (object) array(
			'name'          => 'MagicLobby',
			'slug'          => self::SLUG,
			'version'       => $version,
			'author'        => '<a href="https://kombi.cl">Kombi</a>',
			'homepage'      => 'https://github.com/' . self::REPO,
			'download_link' => $release['zip'] ?? '',
			'sections'      => array(
				'description' => esc_html__( 'Señalética digital para pantallas, como plugin de WordPress. Primera herramienta open source de Kombi.', 'magiclobby' ),
				'changelog'   => $release ? wp_kses_post( wpautop( $release['notes'] ) ) : '',
			),
		);
	}

	/**
	 * El zip de GitHub se descomprime como "MagicLobby-0.2.0"; WordPress espera
	 * una carpeta llamada "magiclobby". La renombramos antes de instalar.
	 */
	public static function fix_source_dir( $source, $remote_source, $upgrader, $extra = array() ) {
		if ( empty( $extra['plugin'] ) || self::basename() !== $extra['plugin'] ) {
			return $source;
		}
		global $wp_filesystem;
		$desired = trailingslashit( $remote_source ) . self::SLUG;
		if ( $source === trailingslashit( $remote_source ) . self::SLUG . '/' ) {
			return $source;
		}
		if ( $wp_filesystem && $wp_filesystem->move( untrailingslashit( $source ), $desired ) ) {
			return trailingslashit( $desired );
		}
		return $source;
	}

	/** Enlace al repositorio en la fila del plugin. */
	public static function row_meta( $links, $file ) {
		if ( self::basename() === $file ) {
			$links[] = '<a href="https://github.com/' . self::REPO . '" target="_blank" rel="noopener">GitHub</a>';
			$links[] = '<a href="https://github.com/' . self::REPO . '/issues" target="_blank" rel="noopener">' . esc_html__( 'Reportar problema', 'magiclobby' ) . '</a>';
		}
		return $links;
	}

	/** Último release publicado (cacheado). Devuelve tag, zip y notas. */
	private static function latest_release() {
		$cached = get_transient( self::CACHE );
		if ( is_array( $cached ) ) {
			return $cached ?: null; // array vacío = "ya consulté y no hay release".
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::REPO . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'MagicLobby-Updater',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( self::CACHE, array(), self::TTL );
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['tag_name'] ) ) {
			set_transient( self::CACHE, array(), self::TTL );
			return null;
		}

		$release = array(
			'tag'   => (string) $data['tag_name'],
			'zip'   => (string) ( $data['zipball_url'] ?? '' ),
			'notes' => (string) ( $data['body'] ?? '' ),
		);
		set_transient( self::CACHE, $release, self::TTL );
		return $release;
	}
}
