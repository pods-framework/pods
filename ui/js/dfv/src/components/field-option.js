import React from 'react';
import * as PropTypes from 'prop-types';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';

import HelpTooltip from 'dfv/src/components/help-tooltip';
import { richTextNoLinks } from '../../../blocks/src/config/html';

const toBool = ( stringOrNumber ) => {
	// Force any strings to numeric first
	return !! ( +stringOrNumber );
};

const PodsFieldOption = ( {
	fieldType,
	name,
	required,
	value,
	label,
	data = {},
	onChange,
	helpText,
	description,
} ) => {
	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const helpTextString = Array.isArray( helpText ) ? helpText[ 0 ] : helpText;
	const helpLink = ( Array.isArray( helpText ) && !! helpText[ 1 ] )
		? helpText[ 1 ]
		: undefined;

	return (
		<div className="pods-field-option">
			{ 'heading' !== fieldType && (
				<label
					className={ `pods-form-ui-label pods-form-ui-label-${ name }` }
					htmlFor={ name }>
					{ label }
					{ required && ( <span className="pods-form-ui-label__required">{ '\u00A0' /* &nbsp; */ }*</span> ) }
					{ shouldShowHelpText && (
						<span style={ { whiteSpace: 'nowrap' } }>
							{ '\u00A0' /* &nbsp; */ }
							<HelpTooltip
								helpText={ helpTextString }
								helpLink={ helpLink }
							/>
						</span>
					) }
				</label>
			) }

			<div className="pods-field-option__field">
				{ ( () => {
					switch ( fieldType ) {
						case 'heading': {
							return (
								<h3 className={ `pods-form-ui-heading pods-form-ui-heading-${ name }` }>
									{ label }
									{ shouldShowHelpText && (
										<HelpTooltip
											helpText={ helpTextString }
											helpLink={ helpLink }
										/> ) }
								</h3>
							);
						}
						case 'boolean': {
							return (
								<input
									type="checkbox"
									id={ name }
									name={ name }
									checked={ toBool( value ) }
									onChange={ onChange }
									aria-label={ shouldShowHelpText && helpText }
								/>
							);
						}
						case 'pick': {
							return (
								/* eslint-disable-next-line jsx-a11y/no-onchange */
								<select
									id={ name }
									name={ name }
									value={ value }
									onChange={ onChange }
								>
									{ Object.keys( data )
										// This custom sorting function is necessary because
										// JS will change the ordering of the keys in the object
										// if one of them is an empty string (which we may want as
										// the placeholder value), and will send the empty string
										// to the end of the object when it is enumerated.
										//
										// eslint-disable-next-line no-unused-vars
										.sort( ( a, b ) => a === '' ? -1 : 0 )
										.map( ( optionValue ) => {
											const option = data[ optionValue ];

											if ( 'string' === typeof option ) {
												return (
													<option key={ optionValue } value={ optionValue }>
														{ option }
													</option>
												);
											} else if ( 'object' === typeof option ) {
												const optgroupOptions = Object.entries( option );

												return (
													<optgroup label={ optionValue } key={ optionValue }>
														{ optgroupOptions.map( ( [ suboptionValue, suboptionLabel ] ) => {
															return (
																<option key={ suboptionValue } value={ suboptionValue }>
																	{ suboptionLabel }
																</option>
															);
														} ) }
													</optgroup>
												);
											}
											return null;
										} ) }
								</select>
							);
						}
						case 'paragraph':
							return (
								<textarea
									id={ name }
									name={ name }
									value={ value || '' }
									onChange={ onChange }
									aria-label={ shouldShowHelpText && helpText }
								/>
							);
						default: {
							return (
								<input
									type="text"
									id={ name }
									name={ name }
									value={ value || '' }
									onChange={ onChange }
									aria-label={ shouldShowHelpText && helpText }
								/>
							);
						}
					}
				} )() }

				{ !! description && (
					<p
						className="description"
						dangerouslySetInnerHTML={ {
							__html: removep( sanitizeHtml( description, richTextNoLinks ) ),
						} }
					/>
				) }
			</div>
		</div>
	);
};

PodsFieldOption.defaultProps = {
	value: '',
};

PodsFieldOption.propTypes = {
	description: PropTypes.string,
	fieldType: PropTypes.string.isRequired,
	helpText: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	data: PropTypes.oneOfType( [
		PropTypes.array,
		PropTypes.object,
	] ),
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	required: PropTypes.bool.isRequired,
	onChange: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
};

export default PodsFieldOption;
