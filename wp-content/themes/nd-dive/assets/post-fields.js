/*
 * Post edit screen:
 * - Post details shows only the fields for the layout the category picks (hidden fields are disabled, so not saved).
 * - Project update / Release are built from their rows, so the body editor (and its label) hides while it's empty.
 * - Rows: + Add, ↑ ↓ reorder, × remove. Inputs are submitted in on-screen order.
 * - "Insert outline" (Notes) adds the starter body at the cursor.
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
		box.querySelector( '.nd-fields-hint' ).hidden = '' !== current;
		if ( body ) {
			body.hidden = label.hidden = '' !== current && bodyIsEmpty();
			label.querySelector( '[data-for="notes"]' ).hidden = '' !== current;
			label.querySelector( '[data-for="layout"]' ).hidden = '' === current;
		}
	}

	// After core syncs the "Most used" tab's checkboxes with the main list.
	cats.addEventListener( 'change', () => setTimeout( update ) );
	// TinyMCE fills the body after load, so check it once it's ready.
	window.addEventListener( 'load', update );

	box.querySelector( '.nd-outline' ).addEventListener( 'click', () => window.send_to_editor( window.ndPost.outline ) );

	let added = 0;
	box.querySelectorAll( '.nd-rows' ).forEach( function ( rows ) {
		const list = rows.querySelector( '.nd-rows__list' );
		rows.querySelector( '.nd-rows__add' ).addEventListener( 'click', function () {
			list.insertAdjacentHTML( 'beforeend', rows.querySelector( 'template' ).innerHTML.replaceAll( '__i__', 'new' + added++ ) );
			list.lastElementChild.querySelector( 'input, textarea, select' ).focus();
		} );
		list.addEventListener( 'click', function ( e ) {
			const button = e.target.closest( 'button' );
			const row = button && button.closest( '.nd-row' );
			if ( ! row ) {
				return;
			}
			if ( button.classList.contains( 'nd-remove' ) ) {
				row.remove();
				rows.querySelector( '.nd-rows__add' ).focus();
			} else if ( button.classList.contains( 'nd-up' ) && row.previousElementSibling ) {
				row.previousElementSibling.before( row );
				button.focus();
			} else if ( button.classList.contains( 'nd-down' ) && row.nextElementSibling ) {
				row.nextElementSibling.after( row );
				button.focus();
			}
		} );
	} );
} )();
