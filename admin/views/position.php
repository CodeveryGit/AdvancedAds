<?php
	$options = isset( $placement['options']['corner_placement']['sticky'] ) ? $placement['options']['corner_placement']['sticky'] : array();

	$enabled   = isset( $options['enabled'] ) ? $options['enabled'] : false;
	$assistant = isset( $options['assistant'] ) ? $options['assistant'] : 'topright';
	$type      = isset( $options['type'] ) ? $options['type'] : 'assistant';
	$width     = isset( $options['position']['width'] ) ? absint( $options['position']['width'] ) : 0;
	$height    = isset( $options['position']['height'] ) ? absint( $options['position']['height'] ) : 0;

	$option_name = "advads[placements][$placement_slug][options][corner_placement][sticky]";
	if (! isset($corner_class)){
		$corner_class = Advanced_Ads_Corner::get_corner_class();
	}
?>

<div>
	<div class="<?php echo $corner_class; ?>-aa-position">
		
		<div class="<?php echo $corner_class; ?>-assistant-wrapper">
			<div class="advads-sticky-assistant" id="<?php echo $corner_class; ?>-ads-type-assistant-inputs-<?php echo $placement_slug; ?>">
				<table>
					<tr>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'top left', 'advanced-ads-corner' ); ?>" value="topleft" <?php checked( $assistant, 'topleft' ); ?>/></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'top center', 'advanced-ads-corner' ); ?>" value="topcenter" <?php checked( $assistant, 'topcenter' ); ?> disabled /></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'top right', 'advanced-ads-corner' ); ?>" value="topright" <?php checked( $assistant, 'topright' ); ?>/></td>
					</tr>
					<tr>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'center left', 'advanced-ads-corner' ); ?>" value="centerleft" <?php checked( $assistant, 'centerleft' ); ?> disabled /></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'center', 'advanced-ads-corner' ); ?>" value="center" <?php checked( $assistant, 'center' ); ?> disabled /></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'center right', 'advanced-ads-corner' ); ?>" value="centerright" <?php checked( $assistant, 'centerright' ); ?> disabled /></td>
					</tr>
					<tr>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'bottom left', 'advanced-ads-corner' ); ?>" value="bottomleft" <?php checked( $assistant, 'bottomleft' ); ?>/></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'bottom center', 'advanced-ads-corner' ); ?>" value="bottomcenter" <?php checked( $assistant, 'bottomcenter' ); ?> disabled /></td>
						<td><input type="radio" name="<?php echo $option_name; ?>[assistant]" title="<?php _e( 'bottom right', 'advanced-ads-corner' ); ?>" value="bottomright" <?php checked( $assistant, 'bottomright' ); ?>/></td>
					</tr>
				</table>

				
				<div <?php if ( ( ! $width && ! $height )  ): ?> style="display:none;" <?php endif; ?>>
					<br/>
					<p class="advads-error-message"><?php _e( 'These settings are deprecated. Please, set width and height for the ad itself.', 'advanced-ads-corner' ); ?></p>
					<p class="description"><?php _e( 'Enter banner width and height to correctly center the ad.', 'advanced-ads-corner' ); ?></p>
					<label><?php _e( 'banner width', 'advanced-ads-corner' ); ?>
						<input type="number" name="<?php echo $option_name; ?>[position][width]" title="<?php _e( 'banner width', 'advanced-ads-corner' ); ?>" value="<?php echo $width; ?>"/>px
					</label>, 
					<label><?php _e( 'banner height', 'advanced-ads-corner' ); ?>
						<input type="number" name="<?php echo $option_name; ?>[position][height]" title="<?php _e( 'banner height', 'advanced-ads-corner' ); ?>" value="<?php echo $height; ?>"/>px
					</label>
				</div>

			</div>
			<p class="description"><?php _e( 'Choose a position on the screen.', 'advanced-ads-corner' ); ?></p>

		</div>

		<div class='clear'></div>
	</div>
</div>
