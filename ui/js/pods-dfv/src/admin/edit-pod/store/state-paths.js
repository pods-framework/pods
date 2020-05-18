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

export const createStatePath = ( path ) => {
	return {
		// path 'ui.tabs.tabList', tailPath: 'tabs.tabList'
		path,
		tailPath: tailPath( path ),

		getFrom: ( state, dotPath = path ) => {
			return getFrom( state, dotPath );
		},

		tailGetFrom: ( state ) => {
			return getFrom( state, tailPath( path ) );
		},

		createTree: ( value, dotPath = path ) => {
			return createTree( value, dotPath );
		},

		tailCreateTree: ( value ) => {
			return createTree( value, tailPath( path ) );
		},
	};
};

export const CURRENT_POD = createStatePath( 'currentPod' );
export const GLOBAL_POD = createStatePath( 'global.pod' );

// Current Pod
export const POD_NAME = createStatePath( `${ CURRENT_POD.path }.name` );
export const POD_ID = createStatePath( `${ CURRENT_POD.path }.id` );

export const GROUPS = createStatePath( `${ CURRENT_POD.path }.groups` );
// export const FIELDS = createStatePath( `${ CURRENT_POD.path }.fields` );

// Global Pod
export const GLOBAL_GROUPS = createStatePath( `${ GLOBAL_POD.path }.groups` );
export const GLOBAL_FIELDS = createStatePath( `${ GLOBAL_POD.path }.fields` );

// UI
export const UI = createStatePath( 'ui' );
export const ACTIVE_TAB = createStatePath( `${ UI.path }.activeTab` );
export const SAVE_STATUS = createStatePath( `${ UI.path }.saveStatus` );
export const DELETE_STATUS = createStatePath( `${ UI.path }.deleteStatus` );
export const SAVE_MESSAGE = createStatePath( `${ UI.path }.saveMessage` );

// export const POD_META = createStatePath( 'podMeta' );
// export const TABS = createStatePath( `${ GROUPS.path }` );

// export const GROUPS_BY_NAME = createStatePath( `${ GROUPS.path }.byName` );

// Ordered list of group names as an array: [ 'group1', 'group2', ... ]
// export const GROUP_LIST = createStatePath( `${ GROUPS.path }.groupList` );

// One to many relationship:
// { 'group1': [ 'field1', 'field2', ...], 'group2': [...] }
// export const GROUP_FIELD_LIST = createStatePath( `${ GROUPS.path }.groupFieldList` );

// Ordered list of tab names as an array: [ 'tab1', 'tab2', ... ]
// export const TAB_LIST = createStatePath( `${ TABS.path }.tabList` );

// Tab objects keyed by tab name:
// { tab1: {tab object}, tab2: {tab object}, ...}
// export const TABS_BY_NAME = createStatePath( `${ TABS.path }.byName` );

// One to many relationship tab => options:
// { 'tab1': [ 'option1', 'option2', ...], 'tab2': [...] }
// export const TAB_OPTIONS_LIST = createStatePath( `${ TABS.path }.tabOptionsList` );

// Pod option list keyed by option name:
// { can_export: {object}, show_ui: {object}, etc }
// export const OPTIONS = createStatePath( 'options' );
