import React from 'react';
import PropTypes from 'prop-types';

// noinspection JSUnresolvedVariable
const { sprintf, __ } = wp.i18n;

const MISSING = __( '[MISSING DEFAULT]', 'pods' );

export const PodsParameterizedLabel = ( props ) => {
	const  { labelFormat, labelParam, labelParamDefault } = props;

	return (
		<label>
			{sprintf( labelFormat, labelParam || labelParamDefault || MISSING )}
		</label>
	);
};

PodsParameterizedLabel.propTypes = {
	labelFormat: PropTypes.string.isRequired,
	labelParam: PropTypes.string,
	labelParamDefault: PropTypes.string,
};
