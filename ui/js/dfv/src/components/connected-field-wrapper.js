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
		const name = ownProps.field.name || '';

		const dependsOn = ownProps.field?.[ 'depends-on' ] || {};

		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		// Find all of the dependency values.
		const dependencyValueEntries = Object.keys( dependsOn )
			.map( ( fieldName ) => {
				let storeKeyName = fieldName;

				// Some Pods fields have prefixes of pods_meta_, pods_setting_, or pods_field_,
				// so adjust the keys that we look for if the field name has one of those.
				if ( name.startsWith( 'pods_meta_' ) ) {
					storeKeyName = `pods_meta_${ fieldName }`;
				} else if ( name.startsWith( 'pods_setting_' ) ) {
					storeKeyName = `pods_setting_${ fieldName }`;
				} else if ( name.startsWith( 'pods_field_' ) ) {
					storeKeyName = `pods_field_${ fieldName }`;
				}

				return [
					fieldName,
					allPodValues[ storeKeyName ],
				];
			} );

		return {
			dependencyValues: Object.fromEntries( dependencyValueEntries ),
			value: allPodValues[ name ] || ownProps.field?.default || '',
		};
	} ),
	withDispatch( ( storeDispatch ) => {
		return {
			setOptionValue: storeDispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

export default ConnectedFieldWrapper;
