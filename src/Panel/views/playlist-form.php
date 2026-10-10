<?php
/**
 * Alta/edición de una lista de reproducción.
 *
 * @var int        $id
 * @var string     $title
 * @var \WP_Post[] $all_items
 * @var int[]      $chosen
 * @var \WP_Term[] $categories
 * @var int[]      $chosen_cat
 * @var array      $types
 */

namespace LobbyInfo\Panel;

use LobbyInfo\Contents\Content;

defined( 'ABSPATH' ) || exit;

$chosen_ids = array_map( 'intval', $chosen );
// Contenidos elegidos (en orden) y luego el resto, para el selector.
$by_id = array();
foreach ( $all_items as $it ) {
	$by_id[ $it->ID ] = $it;
}
?>
<div class="lp-head">
	<a class="lp-back" href="<?php echo esc_url( Panel::url( 'listas' ) ); ?>">← <?php esc_html_e( 'Listas', 'lobbyinfo' ); ?></a>
	<h1><?php echo $id ? esc_html__( 'Editar lista', 'lobbyinfo' ) : esc_html__( 'Nueva lista', 'lobbyinfo' ); ?></h1>
</div>

<form class="lp-form" method="post" action="<?php echo esc_url( Panel::url( 'listas' ) ); ?>" data-playlist>
	<?php Panel::nonce_field(); ?>
	<input type="hidden" name="do" value="guardar_lista">
	<input type="hidden" name="id" value="<?php echo (int) $id; ?>">
	<input type="hidden" name="orden" value="<?php echo esc_attr( implode( ',', $chosen_ids ) ); ?>" data-order>

	<label class="lp-field"><?php esc_html_e( 'Nombre de la lista', 'lobbyinfo' ); ?>
		<input type="text" name="titulo" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Ej.: Avisos del domingo', 'lobbyinfo' ); ?>" required>
	</label>

	<div class="lp-field">
		<span class="lp-label"><?php esc_html_e( 'Contenidos (arrastra para ordenar)', 'lobbyinfo' ); ?></span>
		<ul class="lp-sortable" data-list>
			<?php
			foreach ( $chosen_ids as $cid ) :
				if ( empty( $by_id[ $cid ] ) ) {
					continue;
				}
				?>
				<li data-id="<?php echo (int) $cid; ?>">
					<span class="lp-grab">⠿</span>
					<span class="lp-li-title"><?php echo esc_html( get_the_title( $cid ) ); ?></span>
					<small><?php echo esc_html( $types[ Content::type( $cid ) ] ?? '' ); ?></small>
					<button type="button" class="lp-li-remove" aria-label="<?php esc_attr_e( 'Quitar', 'lobbyinfo' ); ?>">×</button>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="lp-add">
			<select data-add>
				<option value="0"><?php esc_html_e( '— Agregar contenido —', 'lobbyinfo' ); ?></option>
				<?php foreach ( $all_items as $it ) : ?>
					<option value="<?php echo (int) $it->ID; ?>" data-type="<?php echo esc_attr( $types[ Content::type( $it->ID ) ] ?? '' ); ?>"><?php echo esc_html( get_the_title( $it ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="lp-btn" data-add-btn><?php esc_html_e( 'Agregar', 'lobbyinfo' ); ?></button>
		</div>
	</div>

	<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
		<div class="lp-field">
			<span class="lp-label"><?php esc_html_e( 'Incluir categorías completas', 'lobbyinfo' ); ?></span>
			<div class="lp-checks">
				<?php foreach ( $categories as $cat ) : ?>
					<label><input type="checkbox" name="categorias[]" value="<?php echo (int) $cat->term_id; ?>" <?php checked( in_array( (int) $cat->term_id, array_map( 'intval', $chosen_cat ), true ) ); ?>> <?php echo esc_html( $cat->name ); ?></label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="lp-actions">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Guardar', 'lobbyinfo' ); ?></button>
		<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( Panel::url( 'listas' ) ); ?>"><?php esc_html_e( 'Cancelar', 'lobbyinfo' ); ?></a>
		<?php if ( $id ) : ?>
			<button type="submit" form="lp-del" class="lp-btn lp-btn-danger" data-confirm="<?php esc_attr_e( '¿Eliminar esta lista?', 'lobbyinfo' ); ?>"><?php esc_html_e( 'Eliminar', 'lobbyinfo' ); ?></button>
		<?php endif; ?>
	</div>
</form>

<template data-row-tpl>
	<li data-id="__id__">
		<span class="lp-grab">⠿</span>
		<span class="lp-li-title">__title__</span>
		<small>__type__</small>
		<button type="button" class="lp-li-remove" aria-label="<?php esc_attr_e( 'Quitar', 'lobbyinfo' ); ?>">×</button>
	</li>
</template>

<?php if ( $id ) : ?>
	<form id="lp-del" method="post" action="<?php echo esc_url( Panel::url( 'listas' ) ); ?>" hidden>
		<?php Panel::nonce_field(); ?>
		<input type="hidden" name="do" value="borrar_lista">
		<input type="hidden" name="id" value="<?php echo (int) $id; ?>">
	</form>
<?php endif; ?>
