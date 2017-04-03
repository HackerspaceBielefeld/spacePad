var updater = false;
var timeout = false;
var focuson = false;
var last = 0;

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

//absatz blocken weil bearbeitung durch anderen
function block(id) {
	$('#content_div_'+id).addClass('content_block').removeClass('content_div');
}

//absatz block beenden
function unblock(id) {
	$('#content_div_'+id).removeClass('content_block').addClass('content_div');
}

// absatz zum ändern markieren
function edit(id) {
	$.ajax({
		type: "GET",
		url: "ajax.php?a=edit&doc="+doc +"&line="+id,

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
			type: "GET",
			url: "ajax.php?a=save&doc="+doc +"&line="+id+"&text="+encodeURI(text),

			async: true,
			cache: false,
			timeout:1000,
			success: function(data) {
				if(data == 'true') {
					$('#content_div_'+id).html(text);
					$('#content_div_'+id).hide();
					$('#content_text_'+id).show();
				}else{
					//block(id);
				}
			}
		});
	}else{
		console.log('nosave');
	}
}

// edit beenden und komplett speichern
function finish(id) {
	text = $('#content_text_'+id).val();
	$.ajax({
		type: "GET",
		url: "ajax.php?a=save&doc="+doc +"&line="+id +"&text="+encodeURI(text),

		async: true,
		cache: false,
		timeout:5000,
		success: function(data) {
			if(data != 'fail') {
				if(text == '') {
					console.log('nbsp');
					//TODO
					text = '&nbsp;';
				}
				$('#content_div_'+id).html(text);
				$('#content_div_'+id).show();
				$('#content_text_'+id).hide();
			}else{
				//block(id);
			}
		}
	});
}

// lebenszeichen vom user
function lifesign(id) {
	console.log('start lifesign');
	
	if(timeout != false) {
		clearTimeout(timeout);
	}
	
	if(updater != false) {
		clearTimeout(updater);
	}
	
	timeout = setTimeout(function() {
		finish(id);
		console.log("timeout");
	},60000);
	
	updater = setTimeout(function() {
		save(id);
		console.log('zwischenspeichern');
	},5000);
}

// textarea höhe anpassen
function adjustTextarea(id) {
	o = document.getElementById('content_text_'+id);
	o.style.height = "1px";
	o.style.height = (5+o.scrollHeight)+"px";
}

// zwei absätze zusammenführen
function concat(id) {
	
}

function checkInput(id) {
	//pos = getCursorPos('content_text_'+id);
	text = $('#content_text_'+id).val();
	match = /\r|\n/.exec(text);
	if (match) {
		text = text.split('\n');
		console.log(text);
		$('#content_text_'+id).val(text[0]);
		$.ajax({
			type: "GET",
			url: "ajax.php?a=add&doc="+doc+"&line="+id +"&text="+encodeURI(text[1])+"&text2="+encodeURI(text[0]),

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
function response(data) {
	d = JSON.parse(data);
	switch (d.type) {
		case 'heartbeat':
			break;
		case 'block':
			block(d.id);
			break;
		case 'unblock':
			unblock(d.id);
			break;
		case 'add':
			//TODO
			break;
		case 'remove':
			//TODO
			break;
		case 'change':
			//TODO
			break;
		
	}
	console.log("response:");
	console.log(data);
}

//update funktion für live update
function longpoll() {
	if(doc > 0) {
		$.ajax({
			type: "GET",
			url: "ajax.php?doc="+doc+"&last="+last,

			async: true,
			cache: false,
			timeout:35000,

			success: function(data) {
				response(data);
				
				setTimeout(
					longpoll,
					1000
				);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				
				setTimeout(
					longpoll,
					15000);
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
		console.log("blur");
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
	
	$('.content_text').keyup(function(e){
		if(e.keyCode == 8) {
			id = event.currentTarget.id.split('_')[2];
			cur = $('#content_text_'+id).getCursorPosition();
			if(cur == 0) {
				
			}
		}
	});
	
	longpoll();
});

 // )  