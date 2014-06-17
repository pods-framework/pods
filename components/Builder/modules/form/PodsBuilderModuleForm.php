<?php
/**
 * @package Pods\Components
 * @subpackage Builder
 */
if ( !class_exists( 'LayoutModule' ) )
    return;

if ( !class_exists( 'PodsBuilderModuleForm' ) ) {
    class PodsBuilderModuleForm extends LayoutModule {

        var $_name = '';
        var $_var = 'pods-builder-form';
        var $_description = '';
        var $_editor_width = 500;
        var $_can_remove_wrappers = true;

        /**
         * Register the Module
         */
        public function PodsBuilderModuleForm () {
            $this->_name = __( 'Pods - Form', 'pods' );
            $this->_description = __( 'Display a form for creating and editing Pod items', 'pods' );
            $this->module_path = dirname( __FILE__ );

            $this->LayoutModule();
        }

        /**
         * Set default variables
         *
         * @param $defaults
         *
         * @return mixed
         */
        function _get_defaults ( $defaults ) {
            $new_defaults = array(
                'pod_type' => '',
                'slug' => '',
                'fields' => '',
                'label' => __( 'Submit', 'pods' ),
                'thank_you' => '',
                'sidebar' => 'none'
            );

            return ITUtility::merge_defaults( $new_defaults, $defaults );
        }

        /**
         * Output something before the table form
         *
         * @param object $form Form class
         * @param bool $results
         */
        function _before_table_edit ( $form, $results = true ) {
?>
    <p><?php echo $this->_description; ?></p>
<?php
        }

        /**
         * Output something at the start of the table form
         *
         * @param object $form Form class
         * @param bool $results
         */
        function _start_table_edit ( $form, $results = true ) {
            $api = pods_api();
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
                if ( 0 < count( $all_pods ) )
                    $form->add_drop_down( 'pod_type', $pod_types );
                else
                    echo '<strong class="red">' . __( 'None Found', 'pods' ) . '</strong>';
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
            <label for="fields"><?php _e( 'Fields (comma-separated)', 'pods' ); ?></label>
        </td>
        <td>
            <?php $form->add_text_box( 'fields' ); ?>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <label for="label"><?php _e( 'Submit Label', 'pods' ); ?></label>
        </td>
        <td>
            <?php $form->add_text_box( 'label' ); ?>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <label for="thank_you"><?php _e( 'Thank You URL upon submission', 'pods' ); ?></label>
        </td>
        <td>
            <?php $form->add_text_box( 'thank_you' ); ?>
        </td>
    </tr>
<?php
        }

        /**
         * Module Output
         */
        function _render ( $fields ) {
            $args = array(
                'name' => trim( pods_var_raw( 'pod_type', $fields[ 'data' ], '' ) ),
                'slug' => trim( pods_var_raw( 'slug', $fields[ 'data' ], '' ) ),
                'fields' => trim( pods_var_raw( 'fields', $fields[ 'data' ], '' ) ),
                'label' => trim( pods_var_raw( 'label', $fields[ 'data' ], __( 'Submit', 'pods' ), null, true ) ),
                'thank_you' => trim( pods_var_raw( 'thank_you', $fields[ 'data' ], '' ) ),
                'form' => 1
            );

            if ( 0 < strlen( $args[ 'name' ] ) )
                echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );
        }

    }
}

new PodsBuilderModuleForm();