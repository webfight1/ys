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
?>

<div id="content" class="single-product">
    <?php if($title): ?>
        <div class="title_container">
            <h1><?php echo $title; ?></h1>
        </div>
    <?php endif; ?>

    <?php if($text): ?>
        <div class="text_container">
            <?php echo $text; ?>
        </div>
    <?php endif; ?>

    <?php if($color_card_image && $color_card_image_zoom): ?>
        <div class="luup_img">
            <img class="luup" src="<?php echo get_template_directory_uri(); ?>/img/luup.svg" />
        </div>

        <div class="color_card_container">
            <img src="<?php echo $color_card_image; ?>" class="zoom" data-magnify-src="<?php echo $color_card_image_zoom[0]; ?>" data-magnify-magnifiedwidth="<?php echo $color_card_image_zoom[1] * 1.5; ?>" data-magnify-magnifiedheight="<?php echo $color_card_image_zoom[2] * 1.5; ?>" />

            <p><?php _e('Ysse color card', 'Ysse'); ?></p>
        </div>
    <?php endif; ?>

    <?php if($blocks): ?>
        <div class="blocks">
            <?php foreach($blocks as $block): ?>
                <div class="block d-flex flex-wrap">
                    <div class="block_left col-lg-6">
                        <div class="text">
                            <div class="text_container">
                                <?php echo $block['text']; ?>

                                <span class="see_more"><?php _e('Vaata edasi', 'Ysse'); ?></span>
                            </div>
                        </div>
                        <div class="left_lower">
                            <?php if($block['lower_title']): ?>
                                <p><?php echo $block['lower_title']; ?></p>
                            <?php endif; ?>

                            <?php if($block['lower_image']): ?>
                                <img src="<?php echo $block['lower_image']; ?>" />
                            <?php endif; ?>

                            <div class="links d-flex justify-content-center">
                                <?php if($block['pdf_file'] && $pdf_icon): ?>
                                    <a href="<?php echo $block['pdf_file']; ?>" target="_blank">
                                        <img src="<?php echo $pdf_icon; ?>" />
                                        <span><?php _e('PDF JOONIS', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['dwg_file'] && $dwg_icon): ?>
                                    <a href="<?php echo $block['dwg_file']; ?>" target="_blank">
                                        <img src="<?php echo $dwg_icon; ?>" />
                                        <span><?php _e('DWG JOONIS', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['deklar_file'] && $deklar_icon): ?>
                                    <a href="<?php echo $block['pdf_file']; ?>" target="_blank">
                                        <img src="<?php echo $deklar_icon; ?>" />
                                        <span><?php _e('DEKLARATSIOON', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['sert_file'] && $sert_icon): ?>
                                    <a href="<?php echo $block['sert_file']; ?>" target="_blank">
                                        <img src="<?php echo $sert_icon; ?>" />
                                        <span><?php _e('SERTIFIKAAT', 'Ysse'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if($block['velux_file'] && $pdf_icon): ?>
                                    <a href="<?php echo $block['velux_file']; ?>" target="_blank">
                                        <img src="<?php echo $pdf_icon; ?>" />
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
                                <?php $counter = 0; ?>
                                <?php foreach($gallery as $img): ?>
                                    <div class="slide">
                                        <div class="img-container" style="background-image: url('<?php echo $img['url']; ?>')"></div>
                                    </div>
                                    <?php $counter++; ?>
                                <?php endforeach; ?>
                            </div>

                            <div class="prev slick-arrow"></div>
                            <div class="next slick-arrow"></div>

                            <div id="bx-pager" class="product-custom-pager">
                                <?php $counter = 0; ?>
                                <?php foreach($gallery as $img): ?>
                                    <div class="slide">
                                        <a class="block" data-slide-index="<?php echo $counter; ?>">
                                            <div class="img-container" style="background-image: url('<?php echo $img['url']; ?>')"></div>
                                        </a>
                                    </div>
                                    <?php $counter++; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php elseif($image): ?>
                            <div class="image d-flex justify-content-center align-items-center col-lg-6">
                                <img src="<?php echo $image; ?>" />
                            </div>
                        <?php endif; ?>
                    </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>