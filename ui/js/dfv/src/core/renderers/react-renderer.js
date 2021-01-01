import React from 'react';
import { omit } from 'lodash';
import ReactDOM from 'react-dom';

// WordPress dependencies
import {
	withSelect,
	withDispatch,
	select,
	dispatch,
} from '@wordpress/data';
import { compose } from '@wordpress/compose';

// Pods dependencies
import { initPodStore } from 'dfv/src/admin/edit-pod/store/store';
import { DependentFieldOption } from 'dfv/src/components/dependent-field-option';
import { STORE_KEY_DFV } from 'dfv/src/admin/edit-pod/store/constants';

const ConnectedDependentFieldOption = compose( [
	withSelect( ( storeSelect, ownProps ) => {
		const name = ownProps.field.name || '';
		const dependsOn = ownProps.field?.[ 'depends-on' ] || {};

		// @todo does this work?
		const allPodValues = storeSelect( STORE_KEY_DFV ).getPodOptions();

		const dependencyValueEntries = Object
			.keys( dependsOn )
			.map( ( fieldName ) => ( [
				fieldName,
				allPodValues[ fieldName ],
			] ) );

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
] )( DependentFieldOption );

function reactRenderer( component, element, props ) {
	// Create the store if it hasn't been done already.
	if ( ! select( STORE_KEY_DFV ) ) {
		initPodStore( props.config || {} );
	}

	console.log( 'react renderer: ', element, props );

	const fieldConfig = omit(
		props.data?.fieldConfig || {},
		[ '_field_object', 'output_options', 'item_id' ]
	);

	// Fields have values provided as arrays, even if the field
	// type should just have a singular value. This may change
	// in the future once repeatable fields are supported.
	const value = [ 'avatar', 'file', 'pick' ].includes( fieldConfig.type )
		? ( props.data?.fieldItemData || fieldConfig.default || [] )
		: ( props.data?.fieldItemData?.[ 0 ] || fieldConfig.default || '' );

	// Some field types need the value to be adjusted because it's in a different
	// shape than expected.
	// @todo there are probably others?
	let formattedValue = value;

	switch ( fieldConfig.type ) {
		case 'pick':
			if ( 'multi' === fieldConfig.format_type ) {
				formattedValue = value
					.map( ( option ) => option.selected ? option.id : null )
					.filter( ( option ) => null !== option );
			} else {
				formattedValue = value.find( ( option ) => true === option.selected )?.id;
			}
			break;
		default:
			break;
	}

	// Set the initial value.
	dispatch( STORE_KEY_DFV ).setOptionValue( fieldConfig.name, formattedValue );

	ReactDOM.render(
		<ConnectedDependentFieldOption
			field={ {
				...fieldConfig,
				htmlAttr: props.data?.htmlAttr || {},
				fieldEmbed: props.data?.fieldEmbed || false,
			} }
		/>,
		element
	);
}

export default reactRenderer;
