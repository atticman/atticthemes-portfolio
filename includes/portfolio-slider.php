<?php
/*

Used to output the slider portfolio.

*/
?>
<div class="atp-portfolio-wrapper atp-<?php echo $a['type']; ?>-portfolio <?php echo $a['classes']; ?>" data-type="<?php echo $a['type']; ?>" data-animation="<?php echo $a['animation']; ?>" data-animspeed="<?php echo $a['animspeed']; ?>" data-slidespeed="<?php echo $a['slidespeed']; ?>" data-dircontrols="<?php echo $a['dircontrols']; ?>" data-navcontrols="<?php echo $a['navcontrols']; ?>">
	<ul class="atp-projects">
		<?php foreach ($projects as $project_id) {
			$project = get_post( $project_id );
			$term_names = wp_get_post_terms( $project_id, 'atp_category', array('fields'=>'names') );

			$a['meta'] = get_post_meta( $project_id, '_atp_project_metabox', true );
			$thumb_size = apply_filters('atp_project_thumb_size', 'large', $project_id, $a );

		?><li class="<?php echo implode( ' ', get_post_class('atp-project', $project_id) ); ?>"><div class="atp-project-wrapper"><?php
				$thumb = '<figure class="atp-project-thumb">';
				if( isset($a['meta']['thumb_id']) && !$a['ignorethumb'] ) {
					$thumb .= wp_get_attachment_image( $a['meta']['thumb_id'], $thumb_size );
				} else {
					$thumb .= get_the_post_thumbnail( $project_id, $thumb_size );
				}
				$thumb .= '</figure>';
				echo apply_filters('atp_project_thumb', $thumb, $project_id, $a );

				$details = '<div class="atp-project-details">';

				$details .= '<a href="'.get_permalink($project_id).'" class="atp-project-title">';
				$details .= get_the_title($project_id);
				$details .= '</a>';

				$details .= '<div class="atp-project-categories">';
				foreach ($term_names as $term_name) {
					$details .= '<span class="atp-category">';
					$details .= $term_name;
					$details .= '</span>';
				}
				$details .= '</div>';

				$excerpt =  wp_trim_words( strip_shortcodes( $project->post_content ), 10, '&hellip;');

				if( !empty($excerpt) ) {
					$details .= '<div class="atp-project-excerpt">';
					$details .= $excerpt;
					$details .= '</div>';
				}

				$details .= '</div>';

				echo apply_filters('atp_project_details', $details, $project_id, $a );
			?></div></li>
		<?php } ?>
	</ul>
</div>