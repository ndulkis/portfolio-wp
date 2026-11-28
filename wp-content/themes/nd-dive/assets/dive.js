/*
 * Progressive enhancements: 404 dive-torch cursor, link hover bubble puffs,
 * and seafloor click bubble bursts. Degrades cleanly with no JS or under prefers-reduced-motion.
 */
( function () {
	const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/** Smooth GPU-composited sinusoidal bubble rise using Web Animations API. */
	function floatBubble( el, opts ) {
		const {
			rise,
			drift = 0,
			sway = 5,
			cycles = 1,
			maxOpacity = 0.78,
			duration = 1350,
			delay = 0,
			baseY = 0,
		} = opts;
		const dir = Math.random() < 0.5 ? 1 : -1;
		const steps = 14;
		const frames = [];
		for ( let s = 0; s <= steps; s++ ) {
			const t = s / steps;
			const y = baseY - rise * ( 1 - Math.pow( 1 - t, 1.35 ) );
			const x = drift * t + Math.sin( t * Math.PI * 2 * cycles ) * sway * dir;
			const scale = 0.48 + 0.58 * Math.sin( t * Math.PI * 0.5 );
			const opacity = Math.sin( Math.pow( t, 0.72 ) * Math.PI ) * maxOpacity;
			frames.push( {
				transform: `translate3d(${ x.toFixed( 2 ) }px, ${ y.toFixed( 2 ) }px, 0) scale(${ scale.toFixed( 3 ) })`,
				opacity: Number( opacity.toFixed( 3 ) ),
			} );
		}
		if ( ! el.animate ) {
			el.remove();
			return;
		}
		const anim = el.animate( frames, { duration, delay, easing: 'linear', fill: 'forwards' } );
		anim.onfinish = () => el.remove();
	}

	// 404 dive-torch cursor
	const abyss = document.querySelector( '.nd-ocean--abyss' );
	if ( abyss && ! reduced && window.matchMedia( '(pointer: fine)' ).matches ) {
		let raf = 0;
		let x = 0;
		let y = 0;
		const paint = () => {
			raf = 0;
			abyss.style.setProperty( '--tx', x + 'px' );
			abyss.style.setProperty( '--ty', y + 'px' );
		};
		window.addEventListener( 'pointermove', ( e ) => {
			if ( 'touch' === e.pointerType ) {
				return;
			}
			x = e.clientX;
			y = e.clientY;
			abyss.classList.add( 'is-torch-on' );
			if ( ! raf ) {
				raf = window.requestAnimationFrame( paint );
			}
		}, { passive: true } );
		document.documentElement.addEventListener( 'mouseleave', () => {
			abyss.classList.remove( 'is-torch-on' );
		} );
	}

	// Link hover bubble puff: release 3 gentle bubbles when a fine pointer enters a link
	const ocean = document.querySelector( '.nd-ocean' );
	if ( ocean && ! reduced && window.matchMedia( '(pointer: fine)' ).matches ) {
		const linkSelector = '.nd-section__head a, .nd-split a, .nd-story-card__body > a, .nd-footer a, .nd-log a, .nd-back a';
		ocean.addEventListener( 'pointerover', ( e ) => {
			if ( 'touch' === e.pointerType ) {
				return;
			}
			const link = e.target.closest( linkSelector );
			if ( ! link || ( e.relatedTarget && link.contains( e.relatedTarget ) ) ) {
				return;
			}
			const oceanRect = ocean.getBoundingClientRect();
			const rect = link.getBoundingClientRect();
			const frag = document.createDocumentFragment();
			const queue = [];
			for ( let i = 0; i < 3; i++ ) {
				const b = document.createElement( 'span' );
				const size = 4 + Math.round( Math.random() * 3 );
				b.className = 'nd-link-puff';
				b.style.left = ( rect.left - oceanRect.left + rect.width * ( 0.18 + Math.random() * 0.68 ) - size / 2 ).toFixed( 1 ) + 'px';
				b.style.top = ( rect.top - oceanRect.top + 3 - size / 2 ).toFixed( 1 ) + 'px';
				b.style.width = size + 'px';
				b.style.height = size + 'px';
				frag.appendChild( b );
				queue.push( [ b, {
					rise: 30 + Math.random() * 10,
					drift: ( Math.random() - 0.5 ) * 6,
					sway: 3 + Math.random() * 3.5,
					cycles: 1,
					maxOpacity: 0.76,
					duration: 1200 + Math.round( Math.random() * 380 ),
					delay: i * 140 + Math.round( Math.random() * 45 ),
				} ] );
			}
			ocean.appendChild( frag );
			queue.forEach( ( [ el, opts ] ) => floatBubble( el, opts ) );
		} );
	}

	// Seafloor Easter egg: click the clam, starfish, or coral for a bubble burst
	const wrap = document.querySelector( '.nd-seafloor-wrap' );
	if ( ! wrap || reduced ) {
		return;
	}
	const words = { clam: '*blub*', starfish: '*hop*', coral: '*swish*' };
	wrap.addEventListener( 'click', ( e ) => {
		const target = e.target.closest( '[data-critter]' );
		if ( ! target ) {
			return;
		}
		const kind = target.dataset.critter;
		const rect = wrap.getBoundingClientRect();
		const ox = e.clientX - rect.left;
		const oy = e.clientY - rect.top;

		target.classList.remove( 'is-popped' );
		void target.getBoundingClientRect();
		target.classList.add( 'is-popped' );
		window.setTimeout( () => target.classList.remove( 'is-popped' ), 700 );

		const count = kind === 'clam' ? 7 : 5;
		const frag = document.createDocumentFragment();
		const queue = [];
		for ( let i = 0; i < count; i++ ) {
			const b = document.createElement( 'span' );
			const size = 5 + Math.round( Math.random() * 7 );
			b.className = 'nd-pop-bubble';
			b.style.left = ( ox - size / 2 ).toFixed( 1 ) + 'px';
			b.style.top = ( oy - size / 2 ).toFixed( 1 ) + 'px';
			b.style.width = size + 'px';
			b.style.height = size + 'px';
			frag.appendChild( b );
			queue.push( [ b, {
				rise: 92 + Math.random() * 32,
				drift: ( Math.random() - 0.5 ) * 24,
				sway: 5 + Math.random() * 7,
				cycles: 1.25,
				maxOpacity: 0.82,
				duration: 1400 + Math.round( Math.random() * 450 ),
				delay: i * 85 + Math.round( Math.random() * 35 ),
			} ] );
		}
		wrap.appendChild( frag );
		queue.forEach( ( [ el, opts ] ) => floatBubble( el, opts ) );

		if ( words[ kind ] ) {
			const note = document.createElement( 'span' );
			note.className = 'nd-pop-note';
			note.textContent = words[ kind ];
			note.style.left = ox + 'px';
			note.style.top = oy - 10 + 'px';
			note.addEventListener( 'animationend', () => note.remove(), { once: true } );
			wrap.appendChild( note );
		}
	} );
} )();
