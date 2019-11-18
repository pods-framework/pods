/**
 * External dependencies
 */
import { isEmpty, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

export const toWpParams = ( args = {} ) => {
	const params = {
		orderby: 'title',
		status: [ 'draft', 'publish' ],
		order: 'asc',
		page: 1,
		...args,
	};

	if ( ! isUndefined( params.search ) && ! isEmpty( params.search ) ) {
		params.orderby = 'relevance';
	}

	if ( isEmpty( params.exclude ) ) {
		delete params.exclude;
	}

	return params;
};

export const toWPQuery = ( args = {} ) => stringify( toWpParams( args ) );

export const getTotalPages = ( headers ) => {
	const totalPages = parseInt( headers.get( 'x-wp-totalpages' ), 10 );
	return isNaN( totalPages ) ? 0 : totalPages;
};
