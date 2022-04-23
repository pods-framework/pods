/**
 * WordPress dependencies
 */
 import { select } from '@wordpress/data';

/**
 * Checks if the block editor is active.
 *
 * @returns bool True if using the block editor.
 */
const isGutenbergEditorLoaded = () => {
    return ( select( 'core/editor' ) !== undefined );
};

export default isGutenbergEditorLoaded;
