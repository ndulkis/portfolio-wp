<?php
/**
 * Global footer + seafloor. Closes the .nd-ocean wrapper opened in header.php.
 */
$nd_email = nd_copy( 'nd_email' );
?>
<div class="nd-wrap">
	<footer class="nd-footer">
		<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> Nathan Dulkis</span>
		<nav aria-label="Social">
			<a href="<?php echo esc_url( nd_copy( 'nd_github' ) ); ?>">GitHub</a>
			<?php if ( nd_copy( 'nd_linkedin' ) ) : ?><a href="<?php echo esc_url( nd_copy( 'nd_linkedin' ) ); ?>">LinkedIn</a><?php endif; ?>
			<?php if ( $nd_email ) : ?><a href="<?php echo esc_url( 'mailto:' . antispambot( $nd_email ) ); ?>">Email</a><?php endif; ?>
		</nav>
	</footer>
</div>
<?php get_template_part( 'parts/seafloor' ); ?>
</div><!-- .nd-ocean -->
<?php wp_footer(); ?>
</body>
</html>
