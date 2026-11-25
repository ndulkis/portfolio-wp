<?php
/** Self-check for theme field sanitizing + layout-aware meta box. Run: lando wp eval-file scripts/check-fields.php */
$id = wp_insert_post( array( 'post_type' => 'project', 'post_title' => 'nd check', 'post_status' => 'draft' ) );
update_post_meta( $id, 'nd_learned', "line one\nline <b>two</b>" );
update_post_meta( $id, 'nd_status', 'bogus' );
update_post_meta( $id, 'nd_link_url', 'javascript:alert(1)' );
assert( "line one\nline two" === get_post_meta( $id, 'nd_learned', true ) );
assert( 'live' === get_post_meta( $id, 'nd_status', true ) );
assert( '' === get_post_meta( $id, 'nd_link_url', true ) );
wp_delete_post( $id, true );

$post = get_page_by_path( 'bollard-0-1-2-is-out', OBJECT, 'post' ); // Release category → release layout.
assert( 'release' === nd_post_layout( $post ) );
assert( 'project-update' === nd_post_layout( get_page_by_path( 'building-the-uab-alumni-site', OBJECT, 'post' ) ) );
assert( '' === nd_post_layout( get_page_by_path( 'joined-hounder', OBJECT, 'post' ) ) );
ob_start();
nd_render_fields_box( $post, array( 'args' => array( 'fields' => nd_post_fields() ) ) );
$html = ob_get_clean();
assert( str_contains( $html, 'id="nd_version" name="nd_version" class="widefat" value' ) );   // Release field enabled.
assert( str_contains( $html, 'name="nd_changes[0][type]" class="widefat">' ) );              // Release rows enabled…
assert( str_contains( $html, 'name="nd_facts[0][label]" class="widefat" disabled' ) );       // …case-study rows disabled.
assert( str_contains( $html, 'name="excerpt" class="widefat" rows="3">[One or two sentences' ) );     // Summary = the excerpt.
assert( str_contains( $html, '<template>' ) && str_contains( $html, '+ Add change' ) );     // Add-row template.
assert( ! str_contains( nd_post_outline(), '<!--' ) );                                     // Plain HTML for the classic editor.
assert( ! use_block_editor_for_post_type( 'post' ) );

// Rows sanitize: columns by type, empty rows and the "_" marker dropped, order kept.
$case = get_page_by_path( 'building-the-uab-alumni-site', OBJECT, 'post' );
$keep = get_post_meta( $case->ID, 'nd_sections', true );
update_post_meta( $case->ID, 'nd_sections', array( '_' => '', 'new0' => array( 'heading' => 'B <b>x</b>', 'text' => "a\nb" ), 3 => array( 'heading' => '', 'text' => '' ), 1 => array( 'heading' => 'A' ) ) );
assert( array( array( 'heading' => 'B x', 'text' => "a\nb" ), array( 'heading' => 'A', 'text' => '' ) ) === get_post_meta( $case->ID, 'nd_sections', true ) );
update_post_meta( $case->ID, 'nd_sections', $keep );
$rel = get_post_meta( $post->ID, 'nd_changes', true );
update_post_meta( $post->ID, 'nd_changes', array( array( 'type' => 'bogus', 'text' => 'x' ), array( 'type' => 'fixed', 'text' => '' ) ) );
assert( array( array( 'type' => 'new', 'text' => 'x' ) ) === get_post_meta( $post->ID, 'nd_changes', true ) );
update_post_meta( $post->ID, 'nd_changes', $rel );
assert( array() === nd_rows( 'nd_sections', $post->ID ) );                         // Never saved: empty list, not array( '' ).
// Page copy: default until the page sets it, then the page's value.
$page = nd_copy_page_id( 'nd_404' );
assert( $page && 'nd_404' === nd_page_section( $page ) );
assert( 'Lost at sea.' === nd_copy( 'nd_404_title' ) );
update_post_meta( $page, 'nd_404_title', 'Check <b>title</b>' );
assert( 'Check title' === nd_copy( 'nd_404_title' ) );
delete_post_meta( $page, 'nd_404_title' );
assert( 'Lost at sea.' === nd_copy( 'nd_404_title' ) );

// Saving the edit screen (WordPress slashes $_POST): page copy saves, backslashes survive,
// and removing every row saves an empty list (the "_" marker is still submitted).
$about = get_page_by_path( 'about' );
wp_set_current_user( get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0] );
$_POST = wp_slash( array( 'nd_fields_nonce' => wp_create_nonce( 'nd_fields' ), 'nd_stop1_title' => 'Saved <i>heading</i> C:\\dir' ) );
do_action( 'save_post', $about->ID, $about, true );
assert( 'Saved heading C:\\dir' === nd_copy( 'nd_stop1_title' ) );
delete_post_meta( $about->ID, 'nd_stop1_title' );
$keep  = get_post_meta( $case->ID, 'nd_facts', true );
$_POST = wp_slash( array( 'nd_fields_nonce' => wp_create_nonce( 'nd_fields' ), 'nd_facts' => array( '_' => '' ) ) );
do_action( 'save_post', $case->ID, $case, true );
assert( array() === get_post_meta( $case->ID, 'nd_facts', true ) );
update_post_meta( $case->ID, 'nd_facts', $keep );
$_POST = array();

// Buttons: label + link from the page; an empty label hides the button.
$btn = static function () { ob_start(); nd_link( 'nd_404_btn2', 'nd-btn' ); return ob_get_clean(); };
assert( '<a class="nd-btn" href="/projects/">See projects</a>' === $btn() );
update_post_meta( $page, 'nd_404_btn2_url', 'javascript:alert(1)' );
assert( '' === $btn() );
update_post_meta( $page, 'nd_404_btn2_url', '/x/' );
update_post_meta( $page, 'nd_404_btn2_label', '' );
assert( '' === $btn() );
delete_post_meta( $page, 'nd_404_btn2_url' );
delete_post_meta( $page, 'nd_404_btn2_label' );
// Menus: an assigned menu's items, otherwise the fallback minus empty links.
$saved = get_theme_mod( 'nav_menu_locations' );
set_theme_mod( 'nav_menu_locations', array() );
assert( array( array( 'GitHub', 'https://x' ) ) === nd_menu_links( 'footer', array( array( 'GitHub', 'https://x' ), array( 'LinkedIn', '' ) ) ) );
$menu = wp_create_nav_menu( 'nd check menu' );
wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Mastodon', 'menu-item-url' => 'https://m.example', 'menu-item-status' => 'publish' ) );
set_theme_mod( 'nav_menu_locations', array( 'footer' => $menu ) );
assert( array( array( 'Mastodon', 'https://m.example' ) ) === nd_menu_links( 'footer', array() ) );
wp_delete_nav_menu( $menu );
set_theme_mod( 'nav_menu_locations', $saved );
WP_CLI::success( 'Field checks passed.' );
