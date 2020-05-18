import { merge } from 'lodash';

import * as paths from './state-paths';
import { STORE_KEY_EDIT_POD, initialUIState } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';

const { registerStore } = wp.data;

export const initStore = ( props ) => {
	const initialState = merge(
		paths.UI.createTree( initialUIState ),
		props.config || {}
	);

	console.log( 'initialState', initialState );

	return registerStore( STORE_KEY_EDIT_POD, {
		reducer,
		selectors,
		actions,
		initialState,
	} );
};
