<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Block_Field class.
 *
 * @since 2.8.0
 */
class Block_Field extends Field {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block-field';

	/**
	 * Get list of block args used for each field type.
	 *
	 * @since 2.8.0
	 *
	 * @return array[] List of block args used for each field type.
	 */
	protected function get_block_arg_mapping() {
		return [
			'text'      => [
				'type'             => 'TextControl',
				'fieldOptions'     => [
					'className' => 'text__container',
					'type'      => 'text',
				],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
			'paragraph' => [
				'type'             => 'TextareaControl',
				'fieldOptions'     => [
					'className' => 'textarea__container',
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
			'datetime'  => [
				'type'             => 'DateTimePicker',
				'fieldOptions'     => [
					'is12Hour' => true,
				],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
			'number'    => [
				'type'             => 'NumberControl',
				'fieldOptions'     => [
					'isShiftStepEnabled' => false,
					'shiftStep'          => false,
					'step'               => 1,
				],
				'attributeOptions' => [
					'type' => 'number',
				],
			],
			'file'      => [
				'type'             => 'MediaUpload',
				'fieldOptions'     => [],
				'attributeOptions' => [
					'type' => 'object',
				],
			],
			'color'     => [
				'type'             => 'ColorPicker',
				'fieldOptions'     => [],
				'attributeOptions' => [
					'type' => 'string',
				],
			],
		];
	}

	/**
	 * Get list of Block API arguments to use.
	 *
	 * @since 2.8.0
	 *
	 * @return array|null List of Block API arguments or null if not valid.
	 */
	public function get_block_args() {
		$field_mapping = $this->get_block_arg_mapping();

		$type = $this->get_arg( 'type' );

		if ( 'pick' === $type ) {
			$field_mapping[ $type ] = $this->get_pick_block_args();
		} elseif ( 'boolean' === $type ) {
			$field_mapping[ $type ] = $this->get_boolean_block_args();
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

		if ( 'boolean' !== $type ) {
			$block_args['fieldOptions']['label'] = $this->get_arg( 'label' );

			$default_value = $this->get_arg( 'default' );

			if ( 'pick' !== $type && ! in_array( $default_value, [ '', null ], true ) ) {
				$block_args['attributeOptions']['default'] = $default_value;
			}
		}

		return $block_args;
	}

	/**
	 * Get block args for a pick field type.
	 *
	 * @return array Block args.
	 */
	public function get_pick_block_args() {
		$format_type   = $this->get_arg( 'pick_format_type', 'single' );
		$format_single = $this->get_arg( 'pick_format_single', 'dropdown' );
		$format_multi  = $this->get_arg( 'pick_format_multi', 'checkbox' );

		// Support raw data for now.
		$raw_data = (array) $this->get_arg( 'data', [] );
		$data     = [];

		foreach ( $raw_data as $key => $item ) {
			if ( ! is_array( $item ) ) {
				$item = [
					'label' => $item,
					'value' => $key,
				];
			}

			if ( ! isset( $item['label'], $item['value'] ) ) {
				continue;
			}

			$data[] = $item;
		}

		$label   = $this->get_arg( 'label' );
		$default = $this->get_arg( 'default', '' );

		if ( 'single' === $format_type ) {
			if ( 'radio' === $format_single ) {
				return [
					'type'             => 'RadioControl',
					'fieldOptions'     => [
						'heading' => $label,
						'options' => $data,
					],
					'attributeOptions' => [
						'type'    => 'string',
						'default' => $default,
					],
				];
			}

			foreach ( $data as $data_value ) {
				if ( $default === $data_value['value'] ) {
					$default = $data_value;

					break;
				}
			}

			return [
				'type'             => 'SelectControl',
				'fieldOptions'     => [
					'heading' => $label,
					'options' => $data,
				],
				'attributeOptions' => [
					'type'    => 'object',
					'default' => $default,
				],
			];
		}

		if ( in_array( $format_multi, [ 'multiselect', 'autocomplete' ], true ) ) {
			return [
				'type'             => 'SelectControl',
				'fieldOptions'     => [
					'multiple' => true,
					'heading'  => $label,
					'options'  => $data,
				],
				'attributeOptions' => [
					'type' => 'array',
				],
			];
		}

		return [
			'type'             => 'CheckboxGroup',
			'name'             => 'checkboxGroup',
			'fieldOptions'     => [
				'heading' => $label,
				'options' => $data,
			],
			'attributeOptions' => [
				'type' => 'array',
			],
		];
	}

	/**
	 * Get block args for a boolean field type.
	 *
	 * @return array Block args.
	 */
	public function get_boolean_block_args() {
		$format_type = $this->get_arg( 'boolean_format_type', 'checkbox' );

		$data = [
			[
				'label' => $this->get_arg( 'boolean_yes_label', __( 'Yes', 'pods' ) ),
				'value' => 1,
			],
			[
				'label' => $this->get_arg( 'boolean_no_label', __( 'No', 'pods' ) ),
				'value' => 0,
			],
		];

		$label   = $this->get_arg( 'label' );
		$default = (boolean) $this->get_arg( 'default', 0 );

		if ( 'radio' === $format_type ) {
			return [
				'type'             => 'RadioControl',
				'fieldOptions'     => [
					'heading' => $label,
					'options' => $data,
				],
				'attributeOptions' => [
					'type'    => 'string',
					'default' => $default,
				],
			];
		}

		if ( 'dropdown' === $format_type ) {
			return [
				'type'             => 'SelectControl',
				'fieldOptions'     => [
					'heading' => $label,
					'options' => $data,
				],
				'attributeOptions' => [
					'type'    => 'object',
					'default' => $default,
				],
			];
		}

		return [
			'type'             => 'CheckboxControl',
			'fieldOptions'     => [
				'heading' => $label,
				'label'   => $data[0]['label'],
			],
			'attributeOptions' => [
				'type'    => 'boolean',
				'default' => $default,
			],
		];
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
	public function get_arg( $arg, $default = null, $strict = false ) {
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
