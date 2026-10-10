<?php
/**
 * Alta/edición de una pantalla.
 *
 * @var int        $id
 * @var string     $title
 * @var string     $url
 * @var int        $playlist
 * @var string     $transition
 * @var array      $transitions
 * @var \WP_Post[] $playlists
 */

namespace LobbyInfo\Panel;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<a class="lp-back" href="<?php echo esc_url( Panel::url( 'pantallas' ) ); ?>">← <?php esc_html_e( 'Pantallas', 'lobbyinfo' ); ?></a>
	<h1><?php echo $id ? esc_html__( 'Editar pantalla', 'lobbyinfo' ) : esc_html__( 'Nueva pantalla', 'lobbyinfo' ); ?></h1>
</div>

<form class="lp-form" method="post" action="<?php echo esc_url( Panel::url( 'pantallas' ) ); ?>">
	<?php Panel::nonce_field(); ?>
	<input type="hidden" name="do" value="guardar_pantalla">
	<input type="hidden" name="id" value="<?php echo (int) $id; ?>">

	<label class="lp-field"><?php esc_html_e( 'Nombre', 'lobbyinfo' ); ?>
		<input type="text" name="titulo" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Ej.: Entrada principal', 'lobbyinfo' ); ?>" required>
	</label>

	<label class="lp-field"><?php esc_html_e( 'Lista que muestra', 'lobbyinfo' ); ?>
		<select name="lista">
			<option value="0"><?php esc_html_e( '— Ninguna —', 'lobbyinfo' ); ?></option>
			<?php foreach ( $playlists as $pl ) : ?>
				<option value="<?php echo (int) $pl->ID; ?>" <?php selected( (int) $playlist, $pl->ID ); ?>><?php echo esc_html( get_the_title( $pl ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>

	<label class="lp-field"><?php esc_html_e( 'Transición entre contenidos', 'lobbyinfo' ); ?>
		<select name="transicion">
			<?php foreach ( $transitions as $k => $label ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $transition, $k ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>

	<?php if ( $id && $url ) : ?>
		<div class="lp-field">
			<span class="lp-label"><?php esc_html_e( 'Enlace para el televisor', 'lobbyinfo' ); ?></span>
			<div class="lp-copy">
				<input type="text" value="<?php echo esc_attr( $url ); ?>" readonly onfocus="this.select()">
				<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir', 'lobbyinfo' ); ?></a>
			</div>
			<small><?php esc_html_e( 'Abre este enlace en el navegador del televisor, en pantalla completa.', 'lobbyinfo' ); ?></small>
		</div>
	<?php endif; ?>

	<div class="lp-actions">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Guardar', 'lobbyinfo' ); ?></button>
		<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( Panel::url( 'pantallas' ) ); ?>"><?php esc_html_e( 'Cancelar', 'lobbyinfo' ); ?></a>
	</div>
	<?php if ( ! $id ) : ?>
		<p class="lp-hint"><?php esc_html_e( 'Guarda la pantalla para obtener su enlace.', 'lobbyinfo' ); ?></p>
	<?php endif; ?>
</form>
