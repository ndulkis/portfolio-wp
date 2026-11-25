<?php
/**
 * Global footer + seafloor. Closes the .nd-ocean wrapper opened in header.php.
 */
// Links come from Appearance → Menus → "Footer links"; without one, from Customizer → Links & details.
$nd_email = nd_copy( 'nd_email' );
$nd_links = nd_menu_links( 'footer', array(
	array( 'GitHub', nd_copy( 'nd_github' ) ),
	array( 'LinkedIn', nd_copy( 'nd_linkedin' ) ),
	array( 'Email', $nd_email ? 'mailto:' . antispambot( $nd_email ) : '' ),
) );
?>
<div class="nd-wrap">
	<footer class="nd-footer">
		<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Nathan Dulkis</span>
		<nav aria-label="Social">
			<?php foreach ( $nd_links as list( $label, $url ) ) : ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
	</footer>
</div>
<?php get_template_part( 'parts/seafloor' ); ?>
</div><!-- .nd-ocean -->
<?php wp_footer(); ?>
</body>
</html>
