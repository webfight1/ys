<?php 
/* Template Name: Gallery Template */
get_header();
?>

<?php
$gallery = get_field('gallery');
?>


<div id="content" class="gallery-page">
    <?php if($gallery): ?>
        <ul class="bxslider ysse-slider">
            <?php $counter = 0; ?>

            <?php foreach($gallery as $img): ?>
                <li class="slide">
                    <div class="img-container" style="background-image: url('<?php echo $img['url']; ?>')">
                    </div>
                </li>
                
                <?php $counter++; ?>
            <?php endforeach; ?>
        </ul>

        <div class="slides-navigation">
            <div class="prev"></div>
            <div class="next"></div>
        </div>

        <ul id="bx-pager" class="custom-pager">
            <?php $counter = 0; ?>

            <?php foreach($gallery as $img): ?>
                <li class="slide">
                    <a class="block" data-slide-index="<?php echo $counter; ?>">
                        <div class="img-container" style="background-image: url('<?php echo $img['sizes']['thumbnail']; ?>')">
                        </div>
                    </a>
                </li>

                <?php $counter++; ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php get_footer(); ?>