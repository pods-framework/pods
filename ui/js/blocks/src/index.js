/**
 * Internal dependencies
 */
import createBlockCollection from './block-collections';
import createBlock from './blocks';

// Register block collections from the config.
window.podsBlocksConfig.collections.forEach( createBlockCollection );

// Register blocks from the config.
window.podsBlocksConfig.blocks.forEach( createBlock );
