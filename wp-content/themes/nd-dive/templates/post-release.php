<?php
/**
 * Post layout 3 — release note, used for posts in the "Release" category (see nd_post_layouts()).
 * Intro = the post summary; version, changes (grouped New / Improved / Fixed) and the download card come
 * from the "Post details" box; any body text (e.g. a screenshot) follows the changes.
 * Design: docs/design/PostRelease.dc.html
 */
get_header();
the_post();
$nd_meta    = static fn( $key ) => get_post_meta( get_the_ID(), $key, true );
$nd_version = $nd_meta( 'nd_version' );
$nd_changes = array();
foreach ( nd_rows( 'nd_changes' ) as $nd_change ) {
	$nd_changes[ $nd_change['type'] ][] = $nd_change['text'];
}
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
		<div class="nd-prose" style="font-size: 17px">
			<?php if ( has_excerpt() ) : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<?php foreach ( nd_change_types() as $nd_type => $nd_heading ) : ?>
				<?php if ( ! empty( $nd_changes[ $nd_type ] ) ) : ?>
					<h2><?php echo esc_html( $nd_heading ); ?></h2>
					<ul><?php foreach ( $nd_changes[ $nd_type ] as $nd_text ) : ?><li><?php echo esc_html( $nd_text ); ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php the_content(); ?>
		</div>
		<?php if ( $nd_meta( 'nd_link_url' ) ) : ?>
			<div class="nd-card nd-download">
				<div>
					<p style="font-weight: 600"><?php echo esc_html( ( $nd_meta( 'nd_download_title' ) ?: 'Get this release' ) . ( $nd_version ? ' — v' . $nd_version : '' ) ); ?></p>
					<p style="font-size: 14px; color: var(--nd-faint)"><?php echo esc_html( $nd_meta( 'nd_download_meta' ) ); ?></p>
				</div>
				<a class="nd-btn nd-btn--primary" href="<?php echo esc_url( $nd_meta( 'nd_link_url' ) ); ?>"><?php echo esc_html( $nd_meta( 'nd_link_label' ) ?: 'Download ↗' ); ?></a>
			</div>
		<?php endif; ?>
	</article>
	<div class="nd-wrap"><?php get_template_part( 'parts/post-nav' ); ?></div>
</main>
<?php
get_footer();
