<?php $color = $peel_color != '' ? $peel_color : '#5d5d5d'; ?>
<input class="peel-color-picker" name="advads[placements][<?php _e($placement_slug,'advanced-ads-corner'); ?>][options][corner_placement][peel_color]"
       type="text" value="<?php echo $color ?>" />
<p class="description"><?php _e( 'Corner Peel color', 'advanced-ads-corner' ); ?></p>