<p>
    <label><?php _e( 'Full width', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $full_width; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][corner_placement][full_width]"> px</label>&nbsp;

    <label><?php _e( 'Full height', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $full_height; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][corner_placement][full_height]"> px</label>
</p>
<p class="description"><?php _e( "Ad size when it's hovered", 'advanced-ads-corner' ); ?></p>