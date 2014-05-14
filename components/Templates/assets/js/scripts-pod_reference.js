var htmleditor;

CodeMirror.defineMode("mustache", function(config, parserConfig) {
	var mustacheOverlay = {
		token: mustache
	};
	return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
});


// setup pod selection
jQuery(function($){

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
		request: ajaxurl,
		method: 'POST'
	});

});

