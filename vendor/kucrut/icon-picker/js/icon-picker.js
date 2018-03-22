/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 15);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

wp.media.model.IconPickerTarget = __webpack_require__(5);
wp.media.model.IconPickerFonts = __webpack_require__(4);

wp.media.controller.iconPickerMixin = __webpack_require__(3);
wp.media.controller.IconPickerFont = __webpack_require__(1);
wp.media.controller.IconPickerImg = __webpack_require__(2);

wp.media.view.IconPickerBrowser = __webpack_require__(6);
wp.media.view.IconPickerSidebar = __webpack_require__(13);
wp.media.view.IconPickerFontItem = __webpack_require__(9);
wp.media.view.IconPickerFontLibrary = __webpack_require__(10);
wp.media.view.IconPickerFontFilter = __webpack_require__(8);
wp.media.view.IconPickerFontBrowser = __webpack_require__(7);
wp.media.view.IconPickerImgBrowser = __webpack_require__(12);
wp.media.view.IconPickerSvgItem = __webpack_require__(14);
wp.media.view.MediaFrame.IconPicker = __webpack_require__(11);

/***/ }),
/* 1 */
/***/ (function(module, exports) {

/**
 * wp.media.controller.IconPickerFont
 *
 * @class
 * @augments wp.media.controller.State
 * @augments Backbone.Model
 * @mixes    wp.media.controller.iconPickerMixin
 */
var IconPickerFont = wp.media.controller.State.extend(_.extend({}, wp.media.controller.iconPickerMixin, {
	defaults: {
		multiple: false,
		menu: 'default',
		toolbar: 'select',
		baseType: 'font'
	},

	initialize: function initialize() {
		var data = this.get('data');

		this.set('groups', new Backbone.Collection(data.groups));
		this.set('library', new wp.media.model.IconPickerFonts(data.items));
		this.set('selection', new wp.media.model.Selection(null, {
			multiple: this.get('multiple')
		}));
	},

	activate: function activate() {
		this.frame.on('open', this.updateSelection, this);
		this.resetFilter();
		this.updateSelection();
	},

	deactivate: function deactivate() {
		this.frame.off('open', this.updateSelection, this);
	},

	resetFilter: function resetFilter() {
		this.get('library').props.set('group', 'all');
	},

	updateSelection: function updateSelection() {
		var selection = this.get('selection'),
		    library = this.get('library'),
		    target = this.frame.target,
		    icon = target.get('icon'),
		    type = target.get('type'),
		    selected;

		if (this.id === type) {
			selected = library.findWhere({ id: icon });
		}

		selection.reset(selected ? selected : null);
	},

	getContentView: function getContentView() {
		return new wp.media.view.IconPickerFontBrowser(_.extend({
			controller: this.frame,
			model: this,
			groups: this.get('groups'),
			collection: this.get('library'),
			selection: this.get('selection'),
			baseType: this.get('baseType'),
			type: this.get('id')
		}, this.ipGetSidebarOptions()));
	}
}));

module.exports = IconPickerFont;

/***/ }),
/* 2 */
/***/ (function(module, exports) {

var Library = wp.media.controller.Library,
    l10n = wp.media.view.l10n,
    models = wp.media.model,
    views = wp.media.view,
    IconPickerImg;

/**
 * wp.media.controller.IconPickerImg
 *
 * @augments wp.media.controller.Library
 * @augments wp.media.controller.State
 * @augments Backbone.Model
 * @mixes    media.selectionSync
 * @mixes    wp.media.controller.iconPickerMixin
 */
IconPickerImg = Library.extend(_.extend({}, wp.media.controller.iconPickerMixin, {
	defaults: _.defaults({
		id: 'image',
		baseType: 'image',
		syncSelection: false
	}, Library.prototype.defaults),

	initialize: function initialize(options) {
		var selection = this.get('selection');

		this.options = options;

		this.set('library', wp.media.query({ type: options.data.mimeTypes }));

		this.routers = {
			upload: {
				text: l10n.uploadFilesTitle,
				priority: 20
			},
			browse: {
				text: l10n.mediaLibraryTitle,
				priority: 40
			}
		};

		if (!(selection instanceof models.Selection)) {
			this.set('selection', new models.Selection(selection, {
				multiple: false
			}));
		}

		Library.prototype.initialize.apply(this, arguments);
	},

	activate: function activate() {
		Library.prototype.activate.apply(this, arguments);
		this.get('library').observe(wp.Uploader.queue);
		this.frame.on('open', this.updateSelection, this);
		this.updateSelection();
	},

	deactivate: function deactivate() {
		Library.prototype.deactivate.apply(this, arguments);
		this.get('library').unobserve(wp.Uploader.queue);
		this.frame.off('open', this.updateSelection, this);
	},

	getContentView: function getContentView(mode) {
		var content = mode === 'upload' ? this.uploadContent() : this.browseContent();

		this.frame.$el.removeClass('hide-toolbar');

		return content;
	},

	/**
  * Media library content
  *
  * @returns {wp.media.view.IconPickerImgBrowser} "Browse" content view.
  */
	browseContent: function browseContent() {
		var options = _.extend({
			model: this,
			controller: this.frame,
			collection: this.get('library'),
			selection: this.get('selection'),
			sortable: this.get('sortable'),
			search: this.get('searchable'),
			filters: this.get('filterable'),
			dragInfo: this.get('dragInfo'),
			idealColumnWidth: this.get('idealColumnWidth'),
			suggestedWidth: this.get('suggestedWidth'),
			suggestedHeight: this.get('suggestedHeight')
		}, this.ipGetSidebarOptions());

		if (this.id === 'svg') {
			options.AttachmentView = views.IconPickerSvgItem;
		}

		return new views.IconPickerImgBrowser(options);
	},

	/**
  * Render callback for the content region in the `upload` mode.
  *
  * @returns {wp.media.view.UploaderInline} "Upload" content view.
  */
	uploadContent: function uploadContent() {
		return new wp.media.view.UploaderInline({
			controller: this.frame
		});
	},

	updateSelection: function updateSelection() {
		var selection = this.get('selection'),
		    target = this.frame.target,
		    icon = target.get('icon'),
		    type = target.get('type'),
		    selected;

		if (this.id === type) {
			selected = models.Attachment.get(icon);
			this.dfd = selected.fetch();
		}

		selection.reset(selected ? selected : null);
	},

	/**
  * Get image icon URL
  *
  * @param  {object} model - Selected icon model.
  * @param  {string} size  - Image size.
  *
  * @returns {string} Icon URL.
  */
	ipGetIconUrl: function ipGetIconUrl(model, size) {
		var url = model.get('url'),
		    sizes = model.get('sizes');

		if (undefined === size) {
			size = 'thumbnail';
		}

		if (sizes && sizes[size]) {
			url = sizes[size].url;
		}

		return url;
	}
}));

module.exports = IconPickerImg;

/***/ }),
/* 3 */
/***/ (function(module, exports) {

/**
 * Methods for the state
 *
 * @mixin
 */
var iconPickerMixin = {

	/**
  * @returns {object}
  */
	ipGetSidebarOptions: function ipGetSidebarOptions() {
		var frameOptions = this.frame.options,
		    options = {};

		if (frameOptions.SidebarView && frameOptions.SidebarView.prototype instanceof wp.media.view.IconPickerSidebar) {
			options.sidebar = true;
			options.SidebarView = frameOptions.SidebarView;
		} else {
			options.sidebar = false;
		}

		return options;
	},

	/**
  * Get image icon URL
  *
  * @returns {string}
  */
	ipGetIconUrl: function ipGetIconUrl() {
		return '';
	}
};

module.exports = iconPickerMixin;

/***/ }),
/* 4 */
/***/ (function(module, exports) {

/**
 * wp.media.model.IconPickerFonts
 */
var IconPickerFonts = Backbone.Collection.extend({
	constructor: function constructor() {
		Backbone.Collection.prototype.constructor.apply(this, arguments);

		this.items = new Backbone.Collection(this.models);
		this.props = new Backbone.Model({
			group: 'all',
			search: ''
		});

		this.props.on('change', this.refresh, this);
	},

	/**
  * Refresh library when props is changed
  *
  * @param {Backbone.Model} props
  */
	refresh: function refresh(props) {
		var _this = this;

		var items = _.clone(this.items.models);

		_.each(props.toJSON(), function (value, filter) {
			var method = _this.filters[filter];

			if (method) {
				items = items.filter(function (item) {
					return method(item, value);
				});
			}
		});

		this.reset(items);
	},

	filters: {
		/**
   * @static
   *
   * @param {Backbone.Model} item  Item model.
   * @param {string}         group Group ID.
   *
   * @returns {Boolean}
   */
		group: function group(item, _group) {
			return _group === 'all' || item.get('group') === _group || item.get('group') === '';
		},

		/**
   * @static
   *
   * @param {Backbone.Model} item Item model.
   * @param {string}         term Search term.
   *
   * @returns {Boolean}
   */
		search: function search(item, term) {
			var result = void 0;

			if (term === '') {
				result = true;
			} else {
				result = _.any(['id', 'name'], function (attribute) {
					var value = item.get(attribute);

					return value && value.search(term) >= 0;
				}, term);
			}

			return result;
		}
	}
});

module.exports = IconPickerFonts;

/***/ }),
/* 5 */
/***/ (function(module, exports) {

/**
 * wp.media.model.IconPickerTarget
 *
 * A target where the picked icon should be sent to
 *
 * @augments Backbone.Model
 */
var IconPickerTarget = Backbone.Model.extend({
	defaults: {
		type: '',
		group: 'all',
		icon: '',
		url: '',
		sizes: []
	}
});

module.exports = IconPickerTarget;

/***/ }),
/* 6 */
/***/ (function(module, exports) {

/**
 * Methods for the browser views
 */
var IconPickerBrowser = {
	createSidebar: function createSidebar() {
		this.sidebar = new this.options.SidebarView({
			controller: this.controller,
			selection: this.options.selection
		});

		this.views.add(this.sidebar);
	}
};

module.exports = IconPickerBrowser;

/***/ }),
/* 7 */
/***/ (function(module, exports) {

/**
 * wp.media.view.IconPickerFontBrowser
 */
var IconPickerFontBrowser = wp.media.View.extend(_.extend({
	className: function className() {
		var className = 'attachments-browser iconpicker-fonts-browser';

		if (!this.options.sidebar) {
			className += ' hide-sidebar';
		}

		return className;
	},

	initialize: function initialize() {
		this.groups = this.options.groups;

		this.createToolbar();
		this.createLibrary();

		if (this.options.sidebar) {
			this.createSidebar();
		}
	},

	createLibrary: function createLibrary() {
		this.items = new wp.media.view.IconPickerFontLibrary({
			controller: this.controller,
			collection: this.collection,
			selection: this.options.selection,
			baseType: this.options.baseType,
			type: this.options.type
		});

		// Add keydown listener to the instance of the library view
		this.items.listenTo(this.controller, 'attachment:keydown:arrow', this.items.arrowEvent);
		this.items.listenTo(this.controller, 'attachment:details:shift-tab', this.items.restoreFocus);

		this.views.add(this.items);
	},

	createToolbar: function createToolbar() {
		this.toolbar = new wp.media.view.Toolbar({
			controller: this.controller
		});

		this.views.add(this.toolbar);

		// Dropdown filter
		this.toolbar.set('filters', new wp.media.view.IconPickerFontFilter({
			controller: this.controller,
			model: this.collection.props,
			priority: -80
		}).render());

		// Search field
		this.toolbar.set('search', new wp.media.view.Search({
			controller: this.controller,
			model: this.collection.props,
			priority: 60
		}).render());
	}
}, wp.media.view.IconPickerBrowser));

module.exports = IconPickerFontBrowser;

/***/ }),
/* 8 */
/***/ (function(module, exports) {

/**
 * wp.media.view.IconPickerFontFilter
 */
var IconPickerFontFilter = wp.media.view.AttachmentFilters.extend({
	createFilters: function createFilters() {
		var groups = this.controller.state().get('groups'),
		    filters = {};

		filters.all = {
			text: wp.media.view.l10n.iconPicker.allFilter,
			props: { group: 'all' }
		};

		groups.each(function (group) {
			filters[group.id] = {
				text: group.get('name'),
				props: { group: group.id }
			};
		});

		this.filters = filters;
	},

	change: function change() {
		var filter = this.filters[this.el.value];

		if (filter) {
			this.model.set('group', filter.props.group);
		}
	}
});

module.exports = IconPickerFontFilter;

/***/ }),
/* 9 */
/***/ (function(module, exports) {

var Attachment = wp.media.view.Attachment.Library,
    IconPickerFontItem;

/**
 * wp.media.view.IconPickerFontItem
 */
IconPickerFontItem = Attachment.extend({
	className: 'attachment iconpicker-item',

	initialize: function initialize() {
		this.template = wp.media.template('iconpicker-' + this.options.baseType + '-item');
		Attachment.prototype.initialize.apply(this, arguments);
	},

	render: function render() {
		var options = _.defaults(this.model.toJSON(), {
			baseType: this.options.baseType,
			type: this.options.type
		});

		this.views.detach();
		this.$el.html(this.template(options));
		this.updateSelect();
		this.views.render();

		return this;
	}
});

module.exports = IconPickerFontItem;

/***/ }),
/* 10 */
/***/ (function(module, exports) {

var $ = jQuery,
    Attachments = wp.media.view.Attachments,
    IconPickerFontLibrary;

/**
 * wp.media.view.IconPickerFontLibrary
 */
IconPickerFontLibrary = Attachments.extend({
	className: 'attachments iconpicker-items clearfix',

	initialize: function initialize() {
		Attachments.prototype.initialize.apply(this, arguments);

		_.bindAll(this, 'scrollToSelected');
		_.defer(this.scrollToSelected, this);
		this.controller.on('open', this.scrollToSelected, this);
		$(this.options.scrollElement).off('scroll', this.scroll);
	},

	_addItem: function _addItem(model) {
		this.views.add(this.createAttachmentView(model), {
			at: this.collection.indexOf(model)
		});
	},

	_removeItem: function _removeItem(model) {
		var view = this._viewsByCid[model.cid];
		delete this._viewsByCid[model.cid];

		if (view) {
			view.remove();
		}
	},

	render: function render() {
		_.each(this._viewsByCid, this._removeItem, this);
		this.collection.each(this._addItem, this);

		return this;
	},

	createAttachmentView: function createAttachmentView(model) {
		var view = new wp.media.view.IconPickerFontItem({
			controller: this.controller,
			model: model,
			collection: this.collection,
			selection: this.options.selection,
			baseType: this.options.baseType,
			type: this.options.type
		});

		return this._viewsByCid[view.cid] = view;
	},

	/**
  * Scroll to selected item
  */
	scrollToSelected: function scrollToSelected() {
		var selected = this.options.selection.single(),
		    singleView,
		    distance;

		if (!selected) {
			return;
		}

		singleView = this.getView(selected);

		if (singleView && !this.isInView(singleView.$el)) {
			distance = singleView.$el.offset().top - parseInt(singleView.$el.css('paddingTop'), 10) - this.$el.offset().top + this.$el.scrollTop() - parseInt(this.$el.css('paddingTop'), 10);

			this.$el.scrollTop(distance);
		}
	},

	getView: function getView(model) {
		return _.findWhere(this._viewsByCid, { model: model });
	},

	isInView: function isInView($elem) {
		var docViewTop = this.$window.scrollTop(),
		    docViewBottom = docViewTop + this.$window.height(),
		    elemTop = $elem.offset().top,
		    elemBottom = elemTop + $elem.height();

		return elemBottom <= docViewBottom && elemTop >= docViewTop;
	},

	prepare: function prepare() {},
	ready: function ready() {},
	initSortable: function initSortable() {},
	scroll: function scroll() {}
});

module.exports = IconPickerFontLibrary;

/***/ }),
/* 11 */
/***/ (function(module, exports) {

/**
 * wp.media.view.MediaFrame.IconPicker
 *
 * A frame for selecting an icon.
 *
 * @class
 * @augments wp.media.view.MediaFrame.Select
 * @augments wp.media.view.MediaFrame
 * @augments wp.media.view.Frame
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @mixes wp.media.controller.StateMachine
 */

var l10n = wp.media.view.l10n,
    Select = wp.media.view.MediaFrame.Select,
    IconPicker;

IconPicker = Select.extend({
	initialize: function initialize() {
		_.defaults(this.options, {
			title: l10n.iconPicker.frameTitle,
			multiple: false,
			ipTypes: iconPicker.types,
			target: null,
			SidebarView: null
		});

		if (this.options.target instanceof wp.media.model.IconPickerTarget) {
			this.target = this.options.target;
		} else {
			this.target = new wp.media.model.IconPickerTarget();
		}

		Select.prototype.initialize.apply(this, arguments);
	},

	createStates: function createStates() {
		var Controller;

		_.each(this.options.ipTypes, function (props) {
			if (!wp.media.controller.hasOwnProperty('IconPicker' + props.controller)) {
				return;
			}

			Controller = wp.media.controller['IconPicker' + props.controller];

			this.states.add(new Controller({
				id: props.id,
				content: props.id,
				title: props.name,
				data: props.data
			}));
		}, this);
	},

	/**
  * Bind region mode event callbacks.
  */
	bindHandlers: function bindHandlers() {
		this.on('router:create:browse', this.createRouter, this);
		this.on('router:render:browse', this.browseRouter, this);
		this.on('content:render', this.ipRenderContent, this);
		this.on('toolbar:create:select', this.createSelectToolbar, this);
		this.on('open', this._ipSetState, this);
		this.on('select', this._ipUpdateTarget, this);
	},

	/**
  * Set state based on the target's icon type
  */
	_ipSetState: function _ipSetState() {
		var stateId = this.target.get('type');

		if (!stateId || !this.states.findWhere({ id: stateId })) {
			stateId = this.states.at(0).id;
		}

		this.setState(stateId);
	},

	/**
  * Update target's attributes after selecting an icon
  */
	_ipUpdateTarget: function _ipUpdateTarget() {
		var state = this.state(),
		    selected = state.get('selection').single(),
		    props;

		props = {
			type: state.id,
			icon: selected.get('id'),
			sizes: selected.get('sizes'),
			url: state.ipGetIconUrl(selected)
		};

		this.target.set(props);
	},

	browseRouter: function browseRouter(routerView) {
		var routers = this.state().routers;

		if (routers) {
			routerView.set(routers);
		}
	},

	ipRenderContent: function ipRenderContent() {
		var state = this.state(),
		    mode = this.content.mode(),
		    content = state.getContentView(mode);

		this.content.set(content);
	}
});

module.exports = IconPicker;

/***/ }),
/* 12 */
/***/ (function(module, exports) {

/**
 * wp.media.view.IconPickerImgBrowser
 */
var IconPickerImgBrowser = wp.media.view.AttachmentsBrowser.extend(wp.media.view.IconPickerBrowser);

module.exports = IconPickerImgBrowser;

/***/ }),
/* 13 */
/***/ (function(module, exports) {

/**
 * wp.media.view.IconPickerSidebar
 */
var IconPickerSidebar = wp.media.view.Sidebar.extend({
	initialize: function initialize() {
		var selection = this.options.selection;

		wp.media.view.Sidebar.prototype.initialize.apply(this, arguments);

		selection.on('selection:single', this.createSingle, this);
		selection.on('selection:unsingle', this.disposeSingle, this);

		if (selection.single()) {
			this.createSingle();
		}
	},

	/**
  * @abstract
  */
	createSingle: function createSingle() {},

	/**
  * @abstract
  */
	disposeSingle: function disposeSingle() {}
});

module.exports = IconPickerSidebar;

/***/ }),
/* 14 */
/***/ (function(module, exports) {

/**
 * wp.media.view.IconPickerSvgItem
 */
var IconPickerSvgItem = wp.media.view.Attachment.Library.extend({
  template: wp.template('iconpicker-svg-item')
});

module.exports = IconPickerSvgItem;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(0);

(function ($) {
	var l10n = wp.media.view.l10n.iconPicker,
	    templates = {},
	    frame,
	    selectIcon,
	    removeIcon,
	    getFrame,
	    updateField,
	    updatePreview,
	    $field;

	getFrame = function getFrame() {
		if (!frame) {
			frame = new wp.media.view.MediaFrame.IconPicker();

			frame.target.on('change', updateField);
		}

		return frame;
	};

	updateField = function updateField(model) {
		_.each(model.get('inputs'), function ($input, key) {
			$input.val(model.get(key));
		});

		model.clear({ silent: true });
		$field.trigger('ipf:update');
	};

	updatePreview = function updatePreview(e) {
		var $el = $(e.currentTarget),
		    $select = $el.find('a.ipf-select'),
		    $remove = $el.find('a.ipf-remove'),
		    type = $el.find('input.ipf-type').val(),
		    icon = $el.find('input.ipf-icon').val(),
		    url = $el.find('input.url').val(),
		    template;

		if (type === '' || icon === '' || !_.has(iconPicker.types, type)) {
			$remove.addClass('hidden');
			$select.removeClass('has-icon').addClass('button').text(l10n.selectIcon).attr('title', '');

			return;
		}

		if (templates[type]) {
			template = templates[type];
		} else {
			template = templates[type] = wp.template('iconpicker-' + iconPicker.types[type].templateId + '-icon');
		}

		$remove.removeClass('hidden');
		$select.attr('title', l10n.selectIcon).addClass('has-icon').removeClass('button').html(template({
			type: type,
			icon: icon,
			url: url
		}));
	};

	selectIcon = function selectIcon(e) {
		var frame = getFrame(),
		    model = { inputs: {} };

		e.preventDefault();

		$field = $(e.currentTarget).closest('.ipf');
		model.id = $field.attr('id');

		// Collect input fields and use them as the model's attributes.
		$field.find('input').each(function () {
			var $input = $(this),
			    key = $input.attr('class').replace('ipf-', ''),
			    value = $input.val();

			model[key] = value;
			model.inputs[key] = $input;
		});

		frame.target.set(model, { silent: true });
		frame.open();
	};

	removeIcon = function removeIcon(e) {
		var $el = $(e.currentTarget).closest('div.ipf');

		$el.find('input').val('');
		$el.trigger('ipf:update');
	};

	$(document).on('click', 'a.ipf-select', selectIcon).on('click', 'a.ipf-remove', removeIcon).on('ipf:update', 'div.ipf', updatePreview);

	$('div.ipf').trigger('ipf:update');
})(jQuery);

/***/ })
/******/ ]);