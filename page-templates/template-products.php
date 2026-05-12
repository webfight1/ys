<?php
/* Template Name: Products Template */
get_header();
?>

<?php $category = get_field('category'); ?>

<div id="content" class="products-page">

    <?php /* === Page Content ülal (H1, sissejuhatus) === */ ?>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <header class="products-hub-header">
            <h1><?php the_title(); ?></h1>
        </header>

        <?php if (get_the_content()) : ?>
            <div class="products-hub-intro">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    <?php endwhile; endif; ?>

    <?php
    $args = array(
        'post_type'        => 'products',
        'status'           => 'published',
        'tax_query'        => array(
            array(
                'taxonomy' => 'products_categories',
                'terms'    => $category,
                'field'    => 'term_id'
            )
        ),
        'posts_per_page'   => -1,
        'suppress_filters' => false
    );

    $category_posts = get_posts($args);
    ?>

    <?php /* === Olemasolev tooteplokk === */ ?>
    <div class="products d-flex">
        <?php if ($category_posts) : ?>
            <?php foreach ($category_posts as $post) : ?>
                <?php
                $icon             = get_field('icon', $post->ID);
                $background_image = get_field('background_image', $post->ID);
                $content_post     = get_post($post->ID);
                $content          = $content_post->post_content;
                $content          = apply_filters('the_content', $content);
                $content          = str_replace(']]>', ']]&gt;', $content);
                ?>

                <a class="product d-flex justify-content-center align-items-center"
                   href="<?php echo get_the_permalink($post->ID); ?>"
                   style="background-image: url(<?php echo $background_image; ?>);">
                    <div class="product_content">
                        <img src="<?php echo $icon; ?>" />
                        <span><?php echo get_the_title($post->ID); ?></span>
                    </div>

                    <div class="product_content_hover d-flex justify-content-center align-items-center">
                        <div class="product_hover_container">
                            <img src="<?php echo $icon; ?>" />
                            <span><?php echo get_the_title($post->ID); ?></span>
                            <?php echo $content; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php /* === Koht alumise sisu jaoks (FAQ, CTA) === */ ?>
    <?php if (function_exists('get_field')) : ?>
        <?php
        $hub_below_content = get_field('hub_below_content');
        if ($hub_below_content) :
        ?>
            <div class="products-hub-below">
                <?php echo apply_filters('the_content', $hub_below_content); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php get_footer(); ?>