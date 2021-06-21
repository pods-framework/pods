import validateFieldDependencies from '../validateFieldDependencies';

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

	expect( validateFieldDependencies( podValuesFail1, dependsOnRules ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail2, dependsOnRules ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail3, dependsOnRules ) ).toBe( false );

	expect( validateFieldDependencies( podValuesSuccess1, dependsOnRules ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess2, dependsOnRules ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess3, dependsOnRules ) ).toBe( true );
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

	expect( validateFieldDependencies( podValuesFirstFail, dependsOnRules ) ).toBe( false );
	expect( validateFieldDependencies( podValuesSecondFail, dependsOnRules ) ).toBe( false );
	expect( validateFieldDependencies( podValuesThirdFail, dependsOnRules ) ).toBe( false );

	expect( validateFieldDependencies( podValuesSuccess, dependsOnRules ) ).toBe( true );
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

	expect( validateFieldDependencies( podValuesFail1, dependsOnAnyRules, 'depends-on-any' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail2, dependsOnAnyRules, 'depends-on-any' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail3, dependsOnAnyRules, 'depends-on-any' ) ).toBe( false );

	expect( validateFieldDependencies( podValuesSuccess1, dependsOnAnyRules, 'depends-on-any' ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess2, dependsOnAnyRules, 'depends-on-any' ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess3, dependsOnAnyRules, 'depends-on-any' ) ).toBe( true );
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

	expect( validateFieldDependencies( podValuesFail1, excludesOnRules, 'excludes-on' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail2, excludesOnRules, 'excludes-on' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail3, excludesOnRules, 'excludes-on' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail4, excludesOnRules, 'excludes-on' ) ).toBe( false );

	expect( validateFieldDependencies( podValuesSuccess1, excludesOnRules, 'excludes-on' ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess2, excludesOnRules, 'excludes-on' ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess3, excludesOnRules, 'excludes-on' ) ).toBe( true );
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

	expect( validateFieldDependencies( podValuesFail1, wildcardOnRules, 'wildcard-on' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail2, wildcardOnRules, 'wildcard-on' ) ).toBe( false );
	expect( validateFieldDependencies( podValuesFail3, wildcardOnRules, 'wildcard-on' ) ).toBe( false );

	expect( validateFieldDependencies( podValuesSuccess1, wildcardOnRules, 'wildcard-on' ) ).toBe( true );
	expect( validateFieldDependencies( podValuesSuccess2, wildcardOnRules, 'wildcard-on' ) ).toBe( true );
} );
