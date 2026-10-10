<?php
/**
 * Ajustes del sistema.
 *
 * @var array $settings
 */

namespace LobbyInfo\Panel;

use LobbyInfo\Settings\Settings;

defined( 'ABSPATH' ) || exit;

$logo_url = Settings::logo_url();
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Ajustes', 'lobbyinfo' ); ?></h1>
</div>

<form class="lp-form" method="post" action="<?php echo esc_url( Panel::url( 'ajustes' ) ); ?>" enctype="multipart/form-data">
	<?php Panel::nonce_field(); ?>
	<input type="hidden" name="do" value="guardar_ajustes">

	<label class="lp-field"><?php esc_html_e( 'Nombre del sistema', 'lobbyinfo' ); ?>
		<input type="text" name="brand_name" value="<?php echo esc_attr( $settings['brand_name'] ); ?>">
		<small><?php esc_html_e( 'Aparece en la cabecera y en la pantalla de acceso.', 'lobbyinfo' ); ?></small>
	</label>

	<div class="lp-field">
		<span class="lp-label"><?php esc_html_e( 'Logo', 'lobbyinfo' ); ?></span>
		<?php if ( $logo_url ) : ?>
			<div class="lp-filepreview"><img src="<?php echo esc_url( $logo_url ); ?>" alt=""></div>
			<label class="lp-check"><input type="checkbox" name="quitar_logo" value="1"> <?php esc_html_e( 'Quitar el logo', 'lobbyinfo' ); ?></label>
		<?php endif; ?>
		<input type="file" name="logo" accept="image/*">
	</div>

	<label class="lp-field"><?php esc_html_e( 'Cada cuántos segundos revisa la TV si hay cambios', 'lobbyinfo' ); ?>
		<input type="number" name="poll_seconds" min="10" max="3600" value="<?php echo (int) $settings['poll_seconds']; ?>">
	</label>

	<label class="lp-field"><?php esc_html_e( 'Color de fondo cuando no hay contenido', 'lobbyinfo' ); ?>
		<input type="color" name="player_bg" value="<?php echo esc_attr( $settings['player_bg'] ); ?>">
	</label>

	<div class="lp-actions">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Guardar ajustes', 'lobbyinfo' ); ?></button>
	</div>
</form>
