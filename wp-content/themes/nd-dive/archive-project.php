<?php
/**
 * Projects archive (/projects/) and project-type archives. Design: docs/design/Projects.dc.html
 * Filter chips are plain links to the project_type term archives — no JS needed.
 */
get_header();
nd_decor( 'sub', 'top: 76px; left: 44%;' );
nd_decor( 'jelly', 'top: 1300px; left: 1.5%;' );
nd_decor( 'fish', 'top: 2100px; right: 2.5%;' );

$nd_terms   = get_terms( array( 'taxonomy' => 'project_type', 'hide_empty' => true ) );
$nd_current = is_tax( 'project_type' ) ? get_queried_object_id() : 0;
$nd_total   = wp_count_posts( 'project' )->publish;
?>
<main id="main" class="nd-wrap">
	<section class="nd-hero" style="padding-bottom: 32px">
		<div class="nd-hero__text">
			<p class="nd-eyebrow">projects</p>
			<h1 class="nd-h1"><?php echo esc_html( nd_copy( 'nd_projects_title' ) ); ?><br><span class="nd-hl"><?php echo esc_html( nd_copy( 'nd_projects_highlight' ) ); ?></span></h1>
			<p class="nd-lede"><?php echo esc_html( nd_copy( 'nd_projects_lede' ) ); ?></p>
		</div>
		<?php nd_diver( 'projects', 'Illustration of Nathan as a deep-sea diver, holding an open treasure chest full of glowing gold', 'nd-anim-soft' ); ?>
	</section>

	<nav class="nd-chips" aria-label="Filter projects">
		<a class="nd-chip" href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"<?php echo $nd_current ? '' : ' aria-current="page"'; ?>>All · <?php echo (int) $nd_total; ?></a>
		<?php foreach ( $nd_terms as $t ) : ?>
			<a class="nd-chip" href="<?php echo esc_url( get_term_link( $t ) ); ?>"<?php echo $t->term_id === $nd_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $t->name . ' · ' . $t->count ); ?></a>
		<?php endforeach; ?>
	</nav>

	<div class="nd-grid-2">
		<?php while ( have_posts() ) : the_post(); $d = nd_project(); ?>
			<article class="nd-card nd-story-card">
				<?php nd_slot( get_the_ID(), $d['title'] . ' screenshot' ); ?>
				<div class="nd-story-card__body">
					<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px">
						<?php if ( 'in-progress' === $d['status'] ) : ?>
							<span class="nd-pill nd-pill--progress">In progress</span>
						<?php else : ?>
							<span class="nd-pill">Live</span>
						<?php endif; ?>
						<span class="nd-mono"><?php echo esc_html( $d['category'] ); ?></span>
					</div>
					<div>
						<h2><?php echo esc_html( $d['title'] ); ?></h2>
						<?php if ( $d['collab'] ) : ?><p class="nd-mono" style="font: 14px var(--nd-sans); margin: 4px 0 0"><?php echo esc_html( $d['collab'] ); ?></p><?php endif; ?>
					</div>
					<dl class="nd-dl">
						<dt>Problem</dt><dd><?php echo nl2br( esc_html( $d['problem'] ) ); ?></dd>
						<dt>Built</dt><dd><?php echo nl2br( esc_html( $d['built'] ) ); ?></dd>
						<dt>Learned</dt><dd class="nd-muted"><?php echo nl2br( esc_html( $d['learned'] ) ); ?></dd>
					</dl>
					<p class="nd-mono" style="margin: auto 0 0; padding-top: 6px"><?php echo esc_html( $d['tags'] ); ?></p>
					<?php if ( $d['link_url'] ) : ?><a href="<?php echo esc_url( $d['link_url'] ); ?>" style="font-weight: 500"><?php echo esc_html( ( $d['link_label'] ?: 'Visit' ) . ' ↗' ); ?></a><?php endif; ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>

	<section class="nd-cta">
		<div>
			<h2 class="nd-h2" style="margin-bottom: 6px"><?php echo esc_html( nd_copy( 'nd_projects_cta_title' ) ); ?></h2>
			<p class="nd-muted" style="margin: 0"><?php echo esc_html( nd_copy( 'nd_projects_cta_text' ) ); ?></p>
		</div>
		<a class="nd-btn nd-btn--primary" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Read the posts</a>
	</section>
</main>
<?php
get_footer();
