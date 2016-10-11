<?php
/*

Used to output the carousel portfolio.

*/
?>
<div class="atp-portfolio-wrapper atp-<?php echo $a['type']; ?>-portfolio atp-columns-<?php echo $a['columns']; ?> <?php echo $a['nogutter'] ? 'atp-nogutter' : ''; ?> <?php echo $a['classes']; ?>" data-type="<?php echo $a['type']; ?>" data-dircontrols="<?php echo $a['dircontrols']; ?>" data-navcontrols="<?php echo $a['navcontrols']; ?>" data-columns="<?php echo $a['columns']; ?>">
	<div class="grid-sizer"></div>
	<div class="gutter-sizer"></div>

	<ul class="atp-projects">
		<?php foreach ($projects as $project_id) {
			$project = get_post( $project_id );
			$term_names = wp_get_post_terms( $project_id, 'atp_category', array('fields'=>'names') );
			$thumb_size = '';

			$a['meta'] = get_post_meta( $project_id, '_atp_project_metabox', true );
				
			switch ( intval($a['columns']) ) {
				case 1:
					$thumb_size = 'full';
				break;
				case 2:
					$thumb_size = 'large';
				break;
				default:
					$thumb_size = 'medium';
				break;
			}
			$thumb_size = apply_filters('atp_project_thumb_size', $thumb_size, $project_id, $a );

		?><li class="<?php echo implode( ' ', get_post_class('atp-project', $project_id) ); ?>"><div class="atp-project-wrapper"><?php
				$thumb = '<figure class="atp-project-thumb">';
				$thumb .= '<a href="'.get_permalink($project_id).'">';
				if( isset($a['meta']['thumb_id']) && !$a['ignorethumb'] ) {
					$thumb .= wp_get_attachment_image( $a['meta']['thumb_id'], $thumb_size );
				} else {
					$thumb .= get_the_post_thumbnail( $project_id, $thumb_size );
				}
				$thumb .= '</a>';
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

				$details .= '</div>';

				echo apply_filters('atp_project_details', $details, $project_id, $a );
			?></div></li>
		<?php } ?>
	</ul>
</div>