/*
 * Post edit screen:
 * - Post details shows only the fields for the layout the category picks (hidden fields are disabled, so not saved).
 * - Notes and Blog are written in the body (they have an outline); the other layouts are built from their rows,
 *   so there the body editor (and its label) hides while it's empty.
 * - "Insert outline" adds the current layout's starter body at the cursor.
 */
( function () {
	const box = document.getElementById( 'nd-post' );
	const cats = document.getElementById( 'categorydiv' );
	const body = document.getElementById( 'postdivrich' );
	const label = document.querySelector( '.nd-body-label' );
	if ( ! box || ! cats ) {
		return;
	}

	// Same rule as nd_post_layout(): the first layout whose category is ticked.
	function layout() {
		const ticked = Array.from( cats.querySelectorAll( '#categorychecklist input:checked' ), ( el ) => Number( el.value ) );
		return ( window.ndPost.layouts.find( ( [ , id ] ) => ticked.includes( id ) ) || [ '' ] )[ 0 ];
	}

	function bodyIsEmpty() {
		const editor = window.tinymce && window.tinymce.get( 'content' );
		const html = editor && ! editor.isHidden() ? editor.getContent() : document.getElementById( 'content' ).value;
		return '' === html.replace( /<[^>]*>|&nbsp;|\s/g, '' );
	}

	function update() {
		const current = layout();
		box.querySelectorAll( '[data-layouts]' ).forEach( function ( field ) {
			const on = field.dataset.layouts.split( ' ' ).includes( current );
			field.hidden = ! on;
			field.querySelectorAll( 'input, textarea, select' ).forEach( ( el ) => ( el.disabled = ! on ) );
		} );
		const written = current in window.ndPost.outlines;
		box.querySelector( '.nd-fields-hint' ).hidden = '' !== current;
		box.querySelector( '.nd-outline-row' ).hidden = ! written;
		if ( body ) {
			body.hidden = label.hidden = ! written && bodyIsEmpty();
			label.querySelector( '[data-for="body"]' ).hidden = ! written;
			label.querySelector( '[data-for="layout"]' ).hidden = written;
		}
	}

	// After core syncs the "Most used" tab's checkboxes with the main list.
	cats.addEventListener( 'change', () => setTimeout( update ) );
	// TinyMCE fills the body after load, so check it once it's ready.
	window.addEventListener( 'load', update );

	box.querySelector( '.nd-outline' ).addEventListener( 'click', () => window.send_to_editor( window.ndPost.outlines[ layout() ] ) );
} )();
