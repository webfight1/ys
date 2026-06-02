<?php get_header(); ?>

<?php
$title = get_field('title');
$text = get_field('text');
$blocks = get_field('blocks');
$color_card = get_field('color_card');
$color_card_image = get_field('color_card_image');
$color_card_image_id = get_post_meta($post->ID,'color_card_image_zoom', true);
$color_card_image_zoom = $color_card_image_id ? wp_get_attachment_image_src($color_card_image_id, 'full') : false;
$pdf_icon = get_field('pdf_icon', 'options');
$dwg_icon = get_field('dwg_icon', 'options');
$sert_icon = get_field('sert_icon', 'options');
$deklar_icon = get_field('deklar_icon', 'options');
$pdf_icon_dims = $pdf_icon ? ysse_url_image_dimensions($pdf_icon, 'full', 24, 24) : null;
$dwg_icon_dims = $dwg_icon ? ysse_url_image_dimensions($dwg_icon, 'full', 24, 24) : null;
$sert_icon_dims = $sert_icon ? ysse_url_image_dimensions($sert_icon, 'full', 24, 24) : null;
$deklar_icon_dims = $deklar_icon ? ysse_url_image_dimensions($deklar_icon, 'full', 24, 24) : null;
?>

<div id="content" class="single-product">
    <?php if($title): ?>
        <div class="title_container">
            <h1><?php echo $title; ?></h1>
        </div>
    <?php endif; ?>

    <?php if($text): ?>
        <div class="text_container">
            <?php echo ysse_optimize_content_html($text); ?>
        </div>
    <?php endif; ?>

    <?php if($color_card_image && $color_card_image_zoom): ?>
        <?php $color_card_dims = ysse_acf_image_dimensions($color_card_image, 'large'); ?>
        <div class="luup_img">
            <img class="luup" src="<?php echo get_template_directory_uri(); ?>/img/luup.svg" alt="" aria-hidden="true" width="24" height="24" />
        </div>

        <div class="color_card_container">
            <img src="<?php echo esc_url(ysse_acf_image_url($color_card_image, 'large')); ?>" class="zoom" loading="lazy" width="<?php echo esc_attr($color_card_dims['width']); ?>" height="<?php echo esc_attr($color_card_dims['height']); ?>" data-magnify-src="<?php echo esc_url($color_card_image_zoom[0]); ?>" data-magnify-magnifiedwidth="<?php echo esc_attr($color_card_image_zoom[1] * 1.5); ?>" data-magnify-magnifiedheight="<?php echo esc_attr($color_card_image_zoom[2] * 1.5); ?>" alt="<?php echo esc_attr(ysse_acf_image_alt($color_card_image, __('Ysse värvikaart', 'Ysse'))); ?>" />

            <p><?php _e('Ysse color card', 'Ysse'); ?></p>
        </div>
    <?php endif; ?>

    <?php if($blocks): ?>
        <?php $ysse_lcp_gallery_done = false; ?>
        <div class="blocks">
            <?php foreach($blocks as $block): ?>
                <div class="block d-flex flex-wrap">
                    <div class="block_left col-lg-6">
                        <div class="text">
                            <div class="text_container">
                                <?php echo ysse_optimize_content_html($block['text']); ?>

                                <span class="see_more"><?php _e('Vaata edasi', 'Ysse'); ?></span>
                            </div>
                        </div>
                        <div class="left_lower">
                            <?php if($block['lower_title']): ?>
                                <p><?php echo $block['lower_title']; ?></p>
                            <?php endif; ?>

                            <?php if($block['lower_image']): ?>
                                <?php $lower_dims = ysse_acf_image_dimensions($block['lower_image'], 'medium'); ?>
                                <img src="<?php echo esc_url(ysse_acf_image_url($block['lower_image'], 'medium')); ?>" loading="lazy" width="<?php echo esc_attr($lower_dims['width']); ?>" height="<?php echo esc_attr($lower_dims['height']); ?>" alt="<?php echo esc_attr(ysse_acf_image_alt($block['lower_image'])); ?>" />
                            <?php endif; ?>

                            <div class="links d-flex justify-content-center">
                                <?php if($block['pdf_file'] && $pdf_icon): ?>
                                    <a href="<?php echo $block['pdf_file']; ?>" target="_blank" aria-label="<?php esc_attr_e('PDF joonis', 'Ysse'); ?>">
                                        <img src="<?php echo $pdf_icon; ?>" alt="" aria-hidden="true" width="<?php echo esc_attr($pdf_icon_dims['width']); ?>" height="<?php echo esc_attr($pdf_icon_dims['height']); ?>" />
                                        <span><?php _e('PDF JOONIS', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['dwg_file'] && $dwg_icon): ?>
                                    <a href="<?php echo $block['dwg_file']; ?>" target="_blank" aria-label="<?php esc_attr_e('DWG joonis', 'Ysse'); ?>">
                                        <img src="<?php echo $dwg_icon; ?>" alt="" aria-hidden="true" width="<?php echo esc_attr($dwg_icon_dims['width']); ?>" height="<?php echo esc_attr($dwg_icon_dims['height']); ?>" />
                                        <span><?php _e('DWG JOONIS', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['deklar_file'] && $deklar_icon): ?>
                                    <a href="<?php echo $block['pdf_file']; ?>" target="_blank" aria-label="<?php esc_attr_e('Deklaratsioon', 'Ysse'); ?>">
                                        <img src="<?php echo $deklar_icon; ?>" alt="" aria-hidden="true" width="<?php echo esc_attr($deklar_icon_dims['width']); ?>" height="<?php echo esc_attr($deklar_icon_dims['height']); ?>" />
                                        <span><?php _e('DEKLARATSIOON', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['sert_file'] && $sert_icon): ?>
                                    <a href="<?php echo $block['sert_file']; ?>" target="_blank" aria-label="<?php esc_attr_e('Sertifikaat', 'Ysse'); ?>">
                                        <img src="<?php echo $sert_icon; ?>" alt="" aria-hidden="true" width="<?php echo esc_attr($sert_icon_dims['width']); ?>" height="<?php echo esc_attr($sert_icon_dims['height']); ?>" />
                                        <span><?php _e('SERTIFIKAAT', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['velux_file'] && $pdf_icon): ?>
                                    <a href="<?php echo $block['velux_file']; ?>" target="_blank" aria-label="<?php esc_attr_e('Velux tootekataloog', 'Ysse'); ?>">
                                        <img src="<?php echo $pdf_icon; ?>" alt="" aria-hidden="true" width="<?php echo esc_attr($pdf_icon_dims['width']); ?>" height="<?php echo esc_attr($pdf_icon_dims['height']); ?>" />
                                        <span><?php _e('VELUX TOOTEKATALOOG 2019', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['link'] && $block['link_text']): ?>
                                    <a href="<?php echo $block['link']; ?>" target="_blank">
                                        <span><?php echo $block['link_text']; ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        $image = $block['image'];
                        $gallery = $block['block_gallery'];
                    ?>
                    <?php if($gallery): ?>
                        <div class="product-gallery d-flex flex-column justify-content-center align-items-center col-lg-6">
                            <div class="bxslider ysse-product-slider">
                                <?php foreach($gallery as $index => $img): ?>
                                    <?php
                                        $large_url = ysse_acf_image_url($img, 'large');
                                        $dims = ysse_acf_image_dimensions($img, 'large');
                                        $img_alt = ysse_acf_image_alt(
                                            $img,
                                            sprintf(__('Toote pilt %d', 'Ysse'), $index + 1)
                                        );
                                    ?>
                                    <div class="slide">
                                        <div class="img-container">
                                            <img
                                                class="ysse-gallery-img"
                                                src="<?php echo esc_url($large_url); ?>"
                                                width="<?php echo esc_attr($dims['width']); ?>"
                                                height="<?php echo esc_attr($dims['height']); ?>"
                                                alt="<?php echo esc_attr($img_alt); ?>"
                                                loading="eager"
                                                decoding="async"
                                                <?php echo ($index === 0 && !$ysse_lcp_gallery_done) ? 'fetchpriority="high"' : ''; ?>
                                            />
                                        </div>
                                        <?php if ($index === 0 && !$ysse_lcp_gallery_done) { $ysse_lcp_gallery_done = true; } ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="product-gallery-arrow product-gallery-arrow-prev" aria-label="<?php esc_attr_e('Eelmine pilt', 'Ysse'); ?>"></button>
                            <button type="button" class="product-gallery-arrow product-gallery-arrow-next" aria-label="<?php esc_attr_e('Järgmine pilt', 'Ysse'); ?>"></button>

                            <div class="bx-pager product-custom-pager">
                                <?php foreach($gallery as $pager_index => $img): ?>
                                    <?php
                                        $thumb_url = ysse_acf_image_url($img, 'thumbnail');
                                        $pager_label = ysse_acf_image_alt(
                                            $img,
                                            sprintf(__('Vaata pilti %d', 'Ysse'), $pager_index + 1)
                                        );
                                    ?>
                                    <div class="slide">
                                        <button type="button" class="block" aria-label="<?php echo esc_attr($pager_label); ?>">
                                            <div class="img-container" style="background-image: url('<?php echo esc_url($thumb_url); ?>')"></div>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php elseif($image): ?>
                            <?php $block_image_dims = ysse_acf_image_dimensions($image, 'medium'); ?>
                            <div class="image d-flex justify-content-center align-items-center col-lg-6">
                                <img src="<?php echo esc_url(ysse_acf_image_url($image, 'medium')); ?>" loading="lazy" width="<?php echo esc_attr($block_image_dims['width']); ?>" height="<?php echo esc_attr($block_image_dims['height']); ?>" alt="<?php echo esc_attr(ysse_acf_image_alt($image, $block['lower_title'] ?: ($title ?: get_the_title()))); ?>" />
                            </div>
                        <?php endif; ?>
                    </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
