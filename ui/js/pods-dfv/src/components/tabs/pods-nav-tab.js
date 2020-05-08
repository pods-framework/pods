import React from 'react';
import * as PropTypes from 'prop-types';
import classNames from 'classnames';

export const PodsNavTab = ( { tabs, activeTab, setActiveTab } ) => {
	const getClassName = ( tabName ) => {
		return classNames( 'nav-tab pods-nav-tab-link', {
			'nav-tab-active': tabName === activeTab,
		} );
	};

	const handleClick = ( e, tabName ) => {
		e.preventDefault();
		setActiveTab( tabName );
	};

	return (
		<h2 className="nav-tab-wrapper pods-nav-tabs">
			{ tabs.map( ( thisTab ) => (
				<a
					key={ thisTab.name }
					href={ `#pods-${ thisTab.name }` }
					className={ getClassName( thisTab.name ) }
					onClick={ ( e ) => handleClick( e, thisTab.name ) }
				>
					{ thisTab.titleText }
				</a>
			) ) }
		</h2>
	);
};

PodsNavTab.propTypes = {
	tabs: PropTypes.arrayOf(
		PropTypes.shape( {
			name: PropTypes.string,
			titleText: PropTypes.string,
		} )
	).isRequired,
	activeTab: PropTypes.string.isRequired,
	setActiveTab: PropTypes.func.isRequired,
};
