/*
 * List ("rows") fields in the theme's edit boxes: + Add, ↑ ↓ reorder, × remove.
 * Inputs are submitted in on-screen order, so moving a row reorders it on save.
 */
( function () {
	let added = 0;
	document.querySelectorAll( '.nd-rows' ).forEach( function ( rows ) {
		const list = rows.querySelector( '.nd-rows__list' );
		const add = rows.querySelector( '.nd-rows__add' );
		add.addEventListener( 'click', function () {
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
				add.focus();
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
