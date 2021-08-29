// Всегда считываем координаты курсора
var IE = document.all?true:false;
window.onmousemove = getMouseXY;
window.onclick = updateLastClickXY;
var cursorWindowX = 0;// Относительно окна
var cursorWindowY = 0;
var cursorPageX = 0;// Относительно документа
var cursorPageY = 0;
var clickWindowX = 0;// Относительно окна
var clickWindowY = 0;
var clickPageX = 0;// Относительно документа
var clickPageY = 0;
var adsCounter=0;//

var buff="";

/* SCROLLING SPEED-UP */
// var body = document.body, timer;
// var BhoverClass=false;
// window.addEventListener('scroll', function() {
//  clearTimeout(timer);
//  if(BhoverClass===false) {
//    classAdd("mainBody","disable-hover");
//  }
//  timer = setTimeout(function(){
//    classRemove("mainBody","disable-hover");
//  },500);
// }, false);

function getMouseXY(mevents) {
    if (IE) {
        cursorWindowX = event.clientX + document.body.scrollLeft;
        cursorWindowY = event.clientY + document.body.scrollTop;
    }
    else {
        cursorWindowX = mevents.clientX;
        cursorWindowY = mevents.clientY;
        cursorPageX = mevents.pageX;
        cursorPageY = mevents.pageY;
    }
    if (cursorWindowX < 0){cursorWindowX = 0;}
    if (cursorWindowY < 0){cursorWindowY = 0;}
    return true;
}
// Сохранение координат последнейго клика
function updateLastClickXY(){
    clickWindowX = cursorWindowX;
    clickWindowY = cursorWindowY;
    clickPageX = cursorPageX;
    clickPageY = cursorPageY;
    return true;
}


// Возвращает unix_timestamp
function time(){
    return Math.round(new Date().getTime() / 1000);
}

// Возвращает тэг по ID элемента
function getTagName(id){
    return document.getElementById(id).tagName;
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
	
// Аналог PHP функции print_r
// Распечатывает массив или любой объект (включая элементы DOM)
function print_r(variable,deep, index) {
	if (variable===null) { variable = 'null';}
	if (deep==undefined) { deep = 0;}
	if (index==undefined) { index = '';} else {index+=': ';}
	var mes = ''; var i = 0; var pre = '\n';
	while (i<deep) {pre+='\t'; i++;}
	if (variable && variable.nodeType!=false) {
		mes+=pre+index+'DOM node'+((variable.nodeType==1)? (' <'+variable.tagName+'>'): '');
	} else if (typeof(variable)=='object') {
		mes+=pre+index+' {';
		for (index in variable) {
			//noinspection JSUnfilteredForInLoop
            mes+=print_r(variable[index], (deep+1), index);
		}
		mes+=pre+'}';
	} else {
		mes+=pre+index+variable;
	}
	if (deep) {return mes;} else {alert(mes); return true}
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
        alert("IE не поддерживается!");
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


// Закрытие диалогового окна
function dialogClose(action){
	domRemove('AxiomDialogMask');
	domRemove('AxiomDialogMask2');
	domRemove('AxiomDialogMask3');
	if(scrollbarIsLocked==true) scrollbarLock();
	if(action!==undefined){
		if(redirectUrl!=false) redirect(redirectUrl);
		if(execFunction!=false) eval(execFunction);
	}
	redirectUrl=false;
	execFunction=false;
}

// Плавное растворение элемента
function slowlyHide(id, callback){
    if(getId(id)!=undefined){
        var fps = 45;
        var time = 1000;
        var steps = time / fps;
        var op = 1;
        var d0 = op / steps;
        var timer = setInterval(function(){
            op -= d0;
            if(getId(id).style!=undefined) getId(id).style.opacity = op;
            steps--;
            if(steps <= 0 ){
                getId(id).style.opacity=0;
                clearInterval(timer);
                if(callback!=undefined){
                    eval(callback);
                }
            }
        }, (1000 / fps));
    }
    else {
        return;
    }
}

// Плавное растворение элемента и его удаление
function slowlyDel(id){
  if(getId(id)!=undefined){
      var fps = 60;
      var time = 1200;
      var steps = time / fps;
      var op = 1;
      var d0 = op / steps;
      var timer = setInterval(function(){
        op -= d0;
        if(getId(id).style!=undefined) getId(id).style.opacity = op;
        steps--;
        if(steps <= 0 ){
          clearInterval(timer);
          domRemove(id);
        }
      }, (1000 / fps));
  }
  else {
    return;
  }
}


// Message window
var aMessId=0;
var axMessageBlock=false;
function axiomMessage(text,color,time){
	if(axMessageBlock==false){
		var parentElem = document.body;
		var newDiv = document.createElement('div');
		newDiv.id = 'AxiomMessage';
		parentElem.appendChild(newDiv);
		axMessageBlock=true;
    }
	aMessId++;
	if(color==undefined) color='#000000';
	if(time==undefined) time=300;
	getId("AxiomMessage").innerHTML+='<div id="AxiomMessage'+aMessId+'" class="AxiomMessage" style="background:'+color+' !important">'+text+'</div>';
	setTimeout('slowlyDel("AxiomMessage'+aMessId+'")', time*1000);
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
	//window.location.replace(url);
	document.location.href=url;
}

// Open link in popup window
var scCount=0;
function popup(url){
    scCount++;
    var w=1100;
    var h=600;
    var width=windowWidth();
    var height=windowHeight();
    if(width<w) w=width-20;
    if(height<h) h=height-20;
    var left=0; var top=0;
    left=parseInt((width-w)/2);
    top=parseInt((height-h)/2);
    window.open(url,scCount+' AxiomCodeEDITOR','menubar="no",toolbar="no",location="no",status="no",width='+w+',height='+h+',left='+left+',top='+top);
    return false;
}


// Аналог функции PHP str_replace	
function str_replace(search, replace, subject) {
	return subject.split(search).join(replace);
}

// Аналог PHP функции	
function is_array(inputArray) {
    return inputArray && !(inputArray.propertyIsEnumerable('length')) && typeof inputArray === 'object' && typeof inputArray.length === 'number';
}

// Отображение / Скрытие левого блока
var leftBlockExpanded=false;
function expand(str) {
	var s = document.all[str].style.display;
	if (s == 'none') document.all[str].style.display = 'block';
	else document.all[str].style.display = 'none';
	if(str=="leftBlock"){
	    if(s=='none') leftBlockExpanded = true;
	    else leftBlockExpanded=false;
	}
}


function urlTranslit(str){
     var cyr2latChars = new Array(
['а', 'a'], ['б', 'b'], ['в', 'v'], ['г', 'g'], ['д', 'd'],  ['е', 'e'], ['ё', 'yo'], ['ж', 'zh'], ['з', 'z'], ['и', 'i'], ['й', 'y'], ['к', 'k'], ['л', 'l'], ['м', 'm'],  ['н', 'n'], ['о', 'o'], ['п', 'p'],  ['р', 'r'], ['с', 's'], ['т', 't'], ['у', 'u'], ['ф', 'f'], ['х', 'h'],  ['ц', 'c'], ['ч', 'ch'],['ш', 'sh'], ['щ', 'shch'], ['ъ', ''],  ['ы', 'y'], ['ь', ''],  ['э', 'e'], ['ю', 'yu'], ['я', 'ya'],
['А', 'a'], ['Б', 'b'],  ['В', 'v'], ['Г', 'g'], ['Д', 'd'], ['Е', 'e'], ['Ё', 'yo'],  ['Ж', 'zh'], ['З', 'z'], ['И', 'i'], ['Й', 'y'],  ['К', 'k'], ['Л', 'l'], ['М', 'm'], ['Н', 'n'], ['О', 'o'],  ['П', 'p'],  ['Р', 'r'], ['С', 's'], ['Т', 't'],  ['У', 'u'], ['Ф', 'f'], ['Х', 'h'], ['Ц', 'c'], ['Ч', 'ch'], ['Ш', 'sh'], ['Щ', 'shch'], ['Ъ', ''],  ['Ы', 'y'], ['Ь', ''], ['Э', 'e'], ['Ю', 'yu'], ['Я', 'ya'],
['a', 'a'], ['b', 'b'], ['c', 'c'], ['d', 'd'], ['e', 'e'], ['f', 'f'], ['g', 'g'], ['h', 'h'], ['i', 'i'], ['j', 'j'], ['k', 'k'], ['l', 'l'], ['m', 'm'], ['n', 'n'], ['o', 'o'], ['p', 'p'], ['q', 'q'], ['r', 'r'], ['s', 's'], ['t', 't'], ['u', 'u'], ['v', 'v'], ['w', 'w'], ['x', 'x'], ['y', 'y'], ['z', 'z'],
['A', 'a'], ['B', 'b'], ['C', 'b'], ['D', 'd'],['E', 'e'], ['F', 'f'],['G', 'g'],['H', 'h'],['I', 'i'],['J', 'j'],['K', 'k'], ['L', 'l'], ['M', 'm'], ['N', 'n'], ['O', 'o'],['P', 'p'], ['Q', 'q'],['R', 'r'],['S', 's'],['T', 't'],['U', 'u'],['V', 'v'], ['W', 'w'], ['X', 'x'], ['Y', 'y'], ['Z', 'z'],
[' ', '_'],['0', '0'],['1', '1'],['2', '2'],['3', '3'], ['4', '4'],['5', '5'],['6', '6'],['7', '7'],['8', '8'],['9', '9'], ['-', '_'],['_', '_']);
    var newStr = '';
     for (var i = 0; i < str.length; i++) {
        var ch = str.charAt(i);
        var newCh = '';
        for (var j = 0; j < cyr2latChars.length; j++) {
            if (ch == cyr2latChars[j][0]) { newCh = cyr2latChars[j][1]; 
			}
        }
        newStr += newCh;
    }
    newStr=str_replace('ks','x',newStr);
    newStr=newStr.replace(/[_]{2,}/gim, '_').replace(/\n/gim, '');
    return newStr.replace(/[-]{2,}/gim, '-').replace(/\n/gim, '');
}

// Генерация пароля
// принимает на вход длину пароля и сложность (1-3)
function passGenerator(len,simplify) {
    if(len==undefined) len=8;
    var passwd = '';
    var chars = '';
    if(simplify==undefined) simplify=3;
    if(simplify==1) chars='abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ';
    if(simplify==2) chars='abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789';
    if(simplify>=3) chars='abcdefghijklmn@#$%&?_=opqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789';
    for (i=1;i<=len;i++) {
        var c = Math.floor(Math.random()*chars.length + 1);
        passwd += chars.charAt(c)
    }
    return passwd;
}

// Проверка сложности пароля
// Возвращает число 1-5. 1-очень слабый, 2-слабый, 3-нормальный, 4-хороший, 5-Отличный
function passCheck(item, score) {
	var pass = item;
	var pass_length = item.length;
	var count=0;
	var passQuality=0;
	var reg = "\!\"\:\?\\\%\?\;\|\/\=\-\+\_\(\)\*\#\@\$\^\&\.\,\[\]\{\}\'\`\~№";
	if (pass_length > 0 && pass_length <= 5) { count += 3; count *= 1;}
	else if (pass_length > 5 && pass_length <= 8) { count += 5; count += passCCS(pass,pass_length); count *= 1.5; } 
	else if (pass_length > 8 && pass_length <= 12) { count += 10; count += passCCS(pass,pass_length); count *= 2; } 
	else if (pass_length > 12 && pass_length <= 17) { count += 15; count += passCCS(pass,pass_length); count *= 2.5; } 
	else if (pass_length > 17 && pass_length <= 20) { count += 20; count += passCCS(pass,pass_length); count *= 3; } 
	else if (pass_length > 21) { count += 25; count += passCCS(pass,pass_length); count *= 4; }	
	if (count <= 40) passQuality=1;
	else if (count > 41 && count <= 50) passQuality=1;	// очень слабый
	else if (count > 51 && count <= 80) passQuality=2; // слабый
	else if (count > 81 && count <= 200) passQuality=3;// нормальный
	else if (count > 201 && count <= 400) passQuality=4;// хороший
	else if (count > 401) passQuality=5;				// Отличный
	if(score!=undefined) return count;
	return passQuality;
}
function passCCS(pass,pass_length) {
	var count = 0;
	var i='';
	if (pass.match( /\d/ )){ i = pass_length - pass.replace(/\d/gm,'').length; count += 5; if (i >= 6) count += 10; }
	if (pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) { count += 8; i = pass_length - pass.replace(/.[!,@,#,$,%,^,&,*,?,_,~]/gm,'').length; if (i >= 3) count += 20; }
	if (pass.match(/[a-zа-я]/)) { count+=5; i = pass_length - pass.replace(/[a-zа-я]/gm,'').length; if (i >= 6) count += 10;}
	if (pass.match(/[A-ZА-Я]/)) { count += 5; i = pass_length - pass.replace(/[A-ZА-Я]/gm,'').length; if (i >= 6) count += 10; }
	if (pass.match(/\d/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) { count += 10;}
	if (pass.match(/\d/) && pass.match(/[a-zа-я]/)) { count += 8;}
	if (pass.match(/\d/) && pass.match(/[A-ZА-Я]/)) { count += 8;}
	if (pass.match(/[a-zа-я]/) && pass.match(/[A-ZА-Я]/)) { count += 6;	}
	if (pass.match(/[a-zа-я]/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) { count += 10; }
	if (pass.match(/[A-ZА-Я]/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) { count += 10; }
	if (pass.match(/\d/) && pass.match(/[A-Za-zА-Яа-я]/)) { count += 10; }
	if (pass.match(/\d/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/) && pass.match(/[a-zа-я]/)) { count += 15; }
	if (pass.match(/\d/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/) && pass.match(/[A-ZА-Я]/)) { count += 15; }
	if (pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)  && pass.match(/[A-Za-zА-Яа-я]/)) { count += 15; }
	if (pass.match(/\d/) && pass.match(/[A-Za-zА-Яа-я]/) && pass.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) { count += 20; }
	return count;
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
        value=value.replace(/\D+/g,"");
    }
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
                    console.log("#"+key+" не найден!");
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

// Обработка ответа сервера AJAX
function responseProcess(response, to, callb){
    if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
    var evalJs='';
    var raw=explode('|a|:|a|',response);
    if(raw[0]!="" && raw[0]!=false && raw[0]!="") console.log(raw[0]);
    value=raw[1];
    /* Если будут проблемы с распаковкой  */
    /* value.replace(/\\/g,"\\\\");       */
    if(value!='' && value!=undefined){
        evalJs=axiomJsonParse(JSON.parse(value),to);
    }
    if(callb != undefined) {
        var cbFunc=callb+'()';
        setTimeout(cbFunc, 10);
        callb=undefined;
    }
    runSearch();
    /*console.log(evalJs);*/
    // Если требуется исполнение скриптов из BACKEND
    if(evalJs!=''){
        eval(evalJs);
    }
}

// Отправка AJAX запроса методом GET
function ajaxGet(value,to,callb,timeout){
    value=str_replace('::','-',value);
    ckeditorsGetContents();
	aceGetContents();
    if(timeout==undefined) timeout=180;
    timeout=timeout*1000;
    if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
    var xhr = getxmlHttp();
    xhr.timeout=timeout;
    xhr.onreadystatechange = function(){
        if (xhr.readyState != 4) return;
        if (xhr.status == 200) {
            responseProcess(xhr.responseText, to, callb);
        }
    };
    xhr.open("GET", document.location.protocol+"//"+document.location.host+"/admin/axiom_req.php?action="+value+"&AXrand="+Math.random(), true);
    xhr.send();
    xhr.ontimeout = function() {
        alert("Превышено время ожидания ответа от сервера!");
        if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
    }
}

// Асинхронный запрос к серверу методом POST
function ajaxPost(form,action,to,callb,timeout){
    action=str_replace('::','-',action);
    ckeditorsGetContents();
	aceGetContents();
 	if(timeout==undefined) timeout=180;
 	timeout=timeout*1000;
 	var data = new FormData(getId(form));
 	data.append("action", action);
    data.append("AxiomRandomValue", Math.random());
 	var xhr = getxmlHttp();
 	if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "visible";
 	xhr.timeout=timeout;
 	xhr.open("POST", document.location.protocol+"//"+document.location.host+"/admin/axiom_req.php", true);
 	xhr.onreadystatechange = function() {
        if (xhr.readyState != 4) return;
        if(xhr.status == 200) {
            //console.log(xhr.responseText);
            responseProcess(xhr.responseText, to, callb);
        }
    }
 	xhr.send(data);
 	xhr.ontimeout = function() {
        alert("Превышено время ожидания ответа от сервера!");
        if(getId("ajaxWindow")) getId("ajaxWindow").style.visibility = "hidden";
    }
}

/* Инициализация Drag&Drop сортировки элементов списка */
var currentSortable=false;
function dragInit(id,action){
    new Sortable(getId(id),{
        animation: 150,  // ms, animation speed, `0` — without animation
        handle: ".drag", // Restricts sort start click/touch to the specified element
        sort: true,
        scroll: false,
        scrollSensitivity: 30,
        scrollSpeed: 2,
        draggable: ".drag", // Specifies which items inside the element should be sortable
        ghostClass: "ghost",
        // onEnd
        onEnd: function (evt){
            var dstatus=implode(',',getChilds(id));
            dstatus=dstatus.replace(/[^\d,]/g, '');
            if(currentSortable!=id+dstatus){
                // Если длина меньше 1600, отправка методом GET, иначе - POST
                if(dstatus.length>1600){
                    setValue("dragStatus",dstatus);
                    update(action);
                }
                else {
                    action+='&dragStatus='+dstatus;
                    ajaxGet(action);
                }
            }
            currentSortable=id+dstatus;
        }
    });
}


/* Инициализация Drag&Drop рядов таблицы */
var currentDrag=false;
function dragTableInit(id,action){
    if(currentDrag!=id){
        currentDrag=id;
        $('#'+id).tableDnD({
            onDrop: function(table, row) {
                var dstatus=str_replace("&",",",str_replace("drt[]=&","",$('#'+id).tableDnDSerialize())).replace(/[^\d,]/g, '');
                if(dstatus.charAt(0)==',') {
                    dstatus='s'+dstatus;
                    dstatus=str_replace("s,","",dstatus);
                }
                // Если длина меньше 1600, отправка методом GET, иначе - POST
                if(dstatus.length>1600){
                    setValue("dragStatus",dstatus);
                    update(action);
                }
                else {
                    action+='&dragStatus='+dstatus;
                    ajaxGet(action);
                }
            },
            dragHandle: "drag",
            onDragClass: "dragged"
        });
    }
}


// Установка активной вкладки
function AXsetTab(tabId,tabNum){
    var elm=getId(tabId);
    for (var i = 0; i < elm.childNodes.length; i++){
        // если тип узла - элемент
        if (elm.childNodes[i].nodeType == 1){
            var className=elm.childNodes[i].classList[0];
            if(className=="current"){
                classRemove('tab'+tabId+i,'visible');
                classRemove(elm.childNodes[i].id,'current');
            }
            // Делаем вкладку активной
            if(i==parseInt(tabNum)){
                classAdd(elm.childNodes[i].id,'current');
                classAdd('tab'+tabId+i,'visible');
            }
        }
    }
}

// Разрушение редакторов, расположенных в заданном ID
function ckeditorDestroyIn(divid){
    if (typeof getId == 'function' && typeof getElementsByTagName == 'function'){
        var m=getId(divid).getElementsByTagName('textarea');
        if(m.length>0){
            for (var i = 0; i < m.length; i++) {
                // Если имя ID содержит "ckedit"
                if(m[i].id.indexOf('ckedit') +1) {
                    var edname=m[i].id;
                    var execname="CKEDITOR.instances."+edname+".destroy()";
                    eval(execname);
                }
            }
        }
    }
}
// Удаление визредактора
function editorDestroy(num){
    var edname=window["editor"+num];
    if(edname){
        edname.destroy();
        edname = null;
        domRemove("cke_ckedit"+num);
    }
}

// Get all editor contents
function ckeditorsGetContents(){
    var m=document.getElementsByTagName('textarea');
    if(m.length>0){
        for (var i = 0; i < m.length; i++) {
            // Если имя ID содержит "ckedit"
            if(m[i].id.indexOf('ckedit') +1) {
                var edname=m[i].id;
                var cnt=eval("CKEDITOR.instances."+edname+".getData()");
                getId(edname).innerHTML=cnt;
                getId(edname).value=cnt;
                getId(edname).innerTEXT=cnt;
            }
        }
    }
}

// Get all ACE editor contents
function aceGetContents(){
	var m=getByClass("aceEditor");
	if(m.length>0){
		for (var i=0; i < m.length; i++){
			var index=onlyDigit(m[i].id);
			evalScript('getId("codeEditorField'+index+'").innerHTML=aceEditor'+index+'.getValue();');
		}
	}
}

/* EVAL alternative */
function evalScript(src){
	var el=document.createElement('script');
	el.setAttribute('type','text/javascript');
	el.appendChild(document.createTextNode(src));
	document.body.appendChild(el);
	return el;
}

// Поиск и запуск функций переданных из PHP
var existEditor=[];// Массив с виз редакторами
var existCodeEditor=[];// Массив с редакторами исходного кода
var aceEditorIsLoaded=false;
function runSearch(){
    // Очищаем переменную, отвечающую за старт Drag&Drop ячеек таблицы!!!!!
    currentDrag=false;// ВАЖНО!! Не убирать, иначе будут ошибки с Drag&Drop после AJAX
    existEditor=false;
    // Создание визуальных редакторов ckEditor и редакторов кода
    existEditor=[];
    for ( var i=0; i<10; i++ ) {
        if(getId("ckedit"+i)){
            // create editor with textarea class
            if(window["cke_ckedit"+i]==undefined){
                var toolbar = 'ck'+getId("ckedit"+i).className;
                window["editor"+i] = CKEDITOR.replace( "ckedit"+i ,{
                    toolbar: toolbar ,
                    height: getId("ckedit"+i).style.height,
                    width: getId("ckedit"+i).style.width
                });
            }
            existEditor[i]=true;
        }
    }
    aceEditorsActivate();
}

var aceEditorIsLoaded=false;
function aceEditorsActivate(){
	var aceArray=getByClass("aceEditor");
	if(aceArray!=undefined){
		if(aceEditorIsLoaded===false){
			aceEditorIsLoaded=true;
			includeScript(document.location.protocol+'//'+document.location.host+'/admin/plugins/ace/ace.js',aceEditorsActivate);
		}
		if(aceEditorIsLoaded===true){
			for(i = 0; i < aceArray.length; i++){
				var el=aceArray[i]
				var z=el.id;
				var lang=el.getAttribute('data-lang');
				var inl=el.getAttribute('data-inline');
				window["aceEditor"+(i+1)]=ace.edit(z);
				window["aceEditor"+(i+1)].setOptions({
					theme: "ace/theme/twilight",
					mode: {path:"ace/mode/"+lang, inline: inl },
					tabSize: 4,
					useSoftTabs: true,
					wrap: true,
					minLines:6,
					maxLines: Infinity,
					newLineMode: "unix",
					showPrintMargin: false,
					showInvisibles: false
				});
			}
		}
	}
	return true;
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
	
// Добавление интервала для запуска Ajax запросов через заданные промежутки времени
// Аргументы:
// 		elementId - ID элемента, который будет обновляться
//		function - Функция, которая запускается в backend файле через заданный интервал
//		backend - имя backend файла
//		interval - к-во секунд между запусками
//		method - get (по умолчанию),  или post
var ajaxLastTimer=false;
function ajaxTimer(id, func, backend, interv, method){
	if(typeof(method) === "undefined") method="get";
	else method="post";
	if(typeof(window["axiomTimer"+id])=="undefined") {
		if(aCompareString(method,"get")==true) ajaxLastTimer=setInterval("ajaxGet(\'"+func+"\',\'"+id+"\',\'"+backend+"\')", interv*1000);
		else ajaxLastTimer=setInterval("update(\'"+func+"\',\'"+id+"\',\'"+backend+"\')", interv*1000);
		window["axiomTimer"+id]=ajaxLastTimer;
		return ajaxLastTimer;
    }
	else return false;
}
	
//////////////////////
// Удаление таймера
function ajaxTimerRemove(id){
	clearInterval(id);
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
function removeHandler(object, event, handler, useCapture) {
    if (object.removeEventListener) { object.removeEventListener(event, handler, useCapture ? useCapture : false);
    } else if (object.attachEvent) { object.attachEvent('on' + event, handler); }
}
function addHandler(object, event, handler, useCapture) {
    if (object.addEventListener) { object.addEventListener(event, handler, useCapture ? useCapture : false);
    } else if (object.attachEvent) { object.attachEvent('on' + event, handler); }
}

// Контроль колеса мыши
function wheel(event) {
    var delta; // Направление: -1 - скролл вниз, 1 - скролл вверх
    event = event || window.event;
    if (event.wheelDelta) { delta = event.wheelDelta / 120; if (window.opera) delta = -delta; } 
	else if (event.detail) { delta = -event.detail / 3; }
    if (event.preventDefault)  event.preventDefault();// Запрещаем обработку события по умолчанию
    event.returnValue = false;
    return delta;
}

// Получение высоты видимой области экрана
function windowHeight() {
	var de = document.documentElement;
	return self.innerHeight || ( de && de.clientHeight ) || document.body.clientHeight;
}

// Получение ширины видимой области экрана
function windowWidth() {
	var de = document.documentElement;
	return self.innerWidth || ( de && de.clientWidth ) || document.body.clientWidth;
}

// Получение координаты вертикального скроллинга страницы
function getScrollTop(){
	return document.documentElement.scrollTop || document.body.scrollTop;
}
	
// Получение координаты горизонтального скроллинга страницы
function getScrollLeft(){
	return document.documentElement.scrollLeft || document.body.scrollLeft;
}

// Получение координат элемента DOM	относительно документа
// на входе - элемент DOM. На выходе - массив
function getOffset(elem) {
    if (elem.getBoundingClientRect) { return getOffsetRect(elem) } else { return getOffsetSum(elem) }
}
function getOffsetSum(elem) {
    var top=0, left=0;
    while(elem) {
        top = top + parseInt(elem.offsetTop);
        left = left + parseInt(elem.offsetLeft);
        elem = elem.offsetParent;
    }
    return {top: top, left: left}
}
function getOffsetRect(elem) {
    var box = elem.getBoundingClientRect();
    var body = document.body;
    var docElem = document.documentElement;
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
    var clientTop = docElem.clientTop || body.clientTop || 0;
    var clientLeft = docElem.clientLeft || body.clientLeft || 0;
    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    return { top: Math.round(top), left: Math.round(left) }
}

var cal_calendarId=false;
var cal_selectedDate=false;
var cal_monthNames=explode(",","Январь,Февраль,Март,Апрель,Май,Июнь,Июль,Август,Сентябрь,Октябрь,Ноябрь,Декабрь");
var calType=1;
var cal_isOpened=false;
var cal_selectedTime='';
function createCalendar(element,setMonth,setYear){
    if(cal_isOpened==false){
        cal_isOpened=true;
        cal_calendarId=element;
        var mn=element+"Cal";
        domCreate(mn);
    }
    else {
        if(cal_calendarId==element){
            if(setMonth==undefined && setYear==undefined){
                cal_close();
                return true;
            }
        }
        else {
            cal_close();
            createCalendar(element,setMonth,setYear);
            return false;
        }
    }
    var date=getId(element).value;
    if(date==""){
        var cs = new Date();
        var ccY = cs.getFullYear();
        var ccM = cs.getMonth()+1;
        var ccD = cs.getDate();
        date = cal_normalizeDate(ccD+'.'+ccM+'.'+ccY);
    }
    cal_selectedDate=cal_normalizeDate(date);
    var sdate=explode(".",date);
    var day=sdate[0];
    var month=sdate[1];
    if(setMonth!=undefined) month=setMonth;
    var mon=month-1;// Месяцы с 0 до 11
    var year=sdate[2];
    if(setYear!=undefined) year=setYear;
    var d= new Date(year, mon);

    // Текущий год
    var curstamp = new Date();
    var curYear = curstamp.getFullYear();
    var curMonth = curstamp.getMonth()+1;
    var curDate = curstamp.getDate();
    var segodnia = cal_normalizeDate(curDate+'.'+curMonth+'.'+curYear);

    var table = '<table><tr class="mheight"><td style="padding-bottom:12px"><i class="ic-erase" title="Очистить" onClick="setValue(\''+cal_calendarId+'\',\'\'); cal_close()"></i></td><td colspan="6" style="padding-bottom:12px"><i class="ic-x2" style="color:#999999; margin:2px 6px 0 0!important; float:right" title="Закрыть" onClick="cal_close()"></i></td></tr><tr class="mheight"><td class="m" onClick="cal_chMonth(\'yb\','+month+','+year+')"><i class="ic-left2"></i></td><td colspan="5" class="axcalyear"><div id="axcalYear"><span onClick="cal_yearSelector('+year+','+month+')">'+year+'</span></div></td><td class="m" onClick="cal_chMonth(\'yf\','+month+','+year+')"><i class="ic-right2"></i></td></tr><tr class="mheight"><td  class="m" onClick="cal_chMonth(\'b\','+month+','+year+')"><i class="ic-left"></i></td><td colspan="5" class="axcalyear">'+cal_monthNames[mon]+'</td><td class="m" onClick="cal_chMonth(\'f\','+month+','+year+')"><i class="ic-right"></i></td></tr><tr><th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>СБ</th><th>ВС</th></tr><tr>';
    // заполнить первый ряд от понедельника
    // и до дня, с которого начинается месяц
    for (i=0; i<cal_getDay(d); i++) {
        table += '<td class="hd"></td>';
    }
    // ячейки календаря с датами
    while(d.getMonth() == mon){
        var clname="m";
        var dn=cal_normalizeDate(d.getDate()+'.'+month+'.'+year);
        if(dn==segodnia) clname='curr';
        if(dn==cal_selectedDate) clname='sel';
        table += '<td id="d'+dn+'" class="'+clname+'" onClick="cal_sD(\''+dn+'\')">'+d.getDate()+'</td>';
        if (cal_getDay(d) % 7 == 6)table += '</tr><tr>';
        d.setDate(d.getDate()+1);
    }
    // добить таблицу пустыми ячейками, если нужно
    if (cal_getDay(d) != 0) {
        for (var i=cal_getDay(d); i<7; i++) table += '<td class="hd"></td>';
    }
    // Если класс datetime, то также отображаем timepicker
    if(getId(element).className=="datetime"){
        table+='</tr><tr><td class="hd" colspan="7">Время</td>';
    }
    table += '</tr></table>';
    getId(element+"Cal").innerHTML=table;
    getId(element+"Cal").className="axiomCalendar";
    getId(element+"Cal").style.display="block";
    var coords=getOffset(getId(element));
    getId(element+"Cal").style.left=(coords["left"]+1)+"px";
    getId(element+"Cal").style.top=(coords["top"]+25)+"px";
    return false;
}
// получает порядковый номер дня недели, от 0(пн) до 6(вс)
function cal_getDay(date) {
    var day = date.getDay();
    if (day == 0) day = 7;
    return day - 1;
}
// Установка выбранной даты
function cal_sD(date){
    if(getId("d"+cal_selectedDate)) getId("d"+cal_selectedDate).className="m";
    cal_selectedDate=date;
    var cl=getId("d"+date).className;
    if(cl!="sel") getId("d"+date).className="sel";
    else getId("d"+date).className="m";
    setValue(cal_calendarId,date);
    if(getId(cal_calendarId).className=="date"){
        cal_close();
        return false;
    }
    return true;
}
// Перевод дат вида 1.4.2020 в вид 01.04.2020
function cal_normalizeDate(date){
    var m=explode(" ",date);
    var prdate=explode('.',date);
    if(prdate[0]<10) prdate[0]='0'+prdate[0];
    if(prdate[1]<10) prdate[1]='0'+prdate[1];
    return str_replace("00","0",prdate[0]+'.'+prdate[1])+'.'+prdate[2];
}
// Изменение месяца или года
function cal_chMonth(val,month,year){
    if(val=="f"){
        month++;
        if(month==13){ year++; month=1; }
    }
    if(val=="b"){
        month--;
        if(month==0){ year--; month=12; }
    }
    if(val=="yf") year++;
    if(val=="yb") year--;
    createCalendar(cal_calendarId, month, year);
}
function cal_yearSelector(year,month){
    var mm=month;
    var m='<select id="cal_year" name="cal_year" onChange="cal_crYear()">';
    for(var y=(year+50); y>=(year-50); y--){
        var selected='';
        if(y==year) selected=' selected="selected"';
        m+='<option'+selected+' value="'+y+'">'+y+'</option>';
    }
    m+='</select>';
    getId("axcalYear").innerHTML=m;
}
function cal_crYear(){
    if(!mm) var mm=1;
    var year = getSelectValue("cal_year");
    createCalendar(cal_calendarId, mm, year);
}
function cal_close(){
    domRemove(cal_calendarId+"Cal");
    makeChangeEvent(cal_calendarId);
    cal_calendarId=false;
    cal_selectedDate=false;
    calType=1;
    cal_isOpened=false;
}
function makeChangeEvent(element_id){
    var element = getId(element_id);
    var o = document.createEvent('HTMLEvents');  // Создаём объект события, выбран модуль событий мыши
    o.initEvent( 'change', false, true); // Инициализируем объект события
    element.dispatchEvent(o);  // Запускаем событие на элементе
}


// Автосохранение поля при изменении. Запускается по таймеру, 
// Функцию надо вызывать по событию onKeyUp для элемента формы.
// В случае, если дольше чем SECONDS секунд с момента вызова изменилось содержимое
// поля с заданным ID, то запускается функция переданная в funcName
var AXcFields=[];
var AXtoCounter=0;
function fieldAutosave(fieldId,funcName,seconds){
    if(seconds==undefined) seconds=1;
    var sec=parseInt(seconds*1000);
    var curContent="";
    if(getId(fieldId).nodeName=="TEXTAREA") curContent=getId(fieldId).innerHTML;
    else curContent=getId(fieldId).value;
    if(AXcFields[fieldId]!=curContent) AXcFields[fieldId]=curContent;
    var fn='fieldASC("'+fieldId+'","'+funcName+'")';
    clearTimeout(AXtoCounter);
    AXtoCounter=setTimeout(fn, sec);
    getId(fieldId).focus();
}
function fieldASC(fieldId,funcName){
     AXcFields[fieldId]=getId(fieldId).value;
     clearTimeout(AXtoCounter);
     var m=setTimeout(funcName, 20);
     AXtoCounter=0;
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

// DRAG для окошек
// onmousedown="dragOBJ(objectId,event); return false;"
function agent(v) { return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0)); }
function xy(e,v) { return(v?(agent('msie')?event.clientY+document.body.scrollTop:e.pageY):(agent('msie')?event.clientX+document.body.scrollTop:e.pageX)); }
function dragOBJ(d,e) {
    function drag(e) { if(!stop) { d.style.top=(tX=xy(e,1)+oY-eY+'px'); d.style.left=(tY=xy(e,0)+oX-eX+'px'); } }
    var oX=parseInt(d.style.left),oY=parseInt(d.style.top),eX=xy(e,0),eY=xy(e,1),tX,tY,stop;
    document.onmousemove=drag; document.onmouseup=function(){ stop=1; document.onmousemove=''; document.onmouseup=''; };
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

// Получение массива элементов по имени класса
function getByClass(classList, node) {
    node = node || document;
    var list = node.getElementsByTagName('*');
    length = list.length;
    classArray = classList.split(/\s+/);
    classes = classArray.length;
    result = [];
    for(i = 0; i < length; i++) {
        for(j = 0; j < classes; j++)  {
            if(list[i].className.search('\\b' + classArray[j] + '\\b') != -1) {
                result.push(list[i]);
                break;
            }
        }
    }
    return result
}

// Установка значения SELECT по VALUE
function selectSetValue(id,value){
    var m;
    if(getId(id)!=false){
        m=getId(id);
    }
    else
    {
        m=getId("select"+id);
    }
    for(var i = 0; i < m.options.length; i++) {
        var o=m.options[i];
        if(o.value == value) {
            o.selected=true;
            m.selectedIndex = i;
            o.setAttribute("selected","selected");
        }
        else{
            o.selected=false;
            o.removeAttribute("selected");
        }
    }
    return false;
}


// Возвращает код нажатой клавиши
function key(event) {
    return ('which' in event) ? event.which : event.keyCode;
}

// Автотранслит.
// Транслитерация во время ввода
// на входе ID полей источника и приемника.
// Если задан LinkCheckbox, то транслитерация работает только если он установлен
function autotranslit(srcId,destId,linkCheckboxId){
    var checked=true;
    if(linkCheckboxId != undefined ) checked=getCheckboxValue(linkCheckboxId);
    if(checked==true) setValue(destId, urlTranslit(getId(srcId).value));
}
// Автокопирование
function autocopy(srcId,destId,linkCheckboxId){
    var checked=true;
    if(linkCheckboxId != undefined ) checked=getCheckboxValue(linkCheckboxId);
    if(checked==true) setValue(destId, getId(srcId).value);
}

function scrollDown(){
    window.scrollTo(0,50000);
    getId("msContent").focus();
    if(getId("tongue").style.left=="8px"){
        spanelSwitch();
    }
}

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
    newDiv.innerHTML = '<div id="RW'+id+'" class="roundWin"><i class="AxiomWinCloseBtn ic-cross" onClick="slowlyDel(\''+id+'\')"></i><div class="inRound">'+content+'</div></div>';
    parentElem.appendChild(newDiv);
}

// Copy to clipboard
function setClipboardText(text){
    var id = "mycustom-clipboard-textarea-hidden-id";
    var existsTextarea = document.getElementById(id);
    if(!existsTextarea){
        console.log("Creating textarea");
        var textarea = document.createElement("textarea");
        textarea.id = id;
        textarea.style.cssText = 'position:fixed; top:0; left:-9000px; width:1px; height:1px; padding:0; border:none; outline: none; box-shadow:none; background:transparent;';
        document.querySelector("body").appendChild(textarea);
        existsTextarea = document.getElementById(id);
    }
    existsTextarea.value = text;
    existsTextarea.select();
    try {
        var status = document.execCommand('copy');
        if(!status){
            alert("Выделите текст и скопируйте его в буфер обмена");
        }else{
            windowCreate("Текст скопирован в буфер обмена",true);
        }
    } catch (err) {
        console.log('Unable to copy.');
    }
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

/* admin panel login */
function adminLogin(){
    getId("primLoginBTN").style.display="none";
    getId("loginForm").style.opacity=0.35;
    getId("loginError").innerHTML="";
    ajaxPost("loginForm", "auth::authorize");
}

function adminPassRestore(){
	if(emailValidator(getId("rsmail").value)==false) {
        soundPlay('alert');
        getId("loginError").innerHTML='<div class="error">Недопустимый адрес электронной почты!</div>';
    }
}

// reset error message in password recovery form
function admPRReset(){
    getId("loginError").innerHTML='';
}
/* ICON SELECT WINDOW FUNCTIONS */
var lastIconSelectFieldId='';
function iconWindow(fieldId){
    var icon=getId(fieldId).value;
    lastIconSelectFieldId=fieldId;
    var allIcons=explode(",","enter,exit,save,library,home,office,building,shop,trash,trash2,keyboard,mouse,printer,lock,unlock,lock2,edit,editdoc,eyedrop,share,tools,magic,cogs,cog,equalizer,power,calc,chip,abacus,calckey,dice,key,hammer,cap,balance,attach,magnet,puzzle,scissors,calendar,calendar3,calendar4,calendar2,hglass,clock,clock2,eclock,alarmclock,history,timer,binoculars,glasses,search,zoom-in,zoom-out,bug,target2,target3,target,pin,pin2,loc,loc-add,map,compass,earth,sphere,wheelchair,aid,aid2,lifeb,shield,bomb,security,open,sale,cart,cart-add,basket,shipping,box,box-open,boxes,package,road,milestone,truck,phone,phoneup,phone-old,hphone,fax,pig,safe,money,rub,usd,euro,pound,yen,gift,diary,card,card-visa,card-master,card-paypal,barcode,qrcode,plus,plus2,plus3,ellipsis-h,ellipsis-v,minus,erase,x,cross,delete,cancel,eye,eye-plus,eye-minus,eye-blocked,trophy,snow,star-full,star-half,star-empty,shit,pan_tool,tup,tdown,db,server,stack,drive,download,upload,box-down,box-up,drawer,drawer2,download3,upload3,check,x2,check3,minus2,contrast,stop3,play2,pause,backward,forward2,stop,warning,alert,exclam,stop4,stop5,question,denied,info,info2,cursor,tree,folder,folder-open,folder-plus,folder-minus,folder-download,folder-upload,folder-sub,folder-dir,dirlock,fldset,file-empty,copy,paste,clipboard,diff,files-empty,file-text2,file-text,profile,file,file-text3,file2,file-picture,file-music,file-play,file-video,file-zip,file-pdf,file-openoffice,file-word,file-excel,file-loffice,file-text4,file-bin,file-code,file-symlink,file-media,doc-stroke,doc-fill,new-window,versions,newspaper,terminal,image,images,pictures,film,frame,film2,camera,aperture,movie,html,fcamera,play,book2,mirror,browser,window,terminal2,david,christ,muslim,male,female,sheriff,agent,usergroup,user2,user,user-plus,user-minus,user-check,users,child,child2,vcard,badge,person-mail,person-phone,contacts,person-calendar,person-pin,person-other,hotel,person-circle,bubble2,bubbles4,comments,book,books,tags,tag,bookmark,bookmarks,flag,cone,ticket,beaker,dashboard,movealt,fs-out,fs-in,loop2,next,return,zap,undo,reload,loop3,loop4,shuffle,sharell,narrow,move-v,move-h,infinite,refresh,enlarge,shrink,fork,align-a,align-b,align,moveup,movedown,moveleft,moveright,tab,extlink,dright,dplus,dstar,dminus,crop,ungroup,screen-full,screen-normal,codepen,pgbreak,pgbreak2,insert-template,table2,table,border-all,border-bottom,border-clear,border-horizontal,border-inner,border-left,border-outer,border-right,border-style,border-top,border-v,flip,flip-back,flip-front,format-shapes,gridoff,gridon,gradient,ruler,ruler2,down,up,left,right,up1,down1,left1,right1,up2,down2,right2,left2,up3,right3,down3,left3,ch,ch-check,rad,rad-checked,qmark,id,lquote,rquote,image2,chart-alt,bars,graph,pie-chart,stats-dots,stats-bars2,stats,headphones,mic,hear,ring,alarm,alarm-add,alarm-off,alarm-active,music,bullhorn,volume-down,volume-up,volume-off,volume-mute,loundry,droplet,star,pointer,at,mail-open,mail-close,mail2,mail-read,mail-drafts,grid,laptop,tablet,desktop,mobile,tv,socket,socket2,socket3,bat1,bat2,bat3,bat4,steps,heart,heart-broken,spades,clubs,diamonds,connect,spinner,access,numlist,list,menu1,menu,menu2,menu3,menu4,happy,smile,tongue,sad,wink,grin,cool,shocked,play3,pause2,stop2,bw,fw,prev2,next2,eject,move-up,move-down,sort-aasc,sort-adesc,sort-nasc,sort-ndesc,sort-amasc,sort-amdesc,command,shift,ctrl,opt,filter,text-height,text-width,font-size,bold,underline,italic,strikethrough,omega,sigma,spell,super,sub,text-color,format-clear,pilcrow,section,align-left,align-center,align-right,align-justify,indent-inc,indent-dec,newtab,embed,embed2,skype,fill,sim,svideo,usb,google,facebook,instagram,whatsapp,telegram,twitter,vk,rss,rss2,youtube,youtube2,linux,apple,android,windows,wiki,linkedin,chrome,firefox,ie,edge,safari,opera,squirrel,glass,spoon-knife,telescope,locator,satellite,beer,coffee,lab,pulse,microscope,atom,pcord,plug,bulb,leaf,meter,fire,briefcase2,painter,pacman,rocket,umbrella,avia,avia-takeoff,avia-land,avia-on,avia-off,e-station,game,fingerprint,briefcase,hdmi,gas-station,extinguisher,shower,anchor,transit,rail,bus,moto,traffic,swheel,boat,shuttle,car,carpolice,train,tram,hookup,subway,bike,oven,tele,radio2,refreg,lamp,wash,goat,sun,weather,rainy,lightning,cloud,ncloud,cloudy,thermo,none,celsius,fahrengate,export,robot,cattree,seo,mysql,usertree,snippet,pass,pirat,hacker");
    var len=allIcons.length;
    var list='';
    var style='';
    for(var i = 0; i < len; i++) {
        style='';
        if(icon=='ic-'+allIcons[i]) { style='style="background:#fff462" '; }
        list+='<i class="ic-'+allIcons[i]+'" '+style+'onClick="iconSet(\''+allIcons[i]+'\')" title="'+allIcons[i]+'"></i>';
    }
    windowCreate('<div class="iconSelectWindow">'+list+'</div>',true);
}
function iconSet(icon){
    getId(lastIconSelectFieldId).value="ic-"+icon;
    var ni=getId(lastIconSelectFieldId).nextSibling;
    ni.innerHTML='<i class="ic-'+icon+'"></i>';
    slowlyDel("AxiomDialogMask");
}

// NewInfoblock
var BUFval="";
function entityForm(divid,sec){
    BUFval=getId(divid).innerHTML;
    var zt="";
    if(sec!=undefined) zt=1;
    getId(divid).innerHTML='<div class="label">Название: </div><input type="text" id="nEntName" maxlength="64" value="" onKeyPress="autotranslit(this.id,\'nEntAlias\')" onKeyUp="autotranslit(this.id,\'nEntAlias\')"><div class="label">Alias: </div><input type="text" id="nEntAlias" maxlength="64" value=""><div class="btn" title="Сохранить" onClick="entQSAVE('+zt+')"><i class="ic-save"></i></div><div class="btn" onClick="MMsetVal(\''+divid+'\')"><i class="ic-undo" title="Отмена"></i></div>';
}
/* Save New Infoblock */
function entQSAVE(bubu){
    var v=getId("nEntName").value;
    var vv=getId("nEntAlias").value;
    var err='';
    if(v.length<2) { err+="Слишком короткое название!<br>"; }
    if(vv.length<2) { err+="Слишком короткий alias!<br>"; }
    if(err!=''){ dialogAlert(err); }
    else {
        var str="data::entityCreate?="+urlencode(v)+"&alias="+urlencode(vv);
        if(bubu!=undefined) { str+="&nextAct=list"; }
        ajaxGet(str);
    }
}

/* Delete Entity type */
function entityTypeDelete(id,confirm){
    if(id==0){
        dialogAlert("Нельзя удалить служебный инфоблок!");
        return false;
    }
    else {
        if(confirm==undefined) {
            dialogConfirm("Удалить тип инфоблока?","entityTypeDelete("+id+",1)");
        }
        else {
            domRemove("ent"+id);
            ajaxGet("data::typeDelete?="+id);
        }
    }
}


// Чекбокс. Бысрое редактирование
function cbox(id,field){
    ajaxGet("data::checkBox?="+id+"&field="+field);
}

// Установка чекбокса общих аттрибутов
function globalCbox(id,field){
    ajaxGet("data::checkBoxGlobal?="+id+"&field="+field);
}

// Открытие/закрытие ветки дерева
function trChang(trv){
    var prv="plus"+trv;
    trv="c"+trv;
    var mEl=getId(trv).style.display;
    if(mEl=="none") {
        getId(trv).style.display="block";
        getId(prv).className="show minus";
    }
    else {
        getId(trv).style.display="none";
        getId(prv).className="show";
    }
}

// Смена стиля при смене типа текстового поля
function changeTextareaType(id){
    var edType=getSelectValue("css"+id);
    var style="";
    if(edType=="") style="width:100%; height:120px;";
    if(edType=="axiom") style="width:100%; height:300px;";
    if(edType=="basic") style="width:100%; height:120px;";
    if(edType=="mini") style="width:100%; height:200px;";
    setValue("class"+id,style);
}

function checkAttrName(){
    autotranslit("ffname","ffalias");
    var value=getId("ffname").value;
    var alias=getId("ffalias").value;
    if(value.length>=3 && alias.length>=3) {
        getId("ftlist").disabled=false;
        var fn="ajaxGet('data::checkAttribute?="+urlTranslit(value)+"&alias="+urlTranslit(alias)+"&type="+getId('entTypeId').value+")";
        fieldAutosave("ffname",fn,1);
    }
    else getId("ftlist").disabled=true;
}

function MMsetVal(divid){
    getId(divid).innerHTML=BUFval;
}

function newAttr(prnt,name,dop,element){
    adsCounter++;
    var div = document.createElement("div");
    div.id = "adCN"+adsCounter;
    div.className = "btn-group";
    if(element=="varchar") div.innerHTML = '<input type="text" name="'+name+'" '+dop+' value="">';
    if(element=="int" || element=="decimal") div.innerHTML = '<input type="text" onkeypress="return inputNumber(event)" name="'+name+'" '+dop+' value="">';
    if(element=="calendar") div.innerHTML = '<input type="text" id="cal'+adsCounter+'" name="'+name+'" '+dop+' value="">';
    if(element=="googlemap") div.innerHTML = '<input type="hidden" id="gmap'+adsCounter+'" name="'+name+'" '+dop+' value=""><div class="btn" onClick="googleMapSimpleEditor(\'gmap'+adsCounter+'\')">Карта GoogleMaps<i class="ic-earth"></i></div>';
    div.innerHTML += '<div class="btn" style="padding:5px 0 0 6px" onclick="domRemove(\'adCN'+adsCounter+'\')" title="Удалить"><i class="ic-delete color-red"></i></div>';
    getId(prnt).appendChild(div);
}

function createSelValue(id,subm){
    var tex = getId("optName"+id).value;
    if(tex.length<2) {
        dialogAlert("Не заполнено название!");
        subm="undefined";
    }
    else subm=1;
    if(subm==1) {
        getId("grp"+id).innerHTML=buff;
        ajaxGet("data::attributeAddValue?="+id+"&name="+urlencode(tex));
    }
}

function calSet(elid){
    var v=explode("-",elid);
    var fieldId=v[0];
    var id=v[1];
    var exId=str_replace("d","fl",elid);
    if(getId(exId)) {
        domRemove(exId);
        classRemove(elid,"sel");
    }
    else {
        classAdd(elid,"sel");
        var el=document.createElement("input");
        el.type="hidden";
        el.id=exId;
        el.setAttribute("value",id);
        el.name="array["+getId("fiel"+fieldId).getAttribute("data-alias")+"][]";
        getId("fiel"+fieldId).appendChild(el);
    }
}

function itemCopy(id, entity, cat, confirm){
    if(confirm==undefined) dialogConfirm("<b>Создать копию объекта?</b><br>Копия будет полностью соответствовать оригиналу и примет все его свойства.","itemCopy("+id+","+entity+","+cat+",1)");
    else {
        ajaxGet('data::entityCopy?='+id+'&entity='+entity+'&cat='+cat);
    }
}


function setMainFile(id,entity){
    ajaxGet("data::setMainFile?="+id+"&entity="+entity);
    var m=getByClass("bm");
    var i;
    if(m!=undefined){
        for(i = 0; i < m.length; i++) {
            var z=m[i].id;
            getId(z).style.display="none";
        }
        getId("mico"+id).style.display="block";
        soundPlay("click",0.01);
    }
}

function fileDelete(id,renew){
    slowlyDel("d"+id);
    ajaxGet("data::fileDelete?="+id);
    if(renew!=undefined) {
        if(getId("upl"+renew)) getId("upl"+renew).style.display="block";
    }
    soundPlay("click",0.01);
}

function newAttrSelect(prnt,name){
    for(var i=0; i<3; i++){
        adsCounter++;
        var div = document.createElement("div");
        div.id = "adCN"+adsCounter;
        div.className = "btn-group";
        div.innerHTML = '<input type="text" name="attr[options][]" class="size-xl" maxlength="64" value=""><div class="btn" onclick="domRemove(\'adCN'+adsCounter+'\')"><i class="ic-minus"></i></div>';
        getId(prnt).appendChild(div);
    }
}

/* Редактор опции выпадающего списка */
function selEditor(id,value){
    getId("sopd"+id).innerHTML='<div class="btn-group"><input id="soed'+id+'" type="text" style="width:340px" value="'+value+'"><div class="btn" onClick="ajaxGet(\'soEditorSave?='+id+'&value='+getId('soed'+id).value+')"><i class="ic-save"></i></div><div class="btn" onClick="selEditorCancel('+id+',\''+value+'\')"><i class="ic-return"></i></div></div>';
}

/* Отмена редактирования значения SELECT */
function selEditorCancel(id,value){
    getId("sopd"+id).innerHTML='<b class="hand" onClick="selEditor('+id+',this.innerHTML)">'+value+'</b><i class="ic-delete" onclick="ajaxGet(\'data::selectOptionDelete?='+id+'\')"></i>';
}

// Закрытие окна редактора опций
function closeSelectEditor(id){
    ajaxGet("data::showSelectOptions?="+id+"&fieldId=edSel"+id);
}

// Сохранение новой опции выпадающего списка
function selectOptionSave(){
    var val=getId("newOptionValue").value;
    if(val!=""){
        ajaxPost("atrEdFrm","data::selectOptionSave");
        getId("newOptionValue").value="";
    }
}

// Апдейт служебного поля для таблицы-источника в аттрибутах инфоблока
function updateListSrc(){
    setValue("listsrc",getId("fldsel0").value+"."+str_replace(",",".",getId("fldsel1").value)+"."+getId("fldsel2").value);
}

// получение выделенного текста
function getSelectedText(){
    var text = "";
    if (window.getSelection) {
        text = window.getSelection();
    }else if (document.getSelection) {
        text = document.getSelection();
    }else if (document.selection) {
        text = document.selection.createRange().text;
    }
    return text;
}

// Окно редактирования карты Google
function googleMapSimpleEditor(id){
    windowCreate('<div style="width:800px;"><div class="field"><div class="info">Для поиска объекта введите адрес в формате \'Страна, город, улица, номер дома\' и нажмите на кнопку "найти". Если поиск прошел успешно, то в месте расположения объекта будет установлен маркер. Для более точного позиционирования маркера, просто схватите его и перетащите в нужное место карты, или нажмите в нужном месте карты левой клавишей мыши. После того, как установите другие параметры карты (зум, тип) просто нажмите кнопку \'сохранить\'.</div></div><div class="field"><div class="btn-group"><div class="label" style="width:180px;">Поиск по адресу: </div><input id="AXmapTEXT" type="text" class="size-l" value=""><div class="btn" onClick="googleMapSearchAddress(getId(\'AXmapTEXT\').value)"><i class="ic-search"></i>Найти</div></div></div><div class="field"><div class="btn-group"><div class="label" style="width:180px;">Описание:</div><input type="text" style="width:500px" id="AXmapDESC" value=""><div class="btn" onClick="googleMapSetData(\''+id+'\')"><i class="ic-save"></i>Сохранить</div></div></div><div id="AXIOMGoogleMap" style="width:100%; height:420px; font-size:11px !important;"></div></div>',true,"GoogleMapsEditor");

    var string=getId(id).value;
    if(string=='') string="53.90190776306808|27.46261775493622|15|0|";
    var d=explode('|',string);
    var mapType = (d[3] == 1) ? "satellite" : (d[3] == 2) ? "hybrid" : (d[3] == 3) ? "terrain" : "roadmap";
    getId("AXmapDESC").value=d[4];

    MAPEDIT = new google.maps.Map(getId("AXIOMGoogleMap"),{
        center: {lat: parseFloat(d[0]), lng: parseFloat(d[1])},
        zoom: parseInt(d[2]),
        mapTypeControl: true,
        disableDefaultUI: true,
        disableDoubleClickZoom: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP,// ROADMAP; SATELLITE; HYBRID; TERRAIN
    });
    MAPEDIT.setMapTypeId(mapType);

    MMark = new google.maps.Marker({
        position: {lat: parseFloat(d[0]), lng: parseFloat(d[1])}, draggable:true, map: MAPEDIT, title: d[4]
    });

    google.maps.event.addListener(MAPEDIT,"click", function(e) {
        MAPEDIT.panTo(e.latLng);
        MMark.setPosition(e.latLng);// change marker position
    });
}

// Вставляет данные редактора в поле INPUT
function googleMapSetData(id){
    var mT=MAPEDIT.getMapTypeId();
    var mapTypeName = (mT == "satellite") ? "1" : (mT == "hybrid") ? "2" : (mT == "terrain") ? "3" : "0";
    getId(id).value=MMark.position.lat()+'|'+MMark.position.lng()+'|'+MAPEDIT.getZoom()+'|'+mapTypeName+'|'+getId("AXmapDESC").value;
    domRemove('GoogleMapsEditor');
}

function googleMapSearchAddress(address){
    geocoder = new google.maps.Geocoder();
    geocoder.geocode( { 'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            MAPEDIT.panTo(results[0].geometry.location);
            MMark.setPosition(results[0].geometry.location);
            if(getId("AXmapDESC").value=="") getId("AXmapDESC").value=getId("AXmapTEXT").value;
        }
        else {
            alert("Ошибка! Объект не найден. Попробуйте написать адрес в следующем формате: 'Страна, город, улица, номер дома'");
        }
    });
}

// Эмулируем комбобокс из селекта
function comboEmulator(id,search){
    if(search!=''){
        elem=document.getElementById("field"+onlyDigit(id));
        var index=onlyDigit(id);
        var cnt='';
        for (var i=0; i < elem.options.length; i++){
            var str=elem.options[i].label.toString();
            if(str.toLowerCase().indexOf(search.toLowerCase()) + 1) {
                cnt+='<span onClick="comboSet('+index+','+elem.options[i].value+')">'+elem.options[i].label+'</span>';
            }
        }
        if(cnt==''){
            if(getId("axC"+index)!=false){
                domRemove("axC"+index);
            }
        }
        else {
            if(getId("axC"+index)===false){
                var div=document.createElement("div");
                div.id = "axC"+index;
                div.className = "cDataList";
                var prnt=getId("comboselect"+index);
                prnt.appendChild(div);
            }
            getId("axC"+index).innerHTML=cnt;
        }
    }
}
/* Установка значения COMBO-BOX */
function comboSet(id,value){
    domRemove("axC"+id);
    var m=getId("field"+id);
    for(var i = 0; i < m.options.length; i++) {
        var o=m.options[i];
        if(o.value == value) {
            o.selected=true;
            m.selectedIndex = i;
            o.setAttribute("selected","selected");
            getId("sel"+id+"txt").setAttribute("value",o.label);
            getId("sel"+id+"txt").value=o.label;
        }
        else{
            o.selected=false;
            o.removeAttribute("selected");
        }
    }
    return false;
}
function comboClear(item){
    item.value='';
    item.setAttribute("value","");
    domRemove("axC"+onlyDigit(item.id));
}

// ! Удалить аттрибут
function attrDel(id,confirm){
    if(confirm==undefined) dialogConfirm("Удалить аттрибут?","attrDel("+id+",1)");
    else {
        domRemove("fl"+id);
        ajaxGet("data::attributeDelete?="+id);
    }
}

function childEditorClose(entityType,parent,attrId){
    ajaxGet("showChildList?="+parent+"&type="+entityType+"&attrId="+attrId,"field"+attrId);
    domRemove("childEdWin");
}
// Редактирование дочернего товара
function editChild(item,entityType,parent,attrId){
    ajaxGet("data::edit?="+item+"&entity="+entityType+"&parent="+parent+"&asChild="+parent+"&attrId="+attrId);
}

// Удаление дочернего товара
function childDel(id,parent){
    slowlyDel("child"+id);
    ajaxGet("data::delete?="+id+"&noShow=1");
}

