import { toBool } from 'dfv/src/helpers/booleans';

/**
 * Helper function to compare values of differing items, which allows strings
 * to match numbers.
 *
 * Comparing an array of 1 item could create false positives, because
 * `[ '123' ].toString() === '123'`, so compare objects (usually arrays)
 * without using toString().
 *
 * @param {any} item1 First item to compare.
 * @param {any} item2 Second item to compare.
 *
 * @return {boolean} True if matches.
 */
const looseStringEqualityCheck = ( item1, item2 ) => {
	if ( 'object' === typeof item1 || 'object' === typeof item2 ) {
		return JSON.stringify( item1 ) === JSON.stringify( item2 );
	}

	if ( 'boolean' === typeof item1 ) {
		item1 = item1 ? 1 : 0;
	}

	if ( 'boolean' === typeof item2 ) {
		item2 = item2 ? 1 : 0;
	}

	// Attempt to normalize the number.
	if ( ! isNaN( item1 ) && ! isNaN( item2 ) ) {
		item1 = parseFloat( item1 );
		item2 = parseFloat( item2 );
	}

	return item1.toString().toLowerCase() === item2.toString().toLowerCase();
};

/**
 * Helper function to validate values for conditional logic.
 *
 * @param {string} rule        Any of the possible conditional rules: 'like', 'not like',
 *                             'begins', 'not begins', 'ends', 'not ends', 'matches', 'not matches',
 *                             'in', 'not in', 'empty', 'not empty', '=', '!=', '<', '<=', '>', '>='.
 * @param {string|array} ruleValue   The value to compare against.
 * @param {string} valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const validateConditionalValue = ( rule, ruleValue, valueToTest ) => {
	// Bail if the current value is not set at all.
	if ( 'undefined' === typeof rule ) {
		console.debug( 'Conditional logic: rule is undefined' );

		return false;
	}

	rule = rule.toUpperCase().replace( '-', ' ' );

	switch ( rule ) {
		case 'LIKE':
			return ( valueToTest.toString().toLowerCase() ).includes( ruleValue.toString().toLowerCase() );
		case 'NOT LIKE':
			return ! ( valueToTest.toString().toLowerCase() ).includes( ruleValue.toString().toLowerCase() );
		case 'BEGINS':
			return ( valueToTest.toString().toLowerCase() ).startsWith( ruleValue.toString().toLowerCase() );
		case 'NOT BEGINS':
			return ! ( valueToTest.toString().toLowerCase() ).startsWith( ruleValue.toString().toLowerCase() );
		case 'ENDS':
			return valueToTest.toString().toLowerCase().endsWith( ruleValue.toString().toLowerCase() );
		case 'NOT ENDS':
			return ! valueToTest.toString().toLowerCase().endsWith( ruleValue.toString().toLowerCase() );
		case 'MATCHES':
			if ( ! Array.isArray( valueToTest ) ) {
				return Boolean( valueToTest.toString().match( ruleValue ) );
			}

			return valueToTest.some(
				( valueItem ) => Boolean( valueItem.toString().match( ruleValue ) )
			);
		case 'NOT MATCHES':
			if ( ! Array.isArray( valueToTest ) ) {
				return ! Boolean( valueToTest.toString().match( ruleValue ) );
			}

			return ! valueToTest.some(
				( valueItem ) => Boolean( valueItem.toString().match( ruleValue ) )
			);
		case 'IN': {
			// We can't compare 'in' if the rule's value is not an array.
			if ( ! Array.isArray( ruleValue ) ) {
				return false;
			}

			return ruleValue.some(
				( ruleValueItem ) => looseStringEqualityCheck( ruleValueItem, valueToTest )
			);
		}
		case 'NOT IN': {
			// We can't compare 'not in' if the rule's value is not an array.
			if ( ! Array.isArray( ruleValue ) ) {
				return false;
			}

			return ! ruleValue.some(
				( ruleValueItem ) => looseStringEqualityCheck( ruleValueItem, valueToTest )
			);
		}
		case 'IN VALUES': {
			// We can't compare 'in' if the rule's value is not an array.
			if ( ! Array.isArray( valueToTest ) ) {
				return false;
			}

			return valueToTest.some(
				( valueItem ) => looseStringEqualityCheck( valueItem, ruleValue )
			);
		}
		case 'NOT IN VALUES': {
			// We can't compare 'not in' if the rule's value is not an array.
			if ( ! Array.isArray( valueToTest ) ) {
				return false;
			}

			return ! valueToTest.some(
				( valueItem ) => looseStringEqualityCheck( valueItem, ruleValue )
			);
		}
		case 'EMPTY': {
			if ( Array.isArray( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is an array' );
				return valueToTest.length === 0;
			} else if ( [ null, undefined ].includes( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is null or undefined' );
				// null and undefined are considered "empty".
				return true;
			} else if ( [ '0', 0 ].includes( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is \'0\' or 0' );
				// The string '0' and 0 are not considered "empty".
				return false;
			}

			return ! Boolean( valueToTest );
		}
		case 'NOT EMPTY': {
			if ( Array.isArray( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is an array' );
				return valueToTest.length > 0;
			} else if ( [ null, undefined ].includes( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is null or undefined' );
				// null and undefined are considered "empty".
				return false;
			} else if ( [ '0', 0 ].includes( valueToTest ) ) {
				console.debug( 'Conditional logic: value to test is \'0\' or 0' );
				// The string '0' and 0 are not considered "empty".
				return true;
			}

			return Boolean( valueToTest );
		}
		case '=':
			return looseStringEqualityCheck( ruleValue, valueToTest );
		case '!=':
			return ! looseStringEqualityCheck( ruleValue, valueToTest );
		case '<': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( valueToTest ) < Number( ruleValue );
		}
		case '<=': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( valueToTest ) <= Number( ruleValue );
		}
		case '>': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( valueToTest ) > Number( ruleValue );
		}
		case '>=': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( valueToTest ) >= Number( ruleValue );
		}
		default: {
			console.debug( 'Conditional logic: rule is unsupported' );
			console.debug( { rule, ruleValue, valueToTest } );

			return false;
		}
	}
};

const recursiveCheckConditionalLogicForField = (
	fieldConfig,
	allPodValues,
	allPodFieldsMap,
) => {
	const {
		enable_conditional_logic: enableConditionalLogic,
		conditional_logic: conditionalLogic,
		name: fieldName,
	} = fieldConfig;

	// The field is always enabled if "conditional logic" is not turned on,
	// and/or if the 'conditional_logic' value is empty.
	if ( ! toBool( enableConditionalLogic ) ) {
		return true;
	}

	if ( 'object' !== typeof conditionalLogic ) {
		return true;
	}

	// Maybe check multiple conditional logic sets.
	if ( 'undefined' !== conditionalLogic.logic_sets && Array.isArray( conditionalLogic.logic_sets ) ) {
		return conditionalLogic.logic_sets.every( ( logicGroup ) => {
			return recursiveCheckConditionalLogicForField(
				{
					...fieldConfig,
					conditional_logic: logicGroup,
				},
				allPodValues,
				allPodFieldsMap,
			);
		} );
	}

	console.debug( 'Conditional logic: enabled' );
	console.debug( { fieldName, conditionalLogic, allPodValues } );

	const {
		action,
		logic,
		rules,
	} = conditionalLogic;

	// No need to go through rules if the array is empty.
	if ( 0 === rules.length ) {
		return ( 'show' === action ) ? true : false;
	}

	// If logic is set to 'any', we just need to find the first rule that matches.
	// If logic is set to 'all', we need to go through each rule.
	const rulesCallback = ( rule ) => {
		const {
			compare,
			value: ruleValue,
			field: fieldNameToTest,
		} = rule;

		// Return if the rule is invalid.
		if ( ! compare || ! fieldNameToTest ) {
			return true;
		}

		const variations = [
			fieldNameToTest,
			'pods_meta_' + fieldNameToTest,
			'pods_field_' + fieldNameToTest,
		];

		let valueToTest = undefined;

		variations.every( variation => {
			// Stop the loop if we found the value we were looking for.
			if ( 'undefined' !== typeof allPodValues[ variation ] ) {
				valueToTest = allPodValues[ variation ];

				return false;
			}

			// Continue to the next variation.
			return true;
		} );

		// If the value to test is not set, then it can't pass.
		if ( 'undefined' === typeof valueToTest && ! [ 'EMPTY', 'NOT EMPTY' ].includes( compare.toUpperCase() ) ) {
			console.debug( 'Conditional logic: no value to test' );
			console.debug( { fieldName, fieldNameToTest, valueToTest, allPodValues } );
			return false;
		}

		const doesValueMatch = validateConditionalValue(
			compare,
			ruleValue,
			valueToTest,
		);

		console.debug( 'Conditional logic: validateConditionalValue doesValueMatch' );
		console.debug( { fieldName, fieldNameToTest, doesValueMatch, compare, ruleValue, valueToTest } );

		// No need to go up the tree of dependencies if it already failed.
		if ( false === doesValueMatch ) {
			return false;
		}

		// Check up the tree of dependencies.
		const parentFieldConfig = allPodFieldsMap.get( fieldNameToTest );

		if ( ! parentFieldConfig ) {
			return true;
		}

		const doParentDepenenciesMatch = recursiveCheckConditionalLogicForField(
			parentFieldConfig,
			allPodValues,
			allPodFieldsMap,
		);

		return doParentDepenenciesMatch;
	};

	let meetsRules = false;

	if ( 'all' === logic ) {
		meetsRules = rules.every( rulesCallback );
	} else {
		meetsRules = rules.some( rulesCallback );
	}

	// Inverse the result if the action is 'hide' instead of 'show'.
	if ( 'hide' === action ) {
		meetsRules = ! meetsRules;
	}

	return meetsRules;
};

const useConditionalLogic = (
	fieldConfig = {},
	allPodValues = {},
	allPodFieldsMap = new Map(),
) => {
	return recursiveCheckConditionalLogicForField( fieldConfig, allPodValues, allPodFieldsMap );
};

export default useConditionalLogic;
