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
            <?php foreach($gallery as $index => $img): ?>
                <?php
                $img_url = !empty($img['url']) ? $img['url'] : '';
                $img_alt = !empty($img['alt']) ? $img['alt'] : sprintf(__('Galerii pilt %d', 'Ysse'), $index + 1);
                $img_w = !empty($img['width']) ? (int) $img['width'] : 1200;
                $img_h = !empty($img['height']) ? (int) $img['height'] : 800;
                $loading = $index === 0 ? 'eager' : 'lazy';
                ?>
                <li class="slide">
                    <div class="img-container">
                        <?php if ($img_url) : ?>
                            <img
                                src="<?php echo esc_url($img_url); ?>"
                                alt="<?php echo esc_attr($img_alt); ?>"
                                width="<?php echo esc_attr($img_w); ?>"
                                height="<?php echo esc_attr($img_h); ?>"
                                loading="<?php echo esc_attr($loading); ?>"
                                <?php echo $index === 0 ? 'fetchpriority="high"' : ''; ?>
                            />
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        $arrow_back = get_template_directory_uri() . '/img/arrow_back.svg';
        $arrow_next = get_template_directory_uri() . '/img/arrow_next.svg';
        ?>
        <div class="slides-navigation">
            <button type="button" class="gallery-arrow gallery-arrow-prev" aria-label="<?php esc_attr_e('Eelmine pilt', 'Ysse'); ?>" style="background-image: url('<?php echo esc_url($arrow_back); ?>');"></button>
            <button type="button" class="gallery-arrow gallery-arrow-next" aria-label="<?php esc_attr_e('Järgmine pilt', 'Ysse'); ?>" style="background-image: url('<?php echo esc_url($arrow_next); ?>');"></button>
        </div>

        <div class="gallery-pager-wrap">
            <button type="button" class="gallery-pager-arrow gallery-pager-arrow-prev" aria-label="<?php esc_attr_e('Kerige pisipilte tagasi', 'Ysse'); ?>" style="background-image: url('<?php echo esc_url($arrow_back); ?>');"></button>
            <ul id="bx-pager" class="custom-pager">
                <?php $counter = 0; ?>

                <?php foreach($gallery as $img): ?>
                    <li class="slide">
                        <button type="button" class="block" data-slide-index="<?php echo esc_attr($counter); ?>" aria-label="<?php echo esc_attr(sprintf(__('Vaata pilti %d', 'Ysse'), $counter + 1)); ?>">
                            <div class="img-container" style="background-image: url('<?php echo esc_url($img['sizes']['thumbnail']); ?>')">
                            </div>
                        </button>
                    </li>

                    <?php $counter++; ?>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="gallery-pager-arrow gallery-pager-arrow-next" aria-label="<?php esc_attr_e('Kerige pisipilte edasi', 'Ysse'); ?>" style="background-image: url('<?php echo esc_url($arrow_next); ?>');"></button>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
