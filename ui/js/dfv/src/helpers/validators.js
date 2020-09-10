import { __, sprintf } from '@wordpress/i18n';

export const requiredValidator = ( fieldLabel ) => ( value ) => {
	if ( ! value ) {
		// translators: %s is the Field label of the required field.
		throw sprintf( __( '%s is required.', 'pods' ), fieldLabel );
	}

	return true;
};

export const maxValidator = ( maxValue ) => ( value ) => {
	if ( parseFloat( value ) > parseFloat( maxValue ) ) {
		// translators: %s is the maximum number value allowed.
		throw sprintf( __( 'Exceeds the maximum value of %s.', 'pods' ), maxValue );
	}

	return true;
};

export const minValidator = ( minValue ) => ( value ) => {
	if ( parseFloat( value ) < parseFloat( minValue ) ) {
		// translators: %s is the minimum number value allowed.
		throw sprintf( __( 'Below the minimum value of %s.', 'pods' ), minValue );
	}

	return true;
};

export const emailValidator = () => ( value ) => {
	const EMAIL_REGEX = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	if ( ! value || ! value.match( EMAIL_REGEX ) ) {
		throw __( 'Invalid email address format.', 'pods' );
	}

	return true;
};
