<input class="upload_image" name="advads[placements][<?php _e($placement_slug,'advanced-ads-corner'); ?>][options][cover_background]"
	   type="text" readonly="readonly" value="<?php _e($cover_background,'advanced-ads-corner') ?>"/>
<input type="button" value="Upload" class="button button-primary button-large" onclick="upload_new_img(this)"/>
&nbsp;<a href="javascript:void(0);" onclick="remove_image(this);" class="remove_image">Remove</a>&nbsp;
<p class="description"><?php _e( 'You can set a PNG image that will be cover your AD', 'advanced-ads-corner' ); ?></p>