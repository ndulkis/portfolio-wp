<?php
/**
 * 404 — Lost at sea. Design: docs/design/NotFound.dc.html
 */
get_header( null, array( 'ocean' => 'abyss' ) );
nd_decor( 'angler', 'bottom: 300px; left: 3%;' );
?>
<main id="main" class="nd-lost">
	<div class="nd-hero__text">
		<p class="nd-eyebrow" style="font-size: 15px; color: var(--nd-accent); margin-bottom: 8px">error 404 · depth unknown</p>
		<h1 class="nd-h1"><?php echo esc_html( nd_copy( 'nd_404_title' ) ); ?></h1>
		<p class="nd-lede" style="max-width: 480px"><?php echo esc_html( nd_copy( 'nd_404_lede' ) ); ?></p>
		<div class="nd-actions">
			<?php nd_link( 'nd_404_btn1', 'nd-btn nd-btn--primary' ); ?>
			<?php nd_link( 'nd_404_btn2', 'nd-btn' ); ?>
			<?php nd_link( 'nd_404_btn3', 'nd-btn' ); ?>
		</div>
	</div>
	<?php nd_diver( '404', 'Illustration of Nathan as a deep-sea diver, confused, holding a map upside down', 'nd-anim-soft' ); ?>
</main>
<?php
get_footer();
