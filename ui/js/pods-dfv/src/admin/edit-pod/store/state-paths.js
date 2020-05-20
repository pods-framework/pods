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

// Current Pod
export const CURRENT_POD = createStatePath( 'currentPod' );
export const POD_NAME = createStatePath( `${ CURRENT_POD.path }.name` );
export const POD_ID = createStatePath( `${ CURRENT_POD.path }.id` );
export const GROUPS = createStatePath( `${ CURRENT_POD.path }.groups` );

// Global
export const GLOBAL = createStatePath( 'global' );

export const GLOBAL_POD = createStatePath( `${ GLOBAL.path }.pod` );
export const GLOBAL_POD_GROUPS = createStatePath( `${ GLOBAL_POD.path }.groups` );

export const GLOBAL_GROUP = createStatePath( `${ GLOBAL.path }.group` );
export const GLOBAL_FIELD = createStatePath( `${ GLOBAL.path }.field` );

// UI
export const UI = createStatePath( 'ui' );
export const ACTIVE_TAB = createStatePath( `${ UI.path }.activeTab` );
export const SAVE_STATUS = createStatePath( `${ UI.path }.saveStatus` );
export const DELETE_STATUS = createStatePath( `${ UI.path }.deleteStatus` );
export const SAVE_MESSAGE = createStatePath( `${ UI.path }.saveMessage` );
