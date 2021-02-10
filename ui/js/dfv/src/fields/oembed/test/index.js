/* global global */
/**
 * External dependencies
 */
import { mount } from 'enzyme';
import { act } from 'react-dom/test-utils';

/**
 * Internal dependencies
 */
import Oembed from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Oembed Field',
		name: 'test_oembed_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'oembed',
	},
};

describe( 'Oembed field component', () => {
	it( 'creates a text field by default', () => {
		const props = { ...BASE_PROPS };

		const wrapper = mount( <Oembed { ...props } /> );

		expect(
			wrapper.find( 'input' ).props().type
		).toBe( 'text' );
	} );

	it( 'creates a text field which calls the setValue callback when updated', () => {
		const props = {
			...BASE_PROPS,
			setValue: jest.fn(),
		};

		const wrapper = mount( <Oembed { ...props } /> );
		const input = wrapper.find( 'input' ).first();
		input.simulate( 'change', {
			target: { value: 'https://www.youtube.com/watch?v=test' },
		} );

		expect( input.props().type ).toBe( 'text' );
		expect( props.setValue ).toHaveBeenCalledWith( 'https://www.youtube.com/watch?v=test' );
	} );

	it( 'renders the preview', async () => {
		const props = {
			...BASE_PROPS,
			fieldConfig: {
				...BASE_PROPS.fieldConfig,
				oembed_height: '200',
				oembed_show_preview: '1',
				oembed_width: '200',
			},
			value: 'https://www.youtube.com/watch?v=test',
		};

		// Mock the api response
		global.fetch = jest.fn( () =>
			Promise.resolve( {
				status: 200,
				json: () => Promise.resolve( {
					title: 'Embed Result',
					html: '<iframe title=\'Embed Title\' width=\'200\' height=\'113\' src=\'https://www.youtube.com/embed/test?feature=oembed\' frameborder=\'0\' allow=\'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\' allowfullscreen></iframe>',
					provider_name: 'YouTube',
				} ),
			} )
		);

		const wrapper = mount( <Oembed { ...props } /> );

		await act( async () => wrapper );

		wrapper.update();
		wrapper.render();

		const input = wrapper.find( 'input' ).first();
		const previewContainer = wrapper.find( '.pods-oembed-preview' ).first();

		expect( input.props().type ).toEqual( 'text' );
		expect( global.fetch ).toHaveBeenCalledTimes( 1 );
		expect( previewContainer.render().find( 'iframe' ) ).toHaveLength( 1 );
	} );
} );
