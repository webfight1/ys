<?php get_header(); ?>

<?php
$header_image = ysse_get_page_header_image_url('large');
$header_subtitle = get_field('header_subtitle');
$show_block = get_field('show_ask_windows_and_doors_offer_block');
$show_block_wide = get_field('show_ask_windows_and_doors_offer_block_wide');
?>

<div id="content" class="default-post">
    <div class="content_header d-flex align-items-center"<?php echo ysse_style_background_image($header_image); ?>>
        <div class="content_header_container">
            <h1><?php the_title(); ?></h1>
            
            <?php if($header_subtitle): ?>
                <p><?php echo $header_subtitle; ?></p>
            <?php endif; ?>
        </div>

        <div class="scroll_down">
            <div class="scroll_down_btn">
                <p><?php _e('Read more', 'Ysse'); ?></p>
            </div>
        </div>
    </div>

    <div class="post_content">
        <div class="post_content_container">
            <?php the_content(); ?>
        </div>
    </div>

    <?php if($show_block): ?>
        <?php get_template_part( 'partials/ask-for-offer', 'offer' ); ?>
    <?php endif; ?>

    <?php if($show_block_wide): ?>
		<?php get_template_part( 'partials/ask-for-offer-wide', 'offer' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
