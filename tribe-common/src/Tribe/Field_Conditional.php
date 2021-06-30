<?php

/**
 * Class Tribe__Field_Conditional
 *
 * A work-around for PHP 5.2 lack of closure support to wrap
 *
 * Example usage: in the context of the definition of a list of fields to be fed to the Tribe__Settings class:
 *
 *      $fields = array(
 *          'foo' => array(
 *              'type' => 'checkbox_bool',
 *              // ...
 *          ),
 *          'bar' => array(
 *              'type' => 'text'
 *              'validate_if' => new Tribe__Field_Conditional( 'foo', 'tribe_is_truthy' )
 *              'conditional' => tribe_is_truthy( tribe_get_option( 'foo' ) ),
 *              // ...
 *          ),
 *      );
 *
 * The above will modify the validation logic to make it so that the field will not be validated if
 * the parent (`foo` in the example) is not "truthy".
 * If you need to hide/show the field conditionally use the `conditional` attribute of the field.
 *
 * @since 4.7.7
 */
class Tribe__Field_Conditional {

	/**
	 * @var string The slug of the field the condition
	 */
	protected $depends_on;
	/**
	 * @var bool
	 */
	protected $condition;

	/**
	 * Tribe__Field_Conditional constructor.
	 *
	 * @since 4.7.7
	 *
	 * @param      string    $depends_on_field The slug or identifier of the parent field.
	 * @param mixed|callable $condition        Either a valid callable function or method or a
	 *                                         value that will be used for a shallow comparison.
	 */
	public function __construct( $depends_on_field, $condition = true ) {
		$this->depends_on = $depends_on_field;
		$this->condition  = $condition;
	}

	/**
	 * @param       mixed $value  The value to check, typically the parent field value.
	 * @param array       $fields An array of all the current fields; this will be passed to
	 *                            the condition callback function for context if the condition
	 *                            is a callable function or method.
	 *
	 * @return bool Whether the check was successful (the parent field does have the required
	 *              value) or not.
	 */
	public function check( $value, array $fields ) {
		return is_callable( $this->condition )
			? call_user_func( $this->condition, $value, $fields )
			: $value == $this->condition;
	}

	/**
	 * Return the id/slug of the field this condition depends on.
	 *
	 * @since 4.7.7
	 *
	 * @return string
	 */
	public function depends_on() {
		return $this->depends_on;
	}
}
