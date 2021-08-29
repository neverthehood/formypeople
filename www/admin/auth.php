<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";
class auth
{
    // Инициализация: запускается при вызове страницы без AJAX (по умолчанию)
    // В этой функции необходимо подключать скрипты и задавать параметры страницы
    // --------------------------------------------------------------------------
    static function init()
    {
        global $admin;
        if(isset($admin)) {
            $admin->setTemplate("login");
            return '<div class="AXloginBlock" id="AXloginBlock">'.auth::showLoginForm().'</div>';
        }
        else return auth::showLoginForm();
    }

    // Авторизация на сайте
    static function authorize(){
        global $settings;
        $error='';
        //return auth::showLoginForm();

        if (isset($_POST['aut']['name']) && isset($_POST['aut']['pass'])) {
            if (!getenv("COMSPEC")) sleep(2); // Пауза в 2 секунды для LINUX
            $aut = $_POST['aut'];
            $name= $_POST['aut']['name'];
            $aut['name'] = $_POST['aut']['name'];
            $pass = $_POST['aut']['pass'];
            $aut['pass'] = $_POST['aut']['pass'];


            if (strlen($name) < 4 || strlen($name) > 129) $error .= MESSAGE('auth','ERROR_loginError') . '<br>';
            if (strlen($pass) < 4 || strlen($pass) > 64) $error .= MESSAGE('auth','ERROR_passError') . '<br>';
            // пробуем найти юзера в базе
            if ($error == '') {
                $array = mysql::getArray("SELECT t1.id, t1.locked, t1.active, t1.name, t1.mail, t1.group, t2.groupname
                FROM `users` AS t1
                    JOIN `user_group` AS t2 ON t2.group=t1.group
                WHERE
                    t1.mail='".escape($name)."'
                    AND t1.pass='".escape(md5($pass))."'
                LIMIT 1", true);

                // Если пользователь не найден, то проверим, не включен ли
                // у пользователя режим восстановления забытого пароля.
                // И если он включен, то проверим, соответствует ли новый
                // пароль введенному
                if ($array == "false") {
                    $array = mysql::getArray("SELECT t1.id, t1.locked, t1.active, t1.name, t1.mail, t1.group, t2.groupname, t1.newpassword AS pass, t1.remakestatus
                    FROM `users` AS t1
                        JOIN `user_group` AS t2 ON t2.group=t1.group
                    WHERE
                        t1.mail='" . escape($name) . "'
                        AND t1.newpassword='" . escape(md5($pass)) . "'
                        AND t1.remakestatus='1'
                    LIMIT 1", true);
                    if ($array != false) {
                        // Если новый пароль соответствует, то очистим статус и перепишем основной пароль
                        mysql::query("UPDATE `users` SET pass='" . escape($array['pass']) . "', newpassword='---', remakestatus='0' WHERE id='" . escape($array['id']) . "' LIMIT 1");
                    }
                }
                
                

                // ВАЖНО - ОСТАВИТЬ ВТОРОЙ ПОВТОР УСЛОВИЯ!!!
                if ($array != false) {
	                if(!$authCounter){
		                $authCounter=0;
	                }
                    if ($array['locked'] == 1) {
                        $error .= sprintf(MESSAGE('auth','ERROR_userIsLocked'), $array[0]['name']) . '<br>';
                        $_SESSION['closed'] = 1;
                        $_SESSION['authCounter'] = 5;
                        $authCounter += 3;
                    } else {
                        $redirectUrl='desktop';
                        if(isset($_POST['redirectUrl'])) $redirectUrl=$_POST['redirectUrl'];
                        if($redirectUrl=='auth') $redirectUrl='desktop';
                        // Получаем список разрешений для пользователя
                        if($array['group']==0) $array['perms']="all";// Для суперадмина - все разрешено
                        else $array['perms']=user::permission($array['id']);
                        $array['permTime']=time();
                        $_SESSION['user'] = $array;
                        $_SESSION['authCounter'] = 0;
                        if ($_SESSION['authCounter'] >= 4) sleep(5);
                        mysql::query("UPDATE `users` SET lastvizit=NOW() WHERE id='" . escape($array['id']) . "' LIMIT 1");

                        //ajax::redirect('https://ya.ru/');
                        ajax::redirect($settings['protocol'].$settings['siteUrl'].'/admin/'.$redirectUrl.'/');
                        //return false;
                    }
                } else {
                    $_SESSION['authCounter']++;
                    if ($_SESSION['authCounter'] >= 4) sleep(5);
                    $error.= MESSAGE('auth','ERROR_userNotFnd') . "<br>";
                }
            }
        }
        if ($error != '') {
            ajax::sound('alert');// Команда на воспроизведение звука
            ajax::styleSet("primLoginBTN","display:block");
            ajax::styleSet("loginForm","opacity:1");
            return array('loginError'=>'<div class="error">' . $error . '</div>');
        }
    }

    static function remakePass()
    {
        global $settings;
        $error = array();
        sleep(2); // Пауза в 2 секунды

        if (isset($_POST['userMail']) && checkMail($_POST['userMail'])) {
            $user = mysql::getArray("SELECT id,name,locked FROM `users` WHERE mail='" . escape($_POST['userMail']) . "' LIMIT 1", true);
            // Если емейл не найден, то отправим сообщение
            if ($user === false) $error[] = MESSAGE('auth','ERROR_emailNotFound');
            else {
                // Если юзер заблокирован, то запретим смену пароля
                if ($user['locked'] != 0) $error[] = MESSAGE('auth','ERROR_userIsLocked');
                else {
                    $pass = user::generatePassword(12);
                    $message = sprintf(MESSAGE('auth','MESSAGE_createPass'), $user['name'], $user['name'], $pass,
                    $settings['siteUrl']);
                    if (mail::mailSender($_POST['userMail'], $user['name'], MESSAGE('auth','passwordRemake'), $message)) {
                        mysql::query("UPDATE `users` SET `newpassword`='" . escape(md5($pass)) . "',remakestatus='1' WHERE id='" . escape($user['id']) . "' LIMIT 1");
                        return '<div class="okmessage">' . MESSAGE('auth','passwIsSendedOnMail') . '</div><div class="row"> <div class="btn btn-large" onClick="ajaxGet(\'showLoginForm\',\'AXloginBlock\')"><i class="ic-refresh"></i>' . MESSAGE('auth','LINK_backToAuth') . '</div></div>';
                    }
                    else $error[] = MESSAGE('auth','ERROR_mailIsNotSended');
                }
            }
        }
        else $error[] = MESSAGE('auth','ERROR_mailError');

        if (!empty($error)) return array('loginError'=>'<div class="error">' . implode("<br>", $error) . '</div>');
        else return array('AXloginBlock'=>self::remakePass());
    }

    // Форма восстановления пароля
    static function remakeForm()
    {
        $userMail = '';
        $error = '';
        if (isset($_POST['userMail'])) $userMail = $_POST['userMail'];
        if ($error != '') $error = '<div class="error">' . $error . '</div>';
        return array(
            'AXloginBlock'=>'
<div class="row">
    <img src="http://axiom/admin/img/silverlogo.png" alt="AxiomCMS">
</div>
<div class="row">
    <i class="ic-aid"></i><span>'.MESSAGE('auth','passwordRemake').'</span>
</div>
<form class="loginForm" id="pwrForm" method="POST" enctype="multipart/form-data">
<div class="row">
    <p>'.MESSAGE('auth','inputYourMail').'</p>
</div>
<div class="row" style="position:relative;">
    <input type="text" id="rsmail" onKeyPress="admPRReset()" tabindex="1" name="userMail" value="" placeholder="'.MESSAGE('auth','email').'" style="font-size:15px; padding:6px;">
    <div id="primLoginBTN" style="display:block" class="button button-primary" tabindex="2" onclick="adminPassRestore();"><i class="ic-check ic-white" style="margin-right:0;"></i></div>
</div>
<div class="row" id="loginError"></div>
<div class="row">
    <span class="link" onclick="ajaxGet(\'auth::showLoginForm\',\'AXloginBlock\')">'.MESSAGE('auth','LINK_backToAuth').'</span>
</div>
</form>');
    }

    // Отображение формы входа на сайт
    static function showLoginForm(){
        global $settings;
        global $redirectUrl;
        if(isset($_POST['redirectUrl'])) $redirectUrl=$_POST['redirectUrl'];
        if(!isset($redirectUrl)){
            $redirectUrl='desktop';
            if(isset($_SERVER['REQUEST_URI'])){
                $uriParts=explode('/',$_SERVER['REQUEST_URI']);
                $redirectUrl=trim($uriParts[(count($uriParts)-2)]);
            }
        }
        $aut = array('name' => '', 'pass' => '');
        $error = '';
        $authCounter = 0;

        if (isset($_POST['aut']['name']) && isset($_POST['aut']['pass'])) {
            if (!getenv("COMSPEC")) sleep(2); // Пауза в 2 секунды для LINUX
            $aut = $_POST['aut'];
            $name= $_POST['aut']['name'];
            $aut['name'] = $_POST['aut']['name'];
            $pass = $_POST['aut']['pass'];
            $aut['pass'] = $_POST['aut']['pass'];


            if (strlen($name) < 4 || strlen($name) > 129) $error .= MESSAGE('auth','ERROR_loginError') . '<br>';
            if (strlen($pass) < 4 || strlen($pass) > 64) $error .= MESSAGE('auth','ERROR_passError') . '<br>';
            // пробуем найти юзера в базе
            if ($error == '') {
                $array = mysql::getArray("SELECT t1.id, t1.locked, t1.active, t1.name, t1.mail, t1.group, t2.groupname
                FROM `users` AS t1
                    JOIN `user_group` AS t2 ON t2.group=t1.group
                WHERE t1.mail='".escape($name)."'
                    AND t1.pass='".escape(md5($pass))."'
                LIMIT 1", true);

                // Если пользователь не найден, то проверим, не включен ли
                // у пользователя режим восстановления забытого пароля.
                // И если он включен, то проверим, соответствует ли новый
                // пароль введенному
                if ($array == "false") {
                    $array = mysql::getArray("SELECT users.id, users.locked, users.active, users.name, users.mail, users.group, user_group.groupname, users.newpassword AS pass, users.remakestatus FROM `users`,`user_group` WHERE users.mail='" . escape($name) . "' AND users.newpassword='" . escape(md5($pass)) . "' AND users.remakestatus='1' AND user_group.group=users.group LIMIT 1", true);
                    if ($array != false) {
                        // Если новый пароль соответствует, то очистим статус и перепишем основной пароль
                        mysql::query("UPDATE `users` SET pass='" . escape($array['pass']) . "', newpassword='---', remakestatus='0' WHERE id='" . escape($array['id']) . "' LIMIT 1");
                    }
                }

                // ВАЖНО - ОСТАВИТЬ ВТОРОЙ ПОВТОР УСЛОВИЯ!!!
                if ($array != false) {
                    if ($array['locked'] == 1) {
                        $error .= sprintf(MESSAGE('auth','ERROR_userIsLocked'), $array[0]['name']) . '<br>';
                        $_SESSION['closed'] = 1;
                        $_SESSION['authCounter'] = 5;
                        $authCounter += 3;
                    } else {
                        $redirectUrl='desktop';
                        if(isset($_POST['redirectUrl'])) $redirectUrl=$_POST['redirectUrl'];
                        if($redirectUrl=='auth') $redirectUrl='desktop';
                        // Получаем список разрешений для пользователя
                        if($array['group']==0) $array['perms']="all";// Для суперадмина - все разрешено
                        else $array['perms']=user::permission($array['id']);
                        $array['permTime']=time();
                        $_SESSION['user'] = $array;
                        $_SESSION['authCounter'] = 0;
                        if ($_SESSION['authCounter'] >= 4) sleep(5);
                        mysql::query("UPDATE `users` SET lastvizit=NOW() WHERE id='" . escape($array['id']) . "' LIMIT 1");
                        return '<h1>'.MESSAGE('auth','welcomeMessage').'</h1><a class="button" href="http://'.$settings['siteUrl'].'/admin/desktop/">Рабочий стол</a><div id="AJAXredirect">http://'.$settings['siteUrl'].'/admin/'.$redirectUrl.'/</div>';
                    }
                } else {
                    $_SESSION['authCounter']++;
                    if ($_SESSION['authCounter'] >= 4) sleep(5);
                    $error .= MESSAGE('auth','ERROR_userNotFnd') . "<br>";
                }
            }
        }
        if ($error != '') $error = '<div class="error">' . $error . '</div>';
        return $error . '
<div class="row">
    <img src="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/silverlogo.png" alt="AxiomCMS">
</div>
<div class="row" id="lfWname">
    <i class="ic-security"></i><span>' . MESSAGE('auth','pgName') . '</span>
</div>
<form class="loginForm" id="loginForm" method="POST" enctype="multipart/form-data">
<input type="hidden" name="counter" value="'.$authCounter.'">
<input type="hidden" name="redirectUrl" value="'.$redirectUrl.'">
<div class="row">
    <input id="userMail" tabindex="1" type="text" name=aut[name] value="'.htmlspecialchars($aut['name']).'" placeholder="'.MESSAGE('auth','email').'" onKeyUp="if(key(event)==13){ajaxPost(\'loginForm\',\'AXloginBlock\');}" />
</div>
<div class="row" style="position:relative;">
    <input type="password" tabindex="2" name=aut[pass] value="'. htmlspecialchars($aut['pass']).'" placeholder="'.MESSAGE('auth','passwd').'" />
    <div id="primLoginBTN" style="display:block" class="button button-primary" tabindex="3" onClick="adminLogin();"><i class="ic-key ic-white"></i>' . MESSAGE('auth','BUTTON_login') . '</div>
</div>
<div class="row" id="loginError"></div>
<div class="row">
    <span class="link" onClick="ajaxGet(\'auth::remakeForm\',\'AXloginBlock\')">' . MESSAGE('auth','LINK_prosralPass') . '</span>
</div>
</form>';
    }
}
