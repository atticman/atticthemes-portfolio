<!-- <input name="<?php echo $field_namespace; ?>[blah]" /> -->
<div class="atp-metabox-wrapper">
	<?php $thumb_id = isset($meta['thumb_id']) ? $meta['thumb_id'] : ''; ?>

	<?php do_action( 'atp_meta_before_thumb', $meta, $field_namespace ); ?>

	<div class="atp-meta-option atp-image <?php echo !empty($thumb_id) ? 'atp-has-image' : ''; ?>" data-library="image">
		<p class="atp-option-title"><?php _e('Project Thumbnail', 'atticthemes_portfolio'); ?></p>
		<em class="atp-option-description howto"><?php _e('Used only for the thumbnail in the portfolio pages. If not selected featured image will be used instead.', 'atticthemes_portfolio'); ?></em>
		<div class="atp-meta-option-thumb-wrapper">
			<span class="atp-remove-thumb fa fa-times"></span>
			<a class="atp-meta-option-thumb" title="<?php _e('Select Project Thumbnail', 'atticthemes_portfolio'); ?>">
				<?php echo wp_get_attachment_image( $thumb_id, array(300, 9999) ); ?>
			</a>
		</div>
		<input type="hidden" name="<?php echo $field_namespace; ?>[thumb_id]" value="<?php echo $thumb_id; ?>"/>
	</div>

	<?php do_action( 'atp_meta_after_thumb', $meta, $field_namespace ); ?>
</div>