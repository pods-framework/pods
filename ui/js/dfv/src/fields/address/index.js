import React from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';

import BaseInput from 'dfv/src/fields/base-input';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';
import { toBool } from 'dfv/src/helpers/booleans';

import './address.scss';

const normalizeValue = ( value, addressType ) => {
	const normalized = {
		address: {},
		text: '',
		lat: '',
		long: '',
	};

	if ( value && 'object' === typeof value && ! Array.isArray( value ) ) {
		const geoValue = value.geo && 'object' === typeof value.geo && ! Array.isArray( value.geo ) ? value.geo : {};
		const latValue = undefined !== value.lat ? value.lat : ( undefined !== geoValue.lat ? geoValue.lat : '' );
		const longValue = undefined !== value.long
			? value.long
			: ( undefined !== value.lng
				? value.lng
				: ( undefined !== geoValue.long ? geoValue.long : ( undefined !== geoValue.lng ? geoValue.lng : '' ) ) );

		if ( Object.prototype.hasOwnProperty.call( value, 'address' ) || Object.prototype.hasOwnProperty.call( value, 'text' ) ) {
			return {
				...normalized,
				address: value.address || {},
				text: value.text || '',
				lat: undefined !== value.lat ? value.lat : latValue,
				long: undefined !== value.long ? value.long : longValue,
			};
		}

		if ( 'lat-long' === addressType ) {
			return {
				...normalized,
				lat: latValue,
				long: longValue,
			};
		}

		return {
			...normalized,
			address: value,
		};
	}

	if ( 'string' === typeof value ) {
		if ( 'text' === addressType ) {
			return {
				...normalized,
				text: value,
			};
		}

		return normalized;
	}

	return normalized;
};

const mapToOptions = ( data = {} ) => {
	if ( ! data || 'object' !== typeof data ) {
		return [];
	}

	return Object.entries( data ).map( ( [ optionValue, optionLabel ] ) => ( {
		value: optionValue,
		label: optionLabel,
	} ) );
};

const ADDRESS_LABELS = {
	line_1: __( 'Address Line 1', 'pods' ),
	line_2: __( 'Address Line 2', 'pods' ),
	city: __( 'City', 'pods' ),
	postal_code: __( 'ZIP / Postal Code', 'pods' ),
	region: __( 'State / Province', 'pods' ),
	country: __( 'Country', 'pods' ),
};

const LAT_LONG_LABELS = {
	lat: __( 'Latitude', 'pods' ),
	long: __( 'Longitude', 'pods' ),
};

const Address = ( {
	fieldConfig = {},
	value,
	setValue,
	setHasBlurred,
} ) => {
	const {
		type = 'address',
		name,
		htmlAttr: htmlAttributes = {},
		fieldItemData = {},
		read_only: readOnly,
		address_type: addressTypeOption = 'address',
		address_address_line_1: enableLine1,
		address_address_line_2: enableLine2,
		address_address_postal_code: enablePostalCode,
		address_address_city: enableCity,
		address_address_region: enableRegion,
		address_address_country: enableCountry,
		address_address_region_input: regionInputType = 'text',
		address_address_country_input: countryInputType = 'text',
	} = fieldConfig;

	const baseName = htmlAttributes.name || name;
	const baseId = htmlAttributes.id || `pods-form-ui-${ name }`;
	const isReadOnly = toBool( readOnly );

	const normalizedValue = normalizeValue( value, addressTypeOption );
	const addressValue = normalizedValue.address || {};

	const setAddressValue = ( partName, partValue ) => {
		setValue( {
			...normalizedValue,
			address: {
				...addressValue,
				[ partName ]: partValue,
			},
		} );
	};

	const setTextValue = ( textValue ) => {
		setValue( {
			...normalizedValue,
			text: textValue,
		} );
	};

	const setLatLongValue = ( partName, partValue ) => {
		setValue( {
			...normalizedValue,
			[ partName ]: partValue,
		} );
	};

	const makeSubFieldConfig = ( inputName, inputId ) => ( {
		...fieldConfig,
		name: inputName,
		htmlAttr: {
			...htmlAttributes,
			name: inputName,
			id: inputId,
		},
		read_only: readOnly,
		type,
	} );

	const renderTextInput = ( partName, inputId ) => {
		const inputName = `${ baseName }[address][${ partName }]`;

		return (
			<div className="pods-address-field__row">
				<label className="pods-form-ui-label" htmlFor={ inputId }>
					{ ADDRESS_LABELS[ partName ] }
				</label>
				<BaseInput
					fieldConfig={ makeSubFieldConfig( inputName, inputId ) }
					type="text"
					value={ addressValue[ partName ] || '' }
					onChange={ ( event ) => setAddressValue( partName, event.target.value ) }
					setValue={ ( nextValue ) => setAddressValue( partName, nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			</div>
		);
	};

	const renderSelectInput = ( partName, inputId, options ) => {
		const inputName = `${ baseName }[address][${ partName }]`;

		return (
			<div className="pods-address-field__row">
				<label className="pods-form-ui-label" htmlFor={ inputId }>
					{ ADDRESS_LABELS[ partName ] }
				</label>
				<select
					id={ inputId }
					name={ inputName }
					className={ `pods-address-field__select ${ htmlAttributes.class || '' }`.trim() }
					value={ addressValue[ partName ] || '' }
					disabled={ isReadOnly }
					onChange={ ( event ) => {
						if ( isReadOnly ) {
							return;
						}

						setAddressValue( partName, event.target.value );
					} }
					onBlur={ () => setHasBlurred() }
				>
					<option value=""></option>
					{ options.map( ( option ) => (
						<option key={ option.value } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>
			</div>
		);
	};

	if ( 'text' === addressTypeOption ) {
		return (
			<BaseInput
				fieldConfig={ makeSubFieldConfig( `${ baseName }[text]`, baseId ) }
				type="text"
				value={ normalizedValue.text || '' }
				onChange={ ( event ) => setTextValue( event.target.value ) }
				setValue={ setTextValue }
				setHasBlurred={ setHasBlurred }
			/>
		);
	}

	if ( 'lat-long' === addressTypeOption ) {
		return (
			<div className="pods-address-field">
				<div className="pods-address-field__row">
					<label className="pods-form-ui-label" htmlFor={ baseId }>
						{ LAT_LONG_LABELS.lat }
					</label>
					<BaseInput
						fieldConfig={ makeSubFieldConfig( `${ baseName }[lat]`, baseId ) }
						type="number"
						value={ normalizedValue.lat || '' }
						onChange={ ( event ) => setLatLongValue( 'lat', event.target.value ) }
						setValue={ ( nextValue ) => setLatLongValue( 'lat', nextValue ) }
						setHasBlurred={ setHasBlurred }
					/>
				</div>

				<div className="pods-address-field__row">
					<label className="pods-form-ui-label" htmlFor={ `${ baseId }-long` }>
						{ LAT_LONG_LABELS.long }
					</label>
					<BaseInput
						fieldConfig={ makeSubFieldConfig( `${ baseName }[long]`, `${ baseId }-long` ) }
						type="number"
						value={ normalizedValue.long || '' }
						onChange={ ( event ) => setLatLongValue( 'long', event.target.value ) }
						setValue={ ( nextValue ) => setLatLongValue( 'long', nextValue ) }
						setHasBlurred={ setHasBlurred }
					/>
				</div>
			</div>
		);
	}

	const regions = mapToOptions( fieldItemData.regions || {} );
	const countries = mapToOptions( fieldItemData.countries || {} );

	return (
		<div className="pods-address-field">
			{ toBool( enableLine1 ) && (
				renderTextInput( 'line_1', baseId )
			) }

			{ toBool( enableLine2 ) && (
				renderTextInput( 'line_2', `${ baseId }-line-2` )
			) }

			{ toBool( enableCity ) && (
				renderTextInput( 'city', `${ baseId }-city` )
			) }

			{ toBool( enablePostalCode ) && (
				renderTextInput( 'postal_code', `${ baseId }-postal-code` )
			) }

			{ toBool( enableRegion ) && 'pick' === regionInputType && (
				renderSelectInput( 'region', `${ baseId }-region`, regions )
			) }

			{ toBool( enableRegion ) && 'pick' !== regionInputType && (
				renderTextInput( 'region', `${ baseId }-region` )
			) }

			{ toBool( enableCountry ) && 'pick' === countryInputType && (
				renderSelectInput( 'country', `${ baseId }-country`, countries )
			) }

			{ toBool( enableCountry ) && 'pick' !== countryInputType && (
				renderTextInput( 'country', `${ baseId }-country` )
			) }
		</div>
	);
};

Address.propTypes = {
	...FIELD_COMPONENT_BASE_PROPS,
	fieldConfig: PropTypes.object,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.object,
	] ),
};

export default Address;