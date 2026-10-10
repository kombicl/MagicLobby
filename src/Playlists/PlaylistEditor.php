<?php
/**
 * Formulario de edición de una lista: contenidos ordenables y categorías completas.
 */

namespace LobbyInfo\Playlists;

use LobbyInfo\Categories\Category;
use LobbyInfo\Contents\Content;
use LobbyInfo\Shared\Admin;

defined( 'ABSPATH' ) || exit;

final class PlaylistEditor {

	public static function register() {
		add_action( 'add_meta_boxes_' . Playlist::POST_TYPE, array( self::class, 'add_box' ) );
		add_action( 'save_post_' . Playlist::POST_TYPE, array( self::class, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue() {
		if ( Admin::is_editing( array( Playlist::POST_TYPE ) ) ) {
			Admin::script( 'Playlists', 'playlist-editor', array( 'jquery', 'jquery-ui-sortable' ) );
		}
	}

	public static function add_box() {
		add_meta_box( 'lobbyinfo_playlist', __( 'Contenidos de la lista', 'lobbyinfo' ), array( self::class, 'render' ), Playlist::POST_TYPE, 'normal', 'high' );
	}

	public static function item_row( $id, $title, $type_label ) {
		?>
		<li>
			<span class="dashicons dashicons-menu" aria-hidden="true"></span>
			<input type="hidden" name="li_items[]" value="<?php echo esc_attr( $id ); ?>">
			<span class="lobbyinfo-item-title"><?php echo esc_html( $title ); ?></span>
			<span class="lobbyinfo-item-type"><?php echo esc_html( $type_label ); ?></span>
			<button type="button" class="button-link-delete lobbyinfo-remove" aria-label="<?php esc_attr_e( 'Quitar', 'lobbyinfo' ); ?>">&times;</button>
		</li>
		<?php
	}

	public static function render( $post ) {
		Admin::nonce_field();
		$types = Content::types();
		$cats  = Playlist::category_ids( $post->ID );
		?>
		<p class="description"><?php esc_html_e( 'Arrastra para cambiar el orden. Los contenidos de las categorías marcadas se agregan al final automáticamente.', 'lobbyinfo' ); ?></p>
		<ul class="lobbyinfo-items" id="li_items">
			<?php
			foreach ( Playlist::chosen_ids( $post->ID ) as $id ) {
				if ( Content::POST_TYPE === get_post_type( $id ) ) {
					self::item_row( $id, get_the_title( $id ), $types[ Content::type( $id ) ] ?? '' );
				}
			}
			?>
		</ul>
		<p>
			<select id="li_item_add">
				<option value="0"><?php esc_html_e( '— Elegir contenido —', 'lobbyinfo' ); ?></option>
				<?php foreach ( get_posts( array( 'post_type' => Content::POST_TYPE, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $item ) : ?>
					<option value="<?php echo esc_attr( $item->ID ); ?>" data-type="<?php echo esc_attr( $types[ Content::type( $item->ID ) ] ?? '' ); ?>"><?php echo esc_html( $item->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button" id="li_item_add_btn"><?php esc_html_e( 'Agregar', 'lobbyinfo' ); ?></button>
		</p>
		<script type="text/template" id="li_item_tpl"><?php self::item_row( '__id__', '__title__', '__type__' ); ?></script>

		<h4><?php esc_html_e( 'Incluir categorías completas', 'lobbyinfo' ); ?></h4>
		<?php
		$terms = get_terms( array( 'taxonomy' => Category::TAXONOMY, 'hide_empty' => false ) );
		if ( ! $terms || is_wp_error( $terms ) ) {
			echo '<p class="description">' . esc_html__( 'Aún no hay categorías. Créalas en LobbyInfo → Categorías.', 'lobbyinfo' ) . '</p>';
			return;
		}
		foreach ( $terms as $term ) {
			printf(
				'<label class="lobbyinfo-check"><input type="checkbox" name="li_categories[]" value="%d"%s> %s <span class="count">(%d)</span></label>',
				(int) $term->term_id,
				checked( in_array( (int) $term->term_id, $cats, true ), true, false ),
				esc_html( $term->name ),
				(int) $term->count
			);
		}
	}

	public static function save( $post_id ) {
		if ( ! Admin::can_save( $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, '_li_items', Admin::ids( $_POST['li_items'] ?? array() ) );
		update_post_meta( $post_id, '_li_categories', Admin::ids( $_POST['li_categories'] ?? array() ) );
	}
}
