var windowWidth;

windowWidth=getWindowWidth();

function sztSwitch(){
    var disp=getId("szt").style.display;
    if(disp=='block'){
        disp='none';
    }
    else{
        disp='block';
    }
	getId("szt").style.display=disp;
}

function mImgSet(k,id){
	var img='';
	if(getId("imdi"+k)!=false){
		img=getId("imdi"+k).getAttribute("src");
		img=str_replace('sys.jpg','s.jpg',img);
		img=str_replace('sys.png','s.png',img);
		getId("item"+id).style.backgroundImage='url("'+img+'")';
	}
}

function getWindowWidth() {
    var de = document.documentElement;
    return self.innerWidth || ( de && de.clientWidth ) || document.body.clientWidth;
}


// Алиас document.getElementById()
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

// Функция получает список ID дочерних элементов DOM
// по ID родительского
function getChilds(id){
    var childs = [];
	var list=getId(id).childNodes;
	for (var i=0; i<list.length; i++) {
	    if (1==list[i].nodeType) childs[i]=list[i].id;
	}
	return childs;
}
	
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

// Получение общего размера всех загружаемых файлов
// добавленных к инпуту
function fileinputSize(id) {
    var finp, size;
    finp=fileinputFiles(id);
    size=0;
	for (var i = 0;i<finp.length; i++) {
        size+=finp[i].size;
    }
    return size;
}

// Возвращает массив mime-типов всех файлов
// подготовленных к загрузке
function fileinputMimes(id) {
    var mimes=[];
    var finp=fileinputFiles(id);
	for (var i = 0;i<finp.length; i++) {
        mimes.push(finp[i].type);
    }
    return mimes;
}

// Возвращает массив имен всех файлов
// подготовленных к загрузке
function fileinputNames(id) {
    var names=[];
    var finp=fileinputFiles(id);
	for (var i = 0;i<finp.length; i++) {
        names.push(finp[i].name);
    }
    return names;
}

// Возвращает массив файлов для загрузки
function fileinputFiles(id){
    var finp;
    if ( typeof ActiveXObject == "function" ) { // IE
        finp = (new ActiveXObject("Scripting.FileSystemObject")).getFile(getId(id).value);
    } else { finp = getId(id).files; }
    return finp;
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

// Возвращает массив реальных стилей элемента
// var marginTop = getStyle(elem).marginTop;
function getStyle(elem) {
  return window.getComputedStyle ? getComputedStyle(elem, "") : elem.currentStyle;
}


// Получение значения value по ID заданного элемента SELECT 
function getSelectValue(id){
	if(getId(id)){
	    var sel = getId(id);
	    return sel.options[sel.selectedIndex].value;
    }
    else return false;
}

// Получение значения CHECKED заданного CHECKBOX
function getCheckboxValue(id){
    var v=getId(id).checked;
    if(v===true) return 1;
    return 0;
}

var redirectUrl=false;
var execFunction=false;


// Преобразование размера файла
function fileBytes(length){
	var i = 0, type = ['b','Kb','Mb','Gb','Tb','Pb'];
	while((length / 1000 | 0) && i < type.length - 1) {
		length /= 1024;
		i++;
	}
	return length.toFixed(2) + ' ' + type[i];
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

	
// Добавление DIV элемента к DOM
// divId - id нового DIV
// parentElement - родитель. По умолчанию - document.body
// content - контент
function domCreate(divId,parent,content){
	if(parent!=undefined) var parentElem = getId(parent).children[0];
	var m=document.createElement('div');
	if(divId!=undefined) m.id=divId;
	if(content!=undefined) m.innerHTML=content;
	document.body.appendChild(m);
	return true;
}
	
// Удаление заданного элемента DOM и всех его потомков
function domRemove(elementId){
    var element = getId(elementId);
    if (element) {
		element.parentNode.removeChild(element);
	}
}


// Редирект. Если history=true, то редирект попадет в историю
function redirect(url){
	document.location.href=url;
}


// Аналог функции PHP str_replace	
function str_replace(search, replace, subject) {
	return subject.split(search).join(replace);
}

// Аналог PHP функции	
function is_array(inputArray) {
    return inputArray && !(inputArray.propertyIsEnumerable('length')) && typeof inputArray === 'object' && typeof inputArray.length === 'number';
}

// Проверка e-mail на допустимость
function emailValidator(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

// Ограничение длины textarea
function textareaLimiter(field, size, id) {
    if (field.value.length > size) {
        field.value = field.value.substring(0, size);
    }
    if(getId("delimiter"+id)){
	    getId("delimiter"+id).innerHTML=field.value.length;
	}
}
 
// Разрешение вводить только цифровые значения в поле
function ensureNumeric(){
    var key = window.event.keyCode;
    if (key <48 || key >57)
	if(key==8 || key==37 || key==39) return true;
	else window.event.returnValue = false;
    return false;
}

// Оставляет от строки только цифры
// Если задан аргумент floats, то также разрешена точка для дробной части
function onlyDigit(value,floats){
    if(floats!=undefined){
        value=str_replace(",",".",value);
        value=value.replace(/[^\d.]/g, '');
        }
    else {
        value=value.replace(/D/g, '');
    }
    if(value=='') value=0;
    return value;
}

// Разрешает ввод в текстовое поле только цифровых символов
// Пример: <input type="text" onkeypress="return inputNumber(event)" />
function inputNumber(event,comma){
	var ccode=event.keyCode;
  if(ccode < 48 || ccode > 57) {
	  if(ccode==46 || ccode==44) {
		  event.returnValue=ccode;
	  }
	  else {
		  event.returnValue=false;
	  }
  }
}
	
// URL Кодирование строки
function urlencode(str) {
    str = (str + '').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
    //return encodeURIComponent(str);
}

// Создает ХЭШ для строки
function hash(s){
    return s.split("").reduce(function(a,b){a=((a<<5)-a)+b.charCodeAt(0);return a&a},0);
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
    value=str_replace('::','-',value);
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
    action=str_replace('::','-',action);
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
                setTimeout(cbFunc, 10);
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

/* PLAY SYSTEM SOUND */
function soundPlay(name,volume){
    if(volume==undefined) volume=0.1;
    var auName="AxiomAudio_"+name;
    if(getId(auName)){
        var au=getId(auName);
        au.volume=volume;
        au.currentTime=0;
        au.play();
        au.volume=volume;
    }
    else console.error("Блок аудио "+auName+" не найден в HTML коде страницы!");
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

// Перевод строки в верхний регистр	
function strtoupper(Str){
	var Buf = '';
	for(var i=0; i<Str.length; i++) 
		if(Str.charAt(i)>='a' && Str.charAt(i)<='z' || Str.charAt(i)>='A' && Str.charAt(i)<='Z')
			Buf = Buf + Str.charAt(i).toUpperCase();
		else
			Buf = Buf + Str.charAt(i);
	return(Buf);
}
	
	
// Сравнение строк без учета регистра
function aCompareString(Str, Str2){
	return strtoupper(Str.toString())==strtoupper(Str2.toString());
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
    var style='';
    if(modal!=false){
        newDiv.className = 'AxiomDialogMask';
        style=' style="position:fixed" ';
    }
    newDiv.innerHTML = '<div id="RW'+id+'" class="roundWin"'+style+'><i class="AxiomWinCloseBtn ic-close" onClick="slowlyDel(\''+id+'\')"></i><div class="inRound">'+content+'</div></div>';

    parentElem.appendChild(newDiv);
    var allst = getComputedStyle(getId("RW"+id));
    /*getId("RW"+id).style.cssText="margin: -"+parseInt(allst.height)/2+"px 0 0 -"+parseInt(allst.width)/2+"px;";*/
}


function goTop(acceleration, time) {
	acceleration = acceleration || 0.5;
	time = time || 10;
	var dx = 0; var dy = 0; var bx = 0; var by = 0; var wx = 0; var wy = 0;
	if (document.documentElement) { dx = document.documentElement.scrollLeft || 0; dy = document.documentElement.scrollTop || 0; }
	if (document.body) { bx = document.body.scrollLeft || 0; by = document.body.scrollTop || 0; }
	wx = window.scrollX || 0; wy = window.scrollY || 0;
	var x = Math.max(wx, Math.max(bx, dx)); var y = Math.max(wy, Math.max(by, dy));
	var speed = 1 + acceleration;
	window.scrollTo(Math.floor(x / speed), Math.floor(y / speed));
	if(x > 0 || y > 0) {
		var invokeFunction = "top.goTop(" + acceleration + ", " + time + ")"
		window.setTimeout(invokeFunction, time);
	}
	return false;
}

function scrollTop(){
	var el = document.getElementById("gotop");
	var stop = (document.body.scrollTop || document.documentElement.scrollTop);
	return false;
}

if (window.addEventListener){
	window.addEventListener("scroll", scrollTop, false);
	window.addEventListener("load", scrollTop, false);
}
else if (window.attachEvent){
	window.attachEvent("onscroll", scrollTop);
	window.attachEvent("onload", scrollTop);
}
window["top"] = {};
window["top"]["goTop"] = goTop;

// Форматирование телефонного номера на-лету
// <input type="text" onkeyup="telFormat( this )" value="+375">
function telFormat(elem,patern) {
    if(patern==undefined){
        patern='+37511 111-11-11'
    }
    var ptr = patern, arr = elem.value.match( /\d/g ), i = 0;
    if ( arr === null ) return;
    elem.value = ptr.replace( /\d/g, function( a, b ) {
        if ( arr.length ) i = b + 1;
        return arr.shift();
    }).substring( 0, i );
}


// Возвращает код нажатой клавиши
function key(event) {
    return ('which' in event) ? event.which : event.keyCode;
}

// function createFeedbackF(){
//     var out='<div class="row"><div class="four columns offset-by-four"><form method="post" id="wndForm"><div id="fbfError"><p>Мы не против критики, но, чтобы ваш отзыв отображался на сайте, соблюдайте нормы, принятые для поведения в общественном месте.</p></div><label for="usN">Ваше имя</label><input type="text" id="usN" name="fb[name]"><label for="usA">Отзыв</label><textarea name="fb[content]" id="usA"></textarea><div id="fbBut" class="button" onClick="fbFControl()">Сохранить</div></form></div></div>';
//     getId("fbFor").innerHTML=out;
// }

// function fbFControl(){
//     var err='';
//     if(getId("usN").value.length<3){
//         err+='Слишком короткое имя!<br>';
//     }
//     if(getId("usA").value.length<16){
//         err+='Слишком короткий текст отзыва!<br>';
//     }
//     if(err!=''){
//         getId("fbfError").innerHTML='<div class="error">'+err+'</div>';
//     }
//     else{
//         getId("fbBut").style.display='none';
//         ajaxPost('wndForm','client::feedbackSave');
//     }
// }


//
// function skidkaWin(){
//     windowCreate('<div class="row"><h4>Скидка 5% при следующем обращении</h4></div><div class="row"><p>При втором и последующих ремонтах Вам предоставляется скидка 5% на работы за каждый ремонт. Максимальная суммарная скидка 20%. Скидка фиксируется за автомобилем.</p><p>На запасные части скидка не распространяется.</p></div>',true);
// }

var wResizeTimeout;
window.onresize = function() {
    clearTimeout(wResizeTimeout);
    wResizeTimeout=null;
    wResizeTimeout=setTimeout("winResize()",500);
}

function winResize(){
    width=getWindowWidth();
    if(getId("RWAxiomDialogMask")!=false){
        var style=getComputedStyle(getId("RWAxiomDialogMask"));
        var left='-'+(width-parseInt(style.width))/2+'px';
        /*getId("RWAxiomDialogMask").style.marginLeft=left;*/
    }
}

function menuSwitch(){
    var status=getId("mobileMenu").style.display;
    if(status=='none') status='block';
    else status='none';
    getId("mobileMenu").style.display=status;
}


// var acrdOpened=false;
// function acrdSwitch(number){
//     if(number===acrdOpened){
//         getId("acrdBody"+acrdOpened).style.height='0';
//         acrdOpened=false;
//     }
//     else {
//         if(acrdOpened!==false){
//             getId("acrdBody"+acrdOpened).style.height='0';
//         }
//         var s=getStyle(getId("scrdIn"+number));
//         getId("acrdBody"+number).style.height=s.height;
//         acrdOpened=number;
//     }
// }

// function frendWin(){
//     windowCreate('<form method="post" id="frForm"><div class="row" id="frendError"><b>Для получения скидки оcтавьте ваш телефон и телефоны ваших друзей.</b></div><div class="row"><input type="text" name="f[tel]" id="userTel" onkeyup="telFormat( this )" placeholder="Ваш телефон"></div><div class="row">Телефоны ваших друзей:</div><div class="row"><input type="text" name="f[frends][]" id="ftel1" onkeyup="telFormat( this )"><input type="text" name="f[frends][]"  id="ftel2"  onkeyup="telFormat( this )"><input type="text" name="f[frends][]"  id="ftel3" onkeyup="telFormat( this )"><div class="button button-primary" onClick="frendFormControl()">Сохранить</div></div></form>',true);
// }
//
// function frendFormControl(){
//     var tel='';
//     var fTelCount=0;
//     var err='';
//     tel=onlyDigit(getId("ftel1").value);
//     if(tel.length>=12) fTelCount++;
//     tel=onlyDigit(getId("ftel2").value);
//     if(tel.length>=12) fTelCount++;
//     tel=onlyDigit(getId("ftel3").value);
//     if(tel.length>=12) fTelCount++;
//     if(fTelCount==0){
//         err+='Введите хотябы один телефон друга! ';
//     }
//
//     tel=onlyDigit(getId("userTel").value);
//     if(tel.length<=11 || tel==0) {
//         err+='Не заполнен номер вашего телефона! ';
//     }
//     if(err!=''){
//         getId("frendError").innerHTML='<div class="error">'+err+'</div>';
//     }
//     else {
//         ajaxPost('frForm','client::frendForm');
//     }
//
// }

function mMenuOpen(){
    var l=getId("mMenuDiv").style.right;
    if(l=='105%'){
        getId("mMenuDiv").style.right="0";
    }
    else{
        getId("mMenuDiv").style.right="105%";
    }
}

function msmOpen(id){
    console.log("sm"+id);
    if(getId("sm"+id)!=false){
        var h=getId("sm"+id).style.height;
        if(h=="auto"){
            getId("sm"+id).style.height='0';
            getId("sm"+id+"sign").innerHTML='<i class="ic-plus"></i>';
        }
        else{
            getId("sm"+id).style.height='auto';
            getId("sm"+id+"sign").innerHTML='<i class="ic-minus"></i>';
        }
    }

}

function discountQuery(id){
    var wc='<div id="oneClickWin"><form method="POST" id="oneClickForm"><div class="frmMessage" id="frmMessage"><p>Нашли этот товар дешевле, но хотите купить его у нас? Мы постараемся предложить более выгодные условия. Оставьте ссылку на сайт с более низкой ценой.</p></div>';
    wc+='<div><input type="hidden" id="zakid" name="zakaz[id]" value="'+id+'"><input type="text" id="zaktel" name="zakaz[tel]" value="" placeholder="+37529 000-00-00" onkeyup="telFormat(this)" oninput="telFormat(this)" onblur="telFormat(this)"></div>';
    wc+='<div><input id="zakName" type="text" name="zakaz[name]" value="" placeholder="Имя" title="Имя"></div>';
    wc+='<div><input id="zakLink" type="text" name="zakaz[link]" value="" placeholder="Ссылка" title="Ссылка"></div>';
    wc+='<div><span class="button" onClick="discPrepare()">Отправить заявку</span></div>';
    wc+='</form></div>';
    windowCreate(wc,"Купить за 1 клик",600,420,true);
    getId("zaktel").focus();
}

function discPrepare(){
    if(getId("zaktel").value.length<11){
        soundPlay("alert");
        getId("frmMessage").innerHTML='<div class="error">Введите правильный номер телефона в международном формате!</div>';
    }
    else ajaxPost("oneClickForm","shop::discountSend");
}


function oneClickForm(id,lang){
    var qw;
    var btn;
    var wn='';
    if(lang=='ru'){
        qw='Введите номер вашего телефона. Мы перезвоним в ближайшее время.';
        wn='Купить за 1 клик';
        btn='Отправить';
    }
    else {
        qw='Калі ласка, увядзіце нумар вашага тэлефона. Мы ператэлефануем у бліжэйшы час.';
        wn='Купіць за 1 клік';
        btn='Адправіць';
    }
    var wc='<div id="oneClickWin"><form method="POST" id="oneClickForm"><div class="frmMessage" id="frmMessage"><p>'+qw+'</p></div>';
    wc+='<div><input type="hidden" id="zakid" name="zakaz[id]" value="'+id+'"><input type="text" id="zaktel" name="zakaz[tel]" value="" placeholder="+37529 000-00-00" onkeyup="telFormat(this)" oninput="telFormat(this)"></div>';
    wc+='<div><input id="zakName" type="text" name="zakaz[name]" value="" placeholder="Имя" title="Имя"></div>';
    wc+='<div><span class="button" onClick="oneClickPrepare()">'+btn+'</span></div>';
    wc+='</form></div>';
    windowCreate(wc,wn,600,420,true);
    getId("zaktel").focus();
}

function oneClickPrepare(){
    if(getId("zaktel").value.length<11){
        soundPlay("alert");
        getId("frmMessage").innerHTML='<div class="error">Введите правильный номер телефона в международном формате!</div>';
    }
    else ajaxPost("oneClickForm","shop::OneClickSend");
}

function addtc(id,element,lang){
    addToCart(id,1,lang);
    var bt;
    if(lang=='ru'){
        bt='В корзине';
    }
    else{
        bt='У кошыке';
    }
    var classes = element.classList;
    if(classes.contains("iToCart")){
        classes.remove("iToCart");
    }
    if(classes.contains("inTheCart")===false){
        classes.add("inTheCart");
    }
    element.innerHTML='<i class="ic-cart"></i>'+bt;
}
function addToCart(id,count,lang){
    if(count==undefined) {
        count=1;
        if(getId("itmCount")!=false){
            count=getId("itmCount").value;
        }
    }
    if(count<1) count=1;
    if(id!=0) {
        ajaxGet('shop::addToCart?='+id+'&count='+count);
    }
    var wc;
    if(lang=='ru'){
	    wc='<div class="frmMessage"><p>Товар добавлен в корзину. <a href="'+document.location.protocol+'//'+document.location.host+'/cart/">Перейдите к оформлению заказа,</a> или закройте это окно, если хотите продолжить работу с магазином.</p></div>';
    }
    else {
	    wc='<div class="frmMessage"><p>Тавар дададзены ў кошык. <a href="'+document.location.protocol+'//'+document.location.host+'/be/cart/">Перайдзіце да афармлення замовы,</a> або зачыніце гэта акно, калі хочаце працягнуць працу з сайтам.</p></div>';
    }
    windowCreate(wc,true,"ACWin");
    setTimeout('domRemove("ACWin")',3000);
}

function cartSendPrepare(){
    if(getId("zaktel").value.length<6) alert("Нам надо знать номер вашего телефона!");
    else ajaxPost("zForm","AxiomWinDesc","catalog/ajax");
}

function cntQtt(i){
    var qtt=onlyDigit(getId("itmCount").value);
    if(i==1) qtt++;
    else{
        if(qtt>=2) qtt--;
    }
    if(qtt<=0) qtt=1;
    getId("itmCount").value=qtt;
}

function updatePriceByCount(count,price){
    getId("itmCount").value=count;
    getId("fullPrice").innerHTML=count*price;
}

function qttDec(id){
    if(id===undefined){
        if(getId("itemQtt").value>1) getId("itemQtt").value--;
    }
    else{
        var v=getId("itemQtt"+id).value;
        if(v>1){
            v--;
            ajaxGet("cartSetCount?="+id+"&count="+v,"cartTable","catalog/ajax");
        }
    }
}

function qttInc(id){
    if(id===undefined){
        if(getId("itemQtt").value<20) getId("itemQtt").value++;
    }
    else{
        var v=getId("itemQtt"+id).value;
        if(v<20){
            v++;
            ajaxGet("cartSetCount?="+id+"&count="+v,"cartTable","catalog/ajax");
        }
    }
}

/* change image */
function setImage(img){
    if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
    getId("mainImage").setAttribute("href",document.location.protocol+"//"+document.location.host+"/uploaded/"+str_replace("s.",".",img));
    getId("mainImage").getElementsByTagName("img")[0].setAttribute("src",document.location.protocol+"//"+document.location.host+"/uploaded/"+img);
    if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
}

/* show element close and go to his thumb */
function closeAndGo(id,p){
    setPageNumber(p,id);
}

function setPageNumber(num,id){
    getId("filterP").value=num;
    filterStart("cBlock",id);
}

// dynamically Change browser URL
function changeUrl(url){
    if(url!=undefined){
        window.history.pushState(null, null, url);
    }
    return true;
}

function itemShow(id,p){
    window.scrollTo(0,0);
    changeUrl(document.location.protocol+"//"+document.location.host+"/catalog/"+id+"/");
    ajaxGet("shop::itemShow?="+id+"&p="+getId("filterP").value);
    document.getElementsByTagName("h1")[0].innerHTML=getId("item"+id).getElementsByTagName("h6")[0].innerHTML;
}

var fwrCounter=false;
function filterWinRemove(){
    if(fwrCounter!=false){
        clearTimeout(fwrCounter);
    }
    fwrCounter=window.setTimeout('if(getId("AXIOMpreFilter")){domRemove("AXIOMpreFilter")}',3000);
}

function backToSearchResult(id){
    changeUrl(document.location.protocol+'//'+getId("filterPath").value);
    filterStart('cBlock');
    getId("lastItem").value=id;
}

// Асинхронный запрос к серверу методом POST
function filterStart(divid,goto){
    //document.getElementsByTagName("h1")[0].innerHTML=getId("catSrcName").value+" : Поиск в каталоге";
    changeUrl(document.location.protocol+'//'+getId("filterPath").value);
    if(getId("AXIOMpreFilter")){
        clearTimeout(document.DTo);
        domRemove("AXIOMpreFilter");
    }
    timeout=60000;
    if(divid=="cBlock"){
        if(goto==undefined) {
            window.scrollTo(0,0);
        }
        getId("filterAct").value="filterSend";
    }
    else {
        getId("filterP").value="0";
    }
    var data = new FormData(getId("AXCfilter"));
    // if(divid!="cBlock"){
    //     getId("filterAct").value="filterPrepare";
    // }
    //else {
    //    getId("filterAct").value="filterPrepare";
    //}

    if(divid==undefined) {
        divid="catalogBlock";
    }
    //var xhr = new XMLHttpRequest();
    if(!getId(divid)) alert("Destination DOM block id=\""+divid+"\" is not found in document!");
    else {
        if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
        //getId(divid).style.opacity=0.3;
        getId("prepareDivid").value=divid;
        ajaxPost("AXCfilter","shop::filterSend");
    }
}

/* Pagination */
function filterSetP(p){
    getId("filterP").value=p;
    filterStart('cBlock');
}

function changeOrder(){
    var order=getSelectValue("tovarOrder");
    ajaxGet("shop::changeOrder?="+order+"&url="+document.location.href);
}

// Запуск поиска товаров
function activateSearch(){
    var forSearch=urlencode(str_replace("-","ssDEFss",getId("forsearch").value));
    var t=getId("forsearch").value.length;
    if(t<3) alert("Слишком короткое слово для поиска!");
    else {
        redirect(document.location.protocol+"//"+document.location.host+"/catalog/search-"+forSearch);
    }
}
var qSearchTimeout=false;
function quickSearch(){
    var c=getId("searchArea").value;
    if(c.length<3){
        getId("qsResult").style.display="none";
        getId("qsResult").innerHTML="";
    }
    else{
        clearTimeout(qSearchTimeout);
        qSearchTimeout=setTimeout('qSearchStart()',500);
    }
}
function qSearchStart(){
    var c=getId("searchArea").value;
    ajaxGet("shop::quickSearch?="+urlencode(c));
    getId("qsResult").style.display="block";
}
function intoView(item){
    if(getId(item)!=false){
        getId(item).scrollIntoView();
    }
}

function cartChangeCount(item){
    var count=0;
    if(getId("crtcnt"+item)!=false){
        count=getId("crtcnt"+item).value;
        ajaxGet("shop::changeCount?="+item+"&count="+count);
    }
}

function deleteFromCart(item){
    if(getId("crtcnt"+item)!=false){
        ajaxGet("shop::deleteFromCart?="+item);
    }
}

function cartSent(step){
    if(step==1){
        ajaxGet("shop::cartSent?="+step);
    }
    else {
        ajaxPost("cartForm","shop::cartSent?=2");
    }
}

function setOplata(){
    var ot=getSelectValue("opList");
    if(ot=='Банковский перевод (Для юр. лиц)'){
        ajaxGet("shop::setYurLicoFields");
    }
    else{
        getId("yurLicoDiv").innerHTML='';
    }
}

window.onload = function() {
    if(windowWidth<900){
        if(getId("ctlBody")!=false){
            getId("ctlBody").style.display="none";
        }
    }
}

var catOpened=6;
var smTimeout=null;
var smOpened=false;
function openSubmenu(number){
    var style=document.getElementById("sm"+number).style.display;
    if(style=="none"){
        document.getElementById("sm"+number).style.display="block";
        catOpened=number;
    }
    else {
        document.getElementById("sm"+number).style.display="none";
        catOpened=false;
    }
}


function cmSwitch(){
    disp=document.getElementById("ctlBody").style.display;
    if(disp=="block") {
        disp="none";
    }
    else {
        disp="block";
    }
    document.getElementById("ctlBody").style.display=disp;
}

function overMenu(id){
    clearTimeout(smTimeout);
    smTimeout=null;
    if(document.getElementById("sv"+id)!=undefined){
        if(smOpened!=false && smOpened!="sv"+id){
            document.getElementById(smOpened).style.display="none";
        }
        smOpened="sv"+id;
        document.getElementById(smOpened).style.display="table";
    }
}


function outSubmenu(){
    if(smOpened!=false){
        clearTimeout(smTimeout);
        smTimeout=null;
        smTimeout=setTimeout("closeSubmenu()",1200);
    }
}

function closeSubmenu(){
    if(smOpened!=false){
        clearTimeout(smTimeout);
        document.getElementById(smOpened).style.display="none";
        smTimeout=null;
        smOpened=false;
    }
}

function waitSubmenu(){
    clearTimeout(smTimeout);
    smTimeout=null;
}

//Отображение окна авторизации
function lwOn(){
	getId("siteLoginWindow").style.display="block";
}
// Скрытие окна авторизации
function lwOff(){
	getId("siteLoginWindow").style.display="none";
}
function lwSwitch(){
	var display=getId("siteLoginWindow").style.display;
	if(display=="block") display="none";
	else display="block";
	getId("siteLoginWindow").style.display=display;
}
function clientAuth(){
	ajaxPost("clientSpecialForm","shop::clientAuth");
}
function clientLogout(){
	ajaxGet("shop::clientLogout");
}
function passwordForm(){
	ajaxGet("shop::passwordForm");
}

function registerFormShow(){
	ajaxGet('shop::registerForm');
}

function getCallWindow(){
	var winContent='<div id="callWindowForm"><form id="stForm" method="POST"><div id="stwError"></div><label for="sfName">Имя</label><input type="text" id="sfName" name="skf[name]"><label for="sfTel">Телефон *</label><input type="text" id="sfTel" name="skf[tel]" placeholder="+37529 000-00-00" onkeyup="telFormat(this)" oninput="telFormat(this)"><div class="button" onclick="callControl()">Жду звонка</div></form></div>';
	windowCreate(winContent,true,'callWin');
}

function callControl(){
	var err='';
	getId("stwError").innerHTML='';
	var dTel=getId("sfTel").value;
	if(dTel.length<12) err+="Введите номер телефона с кодом города или оператора! ";
	if(err!="") {
		getId("stwError").innerHTML='<div class="error">'+err+'</div>';
	}
	else {
		ajaxPost("stForm","contact::callSend");
	}
}

function switchSelected(id){
    ajaxGet("shop::switchSelected?="+id);
}

var curPic=0;
function changePic(id){
	getId("smallPic"+curPic).style.cssText="border:1px solid transparent;";
	getId("smallPic"+id).style.cssText="border:1px solid #000000;";
	getId("bigPic"+curPic).style.cssText="display:none;";
	getId("bigPic"+id).style.cssText="display:block;";
	curPic=id;
}