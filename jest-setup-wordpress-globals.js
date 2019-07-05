import lodash from 'lodash';

import {
	combineReducers,
	registerStore,
	select,
	dispatch,
	withSelect,
	withDispatch,
} from '@wordpress/data';


global.lodash = lodash;

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
