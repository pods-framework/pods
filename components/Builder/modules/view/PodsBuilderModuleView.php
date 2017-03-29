<?php
/**
 * @package Pods\Components
 * @subpackage Builder
 */
if ( !class_exists( 'LayoutModule' ) )
    return;

if ( !class_exists( 'PodsBuilderModuleView' ) ) {
    class PodsBuilderModuleView extends LayoutModule {

        var $_name = '';
        var $_var = 'pods-builder-view';
        var $_description = '';
        var $_editor_width = 500;
        var $_can_remove_wrappers = true;

        /**
         * Register the Module
         */
        public function __construct () {
            $this->_name = __( 'Pods - View', 'pods' );
            $this->_description = __( "Include a file from a theme, with caching options", 'pods' );
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
                'view' => '',
                'expires' => 0,
                'cache_mode' => 'none'
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
?>
    <tr>
        <td valign="top">
            <label for="view"><?php _e( 'File to include', 'pods' ); ?></label>
        </td>
        <td>
            <?php $form->add_text_box( 'view' ); ?>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <label for="cache_mode"><?php _e( 'Cache Type', 'pods' ); ?></label>
        </td>
        <td>
            <?php
                $cache_modes = array(
                    'none' => __( 'Disable Caching', 'pods' ),
                    'cache' => __( 'Object Cache', 'pods' ),
                    'transient' => __( 'Transient', 'pods' ),
                    'site-transient' => __( 'Site Transient', 'pods' )
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
         */
        function _render ( $fields ) {
            $args = array(
                'view' => trim( pods_var_raw( 'view', $fields[ 'data' ], '' ) ),
                'expires' => (int) trim( pods_var_raw( 'expires', $fields[ 'data' ], ( 60 * 5 ) ) ),
                'cache_mode' => trim( pods_var_raw( 'cache_mode', $fields[ 'data' ], 'transient', null, true ) )
            );

            if ( 0 < strlen( $args[ 'view' ] ) && 'none' != $args[ 'cache_mode' ] )
                echo pods_shortcode( $args );
        }

    }
}

new PodsBuilderModuleView();