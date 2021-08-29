<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";


class qwestions2 {

    static function showQwestion($val,$right=false){
        $out='';

        list($d,$m,$h,$i)=explode(':',$val['time']);
        $bg='cccccc';
        if($val['flag']=='r') $bg='ffbfa4';
        if($val['flag']=='y') $bg='eeed7c';
        if($val['flag']=='g') $bg='aef5af';

        $out.='<li id="qw'.$val['id'].'" class="userQwestion" style="background:#'.$bg.'">
        <i class="ic-down3" onClick="qhide('.$val['id'].')"></i> <b>'.$val['name'].'</b>
        <div>
            <span>'.(int) $d.' '.monthName($m).' '.$h.':'.$i.'</span>
            <i class="ic-cross" onClick="qwDel('.$val['id'].')"></i>
        </div>
        <div class="qwBody" id="clb'.$val['id'].'" style="display:block">
            <hr>
            <p class="qwContent">'.$val['qwestion'].'</p>
        </div></li>';

        return $out;
    }
	

	static function init(){
		global $admin;
		$out='';
		$out.='<div class="row">
		<ul class="breadCrumbs"><li><span>Вопросы к семинарам</span></li></ul>
		</div>';

		$hiddens=array();
		$readed=array();
		$maxId=0;
        $array=mysql::getArray("SELECT *, DATE_FORMAT(`time`, '%d:%m:%H:%i') AS `time` FROM `qwestion` WHERE hidden='0' ORDER BY id ASC");
        if($array!=false){
            foreach($array AS $val){
                $maxId=$val['id'];
                if($val['hidden']==1) $hiddens[]=$val;
                else $readed[]=$val;
            }
        }


		
		$out.='<div class="row">
		<div class="btn" onClick="ajaxGet(\'qwestions2::init\')"><i class="ic-delete"></i>Обновить</div>
		</div>
		
		<div class="row">
		    <div class="twelve columns">
		        <h2>Обработано:</h2>
		        <ul id="selQwestions">';
                    if($readed!=false){
                        foreach($readed AS $val){
                            $out.=self::showQwestion($val);
                        }
                    }
		        $out.='</ul>
		    </div>
		</div>
		';

		
		if(!isset($admin)) return array('cblock'=>$out);
		$admin->addHeadScript('
var timerId = setInterval(function() {
  ajaxGet(\'qwestions2::init\');
}, 40000);		
		
function qwDeleteAll(confirm){
    if(confirm==undefined) dialogConfirm("Вы действительно хотите удалить все вопросы? Действие необратимо.","qwDeleteAll(1)");
    else {
        ajaxGet("qwestions::deleteAll");
    }
}

function qwDel(id){
    slowlyDel("qw"+id);
    ajaxGet("qwestions::delete?="+id);
}
		
function qwColor(id,color){
    var bg="cccccc";
    if(color=="r") bg="ffbfa4";
    if(color=="y") bg="eeed7c";
    if(color=="g") bg="aef5af";
    getId("qw"+id).style.background="#"+bg;
    ajaxGet("qwestions::setBg?="+id+"&bg="+color);
}

function qwUnhide(id){
    var bg=getId("qw"+id).style.background;
    domRemove("uhAct"+id);
    var inner=getId("qw"+id).innerHTML;
    domRemove("qw"+id);
    var parentElem = getId("selQwestions");
    var newDiv = document.createElement("div");
    newDiv.id = "qw"+id;
    newDiv.className = "userQwestion";
    newDiv.style="background:"+bg;
    newDiv.innerHTML = inner;
    parentElem.appendChild(newDiv);
    ajaxGet("qwestions::unhide?="+id);
}

function qhide(id){
    var disp=getId("clb"+id).style.display;
    if(disp=="block") disp="none";
    else disp="block";
    getId("clb"+id).style.display=disp;
}
');
		$admin->addStyle('
.userQwestion{
    display:block;
    padding:12px;
    background:#dedede;
    margin:0 0 16px 0;
    box-sizing:border-box;
    border-radius:6px;
    font-size:14px;
}
.userQwestion div{
float:right;
color:#ffffff;
}
.userQwestion i.ic-down3{
    cursor:pointer;
}
.userQwestion div i{
    color:#000000;
    margin-left:20px;
    cursor:pointer;
}
.userQwestion p{
    font-size:14px;
    line-height:20px;
}
.actDiv{
    height:30px;
    display:inline;
    width:100%;
}
.actDiv i.ic-tag{
    font-size:20px;
    line-height:26px;
    cursor:pointer;
}
.qwBody{
    display:block;
    width:100% !important;
    float:none !important;
    clear:both;
}
.qwContent{
    color:#000000;
}
');
		return $out;
	}

}