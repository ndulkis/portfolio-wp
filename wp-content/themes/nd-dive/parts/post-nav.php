<?php
/** Previous / next post cards (shared by all post templates). */
$nd_prev = get_previous_post();
$nd_next = get_next_post();
if ( ! $nd_prev && ! $nd_next ) {
	return;
}
?>
<nav class="nd-prevnext" aria-label="More posts">
	<?php if ( $nd_prev ) : ?>
		<a class="nd-card" href="<?php echo esc_url( get_permalink( $nd_prev ) ); ?>"><span class="nd-mono" style="font: 13px var(--nd-sans)">← Previous</span><span><?php echo esc_html( get_the_title( $nd_prev ) ); ?></span></a>
	<?php else : ?><span></span><?php endif; ?>
	<?php if ( $nd_next ) : ?>
		<a class="nd-card is-next" href="<?php echo esc_url( get_permalink( $nd_next ) ); ?>"><span class="nd-mono" style="font: 13px var(--nd-sans)">Next →</span><span><?php echo esc_html( get_the_title( $nd_next ) ); ?></span></a>
	<?php endif; ?>
</nav>
