<?php

// Explanatory text about license settings for the tab information box
$html = '<p>' .
		esc_html__( 'If you\'ve purchased a premium add-on, you\'ll need to enter your license key here in order to have access to automatic updates when new versions are available.', 'tribe-common' ) .
		'</p>';

$html .= '<p>' .
		sprintf(
			esc_html__( 'In order to register a plugin license, you\'ll first need to %1$sdownload and install%2$s the plugin you purchased. You can download the latest version of your plugin(s) from %3$syour account\'s downloads page%4$s. Once the plugin is installed and activated on this site, the license key field will appear below.', 'tribe-common' ),
			'<a href="http://m.tri.be/1acu" target="_blank">',
			'</a>',
			'<a href="http://m.tri.be/1act" target="_blank">',
			'</a>'
		) .
		'</p>';

$html .= '<p>' .
		esc_html__( 'Each paid add-on has its own unique license key. Paste the key into its appropriate field below, and give it a moment to validate. You know you\'re set when a green expiration date appears alongside a "valid" message. Then click Save Changes.', 'tribe-common' ) .
		'</p>';

$html .= '<p>' .
		esc_html__( 'Helpful Links:', 'tribe-common' ) .
		'</p>';

$html .= '<ul>';
$html .= '<li><a href="http://m.tri.be/1acv" target="_blank">' .
			esc_html__( 'Why am I being told my license key is out of installs?', 'tribe-common' ) .
		'</a></li>';
$html .= '<li><a href="http://m.tri.be/1ad1" target="_blank">' .
			esc_html__( 'View and manage your license keys', 'tribe-common' ) .
		'</a></li>';
$html .= '<li><a href="http://m.tri.be/1acw" target="_blank">' .
			esc_html__( 'Moving your license keys', 'tribe-common' ) .
		'</a></li>';
$html .= '<li><a href="http://m.tri.be/1acx" target="_blank">' .
			esc_html__( 'Expired license keys and subscriptions', 'tribe-common' ) .
		'</a></li>';

// Expand with extra information for multisite users
if ( is_multisite() ) {
	$html .= '<li><a href="http://m.tri.be/1ad0" target="_blank">' .
			esc_html__( 'Licenses for Multisites', 'tribe-common' ) .
		'</a></li>';
}

$html .= '</ul>';


$licenses_tab = array(
	'info-start' => array(
		'type' => 'html',
		'html' => '<div id="modern-tribe-info">',
	),
	'info-box-title' => array(
		'type' => 'html',
		'html' => '<h2>' . esc_html__( 'Licenses', 'tribe-common' ) . '</h2>',
	),
	'info-box-description' => array(
		'type' => 'html',
		'html' => $html,
	),
	'info-end' => array(
		'type' => 'html',
		'html' => '</div>',
	),
);
