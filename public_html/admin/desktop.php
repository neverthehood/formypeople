<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";

class desktop
{
    // Инициализация: запускается при вызове страницы без AJAX (по умолчанию)
    // В этой функции необходимо подключать скрипты и задавать параметры страницы
    // --------------------------------------------------------------------------
    static function init()
    {
        global $admin,$settings;
        $out='';
        $array=mysql::getArray("SELECT * FROM `modules` WHERE alias!='desktop' ORDER BY `id` ASC");
        foreach($array AS $val){
            if($_SESSION['user']['perms']=='all' || $_SESSION['user']['perms'][$val['alias']]>=1 || $val['alias']!='desktop') {
                if($_SESSION['user']['group']==10){
                    if($val['alias']!='users' && $val['alias']!='tools' && $val['alias']!='filemanager'){
                        $out.=ui::widget($settings['protocol'].$settings['siteUrl']."/admin/".$val['alias'].'/', false, $val['icon'], $val['name'], $val['description']);
                    }
                }
                else {
                    $out.=ui::widget($settings['protocol'].$settings['siteUrl']."/admin/".$val['alias'].'/', false, $val['icon'], $val['name'], $val['description']);
                }
            }
        }
        if(isset($admin)) {
            return '<div class="field"><ul class="breadCrumbs"><li><span>'.MESSAGE('desktop').'</span></li></ul></div>
			<div id="cblock" style="display:block; float:none; clear:both;">'.$out.'</div>';
        }
        else return $out;
    }



}
