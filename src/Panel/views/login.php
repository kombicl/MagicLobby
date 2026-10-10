<?php
/**
 * Pantalla de acceso del panel.
 *
 * @var array  $brand
 * @var string $error
 */

namespace MagicLobby\Panel;

defined( 'ABSPATH' ) || exit;

$assets = MAGICLOBBY_URL . 'src/Panel/assets/';
$error  = $error ?? '';
?><!doctype html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $brand['name'] ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $assets . 'panel.css?ver=' . MAGICLOBBY_VERSION ); ?>">
</head>
<body class="lp lp-login">
	<form class="lp-card" method="post" action="<?php echo esc_url( Panel::url() ); ?>">
		<div class="lp-login-brand">
			<?php if ( $brand['logo'] ) : ?>
				<img src="<?php echo esc_url( $brand['logo'] ); ?>" alt="">
			<?php endif; ?>
			<h1><?php echo esc_html( $brand['name'] ); ?></h1>
		</div>
		<?php if ( $error ) : ?>
			<div class="lp-flash is-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>
		<label><?php esc_html_e( 'Usuario', 'magiclobby' ); ?>
			<input type="text" name="usuario" autocomplete="username" autofocus required>
		</label>
		<label><?php esc_html_e( 'Clave', 'magiclobby' ); ?>
			<input type="password" name="clave" autocomplete="current-password" required>
		</label>
		<label class="lp-check"><input type="checkbox" name="recordar" value="1"> <?php esc_html_e( 'Mantener la sesión', 'magiclobby' ); ?></label>
		<input type="hidden" name="magiclobby_login" value="1">
		<input type="hidden" name="_pn" value="<?php echo esc_attr( wp_create_nonce( 'magiclobby_login' ) ); ?>">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Entrar', 'magiclobby' ); ?></button>
	</form>
</body>
</html>
