/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVPostboxContainer = ( props ) => {
	return (
		<div id='postbox-container-1' className='postbox-container pods_floatmenu'>
			<div id='side-info-field' className='inner-sidebar'>
				<div id='side-sortables'>
					<div id='submitdiv' className='postbox pods-no-toggle'>
						<h3><span>Manage{'\u00A0' /* &nbsp; */}
							<small>(<a href='/wp-admin/admin.php?page=pods&amp;action=manage'>« Back to Manage</a>)</small>
						</span></h3>
						<div className='inside'>
							<div className='submitbox' id='submitpost'>
								<div id='major-publishing-actions'>
									<div id='delete-action'>
										<a href='#not-implemented' className='submitdelete deletion pods-confirm'> Delete Pod </a>
									</div>
									<div id='publishing-action'>
										{ ( props.isSaving ) ? <img src='/wp-admin/images/wpspin_light.gif' alt='' /> : null }
										{'\u00A0' /* &nbsp; */}
										<button className='button-primary' type='submit'>Save Pod</button>
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
};
