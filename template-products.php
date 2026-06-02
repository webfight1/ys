<?php
/* Template Name: Products Template */
get_header();
?>

<?php
$category = get_field('category');
?>

<div id="content" class="products-page">
    <?php
    $args = array(
        'post_type' => 'products',
        'status' => 'published',
        'tax_query' => array(
            array(
                'taxonomy' => 'products_categories',
                'terms' => $category,
                'field' => 'term_id'
            )
        ),
        'posts_per_page' => -1,
        'suppress_filters' => false
    );

    $category_posts = get_posts($args);
    ?>

    <div class="products d-flex">
        <?php if($category_posts): ?>
            <?php foreach($category_posts as $post): ?>
                <?php
                $icon = get_field('icon', $post->ID);
                $background_image = get_field('background_image', $post->ID);
                $content_post = get_post($post->ID);
                $content = $content_post->post_content;
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);
                $icon_dims = $icon ? ysse_url_image_dimensions($icon, 'full', 80, 80) : null;
                ?>

                <a class="product d-flex justify-content-center align-items-center" href="<?php echo esc_url(get_the_permalink($post->ID)); ?>"<?php echo ysse_style_background_image($background_image); ?>>
                    <div class="product_content">
                        <img src="<?php echo esc_url($icon); ?>" width="<?php echo esc_attr($icon_dims['width']); ?>" height="<?php echo esc_attr($icon_dims['height']); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" loading="lazy" />
                        <span><?php echo get_the_title($post->ID); ?></span>
                    </div>

                    <div class="product_content_hover d-flex justify-content-center align-items-center">
                        <div class="product_hover_container">
                            <img src="<?php echo esc_url($icon); ?>" width="<?php echo esc_attr($icon_dims['width']); ?>" height="<?php echo esc_attr($icon_dims['height']); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" loading="lazy" />
                            <span><?php echo get_the_title($post->ID); ?></span>
                            <?php echo $content; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
