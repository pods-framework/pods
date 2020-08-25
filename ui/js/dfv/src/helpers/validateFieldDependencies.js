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

			return options[ key ] === dependsOn[ key ];
		}
	);
};

export default validateDependencies;
