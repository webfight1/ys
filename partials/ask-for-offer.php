<?php
$ask_for_offer = get_field('ask_for_offer', 'options');
$contactID = icl_object_id(41);
?>

<?php if($ask_for_offer): ?>
    <div class="ask_for_offer">
        <?php echo ysse_optimize_content_html($ask_for_offer); ?>
        <a class="contact_link" href="<?php echo get_the_permalink($contactID); ?>"><?php _e('Contact us', 'Ysse'); ?></a>
    </div>
<?php endif; ?>