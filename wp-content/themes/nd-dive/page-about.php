<?php
/**
 * About (page slug "about"). All copy comes from the page's "Page copy" box (Pages → Edit);
 * story stops, At a glance and How I work are lists there (add / reorder / remove). Design: docs/design/About.dc.html
 */
get_header();
nd_decor( 'fish', 'top: 300px; right: 6%;' );
nd_decor( 'jelly', 'top: 1100px; right: 3%;' );
nd_decor( 'school', 'top: 1900px; left: 2%;' );
$nd_email = nd_copy( 'nd_email' );
?>
<main id="main" class="nd-wrap">
	<section class="nd-hero">
		<div class="nd-hero__text">
			<p class="nd-eyebrow">about</p>
			<h1 class="nd-h1"><?php echo esc_html( nd_copy( 'nd_about_title' ) ); ?><br><span class="nd-hl"><?php echo esc_html( nd_copy( 'nd_about_highlight' ) ); ?></span></h1>
			<p class="nd-lede"><?php echo esc_html( nd_copy( 'nd_about_lede' ) ); ?></p>
		</div>
		<?php nd_diver( 'about', 'Illustration of Nathan as a deep-sea diver, floating cross-legged and typing on a laptop' ); ?>
	</section>

	<section class="nd-story" aria-label="My story">
		<?php foreach ( nd_copy_rows( 'nd_stops' ) as $nd_stop ) : ?>
			<div class="nd-stop">
				<p class="nd-stop__depth"><?php echo esc_html( $nd_stop['depth'] ); ?></p>
				<?php if ( $nd_stop['heading'] ) : ?><h2><?php echo esc_html( $nd_stop['heading'] ); ?></h2><?php endif; ?>
				<?php echo wpautop( esc_html( $nd_stop['text'] ) ); // Escaped, then paragraphs. ?>
			</div>
		<?php endforeach; ?>
	</section>

	<section class="nd-glance">
		<div class="nd-card">
			<h2>At a glance</h2>
			<dl>
				<?php foreach ( nd_copy_rows( 'nd_glance' ) as $nd_item ) : ?>
					<?php if ( '' !== $nd_item['value'] ) : ?><dt><?php echo esc_html( $nd_item['label'] ); ?></dt><dd><?php echo esc_html( $nd_item['value'] ); ?></dd><?php endif; ?>
				<?php endforeach; ?>
			</dl>
		</div>
		<div class="nd-card">
			<h2>How I work</h2>
			<div class="nd-principles">
				<?php foreach ( nd_copy_rows( 'nd_principles' ) as $nd_principle ) : ?>
					<div><h3><?php echo esc_html( $nd_principle['heading'] ); ?></h3><p><?php echo esc_html( $nd_principle['text'] ); ?></p></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="nd-cta">
		<div>
			<h2 class="nd-h2" style="margin-bottom: 6px"><?php echo esc_html( nd_copy( 'nd_about_cta_title' ) ); ?></h2>
			<p class="nd-muted" style="margin: 0"><?php echo nl2br( esc_html( nd_copy( 'nd_about_cta_text' ) ) ); ?></p>
		</div>
		<div class="nd-actions" style="margin: 0">
			<?php nd_link( 'nd_about_email', 'nd-btn nd-btn--primary', $nd_email ? 'mailto:' . antispambot( $nd_email ) : '' ); ?>
			<?php nd_link( 'nd_about_resume', 'nd-btn', null, array( 'download' => '' ) ); ?>
			<?php nd_link( 'nd_about_linkedin', 'nd-btn', nd_copy( 'nd_linkedin' ) ); ?>
		</div>
	</section>
</main>
<?php
get_footer();
