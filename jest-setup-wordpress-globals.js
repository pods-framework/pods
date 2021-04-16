import lodash from 'lodash';
import React from 'react';

import {
	combineReducers,
	registerStore,
	select,
	dispatch,
	withSelect,
	withDispatch,
} from '@wordpress/data';

global.React = React;

global.window.matchMedia = () => ( {
	matches: false,
	addListener: () => {},
	removeListener: () => {},
} );

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
