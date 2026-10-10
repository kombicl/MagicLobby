/* Editor de lista: agregar, quitar y ordenar contenidos. */
jQuery( function ( $ ) {
	var $list = $( '#li_items' );
	var tpl = $( '#li_item_tpl' ).html();

	function escape( text ) {
		return $( '<div>' ).text( text ).html();
	}

	$list.sortable( { handle: '.dashicons-menu, .lobbyinfo-item-title', placeholder: 'ui-sortable-placeholder', forcePlaceholderSize: true } );

	$( '#li_item_add_btn' ).on( 'click', function () {
		var $opt = $( '#li_item_add option:selected' );
		if ( ! +$opt.val() ) {
			return;
		}
		$list.append( tpl
			.replace( /__id__/g, $opt.val() )
			.replace( '__title__', function () { return escape( $opt.text() ); } )
			.replace( '__type__', function () { return escape( $opt.data( 'type' ) || '' ); } ) );
		$( '#li_item_add' ).val( '0' );
	} );

	$list.on( 'click', '.lobbyinfo-remove', function () {
		$( this ).closest( 'li' ).remove();
	} );
} );
