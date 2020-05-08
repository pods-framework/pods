/**
 * Internal dependencies
 */
import createBlock from './blocks';

// Register blocks from the config.
window.podsBlocksConfig.blocks.forEach( createBlock );
