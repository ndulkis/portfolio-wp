<?php
/**
 * Global header. Templates pass a body class modifier via get_header( null, array( 'ocean' => 'abyss' ) ).
 */
$nd_ocean = $args['ocean'] ?? '';
$nd_nav   = nd_nav_current();
$nd_links = array(
	'projects' => array( 'Projects', get_post_type_archive_link( 'project' ) ),
	'posts'    => array( 'Posts', get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ),
	'about'    => array( 'About', home_url( '/about/' ) ),
);
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
			<?php foreach ( $nd_links as $key => list( $label, $url ) ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"<?php echo $key === $nd_nav ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
	</header>
</div>
