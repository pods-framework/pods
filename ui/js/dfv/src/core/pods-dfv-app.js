/**
 * External dependencies
 */
import React, { useEffect } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

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

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';
import { STORE_KEY_DFV } from 'dfv/src/admin/edit-pod/store/constants';

const ConnectedFieldWrapper = compose( [
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
] )( FieldWrapper );

const PodsDFVApp = ( { fieldsData } ) => {
	const fieldComponents = fieldsData.map( ( fieldData = {} ) => {
		console.log( 'fieldConfig about to create Portal', fieldData );

		const {
			fieldComponent, // used if we replace the "direct renderer"
			parentNode,
			fieldConfig,
			fieldItemData, // shouldn't be used here
		} = fieldData;

		return ReactDOM.createPortal(
			<ConnectedFieldWrapper field={ fieldConfig } />,
			parentNode
		);
	} );

	// We don't *really* render anything in the main app, all
	// the fields get set up in Portals.
	return (
		<>
			{ fieldComponents }
		</>
	);
};

PodsDFVApp.propTypes = {
	fieldsData: PropTypes.arrayOf(
		PropTypes.shape( {
			fieldComponent: PropTypes.function,
			parentNode: PropTypes.any,
			fieldConfig: FIELD_PROP_TYPE_SHAPE,
			fieldItemData: PropTypes.arrayOf( PropTypes.any ).isRequired,
			fieldHtmlAttr: PropTypes.object,
			fieldType: PropTypes.string.isRequired,
			fieldEmbed: PropTypes.bool.isRequired,
		} ),
	),
};

export default PodsDFVApp;
