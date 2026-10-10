<?php
/**
 * Ajustes del sistema.
 *
 * @var array $settings
 */

namespace MagicLobby\Panel;

use MagicLobby\Settings\Settings;

defined( 'ABSPATH' ) || exit;

$logo_url = Settings::logo_url();
?>
<div class="lp-head">
	<h1><?php esc_html_e( 'Ajustes', 'magiclobby' ); ?></h1>
</div>

<form class="lp-form" method="post" action="<?php echo esc_url( Panel::url( 'ajustes' ) ); ?>" enctype="multipart/form-data">
	<?php Panel::nonce_field(); ?>
	<input type="hidden" name="do" value="guardar_ajustes">

	<label class="lp-field"><?php esc_html_e( 'Nombre del sistema', 'magiclobby' ); ?>
		<input type="text" name="brand_name" value="<?php echo esc_attr( $settings['brand_name'] ); ?>">
		<small><?php esc_html_e( 'Aparece en la cabecera y en la pantalla de acceso.', 'magiclobby' ); ?></small>
	</label>

	<div class="lp-field">
		<span class="lp-label"><?php esc_html_e( 'Logo', 'magiclobby' ); ?></span>
		<?php if ( $logo_url ) : ?>
			<div class="lp-filepreview">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="">
				<?php if ( ! Settings::has_custom_logo() ) : ?>
					<small><?php esc_html_e( 'Logo por defecto. Sube el tuyo para reemplazarlo.', 'magiclobby' ); ?></small>
				<?php endif; ?>
			</div>
			<?php if ( Settings::has_custom_logo() ) : ?>
				<label class="lp-check"><input type="checkbox" name="quitar_logo" value="1"> <?php esc_html_e( 'Quitar mi logo (volver al predeterminado)', 'magiclobby' ); ?></label>
			<?php endif; ?>
		<?php endif; ?>
		<input type="file" name="logo" accept="image/*">
	</div>

	<label class="lp-field"><?php esc_html_e( 'Cada cuántos segundos revisa la TV si hay cambios', 'magiclobby' ); ?>
		<input type="number" name="poll_seconds" min="10" max="3600" value="<?php echo (int) $settings['poll_seconds']; ?>">
	</label>

	<label class="lp-field"><?php esc_html_e( 'Color de fondo cuando no hay contenido', 'magiclobby' ); ?>
		<input type="color" name="player_bg" value="<?php echo esc_attr( $settings['player_bg'] ); ?>">
	</label>

	<div class="lp-field">
		<span class="lp-label"><?php esc_html_e( 'Seguridad', 'magiclobby' ); ?></span>
		<label class="lp-check">
			<input type="checkbox" name="cf_trust" value="1" <?php checked( ! empty( $settings['cf_trust'] ) ); ?>>
			<?php esc_html_e( 'Confiar en Cloudflare', 'magiclobby' ); ?>
		</label>
		<small><?php esc_html_e( 'Actívalo solo si el sitio está detrás de Cloudflare. Recupera la IP real del visitante (CF-Connecting-IP), validando que la conexión venga de los rangos oficiales de Cloudflare.', 'magiclobby' ); ?></small>
	</div>

	<div class="lp-actions">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Guardar ajustes', 'magiclobby' ); ?></button>
	</div>
</form>
