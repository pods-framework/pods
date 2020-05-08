import React from 'react';
const useState = React.useState;
const useEffect = React.useEffect;
import Engine from 'json-rules-engine-simplified';

/**
 *
 * @return
 * {
 *     {function} addRules
 *     {function} useValidation,
 * 	}
 */
export const podsValidation = () => {
	const [ validationMessages, setValidationMessages ] = useState( [] );
	const rules = [];
	let facts = {};

	/**
	 *
	 * @param conditionalRules
	 */
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

	/**
	 *
	 * @return {Promise<any>}
	 */
	const checkValidation = () => {
		const rulesEngine = new Engine( rules );
		const messages = [];

		return new Promise( ( resolve ) => {
			// noinspection JSUnresolvedFunction
			rulesEngine
				.run( facts )
				.then( ( events ) => {
					events.forEach( ( event ) => {
						messages.push( event.message );
					} );
				} )
				.finally( () => {
					resolve( messages );
				} );
		} );
	};

	/**
	 *
	 * @param {string} value The field's value
	 *
	 * @return {string} Array of messages for all validation failures
	 */
	const useValidation = ( value ) => {
		useEffect( () => {
			checkValidation().then( ( messages ) =>
				setValidationMessages( messages )
			);
		}, [ value ] );

		return validationMessages;
	};

	return {
		addRules,
		useValidation,
	};
};
