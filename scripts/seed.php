<?php
/**
 * Seed local content. Run with: lando seed  (wp eval-file scripts/seed.php)
 * Idempotent — matches existing content by slug and updates it.
 * Text in [brackets] is a placeholder for Nathan to fill in.
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

function nd_seed_term( $name, $tax ) {
	$t = term_exists( $name, $tax );
	return (int) ( $t ? $t['term_id'] : wp_insert_term( $name, $tax )['term_id'] );
}

function nd_seed_post( $args, $meta = array() ) {
	$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] ?? 'post' );
	$args     = array_merge( array( 'post_status' => 'publish', 'post_type' => 'post' ), $args );
	if ( $existing ) {
		$args['ID'] = $existing->ID;
	}
	$id = wp_insert_post( $args, true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	WP_CLI::log( ( $existing ? 'Updated ' : 'Created ' ) . $args['post_type'] . ': ' . $args['post_title'] );
	return $id;
}

$p  = static fn( $t ) => "<!-- wp:paragraph --><p>{$t}</p><!-- /wp:paragraph -->";
$h2 = static fn( $t ) => "<!-- wp:heading --><h2 class=\"wp-block-heading\">{$t}</h2><!-- /wp:heading -->";
$ul = static fn( ...$items ) => '<!-- wp:list --><ul class="wp-block-list">' . implode( '', array_map( fn( $i ) => "<!-- wp:list-item --><li>{$i}</li><!-- /wp:list-item -->", $items ) ) . '</ul><!-- /wp:list -->';

/* Clean out the WordPress defaults. */
foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as list( $slug, $type ) ) {
	$d = get_page_by_path( $slug, OBJECT, $type );
	if ( $d ) {
		wp_delete_post( $d->ID, true );
	}
}

/* ── Pages ─────────────────────────────────────────── */
$home  = nd_seed_post( array( 'post_type' => 'page', 'post_name' => 'home', 'post_title' => 'Home' ) );
$posts = nd_seed_post( array( 'post_type' => 'page', 'post_name' => 'posts', 'post_title' => 'Posts' ) );
nd_seed_post( array(
	'post_type'    => 'page',
	'post_name'    => 'about',
	'post_title'   => 'About',
	'post_content' => implode( '', array(
		nd_stop_markup( '0 m · surface', 'Where it started', '[Where you grew up and what you were into as a kid — one or two sentences.] I fell into software through an obsession with how things work.' ),
		nd_stop_markup( '30 m · curious', 'Getting hooked', 'It started with small scripts to get out of tedious tasks. Slowly that grew into a real interest in how whole systems fit together. [The moment or project that got you hooked.]' ),
		nd_stop_markup( '200 m · building', 'Building with friends', 'Side projects taught me to ship with other people: a blackjack trainer with Joon Rho, backed by 682 tests, and a Pokémon guessing game with Eric Morales.' ),
		nd_stop_markup( '1,000 m · today', 'Sites people depend on', 'Since April 2026 I’ve been a web developer at Hounder, building Drupal, WordPress, and Laravel sites for counties, airports, and utilities. Most of what I’m learning is the part tutorials skip: structuring a build so a non-technical editor can maintain it for years.' ),
	) ),
) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $posts );
update_option( 'blogdescription', 'I build websites. And small tools.' );

/* ── Projects ──────────────────────────────────────── */
$projects = array(
	array( 'uab-blaze-a-brighter-future', 'UAB — Blaze a Brighter Future', 'Client work', array(
		'nd_status' => 'live', 'nd_collab' => 'Built at Hounder',
		'nd_blurb' => 'Custom WordPress theme so staff can publish alumni stories without code.',
		'nd_problem' => 'University staff needed to publish alumni career stories without a developer.',
		'nd_built' => 'A from-scratch WordPress theme with custom blocks and ACF fields, responsive across the full browser matrix.',
		'nd_learned' => '[What this project taught you.]', 'nd_tags' => 'wordpress · php · acf · javascript',
		'nd_link_url' => 'https://sites.uab.edu/blazeabrighterfuture/', 'nd_link_label' => 'Visit site',
	) ),
	array( 'bollard', 'Bollard', 'Apps & tools', array(
		'nd_status' => 'live', 'nd_collab' => '',
		'nd_blurb' => 'Your tasks are just Markdown files in a folder. A free Mac app.',
		'nd_problem' => 'Most task apps lock your tasks inside a database or someone else’s cloud.',
		'nd_built' => 'A free Mac app where tasks are just Markdown files in a folder. No cloud, no accounts.',
		'nd_learned' => '[What this project taught you.]', 'nd_tags' => 'macos · markdown',
		'nd_link_url' => 'https://bollardapp.download', 'nd_link_label' => 'Visit site',
	) ),
	array( 'blackjack-tutor', 'Blackjack Tutor', 'Games', array(
		'nd_status' => 'live', 'nd_collab' => 'With Joon Rho',
		'nd_blurb' => 'Grades every hand against basic strategy and explains the mistake.',
		'nd_problem' => 'Learning basic strategy without ever knowing why a move was wrong.',
		'nd_built' => 'A 6-deck PyGame engine that grades every decision and explains mistakes. 682 tests, 96% coverage.',
		'nd_learned' => '[What this project taught you.]', 'nd_tags' => 'python · pygame · pytest',
		'nd_link_url' => 'https://github.com/ndulkis/blackjack-tutor', 'nd_link_label' => 'View on GitHub',
	) ),
	array( 'pokeguess', 'PokeGuess', 'Games', array(
		'nd_status' => 'live', 'nd_collab' => 'With Eric Morales',
		'nd_blurb' => 'Name the Pokémon from silhouettes and clues.',
		'nd_problem' => '[What made you want to build it?]',
		'nd_built' => 'A guessing game where players name Pokémon from silhouettes and clues. React 19, Vite, Tailwind v4.',
		'nd_learned' => '[What this project taught you.]', 'nd_tags' => 'react · vite · tailwind · react router',
		'nd_link_url' => 'https://github.com/ndulkis/poke-guess', 'nd_link_label' => 'View on GitHub',
	) ),
	array( 'habit-tracker', 'Habit Tracker', 'Apps & tools', array(
		'nd_status' => 'live', 'nd_collab' => '',
		'nd_blurb' => 'A CLI that builds a year-long Excel habit tracker.',
		'nd_problem' => '[What made you want to build it?]',
		'nd_built' => 'A CLI that generates a year-long Excel tracker with monthly sheets, success rates, and trend charts.',
		'nd_learned' => '[What this project taught you.]', 'nd_tags' => 'python · xlsxwriter · cli',
		'nd_link_url' => 'https://github.com/ndulkis/habit-tracker', 'nd_link_label' => 'View on GitHub',
	) ),
	array( 'buck-et-list-brands', 'Buck-et List Brands', 'Client work', array(
		'nd_status' => 'in-progress', 'nd_collab' => '',
		'nd_blurb' => 'A storefront for a hunting and outdoor apparel brand.',
		'nd_problem' => 'A niche hunting and outdoor apparel brand needs a storefront.',
		'nd_built' => 'Early development — still working out the architecture and stack.',
		'nd_learned' => '[Check back soon.]', 'nd_tags' => 'e-commerce',
		'nd_link_url' => '', 'nd_link_label' => '',
	) ),
);
foreach ( $projects as $i => list( $slug, $title, $type, $meta ) ) {
	$id = nd_seed_post( array( 'post_type' => 'project', 'post_name' => $slug, 'post_title' => $title, 'menu_order' => $i ), $meta );
	wp_set_object_terms( $id, nd_seed_term( $type, 'project_type' ), 'project_type' );
}

/* ── Posts (one per template, plus notes) ──────────── */
$cat = array(
	'notes'   => nd_seed_term( 'Notes', 'category' ),
	'update'  => nd_seed_term( 'Project update', 'category' ),
	'release' => nd_seed_term( 'Release', 'category' ),
);
update_option( 'default_category', $cat['notes'] );
$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
if ( $uncategorized ) {
	wp_delete_term( $uncategorized->term_id, 'category' );
}

$article = static fn() => $p( '[Opening paragraph. Set up the story in two or three sentences.]' ) . $p( '[Body paragraph.]' ) . $h2( '[Section heading]' ) . $p( '[Body paragraph.]' )
	. '<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>[A pull quote — the one line you want people to remember.]</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->'
	. $h2( '[Section heading]' ) . $ul( '[Takeaway one]', '[Takeaway two]', '[Takeaway three]' ) . $p( '[Closing paragraph.]' );

$seed_posts = array(
	array( 'joined-hounder', 'Joined Hounder', 'notes', '', '2026-04-01 09:00:00', '[One-line excerpt.]', $article(), array() ),
	array( 'auditing-for-accessibility-before-launch', 'Auditing for accessibility before launch', 'notes', '', '', '[One-line excerpt.]', $article(), array() ),
	array( 'starting-buck-et-list-brands', 'Starting Buck-et List Brands', 'update', 'templates/post-case-study.php', '', '[One-line excerpt.]',
		$h2( '01 · the problem' ) . $p( 'A niche hunting and outdoor apparel brand needs a storefront.' ) . $h2( '02 · the approach' ) . $p( '[Stack and architecture decisions.]' ),
		array( 'nd_client' => 'Buck-et List Brands', 'nd_role' => '[Your role]', 'nd_timeline' => '[Timeline]', 'nd_stack' => 'e-commerce' ) ),
	array( 'config-in-code-not-in-the-database', 'Config in code, not in the database', 'notes', '', '', '[One-line excerpt.]', $article(), array() ),
	array( 'what-building-for-public-agencies-taught-me', 'What building for public agencies taught me', 'notes', '', '', '[One-line excerpt.]', $article(), array() ),
	array( 'building-the-uab-alumni-site', 'Building the UAB alumni site', 'update', 'templates/post-case-study.php', '',
		'A from-scratch WordPress theme that lets university staff publish alumni career stories without a developer.',
		$h2( '01 · the problem' ) . $p( 'University staff needed to publish new alumni spotlights on their own, long after launch, without filing a ticket every time.' )
		. $h2( '02 · the approach' ) . $p( 'A hand-built theme with no parent theme or page builder. Custom blocks and ACF fields give editors structured slots for each story instead of a blank page.' ) . $p( '[More on decisions and trade-offs.]' )
		. $h2( '03 · the result' ) . $p( '[What happened after launch: how staff use it, anything measurable.]' )
		. $h2( '04 · what I learned' ) . $p( '[What this project taught you, and what you’d do differently.]' ),
		array( 'nd_client' => 'University of Alabama at Birmingham', 'nd_built_at' => 'Hounder', 'nd_role' => '[Your role]', 'nd_timeline' => '[Timeline]', 'nd_stack' => 'wordpress · php · acf · javascript', 'nd_link_url' => 'https://sites.uab.edu/blazeabrighterfuture/' ) ),
	array( 'bollard-0-1-2-is-out', 'Bollard 0.1.2 is out', 'release', 'templates/post-release.php', '', '[One-line summary of what changed.]',
		$p( '[One or two sentences on what this release is about.]' ) . $h2( 'New' ) . $ul( '[New feature]', '[New feature]' ) . $h2( 'Improved' ) . $ul( '[Improvement]' ) . $h2( 'Fixed' ) . $ul( '[Bug fix]' ),
		array( 'nd_version' => '0.1.2', 'nd_download_meta' => '4.1 MB · Free · Apple Silicon · macOS 11+', 'nd_link_url' => 'https://bollardapp.download' ) ),
);
foreach ( $seed_posts as $i => list( $slug, $title, $kind, $template, $date, $excerpt, $content, $meta ) ) {
	$args = array(
		'post_name'     => $slug,
		'post_title'    => $title,
		'post_excerpt'  => $excerpt,
		'post_content'  => $content,
		'post_category' => array( $cat[ $kind ] ),
		// Placeholder dates: oldest first, one week apart, newest = today. Replace with real dates.
		'post_date'     => $date ?: wp_date( 'Y-m-d H:i:s', strtotime( '-' . ( count( $seed_posts ) - 1 - $i ) . ' weeks' ) ),
	);
	$id = nd_seed_post( $args, $meta );
	update_post_meta( $id, '_wp_page_template', $template ?: 'default' );
}

WP_CLI::success( 'Seeded pages, projects and posts.' );
