<?php
/** Self-check for theme field sanitizing + template-aware meta box. Run: lando wp eval-file scripts/check-fields.php */
$id = wp_insert_post( array( 'post_type' => 'project', 'post_title' => 'nd check', 'post_status' => 'draft' ) );
update_post_meta( $id, 'nd_learned', "line one\nline <b>two</b>" );
update_post_meta( $id, 'nd_status', 'bogus' );
update_post_meta( $id, 'nd_link_url', 'javascript:alert(1)' );
assert( "line one\nline two" === get_post_meta( $id, 'nd_learned', true ) );
assert( 'live' === get_post_meta( $id, 'nd_status', true ) );
assert( '' === get_post_meta( $id, 'nd_link_url', true ) );
wp_delete_post( $id, true );

$post = get_page_by_path( 'bollard-0-1-2-is-out', OBJECT, 'post' ); // Release note template.
ob_start();
nd_render_fields_box( $post, array( 'args' => array( 'fields' => nd_post_fields() ) ) );
$html = ob_get_clean();
assert( str_contains( $html, 'name="nd_version" class="widefat" value' ) );        // Release field enabled.
assert( str_contains( $html, 'name="nd_client" class="widefat" disabled' ) );      // Case-study field disabled.
// Page copy: default until the page sets it, then the page's value.
$page = nd_copy_page_id( 'nd_404' );
assert( $page && 'nd_404' === nd_page_section( $page ) );
assert( 'Lost at sea.' === nd_copy( 'nd_404_title' ) );
update_post_meta( $page, 'nd_404_title', 'Check <b>title</b>' );
assert( 'Check title' === nd_copy( 'nd_404_title' ) );
delete_post_meta( $page, 'nd_404_title' );
assert( 'Lost at sea.' === nd_copy( 'nd_404_title' ) );

// Saving the edit screen stores page copy (the save_post handler knows page fields).
$about = get_page_by_path( 'about' );
wp_set_current_user( get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) )[0] );
$_POST = array( 'nd_fields_nonce' => wp_create_nonce( 'nd_fields' ), 'nd_stop1_title' => 'Saved <i>heading</i>' );
do_action( 'save_post', $about->ID, $about, true );
$_POST = array();
assert( 'Saved heading' === nd_copy( 'nd_stop1_title' ) );
delete_post_meta( $about->ID, 'nd_stop1_title' );

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
