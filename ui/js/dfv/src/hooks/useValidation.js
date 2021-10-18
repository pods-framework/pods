import { useState, useEffect } from 'react';

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

export default useValidation;
