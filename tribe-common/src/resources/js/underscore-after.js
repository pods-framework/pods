(function(){
	/**
	 * If we have a temp variable of type function it means lodash was loaded before underscore so we need to
	 * remove the reference to underscore from window._ by using the method .noConflict() from underscore, after
	 * this point we need to revert back the value of window._ which was lodash.
	 *
	 * In the second scenario when underscore is loaded before lodash, this will not be executed as window._ will remain as lodash.
	 *
	 * On a third scenario when lodash is not included this will either be executed which will allow to use something like: window.underscore || window._ to fallback to the correct value of underscore in the plugins.
	 */
	if ( window._lodash_tmp !== false && typeof window._lodash_tmp === 'function' ) {
		// Remove reference to _ if is underscore
		window.underscore = _.noConflict();
		// Restore reference to lodash if present
		window._ = window._lodash_tmp;
	}
})();
