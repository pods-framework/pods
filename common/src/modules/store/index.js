/**
 * External dependencies
 */
import 'regenerator-runtime/runtime';

/**
 * Internal dependencies
 */
import configureStore from './configure-store';
import * as middlewares from './middlewares';

export const store = configureStore();
export { middlewares };
