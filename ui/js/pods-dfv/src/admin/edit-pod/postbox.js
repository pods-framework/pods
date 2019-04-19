/* eslint-disable react/prop-types */
import React from 'react';
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';

const { __ } = wp.i18n;
const { withSelect } = wp.data;

export const Postbox = withSelect( ( select ) => {
	return {
		isSaving: select( STORE_KEY_EDIT_POD ).isSaving()
	};
} )
( ( props ) => {
	return (
		<div id='postbox-container-1' className='postbox-container pods_floatmenu'>
			<div id='side-info-field' className='inner-sidebar'>
				<div id='side-sortables'>
					<div id='submitdiv' className='postbox pods-no-toggle'>
						<h3>
							<span>
								{__( 'Manage', 'pods' )}
								{'\u00A0' /* &nbsp; */}
								<small>
									(<a href='/wp-admin/admin.php?page=pods&amp;action=manage'>
										{__( '« Back to Manage', 'pods' )}
									</a>)
								</small>
							</span>
						</h3>
						<div className='inside'>
							<div className='submitbox' id='submitpost'>
								<div id='major-publishing-actions'>
									<div id='delete-action'>
										<a href='#not-implemented' className='submitdelete deletion pods-confirm'>
											{__( 'Delete Pod', 'pods' )}
										</a>
									</div>
									<div id='publishing-action'>
										<Spinner isSaving={ props.isSaving } />
										{'\u00A0' /* &nbsp; */}
										<button className='button-primary' type='submit'>
											{__( 'Save Pod', 'pods' )}
										</button>
									</div>
									<div className='clear'></div>
								</div>
							</div>
						</div>
					</div>
					<div className='pods-submittable-fields'>
						<div id='side-sortables' className='meta-box-sortables'></div>
					</div>
				</div>
			</div>
		</div>
	);
} );

const Spinner = ( props ) => {
	if ( props.isSaving ) {
		return (
			<img src='/wp-admin/images/wpspin_light.gif' alt='' />
		);
	}

	return null;
};
