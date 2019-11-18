/**
 * Allow to set a function (callback) as the parameter of onChange which will send the value of the
 * event into the callback to avoid arrow functions around props of components.
 *
 * @param {Function} callback executed once the event is fired.
 * @return {Function} Function executed by the event to extract the value
 */
export const sendValue = ( callback ) => ( event ) => {
	const { target = {} } = event;
	const { value = '' } = target;
	callback( value );
};
