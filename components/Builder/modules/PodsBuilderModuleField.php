<?php
/**
 * @package Pods\Components
 * @subpackage Builder
 */
if ( !class_exists( 'LayoutModule' ) )
    return;

class PodsBuilderModuleField extends LayoutModule {

    var $_name = '';

    var $_var = 'pods-builder-field';

    var $_description = '';

    var $_editor_width = 450;

    /**
     * Register the widget
     */
    public function PodsBuilderModuleField () {
        $this->_name = __( 'Pods Field Value', 'pods' );
        $this->_description = __( "Display a single Pod item's field value", 'pods' );
        $this->module_path = dirname( __FILE__ );

        $this->LayoutModule();
    }

    /**
     * Module Output
     */
    public function render ( $args, $instance ) {
        extract( $args );

        // Get widget fields
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        $args = array(
            'name' => trim( pods_var_raw( 'pod_type', $instance, '' ) ),
            'slug' => trim( pods_var_raw( 'slug', $instance, '' ) ),
            'field' => trim( pods_var_raw( 'field', $instance, '' ) )
        );

        if ( 0 < strlen( $args[ 'name' ] ) && 0 < strlen( $args[ 'slug' ] ) && 0 < strlen( $args[ 'field' ] ) ) {
            require PODS_DIR . 'ui/front/widgets.php';
        }
    }

    /**
     * Module Form
     */
    public function form ( $instance ) {
        $title = pods_var_raw( 'title', $instance, '' );
        $pod_type = pods_var_raw( 'pod_type', $instance, '' );
        $slug = pods_var_raw( 'slug', $instance, '' );
        $field = pods_var_raw( 'field', $instance, '' );

        require PODS_DIR . 'ui/admin/widgets/field.php';
    }
}
