<?php

/**
 * Tests for Pods filter label customization via pods_form_ui_label_text filter.
 *
 * @group pods-filters-labels
 */
class PodsFiltersLabelFilterCest {
	/**
	 * @var Pods
	 */
	private $pod;

	/**
	 * @var string
	 */
	private $original_filter;

	public function _before( \Codeception\Module\WPBrowser $I ) {
		// Create a test pod with pick fields
		$this->pod = pods( 'post_type', 'test-filter-label-filter' );

		if ( ! $this->pod->exists() ) {
			$pod_data = array(
				'name'        => 'test-filter-label-filter',
				'label'       => 'Test Filter Label Filter',
				'type'        => 'post_type',
				'object'      => 'post',
				'fields'      => array(
					array(
						'name'        => 'test_pick',
						'label'       => 'Test Pick',
						'type'        => 'pick',
						'pick_object' => 'category',
						'pick_format_single' => 'dropdown',
					),
				),
			);
			pods_api()->save_pod( $pod_data );
			$this->pod = pods( 'post_type', 'test-filter-label-filter' );
		}

		// Store original filter (if any)
		$this->original_filter = has_filter( 'pods_form_ui_label_text' );
	}

	public function _after( \Codeception\Module\WPBrowser $I ) {
		// Clean up filter
		remove_all_filters( 'pods_form_ui_label_text' );
		if ( $this->original_filter ) {
			// Note: Codeception doesn't easily restore previous callbacks, this is best effort
		}

		if ( $this->pod && $this->pod->exists() ) {
			pods_api()->delete_pod( 'test-filter-label-filter' );
		}
	}

	/**
	 * Test custom label via pods_form_ui_label_text filter.
	 *
	 * @covers Pods::filters()
	 */
	public function testCustomLabelViaFilter( \Codeception\Module\WPBrowser $I ) {
		// Hook the filter to customize the label
		add_filter( 'pods_form_ui_label_text', function ( $label, $name, $help, $options ) {
			if ( 'filter_test_pick' === $name ) {
				return 'Custom: ' . $label;
			}
			return $label;
		}, 10, 4 );

		$output = $this->pod->filters( array(
			'fields' => 'test_pick',
			'label'  => 'Filter',
		) );

		// Custom label should be rendered
		$I->assertStringContainsString( 'Custom: Filter by Test Pick', $output );
	}

	/**
	 * Test empty label via pods_form_ui_label_text filter (suppresses visible label).
	 *
	 * @covers Pods::filters()
	 */
	public function testSuppressLabelViaFilter( \Codeception\Module\WPBrowser $I ) {
		// Hook the filter to return empty string
		add_filter( 'pods_form_ui_label_text', function ( $label, $name, $help, $options ) {
			if ( 'filter_test_pick' === $name ) {
				return '';
			}
			return $label;
		}, 10, 4 );

		$output = $this->pod->filters( array(
			'fields' => 'test_pick',
			'label'  => 'Filter',
		) );

		// No visible label should be rendered for this field
		$I->assertStringNotContainsString( 'for="pods-form-ui-filter_test_pick"', $output );
		$I->assertStringNotContainsString( 'Filter by Test Pick', $output );

		// The placeholder option "-- Test Pick --" should be present since label is suppressed
		$I->assertStringContainsString( '-- Test Pick --', $output );
	}
}