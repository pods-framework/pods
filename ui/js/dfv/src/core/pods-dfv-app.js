/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

/**
 * Pods dependencies
 */
import ConnectedFieldWrapper from 'dfv/src/components/connected-field-wrapper';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const PodsDFVApp = ( {
	fieldsData,
	storeKey,
} ) => {
	const fieldComponents = fieldsData.map( ( fieldData = {} ) => {
		const {
			directRender = false,
			fieldComponent: FieldComponent = null,
			parentNode,
			fieldConfig,
		} = fieldData;

		// Some components will have a React component passed in (eg. the Edit Pod field
		// for the Edit Pod screen), but most won't.
		const renderedFieldComponent = directRender
			? <FieldComponent storeKey={ storeKey } />
			: (
				<ConnectedFieldWrapper
					storeKey={ storeKey }
					field={ fieldConfig }
					allPodFieldsMap={ new Map( fieldsData.map( ( field ) => [ field.name, field ] ) ) }
				/>
			);

		// Remove the loading indicator.
		parentNode.classList.remove( 'pods-dfv-field--unloaded' );
		parentNode.classList.add( 'pods-dfv-field--loaded' );

		const loadingIndicator = parentNode.querySelector( '.pods-dfv-field__loading-indicator' );

		if ( loadingIndicator ) {
			parentNode.removeChild( loadingIndicator );
		}

		// Create the Portal to render the field.
		return ReactDOM.createPortal(
			renderedFieldComponent,
			parentNode
		);
	} );

	// We don't *really* render anything in the main app, all
	// the fields get set up in Portals.
	return (
		<React.StrictMode>
			{ fieldComponents }
		</React.StrictMode>
	);
};

PodsDFVApp.propTypes = {
	fieldsData: PropTypes.arrayOf(
		PropTypes.shape( {
			directRender: PropTypes.bool.isRequired,
			fieldComponent: PropTypes.function,
			parentNode: PropTypes.any,
			fieldConfig: FIELD_PROP_TYPE_SHAPE,
			fieldItemData: PropTypes.any,
			fieldValue: PropTypes.any,
		} ),
	),
	storeKey: PropTypes.string.isRequired,
};

export default PodsDFVApp;
