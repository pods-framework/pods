<?php
/**
 * Name: Builder Integration
 *
 * Description: Integration with the <a href="http://ithemes.com/">Builder</a> theme / child themes from <a href="http://ithemes.com/">iThemes</a>; Adds new modules to the Layout engine
 *
 * Version: 1.0
 *
 * Category: Integration
 *
 * Plugin: pods-builder/pods-builder.php
 *
 * @package Pods\Components
 * @subpackage Builder
 */

if ( !function_exists( 'pods_builder_modules_init' ) ) {
    function pods_builder_modules_init () {
        require_once( PODS_DIR . 'components/Builder/modules/field/PodsBuilderModuleField.php' );
        require_once( PODS_DIR . 'components/Builder/modules/form/PodsBuilderModuleForm.php' );
        require_once( PODS_DIR . 'components/Builder/modules/list/PodsBuilderModuleList.php' );
        require_once( PODS_DIR . 'components/Builder/modules/single/PodsBuilderModuleSingle.php' );
        require_once( PODS_DIR . 'components/Builder/modules/view/PodsBuilderModuleView.php' );
    }
    add_action( 'builder_modules_loaded', 'pods_builder_modules_init' );
}