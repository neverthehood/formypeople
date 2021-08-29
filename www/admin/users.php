<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";


class users {
	
	// сохранение учетной записи пользователя
	static function userSave(){
		if(isset($_POST['user']['id'])){
			$user=$_POST['user'];
			if(mb_strlen($user['name'],'utf-8')<3) error(MESSAGE('userNameError'));
			if(checkMail($user['mail'])==false) error(MESSAGE('userMailError'));
			if(isset($user['pass'])){
				$user['pass']=trim($user['pass']);
				if(mb_strlen($user['pass'],'utf-8')<6) error(MESSAGE('userPassError'));
				$user['pass']=md5(trim($user['pass']));
			}
			if(!error()){
				mysql::saveArray('users',$user);
				if(!mysql::error()) {
					if($user['id']==$_SESSION['user']['id']){
						$_SESSION['user']['name']=$user['name'];
						$_SESSION['user']['mail']=$user['mail'];
					}
					return users::init();
				}
				else return '<div class="error">'.mysql::error().'</div>'.users::editUser();
			}
			else return error().users::editUser();
		}
	}
	
	// Редактирование учетной записи пользователя
	static function editUser(){
		global $item, $settings;
		
		$user=array(
			'id'=>$item,
			'locked'=>0,
			'site_feedback'=>0,
			'shop_feedback'=>0,
			'name'=>'',
			'pass'=>'',
			'mail'=>'',
			'phone'=>'',
			'group'=>0,
			'description'=>''
		);
		
		if(!isset($_POST['user']))if($user['id']!=0) $user=mysql::getArray("SELECT * FROM `users` WHERE id='".escape($user['id'])."' LIMIT 1",true);
		else $user=$_POST['user'];
		
		$comment=false;
		if($user['id']==0){
			$pass=array('type'=>'html','value'=>'<tr><td><label>'.MESSAGE('password').'</label></td><td id="userPassword"><table><tr><td style="width:600px;"><div class="btn-group"><input id="pass" type="text" name=user[pass] style="width:206px;" onKeyDown="pChecker()" onKeyUp="pChecker()" value=""><div class="btn" onClick="setValue(\'pass\',passGenerator(10)); pChecker()"><i class="ic-dice"></i>'.MESSAGE('generatePass').'</div></td></tr>
		<tr><td id="passQuality"></td></tr></table></td></tr>');
			$lock=array('name'=>'user[locked]', 'type'=>'checkbox', 'label'=>MESSAGE('LOCK'), 'value'=>$user['locked'], 'class'=>'checkbox');
		}
		else {
		    // Если юзер редактирует свою учетную запись, или группа юзера меньше чем группа редактируемого юзера,
            // то разрешим смену пароля
			if($user['id']==$_SESSION['user']['id'] || $_SESSION['group']<$user['group']) {
				$lock=false;
				$pass=array('type'=>'html','value'=>'<tr><td><label>'.MESSAGE('password').'</label></td><td id="userPassword"><div class="btn" onClick="ajaxGet(\'users::showPasswordField?=&ret=1\')"><i class="ic-pass" style="font-size:24px;"></i>'.MESSAGE('changePass').'</div></td></tr>');
				if($_SESSION['group']<$user['group']) $lock=array('name'=>'user[locked]', 'type'=>'checkbox', 'label'=>MESSAGE('LOCK'), 'value'=>$user['locked'], 'class'=>'checkbox');
			}
			else {
				$lock=array('name'=>'user[locked]', 'type'=>'checkbox', 'label'=>MESSAGE('LOCK'), 'value'=>$user['locked'], 'class'=>'checkbox');
				$pass='-';
			}
		}
		
		// Выбрать группу можно только для другого пользователя, а не для себя
		if($user['id']!=$_SESSION['user']['id']){
			if($_SESSION['user']['group']==0) $grpArray=mysql::getSpecial("SELECT `group` AS id, groupname AS name FROM `user_group` ORDER BY `group` ASC");
			else $grpArray=mysql::getSpecial("SELECT `group` AS id, groupname AS name FROM `user_group` WHERE `group`>'".escape($_SESSION['user']['group'])."' ORDER BY `group` ASC");
			$group=array('type'=>'select', 'label'=>MESSAGE('userGroup'), 'name'=>'user[group]', 'value'=>$user['group'], 'options'=>$grpArray);
			if($_SESSION['user']['group']<=3) $comment=array('name'=>'user[description]', 'type'=>'textarea', 'label'=>MESSAGE('userComment'), 'value'=>$user['description'], 'style'=>'width:600px; height:60px;', 'maxlength'=>1000, 'counter'=>true);
			$pgName=MESSAGE('userEditor');
		}
		else $pgName=MESSAGE('personalDataEdit');
		$out='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('userManage').'</a></li>
		<li><span>'.$pgName.'</span></li></ul></div>';

		$sitesms=false;
        $shopsms=false;
        if($settings['smsUser']!='' && $settings['smsPassword']!=''){
            $sitesms=array('name'=>'user[site_sms]', 'type'=>'checkbox', 'label'=>'Отправлять SMS уведомления сайта (обратная связь, данные форм и т.д.)', 'value'=>$user['site_sms'], 'class'=>'checkbox' );
            $shopsms=array('name'=>'user[shop_sms]', 'type'=>'checkbox', 'label'=>'Отправлять SMS уведомления о заказах', 'value'=>$user['shop_sms'], 'class'=>'checkbox' );
        }

		$fields=array(
			array('name'=>'user[id]', 'type'=>'hidden', 'value'=>$user['id']),
			$lock,
			array('name'=>'user[site_feedback]', 'type'=>'checkbox', 'label'=>'Отправлять E-mail уведомления сайта (обратная связь, данные форм и т.д.)', 'value'=>$user['site_feedback'], 'class'=>'checkbox' ),
            array('name'=>'user[shop_feedback]', 'type'=>'checkbox', 'label'=>'Отправлять E-mail уведомления о заказах', 'value'=>$user['shop_feedback'], 'class'=>'checkbox' ),
            $sitesms,
            $shopsms,
			array('name'=>'user[name]', 'label'=>MESSAGE('userName'), 'type'=>'text', 'value'=>$user['name'], 'class'=>'size-l', 'maxlength'=>'127'),
			$pass,
			array('name'=>'user[mail]', 'label'=>MESSAGE('email'), 'type'=>'text', 'value'=>$user['mail'], 'class'=>'size-l', 'maxlength'=>'127'),
			array('name'=>'user[phone]', 'label'=>MESSAGE('telephone'), 'type'=>'text', 'value'=>$user['phone'], 'class'=>'size-l', 'maxlength'=>'127'),
			$group,
			$comment,
			array('type'=>'html','value'=>'<tr><td>&nbsp;</td><td>
		<div class="btn" onClick="ajaxPost(\'dseoform\',\'users::userSave\');"><i class="ic-save"></i>'.MESSAGE('BUTTON_save').'</div>
		<div class="btn" onClick="ajaxGet(\'users::init?=\');"><i class="ic-undo"></i>'.MESSAGE('BUTTON_cancel').'</div>
		</td></tr>')
		);
		$form=new form($fields);
		$form->id='dseoform';
		$form->enctype='multipart/form-data';
        $form->type="horizontal";
        $form->method='POST';
		$out.='<div class="row"><div class="form">'.$form->show().'</div></div>';
		return array('cblock'=>$out);
	}
	
	// Возврат кнопки смены пароля
	static function cancelPassField(){
		return array('userPassword'=>'<div class="btn" onClick="ajaxGet(\'users::showPasswordField?=&ret=1\')"><i class="ic-pass" style="font-size:24px;"></i>'.MESSAGE('changePass').'</div>');
	}
	
	// Отображение поля замены пароля
	static function showPasswordField($pass=''){
		global $ret;
		$df='';
		if($ret==1) $df='<div class="btn" onClick="ajaxGet(\'users::cancelPassField\');"><i class="ic-undo"></i>'.MESSAGE('BUTTON_cancel').'</div>';
		return array('userPassword'=>'<table><tr><td style="width:600px;"><div class="btn-group"><input id="pass" type="text" name=user[pass] style="width:206px;" onKeyDown="pChecker()" onKeyUp="pChecker()" value="'.htmlspecialchars($pass).'"><div class="btn" onClick="setValue(\'pass\',passGenerator(10)); pChecker()"><i class="ic-dice"></i>'.MESSAGE('generatePass').'</div>'.$df.'</td></tr>
		<tr><td id="passQuality"></td></tr></table>');
	}

	static function init(){
		global $admin;
		global $settings;
		$out='';
		$out='<div class="row">
		<ul class="breadCrumbs"><li><span>'.MESSAGE('userManage').'</span></li></ul>
		</div>';
		
		$out.='<div class="row">
		<div class="btn" onClick="ajaxGet(\'users::editUser?=0\');"><i class="ic-user-plus"></i>'.MESSAGE('userAdd').'</div>
		</div>';
		
		$array=mysql::getArray("SELECT users.*, UNIX_TIMESTAMP(users.lastvizit) AS lastvizit, UNIX_TIMESTAMP(users.register) AS register,  user_group.groupname FROM `users`,`user_group` 
		WHERE `user_group`.`group`=`users`.`group` 
		ORDER BY name ASC");
		if($array!=false){


            $dopHeaders='<th colspan="2" style="text-align:center">Уведомления</th>';
            $dopHeaders2='<th style="width:80px; text-align:center">Сайт</th><th style="width:120px; text-align:center">Магазин</th>';

			$out.='<table class="cmstable4">
			<tr>
                <th style="width:24px;" rowspan="2">&nbsp;</th>
                <th rowspan="2">'.MESSAGE('userName').'</th>
                <th colspan="2" style="text-align:center">Уведомления</th>
                <th rowspan="2" style="width:160px;">'.MESSAGE('email').' / Телефон</th>
                <th rowspan="2" style="width:120px;">'.MESSAGE('userGroup').'</th>
                <th rowspan="2" style="width:120px;">'.MESSAGE('userLastVisitTime').'</th>
                <th rowspan="2" style="width:100px;">Регистрация</th>
                <th rowspan="2" style="width:60px;">&nbsp;</th>
			</tr>
			<tr>
			    <th style="width:100px; text-align:center">Сайт</th><th style="width:120px; text-align:center">Магазин</th>
			</tr>
			';
			foreach($array AS $val){
				$actions='';
				$editLink='';
				if(($val['id']!=$_SESSION['user']['id'] && $val['group']>$_SESSION['user']['group']) || $_SESSION['user']['group']==0){
					$actions.='<span title="'.MESSAGE('LOCK').'"><i class="ic ic-lock" onClick="ajaxGet(\'users::userLock?='.$val['id'].'\')"></i></span>';
					$editLink=' onClick="ajaxGet(\'users::editUser?='.$val['id'].'\');"';
				}
				$siteFeedback='';
				$shopFeedback='';
				$regData='';
                $regData=date("d.m.Y",$val['register']);
                if($regData=='01.01.1970') $regData=' - ';
				if($val['site_feedback']==1) $siteFeedback.='<i class="ic-person-mail" style="font-size:28px;" title="E-mail"></i>';
                if($val['site_sms']==1) $siteFeedback.='<i class="ic-person-phone" style="font-size:28px;" title="СМС"></i>';
                if($val['shop_feedback']==1) $shopFeedback.='<i class="ic-person-mail" style="font-size:28px;" title="E-mail"></i>';
                if($val['shop_sms']==1) $shopFeedback.='<i class="ic-person-phone" style="font-size:28px;" title="СМС"></i>';
				$out.='<tr><td><i class="ic ic-user"></i></td><td><b'.$editLink.' class="hand">'.$val['name'].'</b></td><td>'.$siteFeedback.'</td><td>'.$shopFeedback.'</td><td>'.$val['mail'].'<b style="display:block; margin-top:4px; color:#dd0000">'.$val['phone'].'</b></td><td>'.$val['groupname'].'</td><td class="smallgrey">'.date("d.m.Y H:i",$val['lastvizit']).'</td><td class="smallgrey">'.$regData.'</td><td>'.$actions.'</td></tr>';
			}
			$out.='</table>';
		}
		
		if(!isset($admin)) return array('cblock'=>$out);
		$admin->addHeadScript('
		function pChecker(){
		    var q=parseInt(passCheck(getId("pass").value));
		    var ql=explode(",",",Очень слабый,Слабый,Нормальный,Хороший,Очень хороший");
		    var bg=explode(",",",#dc1c1c,#e66e16,#c6d02a,#a3d02a,#61d02a");
		    var cl=explode(",",",#ffffff,#ffffff,#000000,#000000,#000000");
		    getId("passQuality").innerHTML=\'<b style="border:1px solid #ffffff; display:block; float:left; width:416px; background:\'+bg[q]+\' !important; color:\'+cl[q]+\' !important; padding:6px 16px !important; border-radius:14px;">Надежность пароля: <span style="text-transform:uppercase;">\'+ql[q]+\'</span></b>\';
		}
');
		return $out;
	}

}