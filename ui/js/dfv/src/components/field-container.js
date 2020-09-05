import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';

import ValidationMessages from 'dfv/src/components/validation-messages';
import { requiredValidator } from 'dfv/src/helpers/validators';
import toBool from 'dfv/src/helpers/toBool';

// Set up validation rules. The only one set up by default here
// is to validate a required field, but the field child component
// may set additional rules.
const useValidation = ( defaultRules = [], value ) => {
	const [ validationRules, setValidationRules ] = useState( defaultRules );
	const [ validationMessages, setValidationMessages ] = useState( [] );

	useEffect( () => {
		const newMessages = [];

		validationRules.forEach( ( rule ) => {
			if ( ! rule.condition() ) {
				return;
			}

			try {
				rule.rule( value );
			} catch ( error ) {
				if ( typeof error === 'string' ) {
					newMessages.push( error );
				}
			}
		} );

		setValidationMessages( newMessages );
	}, [ value ] );

	const addValidationRules = ( rules = [] ) => {
		rules.forEach( ( rule ) => {
			setValidationRules( ( previousValidationRules ) => {
				return [
					...previousValidationRules,
					rule,
				];
			} );
		} );
	};

	return [
		validationMessages,
		addValidationRules,
	];
};

const FieldContainer = ( props ) => {
	const {
		fieldComponent: Field,
		fieldConfig,
		fieldItemData,
		htmlAttr = {},
	} = props;

	const [ value, setValue ] = useState( fieldItemData[ 0 ] || '' );

	const [ validationMessages, addValidationRules ] = useValidation(
		[
			{
				rule: requiredValidator( fieldConfig.label ),
				condition: () => true === toBool( fieldConfig.required ),
			},
		],
		value
	);

	return (
		<div className="pods-dfv-container">
			<Field
				value={ value }
				setValue={ ( newValue ) => setValue( newValue ) }
				isValid={ !! validationMessages.length }
				addValidationRules={ addValidationRules }
				htmlAttr={ htmlAttr }
				{ ...props }
			/>

			{ !! validationMessages.length && (
				<ValidationMessages
					messages={ validationMessages }
				/>
			) }
		</div>
	);
};

FieldContainer.defaultProps = {
	fieldItemData: [],
};

FieldContainer.propTypes = {
	fieldComponent: PropTypes.func.isRequired,
	// @todo specify shape
	fieldConfig: PropTypes.object,
	// @todo specify types of items in array
	fieldItemData: PropTypes.array,
};

export default FieldContainer;
