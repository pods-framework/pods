<?php
require_once( PODS_DIR . 'classes/fields/pick.php' );

/**
 * @package Pods\Fields
 */
class PodsField_Taxonomy extends PodsField_Pick {

    /**
     * Setup related objects list
     *
     * @since 2.0
     */
    public function __construct () {
	    parent::__construct();
		// this field type just maps to the relationship field
    }

}
