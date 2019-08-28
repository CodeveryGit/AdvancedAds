<?php $option_name = "advads[placements][$placement_slug][options][corner_placement][close]"; ?>
<div id="advads-close-<?php echo $placement_slug; ?>">
    <div>
        <label><input type="radio" name="<?php echo $option_name; ?>" value="opened" <?php checked($close, 'opened'); ?>/><?php _e( 'after opened first', 'advanced-ads-corner' ); ?></label>
        <label><input type="radio" name="<?php echo $option_name; ?>" value="clicked" <?php checked($close, 'clicked'); ?>/><?php _e( 'after clicked once', 'advanced-ads-corner' ); ?></label>
        <label><input type="radio" name="<?php echo $option_name; ?>" value="never" <?php checked($close, 'never'); ?>/><?php _e( 'never', 'advanced-ads-corner' ); ?></label>
    </div>
</div>