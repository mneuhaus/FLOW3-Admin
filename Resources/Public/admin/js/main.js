jQuery(document).ready(function(){
	jQuery("input[type='text']:first").focus();

	// Table select all behavior
	jQuery(".select-all").change(function(){
		var table = jQuery(this).parents("table");
		if(jQuery(this).attr('checked') == "checked")
			table.find(".select-row").attr('checked', "checked");
		else
			table.find(".select-row").removeAttr('checked');
	});
	
	//jQuery('.navbar ul.nav').dropdown();
	
	jQuery(".inline[data-mode='multiple']").inlineHelper();
	
	
	jQuery("[data-height=fixed]").each(function(){
		var e = jQuery(this);
		e.height(jQuery("body").height() - Number(e.css("top").replace("px", "")));
	});
	
	jQuery("body").height(jQuery("body").height() - 40);
	
	jQuery("#shortcuts-modal").modal({show: false, background: true, keyboard: true});
	jQuery("#shortcuts-modal .modal-body").append("<dl class='hotkeys'><dt>h</dt><dd>toggle this help dialog</dd></dl>");
	jwerty.key("h", function () {
		var tag = document.activeElement.tagName;
		if(tag == "INPUT" || tag == "SELECT" || tag == "TEXTAREA") return;
		jQuery("#shortcuts-modal").toggle();
	});
	jQuery("#shortcuts-modal .close").click(function(){
		jQuery("#shortcuts-modal").hide();
	});
	
	jQuery("[data-klove-shortcut]").each(function(){
		var e = jQuery(this);
		
		if(e.attr("data-klove-info") !== undefined)
			var text = e.attr("data-klove-info");
		else if(e.attr("type") == "checkbox")
			var text = "toggle checkbox";
		else
			var text = e.text();
			
		jQuery("#shortcuts-modal .modal-body").append("<dl class='hotkeys'><dt>" + e.attr("data-klove-shortcut") + "</dt><dd>" + text + "</dd></dl>");
	});
	jQuery("#shortcuts-modal .modal-body").append('<h4 class="break">Shortcuts for current item</h4>');
	jQuery("[data-klove=row]:first [data-klove-row-shortcut]").each(function(){
		var e = jQuery(this);
		
		var key = e.attr("data-klove-row-shortcut");
		if(e.attr("data-klove-info") !== undefined)
			var text = e.attr("data-klove-info");
		else if(e.attr("type") == "checkbox")
			var text = "toggle checkbox";
		else
			var text = e.text();
		
		jQuery("#shortcuts-modal .modal-body").append("<dl class='hotkeys'><dt>" + key + "</dt><dd>" + text + "</dd></dl>");
	});
});