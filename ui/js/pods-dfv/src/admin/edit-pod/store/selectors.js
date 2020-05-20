import {
	POD_ID,
	POD_NAME,
	// Current Pod
	CURRENT_POD,
	GLOBAL_POD,
	GROUPS,
	// Global
	GLOBAL_POD_GROUPS,
	GLOBAL_GROUP,
	GLOBAL_FIELD,
	// UI
	ACTIVE_TAB,
	SAVE_STATUS,
	SAVE_MESSAGE,
	DELETE_STATUS,
} from './state-paths';

// Everything
export const getState = ( state ) => state;

// Current Pod
export const getPodID = ( state ) => POD_ID.getFrom( state );

export const getPodName = ( state ) => POD_NAME.getFrom( state );

export const getPodOptions = ( state ) => CURRENT_POD.getFrom( state );

export const getPodOption = ( state, key ) => CURRENT_POD.getFrom( state )[ key ];

//-- Pod Groups
export const getGroups = ( state ) => GROUPS.getFrom( state );

export const getGroupList = ( state ) => getGroups( state )
	.map( ( ( group ) => group.id ) );

export const getGroup = ( state, groupName ) => getGroups( state ).find(
	( group ) => groupName === group.name
);

export const getGroupFields = ( state, groupName ) => {
	if ( getGroup( state, groupName )?.fields ) {
		return getGroup( state, groupName ).fields;
	}
	return [];
};

export const groupFieldList = ( state ) => getGroups( state ).reduce(
	( accumulator, group ) => {
		const groupName = group.name;
		const groupFieldIDs = group.fields.map( ( field ) => field.id );

		return {
			...accumulator,
			[ groupName ]: groupFieldIDs,
		};
	},
	{}
);

// Global Pod config
export const getGlobalPodOptions = ( state ) => GLOBAL_POD.getFrom( state );

export const getGlobalPodOption = ( state, key ) => GLOBAL_POD.getFrom( state )[ key ];

export const getGlobalPodGroups = ( state ) => GLOBAL_POD_GROUPS.getFrom( state );

export const getGlobalPodGroup = ( state, groupName ) => getGlobalPodGroups( state ).find(
	( group ) => group.name === groupName
);

export const getGlobalPodGroupFields = ( state, groupName ) => getGlobalPodGroup( state, groupName )?.fields || [];

// -- Global Groups config
export const getGlobalGroupOptions = ( state ) => GLOBAL_GROUP.getFrom( state );

// -- Global Field config
export const getGlobalFieldOptions = ( state ) => GLOBAL_FIELD.getFrom( state );

// UI
export const getActiveTab = ( state ) => ACTIVE_TAB.getFrom( state );

export const getSaveStatus = ( state ) => SAVE_STATUS.getFrom( state );

export const getSaveMessage = ( state ) => SAVE_MESSAGE.getFrom( state );

export const getDeleteStatus = ( state ) => DELETE_STATUS.getFrom( state );
