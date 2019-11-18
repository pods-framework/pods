/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { store } from '@moderntribe/common/store';

const getStore = () => store;

export default ( additionalProps = {} ) => ( WrappedComponent ) => {

	const WithStore = ( props ) => {
		const extraProps = {
			...additionalProps,
			store: getStore(),
		};

		return <WrappedComponent { ...props } { ...extraProps } />;
	};

	return WithStore;

};
