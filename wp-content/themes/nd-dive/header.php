<?php
/**
 * Global header. Templates pass a body class modifier via get_header( null, array( 'ocean' => 'abyss' ) ).
 */
$nd_ocean = $args['ocean'] ?? '';
// Links come from Appearance → Menus → "Header navigation". The current section's link is highlighted
// (e.g. Projects stays current on a project type filter).
$nd_defaults = nd_default_nav();
$nd_current  = untrailingslashit( $nd_defaults[ nd_nav_current() ][1] ?? '' );
$nd_links    = nd_menu_links( 'header', $nd_defaults );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main">Skip to content</a>
<div class="nd-ocean<?php echo $nd_ocean ? ' nd-ocean--' . esc_attr( $nd_ocean ) : ''; ?>">
<div class="nd-wrap">
	<header class="nd-header">
		<a class="nd-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="nd-badge" aria-hidden="true">nd</span><?php bloginfo( 'name' ); ?></a>
		<nav class="nd-nav" aria-label="Primary">
			<?php foreach ( $nd_links as list( $label, $url ) ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"<?php echo $nd_current && untrailingslashit( $url ) === $nd_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
	</header>
</div>
