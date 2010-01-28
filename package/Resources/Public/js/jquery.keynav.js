(function($)
{
	$.fn.keynav = function(options)
	{
		// Set the options.
		options = $.extend({
		}, options);

        var bindings = {};
        var items = this;
        
        $(document).bind('keydown', {combi:'alt', disableInInput: true}, function(){
            items.find(".keynav-accesskey").show();
        });
        $(document).bind('keyup', {combi:'alt', disableInInput: true}, function(){
            items.find(".keynav-accesskey").hide();
        });
		return this.each(function()
		{
            var e = jQuery(this);
            var x = 0;
            var text = e.text().trim();
            do {
                var binding = text.substr(x, 1).toLowerCase();
                x = x+1;
            } while(typeof(bindings[binding]) !== "undefined")
            
            bindings[binding] = this;
            $(document).bind('keydown', {combi:'alt+'+binding, disableInInput: true}, function(){
                document.location.href = jQuery(e[0]).attr("href");
            });
            
            e.addClass("keynav-enabled");
            e.append("<span class='keynav-accesskey'>"+binding+"</span>");
		});
	};
    
    function trim (zeichenkette) {
        return zeichenkette.replace (/^\s+/, '').replace (/\s+$/, '');
    }
})(jQuery);