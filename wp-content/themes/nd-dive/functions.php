<?php
/**
 * ND Dive theme setup.
 *
 * Content model:
 * - Pages: Home (front page), Posts (posts page), About.
 * - Projects: custom post type `project` + `project_type` taxonomy, story fields in post meta.
 * - Posts: the category (Notes / Project update / Release) is the post kind and picks its layout.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	// The post body editor (TinyMCE) reads like the site: tokens from style.css, prose from editor-style.css.
	$css = static fn( $file ) => get_theme_file_uri( $file ) . '?ver=' . nd_asset_ver( $file );
	add_editor_style( array( $css( 'style.css' ), $css( 'editor-style.css' ) ) );
	register_nav_menus( array( 'header' => 'Header navigation', 'footer' => 'Footer links' ) );
} );

/** Version for a theme asset: its last-modified time, so browsers pick up every edit instead of a cached copy. */
function nd_asset_ver( $file ) {
	return (string) filemtime( get_theme_file_path( $file ) );
}


/* Galleries are laid out by .nd-prose .gallery in style.css, not WordPress's inline float styles. */
add_filter( 'use_default_gallery_style', '__return_false' );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'nd-dive', get_stylesheet_uri(), array(), nd_asset_ver( 'style.css' ) );
} );

/* ── Projects ─────────────────────────────────────────── */

/*
 * Field definitions: key => array( label, type = text|textarea|url|email|select|rows, help, options, default, layouts ).
 * `rows` fields are lists the editor can add to, remove from and reorder: `columns` defines one row
 * (column => field definition), `item` names one row ("+ Add fact"), `default` holds rows for new posts.
 * `layouts` (post fields only) lists the post layouts (category slugs) the field belongs to.
 * `core` (post fields only) maps the field to a WP_Post property that core saves itself (the excerpt).
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
		'nd_tags'       => array( 'label' => 'Tech tags', 'type' => 'rows', 'item' => 'tag', 'help' => 'One per row, e.g. wordpress, php, acf. Shown as "wordpress · php · acf".', 'columns' => array( 'tag' => array( 'label' => 'Tag' ) ) ),
		'nd_link_url'   => array( 'label' => 'Link URL', 'type' => 'url', 'help' => 'Leave empty to hide the link.' ),
		'nd_link_label' => array( 'label' => 'Link label', 'help' => 'e.g. "Visit site" or "View on GitHub". Defaults to "Visit".' ),
	);
}

/*
 * Post layouts: category slug => template file. A post uses the first layout whose category it has;
 * anything else (Notes) is the article layout, single.php.
 */
function nd_post_layouts() {
	return array(
		'release'        => 'templates/post-release.php',
		'project-update' => 'templates/post-case-study.php',
		'blog'           => 'templates/post-blog.php',
	);
}

/** Layout (category slug) of a post, or '' for the article layout. */
function nd_post_layout( $post ) {
	foreach ( array_keys( nd_post_layouts() ) as $slug ) {
		if ( has_category( $slug, $post ) ) {
			return $slug;
		}
	}
	return '';
}

add_filter( 'single_template', function ( $template ) {
	// The category decides, so a stale per-post template choice can't override it.
	return is_singular( 'post' ) ? locate_template( nd_post_layouts()[ nd_post_layout( get_queried_object() ) ] ?? 'single.php' ) : $template;
} );

/** Post details: the summary, plus the case study (Project update), release (Release) and blog (Blog) fields. */
function nd_post_fields() {
	$case = array( 'project-update' );
	$rel  = array( 'release' );
	$blog = array( 'blog' );
	$both = array( 'project-update', 'release' );
	$text = array( 'label' => 'Text', 'type' => 'textarea' );
	return array(
		'excerpt'           => array( 'label' => 'Summary', 'type' => 'textarea', 'core' => 'post_excerpt', 'help' => 'One or two sentences. Shown on the posts list, and as the intro on case studies and releases.' ),
		'nd_facts'          => array( 'label' => 'Project facts', 'type' => 'rows', 'item' => 'fact', 'layouts' => $case, 'help' => 'The card beside the case study. Facts with no value are hidden.',
			'columns' => array( 'label' => array( 'label' => 'Label' ), 'value' => array( 'label' => 'Value' ) ),
			'default' => array_map( fn( $l ) => array( 'label' => $l, 'value' => '' ), array( 'Client', 'Built at', 'My role', 'Timeline', 'Stack' ) ) ),
		'nd_sections'       => array( 'label' => 'Sections', 'type' => 'rows', 'item' => 'section', 'numbered' => true, 'layouts' => $case, 'help' => 'Numbered automatically: "01 · the problem".',
			'columns' => array( 'heading' => array( 'label' => 'Heading' ), 'text' => $text ),
			'default' => array_map( fn( $h ) => array( 'heading' => $h, 'text' => '' ), array( 'the problem', 'the approach', 'the result', 'what I learned' ) ) ),
		'nd_version'        => array( 'label' => 'Version', 'layouts' => $rel, 'help' => 'Without the "v", e.g. 0.1.2' ),
		'nd_changes'        => array( 'label' => 'Changes', 'type' => 'rows', 'item' => 'change', 'layouts' => $rel, 'help' => 'Grouped under New, Improved and Fixed on the post.',
			'columns' => array( 'type' => array( 'label' => 'Type', 'type' => 'select', 'options' => nd_change_types() ), 'text' => array( 'label' => 'Change' ) ),
			'default' => array_map( fn( $t ) => array( 'type' => $t, 'text' => '' ), array_keys( nd_change_types() ) ) ),
		'nd_download_title' => array( 'label' => 'Download heading', 'layouts' => $rel, 'help' => 'Empty = "Get this release". The version is added after it.' ),
		'nd_download_meta'  => array( 'label' => 'Download details', 'layouts' => $rel, 'help' => 'e.g. "4.1 MB · Free · Apple Silicon · macOS 11+"' ),
		'nd_link_url'       => array( 'label' => 'Button link', 'type' => 'url', 'layouts' => $both, 'help' => 'Empty = no button.' ),
		'nd_link_label'     => array( 'label' => 'Button label', 'layouts' => $both, 'help' => 'Empty = "Visit the site ↗" (project update) or "Download ↗" (release).' ),
		'nd_blog_facts'     => array( 'label' => 'Log details', 'type' => 'rows', 'item' => 'detail', 'layouts' => $blog, 'help' => 'The strip under the cover image. Details with no value are hidden.',
			'columns' => array( 'label' => array( 'label' => 'Label' ), 'value' => array( 'label' => 'Value' ) ),
			'default' => array_map( fn( $l ) => array( 'label' => $l, 'value' => '' ), array( 'logged', 'from', 'on repeat' ) ) ),
		'nd_reply_title'    => array( 'label' => 'Sign-off', 'layouts' => $blog, 'help' => 'Empty = "Thanks for reading."' ),
		'nd_reply_text'     => array( 'label' => 'Sign-off line', 'layouts' => $blog, 'help' => 'Empty = "Got a thought on this one? I’d like to hear it."' ),
		'nd_reply_label'    => array( 'label' => 'Reply button label', 'layouts' => $blog, 'help' => 'Empty = "Reply by email". Goes to the email in Customizer → Links & details; hidden until that is set.' ),
	);
}

/** A post's saved rows for a `rows` field: always a list of arrays (empty when never saved). */
function nd_rows( $key, $post_id = 0 ) {
	return array_values( array_filter( (array) get_post_meta( $post_id ?: get_the_ID(), $key, true ), 'is_array' ) );
}

/** Release change groups, in display order. */
function nd_change_types() {
	return array( 'new' => 'New', 'improved' => 'Improved', 'fixed' => 'Fixed' );
}

/*
 * Starter bodies ("Insert outline" in Post details; also used by the seed), keyed by layout.
 * The layouts listed here are written in the body (Notes = '', Blog); the others are built from their rows.
 */
function nd_post_outlines() {
	return array(
		''     => '<p>[Opening paragraph. Set up the story in two or three sentences.]</p><p>[Body paragraph.]</p><h2>[Section heading]</h2><p>[Body paragraph.]</p>'
			. '<blockquote><p>[A pull quote — the one line you want people to remember.]</p></blockquote>'
			. '<h2>[Section heading]</h2><ul><li>[Takeaway one]</li><li>[Takeaway two]</li><li>[Takeaway three]</li></ul><p>[Closing paragraph.]</p>',
		'blog' => '<p>[Opening paragraph. Start in the moment — where you were and what was going on — before you get to the point.]</p><p>[Body paragraph.]</p>'
			. '<h2>[Section heading]</h2><p>[Body paragraph.]</p><p>[Photos: Add Media → Create gallery, two columns.]</p><p>[Body paragraph.]</p>'
			. '<aside class="nd-note"><p>[A tangent that’s worth keeping but would break the flow of the main story.]</p></aside>'
			. '<p class="nd-pull">[The one line you want people to remember.]</p>'
			. '<h2>[Section heading]</h2><p>[Closing paragraph.]</p>',
	);
}

/** Sanitizer for a field definition (also runs on update_post_meta via register_post_meta). */
function nd_field_sanitizer( $field ) {
	return match ( $field['type'] ?? 'text' ) {
		'textarea' => static fn( $v ) => sanitize_textarea_field( $v ),
		'url'      => static fn( $v ) => esc_url_raw( $v ),
		'email'    => static fn( $v ) => sanitize_email( $v ),
		'select'   => static fn( $v ) => is_string( $v ) && array_key_exists( $v, $field['options'] ) ? $v : array_key_first( $field['options'] ),
		// Rows: each column sanitized by its own type; rows with no text (selects aside) are dropped.
		'rows'     => static function ( $rows ) use ( $field ) {
			// A one-column list also takes "a · b · c" or "a, b, c" (old values, pasted text): one row each.
			if ( is_string( $rows ) && 1 === count( $field['columns'] ) ) {
				$rows = array_map( fn( $v ) => array( array_key_first( $field['columns'] ) => trim( $v ) ), preg_split( '/\s*[·,]\s*/u', $rows ) );
			}
			$out = array();
			foreach ( is_array( $rows ) ? $rows : array() as $row ) {
				if ( ! is_array( $row ) ) {
					continue; // The empty marker that lets "remove every row" save.
				}
				$clean = array();
				$text  = '';
				foreach ( $field['columns'] as $col => $def ) {
					$clean[ $col ] = nd_field_sanitizer( $def )( $row[ $col ] ?? '' );
					$text         .= 'select' === ( $def['type'] ?? '' ) ? '' : $clean[ $col ];
				}
				if ( '' !== $text ) {
					$out[] = $clean;
				}
			}
			return $out;
		},
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
			if ( empty( $field['core'] ) ) {
				$rows = 'rows' === ( $field['type'] ?? '' );
				register_post_meta( $type, $key, array( 'type' => $rows ? 'array' : 'string', 'single' => true, 'show_in_rest' => ! $rows, 'sanitize_callback' => nd_field_sanitizer( $field ) ) );
			}
		}
	}

} );

/* Simple meta boxes so the fields are editable without a plugin. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nd-project', 'Project story', 'nd_render_fields_box', 'project', 'normal', 'high', array( 'fields' => nd_project_fields() ) );
	// Posts: one "Post details" box (summary included) between the title and the body ("nd_top", printed below).
	remove_meta_box( 'postexcerpt', 'post', 'normal' );
	add_meta_box( 'nd-post', 'Post details', 'nd_render_fields_box', 'post', 'nd_top', 'high', array( 'fields' => nd_post_fields() ) );
} );

add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'post' === $post->post_type ) {
		do_meta_boxes( get_current_screen(), 'nd_top', $post );
		// Label for the body editor below; post-fields.js shows the hint for the current layout.
		echo '<div class="nd-body-label"><h2>Body</h2>'
			. '<p class="description" data-for="body">The post itself. Use <strong>Insert outline</strong> above for a starting structure.</p>'
			. '<p class="description" data-for="layout" hidden>Optional. Anything here (a screenshot, extra notes) shows after the sections or changes.</p></div>';
	}
} );

/* Keep Post details under the title: drop it from any other column in a user's saved (dragged) box order. */
add_filter( 'get_user_option_meta-box-order_post', function ( $order ) {
	foreach ( is_array( $order ) ? $order : array() as $context => $ids ) {
		if ( 'nd_top' !== $context ) {
			$order[ $context ] = implode( ',', array_diff( explode( ',', $ids ), array( 'nd-post' ) ) );
		}
	}
	return $order;
} );

/* Never let Screen Options hide Post details. */
add_filter( 'hidden_meta_boxes', fn( $hidden, $screen ) => 'post' === $screen->id ? array_diff( $hidden, array( 'nd-post' ) ) : $hidden, 10, 2 );

/* The featured image is a post's cover (posts list + case study hero). */
add_filter( 'post_type_labels_post', function ( $labels ) {
	$labels->featured_image        = 'Cover image';
	$labels->set_featured_image    = 'Set cover image';
	$labels->remove_featured_image = 'Remove cover image';
	$labels->use_featured_image    = 'Use as cover image';
	return $labels;
} );

/*
 * Renders typed fields, label above input. Post fields that don't belong to the post's layout are
 * hidden and disabled (so they aren't submitted); assets/post-fields.js re-toggles them when the
 * category is changed and runs the row buttons.
 */
function nd_render_fields_box( $post, $box ) {
	wp_nonce_field( 'nd_fields', 'nd_fields_nonce' );
	$layout = 'post' === $post->post_type ? nd_post_layout( $post ) : '';
	$any    = false;
	echo '<div class="nd-fields">';
	foreach ( $box['args']['fields'] as $key => $field ) {
		if ( ! empty( $field['core'] ) ) {
			$value = $post->{$field['core']};
		} else {
			$value = metadata_exists( 'post', $post->ID, $key ) ? get_post_meta( $post->ID, $key, true ) : ( $field['default'] ?? '' );
		}
		$off  = isset( $field['layouts'] ) && ! in_array( $layout, $field['layouts'], true );
		$any  = $any || ( isset( $field['layouts'] ) && ! $off );
		$rows = 'rows' === ( $field['type'] ?? '' );
		printf(
			'<div class="nd-field"%s%s>%s%s%s</div>',
			isset( $field['layouts'] ) ? ' data-layouts="' . esc_attr( implode( ' ', $field['layouts'] ) ) . '"' : '',
			$off ? ' hidden' : '',
			$rows ? '<span class="nd-field__label">' . esc_html( $field['label'] ) . '</span>' : '<label class="nd-field__label" for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>',
			empty( $field['help'] ) ? '' : '<p class="description">' . esc_html( $field['help'] ) . '</p>',
			nd_field_input( $key, $field, $value, $off, $key ) // Built from escaped parts.
		); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
	if ( 'post' === $post->post_type ) {
		printf(
			'<div class="nd-fields-hint"%s><p>The category picks the layout. <strong>Project update</strong>, <strong>Release</strong> and <strong>Blog</strong> add their own fields here; Notes are written in the body below.</p></div>'
			. '<p class="nd-outline-row"%s><button type="button" class="button nd-outline">Insert outline</button> <span class="description">Adds this layout’s starter headings to the body, at the cursor.</span></p>',
			$any ? ' hidden' : '',
			isset( nd_post_outlines()[ $layout ] ) ? '' : ' hidden'
		);
	}
}

/** One input (escaped HTML). Rows render their own list of inputs named $name[row][column]. */
function nd_field_input( $name, $field, $value, $disabled, $id = '' ) {
	$attrs = sprintf( '%sname="%s" class="widefat"%s', $id ? 'id="' . esc_attr( $id ) . '" ' : '', esc_attr( $name ), $disabled ? ' disabled' : '' );
	switch ( $field['type'] ?? 'text' ) {
		case 'textarea':
			return sprintf( '<textarea %s rows="3">%s</textarea>', $attrs, esc_textarea( $value ) );
		case 'select':
			$out = sprintf( '<select %s>', $attrs );
			foreach ( $field['options'] as $opt => $opt_label ) {
				$out .= sprintf( '<option value="%s"%s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $opt_label ) );
			}
			return $out . '</select>';
		case 'rows':
			$value = is_string( $value ) && '' !== $value ? nd_field_sanitizer( $field )( $value ) : $value; // Old "a · b" text.
			$row   = static function ( $i, $values ) use ( $name, $field, $disabled ) {
				$cols = '';
				foreach ( $field['columns'] as $col => $def ) {
					$cols .= sprintf( '<label class="nd-row__col nd-row__col--%s"><span>%s</span>%s</label>', esc_attr( $def['type'] ?? 'text' ), esc_html( $def['label'] ), nd_field_input( "{$name}[{$i}][{$col}]", $def, $values[ $col ] ?? '', $disabled ) );
				}
				return '<li class="nd-row"><div class="nd-row__cols">' . $cols . '</div><div class="nd-row__tools">'
					. '<button type="button" class="button-link nd-up" aria-label="Move up" title="Move up">↑</button>'
					. '<button type="button" class="button-link nd-down" aria-label="Move down" title="Move down">↓</button>'
					. '<button type="button" class="button-link nd-remove" aria-label="Remove" title="Remove">×</button></div></li>';
			};
			$items = '';
			foreach ( is_array( $value ) ? array_values( $value ) : array() as $i => $values ) {
				$items .= $row( $i, $values );
			}
			return sprintf(
				'<div class="nd-rows%s"><input type="hidden" name="%s[_]" value=""%s><ol class="nd-rows__list">%s</ol><template>%s</template><button type="button" class="button nd-rows__add">+ Add %s</button></div>',
				empty( $field['numbered'] ) ? '' : ' nd-rows--numbered',
				esc_attr( $name ),
				$disabled ? ' disabled' : '',
				$items,
				$row( '__i__', array() ),
				esc_html( $field['item'] )
			);
		default:
			$type = in_array( $field['type'] ?? '', array( 'url', 'email' ), true ) ? $field['type'] : 'text';
			return sprintf( '<input type="%s" %s value="%s">', $type, $attrs, esc_attr( $value ) );
	}
}

/** The editable fields a post has: project story, post details, or its page copy. */
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
	foreach ( nd_fields_for( $post ) as $key => $field ) {
		if ( empty( $field['core'] ) && isset( $_POST[ $key ] ) ) {
			// update_post_meta() unslashes itself; register_post_meta sanitizes.
			update_post_meta( $post_id, $key, $_POST[ $key ] ); // phpcs:ignore WordPress.Security
		}
	}
}, 10, 2 );

/* One editing style everywhere: the classic screen, with each type's fields in a box (projects, pages, posts). */
add_filter( 'use_block_editor_for_post_type', '__return_false' );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_style( 'nd-admin', get_theme_file_uri( 'assets/admin.css' ), array(), nd_asset_ver( 'assets/admin.css' ) );
	wp_enqueue_script( 'nd-rows', get_theme_file_uri( 'assets/rows.js' ), array(), nd_asset_ver( 'assets/rows.js' ), true );
	if ( 'post' === get_current_screen()->post_type ) {
		wp_enqueue_script( 'nd-post-fields', get_theme_file_uri( 'assets/post-fields.js' ), array( 'media-upload' ), nd_asset_ver( 'assets/post-fields.js' ), true );
		// Layout category IDs in precedence order (same rule as nd_post_layout()), and the outlines of body-written layouts.
		$layouts = array();
		foreach ( array_keys( nd_post_layouts() ) as $slug ) {
			$layouts[] = array( $slug, (int) get_category_by_slug( $slug )?->term_id );
		}
		wp_add_inline_script( 'nd-post-fields', 'window.ndPost = ' . wp_json_encode( array( 'layouts' => $layouts, 'outlines' => nd_post_outlines() ) ) . ';', 'before' );
	}
} );

/* Projects archive: manual order (Page Attributes → Order), show all. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_post_type_archive( 'project' ) || $q->is_tax( 'project_type' ) ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		$q->set( 'posts_per_page', -1 );
	}
} );

/* Single project URLs redirect to that project's card on /projects/, and stay out of the XML sitemap. */
add_action( 'template_redirect', function () {
	if ( is_singular( 'project' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'project' ) . '#' . get_queried_object()->post_name, 301 );
		exit;
	}
} );
add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
	unset( $post_types['project'] );
	return $post_types;
} );

/*
 * Saving a project renumbers all projects 1, 2, 3… with this one at its Order: the others at or below it
 * move down one, so no two share a number. Order 0 (left empty) or past the end puts it last.
 */
add_action( 'save_post_project', function ( $post_id, $post ) {
	if ( in_array( $post->post_status, array( 'auto-draft', 'trash', 'inherit' ), true ) ) {
		return;
	}
	$ids = get_posts( array(
		'post_type'   => 'project',
		'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'exclude'     => array( $post_id ),
		'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'numberposts' => -1,
		'fields'      => 'ids',
	) );
	$pos = (int) $post->menu_order;
	array_splice( $ids, $pos < 1 || $pos > count( $ids ) ? count( $ids ) : $pos - 1, 0, array( $post_id ) );
	global $wpdb;
	foreach ( $ids as $i => $id ) {
		if ( (int) get_post_field( 'menu_order', $id ) !== $i + 1 ) {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $i + 1 ), array( 'ID' => $id ) ); // Direct, so it doesn't re-run this hook.
			clean_post_cache( $id );
		}
	}
}, 10, 2 );

/*
 * ── Fixed copy on each page ──
 * Page copy is post meta on its page, edited in Pages → Edit ("Page copy" box).
 * Site-wide links stay in the Customizer. Templates read both with nd_copy( 'id' ).
 * section => array( title, fields: id => array( label, default, type = text|textarea|url|email|rows ) ).
 * `rows` fields are described with the field definitions above nd_project_fields(); read them with nd_copy_rows( 'id' ).
 */

function nd_copy_fields() {
	static $fields;
	if ( $fields ) {
		return $fields;
	}
	$f = static fn( $label, $default, $type = 'text', $help = '' ) => array( 'label' => $label, 'default' => $default, 'type' => $type, 'help' => $help );
	// A button or link: "{$id}_label" + "{$id}_url" (printed with nd_link()). Empty label hides it.
	$btn = static fn( $id, $name, $label, $url = null ) => array( "{$id}_label" => $f( "{$name} — label", $label, 'text', null === $url ? 'Links to the address in Customizer → Links & details (hidden until set).' : 'Leave empty to hide.' ) )
		+ ( null === $url ? array() : array( "{$id}_url" => $f( "{$name} — link", $url, 'url', 'A path like /about/ or a full https:// address.' ) ) );
	// A rows field from (column => label) and default rows given as lists in column order.
	$rows = static function ( $label, $item, $help, $columns, $defaults ) {
		$cols = array_map( fn( $c ) => is_array( $c ) ? $c : array( 'label' => $c ), $columns );
		return array( 'label' => $label, 'type' => 'rows', 'item' => $item, 'help' => $help, 'columns' => $cols, 'default' => array_map( fn( $d ) => array_combine( array_keys( $cols ), $d ), $defaults ) );
	};
	$text = array( 'label' => 'Text', 'type' => 'textarea' );
	// A row of buttons (printed with nd_buttons()); the first is the filled one.
	$buttons = static fn( $defaults ) => $rows( 'Buttons', 'button', 'The first is the main (filled) button. A path like /about/ or a full https:// address.', array( 'label' => 'Label', 'url' => array( 'label' => 'Link', 'type' => 'url' ) ), $defaults );
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
			'nd_home_buttons'   => $buttons( array( array( 'See projects', '/projects/' ), array( 'Read my story', '/about/' ) ) ),
			'nd_home_log_empty' => $f( '"Dive log" when there are no posts yet', 'Nothing logged yet. The first entries are on their way down.' ),
			'nd_home_short'     => $f( '"The short version"', 'I got into software by automating things I didn’t want to do by hand. Now I build sites for counties, airports, and utilities, where a broken page means someone can’t renew a permit.', 'textarea' ),
		) + $btn( 'nd_home_all', 'Projects "view all" link', 'View all →', '/projects/' )
			+ $btn( 'nd_home_more', '"The short version" link', 'The longer version →', '/about/' ) ),
		'nd_about'    => array( 'About page', array(
			'nd_about_title'     => $f( 'Headline', 'Hi, I’m Nathan.' ),
			'nd_about_highlight' => $f( 'Headline — highlighted line', 'Let’s go a little deeper.' ),
			'nd_about_lede'      => $f( 'Intro', 'I build websites and the systems behind them. Here’s how I got here.', 'textarea' ),
			'nd_stops'           => $rows( 'Story stops', 'stop', 'Your story as stops down the depth line, top to bottom.', array( 'depth' => 'Depth label', 'heading' => 'Heading', 'text' => $text ), array(
				array( '0 m · surface', 'Where it started', '[Where you grew up and what you were into as a kid — one or two sentences.] I fell into software through an obsession with how things work.' ),
				array( '30 m · curious', 'Getting hooked', 'It started with small scripts to get out of tedious tasks. Slowly that grew into a real interest in how whole systems fit together. [The moment or project that got you hooked.]' ),
				array( '200 m · building', 'Building with friends', 'Side projects taught me to ship with other people: a blackjack trainer with Joon Rho, backed by 682 tests, and a Pokémon guessing game with Eric Morales.' ),
				array( '1,000 m · today', 'Sites people depend on', 'Since April 2026 I’ve been a web developer at Hounder, building Drupal, WordPress, and Laravel sites for counties, airports, and utilities. Most of what I’m learning is the part tutorials skip: structuring a build so a non-technical editor can maintain it for years.' ),
			) ),
			'nd_glance'          => $rows( 'At a glance', 'item', 'The quick-facts card. Items with no value are hidden.', array( 'label' => 'Label', 'value' => 'Value' ), array(
				array( 'Now', 'Web developer, Hounder' ),
				array( 'Stack', 'PHP, Drupal, WordPress, Laravel, React' ),
				array( 'Based in', '[CITY, STATE]' ),
				array( 'Off the clock', '[HOBBIES]' ),
			) ),
			'nd_principles'      => $rows( 'How I work', 'principle', 'Short principles, shown two per row.', array( 'heading' => 'Heading', 'text' => $text ), array(
				array( 'Config in code', 'Every environment rebuilds the same way, and changes get reviewed before production.' ),
				array( 'Build for the editor', 'Structured fields hold up for years after handoff. Freeform pages decay.' ),
				array( 'Accessibility first', 'Audit before launch, not after. Cheaper, and nobody gets left out.' ),
				array( 'Real browsers', 'Support what visitors actually run, including older and locked-down machines.' ),
			) ),
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
			'nd_posts_empty'     => $f( 'Shown when there are no posts yet', 'No messages yet. The first ones are on their way down.' ),
		) ),
		'nd_404'      => array( '404 page', array(
			'nd_404_title'   => $f( 'Headline', 'Lost at sea.' ),
			'nd_404_lede'    => $f( 'Intro', 'This page drifted off somewhere, or never existed. Let’s get you back to the surface.', 'textarea' ),
			'nd_404_buttons' => $buttons( array( array( 'Back to the surface', '/' ), array( 'See projects', '/projects/' ), array( 'Read posts', '/posts/' ) ) ),
		) ),
	);
	return $fields;
}

/*
 * The page that holds a section's copy. Projects (slug `projects`, its URL serves the project
 * archive) and 404 (slug `not-found`, private) are pages only so their copy is edited like the rest.
 */
function nd_copy_page_id( $section ) {
	static $cache = array();
	if ( array_key_exists( $section, $cache ) ) {
		return $cache[ $section ];
	}
	$slug = array( 'nd_about' => 'about', 'nd_projects' => 'projects', 'nd_404' => 'not-found' );
	return $cache[ $section ] = match ( $section ) {
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

/** A `rows` copy field: always a list of arrays. */
function nd_copy_rows( $id ) {
	return array_values( array_filter( (array) nd_copy( $id ), 'is_array' ) );
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

/** Prints a `rows` buttons field (see $buttons in nd_copy_fields()): first one filled, rows without a label or link skipped. */
function nd_buttons( $id ) {
	$first = true;
	foreach ( nd_copy_rows( $id ) as $b ) {
		if ( '' !== $b['label'] && $b['url'] ) {
			printf( '<a class="nd-btn%s" href="%s">%s</a>', $first ? ' nd-btn--primary' : '', esc_url( $b['url'] ), esc_html( $b['label'] ) );
			$first = false;
		}
	}
}

/** Prints a copy button/link ($id from $btn in nd_copy_fields()); hidden when its label or link is empty. $url overrides the link field. */
function nd_link( $id, $class = '', $url = null ) {
	$label = nd_copy( "{$id}_label" );
	$url   = $url ?? nd_copy( "{$id}_url" );
	if ( '' !== $label && $url ) {
		printf( '<a%s href="%s">%s</a>', $class ? ' class="' . esc_attr( $class ) . '"' : '', esc_url( $url ), esc_html( $label ) );
	}
}

add_action( 'add_meta_boxes_page', function ( $post ) {
	$section = nd_page_section( $post->ID );
	if ( ! $section ) {
		return;
	}
	remove_post_type_support( 'page', 'editor' ); // Built from their copy fields (plus projects/posts), so no body.
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

/* ── Head: description, share previews, icons ─────────── */

/** One-line description of the current page, from copy the editor already writes. */
function nd_meta_description() {
	if ( is_front_page() ) {
		$text = nd_copy( 'nd_home_lede' );
	} elseif ( is_page( 'about' ) ) {
		$text = nd_copy( 'nd_about_lede' );
	} elseif ( is_post_type_archive( 'project' ) || is_tax( 'project_type' ) ) {
		$text = nd_copy( 'nd_projects_lede' );
	} elseif ( is_home() || is_category() ) {
		$text = is_category() && category_description() ? category_description() : nd_copy( 'nd_posts_lede' );
	} elseif ( is_singular( 'project' ) ) {
		$text = get_post_meta( get_queried_object_id(), 'nd_blurb', true );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30 );
	}
	return trim( wp_strip_all_tags( $text ?? '' ) ) ?: get_bloginfo( 'description' );
}

/** Canonical URL for the current request (core only prints it on singular views). */
function nd_canonical_url() {
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	$paged = get_query_var( 'paged' );
	if ( $paged > 1 ) {
		return get_pagenum_link( $paged );
	}
	return home_url( user_trailingslashit( $GLOBALS['wp']->request ) );
}

add_action( 'wp_head', function () {
	if ( is_404() ) {
		return;
	}
	$id    = is_singular() ? get_queried_object_id() : 0;
	$image = $id && has_post_thumbnail( $id ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' ) : false;
	$desc  = nd_meta_description();
	$url   = nd_canonical_url();
	if ( ! is_singular() && ! is_search() ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $url ) );
	}
	$tags = array(
		'description'     => $desc,
		'og:type'         => is_singular( 'post' ) ? 'article' : 'website',
		'og:site_name'    => get_bloginfo( 'name' ),
		'og:title'        => wp_get_document_title(),
		'og:description'  => $desc,
		'og:url'          => $url,
		// Cover image if the post has one, else the default card (assets/og-default.png, 1200×630).
		'og:image'        => $image ? $image[0] : get_theme_file_uri( 'assets/og-default.png' ),
		'og:image:width'  => $image ? $image[1] : 1200,
		'og:image:height' => $image ? $image[2] : 630,
		'twitter:card'    => 'summary_large_image',
	);
	foreach ( $tags as $key => $value ) {
		$attr = str_starts_with( $key, 'og:' ) ? 'property' : 'name';
		printf( "<meta %s=\"%s\" content=\"%s\">\n", $attr, esc_attr( $key ), esc_attr( $value ) );
	}
	// Lightweight structured data from existing copy.
	$person = array(
		'@type' => 'Person',
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'sameAs' => array_values( array_filter( array( nd_copy( 'nd_github' ), nd_copy( 'nd_linkedin' ) ) ) ),
	);
	$schema = null;
	if ( is_front_page() ) {
		$schema = array( '@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ), 'description' => $desc, 'author' => $person );
	} elseif ( is_singular( 'post' ) ) {
		$post   = get_queried_object();
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title( $post ),
			'description'   => $desc,
			'datePublished' => get_the_date( DATE_W3C, $post ),
			'dateModified'  => get_the_modified_date( DATE_W3C, $post ),
			'url'           => $url,
			'image'         => $tags['og:image'],
			'author'        => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
		);
	}
	if ( $schema ) {
		printf( "<script type=\"application/ld+json\">%s</script>\n", wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}
}, 5 );

/* Fonts are self-hosted (@font-face in style.css); start the body font early so text doesn't swap late. */
add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/inter-latin.woff2' ) ) );
}, 1 );

/* Browser/home-screen icons: the "nd" badge, unless a Site Icon is set in the Customizer. */
add_action( 'wp_head', function () {
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/favicon.svg' ) ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( get_theme_file_uri( 'assets/apple-touch-icon.png' ) ) );
	}
} );

/* No emoji script/styles: browsers draw emoji natively, so WordPress's detection script is dead weight. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );
// wp-admin adds its emoji hooks after init (wp-admin/includes/admin-filters.php), so remove those on admin_init.
add_action( 'admin_init', function () {
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
} );
add_filter( 'tiny_mce_plugins', fn( $plugins ) => array_diff( $plugins, array( 'wpemoji' ) ) );
add_filter( 'emoji_svg_url', '__return_false' ); // Drops the s.w.org dns-prefetch too.

/* ── Hardening: don't hand bots usernames, XML-RPC or the WordPress version. ── */

// Usernames: no author archives (the theme has none) — /?author=2 would otherwise redirect to /author/<login>/.
add_action( 'template_redirect', function () {
	if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );
// …nor in the REST API (public /wp/v2/users lists every login name), the sitemap, or oEmbed data.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );
add_filter( 'wp_sitemaps_add_provider', fn( $provider, $name ) => 'users' === $name ? false : $provider, 10, 2 );
add_filter( 'oembed_response_data', fn( $data ) => array_diff_key( $data, array( 'author_name' => 1, 'author_url' => 1 ) ) );
// Same message for an unknown username/email as for a wrong password (other login messages, e.g. lockouts, are left alone).
add_filter( 'authenticate', function ( $user ) {
	if ( is_wp_error( $user ) && array_intersect( $user->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		return new WP_Error( 'incorrect_password', '<strong>Error:</strong> Wrong username or password.' );
	}
	return $user;
}, 99 );

// XML-RPC (old remote-publishing API, a favourite for password guessing and pingback abuse): refused outright,
// including the built-in system.multicall that batches guesses.
add_action( 'init', function () {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		exit;
	}
} );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
remove_action( 'wp_head', 'rsd_link' );

// Don't advertise the WordPress version.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* ── No comments: the theme never shows them, so don't collect them (or their spam). ── */
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
add_action( 'init', function () {
	foreach ( array( 'post', 'page' ) as $type ) {
		remove_post_type_support( $type, 'comments' );
		remove_post_type_support( $type, 'trackbacks' );
	}
} );
add_action( 'admin_menu', fn() => remove_menu_page( 'edit-comments.php' ) );
add_action( 'admin_bar_menu', fn( $bar ) => $bar->remove_node( 'comments' ), 99 );

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
	$dims = array( 'home' => array( 420, 420 ), '404' => array( 380, 420 ) );
	list( $w, $h ) = $dims[ $pose ] ?? array( 380, 400 );
	printf(
		'<figure class="nd-hero__art %3$s"><img src="%1$s" alt="%2$s" width="%4$d" height="%5$d" decoding="async"></figure>',
		esc_url( get_theme_file_uri( "assets/divers/diver-{$pose}.svg" ) ),
		esc_attr( $alt ),
		esc_attr( $anim ),
		$w,
		$h
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
	$post  = get_post( $post );
	$rows  = array_merge( nd_rows( 'nd_sections', $post->ID ), nd_rows( 'nd_changes', $post->ID ) );
	$words = str_word_count( wp_strip_all_tags( $post->post_content . ' ' . implode( ' ', array_column( $rows, 'text' ) ) ) );
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
	$data['tags']     = is_array( $data['tags'] ) ? implode( ' · ', array_column( $data['tags'], 'tag' ) ) : $data['tags']; // Display text.
	return $data;
}

/** Image slot: featured image if set (with fallback alt text when media alt is empty), otherwise a labelled placeholder. */
function nd_slot( $id, $label, $class = '' ) {
	echo '<div class="nd-slot ' . esc_attr( $class ) . '">';
	if ( has_post_thumbnail( $id ) ) {
		$thumb_id = get_post_thumbnail_id( $id );
		$alt      = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
		echo get_the_post_thumbnail( $id, 'large', '' !== $alt ? array() : array( 'alt' => $label ) );
	} else {
		echo esc_html( '[' . $label . ']' );
	}
	echo '</div>';
}
