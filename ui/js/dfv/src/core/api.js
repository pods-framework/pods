/**
 * WordPress dependencies
 */
import { select, dispatch } from '@wordpress/data'

/**
 * Pods dependencies
 */
import { createStoreKey } from 'dfv/src/store/store';

/**
 * Pods helpers
 */
import isEditPodScreen from 'dfv/src/helpers/isEditPodScreen';

import { STORE_KEY_DFV } from 'dfv/src/store/constants';

const API = {
	/**
	 * Store the field configs for reference.
	 */
	_fieldDataByStoreKeyPrefix: {},

	/**
	 * Attempt to detect the form information (based on pod, item ID, and form counter).
	 *
	 * If any argument is not provided, the first instance will be returned.
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of form information, or undefined if not found.
	 */
	detectForm( pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const storeKeys = Object.keys( this._fieldDataByStoreKeyPrefix );
		const fieldData = this._fieldDataByStoreKeyPrefix;
		const hasSearchCriteria = (
			null !== pod
			|| null !== itemId
			|| null !== formCounter
		);
		const returnExact = (
			null !== pod
			&& null !== itemId
			&& null !== formCounter
		);

		// Check if we have an exact match.
		if ( returnExact ) {
			const storeKey = createStoreKey(
				pod,
				itemId,
				formCounter,
				STORE_KEY_DFV
			);

			const stored = select( storeKey );

			// Store not found.
			if ( ! stored ) {
				return undefined;
			}

			// We found our match.
			return {
				pod,
				itemId,
				formCounter,
				storeKey,
				stored,
			};
		}

		// Check if we have a match for what we are looking for.
		let form = undefined;

		storeKeys.every( function( storeKey ) {
			let stored = select( storeKey );

			// Skip if store not found.
			if ( ! stored ) {
				return true;
			}

			let storePodName;
			let storeItemId;
			let storeFormCounter;

			// Skip if first field not found.
			if ( 'undefined' === typeof fieldData[ storeKey ][0] ) {
				return true;
			}

			let firstField = fieldData[ storeKey ][0];

			// Check if the form matches what we are looking for.
			if ( hasSearchCriteria ) {
				// Skip if pod does not match.
				if ( null !== pod && firstField.pod !== pod ) {
					return true;
				}

				// Skip if itemId does not match.
				if ( null !== itemId && firstField.itemId != itemId ) {
					return true;
				}

				// Skip if formCounter does not match.
				if ( null !== formCounter && firstField.formCounter != formCounter ) {
					return true;
				}
			}

			// We found our match.
			form = {
				pod: firstField.pod,
				itemId: firstField.itemId,
				formCounter: firstField.formCounter,
				storeKey,
				stored,
			};

			// Stop the loop.
			return false;
		} );

		return form;
	},

	/**
	 * Attempt to detect the field information (based on pod, item ID, field name, and form counter).
	 *
	 * If any argument is not provided, the first instance will be returned.
	 *
	 * @param {string}      fieldName   Field name.
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of field information, or undefined if not found.
	 */
	detectField( fieldName, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const form = this.detectForm( pod, itemId, formCounter );

		if ( 'undefined' === typeof form ) {
			return undefined;
		}

		const storeKey = form.storeKey;

		if ( 'undefined' === typeof this._fieldDataByStoreKeyPrefix[ storeKey ] ) {
			return undefined;
		}

		const allFields = this._fieldDataByStoreKeyPrefix[ storeKey ];

		let field = undefined;

		allFields.every( function( fieldObject, index ) {
			// Skip if field name not set.
			if ( undefined === typeof fieldObject.fieldConfig.name ) {
				return true;
			}

			// Skip if field does not match.
			if (
				fieldName !== fieldObject.fieldConfig.name
				&& 'pods_field_' + fieldName !== fieldObject.fieldConfig.name
				&& 'pods_meta_' + fieldName !== fieldObject.fieldConfig.name
			) {
				return true;
			}

			// We found our match.
			field = {
				// Normalize the field name while we have it.
				fieldName: fieldObject.fieldConfig.name,
				fieldObject,
				index,
				form,
			};

			// Stop the loop.
			return false;
		} );

		return field;
	},

	/**
	 * Get list of field configs (based on pod, item ID, and form counter).
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of field data keyed by field name, or undefined if not found.
	 */
	getFields( pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		// Maybe return all fields on the screen.
		if ( null === pod && null === itemId && null === formCounter ) {
			let storeFields = [];

			const storeKeys = Object.keys( this._fieldDataByStoreKeyPrefix );
			const fieldData = this._fieldDataByStoreKeyPrefix;

			storeKeys.forEach( function( storeKey ) {
				let stored = select( storeKey );

				if ( ! stored ) {
					return;
				}

				let storePodName;
				let storeItemId;
				let storeFormCounter;

				if ( 'undefined' !== typeof fieldData[ storeKey ][0] ) {
					let firstField = fieldData[ storeKey ][0];

					storePodName = firstField.pod;
					storeItemId = firstField.itemId;
					storeFormCounter = firstField.formCounter;
				}

				let allFields = fieldData[ storeKey ] || [];
				let fieldConfigs = {};

				allFields.forEach( function( field ) {
					fieldConfigs[ field.fieldConfig.name ] = field;
				} );

				storeFields.push( {
									  pod: storePodName,
									  itemId: storeItemId,
									  formCounter: storeFormCounter,
									  fields: fieldConfigs,
								  } );
			} );

			return storeFields;
		}

		const form = this.detectForm( pod, itemId, formCounter );

		if ( 'undefined' === typeof form ) {
			return undefined;
		}

		const allFields = this._fieldDataByStoreKeyPrefix[ form.storeKey ] || [];
		let fieldConfigs = {};

		allFields.forEach( function( field ) {
			fieldConfigs[ field.fieldConfig.name ] = field;
		} );

		return fieldConfigs;
	},

	/**
	 * Get specific field config (based on pod, item ID, field name, and form counter).
	 *
	 * @param {string}      fieldName   Field name.
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Field data, or undefined if not found.
	 */
	getField( fieldName, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		if ( '' === fieldName ) {
			return undefined;
		}

		const fieldInfo = this.detectField( fieldName, pod, itemId, formCounter );

		if ( 'undefined' === typeof fieldInfo ) {
			return undefined;
		}

		return fieldInfo.fieldObject;
	},

	/**
	 * (IN DEVELOPMENT, NOT FUNCTIONAL) Set field config (based on pod, item ID, field name, and form counter).
	 *
	 * @param {string}      fieldName   Field name.
	 * @param {object}      fieldConfig Field config.
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {true|undefined} True if set, or undefined if not found.
	 */
	__setFieldConfig( fieldName, fieldConfig, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const fieldInfo = this.detectField( fieldName, pod, itemId, formCounter );

		if ( 'undefined' === typeof fieldInfo ) {
			return undefined;
		}

		const storeKey = fieldInfo.form.storeKey;
		const index = fieldInfo.index;

		if ( 'undefined' === typeof this._fieldDataByStoreKeyPrefix[ storeKey ] ) {
			return undefined;
		}

		if ( 'undefined' === typeof this._fieldDataByStoreKeyPrefix[ storeKey ][ index ] ) {
			return undefined;
		}

		this._fieldDataByStoreKeyPrefix[ storeKey ][ index ].fieldConfig = {
			...this._fieldDataByStoreKeyPrefix[ storeKey ][ index ].fieldConfig,
			...fieldConfig
		};

		return true;
	},

	/**
	 * (IN DEVELOPMENT, NOT FUNCTIONAL) Set field config (based on pod, item ID, field name, and form counter).
	 *
	 * @param {string}      fieldName     Field name.
	 * @param {object}      fieldItemData Field item data.
	 * @param {string|null} pod           Pod slug/name. (Optional.)
	 * @param {int|null}    itemId        Object ID. (Optional.)
	 * @param {int|null}    formCounter   Form index. (Optional.)
	 *
	 * @returns {true|undefined} True if set, or undefined if not found.
	 */
	__setFieldItemData( fieldName, fieldItemData, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const fieldInfo = this.detectField( fieldName, pod, itemId, formCounter );

		if ( 'undefined' === typeof fieldInfo ) {
			return undefined;
		}

		const storeKey = fieldInfo.form.storeKey;
		const index = fieldInfo.index;

		if ( 'undefined' === typeof this._fieldDataByStoreKeyPrefix[ storeKey ] ) {
			return undefined;
		}

		if ( 'undefined' === typeof this._fieldDataByStoreKeyPrefix[ storeKey ][ index ] ) {
			return undefined;
		}

		this._fieldDataByStoreKeyPrefix[ storeKey ][ index ].fieldItemData = fieldItemData;

		return true;
	},

	/**
	 * Get current field values (based on pod, item ID, and form counter).
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {array|undefined} Field values array or undefined.
	 */
	getFieldValues( pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		// Maybe return all field values on the screen.
		if ( null === pod && null === itemId && null === formCounter ) {
			let storeValues = [];

			const storeKeys = Object.keys( this._fieldDataByStoreKeyPrefix );
			const fieldData = this._fieldDataByStoreKeyPrefix;

			storeKeys.forEach( function( storeKey ) {
				let stored = select( storeKey );

				if ( ! stored ) {
					return;
				}

				let storePodName;
				let storeItemId;
				let storeFormCounter;

				if ( 'undefined' !== typeof fieldData[ storeKey ][0] ) {
					let firstField = fieldData[ storeKey ][0];

					storePodName = firstField.pod;
					storeItemId = firstField.itemId;
					storeFormCounter = firstField.formCounter;
				}

				storeValues.push( {
					pod: storePodName,
					itemId: storeItemId,
					formCounter: storeFormCounter,
					fieldValues : stored.getPodOptions(),
				} );
			} );

			return storeValues;
		}

		const form = this.detectForm( pod, itemId, formCounter );

		if ( 'undefined' === typeof form ) {
			return undefined;
		}

		return form.stored.getPodOptions();
	},

	/**
	 * Get current field values with configs (based on pod, item ID, and form counter).
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {array|undefined} Field values array or undefined.
	 */
	getFieldValuesWithConfigs( pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		// Maybe return all field values on the screen.
		if ( null === pod && null === itemId && null === formCounter ) {
			let storeValues = [];

			const storeKeys = Object.keys( this._fieldDataByStoreKeyPrefix );
			const fieldData = this._fieldDataByStoreKeyPrefix;

			storeKeys.forEach( function( storeKey ) {
				let stored = select( storeKey );

				if ( ! stored ) {
					return;
				}

				let storePodName;
				let storeItemId;
				let storeFormCounter;

				if ( 'undefined' !== typeof fieldData[ storeKey ][0] ) {
					let firstField = fieldData[ storeKey ][0];

					storePodName = firstField.pod;
					storeItemId = firstField.itemId;
					storeFormCounter = firstField.formCounter;
				}

				let rawFieldValues = stored.getPodOptions();
				let allFields = fieldData[ storeKey ] || [];
				let fieldValues = {};

				allFields.forEach( function( field ) {
					fieldValues[ field.fieldConfig.name ] = {
						fieldConfig: field.fieldConfig,
						value: rawFieldValues[ field.fieldConfig.name ] ?? '',
					};
				} );

				storeValues.push( {
					pod: storePodName,
					itemId: storeItemId,
					formCounter: storeFormCounter,
					fieldValues,
				} );
			} );

			return storeValues;
		}

		const form = this.detectForm( pod, itemId, formCounter );

		if ( 'undefined' === typeof form ) {
			return undefined;
		}

		const rawFieldValues = form.stored.getPodOptions();
		const allFields = this._fieldDataByStoreKeyPrefix[ form.storeKey ] || [];
		let fieldValues = {};

		allFields.forEach( function( field ) {
			fieldValues[ field.fieldConfig.name ] = {
				fieldConfig: field.fieldConfig,
				value: rawFieldValues[ field.fieldConfig.name ] ?? '',
			};
		} );

		return fieldValues;
	},

	/**
	 * Get current field value (based on pod, item ID, field name, and form counter).
	 *
	 * @param {string}      fieldName   Field name.
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {any} Field value or undefined.
	 */
	getFieldValue( fieldName, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const fieldInfo = this.detectField( fieldName, pod, itemId, formCounter );

		if ( 'undefined' === typeof fieldInfo ) {
			return undefined;
		}

		return fieldInfo.form.stored?.getPodOptions()?.[ fieldInfo.fieldName ];
	},

	/**
	 * Set current field value (based on pod, item ID, field name, and form counter).
	 *
	 * @param {string}      fieldName   Field name.
	 * @param {any}         value       New value.
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {true|undefined} True if set, or undefined if not found.
	 */
	setFieldValue( fieldName, value, pod = null, itemId = null, formCounter = null ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const fieldInfo = this.detectField( fieldName, pod, itemId, formCounter );

		if ( 'undefined' === typeof fieldInfo ) {
			return undefined;
		}

		dispatch( fieldInfo.form.storeKey ).setOptionValue( fieldInfo.fieldName, value );

		return true;
	},
};

export default API;
