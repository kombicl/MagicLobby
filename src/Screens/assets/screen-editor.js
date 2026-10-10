/* Editor de pantalla: agregar y quitar reglas de programación. */
jQuery( function ( $ ) {
	var $rows = $( '#li_schedule' );
	var tpl = $( '#li_schedule_tpl' ).html();
	var next = $rows.children().length;

	$( '#li_schedule_add' ).on( 'click', function () {
		$rows.append( tpl.replace( /__i__/g, next++ ) );
	} );

	$rows.on( 'click', '.lobbyinfo-remove', function () {
		$( this ).closest( 'tr' ).remove();
	} );
} );
