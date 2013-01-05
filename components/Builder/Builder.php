<?php
/**
 * Name: Builder Integration
 *
 * Description: Integration with the Builder theme / child themes from iThemes
 *
 * Version: 1.0
 *
 * Theme Dependency: Builder|builder|http://www.ithemes.com/
 *
 * @package Pods\Components
 * @subpackage Builder
 */

function pods_builder_modules_init () {
    require_once( PODS_DIR . 'components/Builder/modules/field/PodsBuilderModuleField.php' );
    require_once( PODS_DIR . 'components/Builder/modules/form/PodsBuilderModuleForm.php' );
    require_once( PODS_DIR . 'components/Builder/modules/list/PodsBuilderModuleList.php' );
    require_once( PODS_DIR . 'components/Builder/modules/single/PodsBuilderModuleSingle.php' );
}
add_action( 'builder_modules_loaded', 'pods_builder_modules_init' );