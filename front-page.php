<?php get_header(); ?>

<?php
$header_image = ysse_get_page_header_image_url('large');
$top_blocks = get_field('top_blocks');
$show_block = get_field('show_ask_windows_and_doors_offer_block');
$show_block_wide = get_field('show_ask_windows_and_doors_offer_block_wide');

$args = array(
    'post_type' => 'products',
    'status' => 'published',
    'posts_per_page' => -1,
    'suppress_filters' => false
);

$products = get_posts($args);
?>

<div id="content" class="front-page">
    <div class="content_header d-flex align-items-center"<?php echo ysse_style_background_image($header_image); ?>>
        <div class="content_header_container"<?php echo ysse_style_background_image($header_image); ?>>
            <?php the_content(); ?>
        </div>

        <?php if($products): ?>
            <div class="header_products d-flex justify-content-center">
                <?php foreach($products as $product): ?>
                    <?php
                    $icon = get_field('icon', $product->ID);
                    $show_in_front_page = get_field('show_in_front_page', $product->ID);
                    ?>

                    <?php if($show_in_front_page): ?>
                        <?php
                        $product_slug = get_post_field('post_name', $product->ID);
                        $product_url = home_url('/' . $product_slug . '/');
                        $icon_dims = $icon ? ysse_url_image_dimensions($icon, 'full', 60, 60) : null;
                        ?>
                        <a class="product" href="<?php echo esc_url($product_url); ?>">
                            <div class="image_containter d-flex justify-content-center align-items-center">
                                <img src="<?php echo esc_url($icon); ?>" width="<?php echo esc_attr($icon_dims['width']); ?>" height="<?php echo esc_attr($icon_dims['height']); ?>" alt="<?php echo esc_attr(get_the_title($product->ID)); ?>" />
                            </div>
                            <span><?php echo get_the_title($product->ID); ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="content_body">
        <?php if($top_blocks): ?>
            <div class="top_blocks d-flex justify-content-center">

                <?php $counter = 0; ?>
                <?php foreach($top_blocks as $block): ?>
                    <?php
                    if($counter == 2):
                        $classes = 'bigger';
                        $counter = 0;
                    else:
                        $classes = '';
                        $counter++;
                    endif;
                    ?>
                    
                    <div class="top_block <?php echo $classes; ?>">
                        <?php if($block['image']): ?>
                            <?php $top_block_dims = ysse_url_image_dimensions($block['image'], 'large', 400, 300); ?>
                            <div class="img_container">
                                <img src="<?php echo esc_url($block['image']); ?>" width="<?php echo esc_attr($top_block_dims['width']); ?>" height="<?php echo esc_attr($top_block_dims['height']); ?>" alt="" aria-hidden="true" />
                            </div>
                        <?php endif; ?>

                        <?php if($block['text']): ?>
                            <?php echo ysse_optimize_content_html($block['text']); ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if($show_block): ?>
            <?php get_template_part( 'partials/ask-for-offer', 'offer' ); ?>
        <?php endif; ?>

        <?php if($show_block_wide): ?>
            <?php get_template_part( 'partials/ask-for-offer-wide', 'offer' ); ?>
        <?php endif; ?>

        <div class="front_products">
            <?php
            $front_products = get_field('front_products');
            ?>

            <?php if($front_products): ?>
                <?php foreach($front_products as $product_block): ?>
                    <?php
                    $title = $product_block['title'];
                    $content = $product_block['content'];
                    $image = $product_block['image'];
                    $link = $product_block['url'];
                    ?>

                    <div class="front_product d-flex flex-wrap">
                        <div class="image col-md-6"<?php echo ysse_style_background_image($image); ?>>
                        </div>
                        <div class="text_container col-md-6">
                            <div class="text">    
                                <h2><?php echo $title; ?></h2>
                                <?php echo $content; ?>

                                <a class="button" href="<?php echo esc_url($link); ?>"><?php _e('See more', 'Ysse'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php get_footer(); ?>
