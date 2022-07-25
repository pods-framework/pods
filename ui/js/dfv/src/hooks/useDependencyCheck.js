import { validateFieldDependencies, formatDependency } from 'dfv/src/helpers/validateFieldDependencies';

const recursiveCheckDepsForField = ( fieldConfig, allPodValues, allPodFieldsMap ) => {
	let {
		'depends-on': dependsOn = {},
		'depends-on-any': dependsOnAny = {},
		'excludes-on': excludesOn = {},
		'wildcard-on': wildcardOn = {},
	} = fieldConfig;

	dependsOn = formatDependency( dependsOn );
	dependsOnAny = formatDependency( dependsOnAny );
	excludesOn = formatDependency( excludesOn );
	wildcardOn = formatDependency( wildcardOn );

	// Calculate dependencies, trying to skip as many of these checks as
	// we can because they're expensive.
	if ( Object.keys( dependsOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, dependsOn, 'depends-on' ) ) {
			return false;
		}
	}

	if ( Object.keys( dependsOnAny ).length ) {
		if ( ! validateFieldDependencies( allPodValues, dependsOnAny, 'depends-on-any' ) ) {
			return false;
		}
	}

	if ( Object.keys( excludesOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, excludesOn, 'excludes-on' ) ) {
			return false;
		}
	}

	if ( Object.keys( wildcardOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, wildcardOn, 'wildcard-on' ) ) {
			return false;
		}
	}

	// Go up the tree of dependencies. This works two different ways:
	// parents from a 'depends-on-any' match should have at least one
	// where the whole tree passes. For the other types, all parents need to match.
	const parentFieldsForAnyMatch = Object.keys( dependsOnAny );

	const parentFieldsForEveryMatch = Object.keys( {
		...dependsOn,
		...excludesOn,
		...wildcardOn,
	} );

	if ( ! parentFieldsForAnyMatch.length && ! parentFieldsForEveryMatch.length ) {
		return true;
	}

	// We should fail on the first parent field that doesn't meet its dependencies.
	const checkParentDependencies = ( fieldKey ) => {
		const parentFieldConfig = allPodFieldsMap.get( fieldKey );

		// If it's missing, either something is wrong, or it's part of a Boolean Group field
		// (so there wouldn't be a matching field config for the slug).
		if ( ! parentFieldConfig ) {
			return true;
		}

		// If one doesn't pass, return false and we're done checking.
		const parentFieldPasses = recursiveCheckDepsForField( parentFieldConfig, allPodValues, allPodFieldsMap );

		return parentFieldPasses;
	};

	const atLeastOneDependsOnAnyMatch = !! parentFieldsForAnyMatch.length
		? parentFieldsForAnyMatch.some( checkParentDependencies )
		: true;

	if ( ! atLeastOneDependsOnAnyMatch ) {
		return false;
	}

	const doAllOtherParentFieldsMatch = !! parentFieldsForEveryMatch.length
		? parentFieldsForEveryMatch.every( checkParentDependencies )
		: true;

	if ( ! doAllOtherParentFieldsMatch ) {
		return false;
	}

	// If there were no depends-on, depends-on-any, excludes-on, or
	// wildcard-on matches, and none from parent fields, then the check passes.
	return true;
};

const useDependencyCheck = (
	fieldConfig = {},
	allPodValues = {},
	allPodFieldsMap = new Map(),
) => {
	return recursiveCheckDepsForField( fieldConfig, allPodValues, allPodFieldsMap );
};

export default useDependencyCheck;
