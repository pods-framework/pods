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
global.PodsMn = global.Backbone.noConflict();

/*let consoleSpyWarn;
let consoleSpyError;

beforeAll(() => {
	consoleSpyWarn = jest.spyOn(global.console, 'warn').mockImplementation((message) => {
		if (!message.includes('findDOMNode is deprecated') && !message.includes('ReactDOMTestUtils.act')) {
			global.console.warn(message);
		}
	});
	consoleSpyError = jest.spyOn(global.console, 'error').mockImplementation((message) => {
		if (!message.includes('findDOMNode is deprecated') && !message.includes('ReactDOMTestUtils.act')) {
			global.console.error(message);
		}
	});
});

afterAll(() => {
	consoleSpyWarn.mockRestore();
	consoleSpyError.mockRestore();
});*/

/*const consoleError = console.error.bind(console);
const consoleWarn = console.warn.bind(console);
beforeAll(() => {
	console.error = (message, ...args) =>
		!message.toString().includes('findDOMNode is deprecated') && !message.toString().includes('ReactDOMTestUtils.act') && consoleError(message, args)
	console.warn = (message, ...args) =>
		!message.toString().includes('findDOMNode is deprecated') && !message.toString().includes('ReactDOMTestUtils.act') && consoleWarn(message, args)
})
afterAll(() => {
	console.error = consoleError
	console.warn = consoleWarn
})*/
