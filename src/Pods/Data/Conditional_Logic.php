<?php

namespace Pods\Data;

use Pods\Whatsit;

/**
 * Conditional logic class.
 *
 * @since 3.0
 */
class Conditional_Logic {

	/**
	 * The action to take (show/hide).
	 *
	 * @var string
	 */
	protected $action = 'show';

	/**
	 * The logic to use (any/all).
	 *
	 * @var string
	 */
	protected $logic = 'any';

	/**
	 * The conditional rules.
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * The full list of conditional logic sets.
	 *
	 * @var array
	 */
	protected $logic_sets = [];

	/**
	 * Set up the object.
	 *
	 * @since 3.0
	 *
	 * @param string $action     The action to take (show/hide).
	 * @param string $logic      The logic to use (any/all).
	 * @param array  $rules      The conditional rules.
	 * @param array  $logic_sets The full list of conditional logic sets.
	 */
	public function __construct( string $action = 'show', string $logic = 'any', array $rules = [], array $logic_sets = [] ) {
		if ( $action ) {
			$this->set_action( $action );
		}

		if ( $logic ) {
			$this->set_logic( $logic );
		}

		if ( $rules ) {
			$this->set_rules( $rules );
		}

		if ( $logic_sets ) {
			$this->set_logic_sets( $logic_sets );
		}
	}

	/**
	 * Set up the conditional logic object from a object.
	 *
	 * @since 3.0
	 *
	 * @param Whatsit|array $object The object data.
	 *
	 * @return Conditional_Logic|null The conditional logic object or null if rules not set.
	 */
	public static function maybe_setup_from_object( $object ): ?Conditional_Logic {
		if ( $object instanceof Whatsit && $object->is_conditional_logic_enabled() ) {
			$object_logic = $object->get_conditional_logic_config();

			if ( empty( $object_logic ) ) {
				return null;
			}

			return self::setup_from_object(
				$object,
				$object_logic['action'],
				$object_logic['logic'],
				$object_logic['rules']
			);
		}

		return self::maybe_setup_from_old_syntax( $object );
	}

	/**
	 * Maybe migrate the field name from old prefix naming convention.
	 *
	 * @since 3.0
	 *
	 * @param string|int $field_name The field name.
	 *
	 * @return string The migrated field name.
	 */
	public static function maybe_migrate_field_name( $field_name ): string {
		if ( ! is_string( $field_name ) ) {
			return (string) $field_name;
		}

		if ( 0 === strpos( $field_name, 'pods_field_' ) ) {
			$field_name = substr( $field_name, strlen( 'pods_field_' ) );
		} elseif ( 0 === strpos( $field_name, 'pods_meta_' ) ) {
			$field_name = substr( $field_name, strlen( 'pods_meta_' ) );
		}

		return $field_name;
	}

	/**
	 * Maybe set up the conditional logic object from old syntax.
	 *
	 * @since 3.0
	 *
	 * @param Whatsit|array $object The object data.
	 *
	 * @return Conditional_Logic|null The conditional logic object or null if rules not set.
	 */
	public static function maybe_setup_from_old_syntax( $object ): ?Conditional_Logic {
		$old_syntax = [
			'depends-on'       => pods_v( 'depends-on', $object, [], true ),
			'depends-on-any'   => pods_v( 'depends-on-any', $object, [], true ),
			'depends-on-multi' => pods_v( 'depends-on-multi', $object, [], true ),
			'excludes-on'      => pods_v( 'excludes-on', $object, [], true ),
			'wildcard-on'      => pods_v( 'wildcard-on', $object, [], true ),
		];

		foreach ( $old_syntax as $old_syntax_key => $old_syntax_value ) {
			if ( empty( $old_syntax_value ) ) {
				$old_syntax_value = [];
			} elseif ( ! is_array( $old_syntax_value ) ) {
				$old_syntax_value = pods_maybe_safely_unserialize( $old_syntax_value );

				if ( ! is_array( $old_syntax_value ) ) {
					$old_syntax_value = [];
				}
			}

			$old_syntax[ $old_syntax_key ] = $old_syntax_value;
		}

		$action     = 'show';
		$rules      = [];
		$logic_sets = [];

		if ( $old_syntax['depends-on'] ) {
			$logic = 'all';
			$rules  = [];

			foreach ( $old_syntax['depends-on'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'in' : '=' ),
					'value'   => $value,
				];
			}

			$logic_sets[] = [
				'action' => $action,
				'logic' => $logic,
				'rules' => $rules,
			];
		}

		if ( $old_syntax['depends-on-any'] ) {
			$logic  = 'any';
			$rules  = [];

			foreach ( $old_syntax['depends-on-any'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'in' : '=' ),
					'value'   => $value,
				];
			}

			if ( ! empty( $rules ) ) {
				$logic_sets[] = [
					'action' => $action,
					'logic'  => $logic,
					'rules'  => $rules,
				];
			}
		}

		if ( $old_syntax['depends-on-multi'] ) {
			$logic = 'all';
			$rules  = [];

			foreach ( $old_syntax['depends-on-multi'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => 'in-values',
					'value'   => $value,
				];
			}

			if ( ! empty( $rules ) ) {
				$logic_sets[] = [
					'action' => $action,
					'logic'  => $logic,
					'rules'  => $rules,
				];
			}
		}

		if ( $old_syntax['excludes-on'] ) {
			$logic  = 'any';
			$rules  = [];

			foreach ( $old_syntax['excludes-on'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'not-in' : '!=' ),
					'value'   => $value,
				];
			}

			if ( ! empty( $rules ) ) {
				$logic_sets[] = [
					'action' => $action,
					'logic'  => $logic,
					'rules'  => $rules,
				];
			}
		}

		if ( $old_syntax['wildcard-on'] ) {
			$logic  = 'any';
			$rules  = [];

			foreach ( $old_syntax['wildcard-on'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$value = (array) $value;

				foreach ( $value as $wildcard_value ) {
					$rules[] = [
						'field'   => $field_name,
						'compare' => 'matches',
						'value'   => $wildcard_value,
					];
				}
			}

			if ( ! empty( $rules ) ) {
				$logic_sets[] = [
					'action' => $action,
					'logic'  => $logic,
					'rules'  => $rules,
				];
			}
		}

		if ( empty( $logic_sets ) ) {
			return null;
		}

		return self::setup_from_object(
			$object,
			$action,
			$logic,
			$rules,
			$logic_sets
		);
	}

	/**
	 * Set up the Conditional Logic instance from an object.
	 *
	 * @since 3.0
	 *
	 * @param Whatsit|array $object     The object data.
	 * @param string        $action     The action to take (show/hide).
	 * @param string        $logic      The logic to use (any/all).
	 * @param array         $rules      The conditional rules.
	 * @param array         $logic_sets The full list of conditional logic sets.
	 */
	private static function setup_from_object( $object, $action, $logic, array $rules, array $logic_sets = [] ): Conditional_Logic {
		foreach ( $logic_sets as $key => $logic_set ) {
			$logic_sets[ $key ] = new self(
				pods_v( 'action', $logic_set, $action, true ),
				pods_v( 'logic', $logic_set, $logic, true ),
				pods_v( 'rules', $logic_set, [] )
			);
		}

		$conditional_logic = new self( $action, $logic, $rules, $logic_sets );

		if ( $object instanceof Whatsit ) {
			/**
			 * Allow filtering the conditional logic object used for a Whatsit object.
			 *
			 * @since 3.0
			 *
			 * @param Conditional_Logic $conditional_logic The conditional logic object.
			 * @param Whatsit           $object            The object data.
			 * @param string            $action            The action to take (show/hide).
			 * @param string            $logic             The logic to use (any/all).
			 * @param array             $rules             The conditional rules.
			 * @param array             $logic_sets        The full list of conditional logic sets.
			 */
			$conditional_logic = apply_filters( 'pods_data_conditional_logic_for_object', $conditional_logic, $object, $action, $logic, $rules, $logic_sets );
		}

		return $conditional_logic;
	}

	/**
	 * Get the action.
	 *
	 * @since 3.0
	 *
	 * @return string The action.
	 */
	public function get_action(): string {
		return $this->action;
	}

	/**
	 * Set the action.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action.
	 */
	public function set_action( string $action ): void {
		if ( '' !== $action ) {
			$this->action = $action;
		}
	}

	/**
	 * Get the logic.
	 *
	 * @since 3.0
	 *
	 * @return string The logic.
	 */
	public function get_logic(): string {
		return $this->logic;
	}

	/**
	 * Set the logic.
	 *
	 * @since 3.0
	 *
	 * @param string $logic The logic.
	 */
	public function set_logic( string $logic ): void {
		if ( '' !== $logic ) {
			$this->logic = $logic;
		}
	}

	/**
	 * Get the conditional rules.
	 *
	 * @since 3.0
	 *
	 * @return array The conditional rules.
	 */
	public function get_rules(): array {
		return $this->rules;
	}

	/**
	 * Set the conditional rules.
	 *
	 * @since 3.0
	 *
	 * @param array $rules The conditional rules.
	 */
	public function set_rules( array $rules ): void {
		$this->rules = $rules;
	}

	/**
	 * Set the conditional logic sets.
	 *
	 * @since TBD
	 *
	 * @param array $logic_sets The conditional logic sets.
	 */
	public function set_logic_sets( array $logic_sets ): void {
		$this->logic_sets = $logic_sets;
	}

	/**
	 * Get the conditional logic data as an array.
	 *
	 * @since 3.0
	 *
	 * @return array The conditional logic data as an array.
	 */
	public function to_array(): array {
		$conditional_logic = [
			'action' => $this->action ?: 'show',
			'logic'  => $this->logic ?: 'any',
			'rules'  => $this->rules,
		];

		if ( $this->logic_sets ) {
			$conditional_logic['logic_sets'] = array_map(
				function ( Conditional_Logic $logic ) {
					return $logic->to_array();
				},
				$this->logic_sets
			);
		}

		return $conditional_logic;
	}

	/**
	 * Determine whether the field is visible.
	 *
	 * @since 3.0
	 *
	 * @param array $values The field values.
	 *
	 * @return bool Whether the field is visible.
	 */
	public function is_visible( array $values ): bool {
		$rules_passed = $this->validate_rules( $values );

		if ( 'show' === $this->action ) {
			// Determine whether rules passed and this field should be shown.
			return $rules_passed;
		}

		// Determine whether rules passed and this field should NOT be shown.
		return ! $rules_passed;
	}

	/**
	 * Determine whether the rules validate for the field values provided.
	 *
	 * @since 3.0
	 *
	 * @param array $values The field values.
	 *
	 * @return bool Whether the rules validate for the field values provided.
	 */
	public function validate_rules( array $values ): bool {
		if ( $this->logic_sets ) {
			// Validate rules across logic sets.
			return !! array_filter(
				array_map(
					function ( Conditional_Logic $logic ) use ( $values ) {
						return $logic->validate_rules( $values );
					},
					$this->logic_sets
				)
			);
		}

		if ( empty( $this->rules ) ) {
			return true;
		}

		$logic = strtoupper( $this->logic );

		$rules_passed     = 0;
		$rules_not_passed = 0;

		foreach ( $this->rules as $rule ) {
			$passed = $this->validate_rule( $rule, $values );

			if ( $passed ) {
				$rules_passed ++;
			} else {
				$rules_not_passed ++;
			}
		}

		if ( 'ANY' === $logic ) {
			return 0 < $rules_passed;
		}

		if ( 'ALL' === $logic ) {
			return 0 === $rules_not_passed;
		}

		return true;
	}

	/**
	 * Helper function to compare values of differing items, which allows strings
	 * to match numbers.
	 *
	 * Comparing an array of 1 item could create false positives, because
	 * [ '123' ] when converted to string === '123', so compare objects (usually arrays)
	 * without using toString().
	 *
	 * @since 3.0
	 *
	 * @param mixed $item1 First item to compare.
	 * @param mixed $item2 Second item to compare.
	 *
	 * @return bool True if matches.
	 */
	public function loose_string_equality_check( $item1, $item2 ): bool {
		// Compare objects (usually arrays) using serialization.
		if ( is_object( $item1 ) || is_object( $item2 ) || is_array( $item1 ) || is_array( $item2 ) ) {
			return wp_json_encode( $item1 ) === wp_json_encode( $item2 );
		}

		// Convert booleans to integers.
		if ( is_bool( $item1 ) ) {
			$item1 = $item1 ? 1 : 0;
		}

		if ( is_bool( $item2 ) ) {
			$item2 = $item2 ? 1 : 0;
		}

		// Attempt to normalize numbers.
		if ( is_numeric( $item1 ) && is_numeric( $item2 ) ) {
			$item1 = (float) $item1;
			$item2 = (float) $item2;
		}

		// Case-insensitive string comparison.
		return strtolower( (string) $item1 ) === strtolower( (string) $item2 );
	}

	/**
	 * Convert a string to an array by splitting on commas.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to convert.
	 *
	 * @return array The converted array.
	 */
	public function convert_string_to_array( $value ): array {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_int( $value ) || is_float( $value ) ) {
			return [ $value ];
		}

		if ( ! is_string( $value ) ) {
			return [];
		}

		// Split by comma and trim whitespace from each item.
		return array_map( 'trim', explode( ',', $value ) );
	}

	/**
	 * Check if a value is considered empty.
	 *
	 * @since 3.0
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool True if the value is empty.
	 */
	public function is_value_empty( $value ): bool {
		return in_array( $value, [ '', null, [], false ], true );
	}

	/**
	 * Perform a string comparison operation.
	 *
	 * @since 3.0
	 *
	 * @param string $operation   The operation to perform: 'contains', 'starts_with', or 'ends_with'.
	 * @param mixed  $rule_value  The value to compare against.
	 * @param mixed  $value_to_test The value to be tested.
	 *
	 * @return bool True if the test passes.
	 */
	public function string_comparison( string $operation, $rule_value, $value_to_test ): bool {
		if ( null !== $value_to_test && ! is_scalar( $value_to_test ) ) {
			if ( ! is_array( $value_to_test ) ) {
				return false;
			}

			$value_to_test = implode( ',', $value_to_test );
		}

		if ( null !== $rule_value && ! is_scalar( $rule_value ) ) {
			if ( ! is_array( $rule_value ) ) {
				return false;
			}

			$rule_value = implode( ',', $rule_value );
		}

		$value_str = strtolower( (string) $value_to_test );
		$rule_str  = strtolower( (string) $rule_value );

		if ( '' === $rule_str ) {
			return true;
		}

		switch ( $operation ) {
			case 'contains':
				if ( function_exists( 'str_contains' ) ) {
					return str_contains( $value_str, $rule_str );
				}
				return false !== stripos( $value_str, $rule_str );

			case 'starts_with':
				if ( function_exists( 'str_starts_with' ) ) {
					return str_starts_with( $value_str, $rule_str );
				}
				return 0 === stripos( $value_str, $rule_str );

			case 'ends_with':
				if ( function_exists( 'str_ends_with' ) ) {
					return str_ends_with( $value_str, $rule_str );
				}
				return 0 === substr_compare( $value_str, $rule_str, - strlen( $rule_str ) );

			default:
				return false;
		}
	}

	/**
	 * Perform a regex match operation.
	 *
	 * @since 3.0
	 *
	 * @param mixed $rule_value  The regex pattern to match against.
	 * @param mixed $value_to_test The value to be tested.
	 *
	 * @return bool True if the test passes.
	 */
	public function regex_match( $rule_value, $value_to_test ): bool {
		$pattern = '/' . str_replace( '/', '\/', (string) $rule_value ) . '/';

		if ( is_array( $value_to_test ) ) {
			foreach ( $value_to_test as $value_item ) {
				if ( 1 === preg_match( $pattern, (string) $value_item ) ) {
					return true;
				}
			}
			return false;
		}

		if ( ! is_scalar( $value_to_test ) ) {
			return false;
		}

		return 1 === preg_match( $pattern, (string) $value_to_test );
	}

	/**
	 * Check if value_to_test is in the rule_value array.
	 *
	 * @since 3.0
	 *
	 * @param mixed $rule_value    The array or string to check against.
	 * @param mixed $value_to_test The value to be tested.
	 * @param bool  $exact         If true, all items must match; if false, any item can match.
	 *
	 * @return bool True if the test passes.
	 */
	public function in_comparison( $rule_value, $value_to_test, bool $exact = false ): bool {
		// We can't compare 'in' if the rule's value is not an array.
		if ( ! is_array( $rule_value ) ) {
			// If ruleValue is a string and valueToTest is an array, convert string to array.
			if ( is_array( $value_to_test ) && is_string( $rule_value ) ) {
				$check_rule_value = $this->convert_string_to_array( $rule_value );

				// Check if values in ruleValue are contained within the array valueToTest.
				if ( $exact ) {
					// ALL items in check_rule_value must be found in value_to_test.
					foreach ( $check_rule_value as $rule_value_item ) {
						$found = false;
						foreach ( $value_to_test as $value_item ) {
							if ( $this->loose_string_equality_check( $rule_value_item, $value_item ) ) {
								$found = true;
								break;
							}
						}
						if ( ! $found ) {
							return false;
						}
					}
					return true;
				} else {
					// ANY item in check_rule_value must be found in value_to_test.
					foreach ( $check_rule_value as $rule_value_item ) {
						foreach ( $value_to_test as $value_item ) {
							if ( $this->loose_string_equality_check( $rule_value_item, $value_item ) ) {
								return true;
							}
						}
					}
					return false;
				}
			}

			return false;
		}

		// value_to_test must be scalar for array comparison.
		if ( ! is_scalar( $value_to_test ) ) {
			return false;
		}

		// Use loose equality check for all comparisons.
		if ( $exact ) {
			// ALL items in rule_value must match value_to_test.
			foreach ( $rule_value as $rule_value_item ) {
				if ( ! $this->loose_string_equality_check( $rule_value_item, $value_to_test ) ) {
					return false;
				}
			}
			return true;
		}

		// ANY item in rule_value must match value_to_test.
		foreach ( $rule_value as $rule_value_item ) {
			if ( $this->loose_string_equality_check( $rule_value_item, $value_to_test ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if rule_value is in the value_to_test array.
	 *
	 * @since 3.0
	 *
	 * @param mixed $rule_value    The value to check for.
	 * @param mixed $value_to_test The array to check within.
	 * @param bool  $exact         If true, all items must match; if false, any item can match.
	 *
	 * @return bool True if the test passes.
	 */
	public function in_values_comparison( $rule_value, $value_to_test, bool $exact = false ): bool {
		// We can't compare 'in values' if valueToTest is not an array.
		if ( ! is_array( $value_to_test ) ) {
			// If valueToTest is a string and ruleValue is an array, convert string to array.
			if ( is_array( $rule_value ) && is_string( $value_to_test ) ) {
				$check_value_to_test = $this->convert_string_to_array( $value_to_test );

				// Check if values in valueToTest are contained within the array ruleValue.
				if ( $exact ) {
					// ALL items in check_value_to_test must be found in rule_value.
					foreach ( $check_value_to_test as $value_item ) {
						$found = false;
						foreach ( $rule_value as $rule_value_item ) {
							if ( $this->loose_string_equality_check( $value_item, $rule_value_item ) ) {
								$found = true;
								break;
							}
						}
						if ( ! $found ) {
							return false;
						}
					}
					return true;
				} else {
					// ANY item in check_value_to_test must be found in rule_value.
					foreach ( $check_value_to_test as $value_item ) {
						foreach ( $rule_value as $rule_value_item ) {
							if ( $this->loose_string_equality_check( $value_item, $rule_value_item ) ) {
								return true;
							}
						}
					}
					return false;
				}
			}

			return false;
		}

		// rule_value must be scalar for array comparison.
		if ( ! is_scalar( $rule_value ) ) {
			return false;
		}

		// Use loose equality check for all comparisons.
		if ( $exact ) {
			// ALL items in value_to_test must match rule_value.
			foreach ( $value_to_test as $value_item ) {
				if ( ! $this->loose_string_equality_check( $value_item, $rule_value ) ) {
					return false;
				}
			}
			return true;
		}

		// ANY item in value_to_test must match rule_value.
		foreach ( $value_to_test as $value_item ) {
			if ( $this->loose_string_equality_check( $value_item, $rule_value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Perform an equality comparison.
	 *
	 * @since 3.0
	 *
	 * @param mixed $rule_value  The value to compare against.
	 * @param mixed $value_to_test The value to be tested.
	 *
	 * @return bool True if the test passes.
	 */
	public function equality_comparison( $rule_value, $value_to_test ): bool {
		if ( ! is_scalar( $value_to_test ) ) {
			return false;
		}

		// Numeric comparisons enforce floats on numeric values for strict checks.
		if ( is_numeric( $rule_value ) ) {
			$rule_value = (float) $rule_value;
		}

		if ( is_numeric( $value_to_test ) ) {
			$value_to_test = (float) $value_to_test;
		}

		return $value_to_test === $rule_value;
	}

	/**
	 * Perform a numeric comparison operation.
	 *
	 * @since 3.0
	 *
	 * @param string $operator    The comparison operator: '<', '<=', '>', or '>='.
	 * @param mixed  $rule_value  The value to compare against.
	 * @param mixed  $value_to_test The value to be tested.
	 *
	 * @return bool True if the test passes.
	 */
	public function numeric_comparison( string $operator, $rule_value, $value_to_test ): bool {
		if ( ! is_scalar( $value_to_test ) ) {
			return false;
		}

		$num_value = (float) $value_to_test;
		$num_rule  = (float) $rule_value;

		switch ( $operator ) {
			case '<':
				return $num_rule < $num_value;
			case '<=':
				return $num_rule <= $num_value;
			case '>':
				return $num_rule > $num_value;
			case '>=':
				return $num_rule >= $num_value;
			default:
				return false;
		}
	}

	/**
	 * Validate a single rule.
	 *
	 * @since 3.0
	 *
	 * @param array $rule   The rule data.
	 * @param array $values The values to check.
	 *
	 * @return bool Whether the rule passes.
	 */
	public function validate_rule( array $rule, array $values ): bool {
		$field   = $rule['field'];
		$compare = ! empty( $rule['compare'] ) ? $rule['compare'] : '=';
		$value   = $rule['value'];

		if ( empty( $field ) || empty( $compare ) ) {
			return true;
		}

		// Format for easier readability.
		$compare = strtoupper( str_replace( '-', ' ', $compare ) );

		$check_value = pods_v( $field, $values );

		// Normalize values for non-empty comparisons.
		if ( ! in_array( $compare, [ 'EMPTY', 'NOT EMPTY' ], true ) ) {
			if ( null === $value ) {
				$value = '';
			} elseif ( is_bool( $value ) ) {
				$value = (int) $value;
			}

			if ( null === $check_value ) {
				$check_value = '';
			} elseif ( is_bool( $check_value ) ) {
				$check_value = (int) $check_value;
			}
		}

		switch ( $compare ) {
			case 'LIKE':
				return $this->string_comparison( 'contains', $value, $check_value );
			case 'NOT LIKE':
				return ! $this->string_comparison( 'contains', $value, $check_value );
			case 'BEGINS':
				return $this->string_comparison( 'starts_with', $value, $check_value );
			case 'NOT BEGINS':
				return ! $this->string_comparison( 'starts_with', $value, $check_value );
			case 'ENDS':
				return $this->string_comparison( 'ends_with', $value, $check_value );
			case 'NOT ENDS':
				return ! $this->string_comparison( 'ends_with', $value, $check_value );
			case 'MATCHES':
				return $this->regex_match( $value, $check_value );
			case 'NOT MATCHES':
				return ! $this->regex_match( $value, $check_value );
			case 'IN':
				return $this->in_comparison( $value, $check_value );
			case 'NOT IN':
				return ! $this->in_comparison( $value, $check_value );
			case 'IN VALUES':
				return $this->in_values_comparison( $value, $check_value );
			case 'NOT IN VALUES':
				return ! $this->in_values_comparison( $value, $check_value );
			case 'ALL':
				return $this->in_comparison( $value, $check_value, true );
			case 'NOT ALL':
				return ! $this->in_comparison( $value, $check_value, true );
			case 'ALL VALUES':
				return $this->in_values_comparison( $value, $check_value, true );
			case 'NOT ALL VALUES':
				return ! $this->in_values_comparison( $value, $check_value, true );
			case 'EMPTY':
				return $this->is_value_empty( $check_value );
			case 'NOT EMPTY':
				return ! $this->is_value_empty( $check_value );
			case '=':
				return $this->equality_comparison( $value, $check_value );
			case '!=':
				return ! $this->equality_comparison( $value, $check_value );
			case '<':
			case '<=':
			case '>':
			case '>=':
				return $this->numeric_comparison( $compare, $value, $check_value );
			default:
				return false;
		}
	}
}
