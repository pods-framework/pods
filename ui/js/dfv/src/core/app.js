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

/**
 * Pods config
 */
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';
import useBlockEditor from '../hooks/useBlockEditor';

const App = ( {
	storeKey,
} ) => {
	const fieldsData = PodsDFVAPI._fieldDataByStoreKeyPrefix[ storeKey ];
	const allPodFieldsMap = new Map( fieldsData.map( ( field ) => [ field.name, field ] ) );

	// Initialize Pods Block Editor overrides if available.
	useBlockEditor();

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
					allPodFieldsMap={ allPodFieldsMap }
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

App.propTypes = {
	storeKey: PropTypes.string.isRequired,
};

export default App;
