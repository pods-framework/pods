import React from 'react';
import Engine from 'json-rules-engine-simplified';

export const podsValidation = () => {
	const rules = [];
	let params = {};

	return {
		addRules: ( conditionalRules ) => {

			conditionalRules.forEach( conditionalRule => {
				if ( conditionalRule.condition ) {

					rules.push( conditionalRule.rule );
					if ( conditionalRule.rule.params ) {
						params = Object.assign( params, conditionalRule.rule.params )
					}
				}
			} );
		},

		check: () => {
			const rulesEngine = new Engine( rules );
			const messages = [];

			return new Promise( ( resolve, reject ) => {
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
		}
	};
};
