var openedCat=[[categoryParentId]];
function catalogQuickSearch(cat){
    if(getId("searchByName").value.length>=3){
        var value=urlencode(getId("searchByName").value);
        ajaxGet("data::searchResult?="+cat+"&mn=[[moduleAlias]]&search="+value);
    }
    else {
        getId("searchblock").innerHTML="";
    }
}

function lastRemove(cat,p,del){
    ajaxGet("data::lastDelete?="+del+"&cat="+cat+"&p="+p);
}

// Open category
function catOpen(id,p){
    var elid="a"+id;
    if(p==undefined) p=0;
    openedCat=id;
    var z=document.getElementsByClassName("a")[0].id;
    classRemove(z,"a");
    getId("cn"+onlyDigit(z)).style="color:#000000";
    classAdd(z,"y");
    classAdd(elid, "a");
    getId("cn"+id).style="color:#ea5300";
    ajaxGet("[[moduleAlias]]::items?=&cat="+id+"&p="+p);
    var bc="";
    bc+='<ul class="breadCrumbs"><li onClick="catOpen(4)"><span>[[moduleName]]</span></li>';
    if(id!=4){
        bc+='<li onClick="catOpen('+id+')"><span>'+getId("cn"+id).innerHTML+'</span></li>';
    }
    bc+='</ul>';
    getId("breadCrumbs").innerHTML=bc;
    window.scrollTo(0,0);
}

// Вставка из буфера обмена
function pasteFromBuffer(){
    bufferSwitch();
    ajaxGet("[[moduleAlias]]::pasteFromBuffer?="+openedCat);
}

// Удалить из буфера
function bufDel(id){
    ajaxGet("data::bufferDelete?="+id+"&mn=[[moduleAlias]]");
}

// Buffer window show \ hide
function bufferSwitch(){
    var r=getId("bufferBlock").style.right;
    if(r!="-34px"){
        getId("bufferBlock").style.right="-34px";
        ajaxGet("data::bufferShow?=&moduleAlias=[[moduleAlias]]&asArray=1");
    }
    else getId("bufferBlock").style.right="6000px";
}

// Сохранение дочернего товара
function childSave(entity,asChild,attrId){
    ajaxPost("itemForm"+entity,"save","field"+attrId,"[[moduleAlias]]");
}

// Удаление элемента
function itemDel(id,cat,p,confirm){
    if(confirm==undefined) dialogConfirm("Вы действительно хотите удалить элемент? Действие необратимо.","itemDel("+id+","+cat+","+p+",1)");
    else ajaxGet("data::delete?="+id+"&cat="+cat+"&p="+p+"&frontend=[[moduleAlias]]&noShow=1");
}
// Удаление папки
function catDel(id,confirm){
    if(confirm==undefined) dialogConfirm("Вы действительно хотите удалить папку? Действие необратимо.","catDel("+id+",1)");
    else {
        domRemove("ct"+id);
        slowlyDel("ctl"+id);
        ajaxGet("data::delete?="+id+"&noShow=1");
    }
}

function fileDelete(id,renew){
    slowlyDel("d"+id);
    ajaxGet("data::fileDelete?="+id);
    if(renew!=undefined) {
        if(getId("upl"+renew)) getId("upl"+renew).style.display="block";
    }
}
var decTimeout;
var soundTimeout;
function qeDecimalControl(attrId,entityId,value){
    clearTimeout(decTimeout);
    clearTimeout(soundTimeout);
	value=str_replace(',','.',value);
    value=str_replace(' ','',value);
	var elementId='qeIn'+attrId+'e'+entityId;
	var bgColor='1px solid #eeeeee';
	var z=stringIsFloat(value);
	if(z=='NaN'){
		console.error(value+' не соответствует типу Float');
		bgColor='1px solid #ff0000';
		soundTimeout=setTimeout('soundPlay("alert",0.05)',650);
	}
	else{
		decTimeout=setTimeout(decimalSaveInt,700,attrId,entityId,value);
    }
	getId(elementId).style.border=bgColor;
}
function decimalSaveInt(attrId,entityId,value){
	var elementId='qeIn'+attrId+'e'+entityId;
	var z=stringIsFloat(value);
	if(z!='NaN'){
	    ajaxGetHidden('data::saveQuickEdit?='+entityId+'&attrId='+attrId+'&value=\''+value+'\'');
    }
}
// Является ли строка числом
function stringIsFloat(value){
	if(/^(\-|\+|\.)?([0-9]+(\.[0-9]+)?|Infinity)$/
	.test(value))
	    return Number(value);
	return 'NaN';
}