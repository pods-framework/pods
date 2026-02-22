import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';
import { Button } from '@wordpress/components';

import {
	FIELD_PROP_TYPE_SHAPE,
	GROUP_PROP_TYPE_SHAPE,
	FIELD_COMPONENT_BASE_PROPS,
	OBJECT_OR_JSON_STRING,
} from 'dfv/src/config/prop-types';

import './conditional-logic.scss';

const UNSUPPORTED_FIELD_TYPES = [
	'boolean_group',
	'conditional-logic',
	'wysiwyg',
	'heading',
	'html',
];

const NUMERIC_FIELD_TYPES = [
	'currency',
	'number',
];

const RELATIONAL_FIELD_TYPES = [
	'file',
	'avatar',
	'pick',
];

const FIELD_TYPES_WITH_NO_BLANK_COMPARISONS = [
	'boolean',
];

const FIELD_TYPES_WITH_ONLY_EQUALITY_COMPARISONS = [
	'file',
	'avatar',
	'pick',
	'boolean',
	'color',
];

const COMPARE_TYPES_WITH_NO_VALUE = [
	'empty',
	'not-empty',
];

const ConditionalLogic = ( {
	currentPodGroups,
	currentPodAllFields,
	fieldConfig,
	value,
	setValue,
} ) => {
	const {
		conditional_logic_affected_field_name: affectedFieldName,
	} = fieldConfig;

	const allAvailableFields = currentPodAllFields.filter( ( field ) => {
		return (
			! UNSUPPORTED_FIELD_TYPES.includes( field.type )
			&& field.name !== affectedFieldName
			&& (
				'undefined' === typeof field?.repeatable
				|| 0 === parseInt( field.repeatable )
			)
		);
	} );

	const [ conditions, setConditions ] = useState( {
		action: 'show',
		logic: 'any',
		rules: [
			{
				field: '',
				compare: '=',
				value: '',
			},
		],
	} );

	// When the component loads, set our "conditions" state with
	// the parsed value (which should be a JSON string).
	useEffect( () => {
		if ( ! value || '' === value ) {
			return;
		}

		// The value is already an object.
		if ( 'object' === typeof value ) {
			setConditions( value );

			return;
		}

		// Try to parse the string as JSON.
		try {
			const parsedValue = JSON.parse( value );

			setConditions( parsedValue );
		} catch ( e ) {
			// eslint-disable-next-line no-console
			console.warn( 'Error parsing Conditional Logic JSON: ', e );
		}
	}, [] );

	// Stringify the value whenever the conditions change.
	useEffect( () => {
		setValue( JSON.stringify( conditions ) );
	}, [ conditions ] );

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
				...( oldConditions.rules || [] ).slice( 0, index + 1 ),
				{
					field: '',
					compare: '=',
					value: '',
				},
				...( oldConditions.rules || [] ).slice( index + 1 ),
			],
		} )
	);

	const setRuleOption = function( index, option, ruleValue ) {
		return setConditions(
			( oldConditions ) => ( {
				...oldConditions,
				rules: [
					...( oldConditions.rules || [] ).slice( 0, index ),
					{
						...oldConditions.rules[ index ],
						[ option ]: ruleValue,
					},
					...( oldConditions.rules || [] ).slice( index + 1 ),
				],
			} )
		);
	};

	return (
		<>
			<div className="pods-conditional-logic-options">
				<select
					className="pods-conditional-logic-rule__action"
					value={ conditions.action }
					onChange={ ( event ) => updateAction( event.target.value ) }
				>
					<option value="show">{ __( 'Show', 'pods' ) }</option>
					<option value="hide">{ __( 'Hide', 'pods' ) }</option>
				</select>

				{ __( ' this field if ' ) }

				<select
					className="pods-conditional-logic-rule__logic"
					value={ conditions.logic }
					onChange={ ( event ) => updateLogic( event.target.value ) }
				>
					<option value="any">{ __( 'any', 'pods' ) }</option>
					<option value="all">{ __( 'all', 'pods' ) }</option>
				</select>

				{ __( ' of the following match:' ) }
			</div>

			{ conditions.rules.map( ( rule, index ) => {
				let ruleFieldObject = allAvailableFields.find(
					( field ) => field.name === rule.field,
				);

				// Default the field object / rule to the first available field in the list if no field is selected.
				if ( ! ruleFieldObject ) {
					ruleFieldObject = allAvailableFields[ 0 ];

					rule.field = ruleFieldObject.name;
				}

				const ruleFieldType = ruleFieldObject?.type;

				const isNumericFieldType = NUMERIC_FIELD_TYPES.includes( ruleFieldType );
				const isRelationalFieldType = RELATIONAL_FIELD_TYPES.includes( ruleFieldType );

				return (
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
									{ ( group.fields || [] ).map( ( field ) => {
										// Don't render an option for the field that we're editing,
										// to avoid any circular weirdness.
										if ( field.name === affectedFieldName ) {
											return null;
										}

										// Don't render an option if it's an unsupported field type.
										if ( UNSUPPORTED_FIELD_TYPES.includes( field.type ) ) {
											return null;
										}

										// Don't render an option if it's repeatable.
										if ( 'undefined' !== typeof field?.repeatable && 1 === parseInt( field.repeatable ) ) {
											return null;
										}

										return (
											<option
												value={ field.name }
												key={ field.name }
												data-field-type={ field.type }
											>
												{ field.label }
											</option>
										);
									} ) }
								</optgroup>
							) ) }
						</select>

						<select
							className="pods-conditional-logic-rule__compare"
							value={ rule.compare }
							onChange={ ( event ) => setRuleOption( index, 'compare', event.target.value ) }
						>
							<option value="=">{ isRelationalFieldType ? __( 'is (id/value)', 'pods' ) : __( 'is', 'pods' ) }</option>
							<option value="!=">{ isRelationalFieldType ? __( 'is not (id/value)', 'pods' ) : __( 'is not', 'pods' ) }</option>

							{ isRelationalFieldType ? (
								<>
									<option value="in">{ __( 'contains any (id/value, comma-separated)', 'pods' ) }</option>
									<option value="not-in">{ __( 'does not contain any (id/value, comma-separated)', 'pods' ) }</option>
									<option value="all">{ __( 'contains all (id/value, comma-separated)', 'pods' ) }</option>
									<option value="not-all">{ __( 'does not contain all (id/value, comma-separated)', 'pods' ) }</option>
								</>
							) : null }

							{ ! FIELD_TYPES_WITH_NO_BLANK_COMPARISONS.includes( ruleFieldType ) ? (
								<>
									<option value="empty">{ __( 'is blank', 'pods' ) }</option>
									<option value="not-empty">{ __( 'is not blank', 'pods' ) }</option>
								</>
							) : null }

							{ isNumericFieldType ? (
								<>
									<option value=">">{ __( 'greater than', 'pods' ) }</option>
									<option value=">=">{ __( 'greater than or equal', 'pods' ) }</option>
									<option value="<">{ __( 'less than', 'pods' ) }</option>
									<option value="<=">{ __( 'less than or equal', 'pods' ) }</option>
								</>
							) : null }

							{ ! isNumericFieldType && ! FIELD_TYPES_WITH_ONLY_EQUALITY_COMPARISONS.includes( ruleFieldType ) ? (
								<>
									<option value="like">{ __( 'contains', 'pods' ) }</option>
									<option value="not-like">{ __( 'does not contain', 'pods' ) }</option>
									<option value="begins">{ __( 'starts with', 'pods' ) }</option>
									<option value="not-begins">{ __( 'does not start with', 'pods' ) }</option>
									<option value="ends">{ __( 'ends with', 'pods' ) }</option>
									<option value="not-ends">{ __( 'does not end with', 'pods' ) }</option>
									<option value="matches">{ __( 'matches pattern', 'pods' ) }</option>
									<option value="not-matches">{ __( 'does not match pattern', 'pods' ) }</option>
								</>
							) : null }

						</select>

						{ ! COMPARE_TYPES_WITH_NO_VALUE.includes( rule.compare ) ? (
							<input
								type={ isNumericFieldType ? 'number' : 'text' }
								className="pods-conditional-logic-rule__value"
								value={ rule.value }
								onChange={ ( event ) => setRuleOption( index, 'value', event.target.value ) }
							/>
						) : null }

						<Button
							onClick={ () => addRule( index ) }
							isSecondary
							className="pods-conditional-logic-rule__add"
							aria-label={ __( 'Add new conditional logic rule', 'pods' ) }
						>
							{ __( '+', 'pods' ) }
						</Button>

						{ conditions.rules.length > 1 ? (
							<Button
								className="pods-conditional-logic-rule__remove"
								isSecondary
								onClick={ () => {
									// eslint-disable-next-line no-alert
									const result = confirm(
										__( 'Are you sure you want to delete this rule?', 'pods' ),
									);

									if ( result ) {
										deleteRule( index );
									}
								} }
								aria-label={ __( 'Remove conditional logic rule', 'pods' ) }
							>
								{ __( '-', 'pods' ) }
							</Button>
						) : null }
					</div>
				);
			} ) }
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
	 * Value stored as a object or JSON string.
	 */
	value: OBJECT_OR_JSON_STRING,

	/**
	 * Full array of Pod groups (and fields).
	 */
	currentPodGroups: PropTypes.arrayOf( GROUP_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * Array of all Pod fields (so that we can search without having to dig through Groups).
	 */
	currentPodAllFields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
};

// Unlike most Fields, this one needs to be connected to the Redux store -
// it'll only be used on the Edit Pod screen.
const ConnectedConditionalLogic = withSelect(
	( select, ownProps ) => {
		const {
			storeKey,
		} = ownProps;

		const storeSelect = select( storeKey );

		return {
			currentPodGroups: storeSelect.getGroups(),
			currentPodAllFields: storeSelect.getFieldsFromAllGroups(),
		};
	}
)( ConditionalLogic );

export default ConnectedConditionalLogic;
