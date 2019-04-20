import { STORE_KEY_EDIT_POD } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
const { registerStore } = wp.data;

export const initStore = ( props ) => {
	props.podInfo = props.podInfo || {};

	const initialState = {
		fields: props.fields,
		labels: props.labels,
		podMeta: {
			podName: props.podInfo.name
		}
	};

	return registerStore( STORE_KEY_EDIT_POD, {
		reducer: reducer,
		selectors: selectors,
		actions: actions,
		initialState: initialState,
	} );
};
