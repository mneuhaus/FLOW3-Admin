;(function ( $, window, document, undefined ) {
	var pluginName = 'dateJsInput';
	var defaults = {
			messages: ["Nope", "Keep Trying", "Nadda", "Sorry", "No one\'s home", "Arg", "Bummer", "Faux pas", "Whoops", "Snafu", "Blunder"],
			date_result: '<span class="date-result help-inline"></span>',
			format: "dddd, MMMM dd, yyyy h:mm:ss tt"
		};
	
	function Plugin( element, options ) {
		this.element = jQuery(element);
		
		this.options = $.extend( {}, defaults, options) ;
		
		this._defaults = defaults;
		this._name = pluginName;
		
		this.init();
	}
	
	Plugin.prototype.init = function () {
		var name = this.element.attr("name");
		var hidden = jQuery('<input type="hidden" class="date-result"/>').attr("name", name);
		this.element.after(hidden);
		this.element.after(this.options.date_result);
		this.element.data("options", this.options).attr("autocomplete", "off");
		
		this.element.keyup(function(e){
			input = jQuery(e.currentTarget);
			result = input.siblings("span.date-result").first();
			hidden = input.siblings("input.date-result").first();
			options = input.data("options");
			if (input.val().length > 0) {
				date = Date.parse(input.val());
				if (date !== null) {
					if(input.attr("data-format") !== undefined)
						format = input.attr("data-format");
					else
						format = options.format;
						
					result.removeClass("important").addClass("success").text(date.toString(format));
					hidden.val(date.toString(format));
				} else {
					result.removeClass("success").addClass("important").text(options.messages[Math.round(options.messages.length * Math.random())] + "...");
					hidden.val();
				}
			} else {
				result.text(empty_string).addClass("empty");
			}
		});
	};
	
	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function ( options ) {
		return this.each(function () {
			if (!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName,
				new Plugin( this, options ));
			}
		});
	}

})( jQuery, window, document );