/**
 * Checks if we're running Pods inside a WP media modal.
 *
 * @returns bool
 */
const isMediaModal = ( path ) => {
	if ( ! path ) {
		path = window.location.pathname;
	}

	const pathToUpload = '/wp-admin/upload.php';

	return path.includes( pathToUpload );
};

export default isMediaModal;
