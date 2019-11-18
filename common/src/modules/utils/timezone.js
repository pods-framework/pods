/**
 * External dependencies
 */
import { find, flatten, map, get } from 'lodash';

/**
 * Internal dependencies
 */
import { timezoneHtml } from '@moderntribe/common/utils/globals';

/**
 * Module Code
 */

let timezoneOpts;

export const getTimezoneOpts = () => {
	// Verify if we have it in cache solved
	if ( timezoneOpts ) {
		return timezoneOpts;
	}

	const $timezoneOpts = jQuery( timezoneHtml() );
	const groups = [];
	let number = 0;

	$timezoneOpts.each( ( index, item ) => {
		const $group = jQuery( item );

		if ( ! $group.is( 'optgroup' ) ) {
			return;
		}

		number++;

		const label = $group.attr( 'label' );
		const group = {
			key: label,
			text: label,
			options: [],
		};

		$group.find( 'option' ).each( ( optIndex, optionEl ) => {
			number++;

			const $option = jQuery( optionEl );
			group.options.push( {
				key: $option.val(),
				text: $option.text(),
				index: number,
			} );
		} );

		groups.push( group );
	} );

	// Save it in a cache
	timezoneOpts = groups;

	return groups;
}

export const getItems = ( searchFor ) => {
	const groups = getTimezoneOpts();

	if ( searchFor ) {
		const opts = flatten( map( groups, 'options' ) );
		return find( opts, searchFor );
	}

	return groups;
}
