<?php
/**
 * Lista de contenidos.
 *
 * @var \WP_Post[] $items
 * @var array      $types
 */

namespace LobbyInfo\Panel;

use LobbyInfo\Contents\Content;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Contenidos', 'lobbyinfo' ); ?></h1>
	<a class="lp-btn lp-btn-primary" href="<?php echo esc_url( Panel::url( 'contenidos/nuevo' ) ); ?>"><?php esc_html_e( '+ Nuevo contenido', 'lobbyinfo' ); ?></a>
</div>

<?php if ( ! $items ) : ?>
	<div class="lp-empty"><p><?php esc_html_e( 'Aún no hay contenidos. Crea el primero para empezar.', 'lobbyinfo' ); ?></p></div>
<?php else : ?>
	<div class="lp-list">
		<?php
		foreach ( $items as $it ) :
			$type  = Content::type( $it->ID );
			$media = (int) get_post_meta( $it->ID, '_li_media', true );
			?>
			<a class="lp-row" href="<?php echo esc_url( Panel::url( 'contenidos/editar/' . $it->ID ) ); ?>">
				<span class="lp-thumb">
					<?php
					if ( 'image' === $type && $media ) {
						echo wp_get_attachment_image( $media, array( 64, 64 ) );
					} else {
						echo '<span class="lp-thumb-icon">' . esc_html( mb_strtoupper( mb_substr( $types[ $type ] ?? '?', 0, 1 ) ) ) . '</span>';
					}
					?>
				</span>
				<span class="lp-row-main">
					<strong><?php echo esc_html( get_the_title( $it ) ?: __( '(sin título)', 'lobbyinfo' ) ); ?></strong>
					<small><?php echo esc_html( $types[ $type ] ?? $type ); ?></small>
				</span>
				<span class="lp-badge <?php echo 'publish' === $it->post_status ? 'is-on' : 'is-off'; ?>">
					<?php echo 'publish' === $it->post_status ? esc_html__( 'Publicado', 'lobbyinfo' ) : esc_html__( 'Oculto', 'lobbyinfo' ); ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
