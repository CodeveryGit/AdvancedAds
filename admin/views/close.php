<?php $option_name = "advads[placements][$placement_slug][options][corner_placement][close]"; ?>
<div id="advads-close-<?php echo $placement_slug; ?>">
    <div>
        <label><input class="corner-close" type="radio" name="<?php echo $option_name; ?>[when_to]" value="opened" <?php checked($when_to_close, 'opened'); ?>/><?php _e( 'after opened first', 'advanced-ads-corner' ); ?></label>
        <label><input class="corner-close" type="radio" name="<?php echo $option_name; ?>[when_to]" value="clicked" <?php checked($when_to_close, 'clicked'); ?>/><?php _e( 'after clicked once', 'advanced-ads-corner' ); ?></label>
        <label><input class="corner-close" type="radio" name="<?php echo $option_name; ?>[when_to]" value="never" <?php checked($when_to_close, 'never'); ?>/><?php _e( 'never', 'advanced-ads-corner' ); ?></label>
    </div>
    <div id="advads-close-for-<?php echo $placement_slug; ?>" class="corner-close-for" <?php if ($when_to_close == 'never') : ?> style="display:none;"<?php endif; ?>>
        <p><?php _e( 'close the ad for …', 'advanced-ads-corner' ); ?></p>
        <input type="number" name="<?php echo $option_name; ?>[for_how_long]" value="<?php echo $close_for; ?>"/>
        <span class="description"><?php _e( 'days, 0 = after current session', 'advanced-ads-corner' ); ?></span>
    </div>
</div>