<?php
/**
 * Página del reproductor. No usa el tema: solo pantalla completa.
 *
 * @var WP_Post $screen
 * @var array   $config
 * @var string  $assets
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( get_the_title( $screen ) ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $assets . 'player.css?ver=' . MAGICLOBBY_VERSION ); ?>">
	<?php $bg = $config['initial']['bg'] ?? '#000000'; ?>
	<style>html,body,.magiclobby-slide{background:<?php echo esc_html( $bg ); ?>}</style>
</head>
<body>
	<div id="magiclobby-stage" class="magiclobby-stage"></div>
	<div id="magiclobby-offline" class="magiclobby-offline" hidden><?php esc_html_e( 'Sin conexión — reintentando…', 'magiclobby' ); ?></div>
	<div id="magiclobby-empty" class="magiclobby-empty" hidden>
		<div class="magiclobby-clock" id="magiclobby-clock"></div>
		<p><?php esc_html_e( 'Sin contenido programado', 'magiclobby' ); ?></p>
	</div>
	<button type="button" id="magiclobby-fullscreen" class="magiclobby-fullscreen"><?php esc_html_e( 'Pantalla completa', 'magiclobby' ); ?></button>
	<script>window.MagicLobby = <?php echo wp_json_encode( $config ); ?>;</script>
	<script src="<?php echo esc_url( $assets . 'player.js?ver=' . MAGICLOBBY_VERSION ); ?>"></script>
</body>
</html>
