(function($){
	$.fn.selectbox = function(options) {
		var defaults = {
			tpl: {
				wrap: '<div class="selectbox"></div>',
				availableWrap: '<div class="selectbox-available"></div>',
				availableHeader: '<div class="selectbox-header">Available</div>',
				availableSearch: '<div class="selectbox-search"><input type="text" value="" id="selectbox-search"></div>',
				availableSelect: '<select id="selectbox-available" multiple="true" size="10"></select>',
				toolbar: '<div class="selectbox-toolbar"><a href="#" class="selectbox-add">></a><a href="#" class="selectbox-remove"><</a></div>',
				selectAll: '<div class="selectbox-all"><a href="#">Select All</a></div>',
				selectedWrap: '<div class="selectbox-selected"></div>',
				selectedHeader: '<div class="selectbox-header">Selected</div>',
				selectedInfo: '<div class="selectbox-info"> Select your Choice(s) and click ></div>',
				selectedSelect: '<select id="selectbox-selected" multiple="true" size="10"></select>',
				clearAll: '<div class="selectbox-clear"><a href="#">Clear All</a></div>'
			}
		};

		o = $.extend({}, defaults, options);

		this.each(function() {
			var select = $(this);
			setup(select);
		});

		function setup(select){
			select.hide();
			$.fn.selectbox.sortItems(select.find("option"));

			var name = select.attr("name").replace(/]/g,"").replace(/\[$/g,"").replace(/\[/g,"-");
            select.attr("id",name);

			var selectbox = jQuery(o.tpl.wrap);

			var availableWrap = jQuery(o.tpl.availableWrap);
				selectbox.append(availableWrap);

			var availableHeader = jQuery(o.tpl.availableHeader);
				availableWrap.append(availableHeader);

			var availableSearch = jQuery(o.tpl.availableSearch);
				var search = availableSearch.find("input");
				search.attr("id","selectbox-search-"+name);
				availableWrap.append(availableSearch);

			var availableSelect = jQuery(o.tpl.availableSelect);
				availableSelect.attr("id","selectbox-available-"+name);
				availableSelect.data("ref",name);
				availableSelect.data("name",select.attr("name"));
				availableWrap.append(availableSelect);

//			var selectAll = jQuery(o.tpl.selectAll);
//				availableWrap.append(selectAll);

			var toolbar = jQuery(o.tpl.toolbar);
				selectbox.append(toolbar);

			var selectedWrap = jQuery(o.tpl.availableWrap);
				selectbox.append(selectedWrap);

			var selectedHeader = jQuery(o.tpl.selectedHeader);
				selectedWrap.append(selectedHeader);

			var selectedInfo = jQuery(o.tpl.selectedInfo);
				selectedWrap.append(selectedInfo);

			var selectedSelect = jQuery(o.tpl.selectedSelect);
				selectedSelect.attr("id","selectbox-selected-"+name);
				selectedSelect.data("ref",name);
				selectedSelect.data("name",select.attr("name"));
				selectedWrap.append(selectedSelect);

//			var clearAll = jQuery(o.tpl.clearAll);
//				selectedWrap.append(clearAll);

			availableSelect.append(select.find("option").clone());
			selectedSelect.append(availableSelect.find("option:selected").removeAttr("selected"));

		    jQuery(availableSelect).bind('keydown', 'right', function(){return $.fn.selectbox.selectItem(availableSelect);});
		    jQuery(selectedSelect).bind('keydown', 'left', function(){return $.fn.selectbox.removeItem(selectedSelect);});

//			selectAll.click(function(){selectAllItems(availableSelect);return false;});
//			clearAll.click(function(){clearAllItems(selectedSelect);return false;});

			toolbar.find(".selectbox-remove").click(function(){$.fn.selectbox.removeItem(selectedSelect);return false;});
			toolbar.find(".selectbox-add").click(function(){$.fn.selectbox.selectItem(availableSelect);return false;});

			jQuery("#selectbox-available-"+name+" option").live("dblclick",function(){$.fn.selectbox.selectItem(availableSelect)});
			jQuery("#selectbox-selected-"+name+" option").live("dblclick",function(){$.fn.selectbox.removeItem(selectedSelect)});

			optionFilter(search,availableSelect);
			//jQuery(search).bind('keyup', 'down', function(e){ console.log(e); availableSelect.trigger(e); return false; });

			select.before(selectbox);

		}


		function selectAllItems(e){
			var select = jQuery("#"+jQuery(e).data("ref"));
			var target = jQuery("#selectbox-selected-"+jQuery(e).data("ref"));
			items.change();
			var items = jQuery(e).find("option");
			target.append(items);
			$.fn.selectbox.pushSelection(select);
            console.log(select,"select[name="+jQuery(e).data("name")+"]");
			return false;
		}

		function clearAllItems(e){
			var select = jQuery("#"+jQuery(e).data("ref"));
			var target = jQuery("#selectbox-available-"+jQuery(e).data("ref"));
			items.change();
			var items = jQuery(e).find("option");
			target.append(items);
			$.fn.selectbox.pushSelection(select);
			return false;
		}

		function optionFilter(input, list){
			list = jQuery(list);

			if ( list.length ) {
				var clipboard = jQuery("<div style='display:none'>");
				list.after(clipboard);

				input
					.keyup(filter).keyup()
					.parents('form').submit(function(){
						return false;
					});
			}

			return input;

			function filter(){
				var term = jQuery.trim( jQuery(input).val().toLowerCase() ), scores = [];
				list.append( clipboard.children("option") );
				var rows = list.children('option');
				var cache = rows.map(function(){return this.innerHTML.toLowerCase();});

				if ( !term ) {
					list.append( rows );
				} else {
					clipboard.append( rows );

					cache.each(function(i){
						var score = this.score(term);
						if (score > 0) {scores.push([score, i]);}
					});

					jQuery.each(scores.sort(function(a, b){return b[0] - a[0];}), function(){
						list.append(rows[ this[1] ]);
					});
				}
			}
		}

		return this;
	}
	$.fn.selectbox.selectItem = function(e){
		var select = jQuery("#"+jQuery(e).data("ref"));
		var target = jQuery("#selectbox-selected-"+jQuery(e).data("ref"));
		var items = jQuery(e).find("option:selected").removeAttr("selected");
		items.change();
		target.append(items);
		$.fn.selectbox.pushSelection(select);
		return false;
	}

	$.fn.selectbox.removeItem = function(e){
		var select = jQuery("#"+jQuery(e).data("ref"));
		var target = jQuery("#selectbox-available-"+jQuery(e).data("ref"));
		var items = jQuery(e).find("option:selected").removeAttr("selected");
		items.change();
		target.append(items);
		$.fn.selectbox.pushSelection(select);
		return false;
	}

	$.fn.selectbox.pushSelection = function(select){
		var name = select.attr("name").replace(/]/g,"").replace(/\[$/g,"").replace(/\[/g,"-");
		select.empty();

		selectedSelect = jQuery("#selectbox-selected-"+name);
		availableSelect = jQuery("#selectbox-available-"+name);

		select.append(selectedSelect.find("option").clone().attr("selected","true"));

		$.fn.selectbox.sortItems(selectedSelect.find("option"));
		$.fn.selectbox.sortItems(availableSelect.find("option"));

		availableSelect.find("option").dblclick(function(){$.fn.selectbox.selectItem(availableSelect)});
		selectedSelect.find("option").dblclick(function(){$.fn.selectbox.removeItem(selectedSelect)});
	}


	$.fn.selectbox.sortItems = function(e){
		var options = e.get();
		options.sort(function(a, b) {
		   var compA = $(a).text().toUpperCase();
		   var compB = $(b).text().toUpperCase();
		   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
		})

		jQuery.each(options, function(idx, itm) {e.parent().append(itm);});
	}
})(jQuery);

// qs_score - Quicksilver Score
//
// A port of the Quicksilver string ranking algorithm
//
// "hello world".score("axl") //=> 0.0
// "hello world".score("ow") //=> 0.6
// "hello world".score("hello world") //=> 1.0
//
// Tested in Firefox 2 and Safari 3
//
// The Quicksilver code is available here
// http://code.google.com/p/blacktree-alchemy/
// http://blacktree-alchemy.googlecode.com/svn/trunk/Crucible/Code/NSString+BLTRRanking.m
//
// The MIT License
//
// Copyright (c) 2008 Lachie Cox
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.


String.prototype.score = function(abbreviation,offset) {
  offset = offset || 0 // TODO: I think this is unused... remove

  if(abbreviation.length == 0) return 0.9
  if(abbreviation.length > this.length) return 0.0

  for (var i = abbreviation.length; i > 0; i--) {
    var sub_abbreviation = abbreviation.substring(0,i)
    var index = this.indexOf(sub_abbreviation)


    if(index < 0) continue;
    if(index + abbreviation.length > this.length + offset) continue;

    var next_string       = this.substring(index+sub_abbreviation.length)
    var next_abbreviation = null

    if(i >= abbreviation.length)
      next_abbreviation = ''
    else
      next_abbreviation = abbreviation.substring(i)

    var remaining_score   = next_string.score(next_abbreviation,offset+index)

    if (remaining_score > 0) {
      var score = this.length-next_string.length;

      if(index != 0) {
        var j = 0;

        var c = this.charCodeAt(index-1)
        if(c==32 || c == 9) {
          for(var j=(index-2); j >= 0; j--) {
            c = this.charCodeAt(j)
            score -= ((c == 32 || c == 9) ? 1 : 0.15)
          }

          // XXX maybe not port this heuristic
          //
          //          } else if ([[NSCharacterSet uppercaseLetterCharacterSet] characterIsMember:[self characterAtIndex:matchedRange.location]]) {
          //            for (j = matchedRange.location-1; j >= (int) searchRange.location; j--) {
          //              if ([[NSCharacterSet uppercaseLetterCharacterSet] characterIsMember:[self characterAtIndex:j]])
          //                score--;
          //              else
          //                score -= 0.15;
          //            }
        } else {
          score -= index
        }
      }

      score += remaining_score * next_string.length
      score /= this.length;
      return score
    }
  }
  return 0.0
}