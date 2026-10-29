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
			var srText = document.createElement( 'span' );
			srText.className = 'screen-reader-text';
			srText.textContent = link.textContent;
			var sign = document.createElement( 'span' );
			sign.setAttribute( 'aria-hidden', 'true' );
			sign.className = 'smile-sub-toggle__chev';
			btn.appendChild( srText );
			btn.appendChild( sign );
			link.insertAdjacentElement( 'afterend', btn );
			// Phones and tablets: a short "Overview" link to the parent page sits at the top of
			// each sub-menu, so tapping the parent name can open the sub-menu instead.
			var ov = document.createElement( 'li' );
			ov.className = 'smile-sub-overview';
			var ova = document.createElement( 'a' );
			ova.href = link.href;
			ova.textContent = ( i18n.overview || 'Go to' ) + ' ' + link.textContent.trim();
			ov.appendChild( ova );
			sub.insertBefore( ov, sub.firstChild );

			function setSub( open ) {
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				item.classList.toggle( 'is-open', open );
			}
			function toggleSub() {
				var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
				if ( open ) {
					// One section open at a time keeps the menu short and tidy.
					nav.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( other ) {
						if ( other !== item ) {
							other.classList.remove( 'is-open' );
							var ob = other.querySelector( ':scope > .smile-sub-toggle' );
							if ( ob ) { ob.setAttribute( 'aria-expanded', 'false' ); }
						}
					} );
				}
				setSub( open );
			}
			btn.addEventListener( 'click', toggleSub );
			link.addEventListener( 'click', function ( e ) {
				if ( mq.matches ) {
					e.preventDefault();
					toggleSub();
				}
			} );
			// Auto-open the current section only in the phone/tablet drawer, never on the desktop header.
			if ( mq.matches && ( item.classList.contains( 'current-menu-ancestor' ) || item.classList.contains( 'current-menu-item' ) ) ) {
				setSub( true );
			}
		} );

		function closeAllSubs() {
			nav.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( it ) {
				it.classList.remove( 'is-open' );
				var b = it.querySelector( ':scope > .smile-sub-toggle' );
				if ( b ) { b.setAttribute( 'aria-expanded', 'false' ); }
			} );
		}
		mq.addEventListener( 'change', function ( e ) {
			if ( !e.matches ) { setOpen( false ); closeAllSubs(); }
		} );
		// Desktop: a dropdown opened with its arrow closes on a click elsewhere or on Escape.
		document.addEventListener( 'click', function ( e ) {
			if ( !mq.matches && !nav.contains( e.target ) ) { closeAllSubs(); }
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && !mq.matches ) { closeAllSubs(); if ( document.activeElement && nav.contains( document.activeElement ) ) { document.activeElement.blur(); } }
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
		var root = document.documentElement;
		var topbar = document.querySelector( '.smile-topbar' );
		var measure = function () {
			var tb = ( topbar && topbar.offsetParent !== null ) ? topbar.offsetHeight : 0;
			root.style.setProperty( '--smile-topbar-h', tb + 'px' );
			root.style.setProperty( '--smile-header-h', header.offsetHeight + 'px' );
		};
		var update = function () {
			var was = header.classList.contains( 'is-scrolled' );
			var now = window.scrollY > 40;
			header.classList.toggle( 'is-scrolled', now );
			if ( was !== now ) { window.setTimeout( measure, 300 ); }
			ticking = false;
		};
		measure();
		window.addEventListener( 'resize', measure, { passive: true } );
		window.addEventListener( 'load', measure );
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


	/* Signature smile stroke under italic heading words */
	function initSmiles() {
		var ems = document.querySelectorAll( '.elementor-heading-title em, .smile-slide__title em, .smile-ctaphoto h2 em' );
		if ( !ems.length ) { return; }
		var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		ems.forEach( function ( em ) {
			if ( em.querySelector( '.smile-swash' ) ) { return; }
			em.classList.add( 'smile-em' );
			em.insertAdjacentHTML( 'beforeend', '<svg class="smile-swash" viewBox="0 0 200 34" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M10 2.3 Q100 41.5 189 0.3 Q100 57.7 10 7 Z"/></svg>' );
		} );
		if ( reduce || !( 'IntersectionObserver' in window ) ) {
			ems.forEach( function ( em ) { em.classList.add( 'is-drawn' ); } );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) { e.target.classList.add( 'is-drawn' ); io.unobserve( e.target ); }
			} );
		}, { threshold: 0.6 } );
		ems.forEach( function ( em ) { io.observe( em ); } );
	}

	/* Gift calculator */
	function initCalc() {
		document.querySelectorAll( '.smile-calc' ).forEach( function ( root ) {
			var meal = parseFloat( root.getAttribute( 'data-meal' ) ) || 500;
			var water = parseFloat( root.getAttribute( 'data-water' ) ) || 2500;
			var fees = parseFloat( root.getAttribute( 'data-fees' ) ) || 15000;
			var usd = parseFloat( root.getAttribute( 'data-usd' ) ) || 100;
			var input = root.querySelector( 'input[type="number"]' );
			var chips = root.querySelectorAll( '.smile-calc__chip' );
			var fmt = function ( n ) { return Math.round( n ).toLocaleString( 'en-KE' ); };
			var out = function ( k ) { return root.querySelector( '[data-out="' + k + '"]' ); };
			function render( amount ) {
				amount = Math.max( 0, Math.round( amount || 0 ) );
				out( 'amount' ).textContent = ( i18n.kes || 'KES' ) + ' ' + fmt( amount );
				out( 'usd' ).textContent = ( i18n.aboutUsd || 'about US$' ) + fmt( amount / usd );
				out( 'amount-plain' ).textContent = fmt( amount );
				out( 'amount-copy' ).setAttribute( 'data-smile-copy', String( amount ) );
				var days = amount / meal;
				out( 'meals' ).textContent = days >= 1 ? fmt( days ) : '1';
				var weeks = amount / water;
				out( 'water' ).textContent = weeks >= 1 ? fmt( weeks ) : ( weeks >= 0.5 ? 'half a' : 'part of a' );
				out( 'water-unit' ).textContent = weeks >= 2 ? 'weeks' : 'week';
				out( 'fees' ).hidden = amount < fees;
				chips.forEach( function ( c ) {
					var on = parseInt( c.getAttribute( 'data-amount' ), 10 ) === amount;
					c.classList.toggle( 'is-active', on );
					c.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				} );
			}
			chips.forEach( function ( c ) {
				c.addEventListener( 'click', function () {
					var a = parseInt( c.getAttribute( 'data-amount' ), 10 );
					input.value = a;
					render( a );
				} );
			} );
			input.addEventListener( 'input', function () { render( parseFloat( input.value ) ); } );
			render( parseFloat( input.value ) );
		} );
	}


	/* Bottom bar "Menu" button opens the main menu */
	function initTabbarMenu() {
		var btn = document.querySelector( '[data-smile-open-menu]' );
		var toggle = document.querySelector( '.smile-nav-toggle' );
		if ( btn && toggle ) {
			btn.addEventListener( 'click', function () { toggle.click(); } );
		}
	}


	/* Wall of hope: filters + lightbox */
	function initWall() {
		document.querySelectorAll( '[data-smile-wall]' ).forEach( function ( wall ) {
			var chips = wall.querySelectorAll( '.smile-wall__chip' );
			var items = Array.prototype.slice.call( wall.querySelectorAll( '.smile-wall__item' ) );
			chips.forEach( function ( chip ) {
				chip.addEventListener( 'click', function () {
					var f = chip.getAttribute( 'data-filter' );
					chips.forEach( function ( c ) { var on = c === chip; c.classList.toggle( 'is-active', on ); c.setAttribute( 'aria-pressed', on ? 'true' : 'false' ); } );
					items.forEach( function ( it ) { it.classList.toggle( 'is-hidden', f !== 'all' && it.getAttribute( 'data-cat' ) !== f ); } );
				} );
			} );
			wall.querySelectorAll( '.smile-wall__link' ).forEach( function ( l ) { l.setAttribute( 'data-elementor-open-lightbox', 'no' ); } );
			var box = wall.querySelector( '.smile-lightbox' );
			if ( !box || typeof box.showModal !== 'function' ) { return; }
			var img = box.querySelector( 'img' ), cap = box.querySelector( 'figcaption' ), idx = 0, lastFocus = null;
			function visible() { return items.filter( function ( it ) { return !it.classList.contains( 'is-hidden' ); } ); }
			function open( i ) {
				var list = visible(); if ( !list.length ) { return; }
				idx = ( i + list.length ) % list.length;
				var a = list[ idx ].querySelector( 'a' );
				img.src = a.getAttribute( 'href' ); img.alt = a.getAttribute( 'data-alt' ) || ''; cap.textContent = a.getAttribute( 'data-caption' ) || '';
				if ( !box.open ) { box.showModal(); }
			}
			items.forEach( function ( it ) {
				it.querySelector( 'a' ).addEventListener( 'click', function ( e ) {
					e.preventDefault(); lastFocus = this; open( visible().indexOf( it ) );
				} );
			} );
			box.querySelector( '.smile-lightbox__close' ).addEventListener( 'click', function () { box.close(); } );
			box.querySelector( '.smile-lightbox__prev' ).addEventListener( 'click', function () { open( idx - 1 ); } );
			box.querySelector( '.smile-lightbox__next' ).addEventListener( 'click', function () { open( idx + 1 ); } );
			box.addEventListener( 'click', function ( e ) { if ( e.target === box ) { box.close(); } } );
			box.addEventListener( 'keydown', function ( e ) { if ( e.key === 'ArrowRight' ) { open( idx + 1 ); } if ( e.key === 'ArrowLeft' ) { open( idx - 1 ); } } );
			box.addEventListener( 'close', function () { if ( lastFocus ) { lastFocus.focus(); } } );
		} );
	}

	/* Count-up numbers */
	function initCounters() {
		var els = document.querySelectorAll( '.smile-stats [data-smile-count]' );
		if ( !els.length || !( 'IntersectionObserver' in window ) || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( !e.isIntersecting ) { return; }
				io.unobserve( e.target );
				var el = e.target, end = parseInt( el.getAttribute( 'data-smile-count' ), 10 ), start = end > 1000 ? end - 40 : 0, t0 = null;
				function step( t ) { if ( !t0 ) { t0 = t; } var p = Math.min( 1, ( t - t0 ) / 1400 ), v = Math.round( start + ( end - start ) * ( 1 - Math.pow( 1 - p, 3 ) ) ); el.textContent = String( v ); if ( p < 1 ) { requestAnimationFrame( step ); } }
				requestAnimationFrame( step );
			} );
		}, { threshold: 0.6 } );
		els.forEach( function ( el ) { io.observe( el ); } );
	}


	/* Office open / closed badge (Nairobi time) */
	function initOffice() {
		document.querySelectorAll( '[data-smile-office]' ).forEach( function ( el ) {
			var r = el.getAttribute( 'data-smile-office' ).split( '-' ), open = parseInt( r[0], 10 ), close = parseInt( r[1], 10 ), sat = el.getAttribute( 'data-saturday' ) === '1';
			var h, wd;
			try {
				var parts = new Intl.DateTimeFormat( 'en-GB', { weekday: 'short', hour: 'numeric', hourCycle: 'h23', timeZone: 'Africa/Nairobi' } ).formatToParts( new Date() ), q = {};
				parts.forEach( function ( x ) { q[ x.type ] = x.value; } );
				h = parseInt( q.hour, 10 ); wd = q.weekday;
			} catch ( e ) { return; }
			if ( isNaN( h ) || isNaN( open ) || isNaN( close ) ) { return; }
			var isOpen = ( sat ? [ 'Sun' ] : [ 'Sat', 'Sun' ] ).indexOf( wd ) < 0 && h >= open && h < close;
			el.classList.add( isOpen ? 'is-open' : 'is-closed' );
			var st = el.querySelector( '.smile-hub__state' );
			if ( st ) { st.textContent = isOpen ? ( i18n.officeOpen || 'Office open now' ) : ( i18n.officeClosed || 'Office closed now' ); }
		} );
	}

	function initForms() {
		// Move focus to the status / first error after a server round-trip so screen readers announce it.
		var target = document.querySelector( '.smile-form__status[tabindex="-1"]' );
		if ( target ) { target.focus(); }
	}


	function initWhatsApp() {
		var root = document.querySelector( '[data-smile-wa]' );
		if ( !root ) { return; }
		var fab = root.querySelector( '.smile-wa__fab' ), panel = root.querySelector( '.smile-wa__panel' ), form = root.querySelector( '.smile-wa__form' );
		var text = root.querySelector( 'textarea' ), teaser = root.querySelector( '.smile-wa__teaser' ), number = root.getAttribute( 'data-number' );
		var store = { get: function ( k ) { try { return window.sessionStorage.getItem( k ); } catch ( e ) { return null; } }, set: function ( k, v ) { try { window.sessionStorage.setItem( k, v ); } catch ( e ) {} } };

		// Online status from Nairobi office hours (set in Centre details).
		var oh = parseInt( root.getAttribute( 'data-open-hour' ), 10 ), ch = parseInt( root.getAttribute( 'data-close-hour' ), 10 ), sat = root.getAttribute( 'data-saturday' ) === '1';
		if ( isNaN( oh ) ) { oh = 9; } if ( isNaN( ch ) ) { ch = 17; }
		try {
			var parts = new Intl.DateTimeFormat( 'en-GB', { weekday: 'short', hour: 'numeric', minute: '2-digit', hourCycle: 'h23', timeZone: 'Africa/Nairobi' } ).formatToParts( new Date() );
			var p = {}; parts.forEach( function ( x ) { p[ x.type ] = x.value; } );
			var h = parseInt( p.hour, 10 ), closedDays = sat ? [ 'Sun' ] : [ 'Sat', 'Sun' ], online = closedDays.indexOf( p.weekday ) < 0 && h >= oh && h < ch;
			root.classList.toggle( 'is-online', online );
			var st = root.querySelector( '.smile-wa__status' );
			if ( st ) { st.textContent = root.getAttribute( online ? 'data-open-text' : 'data-closed-text' ) || st.textContent; }
			var tm = root.querySelector( '.smile-wa__time' );
			if ( tm ) { tm.textContent = p.hour + ':' + p.minute; }
		} catch ( e ) {}

		function grow() { text.style.height = 'auto'; text.style.height = Math.min( text.scrollHeight, 120 ) + 'px'; }
		function hideTeaser() { if ( teaser ) { teaser.hidden = true; } store.set( 'smileWaTeaser', '1' ); }
		function open() {
			hideTeaser();
			panel.hidden = false; root.classList.add( 'is-open' ); fab.setAttribute( 'aria-expanded', 'true' );
			if ( window.matchMedia( '(min-width: 768px)' ).matches ) { text.focus(); } else { var c = root.querySelector( '.smile-wa__close' ); if ( c ) { c.focus(); } }
		}
		function close( refocus ) {
			panel.hidden = true; root.classList.remove( 'is-open' ); fab.setAttribute( 'aria-expanded', 'false' );
			if ( refocus ) { fab.focus(); }
		}
		fab.addEventListener( 'click', function () { if ( panel.hidden ) { open(); } else { close( true ); } } );
		root.querySelector( '.smile-wa__close' ).addEventListener( 'click', function () { close( true ); } );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' && !panel.hidden ) { close( true ); } } );
		document.addEventListener( 'click', function ( e ) { if ( !panel.hidden && !root.contains( e.target ) ) { close( false ); } } );

		root.querySelectorAll( '.smile-wa__chip' ).forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				root.querySelectorAll( '.smile-wa__chip' ).forEach( function ( c ) { c.classList.remove( 'is-picked' ); } );
				chip.classList.add( 'is-picked' );
				text.value = chip.textContent.trim() + '. '; grow(); text.focus();
				try { text.setSelectionRange( text.value.length, text.value.length ); } catch ( e ) {}
			} );
		} );
		text.addEventListener( 'input', grow );
		text.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' && !e.shiftKey && window.matchMedia( '(min-width: 768px)' ).matches ) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : form.dispatchEvent( new Event( 'submit', { cancelable: true } ) ); }
		} );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var msg = text.value.trim();
			var url = 'https://wa.me/' + number + ( msg ? '?text=' + encodeURIComponent( msg ) : '' );
			// window.open with "noopener" always returns null, which used to open WhatsApp twice.
			var w = window.open( url, '_blank' );
			if ( w ) { try { w.opener = null; } catch ( err ) {} } else { window.location.href = url; }
			text.value = ''; grow(); close( false );
		} );

		// Friendly nudge once per visit, after 12 seconds, not on the contact or donate pages.
		if ( teaser && !store.get( 'smileWaTeaser' ) && !document.body.classList.contains( 'smile-nav-open' ) && !/\/(contact-us|donate)\/?$/.test( window.location.pathname ) ) {
			// 2.34.0: the teaser no longer opens by itself (it covered page content). Opt back in with data-smile-wa-teaser on <body>.
			if ( document.body.hasAttribute( 'data-smile-wa-teaser' ) ) {
				window.setTimeout( function () { if ( panel.hidden && !store.get( 'smileWaTeaser' ) ) { teaser.hidden = false; } }, 12000 );
			}
			teaser.addEventListener( 'click', function ( e ) { if ( e.target.closest( '.smile-wa__teaser-close' ) ) { hideTeaser(); } else { open(); } } );
		}
	}


	function initJobs() {
		document.querySelectorAll( '[data-smile-jobs]' ).forEach( function ( root ) {
			var chips = root.querySelectorAll( '.smile-jobs__chip' ), jobs = root.querySelectorAll( '.smile-job' ), count = root.querySelector( '.smile-jobs__count' );
			var one = count ? count.textContent.replace( /^\d+\s*/, '' ) : '';
			chips.forEach( function ( chip ) {
				chip.addEventListener( 'click', function () {
					var f = chip.getAttribute( 'data-filter' ), shown = 0;
					chips.forEach( function ( c ) { var on = c === chip; c.classList.toggle( 'is-active', on ); c.setAttribute( 'aria-pressed', on ? 'true' : 'false' ); } );
					jobs.forEach( function ( j ) { var show = f === 'all' || j.getAttribute( 'data-type' ) === f; j.hidden = !show; if ( show ) { shown++; } } );
					if ( count ) { count.textContent = shown + ' ' + one; }
				} );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initNav(); initCopy(); initForms(); initScroll(); initReveal(); initSmiles(); initCalc(); initTabbarMenu(); initWall(); initCounters(); initOffice(); initWhatsApp(); initJobs(); } );
	} else {
		initNav(); initCopy(); initForms(); initScroll(); initReveal(); initSmiles(); initCalc(); initTabbarMenu(); initWall(); initCounters(); initOffice(); initWhatsApp(); initJobs();
	}
}() );
