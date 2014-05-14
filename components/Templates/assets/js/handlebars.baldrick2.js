/* Baldrick handlebars.js templating plugin */
(function($){
	var compiledTemplates	= {};
	$.fn.baldrick.registerhelper('handlebars', {
		bind	: function(triggers, defaults){
			var	templates = triggers.filter("[data-template-url]");
			if(templates.length){
				templates.each(function(){
					var trigger = $(this);
					if(typeof compiledTemplates[trigger.data('templateUrl')] === 'undefined'){
						compiledTemplates[trigger.data('templateUrl')] = true;
						$.get(trigger.data('templateUrl'), function(data){
							compiledTemplates[trigger.data('templateUrl')] = Handlebars.compile(data);
						});
					}
				});
			}

		},
		request_params	: function(request, defaults, params){
			if((params.trigger.data('templateUrl') || params.trigger.data('template')) && typeof Handlebars === 'object'){
				request.dataType = 'json';
				return request;
			}
		},
		filter			: function(opts, defaults){

			if(opts.params.trigger.data('templateUrl')){
				if( typeof compiledTemplates[opts.params.trigger.data('templateUrl')] === 'function' ){
					opts.data = compiledTemplates[opts.params.trigger.data('templateUrl')](opts.data);
				}
			}else if(opts.params.trigger.data('template')){
				if( typeof compiledTemplates[opts.params.trigger.data('template')] === 'function' ){
					opts.data = compiledTemplates[opts.params.trigger.data('template')](opts.data);
				}else{
					if($(opts.params.trigger.data('template'))){
						compiledTemplates[opts.params.trigger.data('template')] = Handlebars.compile($(opts.params.trigger.data('template')).html());
						opts.data = compiledTemplates[opts.params.trigger.data('template')](opts.data);
					}
				}
			}

			return opts;
		}
	});

})(jQuery);