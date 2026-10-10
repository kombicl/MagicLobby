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
	<link rel="stylesheet" href="<?php echo esc_url( $assets . 'player.css?ver=' . LOBBYINFO_VERSION ); ?>">
	<?php $bg = $config['initial']['bg'] ?? '#000000'; ?>
	<style>html,body,.lobbyinfo-slide{background:<?php echo esc_html( $bg ); ?>}</style>
</head>
<body>
	<div id="lobbyinfo-stage" class="lobbyinfo-stage"></div>
	<div id="lobbyinfo-offline" class="lobbyinfo-offline" hidden><?php esc_html_e( 'Sin conexión — reintentando…', 'lobbyinfo' ); ?></div>
	<div id="lobbyinfo-empty" class="lobbyinfo-empty" hidden>
		<div class="lobbyinfo-clock" id="lobbyinfo-clock"></div>
		<p><?php esc_html_e( 'Sin contenido programado', 'lobbyinfo' ); ?></p>
	</div>
	<button type="button" id="lobbyinfo-fullscreen" class="lobbyinfo-fullscreen"><?php esc_html_e( 'Pantalla completa', 'lobbyinfo' ); ?></button>
	<script>window.LobbyInfo = <?php echo wp_json_encode( $config ); ?>;</script>
	<script src="<?php echo esc_url( $assets . 'player.js?ver=' . LOBBYINFO_VERSION ); ?>"></script>
</body>
</html>
