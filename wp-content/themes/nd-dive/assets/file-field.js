/*
 * Media Library file picker fields in the theme's edit boxes.
 * Stores the selected attachment ID and displays a link to the chosen file.
 */
( function () {
	document.querySelectorAll( '.nd-file' ).forEach( function ( wrap ) {
		const input = wrap.querySelector( 'input[type="hidden"]' );
		const info = wrap.querySelector( '.nd-file__info' );
		const link = wrap.querySelector( '.nd-file__link' );
		const none = wrap.querySelector( '.nd-file__none' );
		const pick = wrap.querySelector( '.nd-file__pick' );
		const clear = wrap.querySelector( '.nd-file__clear' );
		let frame;

		function sync( url, name ) {
			const hasFile = Boolean( input.value && url );
			info.hidden = ! hasFile;
			none.hidden = hasFile;
			clear.hidden = ! hasFile;
			pick.textContent = hasFile ? 'Replace file' : 'Choose from Media Library';
			if ( hasFile ) {
				link.href = url;
				link.textContent = name || url.split( '/' ).pop() || 'Selected file';
			} else {
				link.removeAttribute( 'href' );
				link.textContent = '';
			}
		}

		pick.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( ! window.wp || ! window.wp.media ) {
				return;
			}
			if ( ! frame ) {
				frame = window.wp.media( {
					title: 'Choose file',
					button: { text: 'Use this file' },
					multiple: false,
				} );
				frame.on( 'open', function () {
					const selection = frame.state().get( 'selection' );
					const id = parseInt( input.value, 10 );
					selection.reset();
					if ( id ) {
						const att = window.wp.media.attachment( id );
						att.fetch();
						selection.add( att );
					}
				} );
				frame.on( 'select', function () {
					const file = frame.state().get( 'selection' ).first();
					if ( ! file ) {
						return;
					}
					const url = file.get( 'url' ) || '';
					input.value = String( file.get( 'id' ) || '' );
					sync( url, file.get( 'filename' ) );
					input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				} );
			}
			frame.open();
		} );

		clear.addEventListener( 'click', function () {
			input.value = '';
			sync( '', '' );
			pick.focus();
		} );
	} );
} )();
