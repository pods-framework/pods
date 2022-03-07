import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

const PodsNavTab = ( { tabs, activeTab, setActiveTab } ) => {
	const getClassName = ( tabName ) => {
		return classNames(
			'nav-tab pods-nav-tab-link',
			{ 'nav-tab-active': ( tabName === activeTab ) },
		);
	};

	const handleClick = ( e, tabName ) => {
		e.preventDefault();
		setActiveTab( tabName );
	};

	return (
		<h2 className="nav-tab-wrapper pods-nav-tabs">
			{ tabs.map( ( { name, label } ) => (
				<a
					key={ name }
					href={ `#pods-${ name }` }
					className={ getClassName( name ) }
					onClick={ ( e ) => handleClick( e, name ) }
				>
					{ label }
				</a>
			) ) }
		</h2>
	);
};

PodsNavTab.propTypes = {
	tabs: PropTypes.arrayOf( PropTypes.shape( {
		name: PropTypes.string.isRequired,
		label: PropTypes.string.isRequired,
	} ) ).isRequired,
	activeTab: PropTypes.string.isRequired,
	setActiveTab: PropTypes.func.isRequired,
};

export default PodsNavTab;
