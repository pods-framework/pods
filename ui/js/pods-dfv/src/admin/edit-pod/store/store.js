import * as paths from './state-paths';
import { STORE_KEY_EDIT_POD, initialUIState } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';

import { registerStore } from '@wordpress/data';

export const initStore = ( props ) => {
	const initialState = {
		...paths.UI.createTree( initialUIState ),
		...props.config || {},
	};

	return registerStore( STORE_KEY_EDIT_POD, {
		reducer,
		selectors,
		actions,
		initialState,
	} );
};
