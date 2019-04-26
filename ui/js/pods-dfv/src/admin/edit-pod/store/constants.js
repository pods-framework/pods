export const STORE_KEY_EDIT_POD = 'pods/edit-pod';

export const uiConstants = {
	actions: {
		SET_ACTIVE_TAB: 'SET_ACTIVE_TAB',
		SET_SAVE_STATUS: 'SET_SAVE_STATUS',
	},

	tabNames: {
		MANAGE_FIELDS: 'manage-fields',
		LABELS: 'labels',
		ADMIN_UI: 'admin-ui',
		ADVANCED_OPTIONS: 'advanced',
		AUTO_TEMPLATE_OPTIONS: 'pods-pfat',
		REST_API: 'rest-api',
	},

	saveStatuses: {
		NONE: '',
		SAVING: 'SAVING',
		SAVE_SUCCESS: 'SAVE_SUCCESS',
		SAVE_ERROR: 'SAVE_ERROR',
	},
};

export const podMetaConstants = {
	actions: {
		SET_POD_NAME: 'SET_POD_NAME',
		SET_POD_META_VALUE: 'SET_POD_META_VALUE',
	},
};

export const initialUIState = {
	activeTab: uiConstants.tabNames.MANAGE_FIELDS,
	saveStatus: uiConstants.saveStatuses.NONE,
	tabs: [],
	options: []
};
