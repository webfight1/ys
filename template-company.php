<?php
/* Template Name: Company Template */
get_header();
?>

<?php
$header_image = ysse_get_page_header_image_url('large');
$left_image = get_field('left_image');
$windows_and_doors_title = get_field('windows_and_doors_title');
$windows_and_doors = get_field('windows_and_doors');
$factory = get_field('factory');
?>

<div id="content" class="company-page">
    <div class="content_header d-flex align-items-center"<?php echo ysse_style_background_image($header_image); ?>>
        <div class="content_header_container">
            <h1><?php the_title(); ?></h1>
        </div>
    </div>

    <div class="content_body d-flex flex-wrap">
        <div class="left_image col-lg-6"<?php echo ysse_style_background_image($left_image); ?>>
        </div>
        <div class="page_content col-lg-6">
            <div class="page_content_container">
                <?php the_content(); ?>
            </div>
        </div>
    </div>

    <?php if($windows_and_doors): ?>
        <div class="windows_and_doors">
            <h2><?php echo $windows_and_doors_title; ?></h2>

            <div class="blocks d-flex justify-content-center">
                <?php foreach($windows_and_doors as $block): ?>
                    <?php $block_dims = ysse_acf_image_dimensions($block['image'], 'full', 100, 100); ?>
                    <div class="block">
                        <div class="img_container">
                            <img class="svg" src="<?php echo esc_url(ysse_webp_url($block['image']['url'])); ?>" width="<?php echo esc_attr($block_dims['width']); ?>" height="<?php echo esc_attr($block_dims['height']); ?>" alt="" />
                        </div>
                        <p><?php echo $block['text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($factory): ?>
        <div class="factory">
            <?php echo $factory; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
