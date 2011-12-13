/*!
 * jQuery lightweight plugin boilerplate
 * Original author: @ajpiano
 * Further changes, comments: @addyosmani
 * Licensed under the MIT license
 */

;(function ( $, window, document, undefined ) {
	var pluginName = 'klove';
	var defaults = {
		type: "shortcut",
		source: "data-klove-shortcut"
	};
	
	function Plugin( element, options ) {
		this.element = jQuery(element);
		
		this.options = $.extend( {}, defaults, options) ;
		
		this._defaults = defaults;
		this._name = pluginName;
		
		this.init();
	}
	
	Plugin.prototype.init = function () {
		if(this.options.type == "shortcut")
			shortcut(this.element, this.options);
			
		if(this.options.type == "table")
			table(this.element, this.options);
	};
	
	function shortcut(el, options) {
		var key = el.attr(options.source);
		jwerty.key(key, function () {
			var tag = document.activeElement.tagName;
			if(tag == "INPUT" || tag == "SELECT" || tag == "TEXTAREA") return;
			
			var target = el;
			target.click();
			if(target[0].tagName == "A")
				document.location = target.attr("href");
		});
	}
	
	function table(container, options) {
		jwerty.key("j", function(){ table_next_row(container); });
		jwerty.key("↓", function(){ table_next_row(container); return false; });
		jwerty.key("k", function(){ table_prev_row(container); });
		jwerty.key("↑", function(){ table_prev_row(container); return false; });
		
		jwerty.key("x", function(){
			container.find(".active[data-klove=row] [data-klove=marker]").click();
		});
		
		container.find("[data-klove=row]").first().find("[data-klove-row-shortcut]").each(function(){
			var o = options;
			table_shortcut(jQuery(this), options);
		});
	}
	
	function table_next_row (container) {
		var tag = document.activeElement.tagName;
		if(tag == "INPUT" || tag == "SELECT" || tag == "TEXTAREA") return;
		
		if(container.find(".active[data-klove=row]").length > 0){
			var row = container.find(".active[data-klove=row]");
			row.removeClass("active");
			
			if(row.next("[data-klove=row]").length > 0)
				row.next("[data-klove=row]").addClass("active");
			else
				container.find("[data-klove=row]").first().addClass("active");
		}else{
			container.find("[data-klove=row]").first().addClass("active");
		}
		container.find(".active[data-klove=row] [data-klove=focus]").first().focus();
	}
	
	function table_prev_row (container) {
		var tag = document.activeElement.tagName;
		if(tag == "INPUT" || tag == "SELECT" || tag == "TEXTAREA") return;
		
		if(container.find(".active[data-klove=row]").length > 0){
			var row = container.find(".active[data-klove=row]");
			row.removeClass("active");
			
			if(row.prev("[data-klove=row]").length > 0)
				row.prev("[data-klove=row]").addClass("active");
			else
				container.find("[data-klove=row]").last().addClass("active");
		}else{
			container.find("[data-klove=row]").last().addClass("active");
		}
		container.find(".active[data-klove=row] [data-klove=focus]").first().focus();
	}
	
	function table_shortcut(el, options) {
		var key = el.attr("data-klove-row-shortcut");
		jwerty.key(key, function () {
			var tag = document.activeElement.tagName;
			if(tag == "INPUT" || tag == "SELECT" || tag == "TEXTAREA") return;
			
			var container = el.parents("[data-klove=container]");
			var k = key;
			var e = container.find(".active [data-klove-row-shortcut='"+k+"']");
			e.click();
			
			if(e[0].tagName == "A")
				document.location = e.attr("href");
		});
	}
	
	$.fn[pluginName] = function ( options, test) {
		return this.each(function () {
			if (!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName,
				new Plugin( this, {type: options} ));
			}
		});
	}

})( jQuery, window, document );