/**
 * Internal dependencies
 */
import createBlockCollection from './block-collections';
import createBlock from './blocks';
import createCommand from './commands';
import disablePanel from './panels';

import './editor.scss';

// Register block collections from the config.
window.podsBlocksConfig.collections.forEach( createBlockCollection );

// Register blocks from the config.
window.podsBlocksConfig.blocks.forEach( createBlock );

// Register commands from the config.
window.podsBlocksConfig.commands.forEach( createCommand );

// Disable certain panels in the block editor from the config.
window.podsBlocksConfig.panelsToDisable.forEach( disablePanel );
