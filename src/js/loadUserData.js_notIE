/**
 * 
 */
current_user = null;
has_to_check_user = true;
current_room = null;
added_skype_script = false;
   getUsersDataXml = function(user, room){

	   if (has_to_check_user) {
		   current_user = user;
		   current_room = room;
		   if (window.ActiveXObject) xmlReqUser = new ActiveXObject("Microsoft.XMLHTTP");
			else xmlReqUser = new XMLHttpRequest();
			var url="check.php?room="+room;
			xmlReqUser.onreadystatechange = processUserDataXml;
			xmlReqUser.timeout = 100000;
			xmlReqUser.overrideMimeType("text/xml");
			xmlReqUser.open("GET", url, true);
			xmlReqUser.send(null);
	   }
	}
	processUserDataXml = function(){
				if((xmlReqUser.readyState	==	4) && (xmlReqUser.status == 200)){
//when both connected show alert, change user->side images and central image
/*						setTimeout(function(){$("#imgR").attr('src','images/before_connecting<?php echo $user;?>.jpg');},1000);
						setTimeout(function(){$("#imgR").attr('src','images/connecting.jpg');},1500);
						showImage('<?php echo $user;?>');
						*/
					var users=xmlReqUser.responseXML.getElementsByTagName('usuarios');	
					total = users.length;
					if (total>0) {
						users = users[0].childNodes;
						total = users.length;
						if (total>0) {
							if (total>1) {
								//Posem ja conectats
								 has_to_check_user = false;
								 $('div.contenidor_esperant').toggleClass('contenidor_connectat')
								//$('#contenidor_usuaris').toggleClass('contenidor_connexio contenidor_connectat', addOrRemove);
							} else {
								//setTimeout('getUsersDataXml("'+current_user+'","'+current_room+'")',1000);
							}
							for (var i=0; i<total; i++) {
								/*if (users[i].childNodes[0].nodeValue != current_user) {
									alert("es diferent");
								}*/
								var obj = users[i];
								var user_id = obj.childNodes[0].nodeValue;
								if (user_id==current_user) {
									user_id = 'a';
								} else {
									user_id = 'b';
								}
								$("#name_person_"+user_id).html(getAttribute(obj, 'name'));
								img = getAttribute(obj, 'image');
								if (img.length>0)
									$("#image_person_"+user_id).html('<img height="40" width="28" src="'+img+'">');
								//se ocultan los puntos -> mappel
								//$("#points_person_"+user_id).html(getAttribute(obj, 'points')+' points');
								var skype = getSkypeContent(getAttribute(obj, 'skype'));
								var yahoo = getYahooContent(getAttribute(obj, 'yahoo'));
								var icq = getICQContent(getAttribute(obj, 'icq'));
								var msn = getMSNContent(getAttribute(obj, 'msn'));
								if (skype.length>0) {
									try {
										skypep = skype.split("skype:");								
										skypep = skypep[1].split("?call");
										skype = skypep[0];
										$("#chat_person_"+user_id).html("<a href='skype:"+skype+"?call'>SkypeUser <span class='icon skype'></span></a>");
									} catch (e){
										$("#chat_person_"+user_id).html("<a href='skype:"+skype+"?call'>SkypeUser <span class='icon skype'></span></a>");
									}
								}
							}
						}
					}
					}
			}
	getAttribute = function(obj, attrib) {

		var tmp = obj.attributes.getNamedItem(attrib);
		var r = '';
		if (tmp) {
			r = tmp.value;
		}
		return r;
	}
	getSkypeContent = function(skype) {
		var r= '';
		if (skype.length>0) {
			/*<!--
			Skype 'Skype Me™!' button
			http://www.skype.com/go/skypebuttons
			-->*/
			if (!added_skype_script) {
				var body = document.getElementsByTagName('body').item(0);
				var script = document.createElement('script');
				script.src = "http://download.skype.com/share/skypebuttons/js/skypeCheck.js";
				script.type = 'text/javascript';
				body.appendChild(script);
			}
			r = '<span class="mes_dades">Skype: <a href="skype:'+skype+'?call"><img src="http://mystatus.skype.com/smallicon/'+skype+'" style="border: none;" width="16" height="16" alt="Skype Me™!" /></a></span><br>';
		}
		return r;
	}
	getYahooContent = function(yahoo) {
		var r='';
		if (yahoo.length>0) {
			r = '<span class="mes_dades">Yahoo: <a href="http://edit.yahoo.com/config/send_webmesg?.target='+yahoo+'&amp;.src=pg">'+yahoo+' <img src="http://opi.yahoo.com/online?u='+yahoo+'&amp;m=g&amp;t=0" alt=""></a></span><br>';
		}
		return r;
	}
	getICQContent = function(icq) {
		var r='';
		if (icq.length>0) {
			
			r = '<span class="mes_dades">ICQ: <a href="http://web.icq.com/wwp?uin='+icq+'&action=message" target=_blank>'+icq+'</a></span><br>';
			
		}
		return r;
	}
	getMSNContent = function(msn) {
		var r='';
		if (msn.length>0) {
			
			r = '<span class="mes_dades">Msn: '+msn+'</span><br>';
			
		}
		return r;
	}