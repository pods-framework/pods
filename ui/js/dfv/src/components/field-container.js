import React, { useState } from 'react';
import classNames from 'classnames';

import PodsDFVValidationMessages from 'dfv/src/components/validation-messages';
import { validationRules } from 'dfv/src/validation/validation-rules';
import { podsValidation } from 'dfv/src/validation/validation';

const PodsDFVFieldContainer = ( props ) => {
	const Field = props.fieldComponent;
	const [ value, setValue ] = useState( props.fieldItemData[ 0 ] || '' );
	const validation = podsValidation();
	const validationMessages = validation.useValidation( value );

	validation.addRules( [
		{
			rule: validationRules.required( value, props.fieldConfig.label ),
			condition: '1' === props.fieldConfig.required,
		},
	] );

	const fieldClasses = classNames(
		props.htmlAttr.class,
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

export default PodsDFVFieldContainer;
