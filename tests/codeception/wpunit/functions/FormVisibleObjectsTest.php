<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * pods_form_get_visible_objects() is shared by the render path
 * (pods_form_render_fields), the validation path
 * (pods_form_validate_submitted_fields) and the submission path
 * (pods_form_get_submitted_fields).
 *
 * Conditional logic may only be evaluated for paths that inspect a submission.
 * Pods resolves conditional logic in the browser, so a field dropped at render
 * time can never be revealed again, and on an initial GET there is no submitted
 * data -- every conditionally shown field would resolve to hidden.
 *
 * @group  pods-forms
 * @group  pods-issue-7513
 * @covers ::pods_form_get_visible_objects
 */
class FormVisibleObjectsTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'form_cond_pod';

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type' => 'post_type',
			'name' => $this->pod_name,
		] );

		$api->save_field( [
			'pod_id' => $pod_id,
			'name'   => 'trigger_field',
			'label'  => 'Trigger',
			'type'   => 'text',
		] );

		// Required, and only shown once trigger_field is non-empty.
		$api->save_field( [
			'pod_id'                   => $pod_id,
			'name'                     => 'dependent_field',
			'label'                    => 'Dependent',
			'type'                     => 'text',
			'required'                 => 1,
			'enable_conditional_logic' => 1,
			'conditional_logic'        => [
				'action' => 'show',
				'logic'  => 'all',
				'rules'  => [
					[
						'field'   => 'trigger_field',
						'compare' => 'NOT-EMPTY',
						'value'   => '',
					],
				],
			],
		] );
	}

	public function tearDown(): void {
		unset( $_POST['pods_meta_trigger_field'], $_POST['pods_meta_dependent_field'] );

		parent::tearDown();
	}

	/**
	 * @return array
	 */
	protected function get_field_names( array $options = [] ) {
		$pod = pods( $this->pod_name, null, true );

		$options['return_type'] = 'field';

		$fields = pods_form_get_visible_objects( $pod, $options );

		return array_keys( $fields );
	}

	/**
	 * The render path must always emit the conditionally shown field, otherwise the
	 * browser has nothing to reveal.
	 */
	public function test_render_path_keeps_conditionally_hidden_fields() {
		$names = $this->get_field_names();

		$this->assertContains( 'trigger_field', $names );
		$this->assertContains(
			'dependent_field',
			$names,
			'A conditionally shown field must still be rendered so conditional logic can reveal it.'
		);
	}

	/**
	 * Opting in must drop the field when its condition is not met.
	 */
	public function test_conditional_check_drops_hidden_field_when_condition_unmet() {
		$names = $this->get_field_names( [ 'check_conditional_logic' => true ] );

		$this->assertContains( 'trigger_field', $names );
		$this->assertNotContains( 'dependent_field', $names );
	}

	/**
	 * ...and must keep it once the condition is satisfied.
	 */
	public function test_conditional_check_keeps_field_when_condition_met() {
		$_POST['pods_meta_trigger_field'] = 'filled';

		$names = $this->get_field_names( [ 'check_conditional_logic' => true ] );

		$this->assertContains( 'dependent_field', $names );
	}
}
