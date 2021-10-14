/**
 * WordPress dependencies
 */
import {
	withSelect,
	withDispatch,
} from '@wordpress/data';
import { compose } from '@wordpress/compose';
import PropTypes from 'prop-types';

/**
 * Pods dependencies
 */
import FieldWrapper from 'dfv/src/components/field-wrapper';

const ConnectedFieldWrapper = compose( [
	withSelect( ( storeSelect, ownProps ) => {
		const { storeKey } = ownProps;

		const allPodValues = storeSelect( storeKey ).getPodOptions();

		const valueData = {};

		// Boolean Group fields get a map of values, instead of a singular value.
		if ( 'boolean_group' === ownProps.field?.type ) {
			valueData.values = {};

			const subFields = ownProps.field?.boolean_group || [];

			subFields.forEach( ( subField ) => {
				if ( ! subField.name ) {
					return;
				}

				valueData.values[ subField.name ] = allPodValues[ subField.name ];
			} );
		} else {
			const name = ownProps.field?.name;

			valueData.value = name ? allPodValues[ name ] : undefined;
		}

		return {
			...valueData,
			allPodValues,
		};
	} ),
	withDispatch( ( storeDispatch, ownProps ) => {
		const { storeKey } = ownProps;

		return {
			setOptionValue: storeDispatch( storeKey ).setOptionValue,
		};
	} ),
] )( FieldWrapper );

ConnectedFieldWrapper.propTypes = {
	storeKey: PropTypes.string.isRequired,
};

export default ConnectedFieldWrapper;
