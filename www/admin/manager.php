<?php
$_SESSION['time']=time();
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once $_SERVER['DOCUMENT_ROOT']."/admin/axiom_req.php";

// Каталог от 29.12.2017
// Редактирование фото + просмотр миниатюры через iLoad

class manager {

    static $moduleAlias='manager';
    static $moduleName='Администратор';
    static $leftPanelWidth=300;
    static $categoryEntityTypeId=0;
    static $categoryParentId=4;

    static function init(){
        global $admin, $settings, $cat;
        $bufferCount=0;
        if(!isset($cat)){
            $cat=self::$categoryParentId;
        }

        $out="";
        if(isset($admin)) {
            $admin->addJs($settings['protocol'] . $settings['siteUrl'] . '/_jscript/iload/iLoad.js');
        }

        $out.='<div class="row">
         <div id="breadCrumbs" style="width:940px; float:left;">
            <ul class="breadCrumbs"><li onClick="ajaxGet(\'init?=\',\'cblock\',\''.self::$moduleAlias.'\')"><span>'.self::$moduleName.'</span></li></ul>
         </div>
         <div class="btn-group" style="display:block; height:30px; float:right;margin:-4px 0 0 0; ">
            <div class="label" style="position:relative; font:bold 12px sans-serif; line-height:28px; width:180px; background:#ddddde;border:1px solid #dddddd; color:#666666">Буфер обмена <span id="fufCount">'.$bufferCount.'</span>
                <div id="bufferBlock"></div>
            </div>
            <div class="btn" onClick="bufferSwitch();"><i class="ic-clipboard" style="margin-right:0;"></i></div>
         </div>
        </div>
        <div id="side" style="width:'.self::$leftPanelWidth.'px; border:1px solid #ffffff; overflow:auto; float:left; display:block;">'.data::getTree(self::$categoryEntityTypeId, $cat).'</div>
        <div id="right" style="width:'.(1180-self::$leftPanelWidth).'px; float:right;">'.self::items($cat).'</div>';
        if(isset($admin)) return $out;
        else return array('cblock'=>$out);
    }




    static function items($cat){
        return '';
    }

}