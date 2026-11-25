<?php
/**
 * Post layout 2 — case study, used for posts in the "Project update" category (see nd_post_layouts()).
 * Facts, numbered sections ("01 · the problem") and the button come from the "Post details" box;
 * any body text follows the sections.
 * Design: docs/design/PostCaseStudy.dc.html
 */
get_header();
nd_decor( 'jelly', 'top: 1400px; left: 1.5%;' );
the_post();
$nd_meta     = static fn( $key ) => get_post_meta( get_the_ID(), $key, true );
$nd_facts    = array_filter( nd_rows( 'nd_facts' ), fn( $f ) => '' !== ( $f['value'] ?? '' ) );
$nd_sections = nd_rows( 'nd_sections' );
?>
<main id="main" class="nd-wrap">
	<section style="padding: clamp(40px, 5vw, 64px) 0 40px">
		<p class="nd-back"><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">← All posts</a></p>
		<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 16px">
			<span class="nd-pill" style="font-size: 13px"><?php echo esc_html( nd_post_kind() ); ?></span>
			<span style="font-size: 14px; color: var(--nd-faint)"><?php echo esc_html( get_the_date() . ' · ' . nd_reading_time() ); ?></span>
		</div>
		<h1 class="nd-h1" style="max-width: 860px"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?><p class="nd-lede" style="max-width: 640px; font-size: 20px; margin-top: 20px"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
	</section>

	<?php nd_slot( get_the_ID(), 'Hero screenshot of the finished project', 'nd-cover' ); ?>

	<div class="nd-case">
		<aside class="nd-card nd-facts" aria-label="Project facts">
			<dl>
				<?php foreach ( $nd_facts as $nd_fact ) : ?>
					<div><dt><?php echo esc_html( $nd_fact['label'] ); ?></dt><dd<?php echo 'stack' === strtolower( $nd_fact['label'] ) ? ' class="nd-mono" style="font-size: 13px; color: var(--nd-ink)"' : ''; ?>><?php echo esc_html( $nd_fact['value'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<?php if ( $nd_meta( 'nd_link_url' ) ) : ?><a class="nd-btn nd-btn--primary" href="<?php echo esc_url( $nd_meta( 'nd_link_url' ) ); ?>"><?php echo esc_html( $nd_meta( 'nd_link_label' ) ?: 'Visit the site ↗' ); ?></a><?php endif; ?>
		</aside>
		<div class="nd-prose" style="font-size: 17px">
			<?php foreach ( $nd_sections as $nd_i => $nd_section ) : ?>
				<h2><?php echo esc_html( sprintf( '%02d · %s', $nd_i + 1, $nd_section['heading'] ) ); ?></h2>
				<?php echo wpautop( esc_html( $nd_section['text'] ) ); // Escaped, then paragraphs. ?>
			<?php endforeach; ?>
			<?php the_content(); ?>
		</div>
	</div>
	<?php get_template_part( 'parts/post-nav' ); ?>
</main>
<?php
get_footer();
