/*global jQuery, _, Backbone, Mn, assert */
import {RelationshipCollection, RelationshipModel} from 'pods-dfv/src/fields/pick/relationship-model';

const default_model = new RelationshipModel();

const test_data = {
	id          : 1,
	name        : 'def',
	icon        : 'abc',
	link        : 'http://www.example.com/link',
	edit_link   : 'http://www.example.com/edit',
	selected    : true
};

const test_model = new RelationshipModel( test_data );
const collection = new RelationshipCollection( test_model );

describe( 'RelationshipModel', function () {
	it( 'should have proper defaults', function () {
		assert.equal( default_model.get( 'id' ), 0, 'id' );
		assert.equal( default_model.get( 'name' ), '', 'name' );
		assert.equal( default_model.get( 'icon' ), '', 'icon' );
		assert.equal( default_model.get( 'link' ), '', 'link' );
		assert.equal( default_model.get( 'edit_link' ), '', 'edit_link' );
		assert.equal( default_model.get( 'selected' ), false, 'selected' );
	} );

	it( 'should have proper test values', function () {
		assert.equal( test_model.get( 'id' ), test_data.id, 'id' );
		assert.equal( test_model.get( 'name' ), test_data.name, 'name' );
		assert.equal( test_model.get( 'icon' ), test_data.icon, 'icon' );
		assert.equal( test_model.get( 'link' ), test_data.link, 'link' );
		assert.equal( test_model.get( 'edit_link' ), test_data.edit_link, 'edit_link' );
		assert.equal( test_model.get( 'selected' ), test_data.selected, 'selected' );
	} );
} );

describe( 'RelationshipCollection', function () {
	it( 'should have one model', function () {
		assert.equal( collection.length, 1 );
	} );
} );
