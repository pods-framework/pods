import sanitizeSlug from '../sanitizeSlug';

describe( 'sanitizeSlug', () => {
	it( 'strips HTML markup ', () => {
		const value = 'string with <strong>some</strong> markup and <script></script>an attack';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toEqual( 'string_with_some_markup_and_an_attack' );
	} );

	it( 'forces lowercase and replaces spaces with underscores', () => {
		const value = 'Capitalized Words and an ALLCAPS word';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toEqual( 'capitalized_words_and_an_allcaps_word' );
	} );

	it( 'forces lowercase and replaces spaces with dashes and keeps underscores', () => {
		const value = 'Capitalized Words and an ALLCAPS_word';
		const secondValue = 'Pod name with spaces';

		const sanitizedValue = sanitizeSlug( value, '-' );
		const secondSanitizedValue = sanitizeSlug( secondValue );

		expect( sanitizedValue ).toEqual( 'capitalized-words-and-an-allcaps_word' );
		expect( secondSanitizedValue ).toEqual( 'pod_name_with_spaces' );
	} );

	it( 'removes invalid characters', () => {
		const value = 'Test )*&^*😬and*()*)**&^*^# Test';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toEqual( 'test_and_test' );
	} );
} );
