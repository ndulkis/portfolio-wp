<?php
/**
 * Post layout 4 — personal blog post, used for posts in the "Blog" category (see nd_post_layouts()).
 * Summary as the intro, wide cover, a strip of log details, the body (first letter as a drop cap;
 * .nd-note side notes and .nd-pull lines from the outline), a reply-by-email sign-off, then
 * three more Blog posts. Details and sign-off come from the "Post details" box.
 * Design: docs/design/PostBlog.dc.html
 */
get_header();
nd_decor( 'jelly', 'top: 1500px; left: 4%;' );
the_post();
$nd_meta  = static fn( $key ) => get_post_meta( get_the_ID(), $key, true );
$nd_facts = array_filter( nd_rows( 'nd_blog_facts' ), fn( $f ) => '' !== ( $f['value'] ?? '' ) );
$nd_email = nd_copy( 'nd_email' );
$nd_more  = get_posts( array( 'category_name' => 'blog', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ) ) );
?>
<main id="main">
	<header class="nd-blog-head">
		<p class="nd-back"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">← All posts</a></p>
		<div style="max-width: 720px">
			<p class="nd-eyebrow" style="color: var(--nd-accent); margin-bottom: 12px"><?php echo esc_html( strtolower( nd_post_kind() ) ); ?></p>
			<h1 class="nd-h1" style="line-height: 1.08"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="nd-lede" style="max-width: none; font-size: 21px; line-height: 1.6; margin-top: 20px"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</div>
		<div class="nd-byline" style="margin: 28px 0 32px; padding: 0; border: 0">
			<span class="nd-badge" aria-hidden="true" style="width: 36px; height: 36px">nd</span>
			<strong><?php the_author(); ?></strong><span>·</span><span><?php echo esc_html( get_the_date() ); ?></span><span>·</span><span><?php echo esc_html( nd_reading_time() ); ?></span>
		</div>
		<?php if ( has_post_thumbnail() ) : ?><?php nd_slot( get_the_ID(), 'Cover image', 'nd-cover' ); ?><?php endif; ?>
		<?php if ( $nd_facts ) : ?>
			<dl class="nd-card nd-blog-facts">
				<?php foreach ( $nd_facts as $nd_fact ) : ?>
					<div><dt><?php echo esc_html( $nd_fact['label'] ); ?></dt><dd><?php echo esc_html( $nd_fact['value'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</header>

	<article class="nd-narrow" style="padding-top: 56px; padding-bottom: 48px">
		<div class="nd-prose nd-prose--blog"><?php the_content(); ?></div>
		<div class="nd-signoff">
			<div>
				<strong><?php echo esc_html( $nd_meta( 'nd_reply_title' ) ?: 'Thanks for reading.' ); ?></strong>
				<span><?php echo esc_html( $nd_meta( 'nd_reply_text' ) ?: 'Got a thought on this one? I’d like to hear it.' ); ?></span>
			</div>
			<?php if ( $nd_email ) : ?>
				<a class="nd-btn nd-btn--primary" href="<?php echo esc_url( 'mailto:' . antispambot( $nd_email ) . '?subject=' . rawurlencode( 'Re: ' . get_the_title() ) ); ?>"><?php echo esc_html( $nd_meta( 'nd_reply_label' ) ?: 'Reply by email' ); ?></a>
			<?php endif; ?>
		</div>
		<?php the_tags( '<div class="nd-tags" style="margin-top: 28px">', '', '</div>' ); ?>
	</article>

	<div class="nd-wrap">
		<?php if ( $nd_more ) : ?>
			<section class="nd-more" aria-labelledby="nd-more-blog">
				<h2 id="nd-more-blog" class="nd-eyebrow">more from the blog</h2>
				<div class="nd-more__grid">
					<?php foreach ( $nd_more as $nd_post ) : ?>
						<a class="nd-card" href="<?php echo esc_url( get_permalink( $nd_post ) ); ?>">
							<?php nd_slot( $nd_post->ID, 'Cover image' ); ?>
							<span class="nd-more__body"><span class="nd-mono" style="font-size: 13px"><?php echo esc_html( get_the_date( '', $nd_post ) ); ?></span><span class="nd-more__title"><?php echo esc_html( get_the_title( $nd_post ) ); ?></span></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php else : ?>
			<?php get_template_part( 'parts/post-nav' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
