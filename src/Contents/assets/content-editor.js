/* Editor de contenido: muestra los campos según el tipo y elige archivos de la biblioteca. */
jQuery( function ( $ ) {
	var $type = $( '#li_type' );
	var frame;

	function toggleFields() {
		var type = $type.val();
		$( '[data-li-types]' ).each( function () {
			$( this ).toggle( $( this ).data( 'li-types' ).split( ' ' ).indexOf( type ) !== -1 );
		} );
	}
	$type.on( 'change', toggleFields );
	toggleFields();

	$( '#li_media_pick' ).on( 'click', function ( e ) {
		e.preventDefault();
		var type = $type.val() === 'video' ? 'video' : 'image';
		frame = wp.media( { title: 'Elegir archivo', library: { type: type }, multiple: false } );
		frame.on( 'select', function () {
			var file = frame.state().get( 'selection' ).first().toJSON();
			var url = file.sizes && file.sizes.medium ? file.sizes.medium.url : file.url;
			$( '#li_media' ).val( file.id );
			$( '#li_media_preview' ).html(
				file.type === 'video'
					? $( '<video muted controls>' ).attr( 'src', file.url )
					: $( '<img>' ).attr( 'src', url )
			);
			if ( ! $( '#title' ).val() ) {
				$( '#title' ).val( file.title ).trigger( 'input' );
				$( '#title-prompt-text' ).addClass( 'screen-reader-text' );
			}
		} );
		frame.open();
	} );

	$( '#li_media_clear' ).on( 'click', function ( e ) {
		e.preventDefault();
		$( '#li_media' ).val( '' );
		$( '#li_media_preview' ).empty();
	} );
} );
