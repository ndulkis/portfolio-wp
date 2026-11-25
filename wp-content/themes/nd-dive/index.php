<?php
/**
 * Fallback for anything without a dedicated template (plain pages, single projects, search).
 */
get_header();
?>
<main id="main" class="nd-narrow" style="padding-top: clamp(40px, 6vw, 72px); padding-bottom: 72px">
	<?php while ( have_posts() ) : the_post(); ?>
		<h1 class="nd-h1" style="margin-bottom: 32px"><?php the_title(); ?></h1>
		<div class="nd-prose"><?php the_content(); ?></div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
