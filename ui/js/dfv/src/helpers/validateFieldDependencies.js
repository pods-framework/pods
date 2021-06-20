import { toBool } from './booleans';

const ALL_BOOLEAN_VALUES = [ '1', '0', 1, 0, true, false ];

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {Object} rules Key/value field slug and option to check for.
 * @param {string} mode      The dependency mode, either 'wildcard', 'depends-on',
 *                           'depends-on-any', or 'excludes'.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateFieldDependencies = ( options, rules, mode = 'depends-on' ) => {
	const ruleKeys = Object.keys( rules || {} );

	if ( ! ruleKeys.length ) {
		return true;
	}

	if ( 'excludes' === mode ) {
		// Negate the check because the exclusion dependency has failed if it returns true.
		return ! ruleKeys.some( ( key ) => {
			return validateFieldDependenciesForKey( options, mode, key, rules[ key ] );
		} );
	}

	if ( 'depends-on-any' === mode ) {
		return ruleKeys.some( ( key ) => {
			return validateFieldDependenciesForKey( options, mode, key, rules[ key ] );
		} );
	}

	return ruleKeys.every( ( key ) => {
		return validateFieldDependenciesForKey( options, mode, key, rules[ key ] );
	} );
};

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {string} mode      The dependency mode, either 'wildcard', 'depends-on',
 *                           'depends-on-any', or 'excludes'.
 * @param {string} ruleKey   The dependency key being checked.
 * @param {string} ruleValue The dependency value being checked.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateFieldDependenciesForKey = ( options, mode, ruleKey, ruleValue ) => {
	const currentValue = options[ ruleKey ];

	// console.log( 'validateFieldDependenciesForKey', {
	// 	options, mode, ruleKey, ruleValue,
	// } );

	// Bail if the current value is not set at all.
	if ( 'undefined' === typeof currentValue ) {
		return false;
	}

	if ( 'wildcard' === mode ) {
		let wildcardData = ruleValue;
		let wildcardMatchFound = false;

		// We could either have an array of possible values,
		// or a string that is the possible value.
		if ( ! Array.isArray( wildcardData ) ) {
			wildcardData = [
				ruleValue,
			];
		}

		wildcardMatchFound = false;

		// Check for any wildcard match.
		wildcardData.every(
			( regexRule ) => {
				// Skip these rules, but keep going through the array.
				if ( null === currentValue.match( regexRule ) ) {
					return true;
				}

				wildcardMatchFound = true;

				// Stop iterating through further.
				return false;
			}
		);

		return wildcardMatchFound;
	}

	// We could either have an array of possible values,
	// or a string that is the possible value.
	if ( Array.isArray( ruleValue ) ) {
		return ruleValue.includes( currentValue );
	}

	// Start with a strict comparison.
	if ( ruleValue === currentValue ) {
		return true;
	}

	// Work around  typing issues with how boolean fields are saved.
	//
	// This could potentially cause issues where the string '1' is considered
	// equal to boolean true, but may be necessary for now due to type
	// inconsistency with how field values are saved.
	if (
		ALL_BOOLEAN_VALUES.includes( ruleValue ) &&
		ALL_BOOLEAN_VALUES.includes( currentValue ) &&
		toBool( ruleValue ) === toBool( currentValue )
	) {
		return true;
	}

	return false;
};

export default validateFieldDependencies;
