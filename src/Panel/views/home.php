<?php
/**
 * Inicio: resumen y estado de cada pantalla.
 *
 * @var array $cards
 * @var int   $contents
 * @var int   $playlists
 */

namespace MagicLobby\Panel;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Inicio', 'magiclobby' ); ?></h1>
</div>

<div class="lp-stats">
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'pantallas' ) ); ?>"><b><?php echo count( $cards ); ?></b><span><?php esc_html_e( 'Pantallas', 'magiclobby' ); ?></span></a>
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>"><b><?php echo (int) $contents; ?></b><span><?php esc_html_e( 'Contenidos', 'magiclobby' ); ?></span></a>
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'listas' ) ); ?>"><b><?php echo (int) $playlists; ?></b><span><?php esc_html_e( 'Listas', 'magiclobby' ); ?></span></a>
</div>

<?php if ( ! $cards ) : ?>
	<div class="lp-empty">
		<p><?php esc_html_e( 'Todavía no hay pantallas.', 'magiclobby' ); ?></p>
		<a class="lp-btn lp-btn-primary" href="<?php echo esc_url( Panel::url( 'pantallas/nuevo' ) ); ?>"><?php esc_html_e( 'Crear la primera pantalla', 'magiclobby' ); ?></a>
	</div>
<?php else : ?>
	<div class="lp-grid">
		<?php foreach ( $cards as $c ) : ?>
			<div class="lp-screen">
				<div class="lp-screen-top">
					<h3><?php echo esc_html( $c['title'] ); ?></h3>
					<span class="lp-dot <?php echo $c['online'] ? 'is-on' : 'is-off'; ?>" title="<?php echo $c['online'] ? esc_attr__( 'En línea', 'magiclobby' ) : esc_attr__( 'Desconectada', 'magiclobby' ); ?>"></span>
				</div>
				<p class="lp-screen-now">
					<?php if ( $c['showing'] ) : ?>
						<?php esc_html_e( 'Mostrando:', 'magiclobby' ); ?> <strong><?php echo esc_html( $c['showing'] ); ?></strong>
					<?php else : ?>
						<em><?php esc_html_e( 'Sin contenido', 'magiclobby' ); ?></em>
					<?php endif; ?>
				</p>
				<p class="lp-screen-seen">
					<?php
					if ( ! $c['seen'] ) {
						esc_html_e( 'Nunca se ha conectado', 'magiclobby' );
					} elseif ( $c['online'] ) {
						esc_html_e( 'Conectada ahora', 'magiclobby' );
					} else {
						/* translators: %s: tiempo, p. ej. "2 horas" */
						echo esc_html( sprintf( __( 'Vista hace %s', 'magiclobby' ), human_time_diff( $c['seen'] ) ) );
					}
					?>
				</p>
				<div class="lp-screen-actions">
					<a class="lp-btn" href="<?php echo esc_url( $c['edit'] ); ?>"><?php esc_html_e( 'Editar', 'magiclobby' ); ?></a>
					<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( $c['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir', 'magiclobby' ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
