const { last } = lodash;

export const createStatePath = path => {
	return {
		path: path,

		getFrom: state => {
			return path.split( '.' ).reduce( ( value, el ) => value[ el ], state );
		},

		createTree: value => {
			return path.split( '.' ).reduceRight( ( acc, currentValue ) => {
				return { [ currentValue ]: acc };
			}, value );
		}
	};
};

export const UI = createStatePath( 'ui' );
export const POD_META = createStatePath( 'podMeta' );
export const FIELDS = createStatePath( 'fields' );

export const POD_NAME = createStatePath( `${POD_META.path}.name` );
export const POD_ID = createStatePath( `${POD_META.path}.id` );

export const ACTIVE_TAB = createStatePath( `${UI.path}.activeTab` );
export const SAVE_STATUS = createStatePath( `${UI.path}.saveStatus` );

export const TABS = createStatePath( `${UI.path}.tabs` );
export const TABS_LIST = createStatePath( `${TABS.path}.orderedList` );
export const TABS_BY_NAME = createStatePath( `${TABS.path}.byName` );
export const OPTIONS = createStatePath( `${UI.path}.options` );

// Loose strings
export const TAB_OPTIONS_LIST = 'optionList';
