<?php
/**
 * Posts index (the "Posts" page) and category archives. Design: docs/design/PostsIndex.dc.html
 * Filter chips link to category archives.
 */
get_header();
nd_decor( 'jelly', 'top: 980px; right: 2.5%;' );
nd_decor( 'school', 'top: 1700px; left: 2%;' );

$nd_posts_url = get_permalink( get_option( 'page_for_posts' ) );
$nd_current   = is_category() ? get_queried_object_id() : 0;
$nd_first     = true;
?>
<main id="main" class="nd-wrap">
	<section class="nd-hero" style="padding-bottom: 32px">
		<div class="nd-hero__text">
			<p class="nd-eyebrow">posts</p>
			<h1 class="nd-h1"><?php echo esc_html( nd_copy( 'nd_posts_title' ) ); ?><br><span class="nd-hl"><?php echo esc_html( nd_copy( 'nd_posts_highlight' ) ); ?></span></h1>
			<p class="nd-lede"><?php echo esc_html( nd_copy( 'nd_posts_lede' ) ); ?></p>
		</div>
		<?php nd_diver( 'posts', 'Illustration of Nathan as a deep-sea diver, holding up a message in a bottle', 'nd-anim-soft' ); ?>
	</section>

	<nav class="nd-chips" aria-label="Filter posts">
		<a class="nd-chip" href="<?php echo esc_url( $nd_posts_url ); ?>"<?php echo $nd_current ? '' : ' aria-current="page"'; ?>>All</a>
		<?php foreach ( get_categories( array( 'hide_empty' => true ) ) as $c ) : ?>
			<a class="nd-chip" href="<?php echo esc_url( get_category_link( $c ) ); ?>"<?php echo $c->term_id === $nd_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $c->name ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php if ( ! have_posts() ) : ?>
		<p class="nd-muted" style="padding: 8px 0 64px"><?php echo esc_html( nd_copy( 'nd_posts_empty' ) ); ?></p>
	<?php endif; ?>

	<?php if ( have_posts() ) : the_post(); ?>
		<a class="nd-featured" href="<?php the_permalink(); ?>">
			<?php nd_slot( get_the_ID(), 'Cover image' ); ?>
			<div class="nd-featured__body">
				<p class="nd-mono" style="color: var(--nd-accent)">latest · <?php echo esc_html( nd_post_kind() ); ?></p>
				<h2><?php the_title(); ?></h2>
				<p class="nd-muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<p style="font-size: 14px; color: var(--nd-faint)"><?php echo esc_html( get_the_date() . ' · ' . nd_reading_time() ); ?></p>
			</div>
		</a>
	<?php endif; ?>

	<?php if ( have_posts() ) : // The rest, after the featured one. ?>
		<ol class="nd-rows">
			<?php while ( have_posts() ) : the_post(); ?>
				<li>
					<a class="nd-row" href="<?php the_permalink(); ?>">
						<span class="nd-row__date"><?php echo esc_html( get_the_date() ); ?></span>
						<span class="nd-row__main">
							<span class="nd-row__title"><?php the_title(); ?></span>
							<span class="nd-muted" style="font-size: 15px"><?php echo esc_html( get_the_excerpt() ); ?></span>
						</span>
						<span class="nd-row__type"><?php echo esc_html( nd_post_kind() ); ?></span>
					</a>
				</li>
			<?php endwhile; ?>
		</ol>
	<?php endif; ?>
	<?php the_posts_pagination(); ?>
</main>
<?php
get_footer();
