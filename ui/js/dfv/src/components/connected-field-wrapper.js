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
		const name = ownProps.field?.name || '';

		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		const processedAllPodValues = allPodValues;

		// Workaround for the pick_object value: this value should be changed
		// to a combination of the `pick_object` sent by the API and the
		// `pick_val`. This was originally done to make the form easier to select.
		//
		// But this processing may not need to happen - it'll get set correctly
		// after a UI update, but will be wrong after the update from saving to the API,
		// so we'll check that the values haven't already been merged.
		let value = processedAllPodValues[ name ] || ownProps.field?.default || '';

		if (
			'pick_object' === name &&
			processedAllPodValues.pick_val &&
			! value.includes( `-${ processedAllPodValues.pick_val }`, `-${ processedAllPodValues.pick_val }`.length )
		) {
			value = `${ value }-${ processedAllPodValues.pick_val }`;
			processedAllPodValues.pick_object = `${ value }-${ processedAllPodValues.pick_val }`;
		}

		return {
			value,
			allPodValues: processedAllPodValues,
		};
	} ),
	withDispatch( ( storeDispatch ) => {
		return {
			setOptionValue: storeDispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

export default ConnectedFieldWrapper;
