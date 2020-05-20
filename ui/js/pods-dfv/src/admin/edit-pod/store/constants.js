export const STORE_KEY_EDIT_POD = 'pods/edit-pod';

export const uiConstants = {
	actions: {
		SET_ACTIVE_TAB: 'SET_ACTIVE_TAB',
		SET_SAVE_STATUS: 'SET_SAVE_STATUS',
		SET_DELETE_STATUS: 'SET_DELETE_STATUS',
	},

	deleteStatuses: {
		NONE: '',
		DELETING: 'DELETING',
		DELETE_SUCCESS: 'DELETE_SUCCESS',
		DELETE_ERROR: 'DELETE_ERROR',
	},

	saveStatuses: {
		NONE: '',
		SHOULD_SAVE: 'SHOULD_SAVE',
		SAVING: 'SAVING',
		SAVE_SUCCESS: 'SAVE_SUCCESS',
		SAVE_ERROR: 'SAVE_ERROR',
	},

	dragItemTypes: {
		GROUP: 'GROUP',
		FIELD: 'FIELD',
	},
};

export const currentPodConstants = {
	actions: {
		SET_POD_NAME: 'SET_POD_NAME',

		SET_OPTION_VALUE: 'SET_OPTION_ITEM_VALUE',
		SET_OPTIONS_VALUES: 'SET_OPTIONS_VALUES',

		SET_GROUP_LIST: 'SET_GROUP_LIST',
		MOVE_GROUP: 'MOVE_GROUP',
		ADD_GROUP: 'ADD_GROUP',
		DELETE_GROUP: 'DELETE_GROUP',
		SET_GROUP_FIELDS: 'SET_GROUP_FIELDS',
		ADD_GROUP_FIELD: 'ADD_GROUP_FIELD',
	},
};

export const initialUIState = {
	activeTab: 'manage-fields',
	saveStatus: uiConstants.saveStatuses.NONE,
	deleteStatus: uiConstants.deleteStatuses.NONE,
	saveMessage: null,
	deleteMessage: null,
};
