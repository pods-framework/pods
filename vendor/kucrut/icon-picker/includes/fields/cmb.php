<?php

/**
 * 'icon' field type for Custom Meta Boxes
 *
 * @link    https://github.com/humanmade/Custom-Meta-Boxes/wiki/Adding-your-own-field-types CMB Wiki
 * @version 0.1.1
 * @since   Icon_Picker 0.2.0
 */
class Icon_Picker_Field_Cmb extends CMB_Field {
	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 * @see   {CMB_Field::__construct()}
	 */
	public function __construct( $name, $title, array $values, $args = array() ) {
		parent::__construct( $name, $title, $values, $args );
		Icon_Picker::instance()->load();
	}

	/**
	 * Parse save values
	 *
	 * When used as a sub-field of a `group` field, wrap the values with array.
	 *
	 * @since 0.1.1
	 */
	public function parse_save_values() {
		if ( ! empty( $this->parent ) ) {
			$this->values = array( $this->values );
		}
	}

	/**
	 * Display the field
	 *
	 * @since 0.1.0
	 * @see   {CMB_Field::html()}
	 */
	public function html() {
		icon_picker_field( array(
			'id'    => $this->id,
			'name'  => $this->get_the_name_attr(),
			'value' => $this->get_value(),
		) );
	}
}
