<?php
/**
 * Formulario de edición de un contenido.
 */

namespace MagicLobby\Contents;

use MagicLobby\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class ContentEditor {

	public static function register() {
		add_action( 'add_meta_boxes_' . Content::POST_TYPE, array( self::class, 'add_box' ) );
		add_action( 'save_post_' . Content::POST_TYPE, array( self::class, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue() {
		if ( Admin::is_editing( array( Content::POST_TYPE ) ) ) {
			wp_enqueue_media();
			Admin::script( 'Contents', 'content-editor' );
		}
	}

	public static function add_box() {
		add_meta_box( 'magiclobby_content', __( 'Contenido', 'magiclobby' ), array( self::class, 'render' ), Content::POST_TYPE, 'normal', 'high' );
	}

	public static function render( $post ) {
		Admin::nonce_field();
		$type     = Content::type( $post->ID );
		$media    = (int) get_post_meta( $post->ID, '_li_media', true );
		$fit      = get_post_meta( $post->ID, '_li_fit', true ) ?: 'cover';
		$meta     = fn( $key, $default = '' ) => get_post_meta( $post->ID, $key, true ) ?: $default;
		?>
		<table class="form-table magiclobby-form">
			<tr>
				<th><label for="li_type"><?php esc_html_e( 'Tipo', 'magiclobby' ); ?></label></th>
				<td>
					<select name="li_type" id="li_type">
						<?php foreach ( Content::types() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr data-li-types="image video">
				<th><?php esc_html_e( 'Archivo', 'magiclobby' ); ?></th>
				<td>
					<input type="hidden" name="li_media" id="li_media" value="<?php echo esc_attr( $media ?: '' ); ?>">
					<div class="magiclobby-media-preview" id="li_media_preview">
						<?php
						if ( $media ) {
							echo wp_attachment_is( 'video', $media )
								? '<video src="' . esc_url( wp_get_attachment_url( $media ) ) . '" muted controls></video>'
								: wp_get_attachment_image( $media, 'medium' );
						}
						?>
					</div>
					<button type="button" class="button" id="li_media_pick"><?php esc_html_e( 'Elegir de la biblioteca', 'magiclobby' ); ?></button>
					<button type="button" class="button-link-delete" id="li_media_clear"><?php esc_html_e( 'Quitar', 'magiclobby' ); ?></button>
				</td>
			</tr>
			<tr data-li-types="image video">
				<th><label for="li_fit"><?php esc_html_e( 'Ajuste', 'magiclobby' ); ?></label></th>
				<td>
					<select name="li_fit" id="li_fit">
						<option value="cover" <?php selected( $fit, 'cover' ); ?>><?php esc_html_e( 'Llenar la pantalla (recorta)', 'magiclobby' ); ?></option>
						<option value="contain" <?php selected( $fit, 'contain' ); ?>><?php esc_html_e( 'Mostrar completo (con bordes)', 'magiclobby' ); ?></option>
					</select>
				</td>
			</tr>
			<tr data-li-types="text">
				<th><?php esc_html_e( 'Texto', 'magiclobby' ); ?></th>
				<td>
					<?php wp_editor( (string) $meta( '_li_text' ), 'li_text', array( 'textarea_rows' => 8, 'media_buttons' => false ) ); ?>
					<p>
						<label><?php esc_html_e( 'Fondo', 'magiclobby' ); ?> <input type="color" name="li_bg" value="<?php echo esc_attr( $meta( '_li_bg', '#000000' ) ); ?>"></label>
						<label><?php esc_html_e( 'Letra', 'magiclobby' ); ?> <input type="color" name="li_fg" value="<?php echo esc_attr( $meta( '_li_fg', '#ffffff' ) ); ?>"></label>
					</p>
				</td>
			</tr>
			<tr data-li-types="youtube url">
				<th><label for="li_url"><?php esc_html_e( 'Dirección (URL)', 'magiclobby' ); ?></label></th>
				<td>
					<input type="url" class="large-text" name="li_url" id="li_url" value="<?php echo esc_attr( $meta( '_li_url' ) ); ?>" placeholder="https://">
					<p class="description"><?php esc_html_e( 'Algunos sitios web no permiten mostrarse dentro de otra página.', 'magiclobby' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="li_duration"><?php esc_html_e( 'Duración (segundos)', 'magiclobby' ); ?></label></th>
				<td>
					<input type="number" min="0" step="1" class="small-text" name="li_duration" id="li_duration" value="<?php echo esc_attr( $meta( '_li_duration' ) ); ?>">
					<p class="description"><?php echo esc_html( sprintf( __( 'Vacío: %d segundos. En videos, vacío significa reproducir hasta el final.', 'magiclobby' ), Content::DEFAULT_DURATION ) ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Vigencia', 'magiclobby' ); ?></th>
				<td>
					<label><?php esc_html_e( 'Desde', 'magiclobby' ); ?> <input type="date" name="li_from" value="<?php echo esc_attr( $meta( '_li_from' ) ); ?>"></label>
					<label><?php esc_html_e( 'Hasta', 'magiclobby' ); ?> <input type="date" name="li_to" value="<?php echo esc_attr( $meta( '_li_to' ) ); ?>"></label>
					<p class="description"><?php esc_html_e( 'Opcional. Fuera de estas fechas el contenido no se muestra.', 'magiclobby' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! Admin::can_save( $post_id ) ) {
			return;
		}
		$in   = wp_unslash( $_POST );
		$type = sanitize_key( $in['li_type'] ?? '' );

		update_post_meta( $post_id, '_li_type', array_key_exists( $type, Content::types() ) ? $type : 'image' );
		update_post_meta( $post_id, '_li_media', absint( $in['li_media'] ?? 0 ) );
		update_post_meta( $post_id, '_li_fit', 'contain' === ( $in['li_fit'] ?? '' ) ? 'contain' : 'cover' );
		update_post_meta( $post_id, '_li_text', wp_kses_post( $in['li_text'] ?? '' ) );
		update_post_meta( $post_id, '_li_bg', sanitize_hex_color( $in['li_bg'] ?? '' ) ?: '#000000' );
		update_post_meta( $post_id, '_li_fg', sanitize_hex_color( $in['li_fg'] ?? '' ) ?: '#ffffff' );
		update_post_meta( $post_id, '_li_url', esc_url_raw( $in['li_url'] ?? '' ) );
		update_post_meta( $post_id, '_li_duration', '' === ( $in['li_duration'] ?? '' ) ? '' : absint( $in['li_duration'] ) );
		update_post_meta( $post_id, '_li_from', Admin::date( $in['li_from'] ?? '' ) );
		update_post_meta( $post_id, '_li_to', Admin::date( $in['li_to'] ?? '' ) );
	}
}
