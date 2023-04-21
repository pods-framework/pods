import { useState, useEffect } from 'react';
import {
	useSelect,
	useDispatch,
} from '@wordpress/data';

const useValidation = ( defaultRules = [], value, fieldName, strokeKey ) => {
	const [ validationRules, setValidationRules ] = useState( defaultRules );

	//Validation messages for this field
	const validationMessages = useSelect( ( select ) => {
		const currentMessages = select( strokeKey ).getValidationMessages();
		//Return for this field
		if ( currentMessages.hasOwnProperty( fieldName ) ) {
			return currentMessages[ fieldName ];
		}
		return [];
	}, [ fieldName ] );

	//Set validation messages for this field
	const { setValidationMessages } = useDispatch( strokeKey );
	const needsValidation = useSelect( ( select ) => {
		return select( strokeKey ).getNeedsValidating();
	}, [] );

	const toggleNeedsValidating = useDispatch( strokeKey ).toggleNeedsValidating();

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
		setValidationMessages( fieldName, newMessages );
		if ( needsValidation ) {
			toggleNeedsValidating();
		}
	}, [ value, validationRules, needsValidation, toggleNeedsValidating, setValidationMessages ] );

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
