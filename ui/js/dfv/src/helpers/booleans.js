export const toBool = ( stringOrNumber ) => {
	// Force any strings to numeric first
	return !! ( +stringOrNumber );
};

export const toNumericBool = ( boolValue ) => {
	return ( !! boolValue && '0' !== boolValue ) ? '1' : '0';
};
