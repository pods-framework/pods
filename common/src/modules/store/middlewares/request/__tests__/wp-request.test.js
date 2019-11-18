/**
 * Internal dependencies
 */
import wpRequest, { actions } from '@moderntribe/common/store/middlewares/request';

let create;
const nextMock = jest.fn();
const meta = {
	path: '',
	params: {},
	actions: {
		none: jest.fn(),
		start: jest.fn(),
		success: jest.fn(),
		error: jest.fn(),
	},
};

describe( '[STORE] - wp-request middleware', () => {
	let _fetch;
	beforeAll( () => {
		create = () => {
			const invoke = ( action ) => wpRequest()( nextMock )( action );
			return { next: nextMock, invoke };
		};
		_fetch = global.fetch;
	} );

	afterEach( () => {
		global.fetch = _fetch;
	} );

	afterEach( () => {
		nextMock.mockClear();
		meta.actions.start.mockClear();
		meta.actions.error.mockClear();
		meta.actions.none.mockClear();
		meta.actions.success.mockClear();
		window.wp.apiRequest = undefined;
	} );

	it( 'Should move through a unknown action', () => {
		const { next, invoke } = create();
		const action = { type: 'UNKNOWN' };
		invoke( action );

		expect( next ).toHaveBeenCalled();
		expect( next ).toHaveBeenCalledTimes( 1 );
		expect( next ).toHaveBeenCalledWith( action );
	} );

	it( 'Should execute the none action if the path is empty', () => {
		const { next, invoke } = create();
		const action = actions.wpRequest( meta );
		invoke( action );

		expect( next ).toHaveBeenCalled();
		expect( next ).toHaveBeenCalledTimes( 1 );
		expect( next ).toHaveBeenCalledWith( action );
		expect( meta.actions.none ).toHaveBeenCalled();
		expect( meta.actions.none ).toHaveBeenCalledTimes( 1 );
		expect( meta.actions.none ).toHaveBeenLastCalledWith( meta.path );
		expect( meta.actions.start ).not.toHaveBeenCalled();
		expect( meta.actions.success ).not.toHaveBeenCalled();
		expect( meta.actions.error ).not.toHaveBeenCalled();
	} );

	it( 'Should execute the correct actions on success', async () => {
		const { invoke } = create();

		const body = {
			id: 1217,
			date: '2018-05-26T23:07:05',
			meta: {},
		};

		const headers = new Headers();

		global.fetch = jest.fn().mockImplementation( () =>
			Promise.resolve( {
				ok: true,
				status: 200,
				json: () => body,
				headers,
			} ),
		);

		await invoke( actions.wpRequest( { ...meta, path: 'tribe_organizer/1217' } ) );

		expect.assertions( 8 );
		expect( meta.actions.none ).not.toHaveBeenCalled();
		expect( meta.actions.error ).not.toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledWith( 'wp/v2/tribe_organizer/1217', {} );
		expect( meta.actions.start ).toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledTimes( 1 );
		expect( meta.actions.success ).toHaveBeenCalled();
		expect( meta.actions.success )
			.toHaveBeenCalledWith( { body, headers } );
		expect( meta.actions.success ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'execute success actions on 201 response code - creation code', async () => {
		const { invoke } = create();

		const body = {
			id: 201,
			date: '2018-05-26T23:07:05',
			meta: {
				title: 'Creating a post....'
			},
		};

		const headers = new Headers();

		global.fetch = jest.fn().mockImplementation( () =>
			Promise.resolve( {
				ok: true,
				status: 201,
				json: () => body,
				headers,
			} ),
		);

		await invoke( actions.wpRequest( { ...meta, path: 'tribe_organizer/1217' } ) );

		expect.assertions( 8 );
		expect( meta.actions.none ).not.toHaveBeenCalled();
		expect( meta.actions.error ).not.toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledWith( 'wp/v2/tribe_organizer/1217', {} );
		expect( meta.actions.start ).toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledTimes( 1 );
		expect( meta.actions.success ).toHaveBeenCalled();
		expect( meta.actions.success ).toHaveBeenCalledWith( { body, headers } );
		expect( meta.actions.success ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'Should reject on 404 status code', async () => {
		const { invoke } = create();

		global.fetch = jest.fn().mockImplementation( () => Promise.resolve( { status: 404 } ) );

		const error = await invoke( actions.wpRequest( { ...meta, path: 'tribe_organizer/1217' } ) );
		expect.assertions( 6 );
		expect( meta.actions.none ).not.toHaveBeenCalled();
		expect( meta.actions.success ).not.toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledWith( 'wp/v2/tribe_organizer/1217', {} );
		expect( meta.actions.error ).toHaveBeenCalled();
		expect( meta.actions.error ).toHaveBeenCalledWith( error );
	} );

	it( 'Should execute the correct actions on failure', async () => {
		const { invoke } = create();

		global.fetch = jest.fn().mockImplementation( () => Promise.reject( 'Wrong path' ) );

		const error = await invoke( actions.wpRequest( {
			...meta,
			path: 'tribe_organizer/1217//////',
		} ) );
		expect.assertions( 6 );
		expect( meta.actions.none ).not.toHaveBeenCalled();
		expect( meta.actions.success ).not.toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalled();
		expect( meta.actions.start ).toHaveBeenCalledWith( 'wp/v2/tribe_organizer/1217//////', {} );
		expect( meta.actions.error ).toHaveBeenCalled();
		expect( meta.actions.error ).toHaveBeenCalledWith( error );
	} );
} );
