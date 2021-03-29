/**
 * WordPress dependencies
 */
import {
	withSelect,
	withDispatch,
} from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Pods dependencies
 */
import FieldWrapper from 'dfv/src/components/field-wrapper';

import { STORE_KEY_DFV } from 'dfv/src/store/constants';

const ConnectedFieldWrapper = compose( [
	withSelect( ( storeSelect, ownProps ) => {
		const name = ownProps.field?.name;

		return {
			value: name ? storeSelect( STORE_KEY_DFV ).getPodOption( name ) : undefined,
			allPodValues: storeSelect( STORE_KEY_DFV ).getPodOptions(),
		};
	} ),
	withDispatch( ( storeDispatch ) => {
		return {
			setOptionValue: storeDispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

export default ConnectedFieldWrapper;
