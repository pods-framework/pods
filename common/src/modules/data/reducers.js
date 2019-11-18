/**
 * External dependencies
 */
import { setupCreateReducer } from '@nfen/redux-reducer-injector';
import forms from './forms';

/**
 * Internal dependencies
 */
import plugins from './plugins';

export default setupCreateReducer( {
	plugins,
	forms,
} );
