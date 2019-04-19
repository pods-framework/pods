import {
	combineReducers,
	registerStore,
	withSelect,
	withDispatch,
} from '@wordpress/data';

global.wp = {
	data: {
		combineReducers: combineReducers,
		registerStore: registerStore,
		withDispatch: withDispatch,
		withSelect: withSelect,
	}
};
