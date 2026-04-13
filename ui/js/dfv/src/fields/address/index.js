import React from 'react';
import PropTypes from 'prop-types';

import BaseInput from 'dfv/src/fields/base-input';
import { FIELD_COMPONENT_BASE_PROPS } from 'dfv/src/config/prop-types';
import { toBool } from 'dfv/src/helpers/booleans';

const normalizeValue = ( value, addressType ) => {
	if ( value && 'object' === typeof value && ! Array.isArray( value ) ) {
		if ( Object.prototype.hasOwnProperty.call( value, 'address' ) || Object.prototype.hasOwnProperty.call( value, 'text' ) ) {
			return {
				address: value.address || {},
				text: value.text || '',
			};
		}

		return {
			address: value,
			text: '',
		};
	}

	if ( 'string' === typeof value ) {
		if ( 'text' === addressType ) {
			return {
				address: {},
				text: value,
			};
		}

		return {
			address: {},
			text: '',
		};
	}

	return {
		address: {},
		text: '',
	};
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

	const onBlur = () => {
		setHasBlurred();
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

	if ( 'text' === addressTypeOption ) {
		return (
			<BaseInput
				fieldConfig={ makeSubFieldConfig( `${ baseName }[text]`, baseId ) }
				type="text"
				value={ normalizedValue.text || '' }
				onChange={ ( event ) => setTextValue( event.target.value ) }
				onBlur={ onBlur }
				setValue={ setTextValue }
				setHasBlurred={ setHasBlurred }
			/>
		);
	}

	const regions = mapToOptions( fieldItemData.regions || {} );
	const countries = mapToOptions( fieldItemData.countries || {} );

	return (
		<>
			{ toBool( enableLine1 ) && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][line_1]`, baseId ) }
					type="text"
					value={ addressValue.line_1 || '' }
					onChange={ ( event ) => setAddressValue( 'line_1', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'line_1', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }

			{ toBool( enableLine2 ) && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][line_2]`, `${ baseId }-line-2` ) }
					type="text"
					value={ addressValue.line_2 || '' }
					onChange={ ( event ) => setAddressValue( 'line_2', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'line_2', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }

			{ toBool( enableCity ) && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][city]`, `${ baseId }-city` ) }
					type="text"
					value={ addressValue.city || '' }
					onChange={ ( event ) => setAddressValue( 'city', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'city', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }

			{ toBool( enablePostalCode ) && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][postal_code]`, `${ baseId }-postal-code` ) }
					type="text"
					value={ addressValue.postal_code || '' }
					onChange={ ( event ) => setAddressValue( 'postal_code', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'postal_code', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }

			{ toBool( enableRegion ) && 'pick' === regionInputType && (
				<select
					id={ `${ baseId }-region` }
					name={ `${ baseName }[address][region]` }
					className={ htmlAttributes.class || '' }
					value={ addressValue.region || '' }
					onChange={ ( event ) => {
						if ( isReadOnly ) {
							return;
						}

						setAddressValue( 'region', event.target.value );
					} }
					onBlur={ onBlur }
				>
					<option value=""></option>
					{ regions.map( ( option ) => (
						<option key={ option.value } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>
			) }

			{ toBool( enableRegion ) && 'pick' !== regionInputType && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][region]`, `${ baseId }-region` ) }
					type="text"
					value={ addressValue.region || '' }
					onChange={ ( event ) => setAddressValue( 'region', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'region', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }

			{ toBool( enableCountry ) && 'pick' === countryInputType && (
				<select
					id={ `${ baseId }-country` }
					name={ `${ baseName }[address][country]` }
					className={ htmlAttributes.class || '' }
					value={ addressValue.country || '' }
					onChange={ ( event ) => {
						if ( isReadOnly ) {
							return;
						}

						setAddressValue( 'country', event.target.value );
					} }
					onBlur={ onBlur }
				>
					<option value=""></option>
					{ countries.map( ( option ) => (
						<option key={ option.value } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>
			) }

			{ toBool( enableCountry ) && 'pick' !== countryInputType && (
				<BaseInput
					fieldConfig={ makeSubFieldConfig( `${ baseName }[address][country]`, `${ baseId }-country` ) }
					type="text"
					value={ addressValue.country || '' }
					onChange={ ( event ) => setAddressValue( 'country', event.target.value ) }
					onBlur={ onBlur }
					setValue={ ( nextValue ) => setAddressValue( 'country', nextValue ) }
					setHasBlurred={ setHasBlurred }
				/>
			) }
		</>
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