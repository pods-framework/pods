/* global global */

import lodash from 'lodash';
import React from 'react';

import Backbone from 'backbone';
import * as Mn from 'backbone.marionette';
import underscore from 'underscore';

import Enzyme from 'enzyme';
import Adapter from '@cfaester/enzyme-adapter-react-18';

import {
	combineReducers,
	registerStore,
	select,
	dispatch,
	withSelect,
	withDispatch,
} from '@wordpress/data';

global.React = React;

global.lodash = lodash;
global._ = underscore;

global.wp = {
	data: {
		registerStore,
		combineReducers,
		select,
		dispatch,
		withSelect,
		withDispatch,
	},
};

global.Backbone = Backbone;
global.Backbone.Marionette = Mn;

// @see PodsInit.php
global.PodsMn = global.Backbone.noConflict();

Enzyme.configure({ adapter: new Adapter() });
