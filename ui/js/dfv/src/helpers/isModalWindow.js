/**
 * Checks if we're running Pods inside a modal.
 *
 * @returns bool
 */
const isModalWindow = () => {
    return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
};

export default isModalWindow;
