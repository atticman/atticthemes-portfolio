<?php
/*
Plugin Name: AtticThemes: Portfolio
Plugin URI: http://atticthemes.com
Description: This plugin extends BoxBuilder by adding portfolio widget.
Version: 1.0.4
Author: atticthemes
Author URI: http://themeforest.net/user/atticthemes
Requires: 4.0.0
Tested: 4.2.2
Updated: 2015-05-08
Added: 2014-02-01
*/
?>
<?php
if( !class_exists('AtticThemes_Portfolio') && class_exists('AtticThemes_BoxBuilder') ) {

	class AtticThemes_Portfolio {
		public static $version = '1.0.4';

		private static $dev = false;
		private static $min_suffix = '.min';

		public static $shortcodes;




		public static function theme_setup() {
			/* add post type support */
			AtticThemes_BoxBuilder::addPostTypeSupport( 'atp_project' );
		}

		public static function init() {
			if( self::$dev ) {
				self::$min_suffix = '';
			}

			self::$shortcodes = array(
				'grid' => 'attic_grid_portfolio',
				'slider' => 'attic_slider_portfolio',
				'carousel' => 'attic_carousel_portfolio'
			);

			/* add shortcodes */
			foreach (self::$shortcodes as $shortcode) {
				$callback_name = $shortcode . '_handler';
				if( method_exists('AtticThemes_Portfolio', $callback_name) ) {
					add_shortcode( $shortcode, array('AtticThemes_Portfolio', $callback_name) );
				}
			}

			/* admin scrips and styles */
			add_action( 'admin_enqueue_scripts', array( 'AtticThemes_Portfolio', 'enqueue_admin_scripts_and_style' ) );

			/* front-end scrips and styles */
			add_action( 'wp_enqueue_scripts', array( 'AtticThemes_Portfolio', 'enqueue_scripts_and_style' ) );
		}

		public static function admin_init() {
			/* add style */
			AtticThemes_BoxBuilder::addResource(
				'atp-admin-style',
				plugins_url( 'resources/admin/css/atp-admin-style'.self::$min_suffix.'.css' , __FILE__ ),
				'style',
				self::$version
			);

			/* add script */
			AtticThemes_BoxBuilder::addResource(
				'atp-admin-script',
				plugins_url( 'resources/admin/javascript/atp-admin-script'.self::$min_suffix.'.js' , __FILE__ ),
				'script',
				self::$version
			);

			AtticThemes_BoxBuilder::addResource(
				'atp-admin-flexslider',
				plugins_url( 'resources/front-end/javascript/jquery.flexslider.min.js', __FILE__ ),
				'script',
				self::$version
			);

			new ATP_MetaBox(
				'atp_project_metabox',
				__( 'Project Settings', 'atticthemes_portfolio' ),
				plugin_dir_path(__FILE__) . 'includes/metaboxes/project-metabox.php',
				'atp_project',
				'side',
				'low'
			);
		}

		public static function enqueue_scripts_and_style() {
			global $post;
			if( isset($post) && isset($post->post_content) ) {

				/* grid */
				$needs_isotop = self::has_the_shortcodes( $content = $post->post_content, array( 
						self::$shortcodes['grid']
					)
				);
				if( $needs_isotop ) {
					wp_register_script(
						'atp-isotope',
						plugins_url( 'resources/front-end/javascript/jquery.isotope.min.js', __FILE__ ),
						array( 'jquery' ),
						self::$version,
						true
					);
					wp_enqueue_script( 'atp-isotope' );
				}

				/* slider & carousel */
				$needs_slider = self::has_the_shortcodes( $content = $post->post_content, array( 
						self::$shortcodes['slider'],
						self::$shortcodes['carousel']
					)
				);
				if( $needs_slider ) {
					wp_register_script(
						'atp-flexslider',
						plugins_url( 'resources/front-end/javascript/jquery.flexslider.min.js', __FILE__ ),
						array( 'jquery' ),
						self::$version,
						true
					);
					wp_enqueue_script( 'atp-flexslider' );
				}

				if( self::has_the_shortcodes($content = $post->post_content) ) {
					wp_register_style(
						'atp-style',
						plugins_url( 'resources/front-end/css/atp-style'.self::$min_suffix.'.css', __FILE__ ),
						array(),
						self::$version,
						'all'
					);
					wp_enqueue_style( 'atp-style' );
				
					wp_register_script(
						'atp-script',
						plugins_url( 'resources/front-end/javascript/atp-script'.self::$min_suffix.'.js', __FILE__ ),
						array( 'jquery' ),
						self::$version,
						true
					);
					wp_enqueue_script( 'atp-script' );
				}
			}// END if
		}

		public static function has_the_shortcodes( $content = '', $shortcodes = null ) {
			if( $shortcodes && is_array($shortcodes) ) {
				foreach ($shortcodes as $shortcode) {
					if( has_shortcode($content, $shortcode) ) {
						return true;
					}
				}
			} else {
				foreach (self::$shortcodes as $type => $shortcode) {
					if( has_shortcode($content, $shortcode) ) {
						return true;
					}
				}
			}
			
			return false;
		}

		public static function enqueue_admin_scripts_and_style() {
			/* gather data for JavaScript */
			$atp_posts = get_posts(array('post_type'=>'atp_project', 'posts_per_page'=>-1));
			$posts_data = array();
			foreach ($atp_posts as $atp_post) {
				$thumb_id = get_post_thumbnail_id($atp_post->ID);
				$project_meta = get_post_meta( $atp_post->ID, '_atp_project_metabox', true );

				if( empty($thumb_id) ) {
					if( isset($project_meta['thumb_id']) ) {
						$thumb_id = $project_meta['thumb_id'];
					}
				}

				$img = wp_get_attachment_image_src($thumb_id, 'medium');

				$sizes = array(
					'full' => wp_get_attachment_image_src($thumb_id, 'full'),
					'large' => wp_get_attachment_image_src($thumb_id, 'large'),
					'medium' => wp_get_attachment_image_src($thumb_id, 'medium'),
					'thumb' => wp_get_attachment_image_src($thumb_id, 'thumbnail')
				);

				$post_terms = wp_get_post_terms( $atp_post->ID, 'atp_category' );
				$post_term_array = array();

				foreach ($post_terms as $post_term) {
					$post_term_array[ $post_term->term_id ] = array(
						'id' => $post_term->term_id,
						'title' => $post_term->name,
						'slug' => $post_term->slug,
						'count' => $post_term->count
					);
				}
				$posts_data[ $atp_post->ID ] = array(
					'id' => $atp_post->ID,
					'title' => $atp_post->post_title,
					'thumb' => isset($img[0]) ? $img[0] : null,
					'sizes' =>$sizes,
					'terms' => $post_term_array
				);
			}
			//---

			$terms = get_terms('atp_category');
			$terms_data = array();
			foreach ($terms as $term) {
				$terms_data[ $term->term_id ] = array(
					'id' => $term->term_id,
					'title' => $term->name,
					'slug' => $term->slug,
					'count' => $term->count
				);
			}
			//---
			wp_localize_script( 'atp-admin-script', 'atp_raw_data', json_encode( array(
						'posts' => $posts_data,
						'terms' => $terms_data,
						'shortcodes' => self::$shortcodes
					)
				)//end json_encode
			);
			//
		}

		public static function register_post_types() {
			/*-------------------------------------------------------------------*/
			$project_labels = array(
				'name'               => _x( 'Project', 'post type general name', 'atticthemes_portfolio' ),
				'singular_name'      => _x( 'Project', 'post type singular name', 'atticthemes_portfolio' ),
				'menu_name'          => _x( 'Projects', 'admin menu', 'atticthemes_portfolio' ),
				'name_admin_bar'     => _x( 'Project', 'add new on admin bar', 'atticthemes_portfolio' ),
				'add_new'            => _x( 'Add New', 'book', 'atticthemes_portfolio' ),
				'add_new_item'       => __( 'Add New Project', 'atticthemes_portfolio' ),
				'new_item'           => __( 'New Project', 'atticthemes_portfolio' ),
				'edit_item'          => __( 'Edit Project', 'atticthemes_portfolio' ),
				'view_item'          => __( 'View Project', 'atticthemes_portfolio' ),
				'all_items'          => __( 'All Projects', 'atticthemes_portfolio' ),
				'search_items'       => __( 'Search Projects', 'atticthemes_portfolio' ),
				'parent_item_colon'  => __( 'Parent Projects:', 'atticthemes_portfolio' ),
				'not_found'          => __( 'No projects found.', 'atticthemes_portfolio' ),
				'not_found_in_trash' => __( 'No projects found in Trash.', 'atticthemes_portfolio' )
			);
			$project_args = array(
				'labels' => $project_labels,
				'public' => true,
				'publicly_queryable' => true,
				'exclude_from_search' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'project' ),
				'capability_type' => 'page',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array( 'title', 'editor', 'thumbnail', 'comments', 'excerpt' ),
				//'register_meta_box_cb' => array('AtticThemes_Portfolio', 'add_portfolio_metabox_cb')
			);
			register_post_type( 'atp_project', $project_args );

			$category_labels = array(
				'name' => _x( 'Categories', 'taxonomy general name' ),
				'singular_name' => _x( 'Category', 'taxonomy singular name' ),
				'search_items' => __( 'Search Categories' ),
				'popular_items' => __( 'Popular Categories' ),
				'all_items' => __( 'All Categories' ),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __( 'Edit Category' ),
				'update_item' => __( 'Update Category' ),
				'add_new_item' => __( 'Add New Category' ),
				'new_item_name' => __( 'New Category Name' ),
				'separate_items_with_commas' => __( 'Separate categories with commas' ),
				'add_or_remove_items' => __( 'Add or remove categories' ),
				'choose_from_most_used' => __( 'Choose from the most used categories' ),
				'not_found' => __( 'No categories found.' ),
				'menu_name' => __( 'Categories' ),
			);

			$category_args = array(
				'hierarchical' => false,
				'labels' => $category_labels,
				'show_ui' => true,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array( 'slug' => 'atp_category' ),
			);
			register_taxonomy( 'atp_category', 'atp_project', $category_args );
		}






		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/

		public static function attic_grid_portfolio_handler( $attrs ) {
			$output = '';
			$a = shortcode_atts( array(
				'type' => 'grid',
				'projects' => '',
				'categories' => '',
				//
				'columns' => '3',
				'filters' => false,
				'showall' => '',
				'masonry' => false,
				'nogutter' => false,
				//
				'classes' => '',
				'ignorethumb' => false,
				'allprojects' => false,
				//
				'section_width' => '',
				'row_width' => ''
			), $attrs );
			//-----
			return self::shortcode_handeler( $a );
		}


		public static function attic_slider_portfolio_handler( $attrs ) {
			$a = shortcode_atts( array(
				'type' => 'grid',
				'projects' => '',
				'categories' => '',
				//
				'animation' => 'fade',
				'slidespeed' => 6,
				'animspeed' => 0.6,
				'dircontrols' => false,
				'navcontrols' => false,
				//
				'classes' => '',
				'ignorethumb' => false,
				'allprojects' => false,
				//
				'section_width' => '',
				'row_width' => ''
			), $attrs );
			//-----
			return self::shortcode_handeler( $a );
		}

		public static function attic_carousel_portfolio_handler( $attrs ) {
			$a = shortcode_atts( array(
				'type' => 'carousel',
				'projects' => '',
				'categories' => '',
				//
				'columns' => '3',
				'nogutter' => false,
				//
				'dircontrols' => false,
				'navcontrols' => false,
				//
				'classes' => '',
				'ignorethumb' => false,
				'allprojects' => false,
				//
				'section_width' => '',
				'row_width' => ''
			), $attrs );
			//-----
			return self::shortcode_handeler( $a );
		}


		private static function shortcode_handeler( $a = null ) {
			if( !isset($a) ) return;

			$output = '';
			$output_file = plugin_dir_path(__FILE__) . 'includes/portfolio-'. $a['type'] .'.php';
			$raw_projects = !empty($a['projects']) ? explode(',', $a['projects']) : array();
			$raw_categories = !empty($a['categories']) ? explode(',', $a['categories']) : array();

			$projects = array();
			$projects_cats = array();
			$categories = array();

			/* filter to show only ulished projects */
			foreach ($raw_projects as $project_id) {
				if( get_post_status($project_id) === 'publish' ) {
					$projects[] = $project_id;
				}
			}

			/* get published project categories */
			foreach ($projects as $project_id) {
				$projects_cats = array_merge( $projects_cats, wp_get_post_terms($project_id, 'atp_category', array('fields'=>'ids')) );
			}

			/* filter categories to show only ones that have projects */
			foreach ($raw_categories as $raw_category) {
				if( in_array($raw_category, $projects_cats) ) {
					$categories[] = $raw_category;
				}
			}

			/* check for all projects setting to automaticall add new projects to this portfolio */
			if( $a['allprojects'] ) {
				$additional_projects = array();
				$additional_projects_query = new WP_Query( array(
						'post_type' => 'atp_project',
						'posts_per_page' => -1,
						'post__not_in' => $projects
					)
				);
				foreach ($additional_projects_query->posts as $post) {
					$additional_projects[] = $post->ID;
				}
				$projects = array_merge($projects, $additional_projects);

				/* ------ */
				$additional_categories = get_terms( 'atp_category', array(
						'exclude' => $categories,
						'fields' => 'ids'
					)
				);
				$categories = array_merge($categories, $additional_categories);
				/* ------ */
			}



			//-----
			if( empty($projects) || !file_exists($output_file) ) return $output;

			ob_start();
			//-----
			require( $output_file );
			//-----
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/
		/*------------------------------------------------------------------------*/

	}// End class

	add_action( 'init', array('AtticThemes_Portfolio', 'init') );
	add_action( 'init', array('AtticThemes_Portfolio', 'register_post_types') );
	add_action( 'admin_init', array('AtticThemes_Portfolio', 'admin_init') );
	add_action( 'after_setup_theme', array('AtticThemes_Portfolio', 'theme_setup') );
}

function atp_compatibility_notice() {
	?><div class="error atpe-support-notice">
		<p><?php _e( 'Attic Portfolio is an extension to BoxBuilder, please install and/or activate BoxBuilder plugin if you whish to use Attic Portfolio.', 'atticthemes_portfolio' ); ?></p>
	</div><?php
}

if( !class_exists('AtticThemes_BoxBuilder') ) {
	add_action( 'admin_notices', 'atp_compatibility_notice' );
}

























/* add metabox class scripts */
require_once( plugin_dir_path(__FILE__) . 'includes/metaboxes/metabox-class.php' );


/* init updater */
/*require_once( plugin_dir_path(__FILE__) . 'updater.php' );
if( class_exists('AttichThemes_PluginUpdater') ) {
	new AttichThemes_PluginUpdater( __FILE__ );
}*/
?>