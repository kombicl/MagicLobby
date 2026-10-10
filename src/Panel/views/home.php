<?php
/**
 * Inicio: resumen y estado de cada pantalla.
 *
 * @var array $cards
 * @var int   $contents
 * @var int   $playlists
 */

namespace LobbyInfo\Panel;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Inicio', 'lobbyinfo' ); ?></h1>
</div>

<div class="lp-stats">
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'pantallas' ) ); ?>"><b><?php echo count( $cards ); ?></b><span><?php esc_html_e( 'Pantallas', 'lobbyinfo' ); ?></span></a>
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>"><b><?php echo (int) $contents; ?></b><span><?php esc_html_e( 'Contenidos', 'lobbyinfo' ); ?></span></a>
	<a class="lp-stat" href="<?php echo esc_url( Panel::url( 'listas' ) ); ?>"><b><?php echo (int) $playlists; ?></b><span><?php esc_html_e( 'Listas', 'lobbyinfo' ); ?></span></a>
</div>

<?php if ( ! $cards ) : ?>
	<div class="lp-empty">
		<p><?php esc_html_e( 'Todavía no hay pantallas.', 'lobbyinfo' ); ?></p>
		<a class="lp-btn lp-btn-primary" href="<?php echo esc_url( Panel::url( 'pantallas/nuevo' ) ); ?>"><?php esc_html_e( 'Crear la primera pantalla', 'lobbyinfo' ); ?></a>
	</div>
<?php else : ?>
	<div class="lp-grid">
		<?php foreach ( $cards as $c ) : ?>
			<div class="lp-screen">
				<div class="lp-screen-top">
					<h3><?php echo esc_html( $c['title'] ); ?></h3>
					<span class="lp-dot <?php echo $c['online'] ? 'is-on' : 'is-off'; ?>" title="<?php echo $c['online'] ? esc_attr__( 'En línea', 'lobbyinfo' ) : esc_attr__( 'Desconectada', 'lobbyinfo' ); ?>"></span>
				</div>
				<p class="lp-screen-now">
					<?php if ( $c['showing'] ) : ?>
						<?php esc_html_e( 'Mostrando:', 'lobbyinfo' ); ?> <strong><?php echo esc_html( $c['showing'] ); ?></strong>
					<?php else : ?>
						<em><?php esc_html_e( 'Sin contenido', 'lobbyinfo' ); ?></em>
					<?php endif; ?>
				</p>
				<p class="lp-screen-seen">
					<?php
					if ( ! $c['seen'] ) {
						esc_html_e( 'Nunca se ha conectado', 'lobbyinfo' );
					} elseif ( $c['online'] ) {
						esc_html_e( 'Conectada ahora', 'lobbyinfo' );
					} else {
						/* translators: %s: tiempo, p. ej. "2 horas" */
						echo esc_html( sprintf( __( 'Vista hace %s', 'lobbyinfo' ), human_time_diff( $c['seen'] ) ) );
					}
					?>
				</p>
				<div class="lp-screen-actions">
					<a class="lp-btn" href="<?php echo esc_url( $c['edit'] ); ?>"><?php esc_html_e( 'Editar', 'lobbyinfo' ); ?></a>
					<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( $c['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir', 'lobbyinfo' ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
