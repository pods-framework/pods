import React from 'react';
import classNames from 'classnames';
import { PodsDFVValidationMessages } from 'pods-dfv/src/components/validation-messages';
import { validationRules } from 'pods-dfv/src/validation/validation-rules';
import { setValidationRules } from 'pods-dfv/src/validation/validation';
const useState = React.useState;

export const PodsDFVFieldContainer = ( props ) => {
	const Field = props.fieldComponent;
	const [ value, setValue ] = useState( props.fieldItemData[ 0 ] || '' );
	const [ validationMessages, setValidationMessages ] = useState( [] );

	const fieldClasses = classNames(
		props.htmlAttr.class,
		{ 'pods-validate-error': validationMessages.length }
	);

	const checkValidation = setValidationRules( [
		{
			condition: props.fieldConfig.required === '1',
			rule: validationRules.required
		},
	] );

	function handleFieldBlur () {
		checkValidation( value )
		.then( messages => setValidationMessages( messages ) );
	}

	return (
		<div className="pods-dfv-container">
			<Field
				value={value}
				setValue={setValue}
				setValidationMessages={setValidationMessages}
				onBlur={handleFieldBlur}
				className={fieldClasses}
				{...props}
			/>
			<PodsDFVValidationMessages messages={validationMessages} />
		</div>
	);
};
