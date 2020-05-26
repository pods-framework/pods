<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Block_Field class.
 *
 * @since 2.8
 */
class Block_Field extends Field {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block-field';

	/**
	 * Get list of block args used for each field type.
	 *
	 * @since TBD
	 *
	 * @return array[] List of block args used for each field type.
	 */
	protected function get_block_arg_mapping() {
		return [
			'text' => [
				'type'             => 'TextControl',
				//'name'             => 'textField',
				'fieldOptions'     => [
					'className' => 'text__container',
					'type'      => 'text',
					//'help'      => 'Some help text',
					//'label'     => 'Label for the text field',
				],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
			'paragraph' => [
				'type'             => 'TextareaControl',
				//'name'             => 'textareaField',
				'fieldOptions'     => [
					'className' => 'textarea__container',
					//'help'      => 'Some help text',
					//'label'     => 'Label for the textarea field',
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
			'boolean' => [
				'type'             => 'CheckboxControl',
				//'name'             => 'checkboxField',
				'fieldOptions'     => [
					//'heading' => 'Checkbox Field (single)',
					//'label'   => 'Checkbox single',
					//'help'    => 'Additional help text',
				],
				'attributeOptions' => [
					'type' => 'boolean',
					//'default' => true,
				],
			],
			/*[
				'type'             => 'CheckboxControl',
				//'name'             => 'checkboxField',
				'fieldOptions'     => [
					//'heading' => 'Checkbox Field (single)',
					//'label'   => 'Checkbox single',
					//'help'    => 'Additional help text',
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
			],*/
			'datetime' => [
				'type'             => 'DateTimePicker',
				//'name'             => 'dateTimeField',
				'fieldOptions'     => [
					'is12Hour' => true,
					//'label'    => 'Label for the datetime field',
				],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
			'number' => [
				'type'             => 'NumberControl',
				//'name'             => 'numberField',
				'fieldOptions'     => [
					'isShiftStepEnabled' => false,
					'shiftStep'          => false,
					'step'               => 1,
					//'label'              => 'Label for the number field',
				],
				'attributeOptions' => [
					'type' => 'number',
				],
			],
			'file' => [
				'type'             => 'MediaUpload',
				//'name'             => 'mediaUpload',
				'fieldOptions'     => [
					//'label' => 'Media Uploader',
				],
				'attributeOptions' => [
					'type' => 'object',
				],
			],
			'color' => [
				'type'             => 'ColorPicker',
				//'name'             => 'colorPicker',
				'fieldOptions'     => [
					//'label' => 'Color Picker Field',
				],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
		];
	}

	/**
	 * Get list of Block API arguments to use.
	 *
	 * @since 2.8
	 *
	 * @return array|null List of Block API arguments or null if not valid.
	 */
	public function get_block_args() {
		$field_mapping = $this->get_block_arg_mapping();

		$type = $this->get_arg( 'type' );

		if ( 'pick' === $type ) {
			return $this->get_pick_block_args();
		}

		if ( 'boolean' === $type ) {
			return $this->get_boolean_block_args();
		}

		if ( ! isset( $field_mapping[ $type ] ) ) {
			return null;
		}

		if ( 'file' === $type && 'multi' === $this->get_arg( 'file__format_type' ) ) {
			return null;
		}

		$block_args = $field_mapping[ $type ];

		// Handle setting name/label/help.
		$name = $this->get_arg( 'name' );

		$block_args['name'] = $name;

		$block_args['fieldOptions']['help'] = $this->get_arg( 'description' );
		$block_args['fieldOptions']['label'] = $this->get_arg( 'label' );

		$default = $this->get_arg( 'default' );

		if ( ! in_array( $default, [ '', null ], true ) ) {
			$block_args['attributeOptions']['default'] = $default;
		}

		return $block_args;
	}

	public function get_pick_block_args() {
		return [];
	}

	public function get_boolean_block_args() {
		return [];
	}

	/**
	 * Get list of block args used for each field type.
	 *
	 * @since TBD
	 *
	 * @return array[] List of block args used for each field type.
	 */
	protected function get_block_attribute_arg_mapping() {
		return [
			'text' => [
				'type' => 'string',
			],
			'paragraph' => [
				'type' => 'string',
			],
			'boolean' => [
				'type' => 'boolean',
			],
			'datetime' => [
				'type' => 'string',
			],
			'number' => [
				'type' => 'number',
			],
			'file' => [
				'type' => 'object',
			],
			'color' => [
				'type' => 'string',
			],
		];
	}

	public function get_block_attribute_args() {
		$field_mapping = $this->get_block_attribute_arg_mapping();

		$type = $this->get_arg( 'type' );

		if ( 'pick' === $type ) {
			return null;
			//return $this->get_pick_block_args();
		}

		if ( 'boolean' === $type ) {
			return null;
			//return $this->get_boolean_block_args();
		}

		if ( ! isset( $field_mapping[ $type ] ) ) {
			return null;
		}

		if ( 'file' === $type && 'multi' === $this->get_arg( 'file__format_type' ) ) {
			return null;
		}

		return $field_mapping[ $type ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null ) {
		if ( 'block' === $arg ) {
			return $this->get_parent_name();
		}

		return Whatsit::get_arg( $arg, $default );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_related_object_type() {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_related_object_name() {
		return null;
	}
}
