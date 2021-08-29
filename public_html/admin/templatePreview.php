<?php
/////////////////////////////////////////////////////////////////////////
// Отображение внешнего вида шаблона страницы
$template=false;
require_once($_SERVER['DOCUMENT_ROOT'].'/_core/mainFunctions.php');
$settings=parse_ini_file($_SERVER['DOCUMENT_ROOT']."/_core/settings.ini");
$messages=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/admin/ru.ini',true);
mysql::connect();
if(isset($_GET['template'])) $template=$_GET['template'];
else header('location:'.$settings['protocol'].$settings['siteUrl']);

//Автозагрузка классов
function __autoload($_classname) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/_classes/'.$_classname.'.php');
}

$value=mysql::getValue("SELECT content FROM `templates` WHERE id='".escape($template)."' LIMIT 1");
$value=str_replace("src=\"./","src=\"http://".$settings['siteUrl'],$value);
$value=str_replace("href=\"./","href=\"http://".$settings['siteUrl'],$value);
echo $value;