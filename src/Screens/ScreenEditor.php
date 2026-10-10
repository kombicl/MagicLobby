<?php
/**
 * Formulario de edición de una pantalla: lista por defecto, transición, programación y enlace.
 */

namespace MagicLobby\Screens;

use MagicLobby\Playlists\Playlist;
use MagicLobby\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class ScreenEditor {

	public static function register() {
		add_action( 'add_meta_boxes_' . Screen::POST_TYPE, array( self::class, 'add_boxes' ) );
		add_action( 'save_post_' . Screen::POST_TYPE, array( self::class, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue() {
		if ( Admin::is_editing( array( Screen::POST_TYPE ) ) ) {
			Admin::script( 'Screens', 'screen-editor' );
		}
	}

	public static function add_boxes() {
		add_meta_box( 'magiclobby_screen', __( 'Configuración de la pantalla', 'magiclobby' ), array( self::class, 'render' ), Screen::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'magiclobby_screen_link', __( 'Enlace de la pantalla', 'magiclobby' ), array( self::class, 'render_link' ), Screen::POST_TYPE, 'side', 'high' );
	}

	public static function render( $post ) {
		Admin::nonce_field();
		$transition = Screen::transition( $post->ID );
		?>
		<table class="form-table magiclobby-form">
			<tr>
				<th><label for="li_playlist"><?php esc_html_e( 'Lista por defecto', 'magiclobby' ); ?></label></th>
				<td><select name="li_playlist" id="li_playlist"><?php echo Admin::post_options( Playlist::POST_TYPE, Schedule::default_playlist( $post->ID ), __( '— Ninguna —', 'magiclobby' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select></td>
			</tr>
			<tr>
				<th><label for="li_transition"><?php esc_html_e( 'Transición', 'magiclobby' ); ?></label></th>
				<td>
					<select name="li_transition" id="li_transition">
						<?php foreach ( Screen::transitions() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $transition, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>

		<h4><?php esc_html_e( 'Programación', 'magiclobby' ); ?></h4>
		<p class="description"><?php esc_html_e( 'Se usa la primera regla que coincida con el día y la hora actual. Si ninguna coincide, se muestra la lista por defecto. Hora vacía = todo el día.', 'magiclobby' ); ?></p>
		<table class="widefat striped magiclobby-schedule">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Días', 'magiclobby' ); ?></th>
					<th><?php esc_html_e( 'Desde', 'magiclobby' ); ?></th>
					<th><?php esc_html_e( 'Hasta', 'magiclobby' ); ?></th>
					<th><?php esc_html_e( 'Lista', 'magiclobby' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody id="li_schedule">
				<?php
				foreach ( Schedule::rules( $post->ID ) as $i => $rule ) {
					self::schedule_row( $i, $rule );
				}
				?>
			</tbody>
		</table>
		<script type="text/template" id="li_schedule_tpl"><?php self::schedule_row( '__i__', array() ); ?></script>
		<p><button type="button" class="button" id="li_schedule_add"><?php esc_html_e( 'Agregar regla', 'magiclobby' ); ?></button></p>
		<?php
	}

	private static function schedule_row( $i, $rule ) {
		$days = array_map( 'intval', (array) ( $rule['days'] ?? array() ) );
		$name = 'li_schedule[' . $i . ']';
		?>
		<tr>
			<td class="magiclobby-days">
				<?php foreach ( Schedule::days() as $num => $label ) : ?>
					<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[days][]" value="<?php echo esc_attr( $num ); ?>" <?php checked( in_array( $num, $days, true ) ); ?>> <?php echo esc_html( $label ); ?></label>
				<?php endforeach; ?>
			</td>
			<td><input type="time" name="<?php echo esc_attr( $name ); ?>[start]" value="<?php echo esc_attr( $rule['start'] ?? '' ); ?>"></td>
			<td><input type="time" name="<?php echo esc_attr( $name ); ?>[end]" value="<?php echo esc_attr( $rule['end'] ?? '' ); ?>"></td>
			<td><select name="<?php echo esc_attr( $name ); ?>[playlist]"><?php echo Admin::post_options( Playlist::POST_TYPE, $rule['playlist'] ?? 0, __( '— Lista —', 'magiclobby' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></select></td>
			<td><button type="button" class="button-link-delete magiclobby-remove" aria-label="<?php esc_attr_e( 'Quitar', 'magiclobby' ); ?>">&times;</button></td>
		</tr>
		<?php
	}

	public static function render_link( $post ) {
		if ( 'publish' !== $post->post_status ) {
			echo '<p>' . esc_html__( 'Publica la pantalla para obtener su enlace.', 'magiclobby' ) . '</p>';
			return;
		}
		$url = Screen::url( $post );
		?>
		<p><?php esc_html_e( 'Abre este enlace en el navegador de la TV, en pantalla completa:', 'magiclobby' ); ?></p>
		<p><input type="text" class="widefat" readonly value="<?php echo esc_attr( $url ); ?>" onfocus="this.select()"></p>
		<p><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="button"><?php esc_html_e( 'Abrir pantalla', 'magiclobby' ); ?></a></p>
		<p><?php echo ScreenList::status_html( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! Admin::can_save( $post_id ) ) {
			return;
		}
		$in         = wp_unslash( $_POST );
		$transition = sanitize_key( $in['li_transition'] ?? '' );

		update_post_meta( $post_id, '_li_playlist', absint( $in['li_playlist'] ?? 0 ) );
		update_post_meta( $post_id, '_li_transition', array_key_exists( $transition, Screen::transitions() ) ? $transition : 'fade' );
		update_post_meta( $post_id, '_li_schedule', Schedule::sanitize( $in['li_schedule'] ?? array() ) );
	}
}
