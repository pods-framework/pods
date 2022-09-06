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
	if (
		'object' === typeof ( item1 ) || 'object' === typeof ( item2 )
	) {
		return JSON.stringify( item1 ) === JSON.stringify( item2 );
	}

	return item1.toString() === item2.toString();
};

/**
 * Helper function to validate values for conditional logic.
 *
 * @param {string} rule        Any of the possible conditional rules: 'like', 'not like',
 *                             'begins', 'not begins', 'ends', 'not ends', 'matches', 'not matches',
 *                             'in', 'not in', 'empty', 'not empty', '=', '!=', '<', '<=', '>', '>='.
 * @param {string} ruleValue   The value to compare against.
 * @param {string} valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const validateConditionalValue = ( rule, ruleValue, valueToTest ) => {
	// Bail if the current value is not set at all.
	if ( 'undefined' === typeof rule ) {
		return false;
	}

	switch ( rule ) {
		case 'like':
			return ( valueToTest.toLowerCase() ).includes( ruleValue.toLowerCase() );
		case 'not like':
			return ! ( valueToTest.toLowerCase() ).includes( ruleValue.toLowerCase() );
		case 'begins':
			return ( valueToTest.toLowerCase() ).startsWith( ruleValue.toLowerCase() );
		case 'not begins':
			return ! ( valueToTest.toLowerCase() ).startsWith( ruleValue.toLowerCase() );
		case 'ends':
			return valueToTest.toLowerCase().endsWith( ruleValue.toLowerCase() );
		case 'not ends':
			return ! valueToTest.toLowerCase().endsWith( ruleValue.toLowerCase() );
		case 'matches':
			return valueToTest.match( ruleValue );
		case 'not matches':
			return ! valueToTest.match( ruleValue );
		case 'in': {
			// We can't compare 'in' if the rule's value is not an array.
			if ( ! Array.isArray( ruleValue ) ) {
				return false;
			}

			return ruleValue.some(
				( ruleValueItem ) => looseStringEqualityCheck( ruleValueItem, valueToTest )
			);
		}
		case 'not in': {
			// We can't compare 'not in' if the rule's value is not an array.
			if ( ! Array.isArray( ruleValue ) ) {
				return false;
			}

			return ! ruleValue.some(
				( ruleValueItem ) => looseStringEqualityCheck( ruleValueItem, valueToTest )
			);
		}
		case 'empty': {
			if ( Array.isArray( valueToTest ) ) {
				return valueToTest.length === 0;
			} else if ( [ '0', 0 ].includes( valueToTest ) ) {
				// The string '0' and '0' are not considered "empty".
				return false;
			}

			return ! Boolean( valueToTest );
		}
		case 'not empty': {
			if ( Array.isArray( valueToTest ) ) {
				return valueToTest.length > 0;
			} else if ( 0 === valueToTest ) {
				// The integer 0 is considered "not empty".
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

			return Number( ruleValue ) < Number( valueToTest );
		}
		case '<=': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( ruleValue ) <= Number( valueToTest );
		}
		case '>': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( ruleValue ) > Number( valueToTest );
		}
		case '>=': {
			// Don't compare arrays.
			if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
				return false;
			}

			return Number( ruleValue ) >= Number( valueToTest );
		}
		default:
			return false;
	}
};

const recursiveCheckConditionalLogicForField = (
	fieldConfig,
	allPodValues,
	allPodFieldsMap,
) => {
	const {
		enable_conditional_logic: enableConditionalLogic,
		conditional_logic: {
			action,
			logic,
			rules,
		},
	} = fieldConfig;

	// The field is always enabled if "conditional logic" is not turned on.
	if ( ! toBool( enableConditionalLogic ) ) {
		return true;
	}

	// No need to go through rules if the array is empty.
	if ( 0 === rules.length ) {
		return ( 'show' === action ) ? true : false;
	}

	// If logic is set to 'any', we just need to find the first rule that matches.
	// If logic is set to 'all', we need to go through each rule.
	let meetsRules = false;

	const rulesCallback = ( rule ) => {
		const {
			compare,
			value: ruleValue,
			field: fieldToTest,
		} = rule;

		// Return if the rule is invalid.
		if ( ! compare || ! fieldToTest ) {
			return true;
		}

		const valueToTest = allPodValues[ fieldToTest ];

		// If the value to test is not set, then it can't pass.
		if ( 'undefined' === typeof valueToTest ) {
			return false;
		}

		return validateConditionalValue(
			compare,
			ruleValue,
			valueToTest,
		);
	};

	if ( 'all' === logic ) {
		meetsRules = rules.every( rulesCallback );
	} else {
		meetsRules = rules.some( rulesCallback );
	}

	// @todo
	// Go up the tree of dependencies. This works two different ways:
	// parents from a 'depends-on-any' match should have at least one
	// where the whole tree passes. For the other types, all parents need to match.

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
