import React from 'react';
import * as PropTypes from 'prop-types';
import sanitizeHtml from 'sanitize-html';

import { removep } from '@wordpress/autop';
import { __, sprintf } from '@wordpress/i18n';

import FieldContainer from 'dfv/src/components/field-container';
import HelpTooltip from 'dfv/src/components/help-tooltip';
import { richTextNoLinks } from '../../../blocks/src/config/html';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const PodsFieldOption = ( props ) => {
	const {
		field = {},
		value,
		setValue,
	} = props;

	const {
		description,
		helpText,
		label,
		required,
		type: fieldType,
	} = field;

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
				<div className={ `pods-form-ui-label pods-form-ui-label-${ name }` }>
					<label
						className="pods-form-ui-label__label"
						htmlFor={ name }
					>
						<span
							dangerouslySetInnerHTML={ {
								__html: removep( sanitizeHtml( label, richTextNoLinks ) ),
							} }
						/>
						{ required && ( <span className="pods-form-ui-label__required">{ '\u00A0' /* &nbsp; */ }*</span> ) }
					</label>

					{ shouldShowHelpText && (
						<span style={ { whiteSpace: 'nowrap' } }>
							{ '\u00A0' /* &nbsp; */ }
							<HelpTooltip
								helpText={ helpTextString }
								helpLink={ helpLink }
							/>
						</span>
					) }
				</div>
			) }

			<div className="pods-field-option__field">
				{ ( () => {
					// Show an error if the field doesn't exist in the config map.
					if ( ! Object.keys( FIELD_MAP ).includes( fieldType ) ) {
						return (
							<span className="pods-field-option__invalid-field">
								{ sprintf(
									// translators: Message showing that the field type doesn't exist.
									__( 'The field type \'%s\' was invalid.', 'pods' ),
									fieldType
								) }
							</span>
						);
					}

					// Show an error if the field isn't in the React format yet.
					// @todo this can probably be removed later.
					if ( FIELD_MAP[ fieldType ]?.renderer?.name !== 'reactRenderer' ) {
						return (
							<span className="pods-field-option__invalid-field">
								{ sprintf(
									// translators: Message showing that the field type doesn't exist.
									__( 'The field \'%s\' does not have a React renderer yet.', 'pods' ),
									fieldType
								) }
							</span>
						);
					}

					const fieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;

					return (
						<FieldContainer
							fieldComponent={ fieldComponent }
							fieldConfig={ field }
							value={ value }
							setValue={ setValue }
						/>
					);
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
	field: FIELD_PROP_TYPE_SHAPE,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	setValue: PropTypes.func.isRequired,
};

export default PodsFieldOption;
