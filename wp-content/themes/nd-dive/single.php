<?php
/**
 * Post template 1 — Article (default for posts). Design: docs/design/PostArticle.dc.html
 * Other layouts: pick "Case study" or "Release note" under Post → Template.
 */
get_header();
nd_decor( 'fish', 'top: 760px; right: 7%;' );
the_post();
?>
<main id="main">
	<article class="nd-narrow" style="padding-top: clamp(40px, 6vw, 72px); padding-bottom: 48px">
		<p class="nd-back"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">← All posts</a></p>
		<p class="nd-eyebrow" style="color: var(--nd-accent); margin-bottom: 12px"><?php echo esc_html( strtolower( nd_post_kind() ) ); ?></p>
		<h1 class="nd-h1" style="font-size: clamp(34px, 6.5vw, 48px); line-height: 1.1"><?php the_title(); ?></h1>
		<div class="nd-byline">
			<span class="nd-badge" aria-hidden="true" style="width: 36px; height: 36px">nd</span>
			<strong>Nathan Dulkis</strong><span>·</span><span><?php echo esc_html( get_the_date() ); ?></span><span>·</span><span><?php echo esc_html( nd_reading_time() ); ?></span>
		</div>
		<div class="nd-prose"><?php the_content(); ?></div>
		<?php the_tags( '<div class="nd-tags">', '', '</div>' ); ?>
	</article>
	<div class="nd-wrap"><?php get_template_part( 'parts/post-nav' ); ?></div>
</main>
<?php
get_footer();
