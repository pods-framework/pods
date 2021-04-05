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
		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		const valueData = {};

		// Boolean Group fields get a map of values, instead of a singular value.
		if ( 'boolean_group' === ownProps.field?.type ) {
			valueData.values = {};

			const subFields = ownProps.field?.['boolean_group'] || [];

			subFields.forEach( ( subField ) => {
				if ( ! subField.name ) {
					return;
				}

				valueData.values[ subField.name ] = allPodValues[ subField.name ];
			} );
		} else {
			const name = ownProps.field?.name;

			valueData.value = name ? allPodValues[ name ] : undefined;
		}

		return {
			...valueData,
			allPodValues,
		};
	} ),
	withDispatch( ( storeDispatch ) => {
		return {
			setOptionValue: storeDispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

export default ConnectedFieldWrapper;
