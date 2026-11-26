<?php
/**
 * Home. Design: docs/design/UnderwaterDive.dc.html
 */
get_header();
nd_decor( 'sub', 'top: 80px; left: 50%;' );
nd_decor( 'fish', 'top: 650px; left: 46%;' );
nd_decor( 'jelly', 'top: 1160px; left: 1.5%;' );
nd_decor( 'school', 'top: 1880px; right: 2.5%;' );

$nd_projects = get_posts( array( 'post_type' => 'project', 'numberposts' => 3, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$nd_posts    = get_posts( array( 'numberposts' => 3 ) );
?>
<main id="main" class="nd-wrap">
	<section class="nd-hero nd-hero--home">
		<div class="nd-hero__text">
			<h1 class="nd-h1 nd-h1--home"><?php echo esc_html( nd_copy( 'nd_home_title' ) ); ?><br><span class="nd-hl"><?php echo esc_html( nd_copy( 'nd_home_highlight' ) ); ?></span></h1>
			<p class="nd-lede"><?php echo esc_html( nd_copy( 'nd_home_lede' ) ); ?></p>
			<div class="nd-actions">
				<?php nd_buttons( 'nd_home_buttons' ); ?>
			</div>
		</div>
		<?php nd_diver( 'home', 'Illustration of Nathan as a deep-sea diver, drifting at an angle, smiling and waving', 'nd-anim-home' ); ?>
	</section>

	<section class="nd-section">
		<div class="nd-section__head">
			<h2 class="nd-h2">Projects</h2>
			<?php nd_link( 'nd_home_all' ); ?>
		</div>
		<div class="nd-grid-3">
			<?php foreach ( $nd_projects as $p ) : $d = nd_project( $p->ID ); ?>
				<article class="nd-card nd-pcard">
					<?php nd_slot( $p->ID, $d['title'] . ' screenshot' ); ?>
					<div class="nd-pcard__body">
						<h3><?php echo esc_html( $d['title'] ); ?></h3>
						<p class="nd-muted"><?php echo esc_html( $d['blurb'] ); ?></p>
						<p class="nd-mono"><?php echo esc_html( $d['tags'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="nd-split">
		<div>
			<h2 class="nd-h2">The short version</h2>
			<p class="nd-muted"><?php echo esc_html( nd_copy( 'nd_home_short' ) ); ?></p>
			<p><?php nd_link( 'nd_home_more' ); ?></p>
		</div>
		<div>
			<h2 class="nd-h2" style="margin-bottom: 20px">Dive log</h2>
			<?php if ( $nd_posts ) : ?>
				<ol class="nd-log">
					<?php foreach ( $nd_posts as $p ) : ?>
						<li>
							<p class="nd-mono"><?php echo esc_html( get_the_date( 'M j, Y', $p ) . ' · ' . strtolower( nd_post_kind( $p ) ) ); ?></p>
							<p><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></p>
							<?php if ( has_excerpt( $p ) ) : ?><p class="nd-muted" style="font-size: 15px"><?php echo esc_html( get_the_excerpt( $p ) ); ?></p><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="nd-muted"><?php echo esc_html( nd_copy( 'nd_home_log_empty' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
