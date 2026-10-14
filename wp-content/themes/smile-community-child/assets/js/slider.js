/* Smile hero slider: crossfade, autoplay with pause, keyboard, swipe, reduced motion. */
( function () {
	'use strict';
	document.querySelectorAll( '.smile-slider' ).forEach( function ( root ) {
		var slides = root.querySelectorAll( '.smile-slide' );
		if ( slides.length < 2 ) { return; }
		var dots = root.querySelectorAll( '.smile-slider__dot' );
		var pauseBtn = root.querySelector( '.smile-slider__pause' );
		var bar = root.querySelector( '.smile-slider__progress span' );
		var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var DELAY = 7000;
		var current = 0, timer = null, userPaused = reduce, hoverPaused = false;

		function show( n ) {
			n = ( n + slides.length ) % slides.length;
			slides.forEach( function ( s, i ) {
				var on = i === n;
				s.classList.toggle( 'is-active', on );
				if ( on ) { s.removeAttribute( 'aria-hidden' ); s.removeAttribute( 'inert' ); }
				else { s.setAttribute( 'aria-hidden', 'true' ); s.setAttribute( 'inert', '' ); }
				var img = s.querySelector( 'img[loading="lazy"]' );
				if ( on && img ) { img.loading = 'eager'; }
			} );
			dots.forEach( function ( d, i ) {
				d.classList.toggle( 'is-active', i === n );
				if ( i === n ) { d.setAttribute( 'aria-current', 'true' ); } else { d.removeAttribute( 'aria-current' ); }
			} );
			current = n;
			restartBar();
		}
		function restartBar() {
			if ( ! bar ) { return; }
			bar.style.transition = 'none';
			bar.style.width = '0';
			if ( userPaused || hoverPaused ) { return; }
			void bar.offsetWidth;
			bar.style.transition = 'width ' + DELAY + 'ms linear';
			bar.style.width = '100%';
		}
		function play() {
			clearInterval( timer );
			if ( userPaused || hoverPaused ) { restartBar(); return; }
			timer = setInterval( function () { show( current + 1 ); }, DELAY );
			restartBar();
		}
		function setPaused( p ) {
			userPaused = p;
			if ( pauseBtn ) {
				pauseBtn.setAttribute( 'aria-pressed', p ? 'true' : 'false' );
				pauseBtn.setAttribute( 'aria-label', p ? ( ( window.smileTheme || {} ).play || 'Play slideshow' ) : ( ( window.smileTheme || {} ).pause || 'Pause slideshow' ) );
				pauseBtn.classList.toggle( 'is-paused', p );
			}
			root.querySelector( '.smile-slider__track' ).setAttribute( 'aria-live', p ? 'polite' : 'off' );
			play();
		}

		root.querySelectorAll( '[data-dir]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () { show( current + parseInt( b.getAttribute( 'data-dir' ), 10 ) ); play(); } );
		} );
		dots.forEach( function ( d ) {
			d.addEventListener( 'click', function () { show( parseInt( d.getAttribute( 'data-go' ), 10 ) ); play(); } );
		} );
		if ( pauseBtn ) { pauseBtn.addEventListener( 'click', function () { setPaused( ! userPaused ); } ); }

		root.addEventListener( 'mouseenter', function () { hoverPaused = true; play(); } );
		root.addEventListener( 'mouseleave', function () { hoverPaused = false; play(); } );
		root.addEventListener( 'focusin', function () { hoverPaused = true; play(); } );
		root.addEventListener( 'focusout', function ( e ) { if ( ! root.contains( e.relatedTarget ) ) { hoverPaused = false; play(); } } );
		root.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowRight' ) { show( current + 1 ); play(); }
			if ( e.key === 'ArrowLeft' ) { show( current - 1 ); play(); }
		} );

		var x0 = null;
		root.addEventListener( 'touchstart', function ( e ) { x0 = e.touches[ 0 ].clientX; }, { passive: true } );
		root.addEventListener( 'touchend', function ( e ) {
			if ( x0 === null ) { return; }
			var dx = e.changedTouches[ 0 ].clientX - x0;
			if ( Math.abs( dx ) > 45 ) { show( current + ( dx < 0 ? 1 : -1 ) ); play(); }
			x0 = null;
		}, { passive: true } );

		document.addEventListener( 'visibilitychange', function () { if ( document.hidden ) { clearInterval( timer ); } else { play(); } } );

		setPaused( userPaused );
	} );
}() );
