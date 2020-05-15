/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import createBlockEditComponent from './createBlockEditComponent';
import createAttributesFromFields from './createAttributesFromFields';

/**
 * Registers a block from the provided data.
 */
const createBlock = ( block ) => {
	const {
		blockName,
		fields,
		category,
		description,
		icon,
		keywords,
		supports,
		title,
	} = block;

	const EditComponent = createBlockEditComponent( block );

	registerBlockType( blockName, {
		attributes: createAttributesFromFields( fields ),
		category,
		description,
		edit: EditComponent,
		icon,
		keywords,
		save: () => null,
		supports,
		title,
	} );
};

export default createBlock;
