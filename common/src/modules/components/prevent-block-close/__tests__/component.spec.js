import React from 'react';
import PreventBlockClose from '../component';

describe( 'PreventBlockClose', () => {
	test( 'should match snapshot', () => {
		const component = mount(
			<PreventBlockClose>
				<span>Test children</span>
			</PreventBlockClose>
		);
		const child = component.find( 'span' );
		expect( child ).toHaveLength( 1 );
		expect( child.text() ).toEqual( "Test children" );
	} );
} );
