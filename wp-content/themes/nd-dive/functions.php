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
	// The block editor scopes these to the canvas: site tokens + dive stops from style.css, prose from editor-style.css.
	add_editor_style( array( ND_FONTS_URL, 'style.css', 'editor-style.css' ) );
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
	foreach ( array( 'project' => nd_project_fields(), 'post' => nd_post_fields() ) as $type => $fields ) {
		foreach ( $fields as $key => $field ) {
			register_post_meta( $type, $key, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => nd_field_sanitizer( $field ) ) );
		}
	}

	register_block_pattern( 'nd-dive/stop', array(
		'title'       => 'Dive stop (About story)',
		'description' => 'One stop on the About page depth line.',
		'categories'  => array( 'text' ),
		'content'     => nd_stop_markup( '0 m · surface', 'Heading', 'Story paragraph.' ),
	) );
} );

/** Markup for one About-page story stop (used by the block pattern and the seed). */
function nd_stop_markup( $depth, $heading, $text ) {
	return '<!-- wp:group {"className":"nd-stop","layout":{"type":"default"}} --><div class="wp-block-group nd-stop">'
		. '<!-- wp:paragraph {"className":"nd-stop__depth"} --><p class="nd-stop__depth">' . esc_html( $depth ) . '</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->'
		. '</div><!-- /wp:group -->';
}

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
		$value = get_post_meta( $post->ID, $key, true );
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

add_action( 'save_post', function ( $post_id, $post ) {
	if ( ! isset( $_POST['nd_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nd_fields_nonce'] ), 'nd_fields' ) ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = 'project' === $post->post_type ? nd_project_fields() : nd_post_fields();
	foreach ( array_keys( $fields ) as $key ) {
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
 * ── Customizer: links + the fixed copy on each page ──
 * section id => array( title, preview URL, settings: id => array( label, default, type = text|textarea|url|email ) ).
 * Templates read these with nd_copy( 'id' ).
 */
function nd_copy_fields() {
	return array(
		'nd_links'    => array( 'Links & details', '', array(
			'nd_github'   => array( 'GitHub URL', 'https://github.com/ndulkis', 'url' ),
			'nd_linkedin' => array( 'LinkedIn URL', '', 'url' ),
			'nd_email'    => array( 'Email address', '', 'email' ),
		) ),
		'nd_home'     => array( 'Home page', home_url( '/' ), array(
			'nd_home_title'     => array( 'Headline', 'I build websites.' ),
			'nd_home_highlight' => array( 'Headline — highlighted line', 'And small tools.' ),
			'nd_home_lede'      => array( 'Intro', 'I’m Nathan, a web developer at Hounder.', 'textarea' ),
			'nd_home_short'     => array( '"The short version"', 'I got into software by automating things I didn’t want to do by hand. Now I build sites for counties, airports, and utilities, where a broken page means someone can’t renew a permit.', 'textarea' ),
		) ),
		'nd_about'    => array( 'About page', home_url( '/about/' ), array(
			'nd_about_title'     => array( 'Headline', 'Hi, I’m Nathan.' ),
			'nd_about_highlight' => array( 'Headline — highlighted line', 'Let’s go a little deeper.' ),
			'nd_about_lede'      => array( 'Intro', 'I build websites and the systems behind them. Here’s how I got here.', 'textarea' ),
			'nd_now'             => array( 'At a glance — Now', 'Web developer, Hounder' ),
			'nd_stack'           => array( 'At a glance — Stack', 'PHP, Drupal, WordPress, Laravel, React' ),
			'nd_location'        => array( 'At a glance — Based in', '[CITY, STATE]' ),
			'nd_hobbies'         => array( 'At a glance — Off the clock', '[HOBBIES]' ),
			'nd_p1_title'        => array( 'How I work 1 — heading', 'Config in code' ),
			'nd_p1_text'         => array( 'How I work 1 — text', 'Every environment rebuilds the same way, and changes get reviewed before production.', 'textarea' ),
			'nd_p2_title'        => array( 'How I work 2 — heading', 'Build for the editor' ),
			'nd_p2_text'         => array( 'How I work 2 — text', 'Structured fields hold up for years after handoff. Freeform pages decay.', 'textarea' ),
			'nd_p3_title'        => array( 'How I work 3 — heading', 'Accessibility first' ),
			'nd_p3_text'         => array( 'How I work 3 — text', 'Audit before launch, not after. Cheaper, and nobody gets left out.', 'textarea' ),
			'nd_p4_title'        => array( 'How I work 4 — heading', 'Real browsers' ),
			'nd_p4_text'         => array( 'How I work 4 — text', 'Support what visitors actually run, including older and locked-down machines.', 'textarea' ),
			'nd_about_cta_title' => array( 'Closing — heading', 'Come say hello.' ),
			'nd_about_cta_text'  => array( 'Closing — text', 'Always happy to talk about interesting problems.' ),
		) ),
		'nd_projects' => array( 'Projects page', get_post_type_archive_link( 'project' ), array(
			'nd_projects_title'     => array( 'Headline', 'Things I’ve built.' ),
			'nd_projects_highlight' => array( 'Headline — highlighted line', 'And what they taught me.' ),
			'nd_projects_lede'      => array( 'Intro', 'Client sites, side projects, and a few games built with friends.', 'textarea' ),
			'nd_projects_cta_title' => array( 'Closing — heading', 'Want the backstory?' ),
			'nd_projects_cta_text'  => array( 'Closing — text', 'Posts go deeper on how these were built.' ),
		) ),
		'nd_posts'    => array( 'Posts page', get_permalink( get_option( 'page_for_posts' ) ), array(
			'nd_posts_title'     => array( 'Headline', 'Messages from' ),
			'nd_posts_highlight' => array( 'Headline — highlighted line', 'down here.' ),
			'nd_posts_lede'      => array( 'Intro', 'Project updates, release notes, and things I’m learning along the way.', 'textarea' ),
		) ),
		'nd_404'      => array( '404 page', home_url( '/this-page-does-not-exist/' ), array(
			'nd_404_title' => array( 'Headline', 'Lost at sea.' ),
			'nd_404_lede'  => array( 'Intro', 'This page drifted off somewhere, or never existed. Let’s get you back to the surface.', 'textarea' ),
		) ),
	);
}

/** Customizer value with its default. */
function nd_copy( $id ) {
	foreach ( nd_copy_fields() as list( , , $settings ) ) {
		if ( isset( $settings[ $id ] ) ) {
			return get_theme_mod( $id, $settings[ $id ][1] );
		}
	}
	return '';
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_panel( 'nd_site', array( 'title' => 'Site content', 'priority' => 20 ) );
	$sanitize = array( 'url' => 'esc_url_raw', 'email' => 'sanitize_email', 'textarea' => 'sanitize_textarea_field', 'text' => 'sanitize_text_field' );
	foreach ( nd_copy_fields() as $section => list( $title, $preview, $settings ) ) {
		$wp_customize->add_section( $section, array( 'title' => $title, 'panel' => 'nd_site' ) );
		foreach ( $settings as $id => $s ) {
			$type = $s[2] ?? 'text';
			$wp_customize->add_setting( $id, array( 'default' => $s[1], 'sanitize_callback' => $sanitize[ $type ] ) );
			$wp_customize->add_control( $id, array( 'label' => $s[0], 'section' => $section, 'type' => $type ) );
		}
	}
} );

/* Jump the Customizer preview to the page whose section is opened. */
add_action( 'customize_controls_print_footer_scripts', function () {
	$urls = array_filter( wp_list_pluck( nd_copy_fields(), 1 ) );
	?>
	<script>
	wp.customize.bind( 'ready', function () {
		Object.entries( <?php echo wp_json_encode( $urls ); ?> ).forEach( function ( [ id, url ] ) {
			wp.customize.section( id, function ( s ) {
				s.expanded.bind( function ( open ) { if ( open ) { wp.customize.previewer.previewUrl.set( url ); } } );
			} );
		} );
	} );
	</script>
	<?php
} );

/* On the Home, About and Posts pages, point to where their fixed copy is edited. */
add_action( 'add_meta_boxes_page', function ( $post ) {
	$section = match ( (int) $post->ID ) {
		(int) get_option( 'page_on_front' )  => 'nd_home',
		(int) get_option( 'page_for_posts' ) => 'nd_posts',
		default                              => 'about' === $post->post_name ? 'nd_about' : '',
	};
	if ( ! $section ) {
		return;
	}
	add_meta_box( 'nd-page-copy', 'Page headline & copy', function () use ( $section, $post ) {
		$url = add_query_arg( array( 'autofocus[section]' => $section, 'url' => rawurlencode( get_permalink( $post ) ) ), admin_url( 'customize.php' ) );
		echo '<p>' . ( 'nd_about' === $section ? 'This page’s content is the dive-log story (add stops with the “Dive stop” pattern).' : 'This page’s body isn’t shown; the layout is built from projects and posts.' ) . ' The headline, intro and other fixed text are edited in the Customizer.</p>';
		printf( '<p><a class="button" href="%s">Edit page copy</a></p>', esc_url( $url ) );
	}, 'page', 'side', 'high' );
} );

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
