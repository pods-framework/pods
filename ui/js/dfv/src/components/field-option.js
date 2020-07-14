import React from 'react';
import * as PropTypes from 'prop-types';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';

import HelpTooltip from 'dfv/src/components/help-tooltip';
import { richTextNoLinks } from '../../../blocks/src/config/html';

const PodsFieldOption = ( {
	fieldType,
	name,
	value,
	label,
	data = {},
	onChange,
	helpText,
	description,
} ) => {
	const toBool = ( stringOrNumber ) => {
		// Force any strings to numeric first
		return !! ( +stringOrNumber );
	};

	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const helpTextString = Array.isArray( helpText ) ? helpText.join( '\n' ) : helpText;

	return (
		<div className="pods-field-option">
			<label
				className={ `pods-form-ui-label pods-form-ui-label-${ name }` }
				htmlFor={ name }>
				{ label }
				{ shouldShowHelpText && ( <HelpTooltip helpText={ helpTextString } /> ) }
			</label>

			<div className="pods-field-option__field">
				{ ( () => {
					switch ( fieldType ) {
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
								<select
									id={ name }
									name={ name }
									selected={ value }
									onBlur={ onChange }
								>
									{ Object.entries( data ).map( ( [ optionValue, option ] ) => {
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
						default: {
							return (
								<input
									type="text"
									id={ name }
									name={ name }
									value={ value }
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
	data: PropTypes.object,
	label: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
};

export default PodsFieldOption;
