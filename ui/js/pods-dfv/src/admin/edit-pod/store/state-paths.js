export const UI = 'ui';
export const POD_META = 'podMeta';
export const FIELDS = 'fields';

export const POD_NAME = `${POD_META}.name`;
export const POD_ID = `${POD_META}.id`;

export const ACTIVE_TAB = `${UI}.activeTab`;
export const SAVE_STATUS = `${UI}.saveStatus`;

export const TABS = `${UI}.tabs`;
export const ORDERED_TAB_LIST = `${TABS}.orderedList`;
export const TAB_BY_NAME = `${TABS}.byName`;
export const OPTIONS = `${UI}.options`;

/**
 * get( { x: { y: { z: 'value' } } }, 'x.y' )
 *
 * { z: 'value }
 *
 * @param obj
 * @param path
 * @return {*}
 */
export const get = ( obj, path ) => {
	return path.split( '.' ).reduce(
		( value, el ) => value[ el ],
		obj
	);
};

/**
 * createObjectIn( 'x.y.z', { name: 'Name' } );
 *
 * {
 *   x: {
 *     y: {
 *       z: {
 *         name: 'Name'
 *       }
 *     }
 *   }
 * }
 *
 * @param path
 * @param object
 * @return {{}}
 */
export const createObjectIn = ( path, object = {} ) => {
	return path.split( '.' ).reduceRight(
		( acc, currentValue ) => {
			return { [ currentValue ]: acc };
		},
		object
	);
};
