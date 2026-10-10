<?php
/**
 * Lista de listas de reproducción.
 *
 * @var \WP_Post[] $lists
 */

namespace MagicLobby\Panel;

use MagicLobby\Playlists\Playlist;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Listas', 'magiclobby' ); ?></h1>
	<a class="lp-btn lp-btn-primary" href="<?php echo esc_url( Panel::url( 'listas/nuevo' ) ); ?>"><?php esc_html_e( '+ Nueva lista', 'magiclobby' ); ?></a>
</div>

<?php if ( ! $lists ) : ?>
	<div class="lp-empty"><p><?php esc_html_e( 'Una lista agrupa contenidos y es lo que muestra una pantalla.', 'magiclobby' ); ?></p></div>
<?php else : ?>
	<div class="lp-list">
		<?php foreach ( $lists as $pl ) : $n = count( Playlist::content_ids( $pl->ID ) ); ?>
			<a class="lp-row" href="<?php echo esc_url( Panel::url( 'listas/editar/' . $pl->ID ) ); ?>">
				<span class="lp-row-main">
					<strong><?php echo esc_html( get_the_title( $pl ) ); ?></strong>
					<small><?php echo esc_html( sprintf( _n( '%d contenido', '%d contenidos', $n, 'magiclobby' ), $n ) ); ?></small>
				</span>
				<span class="lp-chev">›</span>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
