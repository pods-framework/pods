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
		const excludesOn = ownProps.field?.[ 'excludes-on' ] || {};

		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		const dependencyValueEntries = Object
			.keys( dependsOn )
			.map( ( fieldName ) => ( [
				fieldName,
				allPodValues[ fieldName ],
			] ) );
		const exclusionValueEntries = Object
			.keys( excludesOn )
			.map( ( fieldName ) => ( [
				fieldName,
				allPodValues[ fieldName ],
			] ) );

		return {
			dependencyValues: Object.fromEntries( dependencyValueEntries ),
			exclusionValues: Object.fromEntries( exclusionValueEntries ),
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
