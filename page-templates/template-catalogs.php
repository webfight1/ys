<?php
/* Template Name: Catalogs Template */
get_header();
?>

<?php 
$catalogs = get_field('catalogs');
$show_block = get_field('show_ask_windows_and_doors_offer_block');
$show_block_wide = get_field('show_ask_windows_and_doors_offer_block_wide');
?>

<div id="content" class="default-page catalog-page">
	<div class="default_page_header	d-flex justify-content-center align-items-center">
		<div class="title_container">
			<h1><?php the_title(); ?></h1>

            <?php if(get_the_content() && get_the_content() !== ''): ?>
                <div class="post_content_container">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
		</div>
	</div>

	<?php if($catalogs): ?>
		<div class="catalogs_container container">
			<div class="row">
				<?php foreach($catalogs as $catalog): ?>
					<div class="col-sm-6 col-12 d-flex flex-column">
                        <?php if($catalog['image']): ?>
							<div class="catalog_image" style="background-image: url(<?php echo $catalog['image']; ?>);">
							</div>
						<?php endif; ?>

						<?php if($catalog['title']): ?>
							<div class="catalog_title">
								<h2><?php echo $catalog['title']; ?></h2>
							</div>
						<?php endif; ?>
					
						<?php if($catalog['text']): ?>
							<div class="catalog_text">
								<?php echo $catalog['text']; ?>
							</div>
						<?php endif; ?>

                        <?php if($catalog['file']): ?>
							<div class="catalog_file">
								<a class="button" href="<?php echo $catalog['file']; ?>"><?php _e('Download', 'Ysse'); ?></a>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

    <?php if($show_block): ?>
		<?php get_template_part( 'partials/ask-for-offer', 'offer' ); ?>
	<?php endif; ?>

	<?php if($show_block_wide): ?>
		<?php get_template_part( 'partials/ask-for-offer-wide', 'offer' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
