<?php

$generalTabFields = [
	'info-start'                    => [
		'type' => 'html',
		'html' => '<div id="modern-tribe-info">
					<img
						src="' . plugins_url( 'resources/images/logo/tec-brand.svg', dirname( __FILE__ ) ) . '"
						alt="' . esc_attr( 'The Events Calendar brand logo', 'tribe-common' ) . '"
					/>',
	],
	'event-tickets-info' => [
		'type'        => 'html',
		'html'        => '<p>' . sprintf( esc_html__( 'Thank you for using Event Tickets! All of us at The Events Calendar sincerely appreciate your support and we\'re excited to see you using our plugins. Check out our handy %1$sNew User Primer%2$s to get started.', 'tribe-common' ), '<a href="http://evnt.is/18nd">', '</a>' ) . '</p>',
		'conditional' => ! class_exists( 'Tribe__Events__Main' ),
	],
	'event-tickets-upsell-info' => [
		'type'        => 'html',
		'html'        => '<p>' . sprintf( esc_html__( 'Optimize your site\'s event listings with %1$sThe Events Calendar%2$s, our free calendar plugin. Looking for additional functionality including recurring events, user-submission, advanced ticket sales and more? Check out our %3$spremium add-ons%4$s.', 'tribe-common' ), '<a href="http://evnt.is/18x6">', '</a>', '<a href="http://evnt.is/18x5">', '</a>' ) . '</p>',
		'conditional' => ! class_exists( 'Tribe__Events__Main' ),
	],
	'upsell-info'                   => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-common' ) . ' <a href="' . Tribe__Main::$tec_url . 'products/?utm_source=generaltab&utm_medium=plugin-tec&utm_campaign=in-app">' . esc_html__( 'Check out the available add-ons', 'tribe-common' ) . '</a>.</p>',
		'conditional' => ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ) && class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link-heading'           => [
		'type'  => 'heading',
		'label' => esc_html__( 'We hope our plugin is helping you out.', 'tribe-common' ),
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link-info'              => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to The Events Calendar for all their hard work." The greatest thanks we could ask for is recognition. Add a small text-only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-common' ) . '<br><a href="' . esc_url( plugins_url( 'resources/images/donate-link-screenshot.png', dirname( __FILE__ ) ) ) . '" class="thickbox">' . esc_html__( 'See an example of the link', 'tribe-common' ) . '</a>.</p>',
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link'                   => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show The Events Calendar link', 'tribe-common' ),
		'default'         => false,
		'validation_type' => 'boolean',
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'info-end'                      => [
		'type' => 'html',
		'html' => '</div>',
	],
	'tribe-form-content-start'      => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap">',
	],
];

if ( is_super_admin() ) {
	$generalTabFields['debugEvents'] = [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Debug mode', 'tribe-common' ),
		'tooltip' => sprintf(
			esc_html__(
				'Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %s and look for the "Tribe" tab in the debug output.',
				'tribe-common'
			),
			'<a href="https://wordpress.org/extend/plugins/debug-bar/" target="_blank">' . esc_html__( 'Debug Bar Plugin', 'tribe-common' ) . '</a>'
		),
		'default'         => false,
		'validation_type' => 'boolean',
	];
}

// Closes form
$generalTabFields['tribe-form-content-end'] = [
	'type' => 'html',
	'html' => '</div>',
];


$generalTab = [
	'priority' => 10,
	'fields'   => apply_filters( 'tribe_general_settings_tab_fields', $generalTabFields ),
];
