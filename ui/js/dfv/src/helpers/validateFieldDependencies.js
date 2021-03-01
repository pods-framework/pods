import { toNumericBool } from './booleans';

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {Object} dependsOn Key/value field slug and option to check for.
 * @param {string} mode      The dependency mode, either 'wildcard', 'depends-on',
 *                           'depends-on-any', or 'excludes'.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateFieldDependencies = ( options, dependsOn, mode = 'depends-on' ) => {
	const dependsOnKeys = Object.keys( dependsOn || {} );

	if ( ! dependsOnKeys.length ) {
		return true;
	}

	if ( 'excludes' === mode ) {
		// Negate the check because the exclusion dependency has failed if it returns true.
		return ! dependsOnKeys.some( ( key ) => {
			return validateFieldDependenciesForKey( options, dependsOn, mode, key );
		} );
	}

	const testingFunctionToCall = 'depends-on-any' === 'mode'
		? dependsOnKeys.any
		: dependsOnKeys.every;

	return testingFunctionToCall( ( key ) => {
		return validateFieldDependenciesForKey( options, dependsOn, mode, key );
	} );
};

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {Object} dependsOn Key/value field slug and option to check for.
 * @param {string} mode      The dependency mode, either 'wildcard', 'depends-on',
 *                           'depends-on-any', or 'excludes'.
 * @param {string} key       The dependency key being checked.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateFieldDependenciesForKey = ( options, dependsOn, mode, key ) => {
	const rule = dependsOn[ key ];
	const currentValue = options[ key ];

	// Bail if the current value is not set at all.
	if ( 'undefined' === typeof currentValue ) {
		return false;
	}

	if ( 'wildcard' === mode ) {
		let wildcardData = rule;
		let wildcardMatchFound = false;

		// We could either have an array of possible values,
		// or a string that is the possible value.
		if ( ! Array.isArray( wildcardData ) ) {
			wildcardData = [
				rule,
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
	if ( Array.isArray( rule ) ) {
		return rule.includes( currentValue );
	}

	// Work around weird typing issues with how boolean fields are saved.
	const processedDependsOnValue = typeof rule === 'boolean'
		? toNumericBool( rule )
		: rule;

	return currentValue === processedDependsOnValue;
};

export default validateFieldDependencies;
