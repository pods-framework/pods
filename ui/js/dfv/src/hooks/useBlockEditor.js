/**
 * WordPress dependencies
 */
import {__, sprintf} from '@wordpress/i18n';

const useBlockEditor = () => {
	const editorSelect = wp.data?.select('core/editor');
	const editorDispatch = wp.data?.dispatch('core/editor');
	const notices = wp.data?.dispatch('core/notices');

	// @todo Use hook instead of savePost override once stable.
	if (!window.PodsBlockEditor && editorDispatch && editorDispatch.hasOwnProperty('savePost')) {
		// First init.
		window.PodsBlockEditor = {
			// Store original.
			savePost: editorDispatch.savePost,
			messages: {},
			callbacks: {},
		};

		// Override the current editor savePost function.
		editorDispatch.savePost = (options) => {
			options = options || {};

			const pbe = window.PodsBlockEditor;

			if (options.isAutosave || options.isPreview) {
				console.debug( 'Pods object pre-save validation ignored (autosave/preview)' );

				// eslint-disable-next-line no-undef
				return pbe.savePost.apply(this, [options]);
			}

			if (!Object.values(pbe.messages).length) {
				notices.removeNotice('pods-validation');

				console.debug( 'Pods object pre-save validation passed' );

				// eslint-disable-next-line no-undef
				return pbe.savePost.apply(this, [options]);
			}

			let validationFieldErrorList = [];
			for (const fieldName in pbe.messages) {
				editorDispatch?.lockPostSaving(fieldName);

				let fieldRealName = fieldName.replace('pods-field-', '');
				let fieldData = window.PodsDFVAPI.getField(fieldRealName);

				validationFieldErrorList.push(fieldData?.fieldConfig?.label ?? realFieldName);
			}

			notices.createErrorNotice(
				sprintf(__('Please complete these fields before saving: %s', 'pods'), validationFieldErrorList.join(', ')),
				{id: 'pods-validation', isDismissible: true},
			);

			console.debug( 'Pods object pre-save validation failed', validationFieldErrorList );

			for (const fieldCallback in pbe.callbacks) {
				if (pbe.callbacks.hasOwnProperty(fieldCallback) && 'function' === typeof pbe.callbacks[fieldCallback]) {
					pbe.callbacks[fieldCallback]();
				}
			}
		};
	}

	return {
		data: wp.data,
		select: editorSelect,
		dispatch: editorDispatch,
		notices,
		lockPostSaving: (name, messages, callback) => {
			console.debug( 'Pods object pre-save post saving locked' );

			// @todo Use hook instead of savePost override once stable.
			//wp.hooks.addFilter( 'editor.__unstablePreSavePost', 'editor', filter );

			if (typeof window.PodsBlockEditor !== 'undefined') {
				if (messages.length) {
					window.PodsBlockEditor.messages[name] = messages;
					window.PodsBlockEditor.callbacks[name] = callback;
				}
			}
		},
		unlockPostSaving: (name) => {
			// @todo Use hook instead of savePost override once stable.
			//wp.hooks.removeFilter( 'editor.__unstablePreSavePost', 'editor', filter );

			if (typeof window.PodsBlockEditor !== 'undefined') {
				delete window.PodsBlockEditor.messages[name];
				delete window.PodsBlockEditor.callbacks[name];
			}

			editorDispatch?.unlockPostSaving(name);
		},
	};
};

export default useBlockEditor;
