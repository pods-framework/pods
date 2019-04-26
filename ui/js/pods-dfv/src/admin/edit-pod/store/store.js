import {
	STORE_KEY_EDIT_POD,
	initialUIState
} from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';

const { registerStore } = wp.data;

export const initStore = ( props ) => {
	const initialState = {
		ui: { ...initialUIState, ...props.ui },
		fields: props.fields,
		podMeta: props.podMeta,
	};

	return registerStore( STORE_KEY_EDIT_POD, {
		reducer: reducer,
		selectors: selectors,
		actions: actions,
		initialState: initialState,
	} );
};
