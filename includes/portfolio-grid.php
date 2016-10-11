<?php
/*

Used to output the grid portfolio.

*/
?>

<div class="atp-portfolio-wrapper atp-<?php echo $a['type']; ?>-portfolio atp-columns-<?php echo $a['columns']; ?> <?php echo $a['nogutter'] ? 'atp-nogutter' : ''; ?> <?php echo $a['classes']; ?>" data-type="<?php echo $a['type']; ?>">
	
	<?php if( !empty($a['categories']) && $a['filters'] ) { ?>
		<?php do_action( 'atp_before_filter_container', $a ); ?>

		<div class="atp-filter-wrapper">
			<?php do_action( 'atp_before_filters', $a ); ?>

			<ul class="atp-filters">
			<?php if( !empty($a['showall']) ) { ?>
				<li class="atp-filter" data-filter="all" >
					<a class="atp-filter-label"><?php echo $a['showall']; ?></a>
				</li>
			<?php } ?>

			<?php foreach ($categories as $category_id) { 
				$category = get_term_by( 'id', $category_id, 'atp_category' );
				?><li class="atp-filter" data-filter=".<?php echo $category->slug; ?>" >
					<a class="atp-filter-label"><?php echo isset($category->name) ? $category->name : ''; ?></a>
				</li><?php
			} ?>
			</ul>

			<?php do_action( 'atp_after_filters', $a ); ?>
		</div>

		<?php do_action( 'atp_after_filter_container', $a ); ?>
	<?php } ?>

	<div class="atp-projects-wrapper">
		<ul class="atp-projects">
			<li class="grid-sizer"></li>
			<li class="gutter-sizer"></li>

			<?php $a['loop'] = array(
				'count' => count($projects),
				'step' => 0
			); ?>

			<?php foreach ($projects as $project_id) {
				$project = get_post( $project_id );

				$term_slugs_arr = wp_get_post_terms( $project_id, 'atp_category', array('fields'=>'slugs') );

				$term_slugs = !is_wp_error($term_slugs_arr) ? $term_slugs_arr : array();
				$terms = wp_get_post_terms( $project_id, 'atp_category' );
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

				//error_log(var_export($project_id, true) );

				$thumb_size = apply_filters('atp_project_thumb_size', $thumb_size, $project_id, $a );

				$post_classes_arr = array_merge( array( 'atp-project' ), $term_slugs );
				$post_classes = apply_filters('atp_project_classes', $post_classes_arr, $project_id, $a);
				$post_classes_str = implode( ' ', get_post_class( $post_classes, $project_id ) );

				?><li class="<?php echo $post_classes_str; ?>"><div class="atp-project-wrapper"><?php
					$thumb = '<figure class="atp-project-thumb">';
					$thumb .= '<a href="'.get_permalink($project_id).'">';

					if( isset($a['meta']['thumb_id']) && !$a['ignorethumb'] ) {
						$thumb_id = $a['meta']['thumb_id'];
						$img = wp_get_attachment_image( $thumb_id, $thumb_size );
						$thumb .= apply_filters('atp_project_thumb_img', $img, $thumb_size, $thumb_id, $project_id, $a );
					} else {
						$thumb_id = get_post_thumbnail_id( $project_id );
						$img = get_the_post_thumbnail( $project_id, $thumb_size );
						$thumb .= apply_filters('atp_project_thumb_img', $img, $thumb_size, $thumb_id, $project_id, $a );
					}

					$thumb .= '</a>';
					$thumb .= '</figure>';
					echo apply_filters('atp_project_thumb', $thumb, $project_id, $a );

					$details  = '<div class="atp-project-details">';
					$details .= '<a href="'.get_permalink($project_id).'" class="atp-project-title">';
					$details .= get_the_title($project_id);
					$details .= '</a>';
					$details .= '<ul class="atp-project-categories">';
					foreach ($terms as $term) {
						$details .= '<li>';
						$details .= '<span class="atp-filter" data-filter=".'.$term->slug.'">';
						$details .= $term->name;
						$details .= '</span>';
						$details .= '</li>';
					}
					$details .= '</ul>';
					$details .= '</div>';
					
					echo apply_filters('atp_project_details', $details, $project_id, $a );
				?></div></li>
				<?php $a['loop']['step']++; ?>
			<?php } ?>
		</ul>
		<?php do_action( 'atp_after_projects', $a ); ?>
	</div>
</div>