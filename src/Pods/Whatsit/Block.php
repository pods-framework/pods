<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Block class.
 *
 * @since 2.8
 */
class Block extends Pod {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block';

	/**
	 * Get list of Block API arguments to use.
	 *
	 * @since 2.8
	 *
	 * @return array List of Block API arguments.
	 */
	public function get_block_args() {
		return [
			'title'         => $this->get_arg( 'label' ),
			'description'   => $this->get_arg( 'description' ),
			'blockName'     => 'pods/' . $this->get_arg( 'name' ),
			'editor_script' => $this->get_arg( 'editor_script', 'pods-blocks-api' ),
			'renderType'    => $this->get_arg( 'renderType', 'js' ),
			'category'      => $this->get_arg( 'category', 'layout' ),
			'icon'          => $this->get_arg( 'icon', 'editor-insertmore' ),
			'keywords'      => \Tribe__Utils__Array::list_to_array( $this->get_arg( 'keywords', 'pods' ) ),
			'template'      => $this->get_arg( 'template', '' ),
			'supports'      => $this->get_arg( 'supports', [
				'html' => false,
			] ),
			'fields'        => $this->get_block_fields(),
		];
	}

	/**
	 * Get list of Block API fields for the block.
	 *
	 * @since 2.8
	 *
	 * @return array List of Block API fields.
	 */
	public function get_block_fields() {
		return [];
		$fields = $this->get_fields();

		foreach ( $fields as $field ) {

		}

		return [
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
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		$args = Whatsit::get_args();

		// Pods generally have no parent, group, or order.
		unset( $args['parent'], $args['group'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_object_fields() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		return [];
	}
}
