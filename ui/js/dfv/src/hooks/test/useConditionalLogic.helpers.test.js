/**
 * Direct unit tests for conditional logic helper functions
 * These tests call the helper functions directly with their arguments
 */

import {
	looseStringEqualityCheck,
	convertStringToArray,
	isValueEmpty,
	stringComparison,
	regexMatch,
	inComparison,
	inValuesComparison,
	equalityComparison,
	numericComparison,
} from '../useConditionalLogic';

describe( 'Conditional Logic Helper Functions - Unit Tests', () => {
	describe( 'looseStringEqualityCheck', () => {
		it( 'should match identical strings', () => {
			expect( looseStringEqualityCheck( 'test', 'test' ) ).toBe( true );
		} );

		it( 'should match case-insensitively', () => {
			expect( looseStringEqualityCheck( 'Test', 'test' ) ).toBe( true );
			expect( looseStringEqualityCheck( 'TEST', 'test' ) ).toBe( true );
			expect( looseStringEqualityCheck( 'abc', 'ABC' ) ).toBe( true );
		} );

		it( 'should match strings and numbers', () => {
			expect( looseStringEqualityCheck( '123', 123 ) ).toBe( true );
			expect( looseStringEqualityCheck( 123, '123' ) ).toBe( true );
			expect( looseStringEqualityCheck( '456', 456 ) ).toBe( true );
		} );

		it( 'should match booleans with numbers', () => {
			expect( looseStringEqualityCheck( true, 1 ) ).toBe( true );
			expect( looseStringEqualityCheck( 1, true ) ).toBe( true );
			expect( looseStringEqualityCheck( false, 0 ) ).toBe( true );
			expect( looseStringEqualityCheck( 0, false ) ).toBe( true );
		} );

		it( 'should match booleans with strings', () => {
			expect( looseStringEqualityCheck( true, '1' ) ).toBe( true );
			expect( looseStringEqualityCheck( '1', true ) ).toBe( true );
			expect( looseStringEqualityCheck( false, '0' ) ).toBe( true );
			expect( looseStringEqualityCheck( '0', false ) ).toBe( true );
		} );

		it( 'should normalize numeric strings', () => {
			expect( looseStringEqualityCheck( '123.0', 123 ) ).toBe( true );
			expect( looseStringEqualityCheck( '456.00', '456' ) ).toBe( true );
		} );

		it( 'should compare objects via JSON', () => {
			expect( looseStringEqualityCheck( { a: 1 }, { a: 1 } ) ).toBe( true );
			expect( looseStringEqualityCheck( { a: 1 }, { a: 2 } ) ).toBe( false );
			expect( looseStringEqualityCheck( [ 1, 2 ], [ 1, 2 ] ) ).toBe( true );
			expect( looseStringEqualityCheck( [ 1, 2 ], [ 2, 1 ] ) ).toBe( false );
		} );

		it( 'should not match different values', () => {
			expect( looseStringEqualityCheck( 'abc', 'def' ) ).toBe( false );
			expect( looseStringEqualityCheck( 123, 456 ) ).toBe( false );
			expect( looseStringEqualityCheck( true, false ) ).toBe( false );
		} );
	} );

	describe( 'convertStringToArray', () => {
		it( 'should split comma-separated string', () => {
			expect( convertStringToArray( '123,456,789' ) ).toEqual( [ '123', '456', '789' ] );
		} );

		it( 'should trim whitespace', () => {
			expect( convertStringToArray( '123, 456, 789' ) ).toEqual( [ '123', '456', '789' ] );
			expect( convertStringToArray( ' 123 , 456 , 789 ' ) ).toEqual( [ '123', '456', '789' ] );
		} );

		it( 'should return array as-is', () => {
			expect( convertStringToArray( [ '123', '456' ] ) ).toEqual( [ '123', '456' ] );
		} );

		it( 'should handle single value', () => {
			expect( convertStringToArray( '123' ) ).toEqual( [ '123' ] );
			expect( convertStringToArray( 123 ) ).toEqual( [ 123 ] );
			expect( convertStringToArray( 123.45 ) ).toEqual( [ 123.45 ] );
		} );

		it( 'should return empty array for non-string', () => {
			expect( convertStringToArray( null ) ).toEqual( [] );
			expect( convertStringToArray( undefined ) ).toEqual( [] );
		} );

		it( 'should handle empty string', () => {
			expect( convertStringToArray( '' ) ).toEqual( [ '' ] );
		} );
	} );

	describe( 'isValueEmpty', () => {
		it( 'should return true for empty string', () => {
			expect( isValueEmpty( '' ) ).toBe( true );
		} );

		it( 'should return true for null', () => {
			expect( isValueEmpty( null ) ).toBe( true );
		} );

		it( 'should return true for empty array', () => {
			expect( isValueEmpty( [] ) ).toBe( true );
		} );

		it( 'should return true for false', () => {
			expect( isValueEmpty( false ) ).toBe( true );
		} );

		it( 'should return false for non-empty values', () => {
			expect( isValueEmpty( 'value' ) ).toBe( false );
			expect( isValueEmpty( 'null' ) ).toBe( false );
			expect( isValueEmpty( '0' ) ).toBe( false );
			expect( isValueEmpty( 0 ) ).toBe( false );
			expect( isValueEmpty( 1 ) ).toBe( false );
			expect( isValueEmpty( true ) ).toBe( false );
			expect( isValueEmpty( [ 'item' ] ) ).toBe( false );
		} );
	} );

	describe( 'stringComparison', () => {
		describe( 'contains operation', () => {
			it( 'should find substring', () => {
				expect( stringComparison( 'includes', 'word', 'sentence with word in it' ) ).toBe( true );
				expect( stringComparison( 'includes', 'test', 'this is a test' ) ).toBe( true );
			} );

			it( 'should be case-insensitive', () => {
				expect( stringComparison( 'includes', 'WORD', 'word' ) ).toBe( true );
				expect( stringComparison( 'includes', 'word', 'WORD' ) ).toBe( true );
			} );

			it( 'should return false when not found', () => {
				expect( stringComparison( 'includes', 'word', 'no match' ) ).toBe( false );
			} );

			it( 'should return true for empty search', () => {
				expect( stringComparison( 'includes', '', 'anything' ) ).toBe( true );
			} );
		} );

		describe( 'startsWith operation', () => {
			it( 'should match start of string', () => {
				expect( stringComparison( 'startsWith', 'word', 'word starts' ) ).toBe( true );
				expect( stringComparison( 'startsWith', 'test', 'testing' ) ).toBe( true );
			} );

			it( 'should be case-insensitive', () => {
				expect( stringComparison( 'startsWith', 'WORD', 'word' ) ).toBe( true );
			} );

			it( 'should return false when not at start', () => {
				expect( stringComparison( 'startsWith', 'word', 'no word here' ) ).toBe( false );
			} );

			it( 'should return true for empty search', () => {
				expect( stringComparison( 'startsWith', '', 'anything' ) ).toBe( true );
			} );
		} );

		describe( 'endsWith operation', () => {
			it( 'should match end of string', () => {
				expect( stringComparison( 'endsWith', 'word', 'ends with word' ) ).toBe( true );
				expect( stringComparison( 'endsWith', 'test', 'a test' ) ).toBe( true );
			} );

			it( 'should be case-insensitive', () => {
				expect( stringComparison( 'endsWith', 'WORD', 'word' ) ).toBe( true );
			} );

			it( 'should return false when not at end', () => {
				expect( stringComparison( 'endsWith', 'word', 'word at start' ) ).toBe( false );
			} );

			it( 'should return true for empty search', () => {
				expect( stringComparison( 'endsWith', '', 'anything' ) ).toBe( true );
			} );
		} );

		it( 'should return based on includes functionality', () => {
			expect( stringComparison( 'includes', 'word', [ 'word' ] ) ).toBe( true );
			expect( stringComparison( 'includes', [ 'word' ], 'word' ) ).toBe( true );
			expect( stringComparison( 'includes', 'word', [ 'word', 'another' ] ) ).toBe( true );
			expect( stringComparison( 'includes', [ 'word', 'another' ], 'word' ) ).toBe( false );
			expect( stringComparison( 'includes', [ 'word' ], [ 'word' ] ) ).toBe( true );
			expect( stringComparison( 'includes', [ 'word' ], [ 'word', 'another' ] ) ).toBe( true );
			expect( stringComparison( 'includes', [ 'word', 'another' ], [ 'word' ] ) ).toBe( false );
		} );
	} );

	describe( 'regexMatch', () => {
		it( 'should match regex pattern', () => {
			expect( regexMatch( '^[a-z]+$', 'onlyletters' ) ).toBe( true );
			expect( regexMatch( '^\\d+$', '12345' ) ).toBe( true );
		} );

		it( 'should not match when pattern fails', () => {
			expect( regexMatch( '^[a-z]+$', 'Has123' ) ).toBe( false );
			expect( regexMatch( '^\\d+$', 'abc' ) ).toBe( false );
		} );

		it( 'should match partial patterns', () => {
			expect( regexMatch( 'test', 'this is a test' ) ).toBe( true );
			expect( regexMatch( 'test', 'no match' ) ).toBe( false );
		} );

		it( 'should handle arrays - returns true if ANY match', () => {
			expect( regexMatch( '^[a-z]+$', [ 'abc', '123' ] ) ).toBe( true );
			expect( regexMatch( '^[a-z]+$', [ '123', '456' ] ) ).toBe( false );
		} );

		it( 'should return false for non-scalar values', () => {
			expect( regexMatch( 'test', { key: 'test' } ) ).toBe( false );
		} );
	} );

	describe( 'inComparison', () => {
		describe( 'exact = false (ANY match)', () => {
			it( 'should match when value in array', () => {
				expect( inComparison( [ '123', '456' ], '123' ) ).toBe( true );
				expect( inComparison( [ '123', '456' ], '456' ) ).toBe( true );
			} );

			it( 'should not match when value not in array', () => {
				expect( inComparison( [ '123', '456' ], '789' ) ).toBe( false );
			} );

			it( 'should use loose equality', () => {
				expect( inComparison( [ '123', '456' ], 123 ) ).toBe( true );
				expect( inComparison( [ 123, 456 ], '123' ) ).toBe( true );
			} );

			it( 'should handle string ruleValue with array valueToTest', () => {
				expect( inComparison( '123,456', [ '123', '999' ] ) ).toBe( true );
				expect( inComparison( '123,456', [ '999', '000' ] ) ).toBe( false );
			} );

			it( 'should return false for non-scalar valueToTest', () => {
				expect( inComparison( [ '123' ], [ '123' ] ) ).toBe( false );
			} );
		} );

		describe( 'exact = true (ALL match)', () => {
			it( 'should match when ALL items match', () => {
				expect( inComparison( [ '123' ], '123', true ) ).toBe( true );
				expect( inComparison( [ '123', '123' ], '123', true ) ).toBe( true );
			} );

			it( 'should not match when not all items match', () => {
				expect( inComparison( [ '123', '456' ], '123', true ) ).toBe( false );
			} );

			it( 'should handle string ruleValue with array valueToTest', () => {
				expect( inComparison( '123,456', [ '123', '456' ], true ) ).toBe( true );
				expect( inComparison( '123,456', [ '123', '456', '789' ], true ) ).toBe( true );
				expect( inComparison( '123,456,789', [ '123', '456' ], true ) ).toBe( false );
			} );
		} );
	} );

	describe( 'inValuesComparison', () => {
		describe( 'exact = false (ANY match)', () => {
			it( 'should match when rule in array', () => {
				expect( inValuesComparison( '123', [ '123', '456' ] ) ).toBe( true );
				expect( inValuesComparison( '456', [ '123', '456' ] ) ).toBe( true );
			} );

			it( 'should not match when rule not in array', () => {
				expect( inValuesComparison( '789', [ '123', '456' ] ) ).toBe( false );
			} );

			it( 'should use loose equality', () => {
				expect( inValuesComparison( 123, [ '123', '456' ] ) ).toBe( true );
				expect( inValuesComparison( '123', [ 123, 456 ] ) ).toBe( true );
			} );

			it( 'should return false for empty array', () => {
				expect( inValuesComparison( '123', [] ) ).toBe( false );
			} );

			it( 'should return false for non-array valueToTest', () => {
				expect( inValuesComparison( '123', '123' ) ).toBe( false );
				expect( inValuesComparison( '123', 123 ) ).toBe( false );
			} );
		} );

		describe( 'exact = true (ALL match)', () => {
			it( 'should match when ALL values match rule', () => {
				expect( inValuesComparison( '123', [ '123' ], true ) ).toBe( true );
				expect( inValuesComparison( '123', [ '123', '123' ], true ) ).toBe( true );
			} );

			it( 'should not match when not all values match', () => {
				expect( inValuesComparison( '123', [ '123', '456' ], true ) ).toBe( false );
			} );

			it( 'should return true for empty array', () => {
				// Empty array with .every() returns true (vacuous truth)
				expect( inValuesComparison( '123', [], true ) ).toBe( true );
			} );

			it( 'should use loose equality', () => {
				expect( inValuesComparison( 123, [ '123', '123' ], true ) ).toBe( true );
				expect( inValuesComparison( '123', [ 123, 123 ], true ) ).toBe( true );
			} );
		} );
	} );

	describe( 'equalityComparison', () => {
		it( 'should match identical values', () => {
			expect( equalityComparison( '123', '123' ) ).toBe( true );
			expect( equalityComparison( 123, 123 ) ).toBe( true );
		} );

		it( 'should match with type coercion', () => {
			expect( equalityComparison( '123', 123 ) ).toBe( true );
			expect( equalityComparison( 123, '123' ) ).toBe( true );
		} );

		it( 'should match booleans with numbers', () => {
			expect( equalityComparison( true, 1 ) ).toBe( true );
			expect( equalityComparison( 1, true ) ).toBe( true );
			expect( equalityComparison( false, 0 ) ).toBe( true );
			expect( equalityComparison( 0, false ) ).toBe( true );
		} );

		it( 'should match booleans with strings', () => {
			expect( equalityComparison( true, '1' ) ).toBe( true );
			expect( equalityComparison( '1', true ) ).toBe( true );
			expect( equalityComparison( false, '0' ) ).toBe( true );
			expect( equalityComparison( '0', false ) ).toBe( true );
		} );

		it( 'should not match different values', () => {
			expect( equalityComparison( '123', '456' ) ).toBe( false );
			expect( equalityComparison( 123, 456 ) ).toBe( false );
			expect( equalityComparison( true, false ) ).toBe( false );
		} );

		it( 'should match single-item arrays', () => {
			expect( equalityComparison( '123', [ '123' ] ) ).toBe( true );
			expect( equalityComparison( 123, [ 123 ] ) ).toBe( true );
		} );

		it( 'should not match multi-item arrays', () => {
			expect( equalityComparison( '123', [ '123', '456' ] ) ).toBe( false );
		} );

		it( 'should return true for if both are arrays and are the same values', () => {
			expect( equalityComparison( [ '123' ], [ '123' ] ) ).toBe( true );
			expect( equalityComparison( [ '123', '232' ], [ '232', '123' ] ) ).toBe( true );
		} );
	} );

	describe( 'numericComparison', () => {
		describe( 'less than (<)', () => {
			it( 'should return true when ruleValue < valueToTest', () => {
				expect( numericComparison( '<', '100', 99 ) ).toBe( true );
				expect( numericComparison( '<', '100', '99' ) ).toBe( true );
			} );

			it( 'should return false when ruleValue >= valueToTest', () => {
				expect( numericComparison( '<', '100', 100 ) ).toBe( false );
				expect( numericComparison( '<', '100', 101 ) ).toBe( false );
			} );
		} );

		describe( 'less than or equal (<=)', () => {
			it( 'should return true when ruleValue <= valueToTest', () => {
				expect( numericComparison( '<=', '100', 99 ) ).toBe( true );
				expect( numericComparison( '<=', '100', 100 ) ).toBe( true );
			} );

			it( 'should return false when ruleValue > valueToTest', () => {
				expect( numericComparison( '<=', '100', 101 ) ).toBe( false );
			} );
		} );

		describe( 'greater than (>)', () => {
			it( 'should return true when ruleValue > valueToTest', () => {
				expect( numericComparison( '>', '100', 101 ) ).toBe( true );
				expect( numericComparison( '>', '100', '101' ) ).toBe( true );
			} );

			it( 'should return false when ruleValue <= valueToTest', () => {
				expect( numericComparison( '>', '100', 100 ) ).toBe( false );
				expect( numericComparison( '>', '100', 99 ) ).toBe( false );
			} );
		} );

		describe( 'greater than or equal (>=)', () => {
			it( 'should return true when ruleValue >= valueToTest', () => {
				expect( numericComparison( '>=', '100', 101 ) ).toBe( true );
				expect( numericComparison( '>=', '100', 100 ) ).toBe( true );
			} );

			it( 'should return false when ruleValue < valueToTest', () => {
				expect( numericComparison( '>=', '100', 99 ) ).toBe( false );
			} );
		} );

		it( 'should convert strings to numbers', () => {
			expect( numericComparison( '<', '100', '99' ) ).toBe( true );
			expect( numericComparison( '>', '100', '101' ) ).toBe( true );
		} );

		it( 'should return false for non-scalar values', () => {
			expect( numericComparison( '<', '100', [ 99 ] ) ).toBe( false );
			expect( numericComparison( '>', '100', [ 101 ] ) ).toBe( false );
		} );
	} );
} );
