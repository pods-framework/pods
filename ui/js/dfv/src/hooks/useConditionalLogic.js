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
 * Check if a value is considered empty.
 *
 * @param {any} value The value to check.
 *
 * @return {boolean} True if the value is empty.
 */
const isValueEmpty = ( value ) => {
	if ( Array.isArray( value ) ) {
		console.debug( 'Conditional logic: value to test is an array' );
		return value.length === 0;
	}

	if ( [ null, undefined ].includes( value ) ) {
		console.debug( 'Conditional logic: value to test is null or undefined' );
		// null and undefined are considered "empty".
		return true;
	}

	if ( [ '0', 0 ].includes( value ) ) {
		console.debug( 'Conditional logic: value to test is \'0\' or 0' );
		// The string '0' and 0 are not considered "empty".
		return false;
	}

	return ! Boolean( value );
};

/**
 * Perform a string comparison operation.
 *
 * @param {string} operation   The operation to perform: 'includes', 'startsWith', or 'endsWith'.
 * @param {any}    ruleValue   The value to compare against.
 * @param {any}    valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const stringComparison = ( operation, ruleValue, valueToTest ) => {
	const valueStr = valueToTest.toString().toLowerCase();
	const ruleStr = ruleValue.toString().toLowerCase();

	return valueStr[ operation ]( ruleStr );
};

/**
 * Perform a regex match operation.
 *
 * @param {any} ruleValue   The regex pattern to match against.
 * @param {any} valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const regexMatch = ( ruleValue, valueToTest ) => {
	if ( ! Array.isArray( valueToTest ) ) {
		return Boolean( valueToTest.toString().match( ruleValue ) );
	}

	return valueToTest.some(
		( valueItem ) => Boolean( valueItem.toString().match( ruleValue ) )
	);
};

/**
 * Check if valueToTest is in the ruleValue array.
 *
 * @param {any}     ruleValue   The array or string to check against.
 * @param {any}     valueToTest The value to be tested.
 * @param {boolean} exact       If true, uses .every (all must match); if false, uses .some (any can match).
 *
 * @return {boolean} True if the test passes.
 */
const inComparison = ( ruleValue, valueToTest, exact = false ) => {
	const arrayMethod = exact ? 'every' : 'some';

	// We can't compare 'in' if the rule's value is not an array.
	if ( ! Array.isArray( ruleValue ) ) {
		// if ruleValue is a string, then separate by comma and trim whitespace, otherwise return false.
		if ( Array.isArray( valueToTest ) && 'string' === typeof ruleValue ) {
			const checkRuleValue = convertStringToArray( ruleValue );

			// Check if values in ruleValue are contained within the array valueToTest.
			// Use .every if exact is true, .some if exact is false.
			return checkRuleValue[ arrayMethod ](
				( ruleValueItem ) => valueToTest.some(
					( valueItem ) => looseStringEqualityCheck( ruleValueItem, valueItem )
				)
			);
		}

		return false;
	}

	// Use .every if exact is true (all values must match), .some if exact is false (any value can match).
	return ruleValue[ arrayMethod ](
		( ruleValueItem ) => looseStringEqualityCheck( ruleValueItem, valueToTest )
	);
};

/**
 * Check if ruleValue is in the valueToTest array.
 *
 * @param {any}     ruleValue   The value to check for.
 * @param {any}     valueToTest The array to check within.
 * @param {boolean} exact       If true, uses .every (all must match); if false, uses .some (any can match).
 *
 * @return {boolean} True if the test passes.
 */
const inValuesComparison = ( ruleValue, valueToTest, exact = false ) => {
	const arrayMethod = exact ? 'every' : 'some';

	// We can't compare 'in values' if valueToTest is not an array.
	if ( ! Array.isArray( valueToTest ) ) {
		// if valueToTest is a string, then separate by comma and trim whitespace, otherwise return false.
		if ( Array.isArray( ruleValue ) && 'string' === typeof valueToTest ) {
			const checkValueToTest = convertStringToArray( valueToTest );

			// Check if any of the values in valueToTest are contained within the array ruleValue.
			// Use .every if exact is true, .some if exact is false.
			return checkValueToTest[ arrayMethod ](
				( valueItem ) => ruleValue.some(
					( ruleValueItem ) => looseStringEqualityCheck( valueItem, ruleValueItem )
				)
			);
		}

		return false;
	}

	// Use .every if exact is true (all values must match), .some if exact is false (any value can match).
	return valueToTest[ arrayMethod ](
		( valueItem ) => looseStringEqualityCheck( valueItem, ruleValue )
	);
};

/**
 * Perform an equality comparison with array handling.
 *
 * @param {any} ruleValue   The value to compare against.
 * @param {any} valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const equalityComparison = ( ruleValue, valueToTest ) => {
	let checkRuleValue = ruleValue;

	if (
		Array.isArray( valueToTest ) &&
		! Array.isArray( checkRuleValue ) &&
		(
			'string' === typeof checkRuleValue ||
			'number' === typeof checkRuleValue
		)
	) {
		checkRuleValue = convertStringToArray( checkRuleValue );
	}

	// Sort checkRuleValue and valueToTest to prevent false negatives due to order, but only if both are arrays.
	if ( Array.isArray( checkRuleValue ) ) {
		checkRuleValue.sort();
	}

	if ( Array.isArray( valueToTest ) ) {
		valueToTest.sort();
	}

	return looseStringEqualityCheck( checkRuleValue, valueToTest );
};

/**
 * Perform a numeric comparison operation.
 *
 * @param {string} operator    The comparison operator: '<', '<=', '>', or '>='.
 * @param {any}    ruleValue   The value to compare against.
 * @param {any}    valueToTest The value to be tested.
 *
 * @return {boolean} True if the test passes.
 */
const numericComparison = ( operator, ruleValue, valueToTest ) => {
	// Don't compare arrays.
	if ( Array.isArray( ruleValue ) || Array.isArray( valueToTest ) ) {
		return false;
	}

	const numValue = Number( valueToTest );
	const numRule = Number( ruleValue );

	switch ( operator ) {
		case '<':
			return numValue < numRule;
		case '<=':
			return numValue <= numRule;
		case '>':
			return numValue > numRule;
		case '>=':
			return numValue >= numRule;
		default:
			return false;
	}
};

/**
 * Helper function to validate values for conditional logic.
 *
 * @param {string}       rule        Any of the possible conditional rules: 'like', 'not like',
 *                                   'begins', 'not begins', 'ends', 'not ends', 'matches', 'not matches',
 *                                   'in', 'not in', 'empty', 'not empty', '=', '!=', '<', '<=', '>', '>='.
 * @param {string|Array} ruleValue   The value to compare against.
 * @param {string}       valueToTest The value to be tested.
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
			return stringComparison( 'includes', ruleValue, valueToTest );
		case 'NOT LIKE':
			return ! stringComparison( 'includes', ruleValue, valueToTest );
		case 'BEGINS':
			return stringComparison( 'startsWith', ruleValue, valueToTest );
		case 'NOT BEGINS':
			return ! stringComparison( 'startsWith', ruleValue, valueToTest );
		case 'ENDS':
			return stringComparison( 'endsWith', ruleValue, valueToTest );
		case 'NOT ENDS':
			return ! stringComparison( 'endsWith', ruleValue, valueToTest );
		case 'MATCHES':
			return regexMatch( ruleValue, valueToTest );
		case 'NOT MATCHES':
			return ! regexMatch( ruleValue, valueToTest );
		case 'IN':
			return inComparison( ruleValue, valueToTest );
		case 'NOT IN':
			return ! inComparison( ruleValue, valueToTest );
		case 'IN VALUES':
			return inValuesComparison( ruleValue, valueToTest );
		case 'NOT IN VALUES':
			return ! inValuesComparison( ruleValue, valueToTest );
		case 'ALL':
			return inComparison( ruleValue, valueToTest, true );
		case 'NOT ALL':
			return ! inComparison( ruleValue, valueToTest, true );
		case 'ALL VALUES':
			return inValuesComparison( ruleValue, valueToTest, true );
		case 'NOT ALL VALUES':
			return ! inValuesComparison( ruleValue, valueToTest, true );
		case 'EMPTY':
			return isValueEmpty( valueToTest );
		case 'NOT EMPTY':
			return ! isValueEmpty( valueToTest );
		case '=':
			return equalityComparison( ruleValue, valueToTest );
		case '!=':
			return ! equalityComparison( ruleValue, valueToTest );
		case '<':
		case '<=':
		case '>':
		case '>=':
			return numericComparison( rule, ruleValue, valueToTest );
		default: {
			console.debug( 'Conditional logic: rule is unsupported' );
			console.debug( { rule, ruleValue, valueToTest } );

			return false;
		}
	}
};

const convertStringToArray = ( value ) => {
	if ( Array.isArray( value ) ) {
		return value;
	}

	if ( 'number' === typeof value ) {
		return [ value ];
	}

	if ( 'string' !== typeof value ) {
		return [];
	}

	return value.split( ',' ).map( ( item ) => item.trim() );
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

// Export helper functions for unit testing
export {
	looseStringEqualityCheck,
	convertStringToArray,
	isValueEmpty,
	stringComparison,
	regexMatch,
	inComparison,
	inValuesComparison,
	equalityComparison,
	numericComparison,
};

export default useConditionalLogic;
