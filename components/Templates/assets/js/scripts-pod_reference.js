var htmleditor;

CodeMirror.defineMode("mustache", function (config, parserConfig) {
	var mustacheOverlay = {
		token: mustache
	};
	return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
});

// setup pod selection
jQuery(function ($) {

	htmleditor = CodeMirror.fromTextArea(document.getElementById("content"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "mustache",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		lineWrapping: true

	});

	/* Setup autocomplete */
	htmleditor.on('keyup', podFields);

	$('.pod-switch').baldrick({
		request: ajaxurl, method: 'POST'
	});

	$('#pods-magic-tag-list').on('click', 'dd.pods-magic-tag-option', function (e) {
		e.preventDefault();

		var $element = $(this);

		var tag = $element.text();

		navigator.clipboard.writeText('{@' + tag.trim() + '}');

		$element.css('background-color', '#ffffb4');

		setTimeout(function () {
			$element.css('background-color', '');
		}, 200);
	});
});

