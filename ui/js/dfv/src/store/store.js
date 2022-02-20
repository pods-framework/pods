/**
 * External dependencies
 */
import { configureStore } from '@reduxjs/toolkit';
import { omit } from 'lodash';
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies
 */
import { registerGenericStore } from '@wordpress/data';

/**
 * Pods dependencies
 */
import * as paths from './state-paths';
import {
	STORE_KEY_EDIT_POD,
	STORE_KEY_DFV,
	INITIAL_UI_STATE,
} from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import apiMiddleware from './api-middleware';

const initStore = ( initialState, storeKey ) => {
	const reduxStore = configureStore( {
		reducer,
		middleware: [ apiMiddleware ],
		preloadedState: initialState,
	} );

	const mappedSelectors = Object.keys( selectors ).reduce( ( acc, selectorKey ) => {
		acc[ selectorKey ] = ( ...args ) =>
			selectors[ selectorKey ]( reduxStore.getState(), ...args );
		return acc;
	}, {} );

	const mappedActions = Object.keys( actions ).reduce( ( acc, actionKey ) => {
		acc[ actionKey ] = ( ...args ) => reduxStore.dispatch( actions[ actionKey ]( ...args ) );
		return acc;
	}, {} );

	const genericStore = {
		getSelectors() {
			return mappedSelectors;
		},
		getActions() {
			return mappedActions;
		},
		subscribe: reduxStore.subscribe,
	};

	const uniqueStoreKey = `${ storeKey }-${ uuidv4() }`;

	registerGenericStore( uniqueStoreKey, genericStore );

	return uniqueStoreKey;
};

export const initEditPodStore = ( config, storeKeyIdentifier = '' ) => {
	// Use the first global Group if showFields is turned off,
	// otherwise the initial active tab should be "Manage Fields", which
	// isn't in the config data.
	const firstGroupTab = config?.global?.pod?.groups?.[ 0 ]?.name || '';

	const initialUIState = {
		...INITIAL_UI_STATE,
		activeTab: config.global.showFields ? 'manage-fields' : firstGroupTab,
	};

	const initialState = {
		...paths.UI.createTree( initialUIState ),
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
	};

	return initStore( initialState, `${ STORE_KEY_EDIT_POD }-${ storeKeyIdentifier }` );
};

export const initPodStore = ( config = {}, initialValues = {}, storeKeyIdentifier = '' ) => {
	const initialState = {
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
		currentPod: initialValues,
	};

	return initStore( initialState, `${ STORE_KEY_DFV }-${ storeKeyIdentifier }` );
};
