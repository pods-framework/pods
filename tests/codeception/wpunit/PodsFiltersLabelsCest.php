<?php

/**
 * Tests for Pods::filters() front-end filter form labels (WCAG).
 *
 * @group pods-filters-labels
 */
class PodsFiltersLabelsCest {
	/**
	 * @var Pods
	 */
	private $pod;

	public function _before( \Codeception\Module\WPBrowser $I ) {
		// Create a test pod with pick fields
		$this->pod = pods( 'post_type', 'test-filter-labels' );

		if ( ! $this->pod->exists() ) {
			// Minimal pod setup via Pods API
			$pod_data = array(
				'name'        => 'test-filter-labels',
				'label'       => 'Test Filter Labels',
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
					array(
						'name'        => 'test_pick2',
						'label'       => 'Second Pick',
						'type'        => 'pick',
						'pick_object' => 'post_tag',
						'pick_format_single' => 'dropdown',
					),
				),
			);
			pods_api()->save_pod( $pod_data );
			$this->pod = pods( 'post_type', 'test-filter-labels' );
		}
	}

	public function _after( \Codeception\Module\WPBrowser $I ) {
		if ( $this->pod && $this->pod->exists() ) {
			pods_api()->delete_pod( 'test-filter-labels' );
		}
	}

	/**
	 * Test that pick filter has visible <label> with for=id matching select id.
	 *
	 * @covers Pods::filters()
	 */
	public function testFilterHasVisibleLabel( \Codeception\Module\WPBrowser $I ) {
		$output = $this->pod->filters( array(
			'fields' => 'test_pick,test_pick2',
			'label'  => 'Filter',
		) );

		// Each pick filter should have a <label> with for="pods-form-ui-filter_test_pick"
		$I->assertStringContainsString( 'for="pods-form-ui-filter_test_pick"', $output );
		$I->assertStringContainsString( 'for="pods-form-ui-filter_test_pick2"', $output );

		// Label text should contain "Filter by {field label}"
		$I->assertStringContainsString( 'Filter by Test Pick', $output );
		$I->assertStringContainsString( 'Filter by Second Pick', $output );

		// Select should have matching id
		$I->assertStringContainsString( 'id="pods-form-ui-filter_test_pick"', $output );
		$I->assertStringContainsString( 'id="pods-form-ui-filter_test_pick2"', $output );
	}

	/**
	 * Test that search input has visible label + id + placeholder + aria-label.
	 *
	 * @covers Pods::filters()
	 */
	public function testSearchInputHasLabel( \Codeception\Module\WPBrowser $I ) {
		$output = $this->pod->filters( array(
			'fields' => 'test_pick',
			'label'  => 'Filter',
		) );

		$search_id = 'pods-form-filters-search-test-filter-labels';

		// Visible label exists
		$I->assertStringContainsString( 'for="' . $search_id . '"', $output );
		$I->assertStringContainsString( '>Search<', $output );

		// Search input has matching id
		$I->assertStringContainsString( 'id="' . $search_id . '"', $output );

		// Placeholder present
		$I->assertStringContainsString( 'placeholder="Search…"', $output );

		// aria-label present
		$I->assertStringContainsString( 'aria-label="Search"', $output );
	}

	/**
	 * Test that placeholder option (-- Label --) is NOT rendered when label is visible.
	 *
	 * @covers Pods::filters()
	 */
	public function testNoPlaceholderOptionWhenLabelVisible( \Codeception\Module\WPBrowser $I ) {
		$output = $this->pod->filters( array(
			'fields' => 'test_pick',
			'label'  => 'Filter',
		) );

		// The placeholder option "-- Test Pick --" should not be present
		$I->assertStringNotContainsString( '-- Test Pick --', $output );

		// First option should be a real category, not the placeholder
		// (we can't easily test exact option without data, but placeholder absence is the key)
	}
}