<?php
/* Template Name: Contact Template */
get_header();
?>

<?php
$header_image = get_field('header_image');
$contact_form_title = get_field('contact_form_title');
$contact_form = get_field('contact_form');
$sales_offices = get_field('sales_offices');
?>

<div id="content" class="contact-page">
    <div class="content_header d-flex align-items-center" style="background-image: url(<?php if($header_image) echo $header_image; ?>);">
        <div class="content_header_container">
            <?php if($contact_form_title && $contact_form): ?>
                <h1><?php echo $contact_form_title; ?></h1>
                <?php echo do_shortcode($contact_form); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if($sales_offices): ?>
        <div class="offices">
            <?php foreach($sales_offices as $block): ?>
                <div class="office d-flex fle-wrap">
                    <div class="image col-md-6" style="background-image: url(<?php if($block['image']) echo $block['image']; ?>);">
                    </div>
                    <div class="text_container col-md-6">
                        <div class="text">    
                            <?php echo $block['text']; ?>

                            <?php if($block['google_maps_url']): ?>
                                <a class="button" href="<?php echo $block['google_maps_url']; ?>" target="_blank"><?php _e('Open google maps', 'Ysse'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="content_body">
        <div class="page_content">
            <?php the_content(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>