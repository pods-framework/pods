import {
	POD_ID,
	POD_NAME,
	// Current Pod
	CURRENT_POD,
	GLOBAL_POD,
	GROUPS,
	// Global
	GLOBAL_SHOW_FIELDS,
	GLOBAL_POD_GROUPS,
	GLOBAL_GROUP,
	GLOBAL_FIELD,
	// Additional Global Config
	GLOBAL_FIELD_TYPES,
	GLOBAL_RELATED_OBJECTS,
	// UI
	ACTIVE_TAB,
	SAVE_STATUS,
	SAVE_MESSAGE,
	DELETE_STATUS,

	GROUP_SAVE_STATUSES,
	GROUP_SAVE_MESSAGES,
	GROUP_DELETE_STATUSES,
	GROUP_DELETE_MESSAGES,

	FIELD_SAVE_STATUSES,
	FIELD_SAVE_MESSAGES,
	FIELD_DELETE_STATUSES,
	FIELD_DELETE_MESSAGES,
} from './state-paths';

// Everything
export const getState = ( state ) => state;

// Current Pod
export const getPodID = ( state ) => POD_ID.getFrom( state );

export const getPodName = ( state ) => POD_NAME.getFrom( state );

export const getPodOptions = ( state ) => CURRENT_POD.getFrom( state );

export const getPodOption = ( state, key ) => CURRENT_POD.getFrom( state )[ key ];

//Get all validation messages
export const getValidationMessages = ( state ) => CURRENT_POD.getFrom( state ).validationMessages;
export const getNeedsValidating = ( state ) => CURRENT_POD.getFrom( state ).needsValidation;
//-- Pod Groups
export const getGroups = ( state ) => GROUPS.getFrom( state );

export const getGroup = ( state, groupName ) => getGroups( state ).find(
	( group ) => groupName === group.name
);

export const getGroupFields = ( state, groupName ) => {
	return getGroup( state, groupName )?.fields ?? [];
};

export const getFieldsFromAllGroups = ( state ) => getGroups( state ).reduce(
	( accumulator, group ) => {
		return [
			...accumulator,
			...( group?.fields || [] ),
		];
	},
	[]
);

// Global Pod config
export const getGlobalShowFields = ( state ) => GLOBAL_SHOW_FIELDS.getFrom( state );

export const getGlobalPodOptions = ( state ) => GLOBAL_POD.getFrom( state );

export const getGlobalPodOption = ( state, key ) => GLOBAL_POD.getFrom( state )[ key ];

export const getGlobalPodGroups = ( state ) => GLOBAL_POD_GROUPS.getFrom( state );

export const getGlobalPodGroup = ( state, groupName ) => getGlobalPodGroups( state ).find(
	( group ) => group.name === groupName
);

export const getGlobalPodGroupFields = ( state, groupName ) => getGlobalPodGroup( state, groupName )?.fields || [];

export const getGlobalPodFieldsFromAllGroups = ( state ) => getGlobalPodGroups( state ).reduce(
	( accumulator, group ) => {
		return [
			...accumulator,
			...( group?.fields || [] ),
		];
	},
	[]
);

// -- Global Groups config
export const getGlobalGroupOptions = ( state ) => GLOBAL_GROUP.getFrom( state );

// -- Global Field config
export const getGlobalFieldOptions = ( state ) => GLOBAL_FIELD.getFrom( state );

// Additional Global Config

// -- Global Field Types config
export const getFieldTypeObjects = ( state ) => GLOBAL_FIELD_TYPES.getFrom( state );
export const getFieldTypeObject = ( state, fieldName ) => GLOBAL_FIELD_TYPES.getFrom( state )[ fieldName ];

// -- Global Related Objects config
export const getFieldRelatedObjects = ( state ) => GLOBAL_RELATED_OBJECTS.getFrom( state );

// UI
export const getActiveTab = ( state ) => ACTIVE_TAB.getFrom( state );

export const getSaveStatus = ( state ) => SAVE_STATUS.getFrom( state );

export const getSaveMessage = ( state ) => SAVE_MESSAGE.getFrom( state );

export const getDeleteStatus = ( state ) => DELETE_STATUS.getFrom( state );

export const getGroupSaveStatuses = ( state ) => GROUP_SAVE_STATUSES.getFrom( state );
export const getGroupSaveStatus = ( state, groupName ) => GROUP_SAVE_STATUSES.getFrom( state )[ groupName ];

export const getGroupSaveMessages = ( state ) => GROUP_SAVE_MESSAGES.getFrom( state );
export const getGroupSaveMessage = ( state, groupName ) => GROUP_SAVE_MESSAGES.getFrom( state )[ groupName ];

export const getGroupDeleteStatuses = ( state ) => GROUP_DELETE_STATUSES.getFrom( state );
export const getGroupDeleteStatus = ( state, groupName ) => GROUP_DELETE_STATUSES.getFrom( state )[ groupName ];

export const getGroupDeleteMessages = ( state ) => GROUP_DELETE_MESSAGES.getFrom( state );
export const getGroupDeleteMessage = ( state, groupName ) => GROUP_DELETE_MESSAGES.getFrom( state )[ groupName ];

export const getFieldSaveStatuses = ( state ) => FIELD_SAVE_STATUSES.getFrom( state );
export const getFieldSaveStatus = ( state, fieldName ) => FIELD_SAVE_STATUSES.getFrom( state )[ fieldName ];

export const getFieldSaveMessages = ( state ) => FIELD_SAVE_MESSAGES.getFrom( state );
export const getFieldSaveMessage = ( state, fieldName ) => FIELD_SAVE_MESSAGES.getFrom( state )[ fieldName ];

export const getFieldDeleteStatuses = ( state ) => FIELD_DELETE_STATUSES.getFrom( state );
export const getFieldDeleteStatus = ( state, fieldName ) => FIELD_DELETE_STATUSES.getFrom( state )[ fieldName ];

export const getFieldDeleteMessages = ( state ) => FIELD_DELETE_MESSAGES.getFrom( state );
export const getFieldDeleteMessage = ( state, fieldName ) => FIELD_DELETE_MESSAGES.getFrom( state )[ fieldName ];
