/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import FieldInspectorControls from './components/FieldInspectorControls';
import BlockPreview from './components/BlockPreview';

/**
 * Creates the 'edit' component for a given block specification.
 *
 * @param {Object} block   Block specification (TBD).
 */
const createBlockEditComponent = ( block ) => ( props ) => {
	const {
		fields = [],
		blockName,
		blockGroupLabel,
	} = block;

	const {
		className,
		attributes = {},
		setAttributes,
		context = {},
	} = props;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ blockGroupLabel }
					key={ blockName }
				>
					<FieldInspectorControls
						fields={ fields }
						attributes={ attributes }
						setAttributes={ setAttributes }
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<BlockPreview
					block={ block }
					attributes={ attributes }
					context={ context }
				/>
			</div>
		</>
	);
};

export default createBlockEditComponent;
