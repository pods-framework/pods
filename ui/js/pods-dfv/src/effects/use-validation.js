import React from 'react';
import Engine from 'json-rules-engine-simplified';
const useEffect = React.useEffect;

const requiredRule = {
	conditions: { value: { equal: '' } },
	event: {
		message: 'This field is required.'
	}
};

/**
 * One-shot effect to initialize the rules engine and rules at render
 *
 * @param {boolean} required
 */
export const useValidation = ( required ) => {
	const rulesEngine = new Engine();

	useEffect( () => {
		if ( required ) {
			rulesEngine.addRule( requiredRule);
		}
	});

	return rulesEngine;
};

/**
 * Run the validation rules and return an array of messages or an empty
 * array if there were no validation issues.
 *
 * @param {string} value
 * @return {Promise<array>}
 */
export const getValidationMessages = ( rulesEngine, value ) => {
	const messages = [];

	return new Promise( (resolve, reject) => {
		rulesEngine.run( { value } )
		.then(
			events => {
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
