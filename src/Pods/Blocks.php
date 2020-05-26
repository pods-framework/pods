<?php

namespace Pods;

use Pods\Whatsit\Block;
use Pods\Whatsit\Storage\Collection;
use Pods\Whatsit\Storage\Post_Type;
use Pods\Whatsit\Store;

/**
 * Blocks functionality class.
 *
 * @since 2.8
 */
class Blocks {

	/**
	 * Register blocks for the Pods Blocks API.
	 *
	 * @since TBD
	 */
	public function register_blocks() {
		$blocks = $this->get_blocks();

		// Pods Blocks API.
		$pods_blocks_options_file = file_get_contents( PODS_DIR . 'ui/js/blocks/pods-blocks-api.min.asset.json' );

		$pods_blocks_options = json_decode( $pods_blocks_options_file, true );

		wp_register_script(
			'pods-blocks-api',
			PODS_URL . 'ui/js/blocks/pods-blocks-api.min.js',
			$pods_blocks_options['dependencies'],
			$pods_blocks_options['version'],
			true
		);

		wp_set_script_translations( 'pods-blocks-api', 'pods' );

		wp_localize_script( 'pods-blocks-api', 'podsBlocksConfig', [
			'blocks' => $blocks,
		] );

		foreach ( $blocks as $block ) {
			$no_fields = $block;

			unset( $no_fields['fields'] );

			register_block_type( $block['blockName'], $no_fields );
		}
	}

	/**
	 * Get list of registered blocks for the Pods Blocks API.
	 *
	 * @since TBD
	 *
	 * @return array List of registered blocks.
	 */
	public function get_blocks() {
		$api = pods_api();

		/** @var Block[] $blocks */
		$blocks = $api->_load_objects( [
			'object_type' => 'block',
		] );

		return array_map( static function( $block ) {
			return $block->get_block_args();
		}, $blocks );

		return [
			[
				'editor_script' => 'pods-blocks-api',
				'blockName'     => 'pods/test-block',
				'renderType'    => 'js',
				'category'      => 'layout',
				'description'   => 'A test block to test defining the fields.',
				'icon'          => 'editor-insertmore',
				'keywords'      => [
					'test',
				],
				'supports'      => [
					'html' => false,
				],
				'title'         => 'Pods Test Block',
				'template'      => '<div class="some_class">
				<p>
					<strong>A text field:</strong><br>
					{@textField}
				</p>
				<p>
					<strong>A textarea field:</strong><br>
					{@textareaField}
				</p>
				<!--<p>
					<strong>A rich text field:</strong><br>
					{@richTextField}
				</p>-->
				<p>
					<strong>A checkbox field:</strong><br>
					{@checkboxField}
				</p>
				<p>
					<strong>A group of checkboxes:</strong><br>
					{@checkboxGroup}
				</p>
				<p>
					<strong>A group of radio buttons:</strong><br>
					{@radioControl}
				</p>
				<p>
					<strong>A select control:</strong><br>
					{@selectControl}
				</p>
				<p>
					<strong>A multiple select control:</strong><br>
					{@multipleSelectControl}
				</p>
				<p>
					<strong>A date/time field:</strong><br>
					{@dateTimeField}
				</p>
				<p>
					<strong>A number field:</strong><br>
					{@numberField}
				</p>
				<p>
					<strong>An uploaded file:</strong><br>
					{@mediaUpload}
				</p>
				<p>
					<strong>A color:</strong><br>
					{@colorPicker}
				</p>
			</div>',
				'fields'        => [
					[
						'type'             => 'TextControl',
						'name'             => 'textField',
						'fieldOptions'     => [
							'className' => 'text__container',
							'type'      => 'text',
							'help'      => 'Some help text',
							'label'     => 'Label for the text field',
						],
						'attributeOptions' => [
							'type' => 'string',
						],
					],
					[
						'type'             => 'TextareaControl',
						'name'             => 'textareaField',
						'fieldOptions'     => [
							'className' => 'textarea__container',
							'help'      => 'Some help text',
							'label'     => 'Label for the textarea field',
							'auto_p'    => true,
						],
						'attributeOptions' => [
							'type' => 'string',
						],
					],
					// @todo Add support for RichText at a later time.
					// [
					// 	'type'             => 'RichText',
					// 	'name'             => 'richTextField',
					// 	'fieldOptions'     => [
					// 		'tagName'   => 'p',
					// 		'className' => 'custom__container',
					// 		'label'     => 'Label for the richtext field',
					// 	],
					// 	'attributeOptions' => [
					// 		'type' => 'string',
					// 	],
					// ],
					[
						'type'             => 'CheckboxControl',
						'name'             => 'checkboxField',
						'fieldOptions'     => [
							'heading' => 'Checkbox Field (single)',
							'label'   => 'Checkbox single',
							'help'    => 'Additional help text',
						],
						'attributeOptions' => [
							'type' => 'boolean',
							//'default' => true,
						],
					],
					[
						'type'             => 'CheckboxGroup',
						'name'             => 'checkboxGroup',
						'fieldOptions'     => [
							'heading' => 'Checkbox Field (multiple)',
							'help'    => 'Additional help text',
							'options' => [
								[
									'label' => 'First Option',
									'value' => 'first',
								],
								[
									'label' => 'Second Option',
									'value' => 'second',
								],
								[
									'label' => 'Third Option',
									'value' => 'third',
								],
							],
						],
						'attributeOptions' => [
							'type' => 'array',
						],
					],
					[
						'type'             => 'RadioControl',
						'name'             => 'radioControl',
						'fieldOptions'     => [
							'heading' => 'Radio Controls (multiple)',
							'help'    => 'Additional help text',
							'options' => [
								[
									'label' => 'First Option',
									'value' => 'first',
								],
								[
									'label' => 'Second Option',
									'value' => 'second',
								],
								[
									'label' => 'Third Option',
									'value' => 'third',
								],
							],
						],
						'attributeOptions' => [
							'type' => 'string',
						],
					],
					[
						'type'             => 'SelectControl',
						'name'             => 'selectControl',
						'fieldOptions'     => [
							'heading' => 'Select Control',
							'help'    => 'Additional help text',
							'options' => [
								[
									'label' => 'First Option',
									'value' => 'first',
								],
								[
									'label' => 'Second Option',
									'value' => 'second',
								],
								[
									'label' => 'Third Option',
									'value' => 'third',
								],
							],
						],
						'attributeOptions' => [
							'type' => 'object',
						],
					],
					[
						'type'             => 'SelectControl',
						'name'             => 'multipleSelectControl',
						'fieldOptions'     => [
							'multiple' => true,
							'heading'  => 'Select Control (Multiple)',
							'help'     => 'Additional help text',
							'options'  => [
								[
									'label' => 'First Option',
									'value' => 'first',
								],
								[
									'label' => 'Second Option',
									'value' => 'second',
								],
								[
									'label' => 'Third Option',
									'value' => 'third',
								],
							],
						],
						'attributeOptions' => [
							'type' => 'array',
						],
					],
					[
						'type'             => 'DateTimePicker',
						'name'             => 'dateTimeField',
						'fieldOptions'     => [
							'is12Hour' => true,
							'label'    => 'Label for the datetime field',
						],
						'attributeOptions' => [
							'type' => 'string',
						],
					],
					[
						'type'             => 'NumberControl',
						'name'             => 'numberField',
						'fieldOptions'     => [
							'isShiftStepEnabled' => false,
							'shiftStep'          => false,
							'step'               => 1,
							'label'              => 'Label for the number field',
						],
						'attributeOptions' => [
							'type' => 'number',
						],
					],
					[
						'type'             => 'MediaUpload',
						'name'             => 'mediaUpload',
						'fieldOptions'     => [
							'label' => 'Media Uploader',
						],
						'attributeOptions' => [
							'type' => 'object',
						],
					],
					[
						'type'             => 'ColorPicker',
						'name'             => 'colorPicker',
						'fieldOptions'     => [
							'label' => 'Color Picker Field',
						],
						'attributeOptions' => [
							'type' => 'string',
						],
					],
				],
			],
		];
	}
}
