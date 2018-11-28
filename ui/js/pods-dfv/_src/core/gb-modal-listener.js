/**
 * Note: No checking is done here to make sure Gutenberg is actually loaded.
 * Consuming code must make sure our implicit Gutenberg dependencies exist
 * before calling through to init().
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
		unSubscribe();
		triggerUpdateEvent();
	}
}

/**
 * Handles edit modals
 */
function saveListener () {
	if ( saveListener.wasSaving ) {
		if ( !editorData.isSavingPost() ) {
			if ( editorData.didPostSaveRequestSucceed() ) {
				unSubscribe();
				triggerUpdateEvent();
			}
		}
	} else {
		saveListener.wasSaving = isUserSaving();
	}
}

/**
 *
 * @return {*|boolean}
 */
function isUserSaving () {
	return editorData.isSavingPost() && !editorData.isAutosavingPost();
}

/**
 *
 */
function triggerUpdateEvent () {
	const postData = {
		'id': editorData.getCurrentPostId(),
		'name': editorData.getCurrentPostAttribute( 'title' ),
		'selected': true
	};
	window.parent.jQuery( window.parent ).trigger( 'dfv:modal:update', postData );
}
