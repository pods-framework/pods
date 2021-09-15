# Pods Block Types

All the block types are defined here for `block.json` configurations only. They are dynamically built through Pods in these places:

* `ui/js/blocks/src/blocks/index.js` - This is where the blocks get registered dynamically with WordPress via the JS `registerBlockType()`.
* `src/Pods/Blocks` - This is the place where all the Blocks-related functionality is located.
* Additional custom blocks can be registered through the Pods Blocks PHP API which will be dynamically generated but will not have their own `block.json` file.
