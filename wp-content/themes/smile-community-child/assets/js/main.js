/**
 * Smile Community Centre theme behaviour:
 * - accessible mobile navigation (Escape to close, focus return, submenu toggles)
 * - copy-to-clipboard buttons for M-Pesa details ([data-smile-copy])
 */
( function () {
	'use strict';

	var i18n = window.smileTheme || {};

	function initNav() {
		var toggle = document.querySelector( '.smile-nav-toggle' );
		var nav = document.getElementById( 'smile-primary-nav' );
		if ( ! toggle || ! nav ) {
			return;
		}
		var label = toggle.querySelector( '.smile-nav-toggle__label' );
		var backdrop = null;
		var mq = window.matchMedia( '(max-width: 1200px)' );

		function setOpen( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			nav.classList.toggle( 'is-open', open );
			document.body.classList.toggle( 'smile-nav-open', open );
			if ( label ) {
				label.textContent = open ? ( i18n.menuClose || 'Close menu' ) : ( i18n.menuOpen || 'Open menu' );
			}
			if ( open ) {
				backdrop = document.createElement( 'div' );
				backdrop.className = 'smile-nav-backdrop';
				backdrop.addEventListener( 'click', function () { setOpen( false ); toggle.focus(); } );
				document.body.appendChild( backdrop );
				var first = nav.querySelector( 'a, button' );
				if ( first ) { first.focus(); }
			} else if ( backdrop ) {
				backdrop.remove();
				backdrop = null;
			}
		}

		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		// Keep focus inside the open mobile panel.
		nav.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'Tab' || ! nav.classList.contains( 'is-open' ) ) { return; }
			var items = nav.querySelectorAll( 'a, button' );
			if ( ! items.length ) { return; }
			var first = items[ 0 ], last = items[ items.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) { e.preventDefault(); toggle.focus(); }
			else if ( ! e.shiftKey && document.activeElement === last ) { e.preventDefault(); toggle.focus(); }
		} );

		// Submenu toggles.
		nav.querySelectorAll( '.menu-item-has-children' ).forEach( function ( item, i ) {
			var link = item.querySelector( ':scope > a' );
			var sub = item.querySelector( ':scope > .sub-menu' );
			if ( ! link || ! sub ) { return; }
			var id = 'smile-sub-' + i;
			sub.id = id;
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'smile-sub-toggle';
			btn.setAttribute( 'aria-expanded', 'false' );
			btn.setAttribute( 'aria-controls', id );
			btn.innerHTML = '<span class="screen-reader-text">' + link.textContent + '</span><span aria-hidden="true">+</span>';
			link.insertAdjacentElement( 'afterend', btn );
			btn.addEventListener( 'click', function () {
				var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				item.classList.toggle( 'is-open', open );
				btn.querySelector( '[aria-hidden]' ).textContent = open ? '\u2212' : '+';
			} );
		} );

		mq.addEventListener( 'change', function ( e ) {
			if ( ! e.matches ) { setOpen( false ); }
		} );
	}

	function initCopy() {
		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-smile-copy]' );
			if ( ! btn ) { return; }
			var value = btn.getAttribute( 'data-smile-copy' );
			var original = btn.getAttribute( 'data-label' ) || btn.textContent;
			btn.setAttribute( 'data-label', original );
			var done = function ( ok ) {
				btn.textContent = ok ? ( i18n.copied || 'Copied' ) : ( i18n.copyFail || 'Press and hold to copy' );
				btn.classList.toggle( 'is-copied', ok );
				var live = document.getElementById( 'smile-live' );
				if ( live ) { live.textContent = ok ? value + ' ' + ( i18n.copied || 'Copied' ) : ''; }
				setTimeout( function () { btn.textContent = original; btn.classList.remove( 'is-copied' ); }, 2200 );
			};
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( value ).then( function () { done( true ); }, function () { done( false ); } );
			} else {
				done( false );
			}
		} );
	}

	function initScroll() {
		var header = document.getElementById( 'site-header' );
		if ( ! header ) { return; }
		var ticking = false;
		var update = function () { header.classList.toggle( 'is-scrolled', window.scrollY > 40 ); ticking = false; };
		window.addEventListener( 'scroll', function () { if ( ! ticking ) { ticking = true; window.requestAnimationFrame( update ); } }, { passive: true } );
		update();
	}

	function initReveal() {
		var items = document.querySelectorAll( '.smile-reveal' );
		if ( ! items.length ) { return; }
		if ( ! ( 'IntersectionObserver' in window ) || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			items.forEach( function ( el ) { el.classList.add( 'is-in' ); } );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) { e.target.classList.add( 'is-in' ); io.unobserve( e.target ); }
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
		items.forEach( function ( el, i ) {
			if ( ! el.style.getPropertyValue( '--i' ) ) { el.style.setProperty( '--i', String( i % 4 ) ); }
			io.observe( el );
		} );
	}

	function initForms() {
		// Move focus to the status / first error after a server round-trip so screen readers announce it.
		var target = document.querySelector( '.smile-form__status[tabindex="-1"]' );
		if ( target ) { target.focus(); }
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initNav(); initCopy(); initForms(); initScroll(); initReveal(); } );
	} else {
		initNav(); initCopy(); initForms(); initScroll(); initReveal();
	}
}() );
