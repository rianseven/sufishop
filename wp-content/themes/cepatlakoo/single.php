<?php
/**
 * The template for displaying single part.
 *
 * @package WordPress
 * @subpackage CepatLakoo
 * @since CepatLakoo 1.0.0
 */
?>
<?php get_header(); ?>

<div id="main" <?php echo cepatlakoo_fb_pixel_data(); ?> >
	<div id="maincontent">
		<div class="container clearfix">
			<?php get_template_part( 'includes/breadcrumb' ); ?>

			<div id="contentarea">
				<div class="postlist">
				<?php
					// Load posts
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							get_template_part( 'content' );
							get_template_part( 'includes/share-buttons' );
							get_template_part( 'includes/post-nav' );
							comments_template( '', true );
						}
					} else {
						esc_html_e( 'The page you\'re looking for is not available. The page may have been deleted or unpublished.', 'cepatlakoo' );
					}
					wp_reset_postdata();
				?>
				</div>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>