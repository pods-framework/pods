import React from 'react';
import { PodsDFVValidationMessages } from 'pods-dfv/src/components/validation-messages';
import { useValidation, getValidationMessages } from 'pods-dfv/src/effects/use-validation';

const useState = React.useState;

export const PodsDFVFieldContainer = ( props ) => {
	const Field = props.fieldComponent;
	const [ value, setValue ] = useState( props.fieldItemData[ 0 ] || '' );
	const [ validationMessages, setValidationMessages ] = useState( [] );
	const rulesEngine = useValidation( props.fieldConfig.required === '1' );

	function handleFieldBlur () {
		getValidationMessages( rulesEngine, value )
		.then( messages => setValidationMessages( messages ) );
	}

	return (
		<div className="pods-dfv-container">
			<Field
				value={value}
				setValue={setValue}
				setValidationMessages={setValidationMessages}
				onBlur={handleFieldBlur}
				className={
					props.htmlAttr.class + ( validationMessages.length ? ' pods-validate pods-validate-error ' : '' )
				}
				{...props}
			/>
			<PodsDFVValidationMessages messages={validationMessages} />
		</div>
	);
};
