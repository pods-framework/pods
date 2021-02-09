import { toNumericBool } from './booleans';

/**
 * Helper function to validate that a field or tab's field dependencies
 * have been met.
 *
 * @param {Object} options   Key/value object with the selected options to compare to.
 * @param {Object} dependsOn Key/value field slug and option to check for.
 *
 * @return {boolean} True if dependencies are met to show the item.
 */
const validateDependencies = ( options, dependsOn ) => {
	if ( ! dependsOn ) {
		return true;
	}

	console.log( 'validateDependencies', options, dependsOn );

	const dependsOnKeys = Object.keys( dependsOn );

	if ( ! dependsOnKeys.length ) {
		return true;
	}

	return dependsOnKeys.every(
		( key ) => {
			// We could either have an array of possible values,
			// or a string that is the possible value.
			if ( Array.isArray( dependsOn[ key ] ) ) {
				return dependsOn[ key ].includes( options[ key ] );
			}

			// Work around weird typing issues with how boolean fields are saved.
			const processedDependsOnValue = typeof dependsOn[ key ] === 'boolean'
				? toNumericBool( dependsOn[ key ] )
				: dependsOn[ key ];

			console.log( 'option and processedDependsOnValue', options[ key ], processedDependsOnValue );

			return options[ key ] === processedDependsOnValue;
		}
	);
};

export default validateDependencies;
