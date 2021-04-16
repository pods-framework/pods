/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import BooleanGroup from '..';

const BASE_PROPS = {
	value: '',
	setValue: jest.fn(),
	addValidationRules: jest.fn(),
	fieldConfig: {
		group: 'group/pod/_pods_pod/dfv-demo',
		id: 'some_id',
		label: 'Test Boolean Group Field',
		name: 'test_boolean_group_field',
		object_type: 'field',
		parent: 'pod/_pods_pod',
		type: 'boolean_group',
		allPodValues: {
			type: 'currency',
		},
		boolean_group: [
			{
				name: 'admin_only',
				label: 'Restrict access to Admins',
				default: 0,
				type: 'boolean',
				dependency: true,
				help: 'Some help text.',
			},
			{
				name: 'restrict_role',
				label: 'Restrict access by Role',
				default: 0,
				type: 'boolean',
				dependency: true,
			},
			{
				name: 'read_only',
				label: 'Make field "Read Only" in UI',
				default: 0,
				type: 'boolean',
				help: 'This option is overridden by access restrictions. If the user does not have access to edit this field, it will be read only. If no access restrictions are set, this field will always be read only.',
				'depends-on': {
					type: [
						'boolean',
						'color',
						'currency',
						'date',
						'datetime',
						'email',
						'number',
						'paragraph',
						'password',
						'phone',
						'slug',
						'text',
						'time',
						'website',
					],
				},
			},
		],
	},
};
