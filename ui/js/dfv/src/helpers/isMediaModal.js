/**
 * Checks if we're running Pods inside a WP media modal.
 *
 * @returns bool
 */
const isMediaModal = () => {
    return window.location.pathname === '/wp-admin/upload.php';
};

export default isMediaModal;
