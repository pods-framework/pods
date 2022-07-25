/**
 * External dependencies
 */
import { configureStore } from '@reduxjs/toolkit';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { registerGenericStore } from '@wordpress/data';

/**
 * Pods dependencies
 */
import * as paths from './state-paths';
import { INITIAL_UI_STATE } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import apiMiddleware from './api-middleware';

/**
 * Creates a consistent Redux store key, for when we have a store for each pod.
 *
 * @param {string} pod         Pod slug/name.
 * @param {int}    itemId      Object ID.
 * @param {int}    formCounter Form index. (Optional.)
 * @param {string} prefix      Prefix. (Optional.)
 */
 export const createStoreKey = ( pod, itemId, formCounter = 0, prefix = '' ) => {
	return prefix.length ?
		`${ prefix }-${ pod }-${ itemId }-${ formCounter }`
		: `${ pod }-${ itemId }-${ formCounter }`;
};

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

	registerGenericStore( storeKey, genericStore );

	return storeKey;
};

export const initEditPodStore = ( config, storeKey = '' ) => {
	// Use the first global Group if showFields is turned off,
	// otherwise the initial active tab should be "Manage Fields", which
	// isn't in the config data.
	const firstGroupTab = config?.global?.pod?.groups?.[ 0 ]?.name || '';

	const initialUIState = {
		...INITIAL_UI_STATE,
		activeTab: config.global?.showFields === false ? firstGroupTab : 'manage-fields',
	};

	const initialState = {
		...paths.UI.createTree( initialUIState ),
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
	};

	return initStore( initialState, storeKey );
};

export const initPodStore = ( config = {}, initialValues = {}, storeKey = '' ) => {
	const initialState = {
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
		currentPod: initialValues,
	};

	return initStore( initialState, storeKey );
};
