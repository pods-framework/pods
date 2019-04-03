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
 * Effect to initialize all validation rules at render
 *
 * The returned function will run the validation rules and return an array of
 * messages or an empty array if there were no validation issues.
 *
 * @param {boolean} required
 * @return {function({string} value): Promise<array>}
 *
 */
export const useValidation = ( required ) => {
	const rules = [];

	useEffect( () => {
		if ( required ) {
			rules.push( requiredRule);
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

