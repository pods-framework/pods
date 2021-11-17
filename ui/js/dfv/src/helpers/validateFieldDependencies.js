import { toBool } from './booleans';

const ALL_BOOLEAN_VALUES = [ '1', '0', 1, 0, true, false ];

/**
 * Ensure that the dependencies are set properly as an object.
 *
 * @param {Object|Array|string} dependency The dependency object or non-object.
 *
 * @return {Object} The dependency object or an empty object if not set up properly.
 */
export const formatDependency = ( dependency ) => {
	if ( 'object' === typeof dependency ) {
		return dependency;
	}

	return {};
};

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {Object} rules     Key/value field slug and option to check for.
 * @param {string} mode      The dependency mode, either 'wildcard-on', 'depends-on',
 *                           'depends-on-any', or 'excludes-on'.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
export const validateFieldDependencies = ( options, rules, mode = 'depends-on' ) => {
	if ( ! [ 'wildcard-on', 'depends-on', 'depends-on-any', 'excludes-on' ].includes( mode ) ) {
		throw 'Invalid dependency validation mode.';
	}

	rules = formatDependency( rules || {} );

	const ruleKeys = Object.keys( rules || {} );

	if ( ! ruleKeys.length ) {
		return true;
	}

	if ( 'excludes-on' === mode ) {
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

	// Either 'wildcard-on' or 'depends-on'
	return ruleKeys.every( ( key ) => {
		return validateFieldDependenciesForKey( options, mode, key, rules[ key ] );
	} );
};

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {string} mode      The dependency mode, either 'wildcard-on', 'depends-on',
 *                           'depends-on-any', or 'excludes-on'.
 * @param {string} ruleKey   The dependency key being checked.
 * @param {string} ruleValue The dependency value being checked.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateFieldDependenciesForKey = ( options, mode, ruleKey, ruleValue ) => {
	const currentValue = options[ ruleKey ];

	// Bail if the current value is not set at all.
	if ( 'undefined' === typeof currentValue ) {
		return false;
	}

	if ( 'wildcard-on' === mode ) {
		// We could either have an array of possible values,
		// or a string that is the possible value.
		const wildcardData = Array.isArray( ruleValue ) ? ruleValue : [ ruleValue ];

		// Go ahead and return true if there are no wildcard rules.
		if ( 0 === wildcardData.length ) {
			return true;
		}

		// Check for any wildcard match.
		return wildcardData.some(
			( regexRule ) => !! currentValue.match( regexRule )
		);
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
