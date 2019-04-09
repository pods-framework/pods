import React from 'react';
const useState = React.useState;
const useEffect = React.useEffect;
import Engine from 'json-rules-engine-simplified';

/**
 *
 * @return
 * {
 * 	{useValidation: useValidation,
 * 	addRules: addRules,
 * 	check: (function(): Promise<any>)}
 * 	}
 */
export const podsValidation = () => {
	const [ validationMessages, setValidationMessages ] = useState( [] );
	const rules = [];
	let params = {};

	/**
	 *
	 * @param conditionalRules
	 */
	const addRules = ( conditionalRules ) => {
		conditionalRules.forEach( conditionalRule => {
			if ( conditionalRule.condition ) {
				rules.push( conditionalRule.rule );
				if ( conditionalRule.rule.params ) {
					params = Object.assign( params, conditionalRule.rule.params );
				}
			}
		} );
	};

	/**
	 *
	 * @return {Promise<any>}
	 */
	const check = () => {
		const rulesEngine = new Engine( rules );
		const messages = [];

		return new Promise( ( resolve ) => {
			// noinspection JSUnresolvedFunction
			rulesEngine.run( params )
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

	/**
	 *
	 * @param value
	 */
	const useValidation = ( value ) => {
		useEffect( () => {
			check()
			.then( messages => setValidationMessages( messages ) );
		}, [ value ] );

		return validationMessages;
	};

	return {
		addRules: addRules,
		useValidation: useValidation
	};
};
