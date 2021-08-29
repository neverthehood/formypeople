<?php
if(session_id()==''){
    $sessLife=time()+(8*60*60);
    session_set_cookie_params($sessLife);
    session_save_path($_SERVER['DOCUMENT_ROOT'].'/_session');
    session_start();
}
$OOOIOOO=true;
if(!defined("SECURITY")) define("SECURITY",true);
error_reporting(E_ALL);
setlocale(LC_ALL, 'ru_RU.UTF8');
function CLASS_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php')) include $_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php';
}
function ADMIN_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.php')) include $_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.php';
}
spl_autoload_register('CLASS_autoloader');
spl_autoload_register('ADMIN_autoloader');

require_once $_SERVER['DOCUMENT_ROOT'] . '/_core/mainFunctions.php';
$settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/_core/settings.ini");
settingsCorrect();// Коррекция некоторых переменных $settings
$messages = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/_core/ru.ini', true);
mysql::connect();
$settings['enableCaching'] = 0;
$admin = new admin();
$page = "desktop";
$content = '';

// Если юзер не авторизован, то принудительно стартуем авторизацию
if (isset($admin->url['pages'][0])) {
    if (strlen($admin->url['pages'][0]) <= 12) $page = $admin->url['pages'][0];
}
if (!isset($_SESSION['user']) || $page == 'auth') $page = "auth";

$page = htmlspecialchars(str_replace($settings['protocol'], '', $page));
$nofinded = $settings['protocol'].$settings['siteUrl'].'/admin/auth/';
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/'.$page.'.php')) {
    $action = "init";
    require_once $_SERVER['DOCUMENT_ROOT'].'/admin/'.$page.'.php';
} else header("location:$nofinded");

if ($page == 'auth') $tb = false;
else $tb = true;

if(!isset($fuckedHosting)){
    $page = $admin->getTemplate($content, $tb);
    $page = str_replace('src="./p/', 'src="'.$settings['protocol'].$settings['siteUrl'].'/admin/p/', $page);
    $page = str_replace('src=\'./p/', 'src=\''.$settings['protocol'].$settings['siteUrl'].'/admin/p/', $page);
    $page = str_replace('href="./admin/', 'href="'.$settings['protocol'].$settings['siteUrl'].'/admin/', $page);
    $page = str_replace('href=\'./admin/', 'href=\''.$settings['protocol'].$settings['siteUrl'].'/admin/', $page);
    echo $page; // Вывод контента
}