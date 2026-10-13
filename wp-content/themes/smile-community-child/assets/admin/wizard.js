/* Smile setup wizard: sequential plugin install and step-by-step content import. */
( function () {
	'use strict';
	var cfg = window.smileWizard;
	if ( ! cfg ) { return; }

	function post( data ) {
		var body = new FormData();
		body.append( 'nonce', cfg.nonce );
		Object.keys( data ).forEach( function ( k ) { body.append( k, data[ k ] ); } );
		return fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) { return r.json().catch( function () { return { success: false, data: { message: 'HTTP ' + r.status } }; } ); } );
	}

	// Step 1: plugins.
	var installBtn = document.getElementById( 'smile-install-plugins' );
	if ( installBtn ) {
		installBtn.addEventListener( 'click', function () {
			var rows = Array.prototype.slice.call( document.querySelectorAll( '.smile-wizard__plugins tbody tr' ) ).filter( function ( tr ) {
				var cb = tr.querySelector( 'input[type=checkbox]' );
				return cb && cb.checked && ! cb.disabled;
			} );
			if ( ! rows.length ) { window.location = cfg.next2; return; }
			installBtn.disabled = true;
			var failed = false;
			var run = function ( i ) {
				if ( i >= rows.length ) {
					installBtn.disabled = false;
					if ( ! failed ) { window.location = cfg.next2; }
					return;
				}
				var tr = rows[ i ];
				var cell = tr.querySelector( '.smile-wizard__status' );
				cell.textContent = cfg.i18n.working;
				post( { action: 'smile_install_plugin', slug: tr.getAttribute( 'data-slug' ) } ).then( function ( res ) {
					var msg = res && res.data && res.data.message ? res.data.message : cfg.i18n.failed;
					cell.textContent = msg;
					cell.className = 'smile-wizard__status ' + ( res.success ? 'is-ok' : 'is-err' );
					if ( res.success ) {
						var cb = tr.querySelector( 'input[type=checkbox]' );
						cb.checked = false; cb.disabled = true;
					} else { failed = true; }
					run( i + 1 );
				} ).catch( function () {
					cell.textContent = cfg.i18n.failed; cell.className = 'smile-wizard__status is-err'; failed = true; run( i + 1 );
				} );
			};
			run( 0 );
		} );
	}

	// Step 2: content.
	var importBtn = document.getElementById( 'smile-import' );
	if ( importBtn ) {
		importBtn.addEventListener( 'click', function () {
			var opt = function ( id ) { var el = document.getElementById( id ); return el && el.checked ? '1' : ''; };
			var opts = { overwrite: opt( 'smile-o-overwrite' ), noindex: opt( 'smile-o-noindex' ) };
			var steps = [];
			if ( opt( 'smile-o-images' ) ) { cfg.images.forEach( function ( k ) { steps.push( 'image:' + k ); } ); }
			if ( opt( 'smile-o-cleanup' ) ) { steps.push( 'cleanup' ); }
			if ( opt( 'smile-o-title' ) ) { steps.push( 'sitetitle' ); }
			steps.push( 'categories', 'pages' );
			if ( opt( 'smile-o-retire' ) ) { steps.push( 'retire' ); }
			steps.push( 'reading', 'menus', 'logo', 'slider' );
			if ( opt( 'smile-o-posts' ) ) { steps.push( 'posts-index', 'tidy-stories' ); }
			if ( opt( 'smile-o-enhance' ) ) { steps.push( 'enhance-index' ); }
			if ( opt( 'smile-o-kit' ) ) { steps.push( 'kit' ); }
			steps.push( 'finish' );

			var wrap = document.querySelector( '.smile-wizard__progress' );
			var bar = wrap.querySelector( '.smile-wizard__bar span' );
			var now = wrap.querySelector( '.smile-wizard__now' );
			var log = wrap.querySelector( '.smile-wizard__log' );
			wrap.hidden = false;
			importBtn.disabled = true;
			document.querySelectorAll( '.smile-wizard__opts input' ).forEach( function ( i ) { i.disabled = true; } );
			var errors = 0;

			var run = function ( i ) {
				bar.style.width = Math.round( ( i / steps.length ) * 100 ) + '%';
				if ( i >= steps.length ) {
					now.textContent = errors ? errors + ' step(s) reported a problem. See Details, then continue.' : 'Import complete.';
					var a = document.createElement( 'a' );
					a.className = 'button button-primary button-hero';
					a.href = cfg.next3;
					a.textContent = 'Next: finish';
					importBtn.parentNode.appendChild( a );
					return;
				}
				now.textContent = cfg.i18n.working + ' (' + ( i + 1 ) + '/' + steps.length + ')';
				var data = { action: 'smile_demo_step', step: steps[ i ] };
				Object.keys( opts ).forEach( function ( k ) { data[ k ] = opts[ k ]; } );
				post( data ).then( function ( res ) {
					var msg = res && res.data && res.data.message ? res.data.message : cfg.i18n.failed;
					var li = document.createElement( 'li' );
					li.textContent = msg;
					if ( ! res.success || 0 === msg.indexOf( 'FAILED' ) ) { li.className = 'is-err'; errors++; }
					log.appendChild( li );
					if ( res.success && res.data && res.data.add_steps && res.data.add_steps.length ) {
						var at = i + 1;
						Array.prototype.splice.apply( steps, [ at, 0 ].concat( res.data.add_steps ) );
					}
					run( i + 1 );
				} ).catch( function () {
					var li = document.createElement( 'li' ); li.className = 'is-err'; li.textContent = steps[ i ] + ': ' + cfg.i18n.failed; log.appendChild( li ); errors++; run( i + 1 );
				} );
			};
			run( 0 );
		} );
	}
}() );
