import {
	combineReducers,
	registerStore,
	select,
	dispatch,
	withSelect,
	withDispatch,
} from '@wordpress/data';

global.wp = {
	data: {
		registerStore: registerStore,
		combineReducers: combineReducers,
		select: select,
		dispatch: dispatch,
		withSelect: withSelect,
		withDispatch: withDispatch,
	}
};
