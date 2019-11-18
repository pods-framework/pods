/**
 * @function getHiddenHeight
 * @desc gets the height of hidden objects.
 */

const getHiddenHeight = ( el ) => {
	const width = el.clientWidth;
	const element = el;

	element.style.visibility = 'hidden';
	element.style.height = 'auto';
	element.style.maxHeight = 'none';
	element.style.position = 'fixed';
	element.style.width = `${width}px`;

	const tHeight = element.offsetHeight;

	element.style.visibility = '';
	element.style.height = '';
	element.style.maxHeight = '';
	element.style.width = '';
	element.style.position = '';
	element.style.zIndex = '';

	return tHeight;
};

export default getHiddenHeight;
