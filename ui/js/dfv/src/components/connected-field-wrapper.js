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
		const wildcardOn = ownProps.field?.[ 'wildcard-on' ] || {};

		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		// Find all of the dependency values.
		const dependencyValueEntries = Object
			.keys( dependsOn )
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

		const exclusionValueEntries = Object
			.keys( excludesOn )
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

		const wildcardValueEntries = Object
			.keys( wildcardOn )
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

		const dependencyValues = Object.fromEntries( dependencyValueEntries );
		const exclusionValues = Object.fromEntries( exclusionValueEntries );
		const wildcardValues = Object.fromEntries( wildcardValueEntries );

		// Workaround for the pick_object value: this value should be changed
		// to a combination of the `pick_object` sent by the API and the
		// `pick_val`. This was originally done to make the form easier to select.
		//
		// But this processing may not need to happen - it'll get set correctly
		// after a UI update, but will be wrong after the update from saving to the API,
		// so we'll check that the values haven't already been merged.
		let value = allPodValues[ name ] || ownProps.field?.default || '';

		const processedDependencyValues = dependencyValues;
		const processedExclusionValues = exclusionValues;
		const processedWildcardValues = wildcardValues;

		if (
			'pick_object' === name &&
			dependencyValues.pick_val &&
			! value.includes( `-${ dependencyValues.pick_val }`, `-${ dependencyValues.pick_val }`.length )
		) {
			value = `${ value }-${ dependencyValues.pick_val }`;
			processedDependencyValues.pick_object = `${ value }-${ dependencyValues.pick_val }`;
		}

		if (
			'pick_object' === name &&
			exclusionValues.pick_val &&
			! value.includes( `-${ exclusionValues.pick_val }`, `-${ exclusionValues.pick_val }`.length )
		) {
			processedExclusionValues.pick_object = `${ value }-${ exclusionValues.pick_val }`;
		}

		if (
			'pick_object' === name &&
			wildcardValues.pick_val &&
			! value.includes( `-${ wildcardValues.pick_val }`, `-${ wildcardValues.pick_val }`.length )
		) {
			processedWildcardValues.pick_object = `${ value }-${ wildcardValues.pick_val }`;
		}

		return {
			dependencyValues: processedDependencyValues,
			exclusionValues: processedExclusionValues,
			wildcardValues: processedWildcardValues,
			value,
		};
	} ),
	withDispatch( ( storeDispatch ) => {
		return {
			setOptionValue: storeDispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

export default ConnectedFieldWrapper;
