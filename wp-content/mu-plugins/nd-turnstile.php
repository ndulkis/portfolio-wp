<?php
/**
 * Plugin Name: ND Turnstile
 * Description: Cloudflare Turnstile on the login and lost-password forms. On only when ND_TURNSTILE_SITE_KEY and ND_TURNSTILE_SECRET are defined in wp-config.php.
 *
 * Must-use plugin, so login protection doesn't depend on the theme and can't be switched off from the dashboard.
 * Fails closed: no token or a failed check means no login. Locked out (bad keys, Cloudflare down)? Remove the two
 * constants from wp-config.php and it switches off.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ND_TURNSTILE_SITE_KEY' ) || ! defined( 'ND_TURNSTILE_SECRET' ) ) {
	return;
}

/** The widget, inside the login / lost-password form. */
function nd_turnstile_widget() {
	printf( '<div class="cf-turnstile" data-sitekey="%s" data-theme="light" style="margin: 0 0 16px"></div>', esc_attr( ND_TURNSTILE_SITE_KEY ) );
}
add_action( 'login_form', 'nd_turnstile_widget' );
add_action( 'lostpassword_form', 'nd_turnstile_widget' );

add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer' ) );
	// The widget is 300px; WordPress's login box leaves 272px, so widen the box a little.
	wp_add_inline_style( 'login', '#login { width: 352px; }' );
} );

/** True if Cloudflare confirms the token the widget put in the form. */
function nd_turnstile_passed() {
	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( '' === $token ) {
		return false;
	}
	$response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 10,
		// No remoteip: it's optional, and behind Cloudflare or a proxy REMOTE_ADDR would be the proxy, not the visitor.
		'body'    => array(
			'secret'   => ND_TURNSTILE_SECRET,
			'response' => $token,
		),
	) );
	$body = is_wp_error( $response ) ? array() : json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

function nd_turnstile_error() {
	return new WP_Error( 'turnstile_failed', '<strong>Error:</strong> We couldn’t confirm you’re a person. Please try again.' );
}

/*
 * Login form only (wp-login.php posts "log"): runs after WordPress's own password check (priority 20), so it
 * overrides that result either way. REST/app-password logins and WP-CLI never reach login_init, so they're untouched.
 */
add_filter( 'authenticate', function ( $user ) {
	if ( did_action( 'login_init' ) && isset( $_POST['log'] ) && ! nd_turnstile_passed() ) { // phpcs:ignore WordPress.Security.NonceVerification
		return nd_turnstile_error();
	}
	return $user;
}, 30 );

add_action( 'lostpassword_post', function ( $errors ) {
	if ( did_action( 'login_init' ) && ! nd_turnstile_passed() ) {
		$errors->add( 'turnstile_failed', nd_turnstile_error()->get_error_message() );
	}
} );
