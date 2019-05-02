import { tail } from 'lodash';

const tailPath = ( dotPath ) => tail( dotPath.split( '.' ) ).join( '.' );

const createTree = ( value, dotPath ) => {
	return dotPath.split( '.' ).reduceRight(
		( acc, currentValue ) => {
			return { [ currentValue ]: acc };
		},
		value
	);
};

const getFrom = ( state, dotPath ) => {
	return dotPath.split( '.' ).reduce( ( value, el ) => value[ el ], state );
};

export const createStatePath = path => {
	return {
		// path 'ui.tabs.tabList', tailPath: 'tabs.tabList'
		path: path,
		tailPath: tailPath( path ),

		getFrom: ( state, dotPath = path ) => {
			return getFrom( state, dotPath );
		},

		tailGetFrom: state => {
			return getFrom( state, tailPath( path ) );
		},

		createTree: ( value, dotPath = path ) => {
			return createTree( value, dotPath );
		},

		tailCreateTree: value => {
			return createTree( value, tailPath( path ) );
		},
	};
};

export const POD_META = createStatePath( 'podMeta' );
export const POD_NAME = createStatePath( `${POD_META.path}.name` );
export const POD_ID = createStatePath( `${POD_META.path}.id` );

export const FIELDS = createStatePath( 'fields' );

export const GROUPS = createStatePath( 'groups' );
export const GROUP_LIST = createStatePath( `${GROUPS.path}.groupList` );

export const UI = createStatePath( 'ui' );
export const ACTIVE_TAB = createStatePath( `${UI.path}.activeTab` );
export const SAVE_STATUS = createStatePath( `${UI.path}.saveStatus` );
export const TABS = createStatePath( `${UI.path}.tabs` );

// Ordered list of tab names as an array: [ 'tab1name', 'tab2name', ... ]
export const TAB_LIST = createStatePath( `${TABS.path}.tabList` );

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


