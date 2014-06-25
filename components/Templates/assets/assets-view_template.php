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
	'cm-comp' => $this->get_url( '/assets/js/codemirror-compressed1.js', dirname( __FILE__ ) ),
	'cm-editor' => $this->get_url( '/assets/js/editor1.js', dirname( __FILE__ ) ),
	'cm-css' => $this->get_url( '/assets/css/codemirror1.css', dirname( __FILE__ ) ),
);