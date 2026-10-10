<?php
/**
 * Lista de pantallas.
 *
 * @var \WP_Post[] $screens
 */

namespace MagicLobby\Panel;

use MagicLobby\Screens\Schedule;
use MagicLobby\Screens\Screen;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Pantallas', 'magiclobby' ); ?></h1>
	<a class="lp-btn lp-btn-primary" href="<?php echo esc_url( Panel::url( 'pantallas/nuevo' ) ); ?>"><?php esc_html_e( '+ Nueva pantalla', 'magiclobby' ); ?></a>
</div>

<?php if ( ! $screens ) : ?>
	<div class="lp-empty"><p><?php esc_html_e( 'Cada pantalla es una TV. Crea una y abre su enlace en el televisor.', 'magiclobby' ); ?></p></div>
<?php else : ?>
	<div class="lp-list">
		<?php foreach ( $screens as $s ) : $list = Schedule::current_playlist( $s->ID ); ?>
			<a class="lp-row" href="<?php echo esc_url( Panel::url( 'pantallas/editar/' . $s->ID ) ); ?>">
				<span class="lp-dot <?php echo Screen::is_online( $s->ID ) ? 'is-on' : 'is-off'; ?>"></span>
				<span class="lp-row-main">
					<strong><?php echo esc_html( get_the_title( $s ) ); ?></strong>
					<small><?php echo $list ? esc_html( get_the_title( $list ) ) : esc_html__( 'Sin lista', 'magiclobby' ); ?></small>
				</span>
				<span class="lp-chev">›</span>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
