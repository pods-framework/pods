/*
 * marionette.state - One-way state architecture for a Marionette.js app.
 * v1.0.1
 */
var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ('value' in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError('Cannot call a class as a function'); } }

(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('underscore'), require('backbone'), require('backbone.marionette')) : typeof define === 'function' && define.amd ? define(['underscore', 'backbone', 'backbone.marionette'], factory) : global.Marionette.State = factory(global._, global.Backbone, global.Mn);
})(this, function (_, Backbone, Mn) {
	'use strict';

	var State = Mn.Object.extend({

		// State model class to instantiate
		modelClass: undefined,

		// Default state attributes hash
		defaultState: undefined,

		// Events from my component
		componentEvents: undefined,

		// State model instance
		_model: undefined,

		// My component, facilitating lifecycle management and event bindings
		_component: undefined,

		// Initial state attributes hash after 'initialState' option and defaults are applied
		_initialState: undefined,

		// options {
		//   initialState: {object} Attributes that will override `defaultState`.  The result of
		//     defaultState + initialState is the state reverted to by `#reset`.
		//   component: {Mn object} Object to which to bind `componentEvents` and also lifecycle;
		//     i.e., when `component` fires 'destroy', then destroy myself.
		//   preventDestroy: {boolean} If true, then this will not destroy on `component` destroy.
		// }
		constructor: function constructor() {
			var _ref = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

			var initialState = _ref.initialState;
			var component = _ref.component;
			var preventDestroy = _ref.preventDestroy;

			Object.defineProperty(this, 'attributes', {
				get: function get() {
					return this._model.attributes;
				},
				set: function set(attributes) {
					this._model.attributes = attributes;
				}
			});

			// State model class is either a class option or is a standard Backbone model
			this.modelClass = this.modelClass || Backbone.Model;

			// Initialize state
			this._initState(initialState);

			if (component) {
				this.bindComponent(component, { preventDestroy: preventDestroy });
			}

			State.__super__.constructor.apply(this, arguments);
		},

		// Initialize model with attrs or reset it, destructively, to conform to attrs.
		_initState: function _initState(attrs) {
			// Set initial state.
			this._initialState = _.extend({}, this.defaultState, attrs);

			// Create new model with initial state.
			/* eslint-disable new-cap */
			this._model = new this.modelClass(this._initialState);
			this._proxyModelEvents(this._model);
		},

		// Return the state model.
		getModel: function getModel() {
			return this._model;
		},

		// Returns the initiate state, which is reverted to by reset()
		getInitialState: function getInitialState() {
			return _.clone(this._initialState);
		},

		// Proxy to model get().
		get: function get(attr) {
			return this._model.get(attr);
		},

		// Proxy to model set().
		set: function set(key, val, options) {
			this._model.set(key, val, options);
			return this;
		},

		// Return state to its initial value.
		// If `attrs` is provided, they will override initial values for a "partial" reset.
		// Initial state will remain unchanged regardless of override attributes.
		reset: function reset(attrs, options) {
			var resetAttrs = _.extend({}, this._initialState, attrs);
			this._model.set(resetAttrs, options);
			return this;
		},

		// Proxy to model changedAttributes().
		changedAttributes: function changedAttributes() {
			return this._model.changedAttributes();
		},

		// Proxy to model previous().
		previous: function previous(attr) {
			return this._model.previous(attr);
		},

		// Proxy to model previousAttributes().
		previousAttributes: function previousAttributes() {
			return this._model.previousAttributes();
		},

		// Whether any of the passed attributes were changed during the last modification
		hasAnyChanged: function hasAnyChanged() {
			for (var _len = arguments.length, attrs = Array(_len), _key = 0; _key < _len; _key++) {
				attrs[_key] = arguments[_key];
			}

			return State.hasAnyChanged.apply(State, [this].concat(attrs));
		},

		toJSON: function toJSON() {
			return this._model.toJSON();
		},

		// Bind `componentEvents` to `component` and cascade destroy to self when component fires
		// 'destroy'.  To prevent self-destroy behavior, pass `preventDestroy: true` as an option.
		bindComponent: function bindComponent(component) {
			var _ref2 = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

			var preventDestroy = _ref2.preventDestroy;

			this.bindEntityEvents(component, this.componentEvents);
			if (!preventDestroy) {
				this.listenTo(component, 'destroy', this.destroy);
			}
		},

		// Unbind `componentEvents` from `component` and stop listening to component 'destroy' event.
		unbindComponent: function unbindComponent(component) {
			this.unbindEntityEvents(component, this.componentEvents);
			this.stopListening(component, 'destroy', this.destroy);
		},

		// Proxy to StateFunctions#syncEntityEvents.
		syncEntityEvents: function syncEntityEvents(entity, entityEvents, event) {
			State.syncEntityEvents(this, entity, entityEvents, event);
			return this;
		},

		// Convert model events to state events
		_proxyModelEvents: function _proxyModelEvents(other) {
			this.listenTo(other, 'all', function () {
				if (arguments.length > 1 && arguments[1] === this._model) {
					// Replace model argument with State
					arguments[1] = this;
				}
				this.trigger.apply(this, arguments);
			});
		}
	});

	var state = State;

	var state_functions = Object.defineProperties({}, {
		sync: {
			get: function get() {
				return sync;
			},
			configurable: true,
			enumerable: true
		},
		syncEntityEvents: {
			get: function get() {
				return syncEntityEvents;
			},
			configurable: true,
			enumerable: true
		},
		hasAnyChanged: {
			get: function get() {
				return hasAnyChanged;
			},
			configurable: true,
			enumerable: true
		}
	});

	var modelEventMatcher = /^(?:all|change|change:(.+))$/;
	var collectionEventMatcher = /^(?:all|reset)$/;
	var spaceMatcher = /\s+/;

	// Sync individual event binding 'event1' => 'handler1 handler2'.
	function syncBinding(target, entity, event, handlers) {
		var changeOpts = { syncing: true };
		var modelEventMatch;

		// Only certain model/collection events are syncable.
		var collectionMatch = entity instanceof Backbone.Collection && event.match(collectionEventMatcher);
		var modelMatch = (entity instanceof Backbone.Model || entity instanceof state) && (modelEventMatch = event.match(modelEventMatcher));
		if (!collectionMatch && !modelMatch) {
			return;
		}

		// Collect change event arguments.
		var changeArgs = [entity];
		var changeAttr;
		if (modelEventMatch && (changeAttr = modelEventMatch[1])) {
			changeArgs.push(entity.get(changeAttr));
		}
		changeArgs.push(changeOpts);

		// Call change event handler.
		if (_.isFunction(handlers)) {
			handlers.apply(target, changeArgs);
		} else {
			var handlerKeys = handlers.split(spaceMatcher);
			for (var i = 0; i < handlerKeys.length; i++) {
				var handlerKey = handlerKeys[i];
				target[handlerKey].apply(target, changeArgs);
			}
		}
	}

	// Sync bindings hash { 'event1 event 2': 'handler1 handler2' }.
	function sync(target, entity, bindings) {
		if (!entity) {
			throw new Error('`entity` must be provided.');
		}
		if (!bindings) {
			throw new Error('`bindings` must be provided.');
		}
		for (var eventStr in bindings) {
			var handlers = bindings[eventStr];
			var events = eventStr.split(spaceMatcher);
			for (var i = 0; i < events.length; i++) {
				var event = events[i];
				syncBinding(target, entity, event, handlers);
			}
		}
	}

	// A stoppable handle on the syncing listener

	var Syncing = (function () {
		function Syncing(target, entity, bindings) {
			_classCallCheck(this, Syncing);

			this.target = target;
			this.entity = entity;
			this.bindings = bindings;
		}

		// Binds events handlers located on target to an entity using Marionette.bindEntityEvents, and
		// also "syncs" initial state either immediately or whenever target fires a specific event.
		//
		// Initial state is synced by calling certain handlers at a precise moment.  Only the following
		// entity events will sync their handlers: 'all', 'change', 'change:attr', and 'reset'.
		//
		// Returns a Syncing instance.  While syncing handlers are unbound on target destroy, the syncing
		// instance has a single public method stop() for ceasing syncing on target events early.

		_createClass(Syncing, [{
			key: 'stop',
			value: function stop() {
				Mn.unbindEntityEvents(this.target, this.entity, this.bindings);
				this.target.off(this.event, this.handler);
				this.event = this.handler = null;
			}
		}, {
			key: '_when',
			value: function _when(event) {
				Mn.bindEntityEvents(this.target, this.entity, this.bindings);
				this.event = event;
				this.handler = _.bind(sync, this, this.target, this.entity, this.bindings);
				this.target.on(this.event, this.handler).on('destroy', _.bind(this.stop, this));
			}
		}, {
			key: '_now',
			value: function _now() {
				Mn.bindEntityEvents(this.target, this.entity, this.bindings);
				sync(this.target, this.entity, this.bindings);
			}
		}]);

		return Syncing;
	})();

	function syncEntityEvents(target, entity, bindings, event) {
		var syncing = new Syncing(target, entity, bindings);
		if (event) {
			syncing._when(event);
		} else {
			syncing._now();
		}
		return syncing;
	}

	// Determine if any of the passed attributes were changed during the last modification of `model`.
	function hasAnyChanged(model) {
		// Support Marionette.State or Backbone.Model performantly.
		if (model._model) {
			model = model._model;
		}

		for (var _len2 = arguments.length, attrs = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
			attrs[_key2 - 1] = arguments[_key2];
		}

		return !!_.chain(model.changed).keys().intersection(attrs).size().value();
	}

	_.extend(state, state_functions);

	var index = state;

	return index;
});
