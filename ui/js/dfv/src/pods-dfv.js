import mnRenderer from 'dfv/src/core/renderers/mn-renderer';
import reactRenderer from 'dfv/src/core/renderers/react-renderer';
import reactDirectRenderer from 'dfv/src/core/renderers/react-direct-renderer';
import { PodsGbModalListener } from 'dfv/src/core/gb-modal-listener';

import * as fields from 'dfv/src/field-manifest';
import * as models from 'dfv/src/model-manifest';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

const fieldClasses = {
	file: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	avatar: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	pick: {
		FieldClass: fields.Pick,
		renderer: mnRenderer,
	},
	text: {
		FieldClass: fields.PodsDFVText,
		renderer: reactRenderer,
	},
	password: {
		FieldClass: fields.PodsDFVPassword,
		renderer: reactRenderer,
	},
	number: {
		FieldClass: fields.PodsDFVNumber,
		renderer: reactRenderer,
	},
	email: {
		FieldClass: fields.PodsDFVEmail,
		renderer: reactRenderer,
	},
	paragraph: {
		FieldClass: fields.PodsDFVParagraph,
		renderer: reactRenderer,
	},
	'edit-pod': {
		FieldClass: fields.PodsDFVEditPod,
		renderer: reactDirectRenderer,
	},
};

window.PodsDFV = {
	fields: fieldClasses,
	models,
	fieldInstances: {},

	/**
	 * Initialize Pod data.
	 */
	init() {
		// Find all in-line data scripts
		const dataTags = [ ...document.querySelectorAll( SCRIPT_TARGET ) ];

		dataTags.forEach( ( tag ) => {
			const data = JSON.parse( tag.innerHTML );

			// Kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			if ( tag.closest( '.media-modal-content' ) ) {
				data.fieldConfig.pick_allow_add_new = 0;
			}

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType === undefined ) {
				return;
			}

			const field = fieldClasses[ data.fieldType ];

			// @todo remove this later
			// We need to only depend on the `config` and `fieldType`
			// properties, so discard the others for now, until they're
			// removed from the API.
			const actualData = {
				config: {
					...data.config,
				},
				fieldType: data.fieldType,
				data,
			};

			// eslint-disable-next-line no-console
			console.log( 'config data:', actualData );

			// @todo remove this
			// Hack for missing basic field type information
			/* eslint-disable */
			if ( actualData.config?.global?.field?.groups ) {
				actualData.config.global.field.groups = [
					{
						object_type: 'group',
						storage_type: 'collection',
						name: 'basic',
						id: '',
						parent: 'pod/_pods_field',
						label: 'Basic',
						description: '',
						fields: [
							{
								object_type: "field",
								storage_type: "collection",
								name: "label",
								id: "",
								parent: "pod/_pods_field",
								group: "group/pod/_pods_field/basic",
								label: "Label",
								description: "",
								help: "help",
								type: "text",
								default: ""
							},
							{
								object_type: "field",
								storage_type: "collection",
								name: "name",
								id: "",
								parent: "pod/_pods_field",
								group: "group/pod/_pods_field/basic",
								label: "Name",
								description: "",
								help: "help",
								type: "slug",
								default: ""
							},
							{
								object_type: "field",
								storage_type: "collection",
								name: "field_type",
								id: "",
								parent: "pod/_pods_field",
								group: "group/pod/_pods_field/additional-field",
								label: "Field Type",
								description: "",
								help: "",
								default: "text",
								attributes: [],
								class: "",
								type: "pick",
								grouped: 0,
								developer_mode: false,
								dependency: true,
								'depends-on': [],
								'excludes-on': [],
								data: {
									"text": "Plain Text",
									"boolean": "Yes / No",
									"color": "Color"
								}
							},
						],
					},
					...actualData.config.global.field.groups,
				];
			}
			/* eslint-enable */

			if ( field !== undefined ) {
				field.renderer( field.FieldClass, tag.parentNode, actualData );
			}
		} );
	},

	isModalWindow() {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded() {
		return ( wp.data !== undefined && wp.data.select( 'core/editor' ) !== undefined );
	},
};

/**
 * Kick everything off on DOMContentLoaded
 */
document.addEventListener( 'DOMContentLoaded', () => {
	window.PodsDFV.init();

	// Load the Gutenberg modal listener if we're inside a Pods modal with Gutenberg active
	if ( window.PodsDFV.isModalWindow() && window.PodsDFV.isGutenbergEditorLoaded() ) {
		PodsGbModalListener.init();
	}
} );
