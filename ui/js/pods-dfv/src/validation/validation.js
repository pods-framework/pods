import React from 'react';
import Engine from 'json-rules-engine-simplified';

/**
 * Initialize validation rules
 *
 * The returned function will run the validation rules and return an array of
 * messages or an empty array if there were no validation issues.
 *
 * @param {object[]} conditionalRules
 * @return {function({string} value): Promise<array>}
 */
export const setValidationRules = ( conditionalRules ) => {
	const rules = [];

	conditionalRules.forEach( conditionalRule => {
		if ( conditionalRule.condition ) {
			rules.push( conditionalRule.rule );
		}
	} );

	return (value) => {
		const rulesEngine = new Engine( rules );
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
};
