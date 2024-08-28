/**
 * Disables a panel in the block editor.
 *
 * @param {string} panel
 */
const disablePanel = ( panel ) => {
	window.wp.data.dispatch('core/edit-post').removeEditorPanel(panel);
};

export default disablePanel;
