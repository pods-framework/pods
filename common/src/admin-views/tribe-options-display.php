<?php

$sample_date = strtotime( 'January 15 ' . date( 'Y' ) );

$displayTab = array(
	'priority' => 20,
	'fields'   =>
	/**
	 * Filter the fields available on the display settings tab
	 *
	 * @param array $fields a nested associative array of fields & field info passed to Tribe__Field
	 * @see Tribe__Field
	 */
		apply_filters(
		'tribe_display_settings_tab_fields', array(
			'tribe-form-content-start'           => array(
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">',
			),
			'tribeEventsDateFormatSettingsTitle' => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Date Format Settings', 'tribe-common' ) . '</h3>',
			),
			'tribeEventsDateFormatExplanation'   => array(
				'type' => 'html',
				'html' => '<p>'
					. sprintf(
						__( 'The following three fields accept the date format options available to the PHP %1$s function. <a href="%2$s" target="_blank">Learn how to make your own date format here</a>.', 'tribe-common' ),
						'<code>date()</code>',
						'https://codex.wordpress.org/Formatting_Date_and_Time'
					)
					. '</p>',
			),
			'datepickerFormat'                   => array(
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Compact Date Format', 'tribe-common' ),
				'tooltip'         => esc_html__( 'Select the date format used for elements with minimal space, such as in datepickers.', 'tribe-common' ),
				'default'         => 'Y-m-d',
				'options'         => array(
					'0' => date( 'Y-m-d', $sample_date ),
					'1' => date( 'n/j/Y', $sample_date ),
					'2' => date( 'm/d/Y', $sample_date ),
					'3' => date( 'j/n/Y', $sample_date ),
					'4' => date( 'd/m/Y', $sample_date ),
					'5' => date( 'n-j-Y', $sample_date ),
					'6' => date( 'm-d-Y', $sample_date ),
					'7' => date( 'j-n-Y', $sample_date ),
					'8' => date( 'd-m-Y', $sample_date ),
					'9' => date( 'Y.m.d', $sample_date ),
					'10' => date( 'm.d.Y', $sample_date ),
					'11' => date( 'd.m.Y', $sample_date ),
				),
				'validation_type' => 'options',
			),
			'tribe-form-content-end' => array(
				'type' => 'html',
				'html' => '</div>',
			),
		)
	),
);
