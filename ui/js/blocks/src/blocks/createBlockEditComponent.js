/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import FieldInspectorControls from './components/FieldInspectorControls';
import BlockPreview from './components/BlockPreview';

/**
 * Creates the 'edit' component for a given block specification.
 *
 * @param {Object} block Block specification (TBD).
 */
const createBlockEditComponent = ( block ) => ( props ) => {
	const {
		fields = [],
		template,
	} = block;

	const {
		className,
		attributes = {},
		setAttributes,
	} = props;

	return (
		<div className={ className }>
			<InspectorControls>
				<FieldInspectorControls
					fields={ fields }
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
			</InspectorControls>
			<BlockPreview
				template={ template }
				fields={ fields }
				attributes={ attributes }
			/>
		</div>
	);
};

export default createBlockEditComponent;
