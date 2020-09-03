import React, { useState, useEffect } from 'react';
import Engine from 'json-rules-engine-simplified';

export const podsValidation = () => {
	const [ validationMessages, setValidationMessages ] = useState( [] );
	const rules = [];
	let facts = {};

	const addRules = ( conditionalRules ) => {
		conditionalRules.forEach( ( conditionalRule ) => {
			if ( conditionalRule.condition ) {
				rules.push( conditionalRule.rule );
				if ( conditionalRule.rule.facts ) {
					facts = Object.assign( facts, conditionalRule.rule.facts );
				}
			}
		} );
	};

	const checkValidation = () => {
		const rulesEngine = new Engine( rules );
		const messages = [];

		return new Promise( ( resolve ) => {
			// noinspection JSUnresolvedFunction
			rulesEngine.run( facts )
				.then(
					( events ) => {
						events.forEach( event => {
							messages.push( event.message );
						} );
					}
				)
				.finally( () => {
					resolve( messages );
				} );
		} );
	};

	const useValidation = ( value ) => {
		useEffect( () => {
			checkValidation()
				.then( ( messages ) => setValidationMessages( messages ) );
		}, [ value ] );

		return validationMessages;
	};

	return {
		addRules,
		useValidation,
	};
};
