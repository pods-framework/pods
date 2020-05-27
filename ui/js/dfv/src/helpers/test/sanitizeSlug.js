import sanitizeSlug from '../sanitizeSlug';

describe( 'sanitizeSlug', () => {
	it( 'strips HTML markup ', () => {
		const value = 'string with <strong>some</strong> markup and <script></script>an attack';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toBe( 'string_with_some_markup_and_an_attack' );
	} );

	it( 'forces lowercase and replaces spaces with underscores', () => {
		const value = 'Capitalized Words and an ALLCAPS word';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toBe( 'capitalized_words_and_an_allcaps_word' );
	} );

	it( 'removes invalid characters', () => {
		const value = 'Test )*&^*😬and*()*)**&^*^# Test';

		const sanitizedValue = sanitizeSlug( value );

		expect( sanitizedValue ).toBe( 'test_and_test' );
	} );
} );
