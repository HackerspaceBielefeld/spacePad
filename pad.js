var updater = false;
var timeout = false;
var focuson = false;
var tabID = false;

// ermittelt position des cursors in textarea
(function ($, undefined) {
    $.fn.getCursorPosition = function() {
        var el = $(this).get(0);
        var pos = 0;
        if('selectionStart' in el) {
            pos = el.selectionStart;
        } else if('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    }
})(jQuery);

//setzt cursor position in textarea
function setCursorPos(elemId, caretPos) {
    var elem = document.getElementById(elemId);

    if(elem != null) {
        if(elem.createTextRange) {
            var range = elem.createTextRange();
            range.move('character', caretPos);
            range.select();
        }
        else {
            if(elem.selectionStart) {
                elem.focus();
                elem.setSelectionRange(caretPos, caretPos);
            }
            else
                elem.focus();
        }
    }
}

function textFilter(str) {
	str = str.replace('<','&lt;');
	return str.replace('>','&gt;');
}

//absatz blocken weil bearbeitung durch anderen
function block(id) {
	$('#content_div_'+id).addClass('content_block').removeClass('content_div');
}

//absatz block beenden
function unblock(id) {
	$('#content_div_'+id).removeClass('content_block').addClass('content_div');
}

function change(id,text) {
	$('#content_div_'+id).html(text);
	$('#content_text_'+id).html(text);
	
}

function addline(id,newid,text) {
	$('#content_'+id).after('<div id="content_'+newid+'" class="content">				<div id="content_div_'+newid+'" class="content_block">'+text +'</div>				<textarea id="content_text_'+newid+'" class="content_text" style="display:none;">'+text +'</textarea>			</div>');
}

function remline(id,text) {
	$('#content_'+id).prev().find('div').append(text);
	$('#content_'+id).prev().find('textarea').append(text);
	$('#content_'+id).remove();
}

// absatz zum ändern markieren
function edit(id) {
	$.ajax({
		type: "GET",
		url: "ajax.php?a=edit&doc="+doc +"&line="+id+"&writer="+cookie,

		async: true,
		cache: false,
		timeout:1000,
		success: function(data) {
			if(data == 'true') {
				$('#content_div_'+id).hide();
				$('#content_text_'+id).show().focus();
			}else{
				block(id);
			}
		}
	});
}

// on key up kram
function save(id) {
	if($('#content_text_'+id).val() != $('#content_div_'+id).html()){
		text = $('#content_text_'+id).val();
		$.ajax({
			type: "POST",
			url: "ajax.php?a=save&doc="+doc +"&line="+id+"&writer="+cookie,
			data: {text: text},
			
			async: true,
			cache: false,
			timeout:10000,
			success: function(data) {
				if(data == 'true') {
					$('#content_div_'+id).html(textFilter(text));
					$('#content_div_'+id).hide();
					$('#content_text_'+id).show();
				}else{
					//block(id);
				}
			}
		});
	}
}

// edit beenden und komplett speichern
function finish(id) {
	text = $('#content_text_'+id).val();
	$.ajax({
		type: "POST",
		url: "ajax.php?a=save&b=unblock&doc="+doc +"&line="+id+"&writer="+cookie,
		data : {text:text},

		async: true,
		cache: false,
		timeout:5000,
		success: function(data) {
			if(data != 'fail') {
				if(text == '') {
					text = '&nbsp;';
				}
				$('#content_div_'+id).html(textFilter(text));
				$('#content_div_'+id).show();
				$('#content_text_'+id).hide();
			}else{
				//block(id);
			}
		}
	})
}

// lebenszeichen vom user
function lifesign(id) {
	if(timeout != false) {
		clearTimeout(timeout);
	}
	
	if(updater != false) {
		clearTimeout(updater);
	}
	
	timeout = setTimeout(function() {
		finish(id);
	},60000);
	
	updater = setTimeout(function() {
		save(id);
	},5000);
}

// textarea höhe anpassen
function adjustTextarea(id) {
	o = document.getElementById('content_text_'+id);
	o.style.height = "1px";
	o.style.height = (5+o.scrollHeight)+"px";
}

// zwei absätze zusammenführen
function backspace(id) {
	text = $('#content_text_'+id).val();
	$.ajax({
		type: "POST",
		url: "ajax.php?a=cat&doc="+doc+"&line="+id+"&writer="+cookie,
		data: {text: text},

		async: true,
		cache: false,
		timeout:1000,
		success: function(data) {
			if(data != 'fail' && data != 'block') {
				d = JSON.parse(data);
				$('#content_'+id).remove();
				edit(d.id);
				len = $('#content_text_'+d.id).val().length;
				$('#content_text_'+d.id).val(d.text);
				setCursorPos('content_text_'+d.id, len)
			}else if(data == 'block') {
				altert('blocked');
			}
		},
	});
}

function checkInput(id) {
	//pos = getCursorPos('content_text_'+id);
	text = $('#content_text_'+id).val();
	match = /\r|\n/.exec(text);
	if (match) {
		text = text.split('\n');
		$('#content_text_'+id).val(text[0]);
		$.ajax({
			type: "POST",
			url: "ajax.php?a=add&doc="+doc+"&line="+id+"&writer="+cookie,
			data: {
				text: text[1],
				text2: text[0]
			},

			async: true,
			cache: false,
			timeout:1000,
			success: function(data) {
				if(data != 'fail') {
					newid = data;
					$('#content_'+id).after('<div id="content_'+newid+'" class="content">				<div id="content_div_'+newid+'" class="content_div" style="display:none;">'+text[1] +'</div>				<textarea id="content_text_'+newid+'" class="content_text">'+text[1] +'</textarea>			</div>');

					$('#content_text_'+newid).focus();
					setCursorPos('content_text_'+newid,0);
				}else{
					$('#content_text_'+id).val(text[0] + text[1]);
					setCursorPos('content_text_'+id, match.index);
				}
			},
		});
	}
	
	//TODO wenn backspace und cursor auf 0 textareas zusammen legen,außer wenn feld davor exclusive ist
	
	//prüfen ob enter im text
	adjustTextarea(id);
}

// wenn logpoll neue daten hat
function response(d) {
	switch (d.t) {
		case 'block':
			block(d.l);
			break;
		case 'unblock':
			unblock(d.l);
			break;
		case 'add':
			d2 = JSON.parse(d.d);
			addline(d.l,d2.newid,d2.text);
			break;
		case 'remove':
			remline(d.l,d.d);
			break;
		case 'change':
			change(d.l,d.d)
			break;
	}
}

//update funktion für live update
function longpoll() {
	if(doc > 0) {
		$.ajax({
			type: "GET",
			url: "ajax.php?doc="+doc+"&last="+last+"&writer="+cookie,

			async: true,
			cache: false,
			timeout:35000,

			success: function(data) {
				json = JSON.parse(data);
				if(json.last > last) {
					last = json.last;
				}
				if(json.data != undefined) {
					for(i=0;i<json.data.length;i++) {
						response(json.data[i]);
					}
				}
					
				setTimeout(
					longpoll,
					1000
				);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				
				setTimeout(
					longpoll,
					10000);
			}
		});
	}
};

$( document ).ready(function() {
	$('.document').on( "click",".content_div", function(event) {
		id = event.currentTarget.id.split('_')[2];
		edit(id);
		lifesign(id);
	});
	
	$('.document').on( "blur",".content_text", function(event) {
		id = event.currentTarget.id.split('_')[2];
		finish(id);
	});
	
	$('.document').on( "focus",".content_text", function(event){
		focuson = event.currentTarget.id.split('_')[2];
	});
	
	$('.document').on( "input propertychange",".content_text", function(event) {
		id = event.currentTarget.id.split('_')[2];
		checkInput(id);
		lifesign(id);
	});
	
	$('.document').on("keyup",".content_text", function(event){
		if(event.keyCode == 8) {
			id = event.currentTarget.id.split('_')[2];
			cur = $('#content_text_'+id).getCursorPosition();
			if(cur == 0) {
				backspace(id);
			}
		}
	});
	
	longpoll();
});
