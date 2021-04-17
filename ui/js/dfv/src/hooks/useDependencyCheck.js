/**
 * Other Pods dependencies
 */
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';
import unstackDependencies from 'dfv/src/helpers/unstackDependencies';

const useDependencyCheck = (
	allPodValues = {},
	allPodFieldsMap = new Map(),
	dependsOn = {},
	dependsOnAny = {},
	excludesOn = {},
	wildcardOn = {},
) => {
	let meetsDependencies = true;

	// Calculate dependencies, trying to skip as many of these checks as
	// we can because they're expensive.

	if ( dependsOn && Object.keys( dependsOn ).length ) {
		const unstackedDependsOn = unstackDependencies( dependsOn, allPodFieldsMap, 'depends-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedDependsOn, 'depends-on' ) ) {
			meetsDependencies = false;
		}
	} else if ( dependsOnAny && Object.keys( dependsOnAny ).length ) {
		const unstackedDependsOnAny = unstackDependencies( dependsOn, allPodFieldsMap, 'depends-on-any' );

		if ( ! validateFieldDependencies( allPodValues, unstackedDependsOnAny, 'depends-on-any' ) ) {
			meetsDependencies = false;
		}
	} else if ( excludesOn && Object.keys( excludesOn ).length ) {
		const unstackedExcludesOn = unstackDependencies( excludesOn, allPodFieldsMap, 'excludes-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedExcludesOn, 'excludes' ) ) {
			meetsDependencies = false;
		}
	} else if ( wildcardOn ) {
		const unstackedWildcardOn = unstackDependencies( wildcardOn, allPodFieldsMap, 'wildcard-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedWildcardOn, 'wildcard' ) ) {
			meetsDependencies = false;
		}
	}

	return meetsDependencies;
};

export default useDependencyCheck;
