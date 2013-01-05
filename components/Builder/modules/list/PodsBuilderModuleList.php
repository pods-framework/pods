<?php
/**
 * @package Pods\Components
 * @subpackage Builder
 */
if ( !class_exists( 'LayoutModule' ) )
    return;

if ( !class_exists( 'PodsBuilderModuleList' ) ) {
    class PodsBuilderModuleList extends LayoutModule {

        var $_name = '';
        var $_var = 'pods-builder-list';
        var $_description = '';
        var $_editor_width = 500;
        var $_can_remove_wrappers = true;

        /**
         * Register the Module
         */
        public function PodsBuilderModuleList () {
            $this->_name = __( 'Pods - List Items', 'pods' );
            $this->_description = __( 'Display multiple Pod items', 'pods' );
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
                'template' => '',
                'template_custom' => '',
                'limit' => 15,
                'orderby' => '',
                'where' => '',
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
    <p><?php _e( 'Display multiple Pod items', 'pods' ); ?></p>
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
            $all_pods = $api->load_pods();

            $pod_types = array();

            foreach ( $all_pods as $pod ) {
                $pod_types[ $pod[ 'name' ] ] = $pod[ 'label' ];
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

    <?php
        if ( class_exists( 'Pods_Templates' ) ) {
            $all_templates = (array) $api->load_templates( array() );

            $templates = array(
                '' => '- ' . __( 'Custom Template', 'pods' ) . ' -'
            );

            foreach ( $all_templates as $template ) {
                $templates[ $template[ 'name' ] ] = $template[ 'name' ];
            }
    ?>
        <tr>
            <td valign="top">
                <label for="template"><?php _e( 'Template', 'pods' ); ?></label>
            </td>
            <td>
                <?php
                    if ( 0 < count( $all_templates ) )
                        $form->add_drop_down( 'template', $templates );
                    else
                        echo '<strong class="red">' . __( 'None Found', 'pods' ) . '</strong>';
                ?>
            </td>
        </tr>
    <?php
        }
    ?>

    <tr>
        <td valign="top">
            <label for="template_custom"><?php _e( 'Custom Template', 'pods' ); ?></label>
        </td>
        <td>
            <?php $form->add_text_area( 'template_custom', array( 'style' => 'width:90%; max-width:100%; min-height:100px;', 'rows' => '8' ) ); ?>
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
<?php
        }

        /**
         * Module Output
         */
        function _render ( $fields ) {
            $args = array(
                'name' => trim( pods_var_raw( 'pod_type', $fields[ 'data' ], '' ) ),
                'template' => trim( pods_var_raw( 'template', $fields[ 'data' ], '' ) ),
                'limit' => (int) pods_var_raw( 'limit', $fields[ 'data' ], 15, null, true ),
                'orderby' => trim( pods_var_raw( 'orderby', $fields[ 'data' ], '' ) ),
                'where' => trim( pods_var_raw( 'where', $fields[ 'data' ], '' ) )
            );

            $content = trim( pods_var_raw( 'template_custom', $fields[ 'data' ], '' ) );

            if ( 0 < strlen( $args[ 'name' ] ) && ( 0 < strlen( $args[ 'template' ] ) || 0 < strlen( $content ) ) )
                echo pods_shortcode( $args, ( isset( $content ) ? $content : null ) );
        }

    }
}

new PodsBuilderModuleList();