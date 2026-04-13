/**
 * Note: No checking is done here to make sure we're in a modal and that
 * Gutenberg is actually loaded.  Consuming code must make sure the implicit
 * Gutenberg dependencies exist (primarily wp.data) before calling through
 * to init().
 */

// The guard in front is to ensure wp.data exists before accessing select
const editorData = wp.data?.select( 'core/editor' );
let unSubscribe;

//-------------------------------------------
// Helper functions, not externally exposed
//-------------------------------------------

/**
 * Get the featured image URL.
 *
 * @return {string} The featured image URL.
 */
function getFeaturedImageURL() {
	const featuredImageId = editorData.getCurrentPostAttribute( 'featured_media' );
	let url = '';

	// Early exit if nothing was set
	if ( ! featuredImageId ) {
		return url;
	}

	const media = wp.data.select( 'core' ).getMedia( featuredImageId );

	if ( media ) {
		const mediaSize = wp.hooks.applyFilters( 'editor.PostFeaturedImage.imageSize', 'post-thumbnail', '' );
		if ( media.media_details && media.media_details.sizes && media.media_details.sizes[ mediaSize ] ) {
			url = media.media_details.sizes[ mediaSize ].source_url;
		} else {
			url = media.source_url;
		}
	}

	return url;
}

/**
 * Handles "add new" modals
 */
function publishListener() {
	if ( editorData.isCurrentPostPublished() ) {
		unSubscribe();

		triggerUpdateEvent( {
			icon: getFeaturedImageURL(),
			link: editorData.getPermalink(),
			edit_link: `post.php?post=${ editorData.getCurrentPostId() }&action=edit&pods_modal=1`,
			selected: true, // Automatically select add new records
		} );
	}
}

/**
 * Handles "edit existing" modals
 */
function saveListener() {
	if ( saveListener.wasSaving ) {
		// The wasSaving flag already ignores autosave so we only need to
		// check isSavingPost()
		if ( ! editorData.isSavingPost() ) {
			// Currently on save failure we'll remain subscribed and try
			// listening for the next save attempt
			saveListener.wasSaving = false;

			if ( editorData.didPostSaveRequestSucceed() ) {
				unSubscribe();
				triggerUpdateEvent( {
					icon: getFeaturedImageURL(),
				} );
			}
		}
	} else {
		saveListener.wasSaving = isUserSaving();
	}
}

/**
 * Whether or not an active save is in progress due to user action (ignore autosaves)
 *
 * @return {boolean} Whether editor is saving.
 */
function isUserSaving() {
	return !! ( editorData.isSavingPost() && ! editorData.isAutosavingPost() );
}

/**
 * The event listener in the parent window will take care of closing the modal
 *
 * @param {Object} optionalData
 */
function triggerUpdateEvent( optionalData ) {
	const defaultData = {
		id: editorData.getCurrentPostId(),
		name: editorData.getCurrentPostAttribute( 'title' ),
	};
	const postData = Object.assign( defaultData, optionalData );

	window.parent.postMessage( {
		type: 'PODS_MESSAGE',
		data: postData,
	}, window.location.origin );
}

/**
 * initPodsGbModalListener() is the only exposed interface
 */
const initPodsGbModalListener = () => {
	if ( editorData.isCurrentPostPublished() ) {
		// Post is published, this is an edit
		unSubscribe = wp.data.subscribe( saveListener );
	} else {
		// Unpublished post, this is an "add new" modal
		unSubscribe = wp.data.subscribe( publishListener );
	}
};

export default initPodsGbModalListener;
