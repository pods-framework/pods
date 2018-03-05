wp.media.model.IconPickerTarget = require( './models/target.js' );
wp.media.model.IconPickerFonts  = require( './models/fonts.js' );

wp.media.controller.iconPickerMixin = require( './controllers/mixin.js' );
wp.media.controller.IconPickerFont  = require( './controllers/font.js' );
wp.media.controller.IconPickerImg   = require( './controllers/img.js' );

wp.media.view.IconPickerBrowser     = require( './views/browser.js' );
wp.media.view.IconPickerSidebar     = require( './views/sidebar.js' );
wp.media.view.IconPickerFontItem    = require( './views/font-item.js' );
wp.media.view.IconPickerFontLibrary = require( './views/font-library.js' );
wp.media.view.IconPickerFontFilter  = require( './views/font-filter.js' );
wp.media.view.IconPickerFontBrowser = require( './views/font-browser.js' );
wp.media.view.IconPickerImgBrowser  = require( './views/img-browser.js' );
wp.media.view.IconPickerSvgItem     = require( './views/svg-item.js' );
wp.media.view.MediaFrame.IconPicker = require( './views/frame.js' );
