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

export const POD_META = createStatePath( 'podMeta' );
export const POD_NAME = createStatePath( `${POD_META.path}.name` );
export const POD_ID = createStatePath( `${POD_META.path}.id` );

export const FIELDS = createStatePath( 'fields' );

export const UI = createStatePath( 'ui' );
export const ACTIVE_TAB = createStatePath( `${UI.path}.activeTab` );
export const SAVE_STATUS = createStatePath( `${UI.path}.saveStatus` );
export const TABS = createStatePath( `${UI.path}.tabs` );

// Ordered list of tab names as an array: [ 'tab1name', 'tab2name', ... ]
export const TABS_LIST = createStatePath( `${TABS.path}.orderedList` );

// Tab objects keyed by tab name:
// { tab1name: {tab object}, tab2name: {tab object}, ...}
export const TABS_BY_NAME = createStatePath( `${TABS.path}.byName` );

// Pod option list keyed by option name:
// { can_export: {object}, show_ui: {object}, etc }
export const OPTIONS = createStatePath( 'options' );

// Ordered list of option names for this tab as an array:
// [ 'option1name', 'options2name', ...]
// Stored in the tab objects in TABS_BY_NAME
export const TAB_OPTIONS_LIST = 'optionList';


