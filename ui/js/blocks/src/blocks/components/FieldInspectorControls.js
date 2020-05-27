/**
 * WordPress dependencies
 */
import {
	PanelBody,
	PanelRow,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import RenderedField from './RenderedField';

/**
 * Renders the fields that live in the Inspector on the sidebar.
 */
const FieldInspectorControls = ( {
	fields = [],
	attributes,
	setAttributes
} ) => {
	if ( ! fields.length ) {
		return null;
	}

	return (
		<>
			{ fields.map( ( field ) => {
				const {
					name,
					fieldOptions: {
						label,
						heading,
					},
				} = field;

				return (
					<PanelRow className="pods-inspector-row">
						<RenderedField
							field={ field}
							attributes={ attributes }
							setAttributes={setAttributes}
						/>
					</PanelRow>
				)
			} ) }
		</>
	)
};

export default FieldInspectorControls;
