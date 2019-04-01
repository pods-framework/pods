import React from 'react';
import { PodsDFVValidationMessages } from 'pods-dfv/_src/core/react/pods-dfv-validation-message';
const useState = React.useState;

export const PodsDFVFieldContainer = ( props ) => {
	const Field = props.fieldComponent;
	const [ value, setValue ] = useState( props.fieldItemData[ 0 ] );
	const [ validationMessages, setValidationMessages ] = useState( [] );

	return (
		<div className="pods-dfv-container">
			<div>
				<Field
					value={value}
					setValue={setValue}
					setValidationMessages={setValidationMessages}
					{...props}
				/>
			</div>
			<PodsDFVValidationMessages messages={validationMessages} />
		</div>
	);
};
