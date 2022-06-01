import React, { useState } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';

import {
	GROUP_PROP_TYPE_SHAPE,
	FIELD_COMPONENT_BASE_PROPS,
} from 'dfv/src/config/prop-types';

const ConditionalLogic = ( props ) => {
	const {
		currentPodGroups,
	} = props;

	// @todo save this to the actual field data as JSON
	const [conditions, setConditions] = useState( {
		action: 'show',
		logic: 'any',
		rules: [
			{
				field: "",
				compare: "=",
				value: "",
			},
		],
	} );

	const updateAction = ( action ) => setConditions(
		( oldConditions ) => ( {
			...oldConditions,
			action,
		} )
	);

	const updateLogic = ( logic ) => setConditions(
		( oldConditions ) => ( {
			...oldConditions,
			logic,
		} )
	);

	const deleteRule = ( index ) => setConditions(
		( oldConditions ) => ( {
			...oldConditions,
			rules: [
				...( oldConditions.rules || [] ).slice( 0, index ),
				...( oldConditions.rules || [] ).slice( index + 1 ),
			],
		} )
	);

	const addRule = ( index ) => setConditions(
		( oldConditions ) => ( {
			...oldConditions,
			rules: [
				...( oldConditions.rules || [] ).slice( 0, index ),
				{
					field: "",
					compare: "=",
					value: "",
				},
				...( oldConditions.rules || [] ).slice( index ),
			],
		} )
	);

	const setRuleOption = ( index, option, value ) => setConditions(
		( oldConditions ) => ( {
			...oldConditions,
			rules: [
				...( oldConditions.rules || [] ).slice( 0, index ),
				{
					...oldConditions.rules[ index ],
					[option]: value,
				},
				...( oldConditions.rules || [] ).slice( index + 1 ),
			],
		} )
	);

	return (
		<>
			<div>
				<select
					value={ conditions.action }
					onChange={ ( event ) => updateAction( event.target.value ) }
				>
					<option value="show">{ __( 'Show', 'pods' ) }</option>
					<option value="hide">{ __( 'Hide', 'pods' ) }</option>
				</select>

				{__( ' this field if ' )}

				<select
					value={ conditions.logic }
					onChange={ ( event ) => updateLogic( event.target.value ) }
				>
					<option value="any">{ __( 'Any', 'pods' ) }</option>
					<option value="all">{ __( 'All', 'pods' ) }</option>
				</select>

				{__( ' of the following match ' )}
			</div>

			{ conditions.rules.map( ( rule, index ) => (
				<div
					className="pods-conditional-logic-rule"
					key={ `rule-${ index }` }
				>
					<select
						className="pods-conditional-logic-rule__field"
						value={ rule.field }
						onChange={ ( event ) => setRuleOption( index, 'field', event.target.value ) }
					>
						{ currentPodGroups.map( ( group ) => (
							<optgroup
								label={ group.label }
								key={ group.name }
							>
								{ ( group.fields || []).map( ( field ) => (
									<option
										value={ field.name }
										key={ field.name }
									>
										{ field.label }
									</option>
								) ) }
							</optgroup>
						) ) }
					</select>

					<select
						className="pods-conditional-logic-rule__compare"
						value={ rule.compare }
						onChange={ ( event ) => setRuleOption( index, 'compare', event.target.value ) }
					>
						{ /* @todo limit options based on field type */ }
						<option value="=">{ __( 'is', 'pods' ) }</option>
						<option value="!=">{ __( 'is not', 'pods' ) }</option>
						<option value=">">{ __( 'greater than', 'pods' ) }</option>
						<option value=">=">{ __( 'greater than or equal to', 'pods' ) }</option>
						<option value="<">{ __( 'lesser than', 'pods' ) }</option>
						<option value="<=">{ __( 'lesser than or equal to', 'pods' ) }</option>
						<option value="like">{ __( 'contains', 'pods' ) }</option>
						<option value="begins">{ __( 'starts with', 'pods' ) }</option>
						<option value="ends">{ __( 'ends with', 'pods' ) }</option>
						<option value="matches">{ __( 'matches pattern', 'pods' ) }</option>
					</select>

					{ /* @todo make this either a select with available options or a text field? */ }
					<input
						type="text"
						className="pods-conditional-logic-rule__value"
						// @todo real styles
						style={ { maxWidth: '200px' } }
						value={ rule.value }
						onChange={ ( event ) => setRuleOption( index, 'value', event.target.value ) }
					/>

					<button onClick={ () => addRule( index ) }>
						{ __( '+', 'pods' ) }
					</button>

					{ /* @todo add confirmation? */ }
					{ conditions.rules.length > 1 ? (
						<button onClick={ () => deleteRule( index ) }>
							{ __( '-', 'pods' ) }
						</button>
					) : null }
				</div>
			) ) }
		</>
	);
};

ConditionalLogic.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,

	/**
	 * Redux store key.
	 */
	 storeKey: PropTypes.string.isRequired,

	/**
	 * @todo how should the value be stored?
	 */
	value: PropTypes.string,

	/**
	 * Full array of Pod groups (and fields).
	 */
	currentPodGroups: PropTypes.arrayOf( GROUP_PROP_TYPE_SHAPE ).isRequired,
};

// Unlike most Fields, this one needs to be connected to the Redux store -
// it'll only be used on the Edit Pod screen.
const ConnectedConditionalLogic = withSelect(
	( select, ownProps ) => {
		const {
			field = {},
			storeKey,
		} = ownProps;

		const storeSelect = select( storeKey );

		return {
			currentPodGroups: storeSelect.getGroups(),
		};
	}
)( ConditionalLogic );

export default ConnectedConditionalLogic
