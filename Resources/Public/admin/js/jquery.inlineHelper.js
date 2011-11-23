/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */

// the semi-colon before the function invocation is a safety
// net against concatenated scripts and/or other plugins
// that are not closed properly.
;(function ( $, window, document, undefined ) {
	// Create the defaults once
	var pluginName = 'inlineHelper';
	var defaults = {
			container: ".inline[data-mode='multiple']",
			template: ".inline-template",
			item: ".inline-item"
		};
	
	// The actual plugin constructor
	function Plugin( element, options ) {
		this.element = jQuery(element);
		
		this.options = $.extend( {}, defaults, options) ;
		
		this._defaults = defaults;
		this._name = pluginName;
		
		this.init();
	}
	
	Plugin.prototype.init = function () {
		// Create new row after the last one is used
		this.element.find("input, select, textarea").live("change", function(){
			var container = jQuery(this).parents(".inline");
			var row = jQuery(this).parents(".inline-item").first();
			if(row.next(".inline-item").length == 0 && container.attr("data-mode") == "multiple"){
				addItem(container);
			}
			if(row.hasClass("inline-unused")){
				row.removeClass("inline-unused");
				row.find(".close").show();
			}
		});
		
		// Show close buttons
		this.element.find(".close").show();
		this.element.find(".inline-unused .close").hide();
		
		// Remove the item and create a new one if it was the last one
		jQuery(".inline-item .close").live("click", function(){
			var e = jQuery(this);
			var container = e.parents(".inline");
			e.parents(".inline-item").remove();
			if(container.find(".inline-item").length < 1){
				addItem(container);
			}
		});
	};
	
	function addItem(container){
		var counter = Number(container.attr("data-counter"));
		var tpl = container.find(".inline-template");
		var newRow = tpl.clone().removeClass("inline-template").addClass("inline-item");
		
		newRow.find("input:hidden, select:hidden, textarea:hidden").removeAttr("disabled");
		newRow.html(newRow.html().replace(/\{counter}/g, counter));
		newRow.hide().addClass("inline-unused");
		jQuery(tpl).parent().append(newRow);
		newRow.slideDown();
		container.attr("data-counter", counter+1);
	}

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

/*
jQuery(".inline[data-mode='multiple']").find("input, select, textarea").live("change", function(){
	var container = jQuery(this).parents(".inline");
	var tpl = container.find(".inline-template");
	var row = jQuery(this).parents(".inline-item").first();
	
	if(row.next(".inline-item").length == 0 && container.attr("data-mode") == "multiple"){
		var counter = Number(container.attr("data-counter"));
		var newRow = tpl.clone().removeClass("inline-template").addClass("inline-item");
		
		newRow.find("input:hidden, select:hidden, textarea:hidden").removeAttr("disabled");
		newRow.html(newRow.html().replace(/\{counter}/g, counter));
		jQuery(row).after(newRow);
		container.attr("data-counter", counter+1);
	}
});

jQuery(".inline[data-mode='multiple'] .close").show();
jQuery(".inline-item .close").click(function(){
	var e = jQuery(this);
	var container = e.parents(".inline");
	e.parents(".inline-item").remove();
	if(container.find(".inline-item").length < 1){
		var tpl = container.find(".inline-template");
		var counter = Number(container.attr("data-counter"));
		var newRow = tpl.clone().removeClass("inline-template").addClass("inline-item");
		
		newRow.find("input:hidden, select:hidden, textarea:hidden").removeAttr("disabled");
		newRow.html(newRow.html().replace(/\{counter}/g, counter));
		jQuery(container).append(newRow);
		container.attr("data-counter", counter+1);
	}
});

jQuery(".inline input:hidden, .inline select:hidden, .inline textarea:hidden").each(function(){
	var e = jQuery(this);
	if(e.attr("type") !== "hidden")
		e.attr("disabled", "disabled");
});
*/