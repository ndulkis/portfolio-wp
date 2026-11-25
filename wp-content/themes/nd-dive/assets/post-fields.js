/* Show only the "Template details" fields for the template picked in the editor. Disabled fields aren't saved. */
( function () {
	let last;
	wp.data.subscribe( function () {
		const box = document.getElementById( 'nd-post' );
		const template = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'template' ) || '';
		if ( ! box || template === last ) {
			return;
		}
		last = template;
		let any = false;
		box.querySelectorAll( '[data-templates]' ).forEach( function ( row ) {
			const on = row.dataset.templates.split( ' ' ).includes( template );
			any = any || on;
			row.style.display = on ? '' : 'none';
			row.querySelectorAll( 'input, textarea, select' ).forEach( function ( el ) {
				el.disabled = ! on;
			} );
		} );
		box.querySelector( '.nd-fields-hint' ).style.display = any ? 'none' : '';
	} );
} )();
