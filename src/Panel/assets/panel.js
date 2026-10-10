/* Interacciones del panel: menú móvil, campos según tipo, orden de listas, confirmaciones. */
( function () {
	'use strict';

	// Menú en móvil.
	var burger = document.querySelector( '.lp-burger' );
	var nav = document.querySelector( '.lp-nav' );
	if ( burger && nav ) {
		burger.addEventListener( 'click', function () {
			nav.classList.toggle( 'is-open' );
		} );
	}

	// Mostrar solo los campos del tipo de contenido elegido.
	var form = document.querySelector( '[data-type-switch]' );
	if ( form ) {
		var select = form.querySelector( '[data-type-select]' );
		var apply = function () {
			var type = select.value;
			form.querySelectorAll( '[data-when]' ).forEach( function ( box ) {
				// Los bloques son .lp-field (flex); '' dejaría actuar el display:none del CSS.
				box.style.display = box.getAttribute( 'data-when' ).split( ' ' ).indexOf( type ) !== -1 ? 'flex' : 'none';
			} );
		};
		select.addEventListener( 'change', apply );
		apply();
	}

	// Confirmar antes de eliminar.
	document.querySelectorAll( '[data-confirm]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function ( e ) {
			if ( ! window.confirm( btn.getAttribute( 'data-confirm' ) ) ) {
				e.preventDefault();
			}
		} );
	} );

	// Editor de listas: agregar, quitar y ordenar contenidos.
	var pl = document.querySelector( '[data-playlist]' );
	if ( pl ) {
		var list = pl.querySelector( '[data-list]' );
		var order = pl.querySelector( '[data-order]' );
		var addSel = pl.querySelector( '[data-add]' );
		var addBtn = pl.querySelector( '[data-add-btn]' );
		var tpl = pl.querySelector( '[data-row-tpl]' );

		var sync = function () {
			order.value = Array.prototype.map.call( list.children, function ( li ) {
				return li.getAttribute( 'data-id' );
			} ).join( ',' );
		};

		var attachRemove = function ( li ) {
			li.querySelector( '.lp-li-remove' ).addEventListener( 'click', function () {
				li.remove();
				sync();
			} );
		};
		Array.prototype.forEach.call( list.children, attachRemove );

		addBtn.addEventListener( 'click', function () {
			var opt = addSel.options[ addSel.selectedIndex ];
			var id = opt.value;
			if ( ! +id ) {
				return;
			}
			var html = tpl.innerHTML
				.replace( /__id__/g, id )
				.replace( '__title__', opt.textContent )
				.replace( '__type__', opt.getAttribute( 'data-type' ) || '' );
			var wrap = document.createElement( 'tbody' );
			wrap.innerHTML = html.trim();
			var li = wrap.firstChild;
			list.appendChild( li );
			attachRemove( li );
			addSel.selectedIndex = 0;
			sync();
		} );

		// Arrastrar para ordenar (HTML5 drag & drop).
		var dragged = null;
		list.addEventListener( 'dragstart', function ( e ) {
			dragged = e.target.closest( 'li' );
			if ( dragged ) { dragged.classList.add( 'lp-dragging' ); }
		} );
		list.addEventListener( 'dragend', function () {
			if ( dragged ) { dragged.classList.remove( 'lp-dragging' ); }
			dragged = null;
			sync();
		} );
		list.addEventListener( 'dragover', function ( e ) {
			e.preventDefault();
			var after = null;
			var items = Array.prototype.slice.call( list.querySelectorAll( 'li:not(.lp-dragging)' ) );
			for ( var i = 0; i < items.length; i++ ) {
				var box = items[ i ].getBoundingClientRect();
				if ( e.clientY < box.top + box.height / 2 ) { after = items[ i ]; break; }
			}
			if ( dragged ) { list.insertBefore( dragged, after ); }
		} );
		// Hace arrastrables las filas.
		var makeDraggable = function () {
			Array.prototype.forEach.call( list.children, function ( li ) {
				li.setAttribute( 'draggable', 'true' );
			} );
		};
		makeDraggable();
		new MutationObserver( makeDraggable ).observe( list, { childList: true } );
	}
}() );
