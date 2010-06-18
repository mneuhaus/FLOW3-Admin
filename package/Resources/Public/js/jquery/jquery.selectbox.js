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
			sortItems(select.find("option"));

			var name = select.attr("name").replace(/]/g,"").replace(/\[$/g,"").replace(/\[/g,"-");

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
				availableWrap.append(availableSelect);

			var selectAll = jQuery(o.tpl.selectAll);
				availableWrap.append(selectAll);

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
				selectedWrap.append(selectedSelect);

			var clearAll = jQuery(o.tpl.clearAll);
				selectedWrap.append(clearAll);

			availableSelect.append(select.find("option").clone());
			selectedSelect.append(availableSelect.find("option:selected").removeAttr("selected"));

		    jQuery(availableSelect).bind('keydown', 'right', function(){ return selectItem(this); });
		    jQuery(selectedSelect).bind('keydown', 'left', function(){ return removeItem(this); });

			selectAll.click(function(){selectAllItems(availableSelect); return false;});
			clearAll.click(function(){clearAllItems(selectedSelect); return false;});

			toolbar.find(".selectbox-remove").click(function(){removeItem(selectedSelect); return false;});
			toolbar.find(".selectbox-add").click(function(){selectItem(availableSelect); return false;});

			jQuery("#selectbox-available-"+name+" option").live("dblclick",function(){selectItem(availableSelect)});
			jQuery("#selectbox-selected-"+name+" option").live("dblclick",function(){removeItem(selectedSelect)});

			optionFilter(search,availableSelect);
			//jQuery(search).bind('keyup', 'down', function(e){ console.log(e); availableSelect.trigger(e); return false; });

			select.before(selectbox);
		}

		function selectItem(e){
			var select = jQuery("select[name="+jQuery(e).data("ref")+"]");
			var target = jQuery("#selectbox-selected-"+jQuery(e).data("ref"));
			var items = jQuery(e).find("option:selected").removeAttr("selected");
			target.append(items);
			pushSelection(select);
			return false;
		}

		function removeItem(e){
			var select = jQuery("select[name="+jQuery(e).data("ref")+"]");
			var target = jQuery("#selectbox-available-"+jQuery(e).data("ref"));
			var items = jQuery(e).find("option:selected").removeAttr("selected");
			target.append(items);
			pushSelection(select);
			return false;
		}

		function selectAllItems(e){
			var select = jQuery("select[name="+jQuery(e).data("ref")+"]");
			var target = jQuery("#selectbox-selected-"+jQuery(e).data("ref"));
			var items = jQuery(e).find("option");
			target.append(items);
			pushSelection(select);
			return false;
		}

		function clearAllItems(e){
			var select = jQuery("select[name="+jQuery(e).data("ref")+"]");
			var target = jQuery("#selectbox-available-"+jQuery(e).data("ref"));
			var items = jQuery(e).find("option");
			target.append(items);
			pushSelection(select);
			return false;
		}

		function pushSelection(select){
			var name = select.attr("name");
			select.empty();

			selectedSelect = jQuery("#selectbox-selected-"+name);
			availableSelect = jQuery("#selectbox-available-"+name);

			select.append(selectedSelect.find("option").clone().attr("selected","true"));

			sortItems(selectedSelect.find("option"));
			sortItems(availableSelect.find("option"));

			availableSelect.find("option").dblclick(function(){selectItem(availableSelect)});
			selectedSelect.find("option").dblclick(function(){removeItem(selectedSelect)});
		}

		function sortItems(e){
			var options = e.get();
			options.sort(function(a, b) {
			   var compA = $(a).text().toUpperCase();
			   var compB = $(b).text().toUpperCase();
			   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
			})

			jQuery.each(options, function(idx, itm) { e.parent().append(itm); });
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
				var cache = rows.map(function(){ return this.innerHTML.toLowerCase(); });

				if ( !term ) {
					list.append( rows );
				} else {
					clipboard.append( rows );

					cache.each(function(i){
						var score = this.score(term);
						if (score > 0) { scores.push([score, i]); }
					});

					jQuery.each(scores.sort(function(a, b){return b[0] - a[0];}), function(){
						list.append(rows[ this[1] ]);
					});
				}
			}
		}

		return this;
	}
})(jQuery);

/*
(c) Copyrights 2007 - 2008

Original idea by by Binny V A, http://www.openjs.com/scripts/events/keyboard_shortcuts/

jQuery Plugin by Tzury Bar Yochay
tzury.by@gmail.com
http://evalinux.wordpress.com
http://facebook.com/profile.php?id=513676303

Project's sites:
http://code.google.com/p/js-hotkeys/
http://github.com/tzuryby/hotkeys/tree/master

License: same as jQuery license.

USAGE:
    // simple usage
    $(document).bind('keydown', 'Ctrl+c', function(){ alert('copy anyone?');});

    // special options such as disableInIput
    $(document).bind('keydown', {combi:'Ctrl+x', disableInInput: true} , function() {});

Note:
    This plugin wraps the following jQuery methods: $.fn.find, $.fn.bind and $.fn.unbind
*/

(function (jQuery){
    // keep reference to the original $.fn.bind, $.fn.unbind and $.fn.find
    if (jQuery.fn.__bind__ === undefined){
        jQuery.fn.__bind__ = jQuery.fn.bind;
    }
    if (jQuery.fn.__unbind__ === undefined){
        jQuery.fn.__unbind__ = jQuery.fn.unbind;
    }
    if (jQuery.fn.__find__  === undefined){
        jQuery.fn.__find__ = jQuery.fn.find;
    }

    var hotkeys = {
        version: '0.7.9',
        override: /keypress|keydown|keyup/g,
        triggersMap: {},

        specialKeys: { 27: 'esc', 9: 'tab', 32:'space', 13: 'return', 8:'backspace', 145: 'scroll',
            20: 'capslock', 144: 'numlock', 19:'pause', 45:'insert', 36:'home', 46:'del',
            35:'end', 33: 'pageup', 34:'pagedown', 37:'left', 38:'up', 39:'right',40:'down',
            109: '-',
            112:'f1',113:'f2', 114:'f3', 115:'f4', 116:'f5', 117:'f6', 118:'f7', 119:'f8',
            120:'f9', 121:'f10', 122:'f11', 123:'f12', 191: '/',
 			18: 'alt', 17: 'ctrl', 16: 'shift' },

        modifKeys: { 18: 'alt', 17: 'ctrl', 16: 'shift' },

        shiftNums: { "`":"~", "1":"!", "2":"@", "3":"#", "4":"$", "5":"%", "6":"^", "7":"&",
            "8":"*", "9":"(", "0":")", "-":"_", "=":"+", ";":":", "'":"\"", ",":"<",
            ".":">",  "/":"?",  "\\":"|" },

        newTrigger: function (type, combi, callback) {
            // i.e. {'keyup': {'ctrl': {cb: callback, disableInInput: false}}}
            var result = {};
            result[type] = {};
            result[type][combi] = {cb: callback, disableInInput: false, shortcut:combi};
            return result;
        }
    };
    // add firefox num pad char codes
    //if (jQuery.browser.mozilla){
    // add num pad char codes
    hotkeys.specialKeys = jQuery.extend(hotkeys.specialKeys, { 96: '0', 97:'1', 98: '2', 99:
        '3', 100: '4', 101: '5', 102: '6', 103: '7', 104: '8', 105: '9', 106: '*',
        107: '+', 109: '-', 110: '.', 111 : '/'
        });
    //}

    // a wrapper around of $.fn.find
    // see more at: http://groups.google.com/group/jquery-en/browse_thread/thread/18f9825e8d22f18d
    jQuery.fn.find = function( selector ) {
        this.query = selector;
        return jQuery.fn.__find__.apply(this, arguments);
	};

    jQuery.fn.unbind = function (type, combi, fn){
        if (jQuery.isFunction(combi)){
            fn = combi;
            combi = null;
        }
        if (combi && typeof combi === 'string'){
            var selectorId = ((this.prevObject && this.prevObject.query) || (this[0].id && this[0].id) || this[0]).toString();
            var hkTypes = type.split(' ');
            for (var x=0; x<hkTypes.length; x++){
                delete hotkeys.triggersMap[selectorId][hkTypes[x]][combi];
            }
        }
        // call jQuery original unbind
        return  this.__unbind__(type, fn);
    };

    jQuery.fn.bind = function(type, data, fn){
        // grab keyup,keydown,keypress
        var handle = type.match(hotkeys.override);


        if (jQuery.isFunction(data) || !handle){
            // call jQuery.bind only
            return this.__bind__(type, data, fn);
        }
        else{
            // split the job
            var result = null,
            // pass the rest to the original $.fn.bind
            pass2jq = jQuery.trim(type.replace(hotkeys.override, ''));

            // see if there are other types, pass them to the original $.fn.bind
            if (pass2jq){
                result = this.__bind__(pass2jq, data, fn);
            }

            if (typeof data === "string"){
                data = {'combi': data};
            }
            if(data.combi){
                for (var x=0; x < handle.length; x++){
                    var eventType = handle[x];
                    var combi = data.combi.toLowerCase(),
                        trigger = hotkeys.newTrigger(eventType, combi, fn),
                        selectorId = ((this.prevObject && this.prevObject.query) || (this[0].id && this[0].id) || this[0]).toString();

                    //trigger[eventType][combi].propagate = data.propagate;
                    trigger[eventType][combi].disableInInput = data.disableInInput;

                    // first time selector is bounded
                    if (!hotkeys.triggersMap[selectorId]) {
                        hotkeys.triggersMap[selectorId] = trigger;
                    }
                    // first time selector is bounded with this type
                    else if (!hotkeys.triggersMap[selectorId][eventType]) {
                        hotkeys.triggersMap[selectorId][eventType] = trigger[eventType];
                    }
                    // make trigger point as array so more than one handler can be bound
                    var mapPoint = hotkeys.triggersMap[selectorId][eventType][combi];
                    if (!mapPoint){
                        hotkeys.triggersMap[selectorId][eventType][combi] = [trigger[eventType][combi]];
                    }
                    else if (mapPoint.constructor !== Array){
                        hotkeys.triggersMap[selectorId][eventType][combi] = [mapPoint];
                    }
                    else {
                        hotkeys.triggersMap[selectorId][eventType][combi][mapPoint.length] = trigger[eventType][combi];
                    }

                    // add attribute and call $.event.add per matched element
                    this.each(function(){
                        // jQuery wrapper for the current element
                        var jqElem = jQuery(this);

                        // element already associated with another collection
                        if (jqElem.attr('hkId') && jqElem.attr('hkId') !== selectorId){
                            selectorId = jqElem.attr('hkId') + ";" + selectorId;
                        }
                        jqElem.attr('hkId', selectorId);
                    });
                    result = this.__bind__(handle.join(' '), data, hotkeys.handler)
                }
            }
            return result;
        }
    };
    // work-around for opera and safari where (sometimes) the target is the element which was last
    // clicked with the mouse and not the document event it would make sense to get the document
    hotkeys.findElement = function (elem){
        if (!jQuery(elem).attr('hkId')){
            if (jQuery.browser.opera || jQuery.browser.safari){
                while (!jQuery(elem).attr('hkId') && elem.parentNode){
                    elem = elem.parentNode;
                }
            }
        }
        return elem;
    };
    // the event handler
    hotkeys.handler = function(event) {
        var target = hotkeys.findElement(event.currentTarget),
            jTarget = jQuery(target),
            ids = jTarget.attr('hkId');

        if(ids){
            ids = ids.split(';');
            var code = event.which,
                type = event.type,
                special = hotkeys.specialKeys[code],
                // prevent f5 overlapping with 't' (or f4 with 's', etc.)
                character = !special && String.fromCharCode(code).toLowerCase(),
                shift = event.shiftKey,
                ctrl = event.ctrlKey,
                // patch for jquery 1.2.5 && 1.2.6 see more at:
                // http://groups.google.com/group/jquery-en/browse_thread/thread/83e10b3bb1f1c32b
                alt = event.altKey || event.originalEvent.altKey,
                mapPoint = null;

            for (var x=0; x < ids.length; x++){
                if (hotkeys.triggersMap[ids[x]][type]){
                    mapPoint = hotkeys.triggersMap[ids[x]][type];
                    break;
                }
            }

            //find by: id.type.combi.options
            if (mapPoint){
                var trigger;
                // event type is associated with the hkId

				// Check if the key is a special key (including modifier keys)
                if(!shift && !ctrl && !alt) { // No Modifiers
                    trigger = mapPoint[special] ||  (character && mapPoint[character]);
                }
                else{
                    // check combinations (alt|ctrl|shift+anything)
                    var modif = '';
                    if(alt) modif +='alt';
                    if(ctrl) modif+= 'ctrl';
                    if(shift) modif += 'shift';
                    // modifiers + special keys or modifiers + character or modifiers + shift character or just shift character

					// Check if the key is a special key (including modifier keys)
					tmp = mapPoint[modif];

					trigger = mapPoint[modif+"+"+special];
					if (!trigger){
                        if (character){
                            trigger = mapPoint[modif+"+"+character]
                                || mapPoint[modif+"+"+hotkeys.shiftNums[character]]
                                // '$' can be triggered as 'Shift+4' or 'Shift+$' or just '$'
                                || (modif === 'shift' && mapPoint[hotkeys.shiftNums[character]]);
                        }
                    }
					if (!trigger){
						trigger = tmp;
					}
                }

                if (trigger){
                    var result = false;
                    for (var x=0; x < trigger.length; x++){
                        if(trigger[x].disableInInput){
                            // double check event.currentTarget and event.target
                            var elem = jQuery(event.target);
                            if (jTarget.is("input") || jTarget.is("textarea") || jTarget.is("select")
                                || elem.is("input") || elem.is("textarea") || elem.is("select")) {
                                return true;
                            }
                        }
                        // call the registered callback function
                        result = result || trigger[x].cb.apply(this, [event]);
                    }
                    return result;
                }
            }
        }
    };
    // place it under window so it can be extended and overridden by others
    window.hotkeys = hotkeys;
    return jQuery;
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