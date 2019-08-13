<p>
    <label><?php _e( 'width', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $width; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][start_width]">px</label>&nbsp;

    <label><?php _e( 'height', 'advanced-ads-corner'  ); ?>
    <input type="number" value="<?php echo $height; ?>" name="advads[placements][<?php
    echo $placement_slug; ?>][options][start_height]">px</label>
</p>
<p class="description"><?php _e( "Ad size when it's not hovered", 'advanced-ads-corner' ); ?></p>