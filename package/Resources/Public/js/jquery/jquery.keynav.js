(function(jQuery)
{
	var bindings = {};
	jQuery.fn.hotnav = function(options)
	{
		// Set the options.
		options = jQuery.extend({
			trigger:function(e,options){ 		document.location.href = jQuery(e[0]).attr("href"); },
			keysource:function(e,options){		return e.text();},
			insertkey:function(e,key,options){e.append("<span class='"+options.class+"'>"+key.toUpperCase()+"</span>");},
			key:'alt',
			disableInInput:true,
			class:'hotnav-accesskey',
		}, options);
		
        var items = this;
        
        jQuery(document).bind('keydown', {combi:options.key, disableInInput: options.disableInInput}, function(){
            jQuery("."+options.class).show();
        });
        jQuery(document).bind('keyup', {combi:options.key, disableInInput: options.disableInInput}, function(){
            jQuery("."+options.class).hide();
        });
		return this.each(function()
		{
            var e = jQuery(this);
            var x = 0;
            var text = options.keysource(e,options) + "abcdefghijklmnopqrstvwxyz";
            do {
                var binding = text.replace(/^\s\s*/, '').replace(/\s\s*$/, '').substr(x, 1).toLowerCase();
                x = x+1;
            } while(typeof(bindings[binding]) !== "undefined" && x < text.length)2

            bindings[binding] = this;
			options.insertkey(e,binding,options);
            jQuery(document).bind('keydown', {combi:options.key+'+'+binding, disableInInput: options.disableInInput}, function(){
				options.trigger(e,options);
			});
		});
	};
})(jQuery);