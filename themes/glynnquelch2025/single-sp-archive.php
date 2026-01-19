<?php
/**
 * The template for displaying single sp-archive posts
 *
 * @package GlynnQuelch2025
 */

get_header();

use LottaFramework\Facades\CZ;
use LottaFramework\Utils;

$layout          = 'no-sidebar';
$container_style = 'boxed';

$post_sidebar   = yuki_get_current_post_meta( 'site-sidebar-layout' );
$post_container = yuki_get_current_post_meta( 'site-container-style' );

if ( $post_sidebar && $post_sidebar !== 'default' ) {
	$layout = $post_sidebar;
} else if ( CZ::checked( 'yuki_post_sidebar_section' ) ) {
	$layout = CZ::get( 'yuki_post_sidebar_layout' );
}

if ( $post_container && $post_container !== 'default' ) {
	$container_style = $post_container;
} else {
	$container_style = CZ::get( 'yuki_single_post_container_style' );
}

?>

<?php
/**
 * Hook - yuki_action_before_single_post_container.
 */
do_action( 'yuki_action_before_single_post_container' );
?>

<div class="<?php Utils::the_clsx( yuki_container_css( $layout, $container_style ) ) ?>">
    <div id="content" class="flex-grow max-w-full">
		<?php
		// posts loop
		while ( have_posts() ) {
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<?php
				/**
				 * Hook - yuki_action_before_single_post.
				 */
				do_action( 'yuki_action_before_single_post' );
				?>

				<header class="entry-header">
					<?php
					// Display archive type
					$archive_types = get_the_terms( get_the_ID(), 'archive-type' );
					if ( $archive_types && ! is_wp_error( $archive_types ) ) {
						?>
						<div class="sp-archive-type">
							<?php
							$type_names = array();
							foreach ( $archive_types as $type ) {
								$type_names[] = esc_html( $type->name );
							}
							echo implode( ', ', $type_names );
							?>
						</div>
						<?php
					}
					?>
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>

				<div class="entry-content">
					<?php
					the_content();

					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'yuki' ),
						'after'  => '</div>',
					) );
					?>
				</div>

				<footer class="entry-footer sp-archive-footer">
					
					<?php
					// Get tags
					$tags = get_the_tags();
					if ( $tags && ! is_wp_error( $tags ) ) {
						?>
						<div class="sp-archive-meta sp-archive-tags">
							<span class="meta-label"><?php esc_html_e( 'Tags:', 'yuki' ); ?></span>
							<span class="meta-value">
								<?php
								$tag_links = array();
								foreach ( $tags as $tag ) {
									$tag_links[] = '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a>';
								}
								echo implode( ', ', $tag_links );
								?>
							</span>
						</div>
						<?php
					}

					// Get bands
					$bands = get_the_terms( get_the_ID(), 'sp-band' );
					if ( $bands && ! is_wp_error( $bands ) ) {
						$band_count = count( $bands );
						?>
						<div class="sp-archive-meta sp-archive-bands">
							<?php
							if ( $band_count === 1 ) {
								// Single band - show "More from Band Name" as link to band term, with related posts
								$band = $bands[0];
								
								// Get other posts with this band (excluding current post, max 2)
								$related_posts = get_posts( array(
									'post_type'      => 'sp-archive',
									'posts_per_page' => 2,
									'post__not_in'   => array( get_the_ID() ),
									'tax_query'      => array(
										array(
											'taxonomy' => 'sp-band',
											'field'    => 'term_id',
											'terms'    => $band->term_id,
										),
									),
								) );

								if ( ! empty( $related_posts ) ) {
									?>
									<div class="sp-archive-related-posts">
										<div class="yuki-heading yuki-heading-style-1">
											<h3 class="heading-content uppercase my-gutter">
												<a href="<?php echo esc_url( get_term_link( $band ) ); ?>">
													<?php echo esc_html( sprintf( __( 'More from %s', 'yuki' ), $band->name ) ); ?>
												</a>
											</h3>
										</div>
										<ul class="related-posts-list">
											<?php
											foreach ( $related_posts as $related_post ) {
												?>
												<li class="related-post-item">
													<a href="<?php echo esc_url( get_permalink( $related_post->ID ) ); ?>" class="related-post-link">
														<?php echo esc_html( $related_post->post_title ); ?>
													</a>
												</li>
												<?php
											}
											?>
										</ul>
									</div>
									<?php
								} else {
									// No related posts, just show link to band
									?>
									<span class="meta-value">
										<a href="<?php echo esc_url( get_term_link( $band ) ); ?>">
											<?php echo esc_html( sprintf( __( 'More from %s', 'yuki' ), $band->name ) ); ?>
										</a>
									</span>
									<?php
								}
							} else {
								// Multiple bands - show label and links to all band terms
								?>
								<span class="meta-label"><?php echo esc_html( _n( 'Band:', 'Bands:', $band_count, 'yuki' ) ); ?></span>
								<span class="meta-value">
									<?php
									$band_links = array();
									foreach ( $bands as $band ) {
										$band_links[] = '<a href="' . esc_url( get_term_link( $band ) ) . '">' . esc_html( $band->name ) . '</a>';
									}
									echo implode( ', ', $band_links );
									?>
								</span>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</footer>

				<?php
				/**
				 * Hook - yuki_action_after_single_post.
				 */
				do_action( 'yuki_action_after_single_post' );
				?>

			</article>

			<?php
		}
		?>
    </div>

	<?php
	/**
	 * Hook - yuki_action_sidebar.
	 */
	do_action( 'yuki_action_sidebar', $layout );
	?>
</div>

<?php
get_footer();

