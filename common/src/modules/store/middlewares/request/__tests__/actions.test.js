/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/common/store/middlewares/request';

describe( '[STORE] - Request actions', () => {
	test( 'WP Request action', () => {
		const meta = {
			path: 'tribe_organizer/1225',
			actions: {},
		};
		expect( actions.wpRequest( meta ) ).toMatchSnapshot();
	} );
} );
