/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import ImageUpload, {
	renderImageUploadButton,
	renderImage,
} from '@moderntribe/common/elements/image-upload/element';

jest.mock( '@wordpress/editor', () => ( {
	MediaUpload: () => ( <button>Media Upload</button> ),
} ) );

jest.mock( '@moderntribe/common/icons', () => ( {
	Close: () => <span>icon</span>,
} ) );

describe( 'renderImageUploadButton', () => {
	const open = jest.fn();

	afterEach( () => {
		open.mockClear();
	} );

	it( 'renders the button', () => {
		const component = renderer.create( renderImageUploadButton( false, 'label' )( { open } ) );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders the button disabled', () => {
		const component = renderer.create( renderImageUploadButton( true, 'label' )( { open } ) );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes the open action when the mediaUpload is fired', () => {
		const component = mount( renderImageUploadButton( false, 'label' )( { open } ) );
		component.find( 'button' ).simulate( 'click' );
		expect( open ).toHaveBeenCalled();
		expect( open ).toHaveBeenCalledTimes( 1 );
	} );
} );

describe( 'renderImage', () => {
	const onRemove = jest.fn();
	const image = {
		id: 42,
		src: 'test-src',
		alt: 'test-alt',
	};

	afterEach( () => {
		onRemove.mockClear();
	} );

	it( 'renders the image and button', () => {
		const component = renderer.create( renderImage( false, image, onRemove ) );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders the image and disabled button', () => {
		const component = renderer.create( renderImage( true, image, onRemove ) );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes onRemove on click', () => {
		const component = mount( renderImage( false, image, onRemove ) );
		component.find( 'button' ).simulate( 'click' );
		expect( onRemove ).toHaveBeenCalled();
		expect( onRemove ).toHaveBeenCalledTimes( 1 );
	} );
} );

describe( 'ImageUpload', () => {
	const onRemove = jest.fn();
	const onSelect = jest.fn();
	let image;

	beforeEach( () => {
		image = {
			id: 0,
			src: '',
			alt: '',
		};
	} );

	afterEach( () => {
		onRemove.mockClear();
		onSelect.mockClear();
	} );

	it( 'renders the component', () => {
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect }
				onRemove={ onRemove }
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders with title', () => {
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect }
				onRemove={ onRemove }
				title="Modern Tribe"
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders with description', () => {
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect}
				onRemove={ onRemove }
				description="The Next Generation of Digital Agency"
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders with class', () => {
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect }
				onRemove={ onRemove }
				className="test-class"
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders uploaded image', () => {
		image = {
			id: 42,
			src: 'test-src',
			alt: 'test-alt',
		};
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect }
				onRemove={ onRemove }
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders upload image button', () => {
		const component = renderer.create(
			<ImageUpload
				image={ image }
				onSelect={ onSelect }
				onRemove={ onRemove }
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
