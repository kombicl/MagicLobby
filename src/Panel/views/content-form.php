<?php
/**
 * Alta/edición de un contenido.
 *
 * @var int       $id
 * @var string    $title
 * @var bool      $published
 * @var string    $type
 * @var array     $types
 * @var string    $text, $bg, $fg, $url, $duration, $from, $to
 * @var int       $media
 * @var string    $media_url
 * @var \WP_Term[] $categories
 * @var int[]     $chosen_cat
 */

namespace MagicLobby\Panel;

defined( 'ABSPATH' ) || exit;
?>
<div class="lp-head">
	<a class="lp-back" href="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>">← <?php esc_html_e( 'Contenidos', 'magiclobby' ); ?></a>
	<h1><?php echo $id ? esc_html__( 'Editar contenido', 'magiclobby' ) : esc_html__( 'Nuevo contenido', 'magiclobby' ); ?></h1>
</div>

<form class="lp-form" method="post" action="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>" enctype="multipart/form-data" data-type-switch>
	<?php Panel::nonce_field(); ?>
	<input type="hidden" name="do" value="guardar_contenido">
	<input type="hidden" name="id" value="<?php echo (int) $id; ?>">

	<label class="lp-field"><?php esc_html_e( 'Título', 'magiclobby' ); ?>
		<input type="text" name="titulo" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Ej.: Aviso de la semana', 'magiclobby' ); ?>">
	</label>

	<label class="lp-field"><?php esc_html_e( 'Tipo de contenido', 'magiclobby' ); ?>
		<select name="tipo" data-type-select>
			<?php foreach ( $types as $k => $label ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $type, $k ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>

	<div class="lp-field" data-when="text">
		<label><?php esc_html_e( 'Texto', 'magiclobby' ); ?>
			<textarea name="texto" rows="5" placeholder="<?php esc_attr_e( 'Escribe el mensaje…', 'magiclobby' ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
		</label>
		<div class="lp-colors">
			<label><?php esc_html_e( 'Fondo', 'magiclobby' ); ?> <input type="color" name="bg" value="<?php echo esc_attr( $bg ); ?>"></label>
			<label><?php esc_html_e( 'Letra', 'magiclobby' ); ?> <input type="color" name="fg" value="<?php echo esc_attr( $fg ); ?>"></label>
		</div>
	</div>

	<div class="lp-field" data-when="image video">
		<label><?php esc_html_e( 'Archivo', 'magiclobby' ); ?>
			<input type="file" name="archivo" accept="image/*,video/*">
		</label>
		<?php if ( $media_url ) : ?>
			<div class="lp-filepreview"><img src="<?php echo esc_url( $media_url ); ?>" alt=""><small><?php esc_html_e( 'Deja el campo vacío para conservar el archivo actual.', 'magiclobby' ); ?></small></div>
		<?php endif; ?>
		<label class="lp-sub"><?php esc_html_e( 'Ajuste', 'magiclobby' ); ?>
			<select name="ajuste">
				<option value="cover"><?php esc_html_e( 'Llenar la pantalla', 'magiclobby' ); ?></option>
				<option value="contain"><?php esc_html_e( 'Mostrar completo', 'magiclobby' ); ?></option>
			</select>
		</label>
	</div>

	<div class="lp-field" data-when="youtube url">
		<label><?php esc_html_e( 'Dirección (URL)', 'magiclobby' ); ?>
			<input type="url" name="enlace" value="<?php echo esc_attr( $url ); ?>" placeholder="https://">
		</label>
	</div>

	<div class="lp-row2">
		<label class="lp-field"><?php esc_html_e( 'Duración (segundos)', 'magiclobby' ); ?>
			<input type="number" name="duracion" min="0" value="<?php echo esc_attr( $duration ); ?>" placeholder="10">
		</label>
		<label class="lp-field"><?php esc_html_e( 'Mostrar desde', 'magiclobby' ); ?>
			<input type="date" name="desde" value="<?php echo esc_attr( $from ); ?>">
		</label>
		<label class="lp-field"><?php esc_html_e( 'Mostrar hasta', 'magiclobby' ); ?>
			<input type="date" name="hasta" value="<?php echo esc_attr( $to ); ?>">
		</label>
	</div>

	<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
		<div class="lp-field">
			<span class="lp-label"><?php esc_html_e( 'Categorías', 'magiclobby' ); ?></span>
			<div class="lp-checks">
				<?php foreach ( $categories as $cat ) : ?>
					<label><input type="checkbox" name="categorias[]" value="<?php echo (int) $cat->term_id; ?>" <?php checked( in_array( (int) $cat->term_id, array_map( 'intval', $chosen_cat ), true ) ); ?>> <?php echo esc_html( $cat->name ); ?></label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<label class="lp-check"><input type="checkbox" name="publicado" value="1" <?php checked( $published ); ?>> <?php esc_html_e( 'Publicado (visible en las pantallas)', 'magiclobby' ); ?></label>

	<div class="lp-actions">
		<button type="submit" class="lp-btn lp-btn-primary"><?php esc_html_e( 'Guardar', 'magiclobby' ); ?></button>
		<a class="lp-btn lp-btn-ghost" href="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>"><?php esc_html_e( 'Cancelar', 'magiclobby' ); ?></a>
		<?php if ( $id ) : ?>
			<button type="submit" form="lp-del" class="lp-btn lp-btn-danger" data-confirm="<?php esc_attr_e( '¿Eliminar este contenido?', 'magiclobby' ); ?>"><?php esc_html_e( 'Eliminar', 'magiclobby' ); ?></button>
		<?php endif; ?>
	</div>
</form>

<?php if ( $id ) : ?>
	<form id="lp-del" method="post" action="<?php echo esc_url( Panel::url( 'contenidos' ) ); ?>" hidden>
		<?php Panel::nonce_field(); ?>
		<input type="hidden" name="do" value="borrar_contenido">
		<input type="hidden" name="id" value="<?php echo (int) $id; ?>">
	</form>
<?php endif; ?>
