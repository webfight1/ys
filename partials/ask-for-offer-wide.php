<?php
$left = get_field('ask_for_offer_wide_left', 'options');
$right = get_field('ask_for_offer_wide_right', 'options');
$contactID = icl_object_id(41);
?>

<?php if($left && $right): ?>
    <div class="ask_for_offer_wide">
        <div class="ask_for_offer_wide_container d-flex justify-content-between">
            <div class="ask_for_offer_wide_left">
                <?php echo $left; ?>
                <a href="<?php echo get_the_permalink($contactID); ?>"><?php _e('Send e-mail', 'Ysse'); ?></a>
            </div>

            <div class="ask_for_offer_wide_right">
                <?php echo $right; ?>
            </div>
        </div>
    </div>
<?php endif; ?>