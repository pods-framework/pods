/**
 * WordPress dependencies
 */
import { PanelRow } from '@wordpress/components';

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
	setAttributes,
} ) => {
	if ( ! fields.length ) {
		return null;
	}

	return (
		<div className="pods-inspector-rows">
			{ fields.map( ( field ) => {
				const {
					name,
				} = field;

				return (
					<PanelRow key={ name } className="pods-inspector-row">
						<RenderedField
							field={ field }
							attributes={ attributes }
							setAttributes={ setAttributes }
						/>
					</PanelRow>
				);
			} ) }
		</div>
	);
};

export default FieldInspectorControls;
