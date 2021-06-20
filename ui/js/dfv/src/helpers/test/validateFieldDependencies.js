import validateFieldDependencies from '../validateFieldDependencies';

describe( 'validateFieldDependencies helper function', () => {
	it( 'correctly validates depends-on dependencies when there is one boolean dependency', () => {
		const podValues = {
			boolean_format_type: 'checkbox',
			boolean_no_label: 'No',
			boolean_yes_label: 'Yes',
			code_max_length: 0,
			restrict_role: true,
		};

		const dependsOnFail = {
			restrict_role: false,
		};

		const dependsOnSuccess = {
			restrict_role: true,
		};

		const dependsOnSuccessWithStringLooseTyping = {
			restrict_role: '1',
		};

		const dependsOnSuccessWithNumberLooseTyping = {
			restrict_role: 1,
		};

		expect( validateFieldDependencies( podValues, dependsOnFail ) ).toBe( false );

		expect( validateFieldDependencies( podValues, dependsOnSuccess ) ).toBe( true );
		expect( validateFieldDependencies( podValues, dependsOnSuccessWithStringLooseTyping ) ).toBe( true );
		expect( validateFieldDependencies( podValues, dependsOnSuccessWithNumberLooseTyping ) ).toBe( true );
	} );

	it( 'correctly validates depends-on dependencies when there are multiple string dependencies', () => {
		const podValuesFirstFail = {
			first_string_dep: 'wrong',
			second_string_dep: 'No',
		};

		const podValuesSecondFail = {
			first_string_dep: 'correct',
			second_string_dep: 'No',
		};

		const podValuesThirdFail = {
			// Mis-matched type is on purpose.
			first_string_dep: true,
			second_string_dep: 'No',
		};

		const podValuesSuccess = {
			first_string_dep: 'correct',
			second_string_dep: 'Yes',
		};

		const dependsOnSuccess = {
			first_string_dep: 'correct',
			second_string_dep: 'Yes',
		};

		expect( validateFieldDependencies( podValuesFirstFail, dependsOnSuccess ) ).toBe( false );
		expect( validateFieldDependencies( podValuesSecondFail, dependsOnSuccess ) ).toBe( false );
		expect( validateFieldDependencies( podValuesThirdFail, dependsOnSuccess ) ).toBe( false );

		expect( validateFieldDependencies( podValuesSuccess, dependsOnSuccess ) ).toBe( true );
	} );
} );
