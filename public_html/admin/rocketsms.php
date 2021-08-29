<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";


class rocketsms {

    static function quickSend(){
        $error="";
        if(isset($_POST['telnum'])){
            $tel=onlyDigit(trim($_POST['telnum']));
            if(strlen($tel)<12){
                $error.='Недопустимый номер телефона!<br>';
            }
            if(strlen($_POST['smstext'])<6){
                $error.='Слишком короткое сообщение!<br>';
            }
            if($error!=""){
                ajax::sound("alert");
                return array('mError'=>'<div class="error">'.$error.'</div>');
            }
            else {
                sms::send($tel,$_POST['smstext']);
                return array(
                    'mError' => '<div class="info">Сообщение успешно отправлено!</div>',
                    "qsdata" => '<div class="label">СМС:</div>
                    <div class="label"><i class="ic-mobile" style="float:left; margin:6px 0 0 0;"></i></div>
                    <input type="text" name="telnum" class="size-m" value="+375 ">
                    <div class="label">Текст:</div>
                    <input type="text" name="smstext" style="width:600px" maxlength="64" value="">
                    <div class="btn" onClick="ajaxPost(\'qsms\',\'rocketsms::quickSend\')"><i class="ic-check3"></i></div>'
                );
            }
        }


    }

    static function delete(){
        global $item;
        mysql::query("DELETE FROM `sms` WHERE id=".escape($item)." LIMIT 1");
        ajax::sound("click");
        return false;
    }

    static function send(){
        $error='';
        $smsCost=0.0155;
        if(isset($_POST['sms'])){
            $sms=$_POST['sms'];
            if(strlen($sms['text'])<=6){
                $error.='<p>Слишком короткий текст СМС рассылки!</p>';
            }
            $select=array();
            $select[]="t1.tel, t1.id";
            $from=array();
            $from[]="`data_user` AS t1";
            $where=array();
            $where[]="t1.tel>0";
			$where[]="t1.sms='1'";

            $fields=array();
            $fields[]="`date`='".date("Y-m-d H:i:s")."'";
            if($sms['group']!=0){
                $from[]="`entity` AS t2";
                $select[]="t2.name AS groupname";
                $where[]="t1.sfera=".$sms['group'];
                $where[]="t2.id=t1.sfera";
            }
            if($sms['city']!=0){
                $from[]="`entity` AS t3";
                $select[]="t3.name AS cityname";
                $where[]="t1.town=".$sms['city'];
                $where[]="t3.id=t1.town";
            }

            $array=mysql::getArray("SELECT ".implode(", ",$select)." FROM ".implode(", ",$from)." WHERE ".implode(" AND ",$where));
            if($array==false){
                $error.='<p>По заданным вами параметрам не найдены получатели рассылки!</p>';
            }

            if($error!=''){
                ajax::sound("alert");
                return array('errorMessage'=>'<div class="error">'.$error.'</div>');
            }
            else {
                $tellist=array();
                foreach($array AS $val){
                    $tellist[]=onlydigit(trim($val['tel']));
                }
                sms::send($tellist,$sms['text']);
                if(isset($array[0]['groupname'])) $fields[]="`group`='".$array[0]['groupname']."'";
                else $fields[]="`group`='-'";
                if(isset($array[0]['cityname'])) $fields[]="`city`='".$array[0]['cityname']."'";
                else $fields[]="`city`='-'";
                $fields[]="numbers=".count($array);
                $fields[]="cost=".(count($array)*$smsCost);
                mysql::query("INSERT INTO `sms` SET name='".escape(trim($sms['name']))."', `text`='".escape(trim($sms['text']))."', ".implode(", ",$fields));

                return array('cblock'=>'<div class="row">
		<ul class="breadCrumbs"><li onClick="ajaxGet(\'rocketsms::init\')"><span>СМС рассылки</span></li><li><span>Новая СМС рассылка</span></li></ul>
		</div>
		<div class="row">
		    <h3>Новая СМС рассылка</h3>
		        <div class="btn" onClick="ajaxGet(\'rocketsms::init\')"><i class="ic-return"></i>Назад</div>
		</div>
		<div class="row">
		    <div class="info"><p>Рассылка успешно отправлена '.count($array).' абонентам. Ориентировочная стоимость рассылки - '.round((count($array)*$smsCost),02).' руб.</p></div>
		</div>');
            }
        }
        return false;
    }


    static function create(){

        $array=mysql::getArray("SELECT id,name FROM `entity` WHERE entity_type=27 ORDER BY `name` ASC");
        $cityOptions='';
        if($array!=false){
            foreach($array AS $val){
                $cityOptions.='<option value="'.$val['id'].'">'.$val['name'].'</option>';
            }
        }

        $array=mysql::getArray("SELECT id,name FROM `entity` WHERE entity_type=29 ORDER BY `name` ASC");
        $groupOptions='';
        if($array!=false){
            foreach($array AS $val){
                $groupOptions.='<option value="'.$val['id'].'">'.$val['name'].'</option>';
            }
        }

        $out='<div class="row">
		<ul class="breadCrumbs"><li onClick="ajaxGet(\'rocketsms::init\')"><span>СМС рассылки</span></li><li><span>Новая СМС рассылка</span></li></ul>
		</div>
		<div class="row">
		    <h3>Новая СМС рассылка</h3>
		        <div class="btn" onClick="ajaxGet(\'rocketsms::init\')"><i class="ic-return"></i>Назад</div>
		</div>
		<div class="row" id="errorMessage">
		</div>
		<div class="row">
		    <form id="sms" method="POST" style="width:100%;">
		        <table class="formtable">
		            <tr><td><label>Расылка: </label></td><td><input type="text" name="sms[name]" value="Рассылка от '.date("d.m.Y H:i").'"></td></tr>
		            <tr><td><label>Текст:</label></td><td><input id="smstext" type="text" style="width:660px;" maxlength="64" name="sms[text]"></td></tr>
		            <tr><td><label>Группа</label></td><td><select name="sms[group]"><option value="0">-ВСЕ ГРУППЫ-</option>'.$groupOptions.'</select></td></tr>
		            <tr><td><label>Город</label></td><td><select name="sms[city]"><option value="0">-ВСЕ ГОРОДА-</option>'.$cityOptions.'</select></td></tr>
		            <tr><td></td>
		            <td>
		                <div class="btn" onClick="ajaxPost(\'sms\',\'rocketsms::send\')"><i class="ic-alarm-add"></i>Отправить</div>
		                <div class="btn" onClick="ajaxGet(\'rocketsms::init\')"><i class="ic-return"></i>Отмена</div>
		            </td></tr>
		        </table>
		    </form>
		</div>';



        return array(
            'cblock'=>$out
        );
    }

	static function init(){
        $smsCost=0.0155;
		global $admin;

		if(isset($admin)){
		    $admin->addHeadScript('
function smsDel(id){
	slowlyDel("r"+id);
	ajaxGet("rocketsms::delete?="+id);
}
            ');
		}

		global $settings;
		$out='';
		$out='<div class="row">
		<ul class="breadCrumbs"><li><span>СМС рассылки</span></li></ul>
		</div>
		<div class="row">
		    <h3>СМС рассылки</h3>
		    <div id="mError"></div>
		    <form id="qsms" method="POST" style="width:100%;">
		        <div class="btn" onClick="ajaxGet(\'rocketsms::create\')"><i class="ic-bubbles4"></i>Новая рассылка</div>
                <div id="qsdata" class="btn-group" style="float:right; margin-right:0;">
                    <div class="label">СМС:</div>
                    <div class="label"><i class="ic-mobile" style="float:left; margin:6px 0 0 0;"></i></div>
                    <input type="text" name="telnum" class="size-m" value="+375 ">
                    <div class="label">Текст:</div>
                    <input type="text" name="smstext" style="width:600px" maxlength="64" value="">
                    <div class="btn" onClick="ajaxPost(\'qsms\',\'rocketsms::quickSend\')"><i class="ic-check3"></i></div>
                </div>
		    </form>
		</div>';



		$array=mysql::getArray("SELECT 
		t1.*,
		DATE_FORMAT('d.M.Y H:i', t1.date) AS date  
		FROM `sms` AS t1 
		ORDER BY id DESC");

		if($array!=false){
		    $out.='<div class="row">
		    <table class="cmstable3">
		        <tr>
		            <th style="width:24px;"></th>
		            <th>Рассылка</th>
		            <th>Текст</th>
		            <th style="width:80px;">Абонанты</th>
		            <th style="width:100px;">Стоимость</th>
		            <th style="width:120px;">Город</th>
		            <th style="width:120px;">Группа</th>
		            <th style="width:60px;"></th>
		        </tr>';
            foreach($array AS $val){
                $out.='<tr id="r'.$val['id'].'">
                <td><i class="ic-bubbles4"></td>
                <td><b class="hand">'.$val['name'].'</b></td>
                <td class="smallgrey">'.$val['text'].'</td>
                <td>'.$val['numbers'].'</td>
                <td>'.round($val['numbers']*$smsCost,01).'</td>
                <td>'.$val['city'].'</td>
                <td>'.$val['group'].'</td>
                <td><i class="ic-delete color-red" style="float:right;" title="Удалить" onClick="smsDel('.$val['id'].')"></i></td>
                </tr>';
            }
            $out.='</table>';
        }

		if(isset($admin)) return $out;
		else {
		    return array('cblock'=>$out);
        }
	}

}