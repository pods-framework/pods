import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';

const recursiveCheckDepsForField = ( fieldConfig, allPodValues, allPodFieldsMap, level = 1 ) => {
	const {
		'depends-on': dependsOn = {},
		'depends-on-any': dependsOnAny = {},
		'excludes-on': excludesOn = {},
		'wildcard-on': wildcardOn = {},
	} = fieldConfig;

	let indent = '';
	if ( 2 === level ) {
		indent = '  ';
	} else if ( 3 === level ) {
		indent = '    ';
	} else if ( 4 === level ) {
		indent = '      ';
	}

	console.log( `${ indent }validating  ${ fieldConfig.name }` );

	// Calculate dependencies, trying to skip as many of these checks as
	// we can because they're expensive.
	if ( Object.keys( dependsOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, dependsOn, 'depends-on' ) ) {
			console.log( `${ indent }- failed depends-on`, dependsOn, allPodValues );
			return false;
		}
	}

	if ( Object.keys( dependsOnAny ).length ) {
		if ( ! validateFieldDependencies( allPodValues, dependsOnAny, 'depends-on-any' ) ) {
			console.log( `${ indent }- failed depends-on-any`, dependsOnAny, allPodValues );
			return false;
		}
	}

	if ( Object.keys( excludesOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, excludesOn, 'excludes' ) ) {
			console.log( `${ indent }- failed excludes-on`, excludesOn, allPodValues );
			return false;
		}
	}

	if ( Object.keys( wildcardOn ).length ) {
		if ( ! validateFieldDependencies( allPodValues, wildcardOn, 'wildcard' ) ) {
			console.log( `${ indent }- failed wildcard`, wildcardOn, allPodValues );
			return false;
		}
	}

	console.log( `${ indent }-passed on its own 4 dependency types, going to check parents` );

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
		console.log( `${ indent }- no parents to check, passing!` );
		return true;
	}

	console.log( `${ indent }- all parents to check`, parentFieldsForAnyMatch, parentFieldsForEveryMatch );

	// We should fail on the first parent field that doesn't meet its dependencies.
	const checkParentDependencies = ( fieldKey ) => {
		const parentFieldConfig = allPodFieldsMap.get( fieldKey );

		// If it's missing, something is wrong.
		if ( ! parentFieldConfig ) {
			console.log( `${ indent }- missing parent field config from map` );
			return false;
		}

		// If one doesn't pass, return false and we're done checking.
		const parentFieldPasses = recursiveCheckDepsForField( parentFieldConfig, allPodValues, allPodFieldsMap, level + 1 );

		console.log( `${ indent }- did parent field ${ parentFieldConfig.name } meet deps?`, parentFieldPasses );
		return parentFieldPasses;
	};

	const atLeastOneDependsOnAnyMatch = !! parentFieldsForAnyMatch.length
		? parentFieldsForAnyMatch.some( checkParentDependencies )
		: true;

	if ( ! atLeastOneDependsOnAnyMatch ) {
		console.log( `${ indent }- false because at least one depends-on-any parent failed` );
		return false;
	}

	const doAllOtherParentFieldsMatch = !! parentFieldsForEveryMatch.length
		? parentFieldsForEveryMatch.every( checkParentDependencies )
		: false;

	if ( ! doAllOtherParentFieldsMatch ) {
		console.log( `${ indent }- false because of other dependency parent failure` );
		return false;
	}

	// If there were no depends-on, depends-on-any, excludes-on, or
	// wildcard-on matches, and none from parent fields, then the check passes.
	console.log( `${ indent }- passed!` );
	return true;
};

// @todo use useMemo?
const useDependencyCheck = (
	fieldConfig = {},
	allPodValues = {},
	allPodFieldsMap = new Map(),
) => {
	return recursiveCheckDepsForField( fieldConfig, allPodValues, allPodFieldsMap );
};

export default useDependencyCheck;
