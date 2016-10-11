<?php
if( !class_exists('ATP_MetaBox') ) {

	class ATP_MetaBox {
		public $ID;
		public $title;
		public $content;
		public $post_type;
		public $meta_field;

		function __construct( $id, $title, $content, $post_type, $context = 'normal', $priority = 'high' ) {
			$this->ID = $id;
			$this->title = $title;
			$this->content = $content;
			$this->post_type = $post_type;

			$this->meta_field = '_' . $this->ID;

			add_meta_box( $this->ID, $this->title, array($this, 'callback'), $this->post_type, $context, $priority );
			add_action( 'save_post', array($this, 'save') );
		}

		function callback( $post ) {
			if( file_exists($this->content) ) {
				wp_nonce_field( $this->ID . '-nonce-action', $this->ID . '-nonce-name' );

				$meta = get_post_meta( $post->ID, $this->meta_field, true );
				$field_namespace = $this->meta_field;

				include( $this->content );
			} else {
				echo 'The specified file does not exist.';
			}
		}

		function save( $post_id ) {
			if( !isset($_POST[$this->ID . '-nonce-name']) ) return;

			if( !wp_verify_nonce($_POST[$this->ID . '-nonce-name'], $this->ID . '-nonce-action') ) return;

			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

			if( !isset($_POST[ $this->meta_field ]) ) {
				delete_post_meta( $post_id, $this->meta_field );
				return;
			}
			//get the new submited data
			$new_data = $_POST[ $this->meta_field ];
			//clear keys of the data array if the values are not set ot empty
			$this->clear_metabox( $new_data );

			//update the meta data of the post
			update_post_meta( $post_id, $this->meta_field, $new_data );
		}

		private function clear_metabox( &$arr ) {
			if ( is_array( $arr ) ) {
				foreach ( $arr as $i => $v ) {
					if ( is_array( $arr[ $i ] ) ) {
						$this->clear_metabox( $arr[ $i ] );
						if ( !count( $arr[ $i ] ) ) {
							unset( $arr[ $i ] );
						}
					} else {
						if ( trim( $arr[ $i ] ) == '' ) {
							unset( $arr[ $i ] );
						}
					}
				}
				if ( !count( $arr ) ) {
					$arr = null;
				}
			}
		}
	} //end class
}
?>