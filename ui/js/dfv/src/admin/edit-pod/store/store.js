import { configureStore } from '@reduxjs/toolkit';
import { omit } from 'lodash';

import { registerGenericStore } from '@wordpress/data';

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

	registerGenericStore( storeKey, genericStore );
};

export const initEditPodStore = ( config ) => {
	const initialState = {
		...paths.UI.createTree( INITIAL_UI_STATE ),
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
	};

	initStore( initialState, STORE_KEY_EDIT_POD );
};

export const initPodStore = ( config = {}, initialValues = {} ) => {
	const initialState = {
		data: {
			fieldTypes: { ...config.fieldTypes || {} },
			relatedObjects: { ...config.relatedObjects || {} },
		},
		...omit( config, [ 'fieldTypes', 'relatedObjects' ] ),
		currentPod: initialValues,
	};

	initStore( initialState, STORE_KEY_DFV );
};
