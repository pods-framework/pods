/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * Test if a node element has a class present on it
 *
 * @param {HTMLElement|Element} node The node where to look for the class names
 * @param {array} classNames List of class names as an array of strings
 * @returns {boolean} `true` if has any of the classes or false if does not have any
 */
export const hasClass = ( node, classNames = [] ) => {
	for ( let i = 0; i < classNames.length; i++ ) {
		if ( node.classList.contains( classNames[ i ] ) ) {
			return true;
		}
	}
	return false;
};

/**
 * Utility to search the parent of a node looking from the current node Up to the highest
 * node on the DOM Tree
 *
 * @param {(DOMElement|object)} node - The DOM node where the search starts
 * @param {function} callback - Is executed on every iteration, it should return a boolean
 * @returns {boolean} Returns tre if the callback returns true with any of the parents.
 */
export const searchParent = ( node = {}, callback = noop ) => {
	let found = false;
	let testNode = node;
	do {
		if ( testNode ) {
			found = callback( testNode );
		}
		const nextNode = testNode && testNode.parentNode ? testNode.parentNode : null;
		testNode = isRootNode( nextNode ) ? null : nextNode;
	} while ( ! found && testNode !== null );

	return found;
};

/**
 * Test if a node is the same as the root element or the base node of the document.
 *
 * @param {Element} node A Document Node
 * @returns {boolean} true if node is the root Node Document
 */
export const isRootNode = ( node ) => node === window.top.document;
