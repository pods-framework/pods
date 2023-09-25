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
	 * Set up the object.
	 *
	 * @since 3.0
	 *
	 * @param string $action The action to take (show/hide).
	 * @param string $logic  The logic to use (any/all).
	 * @param array  $rules  The conditional rules.
	 */
	public function __construct( $action = 'show', $logic = 'any', $rules = [] ) {
		if ( $action ) {
			$this->set_action( $action );
		}

		if ( $logic ) {
			$this->set_logic( $logic );
		}

		if ( $rules ) {
			$this->set_rules( $rules );
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
	 * Maybe migrate the field anme from old prefix naming convention.
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
				$old_syntax_value = maybe_unserialize( $old_syntax_value );

				if ( ! is_array( $old_syntax_value ) ) {
					$old_syntax_value = [];
				}
			}

			$old_syntax[ $old_syntax_key ] = $old_syntax_value;
		}

		$action = 'show';
		$logic  = 'any';
		$rules  = [];

		if ( $old_syntax['depends-on'] ) {
			$logic = 'all';

			foreach ( $old_syntax['depends-on'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'in' : '=' ),
					'value'   => $value,
				];
			}
		}

		if ( $old_syntax['depends-on-any'] ) {
			foreach ( $old_syntax['depends-on-any'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'in' : '=' ),
					'value'   => $value,
				];
			}
		}

		if ( $old_syntax['depends-on-multi'] ) {
			$logic = 'all';

			foreach ( $old_syntax['depends-on-multi'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => 'in-values',
					'value'   => $value,
				];
			}
		}

		if ( $old_syntax['excludes-on'] ) {
			foreach ( $old_syntax['excludes-on'] as $field_name => $value ) {
				$field_name = self::maybe_migrate_field_name( $field_name );

				$rules[] = [
					'field'   => $field_name,
					'compare' => ( is_array( $value ) ? 'not-in' : '!=' ),
					'value'   => $value,
				];
			}
		}

		if ( $old_syntax['wildcard-on'] ) {
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
		}

		if ( empty( $rules ) ) {
			return null;
		}

		return self::setup_from_object(
			$object,
			$action,
			$logic,
			$rules
		);
	}

	/**
	 * Set up the Conditional Logic instance from an object.
	 *
	 * @since 3.0
	 *
	 * @param Whatsit|array $object The object data.
	 * @param string        $action The action to take (show/hide).
	 * @param string        $logic  The logic to use (any/all).
	 * @param array         $rules  The conditional rules.
	 */
	private static function setup_from_object( $object, $action, $logic, $rules ): Conditional_Logic {
		$conditional_logic = new self( $action, $logic, $rules );

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
			 */
			$conditional_logic = apply_filters( 'pods_data_conditional_logic_for_object', $conditional_logic, $object, $action, $logic, $rules );
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
	 * Get the conditional logic data as an array.
	 *
	 * @since 3.0
	 *
	 * @return array The conditional logic data as an array.
	 */
	public function to_array(): array {
		return [
			'action' => $this->action ?: 'show',
			'logic'  => $this->logic ?: 'any',
			'rules'  => $this->rules,
		];
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
	 * Determine whether the rule validates for the field values provided.
	 *
	 * @since 3.0
	 *
	 * @param array $rule   The conditional rule.
	 * @param array $values The field values.
	 *
	 * @return bool Whether the rule validates for the field values provided.
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

		if ( ! in_array( $compare, [
			'EMPTY',
			'NOT EMPTY',
		], true ) ) {
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

		if ( 'LIKE' === $compare ) {
			if ( '' === $value ) {
				return true;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return false;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return false;
			}

			if ( function_exists( 'str_contains' ) ) {
				return str_contains( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return false !== stripos( (string) $check_value, (string) $value );
		}

		if ( 'NOT LIKE' === $compare ) {
			if ( '' === $value ) {
				return false;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return true;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return true;
			}

			if ( function_exists( 'str_contains' ) ) {
				return ! str_contains( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return false === stripos( (string) $check_value, (string) $value );
		}

		if ( 'BEGINS' === $compare ) {
			if ( '' === $value ) {
				return true;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return false;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return false;
			}

			if ( function_exists( 'str_starts_with' ) ) {
				return str_starts_with( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return 0 === stripos( (string) $check_value, (string) $value );
		}

		if ( 'NOT BEGINS' === $compare ) {
			if ( '' === $value ) {
				return false;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return true;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return true;
			}

			if ( function_exists( 'str_starts_with' ) ) {
				return ! str_starts_with( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return 0 !== stripos( (string) $check_value, (string) $value );
		}

		if ( 'ENDS' === $compare ) {
			if ( '' === $value ) {
				return true;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return false;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return false;
			}

			if ( function_exists( 'str_ends_with' ) ) {
				return str_ends_with( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return 0 === substr_compare( (string) $check_value, (string) $value, - strlen( (string) $value ) );
		}

		if ( 'NOT ENDS' === $compare ) {
			if ( '' === $value ) {
				return false;
			}

			if ( null !== $check_value && ! is_scalar( $check_value ) ) {
				return true;
			}

			if ( null !== $value && ! is_scalar( $value ) ) {
				return true;
			}

			if ( function_exists( 'str_ends_with' ) ) {
				return ! str_ends_with( strtolower( (string) $check_value ), strtolower( (string) $value ) );
			}

			return 0 !== substr_compare( (string) $check_value, (string) $value, - strlen( (string) $value ) );
		}

		if ( 'MATCHES' === $compare ) {
			if ( is_array( $check_value ) ) {
				foreach ( $check_value as $check_value_item ) {
					if ( 1 === preg_match( '/' . str_replace( '/', '\/', (string) $value ) . '/', (string) $check_value_item ) ) {
						return true;
					}
				}

				return false;
			}

			if ( ! is_scalar( $check_value ) ) {
				return false;
			}

			return 1 === preg_match( '/' . str_replace( '/', '\/', (string) $value ) . '/', (string) $check_value );
		}

		if ( 'NOT MATCHES' === $compare ) {
			if ( is_array( $check_value ) ) {
				foreach ( $check_value as $check_value_item ) {
					if ( 0 === preg_match( '/' . str_replace( '/', '\/', (string) $value ) . '/', (string) $check_value_item ) ) {
						return true;
					}
				}

				return false;
			}

			if ( ! is_scalar( $check_value ) ) {
				return true;
			}

			return 0 === preg_match( '/' . str_replace( '/', '\/', (string) $value ) . '/', (string) $check_value );
		}

		if ( 'IN' === $compare ) {
			if ( ! is_scalar( $check_value ) ) {
				return false;
			}

			return in_array( $check_value, (array) $value, false );
		}

		if ( 'NOT IN' === $compare ) {
			if ( ! is_scalar( $check_value ) ) {
				return true;
			}

			return ! in_array( $check_value, (array) $value, false );
		}

		if ( 'IN VALUES' === $compare ) {
			if ( ! is_scalar( $value ) ) {
				return false;
			}

			return in_array( $value, (array) $check_value, false );
		}

		if ( 'NOT IN VALUES' === $compare ) {
			if ( ! is_scalar( $value ) ) {
				return true;
			}

			return ! in_array( $value, (array) $check_value, false );
		}

		if ( 'EMPTY' === $compare ) {
			return in_array( $check_value, [ '', null, [], false ], true );
		}

		if ( 'NOT EMPTY' === $compare ) {
			return ! in_array( $check_value, [ '', null, [], false ], true );
		}

		// Numeric comparisons enforce floats on numeric values for strict checks.
		if ( is_numeric( $value ) ) {
			$value = (float) $value;
		}

		if ( is_numeric( $check_value ) ) {
			$check_value = (float) $check_value;
		}

		if ( '=' === $compare ) {
			if ( ! is_scalar( $check_value ) ) {
				return false;
			}

			return $check_value === $value;
		}

		if ( '!=' === $compare ) {
			if ( ! is_scalar( $check_value ) ) {
				return true;
			}

			return $check_value !== $value;
		}

		if ( ! is_scalar( $check_value ) ) {
			return false;
		}

		if ( '<' === $compare ) {
			return (float) $value < (float) $check_value;
		}

		if ( '<=' === $compare ) {
			return (float) $value <= (float) $check_value;
		}

		if ( '>' === $compare ) {
			return (float) $value > (float) $check_value;
		}

		if ( '>=' === $compare ) {
			return (float) $value >= (float) $check_value;
		}

		return false;
	}
}
