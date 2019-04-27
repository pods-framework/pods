export const get = ( obj, path ) => ( path.split( '.' ).reduce( ( value, el ) => value[ el ], obj ) );

export const createObjectIn = ( path, object = {} ) => {
	return path.split( '.' ).reduceRight( ( acc, currentValue ) => {
		return { [ currentValue ]: acc };
	}, object );
};

export const UI = 'ui';
export const POD_META = 'podMeta';
export const FIELDS = 'fields';

export const ACTIVE_TAB = `${UI}.activeTab`;
export const SAVE_STATUS = `${UI}.saveStatus`;

export const OPTIONS = `${UI}.options`;
export const TABS = `${UI}.tabs`;
export const ORDERED_TAB_LIST = `${TABS}.orderedList`;
export const TAB_BY_NAME = `${TABS}.byName`;
