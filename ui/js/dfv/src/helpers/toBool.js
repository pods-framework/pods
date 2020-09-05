const toBool = ( stringOrNumber ) => {
	// Force any strings to numeric first
	return !! ( +stringOrNumber );
};

export default toBool;
