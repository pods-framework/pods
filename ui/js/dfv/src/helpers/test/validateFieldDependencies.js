import validateFieldDependencies from '../validateFieldDependencies';

// @todo write tests for 'wildcard' and 'excludes' modes

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

		const dependsOnRules = {
			restrict_role: true,
		};

		const dependsOnRulesWithStringLooseTyping = {
			restrict_role: '1',
		};

		const dependsOnRulesWithNumberLooseTyping = {
			restrict_role: 1,
		};

		expect( validateFieldDependencies( podValues, dependsOnFail ) ).toBe( false );

		expect( validateFieldDependencies( podValues, dependsOnRules ) ).toBe( true );
		expect( validateFieldDependencies( podValues, dependsOnRulesWithStringLooseTyping ) ).toBe( true );
		expect( validateFieldDependencies( podValues, dependsOnRulesWithNumberLooseTyping ) ).toBe( true );
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

		const dependsOnRules = {
			first_string_dep: 'correct',
			second_string_dep: 'Yes',
		};

		expect( validateFieldDependencies( podValuesFirstFail, dependsOnRules ) ).toBe( false );
		expect( validateFieldDependencies( podValuesSecondFail, dependsOnRules ) ).toBe( false );
		expect( validateFieldDependencies( podValuesThirdFail, dependsOnRules ) ).toBe( false );

		expect( validateFieldDependencies( podValuesSuccess, dependsOnRules ) ).toBe( true );
	} );

	it( 'correctly validates depends-on-any dependencies', () => {
		const podValuesFail1 = {};

		const podValuesFail2 = {
			some_irrelevant_field: 'something',
		};

		const podValuesFail3 = {
			pick_format_single: 'different',
			pick_format_multi: 'also different',
		};

		const podValuesSuccess1 = {
			pick_format_single: 'list',
			pick_format_multi: 'wrong',
		};

		const podValuesSuccess2 = {
			pick_format_single: 'wrong',
			pick_format_multi: 'list',
		};

		const podValuesSuccess3 = {
			pick_format_single: 'list',
			pick_format_multi: 'list',
		};

		const dependsOnRules = {
			pick_format_single: 'list',
			pick_format_multi: 'list',
		};

		expect( validateFieldDependencies( podValuesFail1, dependsOnRules, 'depends-on-any' ) ).toBe( false );
		expect( validateFieldDependencies( podValuesFail2, dependsOnRules, 'depends-on-any' ) ).toBe( false );
		expect( validateFieldDependencies( podValuesFail3, dependsOnRules, 'depends-on-any' ) ).toBe( false );

		expect( validateFieldDependencies( podValuesSuccess1, dependsOnRules, 'depends-on-any' ) ).toBe( true );
		expect( validateFieldDependencies( podValuesSuccess2, dependsOnRules, 'depends-on-any' ) ).toBe( true );
		expect( validateFieldDependencies( podValuesSuccess3, dependsOnRules, 'depends-on-any' ) ).toBe( true );
	} );
} );
