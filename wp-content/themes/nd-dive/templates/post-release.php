<?php
/**
 * Template Name: Release note
 * Template Post Type: post
 *
 * Post template 3 — short app release notes. Body: intro, screenshot, then
 * "New" / "Improved" / "Fixed" headings with lists. Version + download from "Template details".
 * Design: docs/design/PostRelease.dc.html
 */
get_header();
the_post();
$nd_meta    = static fn( $key ) => get_post_meta( get_the_ID(), $key, true );
$nd_version = $nd_meta( 'nd_version' );
?>
<main id="main">
	<article class="nd-narrow" style="max-width: 760px; padding-top: clamp(40px, 5vw, 64px); padding-bottom: 56px">
		<p class="nd-back" style="margin-bottom: 32px"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">← All posts</a></p>
		<div class="nd-release-head">
			<?php if ( $nd_version ) : ?><div class="nd-sonar" aria-hidden="true"><span>v<?php echo esc_html( $nd_version ); ?></span></div><?php endif; ?>
			<div style="flex: 1 1 360px; min-width: 0">
				<p class="nd-eyebrow" style="color: var(--nd-accent); margin-bottom: 6px">release</p>
				<h1 class="nd-h1" style="font-size: clamp(32px, 6vw, 44px); line-height: 1.1"><?php the_title(); ?></h1>
				<p style="margin: 10px 0 0; font-size: 15px; color: var(--nd-faint)"><?php echo esc_html( get_the_date() . ' · ' . nd_reading_time() ); ?></p>
			</div>
		</div>
		<div class="nd-prose" style="font-size: 17px"><?php the_content(); ?></div>
		<?php if ( $nd_meta( 'nd_link_url' ) ) : ?>
			<div class="nd-card nd-download">
				<div>
					<p style="font-weight: 600"><?php echo esc_html( 'Get this release' . ( $nd_version ? ' — v' . $nd_version : '' ) ); ?></p>
					<p style="font-size: 14px; color: var(--nd-faint)"><?php echo esc_html( $nd_meta( 'nd_download_meta' ) ); ?></p>
				</div>
				<a class="nd-btn nd-btn--primary" href="<?php echo esc_url( $nd_meta( 'nd_link_url' ) ); ?>">Download ↗</a>
			</div>
		<?php endif; ?>
	</article>
	<div class="nd-wrap"><?php get_template_part( 'parts/post-nav' ); ?></div>
</main>
<?php
get_footer();
