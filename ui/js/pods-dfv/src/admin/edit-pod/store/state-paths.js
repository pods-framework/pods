export const get = ( obj, path ) => ( path.split( '.' ).reduce( ( value, el ) => value[ el ], obj ) );

export const UI = 'ui';
export const POD_META = 'podMeta';
export const FIELDS = 'fields';

export const OPTIONS = `${UI}.options`;
export const TABS = `${UI}.tabs`;
export const TAB_BY_NAME = `${TABS}.byName`;
