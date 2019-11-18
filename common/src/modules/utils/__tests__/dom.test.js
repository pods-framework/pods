/**
 * Internal dependencies
 */
import {
	hasClass,
	isRootNode,
	searchParent,
} from '@moderntribe/common/utils/dom';

describe( 'Tests for dom.js', () => {
	beforeAll( () => {
		window.document.body.classList.add( 'one', 'two' );
	} );

	afterAll( () => {
		window.document.body.classList.remove( 'one', 'two' );
	} );

	describe( 'hasClass dom utility', () => {
		it( 'Should return false when the dom element does not have any class', () => {
			expect( hasClass( window.document.body, [] ) ).toBe( false );
			expect( hasClass( window.document.body, [ 'five', 'seven' ] ) ).toBe( false );
			expect( hasClass( window.document.body, [ 'eight' ] ) ).toBe( false );
		} );

		it( 'Should return true whe the dom element has any of the classes', () => {
			expect( hasClass( window.document.body, [ 'one', 'two' ] ) ).toBe( true );
			expect( hasClass( window.document.body, [ 'two', 'one' ] ) ).toBe( true );
			expect( hasClass( window.document.body, [ 'four', 'two' ] ) ).toBe( true );
			expect( hasClass( window.document.body, [ 'one', 'five' ] ) ).toBe( true );
			expect( hasClass( window.document.body, [ 'nostyle', 'six', 'seven', 'one' ] ) ).toBe( true );
		} );
	} );

	test( 'Test for searchParent', () => {
		expect( searchParent( null ) ).toBeFalsy();
		const treeWithNode = {
			parentNode: {
				value: 10,
				parentNode: {
					value: 20,
				},
			},
		};

		const callback = jest.fn( ( node ) => node.value === 20 );
		const result = searchParent( treeWithNode, callback );
		expect( callback ).toBeCalled();
		expect( callback ).toBeCalledWith( { value: 20 } );
		expect( result ).toBeTruthy();

		const treeWithoutNode = {
			parentNode: {
				parentNode: {
					parentNode: {
						top: {
							document: 'global',
						},
					},
				},
			},
		};

		expect( searchParent( treeWithoutNode ) ).toBeFalsy();
	} );

	test( 'Test for isRootNode', () => {
		expect( isRootNode( null ) ).toBeFalsy();
		expect( isRootNode( 'text' ) ).toBeFalsy();
		expect( isRootNode( window.document.body ) ).toBeFalsy();
		expect( isRootNode( window.document ) ).toBeTruthy();
	} );
} );
