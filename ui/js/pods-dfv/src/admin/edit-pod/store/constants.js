export const STORE_KEY_EDIT_POD = 'pods/edit-pod';

export const uiConstants = {
	actions: {
		SET_ACTIVE_TAB: 'SET_ACTIVE_TAB',
		SET_SAVE_STATUS: 'SET_SAVE_STATUS',
	},

	tabNames: {
		MANAGE_FIELDS: 'MANAGE_FIELDS',
		LABELS: 'LABELS',
		ADMIN_UI: 'ADMIN_UI',
		ADVANCED_OPTIONS: 'ADVANCED_OPTIONS',
		AUTO_TEMPLATE_OPTIONS: 'AUTO_TEMPLATE_OPTIONS',
		REST_API: 'REST_API',
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
	},
};

export const labelConstants = {
	actions: {
		SET_LABEL_VALUE: 'SET_LABEL_VALUE',
	},
};
