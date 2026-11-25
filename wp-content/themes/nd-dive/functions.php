<?php
/**
 * ND Dive theme setup.
 *
 * Content model:
 * - Pages: Home (front page), Posts (posts page), About.
 * - Projects: custom post type `project` + `project_type` taxonomy, story fields in post meta.
 * - Posts: category = post kind (Notes / Project update / Release); per-post template picks the layout.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'dark-editor-style' );
	// The block editor scopes these to the canvas: site tokens from style.css, prose from editor-style.css.
	add_editor_style( array( ND_FONTS_URL, 'style.css', 'editor-style.css' ) );
	register_nav_menus( array( 'header' => 'Header navigation', 'footer' => 'Footer links' ) );
} );

const ND_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap';

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'nd-fonts', ND_FONTS_URL, array(), null );
	wp_enqueue_style( 'nd-dive', get_stylesheet_uri(), array( 'nd-fonts' ), wp_get_theme()->get( 'Version' ) );
} );

/* ── Projects ─────────────────────────────────────────── */

/*
 * Field definitions: key => array( label, type = text|textarea|url|select, help, options, templates ).
 * `templates` (post fields only) lists the post templates the field belongs to.
 */

/** Story fields shown on project cards. */
function nd_project_fields() {
	return array(
		'nd_status'     => array( 'label' => 'Status', 'type' => 'select', 'options' => array( 'live' => 'Live', 'in-progress' => 'In progress' ) ),
		'nd_collab'     => array( 'label' => 'Collaborator line', 'help' => 'Optional. e.g. "With Joon Rho" or "Built at Hounder".' ),
		'nd_blurb'      => array( 'label' => 'Short blurb', 'type' => 'textarea', 'help' => 'One sentence for the home page card.' ),
		'nd_problem'    => array( 'label' => 'Problem', 'type' => 'textarea', 'help' => 'What needed solving, in a sentence or two.' ),
		'nd_built'      => array( 'label' => 'Built', 'type' => 'textarea', 'help' => 'What you made and how.' ),
		'nd_learned'    => array( 'label' => 'Learned', 'type' => 'textarea', 'help' => 'What the project taught you.' ),
		'nd_tags'       => array( 'label' => 'Tech tags', 'help' => 'Separate with " · ", e.g. wordpress · php · acf' ),
		'nd_link_url'   => array( 'label' => 'Link URL', 'type' => 'url', 'help' => 'Leave empty to hide the link.' ),
		'nd_link_label' => array( 'label' => 'Link label', 'help' => 'e.g. "Visit site" or "View on GitHub". Defaults to "Visit".' ),
	);
}

/** Extra fields for the case study and release post templates. */
function nd_post_fields() {
	$case = 'templates/post-case-study.php';
	$rel  = 'templates/post-release.php';
	return array(
		'nd_client'        => array( 'label' => 'Client', 'templates' => array( $case ) ),
		'nd_built_at'      => array( 'label' => 'Built at', 'templates' => array( $case ), 'help' => 'Optional, e.g. "Hounder".' ),
		'nd_role'          => array( 'label' => 'My role', 'templates' => array( $case ) ),
		'nd_timeline'      => array( 'label' => 'Timeline', 'templates' => array( $case ), 'help' => 'e.g. "Jan – Apr 2026"' ),
		'nd_stack'         => array( 'label' => 'Stack', 'templates' => array( $case ), 'help' => 'Separate with " · "' ),
		'nd_version'       => array( 'label' => 'Version', 'templates' => array( $rel ), 'help' => 'Without the "v", e.g. 0.1.2' ),
		'nd_download_meta' => array( 'label' => 'Download details', 'templates' => array( $rel ), 'help' => 'e.g. "4.1 MB · Free · Apple Silicon · macOS 11+"' ),
		'nd_link_url'      => array( 'label' => 'Link URL', 'type' => 'url', 'templates' => array( $case, $rel ), 'help' => 'Case study: "Visit the site" button. Release: "Download" button. Leave empty to hide.' ),
	);
}

/** Sanitizer for a field definition (also runs on update_post_meta via register_post_meta). */
function nd_field_sanitizer( $field ) {
	return match ( $field['type'] ?? 'text' ) {
		'textarea' => static fn( $v ) => sanitize_textarea_field( $v ),
		'url'      => static fn( $v ) => esc_url_raw( $v ),
		'email'    => static fn( $v ) => sanitize_email( $v ),
		'select'   => static fn( $v ) => is_string( $v ) && array_key_exists( $v, $field['options'] ) ? $v : array_key_first( $field['options'] ),
		default    => static fn( $v ) => sanitize_text_field( $v ),
	};
}

add_action( 'init', function () {
	register_post_type( 'project', array(
		'labels'       => array(
			'name'                  => 'Projects',
			'singular_name'         => 'Project',
			'add_new_item'          => 'Add project',
			'featured_image'        => 'Card screenshot',
			'set_featured_image'    => 'Set card screenshot',
			'remove_featured_image' => 'Remove card screenshot',
		),
		'public'       => true,
		'has_archive'  => 'projects',
		'rewrite'      => array( 'slug' => 'projects' ),
		'menu_icon'    => 'dashicons-portfolio',
		'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
	) );
	register_taxonomy( 'project_type', 'project', array(
		'label'             => 'Project types',
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'project-type' ),
	) );
	$page_fields = array_merge( ...array_column( array_diff_key( nd_copy_fields(), array( 'nd_links' => 0 ) ), 1 ) );
	foreach ( array( 'project' => nd_project_fields(), 'post' => nd_post_fields(), 'page' => $page_fields ) as $type => $fields ) {
		foreach ( $fields as $key => $field ) {
			register_post_meta( $type, $key, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => nd_field_sanitizer( $field ) ) );
		}
	}
} );

/* Simple meta boxes so the fields are editable without a plugin. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nd-project', 'Project story', 'nd_render_fields_box', 'project', 'normal', 'high', array( 'fields' => nd_project_fields() ) );
	add_meta_box( 'nd-post', 'Template details', 'nd_render_fields_box', 'post', 'normal', 'default', array( 'fields' => nd_post_fields() ) );
} );

/*
 * Renders typed fields. Post fields that don't belong to the post's template are hidden and
 * disabled (so they aren't submitted); assets/post-fields.js re-toggles them when the
 * template is changed in the editor.
 */
function nd_render_fields_box( $post, $box ) {
	wp_nonce_field( 'nd_fields', 'nd_fields_nonce' );
	$template = get_page_template_slug( $post );
	$any      = false;
	echo '<table class="form-table" role="presentation">';
	foreach ( $box['args']['fields'] as $key => $field ) {
		$value = metadata_exists( 'post', $post->ID, $key ) ? get_post_meta( $post->ID, $key, true ) : ( $field['default'] ?? '' );
		$attrs = sprintf( 'id="%1$s" name="%1$s" class="widefat"', esc_attr( $key ) );
		$off   = isset( $field['templates'] ) && ! in_array( $template, $field['templates'], true );
		$any   = $any || ! $off;
		if ( $off ) {
			$attrs .= ' disabled';
		}
		switch ( $field['type'] ?? 'text' ) {
			case 'textarea':
				$input = sprintf( '<textarea %s rows="3">%s</textarea>', $attrs, esc_textarea( $value ) );
				break;
			case 'select':
				$input = sprintf( '<select %s>', $attrs );
				foreach ( $field['options'] as $opt => $opt_label ) {
					$input .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $opt_label ) );
				}
				$input .= '</select>';
				break;
			default:
				$input = sprintf( '<input type="%s" %s value="%s">', 'url' === ( $field['type'] ?? '' ) ? 'url' : 'text', $attrs, esc_attr( $value ) );
		}
		printf(
			'<tr%s%s><th><label for="%s">%s</label></th><td>%s%s</td></tr>',
			isset( $field['templates'] ) ? ' data-templates="' . esc_attr( implode( ' ', $field['templates'] ) ) . '"' : '',
			$off ? ' style="display:none"' : '',
			esc_attr( $key ),
			esc_html( $field['label'] ),
			$input, // Built from escaped parts above.
			empty( $field['help'] ) ? '' : '<p class="description">' . esc_html( $field['help'] ) . '</p>'
		); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</table>';
	if ( 'post' === $post->post_type ) {
		printf( '<p class="nd-fields-hint"%s>Choose the <strong>Case study</strong> or <strong>Release note</strong> template (Post → Template) to fill in its details. Articles don’t need any.</p>', $any ? ' style="display:none"' : '' );
	}
}

/** The editable fields a post has: project story, post template details, or its page copy. */
function nd_fields_for( $post ) {
	return match ( $post->post_type ) {
		'project' => nd_project_fields(),
		'post'    => nd_post_fields(),
		'page'    => nd_copy_fields()[ nd_page_section( $post->ID ) ][1] ?? array(),
		default   => array(),
	};
}

add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['nd_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nd_fields_nonce'] ), 'nd_fields' ) ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array_keys( nd_fields_for( $post ) ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) ); // Sanitized by register_post_meta.
		}
	}
}, 10, 2 );

/* Projects are form-only (no body), so the classic screen is quicker than an empty block canvas. */
add_filter( 'use_block_editor_for_post_type', fn( $use, $type ) => 'project' === $type ? false : $use, 10, 2 );

add_action( 'enqueue_block_editor_assets', function () {
	if ( 'post' === get_current_screen()?->post_type ) {
		wp_enqueue_script( 'nd-post-fields', get_theme_file_uri( 'assets/post-fields.js' ), array( 'wp-data', 'wp-editor' ), wp_get_theme()->get( 'Version' ), true );
	}
} );

/* Projects archive: manual order (Page Attributes → Order), show all. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_post_type_archive( 'project' ) || $q->is_tax( 'project_type' ) ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$q->set( 'posts_per_page', -1 );
	}
} );

/*
 * ── Fixed copy on each page ──
 * Page copy is post meta on its page, edited in Pages → Edit ("Page copy" box).
 * Site-wide links stay in the Customizer. Templates read both with nd_copy( 'id' ).
 * section => array( title, fields: id => array( label, default, type = text|textarea|url|email ) ).
 */
// ponytail: fixed number of About story slots; raise it (or switch to a repeater) if the story outgrows it.
const ND_STOPS = 6;

function nd_copy_fields() {
	static $fields;
	if ( $fields ) {
		return $fields;
	}
	$f = static fn( $label, $default, $type = 'text', $help = '' ) => array( 'label' => $label, 'default' => $default, 'type' => $type, 'help' => $help );
	// A button or link: "{$id}_label" + "{$id}_url" (printed with nd_link()). Empty label hides it.
	$btn = static fn( $id, $name, $label, $url = null ) => array( "{$id}_label" => $f( "{$name} — label", $label, 'text', null === $url ? 'Links to the address in Customizer → Links & details (hidden until set).' : 'Leave empty to hide.' ) )
		+ ( null === $url ? array() : array( "{$id}_url" => $f( "{$name} — link", $url, 'url', 'A path like /about/ or a full https:// address.' ) ) );
	// About story stops: nd_stop{n}_depth/_title/_text, ND_STOPS slots; $defaults fill the first ones.
	$stops = static function ( $defaults ) use ( $f ) {
		$out = array();
		foreach ( range( 1, ND_STOPS ) as $n ) {
			list( $depth, $title, $text ) = $defaults[ $n - 1 ] ?? array( '', '', '' );
			$out[ "nd_stop{$n}_depth" ] = $f( "Story stop {$n} — depth", $depth, 'text', 1 === $n ? 'The label on the depth line, e.g. "30 m · curious".' : '' );
			$out[ "nd_stop{$n}_title" ] = $f( "Story stop {$n} — heading", $title, 'text', 1 === $n ? 'Leave a heading empty to hide that stop.' : '' );
			$out[ "nd_stop{$n}_text" ]  = $f( "Story stop {$n} — text", $text, 'textarea' );
		}
		return $out;
	};
	$fields = array(
		'nd_links'    => array( 'Links & details', array(
			'nd_github'   => $f( 'GitHub URL', 'https://github.com/ndulkis', 'url' ),
			'nd_linkedin' => $f( 'LinkedIn URL', '', 'url' ),
			'nd_email'    => $f( 'Email address', '', 'email' ),
		) ),
		'nd_home'     => array( 'Home page', array(
			'nd_home_title'     => $f( 'Headline', 'I build websites.' ),
			'nd_home_highlight' => $f( 'Headline — highlighted line', 'And small tools.' ),
			'nd_home_lede'      => $f( 'Intro', 'I’m Nathan, a web developer at Hounder.', 'textarea' ),
			'nd_home_short'     => $f( '"The short version"', 'I got into software by automating things I didn’t want to do by hand. Now I build sites for counties, airports, and utilities, where a broken page means someone can’t renew a permit.', 'textarea' ),
		) + $btn( 'nd_home_btn1', 'Button 1', 'See projects', '/projects/' )
			+ $btn( 'nd_home_btn2', 'Button 2', 'Read my story', '/about/' )
			+ $btn( 'nd_home_all', 'Projects "view all" link', 'View all →', '/projects/' )
			+ $btn( 'nd_home_more', '"The short version" link', 'The longer version →', '/about/' ) ),
		'nd_about'    => array( 'About page', array(
			'nd_about_title'     => $f( 'Headline', 'Hi, I’m Nathan.' ),
			'nd_about_highlight' => $f( 'Headline — highlighted line', 'Let’s go a little deeper.' ),
			'nd_about_lede'      => $f( 'Intro', 'I build websites and the systems behind them. Here’s how I got here.', 'textarea' ),
		) + $stops( array(
			array( '0 m · surface', 'Where it started', '[Where you grew up and what you were into as a kid — one or two sentences.] I fell into software through an obsession with how things work.' ),
			array( '30 m · curious', 'Getting hooked', 'It started with small scripts to get out of tedious tasks. Slowly that grew into a real interest in how whole systems fit together. [The moment or project that got you hooked.]' ),
			array( '200 m · building', 'Building with friends', 'Side projects taught me to ship with other people: a blackjack trainer with Joon Rho, backed by 682 tests, and a Pokémon guessing game with Eric Morales.' ),
			array( '1,000 m · today', 'Sites people depend on', 'Since April 2026 I’ve been a web developer at Hounder, building Drupal, WordPress, and Laravel sites for counties, airports, and utilities. Most of what I’m learning is the part tutorials skip: structuring a build so a non-technical editor can maintain it for years.' ),
		) ) + array(
			'nd_now'             => $f( 'At a glance — Now', 'Web developer, Hounder' ),
			'nd_stack'           => $f( 'At a glance — Stack', 'PHP, Drupal, WordPress, Laravel, React' ),
			'nd_location'        => $f( 'At a glance — Based in', '[CITY, STATE]' ),
			'nd_hobbies'         => $f( 'At a glance — Off the clock', '[HOBBIES]' ),
			'nd_p1_title'        => $f( 'How I work 1 — heading', 'Config in code' ),
			'nd_p1_text'         => $f( 'How I work 1 — text', 'Every environment rebuilds the same way, and changes get reviewed before production.', 'textarea' ),
			'nd_p2_title'        => $f( 'How I work 2 — heading', 'Build for the editor' ),
			'nd_p2_text'         => $f( 'How I work 2 — text', 'Structured fields hold up for years after handoff. Freeform pages decay.', 'textarea' ),
			'nd_p3_title'        => $f( 'How I work 3 — heading', 'Accessibility first' ),
			'nd_p3_text'         => $f( 'How I work 3 — text', 'Audit before launch, not after. Cheaper, and nobody gets left out.', 'textarea' ),
			'nd_p4_title'        => $f( 'How I work 4 — heading', 'Real browsers' ),
			'nd_p4_text'         => $f( 'How I work 4 — text', 'Support what visitors actually run, including older and locked-down machines.', 'textarea' ),
			'nd_about_cta_title' => $f( 'Closing — heading', 'Come say hello.' ),
			'nd_about_cta_text'  => $f( 'Closing — text', 'Always happy to talk about interesting problems.' ),
		) + $btn( 'nd_about_email', 'Email button', 'Email me' )
			+ $btn( 'nd_about_resume', 'Résumé button', 'Download résumé', get_theme_file_uri( 'assets/resume.pdf' ) )
			+ $btn( 'nd_about_linkedin', 'LinkedIn button', 'LinkedIn ↗' ) ),
		'nd_projects' => array( 'Projects page', array(
			'nd_projects_title'     => $f( 'Headline', 'Things I’ve built.' ),
			'nd_projects_highlight' => $f( 'Headline — highlighted line', 'And what they taught me.' ),
			'nd_projects_lede'      => $f( 'Intro', 'Client sites, side projects, and a few games built with friends.', 'textarea' ),
			'nd_projects_cta_title' => $f( 'Closing — heading', 'Want the backstory?' ),
			'nd_projects_cta_text'  => $f( 'Closing — text', 'Posts go deeper on how these were built.' ),
		) + $btn( 'nd_projects_cta', 'Closing button', 'Read the posts', '/posts/' ) ),
		'nd_posts'    => array( 'Posts page', array(
			'nd_posts_title'     => $f( 'Headline', 'Messages from' ),
			'nd_posts_highlight' => $f( 'Headline — highlighted line', 'down here.' ),
			'nd_posts_lede'      => $f( 'Intro', 'Project updates, release notes, and things I’m learning along the way.', 'textarea' ),
		) ),
		'nd_404'      => array( '404 page', array(
			'nd_404_title' => $f( 'Headline', 'Lost at sea.' ),
			'nd_404_lede'  => $f( 'Intro', 'This page drifted off somewhere, or never existed. Let’s get you back to the surface.', 'textarea' ),
		) + $btn( 'nd_404_btn1', 'Button 1', 'Back to the surface', '/' )
			+ $btn( 'nd_404_btn2', 'Button 2', 'See projects', '/projects/' )
			+ $btn( 'nd_404_btn3', 'Button 3', 'Read posts', '/posts/' ) ),
	);
	return $fields;
}

/*
 * The page that holds a section's copy. Projects (slug `projects`, its URL serves the project
 * archive) and 404 (slug `not-found`, private) are pages only so their copy is edited like the rest.
 */
function nd_copy_page_id( $section ) {
	$slug = array( 'nd_about' => 'about', 'nd_projects' => 'projects', 'nd_404' => 'not-found' );
	return match ( $section ) {
		'nd_home'  => (int) get_option( 'page_on_front' ),
		'nd_posts' => (int) get_option( 'page_for_posts' ),
		default    => isset( $slug[ $section ] ) ? (int) get_page_by_path( $slug[ $section ] )?->ID : 0,
	};
}

/** Copy section stored on this page, or ''. */
function nd_page_section( $post_id ) {
	foreach ( array_keys( nd_copy_fields() ) as $section ) {
		if ( 'nd_links' !== $section && $post_id && nd_copy_page_id( $section ) === (int) $post_id ) {
			return $section;
		}
	}
	return '';
}

/** Copy value with its default: links from the Customizer, the rest from its page. */
function nd_copy( $id ) {
	foreach ( nd_copy_fields() as $section => list( , $fields ) ) {
		if ( isset( $fields[ $id ] ) ) {
			if ( 'nd_links' === $section ) {
				return get_theme_mod( $id, $fields[ $id ]['default'] );
			}
			$page = nd_copy_page_id( $section );
			return $page && metadata_exists( 'post', $page, $id ) ? get_post_meta( $page, $id, true ) : $fields[ $id ]['default'];
		}
	}
	return '';
}

add_action( 'customize_register', function ( $wp_customize ) {
	list( $title, $fields ) = nd_copy_fields()['nd_links'];
	$wp_customize->add_section( 'nd_links', array( 'title' => $title, 'priority' => 20, 'description' => 'Page text and buttons are edited on each page (Pages → Edit); header and footer links in Appearance → Menus.' ) );
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array( 'default' => $field['default'], 'sanitize_callback' => nd_field_sanitizer( $field ) ) );
		$wp_customize->add_control( $id, array( 'label' => $field['label'], 'section' => 'nd_links', 'type' => $field['type'] ) );
	}
} );

/** Prints a copy button/link ($id from $btn in nd_copy_fields()); hidden when its label or link is empty. $url overrides the link field. */
function nd_link( $id, $class = '', $url = null ) {
	$label = nd_copy( "{$id}_label" );
	$url   = $url ?? nd_copy( "{$id}_url" );
	if ( '' !== $label && $url ) {
		printf( '<a%s href="%s">%s</a>', $class ? ' class="' . esc_attr( $class ) . '"' : '', esc_url( $url ), esc_html( $label ) );
	}
}

/* The theme's pages are built from their copy fields (plus projects/posts), so they get the classic screen with just the copy box. */
add_filter( 'use_block_editor_for_post', fn( $use, $post ) => 'page' === $post->post_type && nd_page_section( $post->ID ) ? false : $use, 10, 2 );

add_action( 'add_meta_boxes_page', function ( $post ) {
	$section = nd_page_section( $post->ID );
	if ( ! $section ) {
		return;
	}
	remove_post_type_support( 'page', 'editor' );
	add_meta_box( 'nd-page-copy', 'Page copy', 'nd_render_fields_box', 'page', 'normal', 'high', array( 'fields' => nd_fields_for( $post ) ) );
} );

/* Mark the theme's pages in the Pages list. */
add_filter( 'display_post_states', function ( $states, $post ) {
	$labels  = array( 'nd_projects' => 'Projects archive', 'nd_404' => '404 page' );
	$section = 'page' === $post->post_type ? nd_page_section( $post->ID ) : '';
	if ( isset( $labels[ $section ] ) ) {
		$states[ $section ] = $labels[ $section ];
	}
	return $states;
}, 10, 2 );

/* ── Template helpers ─────────────────────────────────── */

/** Which nav item is current. */
function nd_nav_current() {
	if ( is_post_type_archive( 'project' ) || is_tax( 'project_type' ) || is_singular( 'project' ) ) {
		return 'projects';
	}
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() ) {
		return 'posts';
	}
	if ( is_page( 'about' ) ) {
		return 'about';
	}
	return '';
}

/*
 * Links for a menu location (Appearance → Menus) as array( label, url ) pairs, or $fallback
 * when no menu is assigned. Printed as plain <a>s so the nav keeps its flat markup.
 */
function nd_menu_links( $location, $fallback ) {
	$menus = get_nav_menu_locations();
	$items = empty( $menus[ $location ] ) ? false : wp_get_nav_menu_items( $menus[ $location ] );
	if ( false === $items ) {
		return array_values( array_filter( $fallback, fn( $l ) => $l[1] ) );
	}
	return array_map( fn( $i ) => array( $i->title, $i->url ), array_filter( $items, fn( $i ) => ! $i->menu_item_parent ) );
}

/** The default header links (also what the seed puts in the Header menu). */
function nd_default_nav() {
	return array(
		'projects' => array( 'Projects', get_post_type_archive_link( 'project' ) ),
		'posts'    => array( 'Posts', get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ),
		'about'    => array( 'About', home_url( '/about/' ) ),
	);
}

/** Inline-safe <img> for a diver pose from assets/divers. */
function nd_diver( $pose, $alt, $anim = 'nd-anim-bob' ) {
	printf(
		'<figure class="nd-hero__art %3$s"><img src="%1$s" alt="%2$s" width="420" height="420"></figure>',
		esc_url( get_theme_file_uri( "assets/divers/diver-{$pose}.svg" ) ),
		esc_attr( $alt ),
		esc_attr( $anim )
	);
}

/** Decorative sea life. $kind: sub | fish | jelly | school | angler. $style: inline position. */
function nd_decor( $kind, $style ) {
	$svgs = array(
		'sub'    => array( 'drift', '<svg viewBox="0 0 130 70" width="130" height="70" fill="none"><path d="M58 22V10h14v12" fill="#E3B04B"/><path d="M70 10h10" stroke="#E3B04B" stroke-width="3" stroke-linecap="round"/><rect x="14" y="22" width="96" height="36" rx="18" fill="#E3B04B"/><circle cx="44" cy="40" r="7" fill="#0A3446" stroke="#F7E3A8" stroke-width="2"/><circle cx="66" cy="40" r="7" fill="#0A3446" stroke="#F7E3A8" stroke-width="2"/><circle cx="88" cy="40" r="7" fill="#0A3446" stroke="#F7E3A8" stroke-width="2"/><path d="M14 40H4M4 30v20" stroke="#C99430" stroke-width="3" stroke-linecap="round"/><circle cx="120" cy="14" r="3" stroke="#B4D9DD" stroke-width="1.5"/><circle cx="126" cy="4" r="2" stroke="#B4D9DD" stroke-width="1.5"/></svg>' ),
		'fish'   => array( 'swim', '<svg viewBox="0 0 34 20" width="34" height="20"><path d="M4 10q8-9 20-6l8-4v20l-8-4Q12 19 4 10z" fill="#F08A4B"/><circle cx="10" cy="9" r="1.6" fill="#04222E"/></svg>' ),
		'jelly'  => array( 'jelly', '<svg viewBox="0 0 52 70" width="52" height="70" fill="none"><path d="M4 26a22 22 0 0 1 44 0q-6 4-11 0-5 4-11 0-5 4-11 0-5 4-11 0z" fill="#C9A6FF" fill-opacity=".55" stroke="#E2D2FF" stroke-width="1.5"/><path d="M12 30q-4 14 2 26M22 30q4 16-2 34M32 30q-4 14 2 28M42 30q4 12-2 22" stroke="#E2D2FF" stroke-opacity=".7" stroke-width="1.5" stroke-linecap="round"/></svg>' ),
		'school' => array( 'swim', '<svg viewBox="0 0 80 50" width="80" height="50"><path d="M4 12q7-7 16-4l6-4v16l-6-4Q11 19 4 12z" fill="#6FEBD7"/><path d="M30 28q7-7 16-4l6-4v16l-6-4q-9 3-16-4z" fill="#6FEBD7" fill-opacity=".8"/><path d="M10 40q7-7 16-4l6-4v16l-6-4q-9 3-16-4z" fill="#6FEBD7" fill-opacity=".6"/></svg>' ),
		'angler' => array( 'swim-slow', '<svg viewBox="0 0 120 90" width="120" height="90" fill="none"><circle class="nd-lure" cx="96" cy="14" r="16" fill="#FFF3B0" fill-opacity=".18"/><path d="M62 40q14-30 34-26" stroke="#3A4F58" stroke-width="2.5" stroke-linecap="round"/><circle class="nd-lure" cx="96" cy="14" r="6" fill="#FFF3B0"/><path d="M14 52q14-26 46-22 26 4 38 26-12 22-38 24-32 2-46-28z" fill="#1B2E36" stroke="#2E4650" stroke-width="2"/><path d="M14 52l-12-14v28z" fill="#1B2E36"/><circle cx="78" cy="48" r="5" fill="#E6F6F7"/><circle cx="79" cy="48" r="2.5" fill="#010B11"/><path d="M66 64l4-6 4 6 4-6 4 6 4-6 4 6" stroke="#E6F6F7" stroke-width="1.5" stroke-linejoin="round"/></svg>' ),
	);
	if ( isset( $svgs[ $kind ] ) ) {
		// SVG markup is a fixed theme constant, not user input.
		printf( '<div aria-hidden="true" class="nd-decor nd-decor--%s" style="%s">%s</div>', esc_attr( $svgs[ $kind ][0] ), esc_attr( $style ), $svgs[ $kind ][1] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/** "5 min read" */
function nd_reading_time( $post = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) );
	return max( 1, (int) ceil( $words / 200 ) ) . ' min read';
}

/** Post kind label from its first category ("Notes", "Project update", "Release"). */
function nd_post_kind( $post = null ) {
	$cats = get_the_category( $post ? get_post( $post )->ID : 0 );
	return $cats ? $cats[0]->name : 'Notes';
}

/** Project card data in one place. */
function nd_project( $id = 0 ) {
	$id    = $id ?: get_the_ID();
	$data  = array( 'title' => get_the_title( $id ) );
	foreach ( array_keys( nd_project_fields() ) as $key ) {
		$data[ substr( $key, 3 ) ] = get_post_meta( $id, $key, true );
	}
	$terms            = get_the_terms( $id, 'project_type' );
	$data['category'] = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
	return $data;
}

/** Image slot: featured image if set, otherwise a labelled placeholder. */
function nd_slot( $id, $label, $class = '' ) {
	echo '<div class="nd-slot ' . esc_attr( $class ) . '">';
	if ( has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, 'large' );
	} else {
		echo esc_html( '[' . $label . ']' );
	}
	echo '</div>';
}
