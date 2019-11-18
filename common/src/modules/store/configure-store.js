/**
 * External dependencies
 */
import { createStore, applyMiddleware } from 'redux';
import { composeWithDevTools } from 'redux-devtools-extension/developmentOnly';
import { augmentStore } from '@nfen/redux-reducer-injector';
import thunk from 'redux-thunk';
import createSagaMiddleware from 'redux-saga';

/**
 * Internal dependencies
 */
import reducer from '@moderntribe/common/data';
import { wpRequest } from './middlewares';

const sagaMiddleware = createSagaMiddleware();

export default () => {
	if ( window.__tribe_common_store__ ) {
		return window.__tribe_common_store__;
	}

	const middlewares = [
		thunk,
		sagaMiddleware,
		wpRequest,
	];

	const composeEnhancers = composeWithDevTools( { name: 'tribe/common' } );

	const store = createStore( reducer( {} ), composeEnhancers( applyMiddleware( ...middlewares ) ) );
	augmentStore( reducer, store );
	store.run = sagaMiddleware.run;
	window.__tribe_common_store__ = store;

	return store;
};
