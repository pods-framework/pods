/* global global */

import lodash from 'lodash';
import React from 'react';

import Backbone from 'backbone';
import * as Mn from 'backbone.marionette';
import underscore from 'underscore';

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
global.PodsMn = Backbone.Marionette.noConflict();
