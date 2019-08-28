<?php $option_name = "advads[placements][$placement_slug][options][corner_placement][how_to_show]"; ?>
<div id="advads-how-to-show-<?php echo $placement_slug; ?>">
    <div>
        <label><input type="radio" name="<?php echo $option_name; ?>" value="triangle" <?php checked($how_to_show, 'triangle'); ?>/><?php _e( 'Only in the triangle', 'advanced-ads-corner' ); ?></label>
        <label><input type="radio" name="<?php echo $option_name; ?>" value="rectangle" <?php checked($how_to_show, 'rectangle'); ?>/><?php _e( 'In whole rectangle', 'advanced-ads-corner' ); ?></label>
    </div>
</div>