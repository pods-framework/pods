<?php
/**
 * The base component class, all components should extend this.
 *
 * @package Pods
 * @category Utilities
 */
class Pods_Component {

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @return \Pods_Component
	 *
	 * @since 2.0
	 */
	public function __construct() {

		// Hulk questions himself

	}

    /**
     * Add options and set defaults for component settings, shows in admin area
     *
     * @return array $options
     *
     * @since 2.0
    */
    /*public function options () {
        $options = array(
            'option_name' => array(
                'label' => 'Option Label',
                'depends-on' => array( 'another_option' => 'specific-value' ),
                'default' => 'default-value',
                'type' => 'field_type',
                'data' => array(
                    'value1' => 'Label 1',

                    // Group your options together
                    'Option Group' => array(
                        'gvalue1' => 'Option Label 1',
                        'gvalue2' => 'Option Label 2'
                    ),

                    // below is only if the option_name above is the "{$fieldtype}_format_type"
                    'value2' => array(
                        'label' => 'Label 2',
                        'regex' => '[a-zA-Z]' // Uses JS regex validation for the value saved if this option selected
                    )
                ),

                // below is only for a boolean group
                'group' => array(
                    'option_boolean1' => array(
                        'label' => 'Option boolean 1?',
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'option_boolean2' => array(
                        'label' => 'Option boolean 2?',
                        'default' => 0,
                        'type' => 'boolean'
                    )
                )
            )
        );

        return $options;
    }*/

	/**
	 * Handler to run code based on $options
	 *
	 * @param $options
	 *
	 * @since 2.0
	 */
	public function handler( $options ) {

		// run code based on $options set

	}

	/**
	 * Build admin area
	 *
	 * @param $options
	 *
	 * @since 2.0
	 */
	 /*public function admin( $options ) {
	    // run code based on $options set
	 }*/

	/**
	 * Register any services required by the component here
	 *
	 * @param Pods_Service_Container $container global Pods container
	 */
	public function register_services( Pods_Service_Container $container ) {

		// Just a littler service !magic

	}

}