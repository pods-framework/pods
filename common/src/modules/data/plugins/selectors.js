/**
 * External dependencies
 */
import { includes } from 'lodash';
import { curry } from 'lodash/fp';

export const getPlugins = ( state ) => state.plugins;

export const hasPlugin = curry( ( state, plugin ) => includes( getPlugins( state ), plugin ) );
