<?php
/**
 *
 * registered assets for pods_templates
 *
 * @package Pods_templates
 * @author David Cramer david@digilab.co.za
 * @license GPL-2.0+
 * @link
 * @copyright 2014 David Cramer
 */

$assets = array(
	'handlebarsjs' => $this->get_url( '/assets/js/handlebars2.js', dirname( __FILE__ ) ),
	'baldrickjs' => $this->get_url( '/assets/js/jquery.baldrick3.js', dirname( __FILE__ ) ),
	'handlebars-baldrick' => $this->get_url( '/assets/js/handlebars.baldrick2.js', dirname( __FILE__ ) ),
);