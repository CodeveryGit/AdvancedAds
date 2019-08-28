<p>
    <label><?php _e( 'Start width', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $width; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][corner_placement][start_width]"> px</label>&nbsp;

    <label><?php _e( 'Start height', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $height; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][corner_placement][start_height]"> px</label>
</p>
<p class="description"><?php _e( "Ad size when it's not hovered", 'advanced-ads-corner' ); ?></p>