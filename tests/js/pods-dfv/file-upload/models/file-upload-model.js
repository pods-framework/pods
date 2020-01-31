/*global jQuery, _, Backbone, Mn, assert */
import {FileUploadCollection, FileUploadModel} from 'pods-dfv/_src/file-upload/file-upload-model';

const default_model = new FileUploadModel();

const test_data = {
	id       : 1,
	icon     : 'abc',
	name     : 'def',
	edit_link: 'http://www.example.com/edit',
	link     : 'http://www.example.com/link',
	download : 'http://www.example.com/download'
};

const test_model = new FileUploadModel( test_data );
const collection = new FileUploadCollection( test_model );

describe( 'FileUploadModel', function () {
	it( 'should have proper defaults', function () {
		assert.equal( default_model.get( 'id' ), 0, 'id' );
		assert.equal( default_model.get( 'icon' ), '', 'icon' );
		assert.equal( default_model.get( 'name' ), '', 'name' );
		assert.equal( default_model.get( 'edit_link' ), '', 'edit_link' );
		assert.equal( default_model.get( 'link' ), '', 'link' );
		assert.equal( default_model.get( 'download' ), '', 'download' );
	} );

	it( 'should have proper test values', function () {
		assert.equal( test_model.get( 'id' ), test_data.id, 'id' );
		assert.equal( test_model.get( 'icon' ), test_data.icon, 'icon' );
		assert.equal( test_model.get( 'name' ), test_data.name, 'name' );
		assert.equal( test_model.get( 'edit_link' ), test_data.edit_link, 'edit_link' );
		assert.equal( test_model.get( 'link' ), test_data.link, 'link' );
		assert.equal( test_model.get( 'download' ), test_data.download, 'download' );
	} );
} );

describe( 'FileUploadCollection', function () {
	it( 'should have one model', function () {
		assert.equal( collection.length, 1 );
	} );
} );
