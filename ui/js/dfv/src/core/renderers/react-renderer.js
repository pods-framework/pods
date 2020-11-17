import React from 'react';
import { omit } from 'lodash';
import ReactDOM from 'react-dom';

// WordPress dependencies
import {
	withSelect,
	withDispatch,
	select,
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
	withDispatch( ( dispatch ) => {
		return {
			setOptionValue: dispatch( STORE_KEY_DFV ).setOptionValue,
		};
	} ),
] )( DependentFieldOption );

function reactRenderer( component, element, props ) {
	// Create the store if it hasn't been done already.
	if ( undefined !== select( STORE_KEY_DFV ) ) {
		console.log( 'store has not been set up, about to initialize' );
		initPodStore( props.config || {} );
	}

	// eslint-disable-next-line no-console
	console.log( 'REACT RENDERER: ', element, props );

	const fieldConfig = omit(
		props.data?.fieldConfig || {},
		[ '_field_object', 'output_options', 'item_id' ]
	);

	ReactDOM.render(
		<ConnectedDependentFieldOption
			field={ fieldConfig }
		/>,
		element
	);
}

export default reactRenderer;
