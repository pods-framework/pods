<?php
/**
 * @package    Pods\Components
 * @subpackage Builder
 */
if ( ! class_exists( 'LayoutModule' ) ) {
	return;
}

if ( ! class_exists( 'PodsBuilderModuleList' ) ) {
	/**
	 * Class PodsBuilderModuleList
	 */
	class PodsBuilderModuleList extends LayoutModule {

		public $_name                = '';
		public $_var                 = 'pods-builder-list';
		public $_description         = '';
		public $_editor_width        = 500;
		public $_can_remove_wrappers = true;

		/**
		 * Register the Module
		 */
		public function __construct() {

			$this->_name        = __( 'Pods - List Items', 'pods' );
			$this->_description = __( 'Display multiple Pod items', 'pods' );
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
				'pod_type'        => '',
				'template'        => '',
				'template_custom' => '',
				'limit'           => 15,
				'orderby'         => '',
				'where'           => '',
				'expires'         => ( 60 * 5 ),
				'cache_mode'      => 'transient',
				'sidebar'         => 'none',
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

			<?php
			if ( class_exists( 'Pods_Templates' ) ) {
				$all_templates = (array) $api->load_templates( array() );

				$templates = array(
					'' => '- ' . __( 'Custom Template', 'pods' ) . ' -',
				);

				foreach ( $all_templates as $template ) {
					$templates[ $template['name'] ] = $template['name'];
				}
				?>
				<tr>
					<td valign="top">
						<label for="template"><?php _e( 'Template', 'pods' ); ?></label>
					</td>
					<td>
						<?php
						if ( 0 < count( $all_templates ) ) {
							$form->add_drop_down( 'template', $templates );
						} else {
							echo '<strong class="red">' . __( 'None Found', 'pods' ) . '</strong>';
						}
						?>
					</td>
				</tr>
				<?php
			} else {
				?>
				<tr>
					<td valign="top">
						<label for="template"><?php _e( 'Template', 'pods' ); ?></label>
					</td>
					<td>
						<?php $form->add_text_box( 'template' ); ?>
					</td>
				</tr>
				<?php
			}//end if
			?>

			<tr>
				<td valign="top">
					<label for="template_custom"><?php _e( 'Custom Template', 'pods' ); ?></label>
				</td>
				<td>
					<?php
					$form->add_text_area(
						'template_custom', array(
							'style' => 'width:90%; max-width:100%; min-height:100px;',
							'rows'  => '8',
						)
					);
					?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="limit"><?php _e( 'Limit', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'limit' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="orderby"><?php _e( 'Order By', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'orderby' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="where"><?php _e( 'Where', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'where' ); ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="cache_mode"><?php _e( 'Cache Type', 'pods' ); ?></label>
				</td>
				<td>
					<?php
					$cache_modes = array(
						'none'           => __( 'Disable Caching', 'pods' ),
						'cache'          => __( 'Object Cache', 'pods' ),
						'transient'      => __( 'Transient', 'pods' ),
						'site-transient' => __( 'Site Transient', 'pods' ),
					);

					$form->add_drop_down( 'cache_mode', $cache_modes );
					?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<label for="expires"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>
				</td>
				<td>
					<?php $form->add_text_box( 'expires' ); ?>
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
				'name'       => trim( (string) pods_var_raw( 'pod_type', $fields['data'], '' ) ),
				'template'   => trim( (string) pods_var_raw( 'template', $fields['data'], '' ) ),
				'limit'      => (int) pods_var_raw( 'limit', $fields['data'], 15, null, true ),
				'orderby'    => trim( (string) pods_var_raw( 'orderby', $fields['data'], '' ) ),
				'where'      => trim( (string) pods_var_raw( 'where', $fields['data'], '' ) ),
				'expires'    => (int) trim( (string) pods_var_raw( 'expires', $fields['data'], ( 60 * 5 ) ) ),
				'cache_mode' => trim( (string) pods_var_raw( 'cache_mode', $fields['data'], 'transient', null, true ) ),
			);

			$content = trim( (string) pods_var_raw( 'template_custom', $fields['data'], '' ) );

			if ( 0 < strlen( $args['name'] ) && ( 0 < strlen( $args['template'] ) || 0 < strlen( $content ) ) ) {
				echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );
			}
		}

	}
}//end if

new PodsBuilderModuleList();
