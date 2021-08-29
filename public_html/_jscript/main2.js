function getId(id){
	if(document.getElementById(id)) return document.getElementById(id);
	else return false
}
// Устанавливает для элемента ID значение VALUE
function setValue(id,value){
	if(getId(id)){
		getId(id).value=value;
		getId(id).setAttribute('value',value);
	}
	else alert('DOM Element #'+id+' not found!');
}

// Динамическое добавление события к элементу
// Пример: eventAdd( getId('name'), 'click', function());
function eventAdd(elm, evType, fn, useCapture) {
	if (elm.addEventListener) {
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent) {
		return elm.attachEvent('on' + evType, fn);
	}
	else elm['on' + evType] = fn;
}

// Mouse Wheel control
function wheel(event) {
	var delta; // Направление: -1 - скролл вниз, 1 - скролл вверх
	event = event || window.event;
	if (event.wheelDelta) { delta = event.wheelDelta / 120; if (window.opera) delta = -delta; }
	else if (event.detail) { delta = -event.detail / 3; }
	if (event.preventDefault)  event.preventDefault();// Запрещаем обработку события по умолчанию
	event.returnValue = false;
	return delta;
}

// Динамическое удаление события элемента
// Пример: eventDel( getId('name'), 'click', function() );
function eventDel(obj, e, h) {
	if (obj.removeEventListener) {
		obj.removeEventListener(e, h, false);
	} else if (obj.detachEvent) {
		obj.detachEvent('on'+e, h);
	}
	else obj['on'+e]=null;
}

function removeHandler(object, event, handler, useCapture) {
	if (object.removeEventListener) { object.removeEventListener(event, handler, useCapture ? useCapture : false);
	} else if (object.attachEvent) { object.attachEvent('on' + event, handler); }
}
function addHandler(object, event, handler, useCapture) {
	if (object.addEventListener) { object.addEventListener(event, handler, useCapture ? useCapture : false);
	} else if (object.attachEvent) { object.attachEvent('on' + event, handler); }
}

// Установка чекбокса
function checkboxSet(id){
	getId(id).checked = true;
	setValue(id,'1');
}

function checkboxReset(id){
	getId(id).checked = false;
	setValue(id,'0');
}

// Добавление CSS класса к элементу
function classAdd(id, className) {
	getId(id).classList.add(className);
}
// Удаление CSS класса
function classRemove(id, className) {
	if(getId(id)){
		if(getId(id).classList.contains(className)){
			getId(id).classList.remove(className);
		}
	}
}

var scrollbarIsLocked=false;
// Блокировка скроллбара
function scrollbarLock() {
	if(scrollbarIsLocked!=true){
		addHandler(window, 'DOMMouseScroll', wheel);/* Gecko */
		addHandler(window, 'mousewheel', wheel);/* Opera */
		addHandler(document, 'mousewheel', wheel);/* IE */
		scrollbarIsLocked=true;
	}
	else {
		removeHandler(window, 'DOMMouseScroll', wheel);
		removeHandler(window, 'mousewheel', wheel);
		removeHandler(document, 'mousewheel', wheel);
		scrollbarIsLocked=false;
	}
	return false;
}

/* Функции управляющие окном контактов */
var hcWinTimer=false;
function hcWinOpen(){
	clearTimeout(hcWinTimer);
	hcWinTimer=false;
	var s=getComputedStyle(document.querySelector("#hcWin"));
	document.getElementById("hcWin").style.top='100%';
	document.getElementById("hcWin").style.opacity='1';
}

function hcWinClose(){
	if(hcWinTimer===false){
		hcWinTimer=setTimeout(function() {
			document.getElementById("hcWin").style.top='-500px';
			document.getElementById("hcWin").style.opacity='0';
		}, 700);
	}
}

// Отображение / скрытие окна контактов по клику (для мобил)
function hcWinOpenForced(){
	var s=getComputedStyle(document.querySelector("#hcWin"));
	var toppos=s.top;
	var opa;
	if(toppos=='-500px'){
		toppos='100%';
		opa=1;
	}
	else{
		toppos='-500px';
		opa=0;
	}
	document.getElementById("hcWin").style.top=toppos;
	document.getElementById("hcWin").style.opacity=opa;
}

// Все что запускается после загрузки
window.onload = function(){
	eventAdd(document.getElementById("ttBlock"),'click',hcWinOpenForced);
	eventAdd(document.getElementById("ttBlock"),'mouseover',hcWinOpen);
	eventAdd(document.getElementById("ttBlock"),'mouseout',hcWinClose);
	eventAdd(document.getElementById("mobMenuButton"),'click',menuSwitch);
	eventAdd(document.getElementById("menuCloseButton"),'click',menuClose);
	eventAdd(document.getElementById("mnShowBtn"),'mouseover',menuOpen);
	eventAdd(document.getElementById("mnShowBtn"),'click',menuOpen);
	eventAdd(document.querySelector(".fiSIcon"),'click',searchFormSwitch);
	eventAdd(document.getElementById("forsearch"),'keyup',searchFormKey);
	eventAdd(document.getElementById('sfCloseButton'),'click',searchFormSwitch);
	var elements = document.querySelectorAll(".mnOff");
	for (var i = 0; i < elements.length; i++) {
		elements[i].onmouseover = function(){
			menuClose();
		};
	}
}

//
function searchFormSwitch(){
	var sf=getComputedStyle(getId("searchRow"));
	var el=document.getElementById("searchRow");
	var m=document.querySelector(".blackMask");
	if(sf.opacity==0){
		m.style.left='0';
		m.style.backgroundColor='rgba(0,0,0,0.6)';
		el.style.opacity=1;
		classAdd('searchRow','searchRowOpened');
		getId("forsearch").focus();
		getId("menuRow").style.boxShadow='none';
		getId("menuRow").style.zIndex=100;
	}
	else{
		m.style.left='-100%';
		m.style.backgroundColor='rgba(0,0,0,0.3)';
		el.style.opacity=0;
		classRemove('searchRow','searchRowOpened');
		getId("forsearch").blur();
		getId("menuRow").style.boxShadow='0 0 15px rgb(29 29 29 / 11%)';
		getId("menuRow").style.zIndex=10;
	}
}

// Прослушивание поисковой формы
function searchFormKey(){
	var value=getId("forsearch").value;
	/*onkeyup="if(key(event)==13){activateSearch();} else{quickSearch();}"*/
}

var clTimeout;
var cmProcess=false;
var curDirection='up';
jQuery("document").ready(function($){
	var nav = $('.menuRow');
	$(window).scroll(function () {
		if(cmProcess==false){
			if ($(this).scrollTop() > 36) {
				changeLogo('down');
				nav.addClass("menuRowFixed");
			} else {
				changeLogo('up');
				nav.removeClass("menuRowFixed");
			}
		}
	});
});

function changeLogo(direction){
	if(direction!=curDirection){
		clearTimeout(clTimeout);
		if(direction=='down'){
			getId("bLogo").style.opacity=0;
			clTimeout=setTimeout('getId("rLogo").style.opacity=1',500);
		}
		else{
			getId("rLogo").style.opacity=0;
			clTimeout=setTimeout('getId("bLogo").style.opacity=1',500);
		}
		curDirection=direction;
	}
}



var menuIsOpened=false;

/* Force open Menu */
function menuOpen(){
	if(menuIsOpened===false){
		menuSwitch(0);
	}
}

function menuClose(){
	if(menuIsOpened===true){
		menuSwitch(1);
	}
}
/* Открытие большого меню */

function menuSwitch(forceOpen){
	var m=document.querySelector(".blackMask");
	var menu=document.querySelector("#mainMenu");
	getId("splashBody").style.opacity=0;
	classAdd("menuCloseButton","rotate");

	if(forceOpen!=undefined){
		if(forceOpen===1){
			menuIsOpened=true;
		}
		else{
			menuIsOpened=false;
		}
	}

	if(menuIsOpened==false){
		menuIsOpened=true;
		m.style.left='0';
		m.style.backgroundColor='rgba(0,0,0,0.6)';
		getId("mainMenu").style.display='block';
		setTimeout(menuLinksUpdate,30);
		setTimeout('classRemove("menuCloseButton","rotate")',700);
		classAdd('mnShowBtn','mnHovered');
		classAdd('axHtml','noScroll');
		getId("searchRow").style.opacity=0;
		classRemove('searchRow','searchRowOpened');
		getId("menuRow").style.zIndex=100;
		getId("mainMenu").style.zIndex=100;
	}
	else{
		menuIsOpened=false;
		m.style.backgroundColor='rgba(0,0,0,0.3)';
		getId("mainMenu").style.opacity=0;
		getId("mainMenu").style.height='1px';
		classRemove('mnShowBtn','mnHovered');
		classRemove('axHtml','noScroll');
		getId("mainMenu").style.display='none';
		m.style.left='-100%';
		getId("menuRow").style.zIndex=20;
		getId("mainMenu").style.zIndex=10;
		getId("menuRow").style.boxShadow='0 0 15px rgb(29 29 29 / 11%)';
	}
}
function menuLinksUpdate(){
	getId("mainMenu").style.opacity=1;
	var sb=getComputedStyle(getId("splashBody"));
	setTimeout('getId("splashBody").style.opacity=1',400);
	getId("mainMenu").style.height=sb.height;
}

function subMenuSwitch(num){
	var s=getComputedStyle(getId("smenu"+num));
	var sb=getComputedStyle(getId("smenuBody"+num));
	var st=getComputedStyle(getId("splashBody"));
	var menuHeight=parseInt(st.height);
	var d=parseInt(s.height);
	if(d==1){
		d=parseInt(sb.height);
		menuHeight+=d;
		getId("str"+num).style.transform='rotate(90deg)';
	}
	else{
		d=1;
		menuHeight-=parseInt(sb.height);
		getId("str"+num).style.transform='rotate(0deg)';
	}
	getId("smenu"+num).style.height=d+'px';
	getId("splashBody").style.height=menuHeight+'px';
	getId("mainMenu").style.height=(menuHeight+20)+'px';
}

function switchRadio(id){
	var f = document.getElementById(id);
	var name = document.getElementById(id).name;
	var disabled=f.checked;
	var s=document.querySelectorAll("input[type='radio'][name='"+name+"']");
	console.log(disabled);
	if(disabled===true){
		// for (var i = 0; i < s.length; i++) {
		// 	s[i].checked=false;
		// }
		//document.getElementById(id).checked=false;

	}
	else{
		//document.getElementById(id).checked=true;
	}


	// var siblings=document.querySelectorAll("input[type='radio'][name='"+f+"']");
	// for (var i = 0; i < siblings.length; i++) {
	// 	if (siblings[i] != f)
	// 		siblings[i].oldChecked = false;
	// }
	// if (f.oldChecked){
	// 	f.checked = false;
	// 	f.oldChecked = f.checked;
	// }
	//console.log(siblings);
	//filterStart();
}

function filterSwitch(){
	var style=getComputedStyle(getId("catFilter"));
	var bodyStyle=getComputedStyle(getId("catFilterBody"));
	var height=parseInt(style.height);
	var bodyHeight=parseInt(bodyStyle.height)+30;
	if(height==0){
		getId("catFilter").style.height=bodyHeight+'px';
	}
	else{
		getId("catFilter").style.height='0px';
	}
}

/* item count in card */
function itemCount(id,sign){
	var cnt=parseInt(getId("cnt"+id).value);
	if(sign==0){
		cnt=cnt-1;
		if(cnt<1) {
			cnt=1;
		}
	}
	else{
		cnt=cnt+1;
		if(cnt>100) {
			cnt=100;
		}
	}
	getId("cnt"+id).value=cnt;
}



/*   --------------------------------------------------------------------------------  */

// Аналог PHP функции explode
function explode( delimiter, string ) {
	var emptyArray = { 0: '' };
	if ( arguments.length != 2 || typeof arguments[0] == 'undefined' || typeof arguments[1] == 'undefined' ){
		return null;
	}
	if ( delimiter === '' || delimiter === false || delimiter === null ){
		return false;
	}
	if ( typeof delimiter == 'function' || typeof delimiter == 'object' || typeof string == 'function' || typeof string == 'object' ){
		return emptyArray;
	}
	if ( delimiter === true ) delimiter = '1';
	return string.toString().split ( delimiter.toString() );
}

// Аналог PHP implode
function implode(separator,array){
	var temp = '';
	for(var i=0;i<array.length;i++){
		if(array[i]!==undefined){
			temp +=  array[i];
			if(i!=array.length-1){
				temp += separator  ;
			}
		}
	}
	return temp;
}


var xmlHttp = null;
function getxmlHttp(){
	if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
	else if (window.XMLHttpRequest) return new XMLHttpRequest();
	else {
		alert("Ajax не поддерживается");
		return null;
	}
}

// Отправка AJAX запроса методом GET
function ajaxGet(value,to,callb,timeout) {
	if(timeout==undefined) timeout=180;
	timeout=timeout*1000;
	if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
	var xhr = getxmlHttp();
	xhr.timeout=timeout;
	xhr.onreadystatechange = function(){
		if (xhr.readyState != 4) return;
		if (xhr.status == 200) {
			var evalJs='';
			var raw=explode('|a|:|a|',xhr.responseText);
			/*console.log(raw);*/
			if(raw[0]!='') console.log(raw[0]);
			value=raw[1];
			/* Если будут проблемы с распаковкой  */
			/* value.replace(/\\/g,"\\\\");       */
			if(value!='' && value!=undefined){
				evalJs=axiomJsonParse(JSON.parse(value),to);
			}
			//runSearch();
			if(callb != undefined) {
				var cbFunc=callb+'()';
				setTimeout(cbFunc, 10);
				callb=undefined;
			}
			// Если требуется исполнение скриптов из BACKEND
			if(evalJs!=''){
				eval(evalJs);
			}
			if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";

		}
	};
	xhr.open("GET", document.location.protocol+"//"+document.location.host+"/_core/axiom_req.php?action="+value+"&AXrand="+Math.random(), true);
	xhr.send();
	xhr.ontimeout = function() {
		alert("Превышено время ожидания ответа от сервера!");
		if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
	}
}

// Асинхронный запрос к серверу методом POST
function ajaxPost(form,action,to,callb,timeout){
	if(timeout==undefined) timeout=180;
	timeout=timeout*1000;
	var data = new FormData(getId(form));
	data.append("action", action);
	data.append("AxiomRandomValue", Math.random());
	var xhr = getxmlHttp();
	if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
	xhr.timeout=timeout;
	xhr.open("POST", document.location.protocol+"//"+document.location.host+"/_core/axiom_req.php", true);
	xhr.onreadystatechange = function() {
		if (xhr.readyState != 4) return;
		if(xhr.status == 200) {
			/*console.log(xhr.responseText);*/
			var evalJs='';
			var raw=explode('|a|:|a|',xhr.responseText);
			if(raw[0]!='') console.log(raw[0]);
			value=raw[1];
			/* Если будут проблемы с распаковкой  */
			/* value.replace(/\\/g,"\\\\");       */
			if(value!='' && value!=undefined){
				evalJs=axiomJsonParse(JSON.parse(value),to);
			}
			//runSearch();
			if(callb != undefined) {
				var cbFunc=callb+'()';
				setTimeout(cbFunc, 100);
				callb=undefined;
			}
			// Если требуется исполнение скриптов из BACKEND
			if(evalJs!=''){
				eval(evalJs);
			}
			if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
		}
	}
	xhr.send(data);
	xhr.ontimeout = function() {
		alert("Превышено время ожидания ответа от сервера!");
		if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
	}
}



// Парсинг вывода AJAX
function axiomJsonParse(json, div){
	var forEval='';
	//console.log(json);
	// Проход по массиву и обновление DOM
	for (var key in json) {
		if (json.hasOwnProperty(key)) {
			// Обработка специальных полей
			////////////////////////////////
			if(key=='ax_includeScript' || key=='ax_domRemove' || key=='ax_message' || key=='ax_javascript' || key=='ax_dialogConfirm' || key=='ax_dialogAlert' || key=='ax_redirect' || key=='ax_consoleLog' || key=='ax_consoleError' || key=='ax_styleSet' || key=='AXIOM_string' || key=='ax_sound' || key=='ax_classRemove' || key=='ax_classAdd' || key=='ax_selectSetValue' || key=='ax_window'){
				var jsonscr=json[key];
				// Исполняемый JavaScript
				if(key=='ax_redirect') {
					redirect(jsonscr);
					break;
				}
				// If return string value
				if(key=='AXIOM_string'){
					if(div!=undefined){
						if(getId(div)!=false){
							getId(div).innerHTML=jsonscr;
						}
						else {
							console.error('Не обнаружен блок вывода #'+div);
						}
					}
					else {
						if(jsonscr!==true && jsonscr!==false){
							console.error('Не задан идентификатор блока вывода DOM');
						}
					}
				}
				// Включаемые скрипты JS
				if(key=='ax_includeScript') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							includeScript(jsonscr[jskey]);
						}
					}
				}
				// Элементы, которые надо удалить
				if(key=='ax_domRemove') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey) && getId(jsonscr[jskey])!=false) {
							domRemove(jsonscr[jskey]);
						}
					}
				}
				// Добавление CSS класса к элементам DOM
				if(key=='ax_classAdd') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							if(getId(jsonscr[jskey]['id'])!=false){
								classAdd(jsonscr[jskey]['id'],jsonscr[jskey]['className']);
							}
						}
					}
				}
				// Добавление CSS класса к элементам DOM
				if(key=='ax_classRemove') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							if(getId(jsonscr[jskey]['id'])!=false){
								classRemove(jsonscr[jskey]['id'],jsonscr[jskey]['className']);
							}
						}
					}
				}
				// Установка значения SELECT
				if(key=='ax_selectSetValue') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							if(getId(jsonscr[jskey]['id'])!=false){
								classRemove(jsonscr[jskey]['id'],jsonscr[jskey]['value']);
							}
						}
					}
				}

				// Всплывающие сообщения
				if(key=='ax_message') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							axiomMessage(jsonscr[jskey]['message'], jsonscr[jskey]['background'], jsonscr[jskey]['time']);
						}
					}
				}

				// Всплывающие сообщения
				if(key=='ax_styleSet') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							if(getId(jsonscr[jskey]['id'])!=false){
								getId(jsonscr[jskey]['id']).style.cssText=jsonscr[jskey]['cssText'];
							}
							else {
								console.error('Элемент #'+jsonscr[jskey]['id']+' не найден!');
							}
						}
					}
				}

				// Вывод сообщения в консоль
				if(key=='ax_consoleLog') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							console.log(jsonscr[jskey]);
						}
					}
				}

				// Вывод сообщения в консоль
				if(key=='ax_consoleError') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							console.error(jsonscr[jskey]);
						}
					}
				}

				// Вставить исполняемый JavaScript
				if(key=='ax_javascript') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							forEval+=jsonscr[jskey];
						}
					}
				}

				// Окно подтверждения
				if(key=='ax_dialogConfirm') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							dialogConfirm(jsonscr[jskey]['query'], jsonscr[jskey]['click']);
						}
					}
				}

				// Окно подтверждения
				if(key=='ax_dialogAlert') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							dialogAlert(jsonscr[jskey]);
						}
					}
				}

				// Добавление CSS класса к элементам DOM
				if(key=='ax_window') {
					for (jskey in jsonscr) {
						if (jsonscr.hasOwnProperty(jskey)) {
							windowCreate(jsonscr[jskey]['content'],jsonscr[jskey]['modal'],jsonscr[jskey]['id']);
						}
					}
				}

				// Воспроизведение системных звуков
				if(key=='ax_sound') {
					soundPlay(jsonscr,0.01);
				}
			}
			else {
				if(getId(key)!=false){
					getId(key).innerHTML=json[key];
				}
				else {
					console.error("#"+key+" не найден!");
				}
			}

		}
	}
	return forEval;
}

// Аналог PHP in_array()
function in_array(value, array) {
	for(var i=0; i<array.length; i++){
		if(value == array[i]) return true;
	}
	return false;
}

var AXIOMincludedScript=[];
// Динамическая загрузка JavaScript
function includeScript(src, callback, appendTo) {
	if(in_array(src,AXIOMincludedScript)==false){
		var script = document.createElement('script');
		if (!appendTo) appendTo = document.getElementsByTagName('head')[0];
		if (script.readyState && !script.onload) {
			// IE, Opera
			script.onreadystatechange = function() {
				if (script.readyState == "loaded" || script.readyState == "complete") {
					script.onreadystatechange = null;
					callback();
				}
			}
		}
		else {
			if(callback!=undefined) script.onload = callback;
		}
		script.src = src;
		appendTo.appendChild(script);
		AXIOMincludedScript[AXIOMincludedScript.length] = src;
	}
}

// Создание диалога подтверждения
function dialogConfirm(query,onclick){
	var content='<div class="row" style="margin:0 0 30px 0; width:300px;">'+query+'</div><div class="row" style="text-align:right;"><div class="button button-primary" style="margin-right:8px;" onClick="(function(){'+onclick+'; domRemove(\'AxiomDialogMask\')})();">Да</div><div class="button" onClick="slowlyDel(\'AxiomDialogMask\')">Отмена</div></div>';
	windowCreate(content,true,"AxiomDialogMask");
}

function dialogAlert(content){
	soundPlay("alert",0.1);
	content='<div class="row" style="margin:0 0 30px 0; width:300px;">'+content+'</div><div class="row" style="text-align:right;"><div class="button button-primary" onClick="slowlyDel(\'AxiomAlertWin\')">ОK</div></div>';
	windowCreate(content,true,"AxiomAlertWin");
}

// Добавление CSS класса к элементу
function classAdd(id, className) {
	getId(id).classList.add(className);
}
// Удаление CSS класса
function classRemove(id, className) {
	if(getId(id)){
		if(getId(id).classList.contains(className)){
			getId(id).classList.remove(className);
		}
	}
}

// Плавное растворение элемента и его удаление
function slowlyDel(id){
	if(getId(id)!=undefined && getId(id)!=false){
		var fps = 60;
		var time = 1200;
		var steps = time / fps;
		var op = 1;
		var d0 = op / steps;
		var timer = setInterval(function(){
			op -= d0;
			getId(id).style.opacity = op;
			steps--;
			if(steps <= 0){
				clearInterval(timer);
				domRemove(id);
			}
		}, (1000 / fps));
	}
	else {
		return;
	}
}



// Создание окна на JavaScript
// Контент
// Создание окна на JavaScript
// Контент
function windowCreate(content,modal,id){
	if(id==undefined) id="AxiomDialogMask";
	if(modal==undefined) modal=false;
	if(getId(id)!=undefined){
		domRemove(id);
	}
	var parentElem = document.body;
	var newDiv = document.createElement('div');
	newDiv.id = id;
	if(modal!=false){
		newDiv.className = 'AxiomDialogMask';
	}
	newDiv.innerHTML = '<div id="RW'+id+'" class="roundWin"><i class="AxiomWinCloseBtn" onClick="slowlyDel(\''+id+'\')"></i><div class="inRound">'+content+'</div></div>';

	parentElem.appendChild(newDiv);
	var allst = getComputedStyle(getId("RW"+id));
	/*getId("RW"+id).style.cssText="margin: -"+parseInt(allst.height)/2+"px 0 0 -"+parseInt(allst.width)/2+"px;";*/
}

// dynamically Change browser URL
function changeUrl(url){
	window.history.pushState(null, "", url);
	return true;
}

// Аналог функции PHP str_replace
function str_replace(search, replace, subject) {
	return subject.split(search).join(replace);
}

// Оставляет от строки только цифры
// Если задан аргумент floats, то также разрешена точка для дробной части
function onlyDigit(value,floats){
	if(floats!=undefined){
		value=str_replace(",",".",value);
		value=value.replace(/[^\d.]/g, '');
	}
	else {
		value=value.replace(/[^\d\+]/g, '');
	}
	if(value=='') value=0;
	return value;
}


// Удаление заданного элемента DOM и всех его потомков
function domRemove(elementId){
	var element = getId(elementId);
	if (element) {
		element.parentNode.removeChild(element);
	}
}

function correctPrice(el){
	var pd=onlyDigit(el);
	var ind=document.getElementById(el).options.selectedIndex;
	var val = document.getElementById(el).options[ind].value;
	var p=explode("|",val);
	var weight=p[0];
	var price=onlyDigit(p[1],true);
	getId("priced"+pd).innerHTML=price;
	getId("price"+pd).innerHTML=price;
}

function addToCart(el) {
	var tovarid=onlyDigit(el);
	var price = getId("price" + el).innerHTML;
	var count = getId("cnt" + el).value;
	var weight="";
	var pomol="";
	if(getId("pomol"+el)){
		var ind=document.getElementById("pomol"+el).options.selectedIndex;
		pomol = document.getElementById("pomol"+el).options[ind].value;
	}
	if(getId("var"+el)){
		var indd=document.getElementById("var"+el).options.selectedIndex;
		weight  = document.getElementById("var"+el).options[indd].value;
		var p=explode("|",weight);
		weight=p[0];
	}
	ajaxGet("coffee::addToCart?="+tovarid+"&count="+count+"&price="+price+"&weight="+weight+"&pomol="+pomol);
}

function delFromCart(num,it){
	slowlyDel("tvc"+num);
	ajaxGet("coffee::deleteFromCart?="+encodeURIComponent(it));
}

// Меняем к-во товара в корзине
function cartCount(id,cname,sign){
	var cnt=parseInt(getId("cc"+id).value);
	var update=false;
	if(sign==0){
		if(cnt>=2){
			cnt=cnt-1;
			update=true;
		}
	}
	else{
		if(cnt<=100){
			cnt=cnt+1;
			update=true;
		}
	}
	if(update===true){
		getId("cc"+id).value=cnt;
		ajaxGet('coffee::cartCount?='+getId("cc"+id).value+'&cnt='+cnt+'&cname='+encodeURIComponent(cname));
	}
}

function cartSent(step){
	if(step==1){
		ajaxGet("coffee::cartSent?="+step);
	}
	else {
		ajaxPost("cartForm","coffee::cartSent?=2");
	}
}

// Форматирование телефонного номера на-лету
// <input type="text" onkeyup="telFormat( this )" value="+375">
function telFormat(elem,patern) {
	if(patern==undefined){
		patern='+375 11 111-11-11'
	}
	var ptr = patern, arr = elem.value.match( /\d/g ), i = 0;
	if ( arr === null ) return;
	elem.value = ptr.replace( /\d/g, function( a, b ) {
		if ( arr.length ) i = b + 1;
		return arr.shift();
	}).substring( 0, i );
}

function smsInputStart(){
	var m=getId("SubsMobTel").value;
	if(m==''){
		getId("SubsMobTel").value='+375 ';
	}
}

function smsTelFormat(elem){
	getId("subsError").innerHTML='';
	telFormat(elem);
}

// Контроль формы подписки на рассылку
function smsSubsControl(step){
	var m=onlyDigit(getId("SubsMobTel").value);
	// Проверка номера телефона
	if(step==undefined){
		if(m.length!=13){
			getId("subsError").innerHTML='<div style="color:#ff0000; font-size:14px;">Номер телефона не соответствует международному формату. Номер должен содержать код страны, код оператора и номер абонента. Например: +375 29 666-00-66</div>';
		}
		else{
			smsSubsControl(1);
		}
	}
	// Номер правильный - оформляем подписку
	if(step==1){
		ajaxGet('coffee::smsPodpiska?='+m);
	}
}

// Переключение вариантов оплаты в корзине
function setOplata(value){
	console.log(value);
}

