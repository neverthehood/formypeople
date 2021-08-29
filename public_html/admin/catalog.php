<?php
$_SESSION['time']=time();
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once $_SERVER['DOCUMENT_ROOT']."/admin/axiom_req.php";

// Каталог от 12.04.2019
class catalog {
    static $moduleAlias             = 'catalog';                // ALIAS модуля
    static $moduleName              = 'Каталог';                // Название модуля
    static $contentDiv              = 'right';                  // ID блока основного контента (cblock/right)
    static $leftPanelWidth          = 250;                      // Ширина левой панели (пикс)
    static $systemEntityTypeId      = 0;                        // ID типа инфоблока - "Все элементы"  (общие параметры для всех элементов)
    static $categoryEntityTypeId    = 7;                        // ID типа инфоблока - "категория" (папка)
    static $itemEntityTypeId        = 10;                       // ID типа инфоблока - "товар"
    static $categoryParentId        = 4;                        // ID главного потомка категории (ПАПКА-РОДИТЕЛЬ открытая по-умолчанию)
    static $itemsPerPage            = 30;                       // Количество элементов на странице
    static $listTextLength          = 128;                      // Максимальная длина строки, получаемая из ячейки типа TEXT при отображении списка
    static $itemsOrder              = '0-a';                    // Сортировка для товаров по-умолчанию

    /*  ---------------------  ОБЩИЕ ФУНКЦИИ  --------------------------  */
    // Вставка товаров из буфера обмена
    static function pasteFromBuffer(){
        global $item;
        data::pasteFromBuffer($item);
        return self::items($item);
    }

    // Сохранение инфоблока
    static function save(){
        global $p, $cat, $error;
        $error=false;
        $array=array();
        $out='';
        ob_start();
        if(isset($_REQUEST['array'])){
            $array=$_REQUEST['array'];
            data::entitySave($array);
        }
        $buff=ob_get_contents();
        if($error===false){
            cacheClear();
            // Сохраняем дочерний элемент
            if(isset($array['asChild'])) {
                $out.=data::showChildList($array['parent_id'], $array['entity_type'], $array['attrId']);
            }
            else {
                // Сохраняем папку
                if(isset($array['entity_type']) && $array['entity_type']==7){
                    ajax::javascript('catOpen('.$_REQUEST['array']['id'].')');
                    return array('side'=>$buff.data::getTree(self::$categoryEntityTypeId, $_REQUEST['array']['id']));
                }
                // Сохраняем сущность
                else {
                    if(isset($_REQUEST['cat'])) $cat=$_REQUEST['cat'];
                    if(isset($_REQUEST['p'])) $p=$_REQUEST['p'];
                    return self::items();
                }
            }
        }
        ajax::dialogAlert($buff.$error);
        return false;
    }

    static function items($category=false){
        $out='';
        global $catList, $perPage, $catButtons, $folderSettingsButton, $admin, $cat, $p, $ppSelector, $paginator, $itemCounter;
        $catList        =   '';
        $buttons        =   '';
        $catButtons     =   '';
        $ppSelector     =   '';
        $paginator      =   '';
        $itemButtons    =   '';
        if(!isset($cat)) $cat=self::$categoryParentId;// Категория по-умолчанию
        if($category!=false) $cat=$category;
        if(!isset($p)) $p=0;
        $GLOBALS['settings']['queryLog']=true;

        $folder=data::getFolderData($cat);// Получим все данные текущей папки
        // Back button
        if($folder['id']!=self::$categoryParentId) $catButtons.='<div class="btn" onclick="catOpen('.$folder['parent_id'].')"><i class="ic-return"></i></div>';
        // Эта кнопка не должна компилироваться
        //if($_SESSION['user']['group']<=1) $catButtons.='<div class="btn" title="Компилировать" onClick="ajaxGet(\'data::startCompiler?='.$folder['id'].'\')"><i class="ic-hacker" style="margin-right:0;"></i></div>';

        $catButtons.='<div class="btn" onclick="ajaxGet(\'data::edit?=0&mn='.self::$moduleAlias.'&contentDiv=right&entity='.$folder['entity_type'].'&cat='.$cat.'\')"><i class="ic-folder-plus"></i>Новая папка</div>';
        $folderSettingsButton='<div class="btn" onclick="ajaxGet(\'data::edit?='.$cat.'&mn='.self::$moduleAlias.'&contentDiv=right&entity=7&cat='.$cat.'\')" title="Свойства"><i class="ic-fldset"></i></div>';
        // Кнопку "Настройки" показываем только в корневой директории
        if($cat==self::$categoryParentId && $_SESSION['user']['group']==0) {
            $catButtons.='<div class="btn" onclick="ajaxGet(\'data::settings?=&moduleAlias='.self::$moduleAlias.'&moduleName='.self::$moduleName.'\')" title="Свойства инфоблоков"><i class="ic-tools"></i></div>';
            $catButtons.='<div class="btn" onClick="ajaxGet(\'import::export?=\')" title="Экспорт БД в форматах CSV, XML, YML"><i class="ic-table2"></i>Экспорт в CSV</div>';
            $catButtons.='<div class="btn" onClick="ajaxGet(\'import::importStart?=\')" title="Импорт из CSV"><i class="ic-file-excel"></i><i class="ic-moveright"></i><i class="ic-db"></i></div>';
            $catButtons.='<div class="btn" onClick="ajaxGet(\'data::createCache?=&moduleAlias='.self::$moduleAlias.'&moduleName='.self::$moduleName.'\')" title="Обновить кэш товаров"><i class="ic-drawer"></i>Обновить кэш</div>';
        }
        $catList=data::catList($folder, $cat, self::$moduleAlias, self::$moduleName, self::$categoryParentId);
        $out.=$catList;
        // Отображаем список товаров
        if($catList==''){
            $itemButtons.='<div class="btn" onClick="ajaxGet(\'data::edit?=0&mn='.self::$moduleAlias.'&contentDiv=right&entity='.$folder['child_entity_type'].'&cat='.$folder['id'].'&p='.$p.'\')"><i class="ic-plus"></i>Добавить</div>';
            $out.=data::itemList($cat, $p, $folder, self::$moduleAlias, self::$itemsOrder, self::$contentDiv );
        }
        $filterBtns='';
        if($folder['child_entity_type']!=self::$categoryEntityTypeId) {
            $buttons.=$itemButtons;
            $filterBtns='<div class="btn" title="Фильтр" onClick="ajaxGet(\'data::showFilter?='.$folder['child_entity_type'].'&category='.$cat.'\')"><i class="ic-filter" style="margin:0"></i></div>';
        }
        else $buttons.=$catButtons;
        $filter='<div class="row" id="searchFilter">';
        if(isset($_SESSION['filter'][$folder['child_entity_type']])) $filter.=data::showFilter($folder['child_entity_type'],false,$cat);
        $filter.='</div>';
        // Если определена глобальная переменная itemCounter, значит поиск вывел результаты
        $findedCounter='';
        if(isset($itemCounter)){
            $showedItems=(($p*$perPage)+$perPage);
            if($showedItems>$itemCounter) $showedItems=$itemCounter;
            $findedCounter=' <span>('.(($p*$perPage)+1).' - '.$showedItems.' из '.$itemCounter.')</span>';
        }
        $out='<div class="row"><div class="btn-group" style="float:left;">'.$filterBtns.$buttons.$folderSettingsButton.'<input type="text" id="searchByName" class="size-m" value="" onKeyUp=\'fieldAutosave(this.id,"catalogQuickSearch('.$cat.')",0.8)\' placeholder="Найти"></div><div style="float:right;">'.$ppSelector.$paginator.'</div></div>
        <div class="row" id="searchblock"></div>'.$out;
        if($folder['name']!='') $out='<div class="row"><h3>'.$folder['name'].$findedCounter.'</h3><hr></div>'.$filter.$out;
        if(isset($admin)) return $out;
        else return array('right'=>$out);
    }

    // Инициализация компонента
    static function init(){
        global $admin, $settings, $cat;
        $cat = !isset($item) ? self::$categoryParentId : $item;
        $out="";
        if(isset($admin)){
            $admin->addJs($settings['protocol'].$settings['siteUrl'].'/_jscript/iload/iLoad.js');
            $HScriptCode=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/js/data.js');
            $HScriptCode=str_replace("[[categoryParentId]]",self::$categoryParentId,$HScriptCode);
            $HScriptCode=str_replace("[[moduleAlias]]",self::$moduleAlias,$HScriptCode);
            $HScriptCode=str_replace("[[moduleName]]",self::$moduleName,$HScriptCode);
            $admin->addBodyScript($HScriptCode);
        }
        $bufferCount=0;
        if(isset($_SESSION['buffer'])) $bufferCount=count($_SESSION['buffer']);
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
}