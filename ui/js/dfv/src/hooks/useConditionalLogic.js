import { toBool } from 'dfv/src/helpers/booleans';
// import { validateFieldDependencies, formatDependency } from 'dfv/src/helpers/validateFieldDependencies';

const recursiveCheckConditionalLogicForField = (
	fieldConfig,
	allPodValues,
	allPodFieldsMap,
) => {
	const {
		enable_conditional_logic: enableConditionalLogic,
		conditional_logic: {
			action,
			logic,
			rules,
		},
	} = fieldConfig;

	// The field is always enabled if "conditional logic" is not turned on.
	if ( ! toBool( enableConditionalLogic ) ) {
		return true;
	}

	return true;
};

const useConditionalLogic = (
	fieldConfig = {},
	allPodValues = {},
	allPodFieldsMap = new Map(),
) => {
	return recursiveCheckConditionalLogicForField( fieldConfig, allPodValues, allPodFieldsMap );
};

export default useConditionalLogic;
