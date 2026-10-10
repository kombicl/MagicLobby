/*
 * Reproductor de LobbyInfo.
 * Muestra los contenidos en bucle y consulta la API cada cierto tiempo; si algo
 * cambió (hash distinto), aplica la nueva lista en la siguiente transición.
 * Si se pierde la conexión, sigue mostrando lo último que recibió.
 */
( function () {
	'use strict';

	var config = window.LobbyInfo;
	var stage = document.getElementById( 'lobbyinfo-stage' );
	var empty = document.getElementById( 'lobbyinfo-empty' );
	var clock = document.getElementById( 'lobbyinfo-clock' );
	var fsButton = document.getElementById( 'lobbyinfo-fullscreen' );

	var TRANSITION_MS = 800;
	var RELOAD_AFTER_MS = 12 * 60 * 60 * 1000; // Recarga preventiva para liberar memoria.

	var data = config.initial;
	var pending = null;
	var index = -1;
	var timer = null;
	var current = null;
	var looping = false; // Un único video en bucle: no hay avance programado.

	function el( tag, attrs ) {
		var node = document.createElement( tag );
		Object.keys( attrs || {} ).forEach( function ( key ) {
			node.setAttribute( key, attrs[ key ] );
		} );
		return node;
	}

	function buildSlide( item ) {
		var slide = el( 'div', { 'class': 'lobbyinfo-slide is-' + item.type } );
		var media;

		switch ( item.type ) {
			case 'image':
				media = el( 'img', { src: item.src, alt: '' } );
				media.style.objectFit = item.fit;
				break;
			case 'video':
				media = el( 'video', { src: item.src, playsinline: '', preload: 'auto' } );
				media.muted = true;
				media.style.objectFit = item.fit;
				break;
			case 'text':
				media = el( 'div', { 'class': 'lobbyinfo-text' } );
				media.innerHTML = item.html;
				slide.style.background = item.bg;
				slide.style.color = item.fg;
				break;
			case 'youtube':
				media = el( 'iframe', {
					src: 'https://www.youtube-nocookie.com/embed/' + item.youtube + '?autoplay=1&mute=1&controls=0&rel=0&playsinline=1&loop=1&playlist=' + item.youtube,
					allow: 'autoplay; encrypted-media',
					frameborder: '0'
				} );
				break;
			case 'url':
				media = el( 'iframe', { src: item.src, frameborder: '0' } );
				break;
		}

		slide.appendChild( media );
		return slide;
	}

	function schedule( item, slide ) {
		clearTimeout( timer );
		timer = null;
		looping = false;
		var video = item.type === 'video' ? slide.querySelector( 'video' ) : null;

		if ( video ) {
			var single = data.items.length === 1 && ! item.duration;
			video.loop = single;
			looping = single;
			video.onended = single ? null : next;
			video.onerror = function () { timer = setTimeout( next, 2000 ); };
			var play = video.play();
			if ( play && play.catch ) {
				play.catch( function () { timer = setTimeout( next, 2000 ); } );
			}
		}
		if ( item.duration ) {
			timer = setTimeout( next, item.duration * 1000 );
		}
	}

	function show( item ) {
		var slide = buildSlide( item );
		var previous = current;

		stage.className = 'lobbyinfo-stage transition-' + ( data.transition || 'fade' );
		stage.appendChild( slide );
		// Forzar el reflow para que la transición se aplique.
		void slide.offsetWidth;
		slide.classList.add( 'is-active' );

		if ( previous ) {
			previous.classList.remove( 'is-active' );
			previous.classList.add( 'is-leaving' );
			setTimeout( function () {
				previous.remove();
			}, data.transition === 'none' ? 0 : TRANSITION_MS );
		}

		current = slide;
		schedule( item, slide );
		preload( ( index + 1 ) % data.items.length );
	}

	function preload( i ) {
		var item = data.items[ i ];
		if ( item && item.type === 'image' ) {
			new Image().src = item.src;
		}
	}

	function next() {
		if ( pending ) {
			data = pending;
			pending = null;
			index = -1;
		}

		var count = data.items.length;
		empty.hidden = count > 0;
		if ( ! count ) {
			clearTimeout( timer );
			if ( current ) {
				current.remove();
				current = null;
			}
			return;
		}

		// Un único contenido que no es video no se vuelve a montar: solo se reprograma.
		if ( count === 1 && current && index === 0 && data.items[ 0 ].type !== 'video' ) {
			schedule( data.items[ 0 ], current );
			return;
		}

		index = ( index + 1 ) % count;
		show( data.items[ index ] );
	}

	function poll() {
		fetch( config.endpoint, { cache: 'no-store' } )
			.then( function ( res ) {
				if ( ! res.ok ) {
					throw new Error( res.status );
				}
				return res.json();
			} )
			.then( function ( fresh ) {
				if ( fresh.hash === data.hash ) {
					return;
				}
				pending = fresh;
				// Si no hay un avance programado (pantalla vacía o video en bucle), se aplica ya.
				if ( ! data.items.length || looping ) {
					next();
				}
			} )
			.catch( function () { /* Sin conexión: se sigue mostrando lo último. */ } );
	}

	function tick() {
		clock.textContent = new Date().toLocaleTimeString( document.documentElement.lang || undefined, { hour: '2-digit', minute: '2-digit' } );
	}

	// Botón de pantalla completa y cursor: visibles solo al mover el mouse.
	var idle;
	function wake() {
		document.body.classList.remove( 'is-idle' );
		clearTimeout( idle );
		idle = setTimeout( function () {
			document.body.classList.add( 'is-idle' );
		}, 3000 );
	}
	document.addEventListener( 'mousemove', wake );
	fsButton.addEventListener( 'click', function () {
		if ( document.fullscreenElement ) {
			document.exitFullscreen();
		} else if ( document.documentElement.requestFullscreen ) {
			document.documentElement.requestFullscreen();
		}
	} );

	tick();
	setInterval( tick, 1000 );
	wake();
	next();
	setInterval( poll, ( data.poll || 30 ) * 1000 );
	setTimeout( function () {
		if ( navigator.onLine ) {
			location.reload();
		}
	}, RELOAD_AFTER_MS );
}() );
