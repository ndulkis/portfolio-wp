<?php
/**
 * Post layout 1 — article, for Notes and any post without a layout category. Design: docs/design/PostArticle.dc.html
 * Project update and Release posts use the layouts in templates/ (see nd_post_layouts()).
 */
get_header();
nd_decor( 'fish', 'top: 760px; right: 7%;' );
the_post();
?>
<main id="main">
	<article class="nd-narrow" style="padding-top: clamp(40px, 6vw, 72px); padding-bottom: 48px">
		<p class="nd-back"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>">← All posts</a></p>
		<p class="nd-eyebrow" style="color: var(--nd-accent); margin-bottom: 12px"><?php echo esc_html( strtolower( nd_post_kind() ) ); ?></p>
		<h1 class="nd-h1" style="font-size: clamp(34px, 6.5vw, 48px); line-height: 1.1"><?php the_title(); ?></h1>
		<div class="nd-byline">
			<span class="nd-badge" aria-hidden="true" style="width: 36px; height: 36px">nd</span>
			<div class="nd-byline__meta">
				<strong><?php the_author(); ?></strong><span class="nd-byline__sub"><span><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( nd_reading_time() ); ?></span></span>
			</div>
		</div>
		<div class="nd-prose"><?php the_content(); ?></div>
		<?php the_tags( '<div class="nd-tags">', '', '</div>' ); ?>
	</article>
	<div class="nd-wrap"><?php get_template_part( 'parts/post-nav' ); ?></div>
</main>
<?php
get_footer();
