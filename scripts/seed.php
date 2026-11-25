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
	// WP-CLI has no current user, so set the author (the post byline) to the site admin.
	$author   = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0] ?? 0;
	$args     = array_merge( array( 'post_status' => 'publish', 'post_type' => 'post', 'post_author' => $author ), $args );
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
/* About, Projects and 404 have no body: their copy is Pages → Edit → "Page copy" (defaults in nd_copy_fields()). */
nd_seed_post( array( 'post_type' => 'page', 'post_name' => 'about', 'post_title' => 'About', 'post_content' => '' ) );
nd_seed_post( array( 'post_type' => 'page', 'post_name' => 'projects', 'post_title' => 'Projects', 'menu_order' => 1 ) );
nd_seed_post( array( 'post_type' => 'page', 'post_name' => 'not-found', 'post_title' => '404 — Not found', 'post_status' => 'private', 'menu_order' => 9 ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $posts );
update_option( 'blogdescription', 'I build websites. And small tools.' );

/* ── Projects ──────────────────────────────────────── */
// nd_tags is a list field; the "a · b · c" text below is split into one row per tag when saved.
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
	'blog'    => nd_seed_term( 'Blog', 'category' ),
);
update_option( 'default_category', $cat['notes'] );
$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
if ( $uncategorized ) {
	wp_delete_term( $uncategorized->term_id, 'category' );
}

$article = nd_post_outlines()[''];
// Rows for "Post details" lists: $facts( 'Client' => 'UAB', … ), $sections( 'the problem' => 'Text', … ), $changes( array( 'new', 'Text' ), … ).
$facts    = static fn( $pairs ) => array_map( fn( $l, $v ) => array( 'label' => $l, 'value' => $v ), array_keys( $pairs ), $pairs );
$sections = static fn( $pairs ) => array_map( fn( $h, $t ) => array( 'heading' => $h, 'text' => $t ), array_keys( $pairs ), $pairs );
$changes  = static fn( ...$rows ) => array_map( fn( $c ) => array( 'type' => $c[0], 'text' => $c[1] ), $rows );

$seed_posts = array(
	array( 'joined-hounder', 'Joined Hounder', 'notes', '2026-04-01 09:00:00', '[One-line excerpt.]', $article, array() ),
	array( 'auditing-for-accessibility-before-launch', 'Auditing for accessibility before launch', 'notes', '', '[One-line excerpt.]', $article, array() ),
	array( 'starting-buck-et-list-brands', 'Starting Buck-et List Brands', 'update', '', '[One-line excerpt.]', '', array(
		'nd_facts'    => $facts( array( 'Client' => 'Buck-et List Brands', 'My role' => '[Your role]', 'Timeline' => '[Timeline]', 'Stack' => 'e-commerce' ) ),
		'nd_sections' => $sections( array( 'the problem' => 'A niche hunting and outdoor apparel brand needs a storefront.', 'the approach' => '[Stack and architecture decisions.]' ) ),
	) ),
	array( 'config-in-code-not-in-the-database', 'Config in code, not in the database', 'notes', '', '[One-line excerpt.]', $article, array() ),
	array( 'what-building-for-public-agencies-taught-me', 'What building for public agencies taught me', 'notes', '', '[One-line excerpt.]', $article, array() ),
	array( 'building-the-uab-alumni-site', 'Building the UAB alumni site', 'update', '',
		'A from-scratch WordPress theme that lets university staff publish alumni career stories without a developer.',
		'', array(
			'nd_facts'    => $facts( array( 'Client' => 'University of Alabama at Birmingham', 'Built at' => 'Hounder', 'My role' => '[Your role]', 'Timeline' => '[Timeline]', 'Stack' => 'wordpress · php · acf · javascript' ) ),
			'nd_sections' => $sections( array(
				'the problem'    => 'University staff needed to publish new alumni spotlights on their own, long after launch, without filing a ticket every time.',
				'the approach'   => "A hand-built theme with no parent theme or page builder. Custom blocks and ACF fields give editors structured slots for each story instead of a blank page.\n\n[More on decisions and trade-offs.]",
				'the result'     => '[What happened after launch: how staff use it, anything measurable.]',
				'what I learned' => '[What this project taught you, and what you’d do differently.]',
			) ),
			'nd_link_url' => 'https://sites.uab.edu/blazeabrighterfuture/',
		) ),
	array( 'first-blog-post', '[A personal title — what this one is about]', 'blog', '', '[Summary: one or two sentences in your own voice.]', nd_post_outlines()['blog'], array(
		'nd_blog_facts' => $facts( array( 'logged' => '[DATE]', 'from' => '[CITY]', 'on repeat' => '[What you were listening to or reading]' ) ),
	) ),
	array( 'bollard-0-1-2-is-out', 'Bollard 0.1.2 is out', 'release', '', '[One or two sentences on what this release is about.]', '', array(
		'nd_version'       => '0.1.2',
		'nd_changes'       => $changes( array( 'new', '[New feature]' ), array( 'new', '[New feature]' ), array( 'improved', '[Improvement]' ), array( 'fixed', '[Bug fix]' ) ),
		'nd_download_meta' => '4.1 MB · Free · Apple Silicon · macOS 11+',
		'nd_link_url'      => 'https://bollardapp.download',
	) ),
);
foreach ( $seed_posts as $i => list( $slug, $title, $kind, $date, $excerpt, $content, $meta ) ) {
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
	// Leftovers from earlier versions: per-post templates (the category decides now) and the old fixed fact fields.
	foreach ( array( '_wp_page_template', 'nd_client', 'nd_built_at', 'nd_role', 'nd_timeline', 'nd_stack' ) as $old ) {
		delete_post_meta( $id, $old );
	}
}

/* ── Menus (Appearance → Menus). Created once; re-seeding leaves your edits alone. ── */
$locations = get_theme_mod( 'nav_menu_locations', array() );
foreach ( array(
	'header' => array( 'Header', array(
		array( 'title' => 'Projects', 'type' => 'post_type_archive', 'object' => 'project' ),
		array( 'title' => 'Posts', 'type' => 'post_type', 'object' => 'page', 'object-id' => $posts ),
		array( 'title' => 'About', 'type' => 'post_type', 'object' => 'page', 'object-id' => get_page_by_path( 'about' )->ID ),
	) ),
	'footer' => array( 'Footer', array(
		array( 'title' => 'GitHub', 'type' => 'custom', 'url' => nd_copy( 'nd_github' ) ),
	) ),
) as $location => list( $name, $items ) ) {
	if ( wp_get_nav_menu_object( $name ) ) {
		WP_CLI::log( "Menu exists: {$name}" );
	} else {
		$menu = wp_create_nav_menu( $name );
		foreach ( $items as $item ) {
			wp_update_nav_menu_item( $menu, 0, array_combine( array_map( fn( $k ) => "menu-item-{$k}", array_keys( $item ) ), $item ) + array( 'menu-item-status' => 'publish' ) );
		}
		WP_CLI::log( "Created menu: {$name}" );
	}
	$locations[ $location ] = $locations[ $location ] ?? wp_get_nav_menu_object( $name )->term_id;
}
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Seeded pages, projects, posts and menus.' );
