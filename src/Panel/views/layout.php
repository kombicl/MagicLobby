<?php
/**
 * Marco del panel: cabecera con marca, navegación y el contenido de la sección.
 * Sin ninguna referencia a WordPress visible para el usuario.
 *
 * @var string $assets
 * @var array  $brand
 * @var string $notice
 * @var string $error
 * @var string $section
 * @var string $content_view
 */

namespace LobbyInfo\Panel;

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $brand['name'] ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( $assets . 'panel.css?ver=' . LOBBYINFO_VERSION ); ?>">
</head>
<body class="lp">
	<header class="lp-top">
		<a class="lp-brand" href="<?php echo esc_url( Panel::url() ); ?>">
			<?php if ( $brand['logo'] ) : ?>
				<img src="<?php echo esc_url( $brand['logo'] ); ?>" alt="">
			<?php endif; ?>
			<span><?php echo esc_html( $brand['name'] ); ?></span>
		</a>
		<button type="button" class="lp-burger" aria-label="Menú">☰</button>
		<nav class="lp-nav">
			<?php foreach ( Panel::nav() as $key => $label ) : ?>
				<a href="<?php echo esc_url( Panel::url( $key ) ); ?>" class="<?php echo $section === $key ? 'is-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( Panel::url( 'salir' ) ); ?>" class="lp-logout"><?php esc_html_e( 'Salir', 'lobbyinfo' ); ?></a>
		</nav>
	</header>

	<main class="lp-main">
		<?php if ( $notice ) : ?>
			<div class="lp-flash is-ok"><?php echo esc_html( $notice ); ?></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="lp-flash is-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>
		<?php require $content_view; ?>
	</main>

	<script src="<?php echo esc_url( $assets . 'panel.js?ver=' . LOBBYINFO_VERSION ); ?>"></script>
</body>
</html>
