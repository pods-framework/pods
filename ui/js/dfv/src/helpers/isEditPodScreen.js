/**
 * Checks if we're on the Edit Pod screen.
 *
 * @returns bool True if on the Edit Pod screen.
 */
const isEditPodScreen = () => 'undefined' !== typeof window.podsAdminConfig;

export default isEditPodScreen;
