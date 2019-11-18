/**
 * Element to slide gets the following CSS:
 *   max-height: 0;
 *   overflow: hidden;
 */
import BezierEasing from 'bezier-easing';
import getHiddenHeight from './get-hidden-height';

const ease = BezierEasing( 0.25, 0.1, 0.25, 1 );

const requestIds = {};

/**
 * Check that request id exists, if not create an entry
 * @param {string} id Unique ID of animation
 */
export const checkRequestIds = ( id ) => {
	if ( ! requestIds[ id ] ) {
		requestIds[ id ] = {
			up: null,
			down: null,
		};
	}

	return requestIds[ id ];
};

/**
 * Cancel animations with request id
 * @param {string} id Unique ID of animation
 */
const cancelAnimations = ( id ) => {
	if ( requestIds[ id ].up ) {
		window.cancelAnimationFrame( requestIds[ id ].up );
		requestIds[ id ].up = null;
	}
	if ( requestIds[ id ].down ) {
		window.cancelAnimationFrame( requestIds[ id ].down );
		requestIds[ id ].down = null;
	}
};

/**
 * Like jQuery's slideDown function
 * @param {Node} elem Element to show and hide
 * @param {string} id Unique ID of animation
 * @param {int} time Length of animation in ms
 * @param {function} callback Callback function
 */
export const down = ( elem, id, time = 400, callback = null ) => {
	const startHeight = elem.offsetHeight;
	const endHeight = getHiddenHeight( elem );
	let startTime = null;
	elem.style.maxHeight = '0';

	checkRequestIds( id );
	cancelAnimations( id );

	const step = ( timestamp ) => {
		if ( ! startTime ) {
			startTime = timestamp;
		}
		const timeDiff = timestamp - startTime;
		const progress = ease( timeDiff / time );
		const height = ( progress * ( endHeight - startHeight ) ) + startHeight;
		elem.style.maxHeight = `${height}px`;

		if ( timeDiff < time ) {
			requestIds[ id ].down = window.requestAnimationFrame( step );
		} else {
			requestIds[ id ].down = null;
			elem.style.maxHeight = 'none';
			if ( callback ) {
				callback();
			}
		}
	};

	requestIds[ id ].down = window.requestAnimationFrame( step );
};

/**
 * Slide element up
 * @param {Node} elem Element to show and hide
 * @param {string} id Unique ID of animation
 * @param {int} time Length of animation in ms
 * @param {function} callback Callback function
 */
export const up = ( elem, id, time = 400, callback = null ) => {
	const startHeight = elem.offsetHeight;
	const endHeight = 0;
	let startTime = null;
	elem.style.maxHeight = `${startHeight}px`;

	checkRequestIds( id );
	cancelAnimations( id );

	const step = ( timestamp ) => {
		if ( ! startTime ) {
			startTime = timestamp;
		}
		const timeDiff = timestamp - startTime;
		const progress = ease( timeDiff / time );
		const height = ( progress * ( endHeight - startHeight ) ) + startHeight;
		elem.style.maxHeight = `${height}px`;

		if ( timeDiff < time ) {
			requestIds[ id ].up = window.requestAnimationFrame( step );
		} else {
			requestIds[ id ].up = null;
			elem.style.maxHeight = '0';
			if ( callback ) {
				callback();
			}
		}
	};

	requestIds[ id ].up = window.requestAnimationFrame( step );
};
