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
});