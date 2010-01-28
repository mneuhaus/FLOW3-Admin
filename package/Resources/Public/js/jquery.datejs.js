(function($)
{
	$.fn.datejs = function(options)
	{
		// Set the options.
		options = $.extend({
			messages:["Nope", "Keep Trying", "Nadda", "Sorry", "No one\'s home", "Arg", "Bummer", "Faux pas", "Whoops", "Snafu", "Blunder"],
			input_empty: "*Enter a date (or time) here", 
			empty_string: "Type a date above",
			date: null,
			display_format:"dddd, MMMM dd, yyyy h:mm:ss tt",
			value_format:"yyyy-MM-ddTHH:mm:ssZ",
			default_value: 0
		}, options);

		// Go through the matched elements and return the jQuery object.
		return this.each(function()
		{
			var input = jQuery(this);
			var date_string= jQuery("<div class='f-inline'>Type a date above</div>");
			var forgiving_input= jQuery("<input name='"+input.attr("name")+"-datejs' id='"+input.attr("id")+"'/>");
			input.after(date_string);
			input.after(forgiving_input);
			input.css("display","none");
			forgiving_input.val(options.input_empty);
			date_string.text(options.empty_string);
			forgiving_input.keyup(process);

			function process(e) {
//				date_string.removeClass();
				if (forgiving_input.val().length > 0) {
					date = Date.parse(forgiving_input.val());
					if (date !== null) {
						forgiving_input.removeClass();
						date_string.addClass("accept").text(date.toString(options.display_format));
						input.val(date.toString(options.value_format));
					} else {
						forgiving_input.addClass("validate_error");
						date = Date.parseExact(input.val(),options.value_format);
						if (date == null) {
							date_string.addClass("error").text(options.messages[Math.round(options.messages.length * Math.random())] + "...");
						}else{
							input.val(date.toString(options.value_format));
							date_string.addClass("error").text("Not recognised, reverting to previous: " + date.toString(options.display_format));
						}
					}
				} else {
					date_string.text(options.empty_string).addClass("empty");
				}
			};
			forgiving_input.focus( 
				function (e) {
					if (forgiving_input.val() === options.input_empty) {
						forgiving_input.val("");
					}
				}
			);
			forgiving_input.blur( 
				function (e) {
					if (forgiving_input.val() === "") {
						forgiving_input.val(options.input_empty).removeClass();
					}
				}
			);
		});
	};
})(jQuery);