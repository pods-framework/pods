<?php
/**
 * @package    Pods\Components
 * @subpackage Builder
 */
if ( ! class_exists( 'LayoutModule' ) ) {
	return;
}

if ( ! class_exists( 'PodsBuilderModuleField' ) ) {
	/**
	 * Class PodsBuilderModuleField
	 */
	class PodsBuilderModuleField extends LayoutModule {

		public $_name                = '';
		public $_var                 = 'pods-builder-field';
		public $_description         = '';
		public $_editor_width        = 500;
		public $_can_remove_wrappers = true;

		/**
		 * Register the Module
		 */
		public function __construct() {

			$this->_name        = __( 'Pods - Field Value', 'pods' );
			$this->_description = __( "Display a single Pod item's field value", 'pods' );
			$this->module_path  = dirname( __FILE__ );

			$this->LayoutModule();
		}

		/**
		 * Set default variables
		 *
		 * @param $defaults
		 *
		 * @return mixed
		 */
		public function _get_defaults( $defaults ) {

			$new_defaults = array(
				'pod_type' => '',
				'slug'     => '',
				'field'    => '',
				'sidebar'  => 'none',
			);

			return ITUtility::merge_defaults( $new_defaults, $defaults );
		}

		/**
		 * Output something before the table form
		 *
		 * @param object $form Form class
		 * @param bool   $results
		 */
		public function _before_table_edit( $form, $results = true ) {

			?>
			<p><?php echo $this->_description; ?></p>
			<?php
		}

		/**
		 * Output something at the start of the table form
		 *
		 * @param object $form Form class
		 * @param bool   $results
		 */
		public function _start_table_edit( $form, $results = true ) {

			$api      = pods_api();
			$all_pods = $api->load_pods( array( 'names' => true ) );

			$pod_types = array();

			foreach ( $all_pods as $pod_name => $pod_label ) {
				$pod_types[ $pod_name ] = $pod_label . ' (' . $pod_name . ')';
			}
			?>
			<tr>
				<td valign="top">
					<label for="pod_type"><?php _e( 'Pod', 'pods' ); ?></label>
				</td>
				<td>
					<?php
					if ( 0 < count( $all_pods ) ) {
						$form->add_drop_down( 'pod_type', $pod_types );
					} else {
						echo '<strong class="red">' . __( 'None Found', 'pods' ) . '</strong>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="slug"><?php _e( 'Slug or ID', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'slug' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="field"><?php _e( 'Field', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'field' ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Module Output
		 *
		 * @param $fields
		 */
		public function _render( $fields ) {

			$args = array(
				'name'  => trim( (string) pods_var_raw( 'pod_type', $fields['data'], '' ) ),
				'slug'  => trim( (string) pods_var_raw( 'slug', $fields['data'], '' ) ),
				'field' => trim( (string) pods_var_raw( 'field', $fields['data'], '' ) ),
			);

			if ( 0 < strlen( $args['name'] ) && 0 < strlen( $args['slug'] ) && 0 < strlen( $args['field'] ) ) {
				echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );
			}
		}

	}
}//end if

new PodsBuilderModuleField();
