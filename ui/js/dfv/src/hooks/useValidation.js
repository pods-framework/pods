import { useState, useEffect } from 'react';
import {
	useSelect,
	useDispatch,
} from '@wordpress/data';

const useValidation = ( defaultRules = [], value, fieldName, strokeKey ) => {
	const [ validationRules, setValidationRules ] = useState( defaultRules );

	const validationMessages = useSelect( ( select ) => {
		const currentMessages = select( strokeKey ).getValidationMessages();
		//has fieldName ?
		if ( currentMessages.hasOwnProperty( fieldName ) ) {
			return currentMessages[ fieldName ];
		}
		return [];
	}, [ fieldName ] );

	const { setValidationMessages } = useDispatch( strokeKey );

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
