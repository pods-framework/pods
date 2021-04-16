import { memoize } from 'lodash';

// The dependencies could be stacked, so if Field B is not shown because
// Field A is not set correctly, then Field C which depends on a specific
// Field B value should fail, even if Field B had the correct matching value.
// To find these, look up each key in the dependsOn (and similar) maps, find
// the relevant field, and add its dependsOn (or similar) values to the full map.
const unstackDependencies = (
	dependencyMap = {},
	allFieldsMap = new Map(),
	dependencyKey = 'depends-on'
) => {
	if ( ! dependencyMap || 0 === Object.keys( dependencyMap ).length ) {
		return {};
	}

	return Object.entries( dependencyMap ).reduce(
		( accumulator, dependencyEntry ) => {
			const fieldName = dependencyEntry[ 0 ];

			// Look up the field config by that key, and add its dependencies
			// recursively, unless they've already been added.
			const dependencyField = allFieldsMap.get( fieldName );

			if ( ! dependencyField?.[ dependencyKey ] ) {
				return accumulator;
			}

			// Call recursively to continue looking for dependencies.
			const nextLevelDependencies = unstackDependencies(
				dependencyField[ dependencyKey ],
				allFieldsMap,
				dependencyKey
			);

			return {
				...accumulator,
				...nextLevelDependencies,
			};
		},
		{
			...dependencyMap,
		}
	);
};

export default memoize( unstackDependencies );
