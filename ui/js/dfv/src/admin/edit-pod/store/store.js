// import { createStore, applyMiddleware } from 'redux';
import { configureStore } from '@reduxjs/toolkit';

import { registerGenericStore } from '@wordpress/data';

import * as paths from './state-paths';
import { STORE_KEY_EDIT_POD, initialUIState } from './constants';
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import apiMiddleware from './api-middleware';

export const initStore = ( props ) => {
	const initialState = {
		...paths.UI.createTree( initialUIState ),
		...props.config || {},
	};

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

	registerGenericStore( STORE_KEY_EDIT_POD, genericStore );
};
