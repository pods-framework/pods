/**
 * Note: No checking is done here to make sure we're in a modal and that
 * Gutenberg is actually loaded.  Consuming code must make sure the implicit
 * Gutenberg dependencies exist (primarily wp.data) before calling through
 * to init().
 */
let unSubscribe;
const editorData = wp.data && wp.data.select( 'core/editor' );

/**
 *
 */
export const PodsGbModalListener = {
	init: function () {
		if ( editorData.isCurrentPostPublished() ) {
			// Post is published, this is an edit
			unSubscribe = wp.data.subscribe( saveListener );
		} else {
			// Unpublished post, this is an "add new" modal
			unSubscribe = wp.data.subscribe( publishListener );
		}
	}
};

/**
 * Handles "add new" modals
 */
function publishListener () {

	if ( editorData.isCurrentPostPublished() ) {
		const postData = {
			'id': editorData.getCurrentPostId(),
			'name': editorData.getCurrentPostAttribute( 'title' ),
			'selected': true // Automatically select add new records
		};

		unSubscribe();
		triggerUpdateEvent( postData );
	}
}

/**
 * Handles "edit existing" modals
 */
function saveListener () {

	if ( saveListener.wasSaving ) {

		// The wasSaving flag already ignores autosave so we only need to
		// check isSavingPost()
		if ( !editorData.isSavingPost() ) {

			// Currently on save failure we'll remain subscribed and try
			// listening for the next save attempt
			saveListener.wasSaving = false;

			if ( editorData.didPostSaveRequestSucceed() ) {
				const postData = {
					'id': editorData.getCurrentPostId(),
					'name': editorData.getCurrentPostAttribute( 'title' ),
				};

				unSubscribe();
				triggerUpdateEvent( postData );
			}
		}
	} else {
		saveListener.wasSaving = isUserSaving();
	}
}

/**
 * Whether or not an active save is in progress due to user action (ignore autosaves)
 *
 * @return boolean
 */
function isUserSaving () {
	return !!( editorData.isSavingPost() && !editorData.isAutosavingPost() );
}

/**
 * The event listener in the parent window will take care of closing the modal
 */
function triggerUpdateEvent ( postData ) {
	window.parent.jQuery( window.parent ).trigger( 'dfv:modal:update', postData );
}
