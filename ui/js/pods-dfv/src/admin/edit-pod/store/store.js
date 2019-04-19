import { STORE_KEY_EDIT_POD } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
const { registerStore } = wp.data;

export const initStore = ( props ) => {
	const initialState = {
		fields: props.fields,
		labels: props.labels,
	};

	return registerStore( STORE_KEY_EDIT_POD, {
		reducer: reducer,
		selectors: selectors,
		actions: actions,
		initialState: initialState,
	} );
};
