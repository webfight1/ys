<?php
/* Template Name: Company Template */
get_header();
?>

<?php
$header_image = get_field('header_image');
$left_image = get_field('left_image');
$windows_and_doors_title = get_field('windows_and_doors_title');
$windows_and_doors = get_field('windows_and_doors');
$factory = get_field('factory');
?>

<div id="content" class="company-page">
    <div class="content_header d-flex align-items-center" style="background-image: url(<?php if($header_image) echo $header_image; ?>);">
        <div class="content_header_container">
            <h1><?php the_title(); ?></h1>
        </div>
    </div>

    <div class="content_body d-flex flex-wrap">
        <div class="left_image col-lg-6" style="background-image: url(<?php if($left_image) echo $left_image; ?>);">
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
                    <div class="block">
                        <div class="img_container">
                            <img class="svg" src="<?php echo $block['image']['url']; ?>" />
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