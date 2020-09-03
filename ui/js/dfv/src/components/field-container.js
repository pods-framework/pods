import React, { useState } from 'react';
import classNames from 'classnames';
import PropTypes from 'prop-types';

import PodsDFVValidationMessages from 'dfv/src/components/validation-messages';
import * as validationRules from 'dfv/src/validation/validation-rules';
import { podsValidation } from 'dfv/src/validation/validation';

const PodsDFVFieldContainer = ( props ) => {
	const {
		fieldComponent: Field,
		fieldConfig,
		fieldItemData,
		htmlAttr = {},
	} = props;

	const [ value, setValue ] = useState( fieldItemData[ 0 ] || '' );
	const validation = podsValidation();
	const validationMessages = validation.useValidation( value );

	validation.addRules( [
		{
			rule: validationRules.required( value, fieldConfig.label ),
			condition: '1' === props.fieldConfig.required,
		},
	] );

	const fieldClasses = classNames(
		htmlAttr.class || '',
		{ 'pods-validate-error': validationMessages.length }
	);

	return (
		<div className="pods-dfv-container">
			<Field
				value={ value }
				setValue={ setValue }
				validation={ validation }
				className={ fieldClasses }
				{ ...props }
			/>
			<PodsDFVValidationMessages messages={ validationMessages } />
		</div>
	);
};

PodsDFVFieldContainer.defaultProps = {
	fieldItemData: [],
	htmlAttr: {},
};

PodsDFVFieldContainer.propTypes = {
	fieldComponent: PropTypes.func.isRequired,
	// @todo specify shape
	fieldConfig: PropTypes.object,
	// @todo specify types of items in array
	fieldItemData: PropTypes.array,
	htmlAttr: PropTypes.shape( {
		class: PropTypes.string,
	} ),
};

export default PodsDFVFieldContainer;
