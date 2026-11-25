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
assert( 'Lost at sea.' === nd_copy( 'nd_404_title' ) );
WP_CLI::success( 'Field checks passed.' );
