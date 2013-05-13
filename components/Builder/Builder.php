<?php
/**
 * Name: Builder Integration
 *
 * Description: Integration with the Builder theme / child themes from iThemes; Adds new modules to the Layout engine
 *
 * Version: 1.0
 *
 * Category: Integration
 *
 * Theme Dependency: Builder|builder|http://ithemes.com/
 *
 * Plugin: pods-builder/pods-builder.php
 *
 * @package Pods\Components
 * @subpackage Builder
 */

if ( function_exists( 'pods_builder_modules_init' ) )
    return;

function pods_builder_modules_init () {
    require_once( PODS_DIR . 'components/Builder/modules/field/PodsBuilderModuleField.php' );
    require_once( PODS_DIR . 'components/Builder/modules/form/PodsBuilderModuleForm.php' );
    require_once( PODS_DIR . 'components/Builder/modules/list/PodsBuilderModuleList.php' );
    require_once( PODS_DIR . 'components/Builder/modules/single/PodsBuilderModuleSingle.php' );
}
add_action( 'builder_modules_loaded', 'pods_builder_modules_init' );