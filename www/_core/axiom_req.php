<?php
if(!session_id()){
    $sessLife=time()+(8*60*60);
    session_set_cookie_params($sessLife);
    session_save_path($_SERVER['DOCUMENT_ROOT'].'/_session');
    session_start();
}
if(!defined("SECURITY")) define("SECURITY",true);
if(!function_exists("CLASS_autoloader")){
    function CLASS_autoloader($class) {
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php')) include $_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php';
    }
    spl_autoload_register('CLASS_autoloader');
}

// --------КОСТЫЛЬ ДЛЯ ГОВНОХОСТИНГОВ, КОТОРЫЕ НЕ ПЕРЕНАПРАВЛЯЮТ ЗАПРОСЫ НА index.php ---------
// --------------------------------------------------------------------------------------------
//if(!isset($_REQUEST['q']) && !isset($_REQUEST['action']) && !isset($OOOIOOO)){
//    $fuckedHosting=true;
//    if(!isset($_REQUEST['q'])) require_once $_SERVER['DOCUMENT_ROOT']."/admin/index.php";
//}
// ---------------------------------------------------------------------------------------------


$uiLang='ru';// Язык пользовательского интерфейса, для загрузки настроек
if(isset($_SESSION['uiLang'])) $uiLang=$_SESSION['uiLang'];
if (!$action && !$site) {
    $GLOBALS['_RESULT'] = @$_REQUEST;
    $maintext=false;
    if(isset($_RESULT)) $maintext=$_RESULT;
    if(!session_id()) session_start();
    require_once($_SERVER['DOCUMENT_ROOT'].'/_core/mainFunctions.php');
    $settings=parse_ini_file($_SERVER['DOCUMENT_ROOT']."/_core/settings.ini");
    //$messages=parse_ini_file($_SERVER['DOCUMENT_ROOT']."/admin/ru.ini");
    $settings['enableCaching']=0;
    if(!defined("SECURITY")) {
        //header('location:'.$settings['protocol'].$settings['siteUrl'].'/admin/auth/');
        exit("SESSION TIMEOUT: axiom_req.php");
    }
    mysql::connect();
    $error='';
    if(isset($_REQUEST['action'])){
        $AXrqACT=$_REQUEST['action'];
        if (strpos($AXrqACT, "?=") === false) $AXrqACT=str_replace("?","?=&",$AXrqACT);
        $act=explode("&",$AXrqACT);
        list($_GET['action'],$_GET['item'])=explode("?=",$act[0]);
        if(isset($act[1])){
            foreach($act as $kkey=>$ii){
                if($kkey!=0){
                    @list($variable,$value)=explode("=",$ii);
                    if(isset($value)) $variables[$variable]=$value;
                }
            }
            if(isset($variables)) extract($variables);
        }
        extract($_GET);
    }
    else {
        $_POST['action']=str_replace("?=","",$_POST['action']);
        $_POST['action']=str_replace("&","",$_POST['action']);
        $action=$_POST['action'];
    }
    list($CLASS,$action)=explode("::",$action);
}
if ($action) {
    //echo 'action='.$CLASS.'::'.$action;
    if (!isset($site)) {
        $out=array();
        if(method_exists($CLASS, $action)){
            $o=call_user_func($CLASS.'::'.$action);
            if(!is_array($o)){
                $out['AXIOM_string']=$o;
            }
            else $out=$o;
            $error=error();
            if($error!='') ajax::consoleError(strip_tags($error));

            // Дополнительная обработка потока вывода
            $axiom_acts=['message','consoleLog','consoleError','javascript','includeScript','domRemove','dialogConfirm','dialogAlert','styleSet','sound','classAdd','classRemove','selectSetValue','window'];
            foreach($axiom_acts AS $actval){
                $m=call_user_func('ajax::'.$actval,false);
                if(is_array($m) && !empty($m)) $out['ax_'.$actval]=$m;
            }
            // Редирект
            $m=ajax::redirect();
            if($m!='') $out['ax_redirect']=$m;
        }
        else {
            ajax::consoleError('Не найден метод '.$CLASS.'::'.$action);
            $out['ax_consoleError']=ajax::consoleError();
        }
        session_write_close();// Принудительно запрещаем запись в сессию
        echo '|a|:|a|'.json_encode($out).'|a|:|a|';
    }
    else {
        if (!method_exists($CLASS, $action)) {
            ajax::dialogAlert('Не удается найти метод '.$action.' объекта '.$CLASS.'.</div>');
            //$action="init";
        }
        // Иначе, в переменную $admin->content шаблона
        // Если в URL['pages'][1] приходит что-то, то сначала инициализируем
        // все функцией ::init(true), а затем запускаем метод с названием ::$admin->url['pages'][1]
//        if(isset($admin->url['pages']) && isset($admin->url['vars']) && isset($admin->url['pages'][1]) && method_exists($CLASS, $admin->url['pages'][1])){
//            $action=trim($admin->url['pages'][1]);
//            call_user_func($CLASS.'::init');
//        }
//        $admin->setContent(call_user_func($CLASS.'::'.$action));
//        if(isset($fuckedHosting)) echo $admin->getTemplate($content, true);
    }
}