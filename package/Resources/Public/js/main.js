jQuery(document).ready(function(){
	jQuery("table").each(function(index) {
		jQuery(this).find("tbody tr:even").addClass("even");
		jQuery(this).find("tbody tr:odd").addClass("odd");
	});
    
	jQuery("input[type='text']:first").focus();
	
	jQuery(".sortable").sortable();
	jQuery(".sortable").each(function(){
		var container = jQuery(this);
		container.data("item",container.children("*:last").clone());
		var button = jQuery("<a href='#' class='ui-button'>Add one more</a>").click(function(){
		    jQuery(".sortable").append(container.data("item").clone());
		    return false;
		})
		container.after(button);
	});
	
	/*
	jQuery(".ui-table").tablesorter({widgets: ['zebra']});
	
	jQuery(".ui-table th").click(function(){	
		jQuery(".ui-table").each(function(index) {
			jQuery(this).find("tbody tr").removeClass("even").removeClass("odd");
			jQuery(this).find("tbody tr:even").addClass("even");
			jQuery(this).find("tbody tr:odd").addClass("odd");
		});
	});
	*/

   jQuery(".ux-select-all").change(function(){
       jQuery(".ux-select-row").attr('checked', jQuery(this).attr('checked'));
   });

    jQuery(".f-multiselect").selectbox();
    jQuery(".f-date").datepicker({dateFormat: "dd.mm.yy"});
   
    jQuery('.f-autoexpand').elastic();

    if(typeof(jQuery().ckeditor) != "undefined"){
        jQuery('.f-fullrte').ckeditor();
    }
});


	/*
	// Form Enhancements
	jQuery(".f-date").datepicker();
	//jQuery(".f-datetimerange").daterangepicker();
	jQuery(".f-spinner").spinner();
	jQuery(".f-slider").slider({
		slide: function(event, ui) {
			jQuery(this).siblings("input").val(ui.value);
			jQuery(this).siblings(".f-slider-value").text(ui.value);
		}
	});
	*/
	//jQuery(".f-multiselect").css("width","400px").css("height","200px").multiselect();
    /*
	jQuery(".available connected-list").css("height","177px");
	jQuery("select.f-autosuggest").autoSuggest();
	jQuery(".f-filtercombo").sexyCombo();
	jQuery('.f-multifile').MultiFile();
	jQuery('.f-datejs').datejs();

	jQuery('.f-fullrte').ckeditor();
	jQuery('.f-markdown').markItUp(markdownSettings);
	jQuery('.f-textile').markItUp(textileSettings);
	jQuery('.f-wiki').markItUp(wikiSettings);
	jQuery('.f-bbcode').markItUp(bbcodeSettings);
	jQuery('.f-dotclear').markItUp(dotclearSettings);
	jQuery('.f-texy').markItUp(texySettings);
	jQuery('#navigation a, .subnav a, a.ui-button, .hotnav').css("position","relative").hotnav();

	jQuery("input[type='text']:first").focus();

	jQuery(document).bind('keydown', {combi:'esc'}, function(){
		jQuery("input").blur();
    });

	*/