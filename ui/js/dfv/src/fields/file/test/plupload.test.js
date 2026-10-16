/* global global */

/**
 * External dependencies
 */
import Backbone from 'backbone';
import $ from 'jquery';

/**
 * Internal dependencies
 */
import { File } from '../file-upload';
import { Plupload } from '../uploaders/plupload';

describe( 'Plupload uploader', () => {
	let mockInit;
	let mockBind;
	let mockDestroy;

	const fieldConfigWithPlupload = () => ( {
		file_uploader: 'plupload',
		file_limit: '0',
		file_add_button: 'Add File',
		file_field_template: 'rows',
		plupload_init: {
			runtimes: 'html5',
			url: 'http://example.test/upload',
			filters: [],
		},
	} );

	const uploaderOptions = ( overrides = {} ) => ( {
		browseButton: null,
		browseContainer: null,
		uiRegion: {},
		fieldConfig: fieldConfigWithPlupload(),
		fileCollection: { models: [] },
		...overrides,
	} );

	beforeEach( () => {
		mockInit = jest.fn();
		mockBind = jest.fn();
		mockDestroy = jest.fn();

		global.plupload = {
			Uploader: jest.fn( function() {
				this.init = mockInit;
				this.bind = mockBind;
				this.destroy = mockDestroy;
			} ),
		};
	} );

	afterEach( () => {
		document.body.innerHTML = '';
		delete global.plupload;
	} );

	test( 'skips init when the browse button is missing', () => {
		expect( () => new Plupload( uploaderOptions() ) ).not.toThrow();
		expect( global.plupload.Uploader ).not.toHaveBeenCalled();
	} );

	test( 'skips init when the button is not in the document', () => {
		const button = document.createElement( 'a' );
		button.className = 'pods-dfv-list-add';

		expect( () => new Plupload( uploaderOptions( { browseButton: button } ) ) ).not.toThrow();
		expect( global.plupload.Uploader ).not.toHaveBeenCalled();
	} );

	test( 'inits with button and container ids when the button is attached', () => {
		const container = document.createElement( 'div' );
		const button = document.createElement( 'a' );
		button.className = 'pods-dfv-list-add';
		container.appendChild( button );
		document.body.appendChild( container );

		const fieldConfig = fieldConfigWithPlupload();
		const uploader = new Plupload( uploaderOptions( {
			browseButton: button,
			browseContainer: container,
			fieldConfig,
		} ) );

		expect( global.plupload.Uploader ).toHaveBeenCalledTimes( 1 );

		const initOptions = global.plupload.Uploader.mock.calls[ 0 ][ 0 ];
		expect( typeof initOptions.browse_button ).toBe( 'string' );
		expect( initOptions.browse_button ).toBe( button.id );
		expect( initOptions.container ).toBe( container.id );
		expect( mockInit ).toHaveBeenCalledTimes( 1 );

		// Shared field config stays untouched for other instances.
		expect( fieldConfig.plupload_init.browse_button ).toBeUndefined();
		expect( fieldConfig.plupload_init.container ).toBeUndefined();

		uploader.onBeforeDestroy();
	} );

	test( 'gives each button a unique id', () => {
		const first = document.createElement( 'a' );
		const second = document.createElement( 'a' );
		document.body.appendChild( first );
		document.body.appendChild( second );

		const firstUploader = new Plupload( uploaderOptions( { browseButton: first } ) );
		const secondUploader = new Plupload( uploaderOptions( { browseButton: second } ) );

		expect( first.id ).not.toBe( '' );
		expect( second.id ).not.toBe( '' );
		expect( first.id ).not.toBe( second.id );

		firstUploader.onBeforeDestroy();
		secondUploader.onBeforeDestroy();
	} );

	test( 'destroys the plupload instance', () => {
		const button = document.createElement( 'a' );
		document.body.appendChild( button );

		const uploader = new Plupload( uploaderOptions( { browseButton: button } ) );
		uploader.onBeforeDestroy();

		expect( mockDestroy ).toHaveBeenCalledTimes( 1 );
		expect( uploader.plupload ).toBeNull();
	} );

	test( 'file view creates the uploader without throwing', () => {
		// jQuery UI is not loaded in tests, stub the sortable call.
		if ( ! $.fn.sortable ) {
			$.fn.sortable = jest.fn();
		}

		const fieldModel = new Backbone.Model( {
			htmlAttr: {},
			fieldConfig: fieldConfigWithPlupload(),
		} );

		const view = new File( { model: fieldModel, fieldItemData: [] } );
		document.body.appendChild( view.el );

		expect( () => view.render() ).not.toThrow();
		expect( view.uploader.browseButton.tagName ).toBe( 'A' );
		expect( global.plupload.Uploader ).toHaveBeenCalledTimes( 1 );

		view.destroy();
		expect( mockDestroy ).toHaveBeenCalledTimes( 1 );
	} );
} );
