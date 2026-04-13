import { validateFieldDependencies } from '../validateFieldDependencies';

test( 'invalid mode throws an error', () => {
	expect( () => {
		validateFieldDependencies( {}, {}, 'invalid-mode' );
	} ).toThrow();
} );

test( 'correctly validates depends-on dependencies when there is one boolean dependency', () => {
	const podValuesFail1 = {
		another_value: 'yes',
		something: 'test',
		restrict_role: false,
	};

	const podValuesFail2 = {
		another_value: 'yes',
		something: 'test',
		restrict_role: '0',
	};

	const podValuesFail3 = {
		another_value: 'yes',
		something: 'test',
	};

	const podValuesSuccess1 = {
		another_value: 'yes',
		something: 'test',
		restrict_role: true,
	};

	const podValuesSuccess2 = {
		another_value: 'yes',
		something: 'test',
		restrict_role: 1,
	};

	const podValuesSuccess3 = {
		another_value: 'yes',
		something: 'test',
		restrict_role: '1',
	};

	const dependsOnRules = {
		restrict_role: true,
	};

	expect( validateFieldDependencies( podValuesFail1, dependsOnRules ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail2, dependsOnRules ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail3, dependsOnRules ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess1, dependsOnRules ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess2, dependsOnRules ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess3, dependsOnRules ) ).toEqual( true );
} );

test( 'correctly validates depends-on dependencies when there are multiple string dependencies', () => {
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

	expect( validateFieldDependencies( podValuesFirstFail, dependsOnRules ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesSecondFail, dependsOnRules ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesThirdFail, dependsOnRules ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess, dependsOnRules ) ).toEqual( true );
} );

test( 'correctly validates depends-on-any dependencies', () => {
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

	const dependsOnAnyRules = {
		pick_format_single: 'list',
		pick_format_multi: 'list',
	};

	expect( validateFieldDependencies( podValuesFail1, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail2, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail3, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess1, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess2, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess3, dependsOnAnyRules, 'depends-on-any' ) ).toEqual( true );
} );

test( 'correctly validates excludes-on dependencies', () => {
	const podValuesFail1 = {
		// Fails because of 'a' match.
		string_match: 'a',
		array_match: '1',
	};

	const podValuesFail2 = {
		string_match: 'b',
		// Fails because of '2' match
		array_match: '2',
	};

	const podValuesFail3 = {
		string_match: 'b',
		// Fails because of '3' match
		array_match: '3',
	};

	const podValuesFail4 = {
		// Fails both values
		string_match: 'a',
		array_match: '2',
	};

	const podValuesSuccess1 = {};

	const podValuesSuccess2 = {
		string_match: 'b',
		array_match: '5',
		something_else: 'a',
	};

	const podValuesSuccess3 = {
		string_match: null,
		array_match: '8',
	};

	const excludesOnRules = {
		string_match: 'a',
		array_match: [ '2', '3', '4' ],
	};

	expect( validateFieldDependencies( podValuesFail1, excludesOnRules, 'excludes-on' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail2, excludesOnRules, 'excludes-on' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail3, excludesOnRules, 'excludes-on' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail4, excludesOnRules, 'excludes-on' ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess1, excludesOnRules, 'excludes-on' ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess2, excludesOnRules, 'excludes-on' ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess3, excludesOnRules, 'excludes-on' ) ).toEqual( true );
} );

test( 'correctly validates wildcard-on dependencies', () => {
	const podValuesFail1 = {};

	const podValuesFail2 = {
		pick_object: 'does-not-match',
	};

	const podValuesFail3 = {
		pick_object: 'post_type-custom_css',
	};

	const podValuesSuccess1 = {
		pick_object: 'post_type-custom-cpt',
	};

	const podValuesSuccess2 = {
		pick_object: 'user',
	};

	const wildcardOnRules = {
		pick_object: [
			'^post_type-(?!(custom_css|customize_changeset)).*$',
			'^taxonomy-.*$',
			'^user$',
			'^pod-.*$',
		],
	};

	expect( validateFieldDependencies( podValuesFail1, wildcardOnRules, 'wildcard-on' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail2, wildcardOnRules, 'wildcard-on' ) ).toEqual( false );
	expect( validateFieldDependencies( podValuesFail3, wildcardOnRules, 'wildcard-on' ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess1, wildcardOnRules, 'wildcard-on' ) ).toEqual( true );
	expect( validateFieldDependencies( podValuesSuccess2, wildcardOnRules, 'wildcard-on' ) ).toEqual( true );
} );

test( 'correctly validates an item from an array of values', () => {
	const podValuesFail = {
		first_dep: [ 'first', '' ],
		// second_repeatable_value: [ 'a' ],
	};

	const podValuesSuccess = {
		first_dep: [ 'first', 'second' ],
		// second_repeatable_value: [ 'a' ],
	};

	const dependsOnRules = {
		first_dep: 'second',
		// second_dep: 'a',
	};

	expect( validateFieldDependencies( podValuesFail, dependsOnRules ) ).toEqual( false );

	expect( validateFieldDependencies( podValuesSuccess, dependsOnRules ) ).toEqual( true );
} );

test( 'correctly validates combined depends-on and excludes-on rules', () => {
	const podValuesFail1 = {
		// Fails because depends-on is not met (restrict_role is false)
		restrict_role: false,
		format_type: 'list', // This would pass excludes-on
	};

	const podValuesFail2 = {
		// Fails because excludes-on is not met (format_type matches excluded value)
		restrict_role: true, // This would pass depends-on
		format_type: 'dropdown', // This fails excludes-on
	};

	const podValuesFail3 = {
		// Fails both rules
		restrict_role: false,
		format_type: 'dropdown',
	};

	const podValuesSuccess = {
		// Passes both rules
		restrict_role: true, // Passes depends-on
		format_type: 'list', // Passes excludes-on (not in excluded values)
	};

	const dependsOnRules = {
		restrict_role: true,
	};

	const excludesOnRules = {
		format_type: [ 'dropdown', 'radio' ],
	};

	// Test that both rules must pass for validation to succeed
	expect(
		validateFieldDependencies( podValuesFail1, dependsOnRules ) &&
		validateFieldDependencies( podValuesFail1, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesFail2, dependsOnRules ) &&
		validateFieldDependencies( podValuesFail2, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesFail3, dependsOnRules ) &&
		validateFieldDependencies( podValuesFail3, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesSuccess, dependsOnRules ) &&
		validateFieldDependencies( podValuesSuccess, excludesOnRules, 'excludes-on' )
	).toEqual( true );
} );

test( 'correctly validates combined depends-on-any and excludes-on rules', () => {
	const podValuesFail1 = {
		// Fails because depends-on-any is not met
		pick_format_single: 'different',
		pick_format_multi: 'also different',
		exclude_field: 'allowed', // This would pass excludes-on
	};

	const podValuesFail2 = {
		// Fails because excludes-on is not met
		pick_format_single: 'list', // This passes depends-on-any
		pick_format_multi: 'different',
		exclude_field: 'forbidden', // This fails excludes-on
	};

	const podValuesFail3 = {
		// Fails both rules
		pick_format_single: 'different',
		pick_format_multi: 'also different',
		exclude_field: 'forbidden',
	};

	const podValuesSuccess1 = {
		// Passes both rules
		pick_format_single: 'list', // Passes depends-on-any
		pick_format_multi: 'different',
		exclude_field: 'allowed', // Passes excludes-on
	};

	const podValuesSuccess2 = {
		// Passes both rules with different depends-on-any match
		pick_format_single: 'different',
		pick_format_multi: 'list', // Passes depends-on-any
		exclude_field: 'allowed', // Passes excludes-on
	};

	const dependsOnAnyRules = {
		pick_format_single: 'list',
		pick_format_multi: 'list',
	};

	const excludesOnRules = {
		exclude_field: [ 'forbidden', 'blocked' ],
	};

	// Test that both rules must pass for validation to succeed
	expect(
		validateFieldDependencies( podValuesFail1, dependsOnAnyRules, 'depends-on-any' ) &&
		validateFieldDependencies( podValuesFail1, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesFail2, dependsOnAnyRules, 'depends-on-any' ) &&
		validateFieldDependencies( podValuesFail2, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesFail3, dependsOnAnyRules, 'depends-on-any' ) &&
		validateFieldDependencies( podValuesFail3, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesSuccess1, dependsOnAnyRules, 'depends-on-any' ) &&
		validateFieldDependencies( podValuesSuccess1, excludesOnRules, 'excludes-on' )
	).toEqual( true );

	expect(
		validateFieldDependencies( podValuesSuccess2, dependsOnAnyRules, 'depends-on-any' ) &&
		validateFieldDependencies( podValuesSuccess2, excludesOnRules, 'excludes-on' )
	).toEqual( true );
} );

test( 'correctly validates complex combined rules with multiple dependencies', () => {
	const podValuesFail1 = {
		// Fails depends-on (missing second_dep: true)
		first_dep: 'required',
		second_dep: false,
		exclude_field1: 'allowed',
		exclude_field2: 'also_allowed',
	};

	const podValuesFail2 = {
		// Fails excludes-on (exclude_field1 matches forbidden value)
		first_dep: 'required',
		second_dep: true,
		exclude_field1: 'forbidden',
		exclude_field2: 'allowed',
	};

	const podValuesSuccess = {
		// Passes both rule sets
		first_dep: 'required',
		second_dep: true,
		exclude_field1: 'allowed',
		exclude_field2: 'also_allowed',
	};

	const dependsOnRules = {
		first_dep: 'required',
		second_dep: true,
	};

	const excludesOnRules = {
		exclude_field1: [ 'forbidden', 'blocked' ],
		exclude_field2: 'not_allowed',
	};

	expect(
		validateFieldDependencies( podValuesFail1, dependsOnRules ) &&
		validateFieldDependencies( podValuesFail1, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesFail2, dependsOnRules ) &&
		validateFieldDependencies( podValuesFail2, excludesOnRules, 'excludes-on' )
	).toEqual( false );

	expect(
		validateFieldDependencies( podValuesSuccess, dependsOnRules ) &&
		validateFieldDependencies( podValuesSuccess, excludesOnRules, 'excludes-on' )
	).toEqual( true );
} );
