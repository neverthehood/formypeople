<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";


class pages {

    // Добавление модуля к разделу сайта
    static function moduleAddToPage(){
        global $item, $page, $settings, $step;
        $out='';
        $ret=array();

        if(!isset($step)) $step=0;
        if(isset($item)){

            // ШАГ 0 - Прикрепляем модуль и ставим его на паузу
            if($step==0){
                $comment='';
                $moduleFile=$_SERVER['DOCUMENT_ROOT'].'/_modules/'.$item.'.php';
                $m=file($moduleFile);
                // Если первые два символа 1 строки модуля комментарий, то делаем его описанием модуля
                if($m[1]{0}=='/' && $m[1]{1}=='/') $comment=str_replace('//','',$m[1]);

                $set['id']=0;
                $set['page_id']=$page;
                $set['module']=$item;
                $set['tmplvar']='pageText';
                $set['pause']=1;
                $set['order']=mysql::getValue("SELECT MAX(`order`) FROM `pages_modules` WHERE page_id='".escape($page)."' LIMIT 1")+1;
                $set['comment']=$comment;
                $set['settings']=NULL;

                // Узнаем, был ли этот модуль подключен к какой либо странице ранее.
                // И если был, то применим к нему предыдущие настройки
                $old=mysql::getArray("SELECT * FROM `pages_modules` WHERE module='".$item."' ORDER BY id DESC LIMIT 1",true);
                if($old!=false){
                    $set['tmplvar']=$old['tmplvar'];
                    $set['settings']=$old['settings'];
                }
                mysql::saveArray('pages_modules',$set);
                $item=mysql::insertId();
                $out=tools::moduleSettings();
                $ret['modules']=self::modulesActive($page);
            }
            // Шаг 1 - отображение формы свойств модуля
            //if($step==1){
            //    $out=tools::moduleSettings();
            //}
        }
        $ret['mdlWin']=$out;
        return $ret;
    }

    // Список модулей в окне
    static function modulesShow(){
        global $page,$settings;
        $out='';
        //$array=mysql::getArray("SELECT * FROM `pages_modules` ");
        $f=file::dir($_SERVER['DOCUMENT_ROOT'].'/_modules/',true);
        if($f!=false){
            $out.='<table class="cmstable4">';
            foreach($f AS $val){
                if($val['isDir']==true){
                    
                }
                else {
                    list($name,$ext)=explode(".",$val['name']);
                    if($ext=="php"){
                        $out.='<tr><td><i class="ic-puzzle"></i></td><td><b class="hand" onClick="ajaxGet(\'pages::moduleAddToPage?='.$name.'&page='.$page.'\')">'.$name.'</b></td><td style="width:24px;"><a style="text-decoration:none;" target="_blank" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.htmlspecialchars($_SERVER['DOCUMENT_ROOT'].'/_modules/'.$val['name']).'"><i class="ic-editdoc" title="Редактировать код"></i></a></td></tr>';
                    }
                }
            }
            $out.='</table>';
        }
        echo '<pre>';
        print_r($f);
        echo '</pre>';
        ajax::window('<div id="mdlWin" style="width:800px;height:500px;"><h2>Модули</h2>'.$out.'</div>',true,'mdlWindow');
    }

    // Отображение списка модулей
    // Если $nowindow===true, то без окна
    static function modulesShowOld($nowindow=false,$opened=false){
        global $page;
        $out='<ul class="accordion">';
        //$prevFolder='';
        if(!isset($page)) $page=0;
        // Массив, в котором будет список модулей подключенных к данной странице
        $pgModules=array();
        $a=mysql::getArray("SELECT `page_id`,`module` FROM `pages_modules`");
        $incModules=array();
        foreach($a AS $val){
            if(isset($incModules[$val['module']])) $incModules[$val['module']]++;// К-во подключений
            else $incModules[$val['module']]=1;
            if(isset($page)){
                if($val['page_id']==$page) $pgModules[$val['module']]=1;
            }
        }

        ob_start();
        $z=ob_get_contents();
        $out.=$z;
        $f=file::dir($_SERVER['DOCUMENT_ROOT'].'/_modules/',true);
        $files='';
        $allowed=explode(',','php,js,css,html,htm,ini,xml,txt');// Расширения пригодные для редактирования
        foreach($f AS $key=>$val){
            // Композитные модули
            if($val['is_dir']===true){
                if($val['name']{0}!='.'){
                    $acts='<i class="ic ic-red ic-deletefolder" style="margin-left:12px;" title="'.MESSAGE('BUTTON_delete').'" onClick="dialogConfirm(\''.sprintf(MESSAGE('tools','MESS_mdlDelete'),$val['name']).'\',\''.addSlashes('ajaxGet(\'fileDelete?='.urlencode($_SERVER['document_root'].'/_modules/'.$val['name'].'/'.$val['name']).'\',\'hiddenblock\',\'tools\')').'\')"></i>';
                    $out.='<li id="acc'.$key.'">&nbsp;<i class="ic ic-space"></i><b onClick="accordionToggle(\'acc'.$key.'\')"><i class="ic ic-grey ic-folder"></i>'.$val['name'].'</b><div class="actlist">'.$acts.'</div>';
                    // Обработка вложенных файлов композитного модуля
                    if(isset($val['files'])){
                        if($opened==$key) $disp='block';
                        else $disp='none';
                        $out.='<ul id="acc'.$key.'sub" style="display:'.$disp.';">';
                        foreach($val['files'] AS $k=>$v){
                            $class='';
                            if(in_array($val['name'],$pgModules)) $class=' class="listActive"';
                            $acts='';
                            list($fname,$ext)=explode('.',$v['name']);
                            $add='';
                            $count='';
                            if(in_array($ext,$allowed)){
                                if($ext=='php') {
                                    if($val['name']!=$fname){
                                        if(isset($incModules[$val['name'].':'.$fname])) $count=' ('.$incModules[$val['name'].':'.$fname].')';
                                        $add=' class="red" title="'.MESSAGE('tools','moduleAdd').'" onClick="ajaxGet(\'moduleAddToPage?='.$val['name'].':'.$fname.'&page='.$page.'&opened='.$key.'\',\'AxiomWinDesc\',\'tools\')"';
                                    }
                                }
                                $acts.='<i class="ic ic-docedit" onClick="ajaxGet(\'fileEdit?='.urlencode($_SERVER['DOCUMENT_ROOT'].'/_modules/'.$val['name'].'/'.$v['name']).'&page='.$page.'\',\'AxiomWinDesc\',\'tools\')" title="'.MESSAGE('tools','codeEdit').'"></i>';
                            }
                            $acts.='<i class="ic ic-red ic-delete" title="'.MESSAGE('BUTTON_delete').'" onClick="dialogConfirm(\''.sprintf(MESSAGE('tools','MESS_fileDelete'),$v['name']).'\',\''.addSlashes('ajaxGet(\'fileDelete?='.urlencode($_SERVER['document_root'].'/_modules/'.$val['name'].'/'.$v['name']).'\',\'hiddenblock\',\'tools\')').'\')"></i>';
                            $ticon='<i class="ic ic-space lic"></i>';
                            if(isset($pgModules[$val['name'].':'.$fname])) $ticon='<i class="ic ic-green ic-checkbox lic"></i>';
                            $treeicon='<div class="pg pg_trn"></div>';
                            if(!isset($val['files'][($k+1)])) $treeicon='<div class="pg pg_tne"></div>';
                            $out.='<li'.$class.'>'.$ticon.$treeicon.'<i class="ic ic-file"></i><b'.$add.'>'.$v['name'].'</b>'.$count.'<div class="actlist">'.$acts.'</div></li>';
                        }
                        $out.='</ul>';
                    }
                    $out.='</li>';
                }
            }
            // Простые модули (одинокие файлы php)
            else {
                list($fname,$ext)=explode('.',$val['name']);
                $acts='';
                $add='';
                $ticon='<i class="ic ic-space lic"></i>';
                if($ext=='php') {
                    $add=' class="red" title="'.MESSAGE('tools','moduleAdd').'" onClick="ajaxGet(\'moduleAddToPage?='.$fname.'&page='.$page.'\',\'AxiomWinDesc\',\'tools\')"';
                    if(isset($pgModules[$fname])) $ticon='<i class="ic ic-green ic-checkbox lic" style="margin-left:9px;"></i>';
                }
                $count='';// Общее к-во подключений
                if(in_array($ext,$allowed)){
                    if(isset($incModules[$fname])) $count=' ('.$incModules[$fname].')';
                    $acts.='<i class="ic ic-docedit" onClick="ajaxGet(\'fileEdit?='.urlencode($_SERVER['DOCUMENT_ROOT'].'/_modules/'.$val['name']).'&page='.$page.'\',\'AxiomWinDesc\',\'tools\')" title="'.MESSAGE('tools','fileEdit').'"></i>';
                }
                $acts.='<i class="ic ic-delete" title="'.MESSAGE('BUTTON_delete').'" onClick="dialogConfirm(\''.sprintf(MESSAGE('tools','MESS_fileDelete'),$val['name']).'\',\''.addSlashes('ajaxGet(\'fileDelete?='.urlencode($_SERVER['document_root'].'/_modules/'.$val['name']).'\',\'AxiomWinDesc\',\'tools\')').'\')"></i>';

                $files.='<li>'.$ticon.'<b class="red"'.$add.'><i class="ic ic-grey ic-php"></i>'.$val['name'].'</b>'.$count.'<div class="actlist">'.$acts.'</div></li>';
            }
        }
        $out.=$files.'</ul>';
        $added='';
        if($page!=0) $added='<div class="field" id="modulesInc">'.tools::showModulesActive($page).'</div>';
        $out=$added.'<h3>'.MESSAGE('tools','modulesAll').'</h3>
        <div class="field">
            <div class="btn"><i class="ic ic-circleadd"></i>'.MESSAGE('tools','moduleCreate').'</div>
            <div class="btn"><i class="ic ic-ftpsession"></i>'.MESSAGE('tools','moduleRepository').'</div>
        </div>'.$out.'</div>';
        if($nowindow==false) $out='<div id="openWindowModal" style="width:1200px; height:100%;" title="'.MESSAGE('tools','LABEL_modules').'">'.$out.'</div>';
        return $out;
    }

    // Сохранение значений чекбоксов модуля
    static function setMdlCheck(){
        global $item,$var;
        $a=mysql::getArray("SELECT page_id,pause FROM `pages_modules` WHERE id='".escape($item)."' LIMIT 1",true);
        if($a!=false){
            if($a[$var]==1) {
                $value=0;
                if($var=='pause') ajax::classRemove('d'.$item,'disabled');
            }
            else {
                $value=1;
                if($var=='pause') ajax::classAdd('d'.$item,'disabled');
            }
            mysql::query("UPDATE `pages_modules` SET ".$var."='".escape($value)."' WHERE id='".escape($item)."' LIMIT 1");
            ajax::sound('sys');
            return true;
        }
        return false;
    }

    // Смена порядка модулей
    static function saveModulesOrder(){
        global $item;
        global $dragStatus;
        $new=explode(",",$dragStatus);
        $old=mysql::getArray("SELECT `id`,`order` FROM `pages_modules` WHERE page_id='".escape($item)."' ORDER BY `order` ASC");
        if(count($old)==count($new)){
            foreach($old AS $key=>$val){
                if($new[$key]!=$val['id']) mysql::query("UPDATE `pages_modules` SET `order`='".escape($val['order'])."' WHERE id='".escape($new[$key])."' LIMIT 1");
            }
        }
        ajax::sound("sys");
        return true;
    }

    // Смена переменной модуля
    static function updateMdlVar(){
        global $item;
        global $value;
        $value=trim(urldecode($value));
        mysql::query("UPDATE `pages_modules` SET tmplvar='".escape($value)."' WHERE id='".escape($item)."' LIMIT 1");
        ajax::message(MESSAGE('MESS_chIsSaved'));
        return false;
    }

    // Удаление модуля со страницы
    static function moduleDelete(){
        global $item;
        $ret=array();
        $editorDestroy='';
        $array=mysql::getArray("SELECT * FROM `pages_modules` WHERE id='".escape($item)."' LIMIT 1",true);
        // Если удаляемый модуль это показ дополнительного текстового блока, то
        // выполним удаление соответствующего поля редактора и почистим текст
        if($array['module']=='showTextBlock'){
            $s=unserialize($array['settings']);
            ajax::domRemove("rowckedit".onlyDigit($s['content']));// Editor delete
            $ret['content'.onlyDigit($s['content'])]='';
            mysql::query("UPDATE `pages_text` SET `text".onlyDigit($s['content'])."`=null WHERE page_id='".escape($array['page_id'])."' LIMIT 1");
        }
        ajax::domRemove("d".$item);
        ajax::domRemove("pageDropMenu");
        mysql::query("DELETE FROM `pages_modules` WHERE page_id='".escape($array['page_id'])."' AND id='".escape($item)."' LIMIT 1");
        ajax::message(sprintf(MESSAGE('pages','MESS_mdlIsDeleted'),$array['module']));
        return false;
    }

    // Удаление неиспользуемых(пустых) папок
    static function emptyFolderDelete(){
        global $item;
        $existFolders=mysql::getList("SELECT id FROM `folders` WHERE 1");
        $usedFolders=mysql::getList("SELECT DISTINCT folder AS id FROM `pages` WHERE 1");
        if(count($existFolders)!=count($usedFolders)) {
            $del=array();
            foreach($existFolders AS $val){
                if(!in_array($val,$usedFolders)) $del[]=$val;
            }
            if(isset($del[0])){
                $item=implode(',',$del);
                return self::folderDelete();
            }
        }
        return self::init();
    }

    // Удаление сразу нескольких страниц
    static function pagesDelete(){
        global $item;
        $item=urldecode($item);
        if($item!='') return self::pageDelete();
        else return self::init();
    }

    // Групповые операции над разделами и подразделами
    static function pagesLock(){
        global $item, $lock;

        $item=trim(urldecode($item));
        if($item!='') {
            $m=explode(",",$item);
            $larr=array();
            foreach($m AS $val) $larr[]=$val;
            mysql::query("UPDATE `pages` SET hidden='".escape($lock)."' WHERE id IN(".implode(",",$larr).")");
            self::refresh();
            return '<div id="splashMessage">'.MESSAGE('MESS_actionIsOk').'</div>'.self::init();
        }
    }

    // Отображение списка активных модулей страницы
    static function modulesActive($id=false){
        global $item;
        global $multiselect;
        global $settings;
        if($id===false) $id=$item;
        if(!isset($multiselect)) $multiselect=0;
        $array=module::getActive($id);
        $out='';
        if($array!=false){
            $templateVars=template::getVars(mysql::getValue("SELECT content FROM `templates` WHERE id='".escape($array[0]['template'])."' LIMIT 1"));
            $out.='<table id="mdpList" class="cmstable3" onmouseover="dragTableInit(this.id,\'pages::saveModulesOrder?='.$id.'\');">
            <tbody>';

            foreach($array AS $val){
                $moduleName=$val['module'];
                $checked='';
                $disabled='';
                $icon='<i class="ic-plugin"></i>';

                // Композитный модуль
                @list($mn,$smn)=explode(':',$val['module']);
                if($mn==$smn){
                    $icon='<span class="tooltip" data-tooltip="'.MESSAGE('tools','moduleComposite').'"><i class="ic-theme"></i></span>';
                    $moduleName=mb_strtoupper($smn,'utf-8');
                }
                $fileName=$_SERVER['DOCUMENT_ROOT'].'/_modules/'.str_replace(':','/',$val['module']).'.php';

                if(count($templateVars)>1){
                    $varlist='';
                    foreach($templateVars AS $var){
                        $selected='';
                        if($var==$val['tmplvar']) $selected=' selected="selected"';
                        $varlist.='<option'.$selected.' value="'.$var.'">'.$var.'</option>';
                    }
                    $varlist='<select class="mdlVars" id="md'.$val['id'].'" onChange="changeMdlTmp('.$val['id'].')">'.$varlist.'</select>';
                }
                else $varlist='<input type="text" class="input" disabled="disabled" style="width:90px; font-size:12px; padding:0; height:22px !important; margin-top:-3px !important;" value="'.$val['tmplvar'].'"/>';

                // скрытый модуль
                if($val['pause']==0) $checked=' checked="checked"';
                else {
                    $disabled=' disabled';
                    $icon='<span class="tooltip" data-tooltip="'.MESSAGE('pages','MESS_moduleIsLocked').'"><i class="ic-red ic-blocked"></i></span>';
                }
                $editor='';
                $mslink='';
                if($val['module']=='showText') $mcomment='<span>'.MESSAGE('pages','MESS_defaultModule').'</span>';
                else $mcomment='<span>'.$val['comment'].'</span>';
                $out.='<tr id="d'.$val['id'].'" class="'.$disabled.'"><td class="drag"><i class="ic-move-v nomargin"></i></td><td><b class="hand" onClick="ajaxGet(\'tools::moduleSettings?='.$val['id'].'&win=1\')">'.$moduleName.'</b>'.$mcomment.'</td><td>'.$varlist.'</td><td><i class="ic-menu" onClick="mdlMenu('.$val['id'].',\''.$val['module'].'\','.$val['pause'].',0,0,0,0,0)"></i></td></tr>';
            }
            $out.='</tbody>
            </table>';
        }
        if($multiselect==1) $mslink='0';
        else $mslink='1';
        return '<div class="row" style="margin:-10px 0 6px 0; display:block; width:200px;">
            <div class="btn tooltip" data-tooltip="'.MESSAGE('pages','LABEL_moduleAdd').'" onClick="ajaxGet(\'pages::modulesShow?=&page='.$id.'\')"><i class="ic-plus" style="margin-right:0;"></i>&nbsp;'.MESSAGE('LABEL_add').'</div>
        </div>'.$out;
    }


    // Сохранение rel="canonical"
    static function saveCanonical(){
        global $item;
        global $canonical;
        
        mysql::query("UPDATE `pages` SET canonical='".escape(urldecode($canonical))."' WHERE id='".escape($item)."' LIMIT 1");
        echo '<div id="splashMessage">'.MESSAGE('MESS_chIsSaved').'</div>';
    }

    // Изменение параметра
    static function updateDBValue(){
        global $item;
        global $value;
        global $pageId;
        
        $splash=MESSAGE('MESS_chIsSaved');
        mysql::query("UPDATE `pages` SET `".escape($item)."`='".escape($value)."' WHERE id='".escape($pageId)."' LIMIT 1");
        if($item==="finalize"){
            $larr=page::allPageChildsId($pageId);// Все потомки заданной страницы
            if($larr!=false) {
                mysql::query("UPDATE `pages` SET hidden='".escape($value)."' WHERE id IN(".implode(",",$larr).")");
                if($value==1) $splash=MESSAGE('pages','MESS_finalizeChLock');
                self::refresh();
            }
        }
        if(mysql::error()!="") $splash=mysql::error();
        return true;
    }

    // Сохранение метаданных
    static function metaSave(){
        mysql::query("UPDATE `pages_text` SET keywords='".escape(trim(clearFull($_POST['pg']['keywords'])))."', description='".escape(trim(clearFull($_POST['pg']['description'])))."' WHERE page_id='".escape(onlyDigit($_POST['page']))."' LIMIT 1");
    }

    // Получение списка повторяющихся на странице слов, для формирования ключевиков
    static function getKeywords(){
        global $item, $settings;
        $out='';
        $path=page::getPath($item);
        if($path=='/') $path=$settings['protocol'].$settings['siteUrl'].'/';
        else $path=$settings['protocol'].$settings['siteUrl'].$path;
	    $item=file_get_contents($path);
	    $item=nl2br($item);
        $item = str_replace("&amp;gt; ", "&gt; ", $item);
        $item = str_replace("&nbsp;", " ", $item);
        $item=strip_tags_smart($item);
	    if(isset($item)){
            $item = str_replace("&laquo;", " ", $item);
            $item = str_replace("&raquo;", " ", $item);
            $item = str_replace("&minus;", " ", $item);
            $item = str_replace("&mdash;", " ", $item);
            $item = str_replace("&ndash;", " ", $item);
            $item = str_replace("&hellip;", " ", $item);
            $item = str_replace("&copy;", " ", $item);
            $item = str_replace("&laquo;", " ", $item);
            $item = str_replace("&laquo;", " ", $item);
            $item=str_replace(","," ",$item);
            $item=str_replace("."," ",$item);
            $item=str_replace("-"," ",$item);
            $item=str_replace("("," ",$item);
            $item=str_replace(")"," ",$item);
            $item=str_replace(":"," ",$item);
            $item=str_replace("/"," ",$item);
            $item=str_replace(";"," ",$item);
		    $item=str_replace("\n","",$item); $item=str_replace("\r"," ",$item);
		    $item=str_replace("&ndash;"," ",$item);
		    if(function_exists('mb_strtolower')) $item=mb_strtolower($item, 'UTF-8');
		    else $item=strtolower($item);
            //echo htmlspecialchars($item);
		    $item=str_replace("."," ",$item);
		    $item=str_replace(","," ",$item);
		    // Слова, которые не брать в рассчет при получении ключевиков
		    $no=explode(',',MESSAGE('pages','MESS_keywIgnoreWords'));
		    $array=explode(" ",$item);
		    $find=array();
		    $finded=array();
            include_once($_SERVER['DOCUMENT_ROOT'].'/_classes/stemmer_ru.php');
            include_once($_SERVER['DOCUMENT_ROOT'].'/_classes/stemmer_en.php');
            $stemru = new Stemmer_RU;
            $stemen = new Stemmer_EN;
		    foreach($array AS $val){
		        $val=trim($val);
		        if(mb_strlen($val,'utf-8')>=3){
                    if( preg_match("'^[а-я]+$'iu", $val) ){
                        $stem = $stemru->getWordBase($val);
                    } elseif( preg_match("'^[a-z]+$'iu", $val) ){
                        $stem = $stemen->getWordBase($val);
                    }
                    if (strpos($val, $stem) !== false){
                        if(!isset($find[$stem])){
                            $find[$stem]=array();
                        }
                        if(!isset($find[$stem][$val])){
                            $find[$stem][$val]=0;
                        }
                        $find[$stem][$val]++;
                    }
                }
            }
            $out.='<table class="kwTable"><tr><th>Стем (основа)</th><th style="width:40px;"></th><th>Словоформы</th></tr>';
            $arout=array();
            $arc=0;
            foreach($find AS $key=>$val){
                $count=0;
                $wrdarr=array();
                foreach($val AS $k=>$v){
                    $count=$count+$v;
                    $wrdarr[]='<b onClick="addKeyw(\''.$k.'\')">'.$k.'</b> <span>'.$v.'</span>';
                }
                if($count>=2) $arout[$count.'.'.$arc]='<tr><td>'.$key.'</td><td>'.$count.'</td><td>'.implode(', ',$wrdarr).'</td></tr>';
                $arc++;
            }
            krsort($arout);
            foreach($arout AS $val){
                $out.=$val;
            }
            $out.='</table>';
	    }
	    if($out!=''){
		    return array('kList'=>'<div style="width:650px; display:table-cell; margin-top:8px; clear:both; padding:4px; border:1px solid #ffffff; background:#fdfdfd"><div class="help">'.MESSAGE('pages','MESS_findedWords').'</div><div style="display:block; float:none; clear:both; height:200px; overflow-y:scroll;">'.$out.'</div></div>');
		}
	    else return array('kList'=>'<div class="error" style="margin-top:8px;">'.MESSAGE('pages','ERROR_notFoundWords').'</div>');
    }

    // Добавление нового текстового блока
    static function addEditor(){
        global $item, $page;
        // Добавляем модуль
        $m['module']='showTextBlock';
        $m['tmplvar']='pageText';
        $m['pause']=0;
        $m['comment']=sprintf(MESSAGE('pages','MESS_tbModule'),$item);
        $m['settings']=array('content'=>'text'.$item);
        module::moduleAdd($page,$m);
        $item=$page;
        ajax::javascript('pa("pe",'.$page.')');
        ajax::sound("sys");
        return true;
    }

    // Сохранение контента редактируемой страницы
    static function pageSave(){
        $page=$_POST['page'];
        // Контроль языка
        if(!isset($page['lang'])){
            if(isset($_POST['pg']['fixedlang'])) $page['lang']=$_POST['pg']['fixedlang'];
        }

        if($page['parent']!=0){
            if($page['alias']==''){
                $page['alias']=translit($page['btname']);
                $va=mysql::getArray("SELECT id,alias,btname,`order` FROM `pages` WHERE parent='".escape($page['parent'])."' ORDER BY `order` ASC");
                $existedAlias=array();
                if($va!=false){
                    foreach($va AS $z) $existedAlias[$z['alias']]=1;
                    if(isset($existedAlias[$page['alias']])){
                        for($zz=1;$zz<=10;$zz++){ if(!isset($existedAlias[$page['alias'].$zz])){ $page['alias']=$page['alias'].$zz; break; } }
                    }
                }
            }
            $path=page::getPath($page['parent']);
            if($path=='/') $path='index/';
            $path.=$page['alias'].'/';
        }
        else $path=page::getPath($page['id']);
		
		// Контроль правильности сохранения языка страницы
		if(!isset($page['lang']) || $page['lang']==''){
			if(isset($_POST['pg']['fixedlang'])) $page['lang']=$_POST['pg']['fixedlang'];
			else {
				if(isset($_POST['pg']['oldlang'])) $page['lang']=$_POST['pg']['oldlang'];
			}
		}
        mysql::query("UPDATE `pages` SET lang='".escape($page['lang'])."', alias='".escape($page['alias'])."', hidden='".escape($page['hidden'])."', urlhash='".escape(md5($path))."', hideinmenu='".escape($page['hideinmenu'])."', btname='".escape($page['btname'])."', nosaved='0' WHERE id='".escape($page['id'])."' LIMIT 1");
		
		
        // Сохраняем основной текст
        $sstring="";
        for($i=2;$i<=5;$i++){
            if(isset($page['text'.$i])) {
				$txt=$page['text'.$i];
				$sstring.=",`text".$i."`='".escape($txt)."' ";
			}
            else $sstring.=",`text".$i."`='' ";
        }
        mysql::query("UPDATE `pages_text` SET name='".escape($page['name'])."', title='".escape($page['title'])."', `text`='".escape($page['text'])."'".$sstring." WHERE page_id='".escape($page['id'])."' LIMIT 1");

        $out=self::refresh();
        ajax::message(MESSAGE('MESS_chIsSaved'));
        ajax::sound("sys");
        if($_POST['exitflag']!=false) return self::init();
        else return false;
    }

    // Удаление папки
    static function folderDelete(){
        global $item;
        mysql::query("DELETE FROM `folders` WHERE id IN (".escape($item).")");
        $id=mysql::getValue("SELECT id FROM `folders` WHERE `default`='1' ORDER BY `order` ASC LIMIT 1");
        if($id===false) mysql::query("UPDATE `folders` SET `default`='1' ORDER BY `order` ASC LIMIT 1");
        self::refresh();
        return self::init();
    }

    // Назначение главной папки
    static function folderSetMain(){
        global $item;
        mysql::query("UPDATE `folders` SET `default`='0' WHERE `default`='1' LIMIT 1");
        mysql::query("UPDATE `folders` SET `default`='1' WHERE id='".escape($item)."' LIMIT 1");
        ajax::sound('sys');
        return array('AXplist'=>self::refresh(true));
    }

    // Перемещение папки
    static function folderMove(){
        global $item, $direction;
        $out=true;
        if($direction=='down') $order='ASC';
        else $order='DESC';
        $array=mysql::getArray("SELECT id,`order` FROM `folders` ORDER BY `order` ".$order);
        if($array!=false){
            foreach($array AS $key=>$val){
                if($val['id']==$item){
                    if(isset($array[($key+1)])){
                        mysql::query("UPDATE `folders` SET `order`='".escape($array[($key+1)]['order'])."' WHERE id='".escape($item)."' LIMIT 1");
                        mysql::query("UPDATE `folders` SET `order`='".escape($val['order'])."' WHERE id='".escape($array[($key+1)]['id'])."' LIMIT 1");
                    }
                    break;
                }
            }
            $out=self::refresh(true);
        }
        ajax::sound("sys");
        return array('AXplist'=>$out);
    }

    // Удаление страницы
    static function pageDelete(){
        global $item, $settings;
        $out='';
        mysql::query("DELETE FROM `pages` WHERE id IN (".escape($item).")");
        // Узнаем, есть ли на сайте СТАРТОВАЯ страница с языком по-умолчанию.
        // И если ее нет - объявим принудительно стартовой первую попавшуюся страницу с языком по-умолчанию
        $dp=mysql::getValue("SELECT pages.id FROM `folders`,`pages` WHERE folders.default='1' AND pages.folder=folders.id AND pages.lang='".escape($settings['siteDefaultLang'])."' LIMIT 1");
        if($dp===false){
            $folderid=mysql::getValue("SELECT folders.id FROM `folders`,`pages` WHERE pages.folder=folders.id AND pages.lang='".escape($settings['siteDefaultLang'])."' LIMIT 1");
            if($folderid!==false) mysql::query("UPDATE `folders` SET default='1' WHERE id='".escape($folderid)."' LIMIT 1");
        }
        // Узнаем, есть ли пустые папки, чтобы вывести окно для удаления оных
        $existFolders=mysql::getArray("SELECT id FROM `folders` WHERE 1");
        $usedFolders=mysql::getArray("SELECT DISTINCT folder FROM `pages` WHERE 1");
        if(count($existFolders)!=count($usedFolders)) {
            ajax::dialogConfirm(MESSAGE('pages','ERROR_fldCount'), "ajaxGet('emptyFolderDelete?=')");
        }
        $out=self::refresh(true);
        ajax::sound("sys");
        return array('AXplist'=>$out);
    }

    // Блокировка страницы
    static function pageLock(){
        global $item;
        $lock=mysql::getValue("SELECT hidden FROM `pages` WHERE id='".escape($item)."' LIMIT 1");
        if($lock==1) $lock=0;
        else $lock=1;
        mysql::query("UPDATE `pages` SET hidden='".escape($lock)."' WHERE id='".escape($item)."'");
        // Если у страницы есть потомки, то спросим о необходимости применения блокировки к ним
        if(mysql::getValue("SELECT id FROM `pages` WHERE parent ='".escape($item)."'")!=false) {
            ajax::dialogConfirm(MESSAGE('pages','LABEL_lockActionForChilds'),"ajaxGet('pages::pageLockChilds?=".$item."&lock=".$lock."')");
        }
        return array('AXplist'=>self::refresh(true));
    }
    // Применение блокировки к потомкам
    static function pageLockChilds(){
        global $item,$lock;
        $larr=page::allPageChildsId($item);// Все потомки заданной страницы
        if($larr!=false) mysql::query("UPDATE `pages` SET hidden='".escape($lock)."' WHERE id IN(".implode(",",$larr).")");
        ajax::message(MESSAGE('MESS_actionIsOk'));
        return array('AXplist'=>self::refresh(true));
    }



    // Добавление дочерней страницы
    static function pageAddChild(){
        global $item, $settings, $folder;
        $parent=$item;
        $item=0;

        // Сохраняем временную страницу
        if(!isset($folder)) $find=mysql::getArray("SELECT pages.folder, pages.lang, pages.template, folders.alias AS folderalias FROM `pages`,`folders` WHERE pages.id='".escape($parent)."' AND folders.id=pages.folder LIMIT 1",true);
        else {
            // Если задана папка, то создаем в ней корневую страницу
            $find['folder']=$folder;
            $a=explode(",",$settings['siteLangs']);
            $allangs=array();
            foreach($a AS $val){
                list($l,)=explode(":",$val);
                $allangs[$l]=1;
            }
            $existLangs=mysql::getArray("SELECT DISTINCT lang FROM `pages` WHERE folder='".escape($folder)."' AND parent='0'");
            if($existLangs!=false){
                foreach($existLangs AS $val){
                if(isset($allangs[$val['lang']])) unset($allangs[$val['lang']]);
                }
            }
            if(count($allangs)>=1) {
                $l=array_keys($allangs);
                $find['lang']=$l[0];
            }
            else $find['lang']=$settings['siteDefaultLang'];
            $find['template']=mysql::getValue("SELECT id FROM `templates` ORDER BY `order` ASC LIMIT 1");
            $find['folderalias']=mysql::getValue("SELECT alias FROM `folders` WHERE id='".escape($folder)."' LIMIT 1");
            $parent=0;
        }
        if($find!=false){
            // Если есть страница с таким же именем, то изменим имя
            $pgname=MESSAGE('pages','MESS_defaultPageName');
            $va=mysql::getArray("SELECT id,alias,btname,`order` FROM `pages` WHERE parent='".escape($parent)."' ORDER BY `order` ASC");
            $existedAlias=array();
            $order=0;
            $alias='page';
            if($va!=false){
                foreach($va AS $z){
                    $existedAlias[$z['alias']]=1;
                    if($order<$z['order']) $order=$z['order'];
                }
                if(isset($existedAlias['page'])){
                    for($zz=1;$zz<=10;$zz++){
                        if(!isset($existedAlias['page'.$zz])){
                            $alias='page'.$zz;
                            break;
                        }
                    }
                }
            }
            if($parent!=0){
                $pgname=MESSAGE('pages','MESS_defaultPageName').' '.date("d.m.Y H:i:s",time());
                $path=page::getPath($parent).$alias.'/';
            }
            else {
                $path='index/';
                $order=0;
            }
            $order++;
            // Получим все страницы с этим потомком
            mysql::query("INSERT INTO `pages` SET folder='".escape($find['folder'])."', `order`='".escape($order)."', parent='".escape($parent)."', template='".escape($find['template'])."', alias='".escape($alias)."', urlhash='".escape(md5($path))."', lang='".escape($find['lang'])."', btname='".$pgname."', nosaved='1', created=NOW()");
	        $item=mysql::insertId();
		    mysql::query("INSERT INTO `pages_modules` SET page_id='".escape($item)."', module='showText', tmplvar='pageText', pause='0', `comment`='".escape(MESSAGE('pages','MESS_mdlPageTextComment'))."'");
		    mysql::query("INSERT INTO `pages_text` SET page_id='".escape($item)."', name='".escape($pgname)."'");
		    self::refresh();
            return self::pageEdit();
        }
        else return '<div class="error">'.MESSAGE('pages','LABEL_prntNtFound').'</div>';
    }

    // Редактирование страницы
    static function pageEdit(){
        global $settings,$parent, $item;
        $out='';
        $out.='<div class="container"><h2>Разделы сайта</h2></div>
        <div class="container" style="margin-bottom:10px;"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('pages','pgName').'</a></li><li><span>'.MESSAGE('pages','ACTS_pgPropertyes').'</span></li></ul></div>';

        $fixedlang=false;
        $npage=new page;
        $npage->setParam(page::getEditedById($item));// Получаем параметры страницы из БД
        if(isset($parent)) $npage->setParam(array('parent'=>$parent));
        $oldalias=array('type'=>'hidden', 'name'=>'pg[oldalias]', 'value'=>$npage->property['alias']);
	    $oldlang=array('type'=>'hidden', 'name'=>'pg[oldlang]', 'value'=>$npage->property['lang']);

        // Если страница еще не сохранялась пользователем, то почистим названия
        if($npage->property['nosaved']==1){
            $npage->property['btname']='';
            $npage->property['name']='';
            $npage->property['title']='';
            $npage->property['alias']='';
        }

        if($npage->property['id']==0) /** @noinspection PhpUnusedLocalVariableInspection */
        $onkeyup=false;
        $alltmpl=false;
        $firsttemplate=false;
        $requiredTab='';

        // Выбор языка разрешен только для корневой страницы (если parent=0)
        if($npage->property['parent']==0){
            // Получим список языков, которые недоступны,
            // для этого получим все языки для всех корневых страниц данного раздела, кроме текущей
	        $i=mysql::getArray("SELECT lang FROM `pages` WHERE folder='".escape($npage->property['folder'])."' AND `parent`='0' AND id!='".escape($npage->property['id'])."'");
	        if($i!=false) { foreach($i AS $val) { @$lockLang[$val['lang']]=1; } }
	        $plang=$npage->property['lang'];
	        if(isset($lockLang[$npage->property['lang']])){ $plang=false; }

	        // Доступные языки сайта
	        $array=explode(',',$settings['siteLangs']);
	        $allangs=array();
	        if(count($array)>=2){
                foreach($array AS $val){
                    list($lng,$name)=explode(':',$val);
                    if(!isset($lockLang[$lng])) {
                        @$allangs[$lng]=$name;
                        if($plang==false) { $npage->property['lang']=$lng; $plang=$lng; }
                    }
                }
		        if(count($allangs)==1) {
			        foreach($allangs AS $key=>$val) $npage->property['lang']=$key;
			        $lselect=array('type'=>'select', 'name'=>'page[lang]','label'=>MESSAGE('pages','LABEL_pageLanguage'), 'value'=>$npage->property['lang'], 'options'=>$allangs, 'disabled'=>'disabled');
			        $fixedlang=array('type'=>'hidden','name'=>'pg[fixedlang]','value'=>$npage->property['lang']);///!!! ВАЖНО !!!! фиксация языка страницы
		        }
		        else $lselect=array('type'=>'select', 'name'=>'page[lang]','label'=>MESSAGE('pages','LABEL_pageLanguage'), 'value'=>$npage->property['lang'], 'options'=>$allangs);
	        }
	        else {
		        // Если всего лишь один язык, то сделаем поле выбора языка скрытым
		        $npage->property['lang']=$settings['siteDefaultLang'];
		        $lselect=array('type'=>'hidden','name'=>'page[lang]','value'=>$settings['siteDefaultLang']);
            }
            /** @noinspection PhpUnusedLocalVariableInspection */
            $alias=array('type'=>'text', 'class'=>'input', 'id'=>'pgalias', 'name'=>'pg[alias]', 'label'=>MESSAGE('pages','LABEL_alias').'<br><span class="smallgrey">('.MESSAGE('pages','MESS_aliasDesc').')</span>', 'value'=>$npage->property['alias'], 'size'=>32, 'maxlength'=>64);
        }
        else {
	        // Иначе, присваиваем странице язык родительской
	        $npage->property['lang']=mysql::getValue("SELECT lang FROM `pages` WHERE id='".$npage->property['parent']."' LIMIT 1");
	        $lselect=array('type'=>'hidden','name'=>'page[lang]','value'=>$npage->property['lang']);
	        $fixedlang=array('type'=>'hidden','name'=>'pg[fixedlang]','value'=>$npage->property['lang']);
	    }

        if($npage->error!=false) error("Error!!!!!");
        $alias=false;
        $z=get_object_vars($npage);
        $z=(array)$z['property'];
        // Получим список модулей страницы, чтобы отобразить поля виз-редакторов
        $pgm=module::getActive($item);
        $viseditors=array();
        $EDcounter=1;
        foreach ($pgm AS $val){
            if($val['module']=='showText' || $val['module']=='showTextBlock'){
                $prefix=$EDcounter;
                if($prefix==1) $prefix='';
                if($val['module']=='showText'){
                    $val['id']='ckedit1';
                    $val['name']='text';
                }
                else {
                    $val['id']='ckedit'.$EDcounter;
                    $val['name']='text'.$EDcounter;
                    if($val['settings']!=''){
                        $s=unserialize($val['settings']);
                        if(isset($s['content'])) {
                            $val['name']=$s['content'];
                            $val['id']='ckedit'.(onlyDigit($s['content']));
                        //echo 'ckedit'.onlyDigit($s['content']);
                        }
                    }
                }
                $mmm='text'.$prefix;
                if($EDcounter>1) $label=MESSAGE('pages','LABEL_txtBlock').' '.$EDcounter;
                else $label='';
                $viseditors[$EDcounter]=array('label'=>$label,'type'=>'ckeditor','id'=>$val['id'],'name'=>'page['.$val['name'].']','value'=>$z[$mmm],'class'=>'axiom','colspan'=>2, 'style'=>'width:'.$settings['editorWidth'].'; height:516px;');
                $EDcounter++;
            }
        }
        for($i=1;$i<=10;$i++){
            if(!isset($viseditors[$i])) $viseditors[$i]=array('type'=>'html','label'=>MESSAGE('pages','LABEL_txtBlock').' '.$i,'value'=>'<tr id="rowckedit'.$i.'"><td colspan="2"><div id="content'.$i.'"></div></td></tr>');
        }

	    // Если новая страница, то включаем автотранслит, иначе оставляем старый ALIAS
	    //$autotranslit=false;
        if($npage->property['parent']!=0) {
            $autotranslit="pgAutotranslit(1)";
            $alias=array('type'=>'text', 'class'=>'input', 'id'=>'pgalias', 'name'=>'page[alias]', 'label'=>MESSAGE('pages','LABEL_alias'), 'value'=>$npage->property['alias'], 'size'=>32, 'maxlength'=>64, 'onKeyUp'=>'setValue("pgalias",this.value)');
        }
        else $autotranslit="pgAutotranslit()";

        $fields=array(
        //$oldalias,
        $oldlang,
        array('type'=>'hidden', 'id'=>'exitflag', 'name'=>'exitflag', 'value'=>'0'),
        array('type'=>'hidden','id'=>'pageId','name'=>'page[id]','value'=>$npage->property['id']),
        array('type'=>'hidden','name'=>'page[parent]','value'=>$npage->property['parent']),
        array('type'=>'hidden','name'=>'page[folder]','value'=>$npage->property['folder']),
        //array('type'=>'hidden','name'=>'page[alias]','value'=>$npage->property['alias']),
        array('type'=>'hidden','name'=>'page[urlhash]','value'=>$npage->property['urlhash']),
        array('type'=>'html','value'=>'<tr><td style="width:230px;"></td><td><div class="field">'),
        array('type'=>'checkbox','name'=>'page[hidden]', 'noformat'=>true, 'label'=>MESSAGE('pages','LABEL_lock'), 'tooltip'=>MESSAGE('pages','TOOLTIP_lock'), 'class'=>'checkbox', 'value'=>$npage->property['hidden']),
        array('type'=>'checkbox','name'=>'page[hideinmenu]', 'noformat'=>true, 'label'=>MESSAGE('pages','LABEL_hideInMenu'), 'tooltip'=>MESSAGE('pages','TOOLTIP_hideInMenu'), 'class'=>'checkbox','value'=>$npage->property['hideinmenu']),
        array('type'=>'html', 'value'=>'</div></td></tr>'),
        $lselect,
        $fixedlang,
        array('type'=>'text', 'class'=>'input', 'name'=>'page[btname]', 'autocomplete'=>'off', 'id'=>'pgname', 'label'=>MESSAGE('pages','LABEL_menuName'), 'value'=>$npage->property['btname'], 'size'=>64, 'maxlength'=>180, 'after'=>'<input type="checkbox" name="block" id="block" />'),
        array('type'=>'text', 'class'=>'input', 'name'=>'page[name]', 'id'=>'pageName', 'autocomplete'=>'off', 'label'=>MESSAGE('pages','LABEL_pageHeader'), 'value'=>$npage->property['name'], 'size'=>64, 'style'=>'width:560px;', 'maxlength'=>255, 'onkeyup'=>$autotranslit),
        array('type'=>'text', 'class'=>'input', 'id'=>'pageTitle', 'name'=>'page[title]', 'autocomplete'=>'off',   'label'=>MESSAGE('pages','LABEL_pageTitle'), 'value'=>$npage->property['title'], 'size'=>64, 'maxlength'=>255, 'style'=>'width:560px;'),
        $alias,
        $viseditors[1],
        $viseditors[2],
        $viseditors[3],
        $viseditors[4],
        $viseditors[5]);

        $form=new form($fields);
        $form->id='contentForm';
        $form->enctype='multipart/form-data';
        $form->type="horizontal";
        $form->method='POST';

        $mdl=ui::tabs( array(
            array('name'=>'<i class="ic-puzzle"></i>'.MESSAGE('pages','TAB_mdl'),'active'=>true,'content'=>'<div id="modules" style="height:664px;">'.self::modulesActive($npage->property['id']).'</div>'),
            array('name'=>'<i class="ic-file-code"></i>'.MESSAGE('pages','LABEL_snippet'),'content'=>'<div id="snippets" style="height:664px;"></div>', 'onClick'=>'showSnippets()')
        ),"tb2");

        $mainTab='<table class="pgEdTable" cellpadding="0" cellspacing="0"><tr><td style="padding:0 !important">'.$form->show().'</td><td style="padding:0 !important"><div id="emset"><h2>'.MESSAGE('pages','LABEL_pageStructure').'</h2>'.$mdl.'</td></tr></table>';


        // Вторая вкладка Метаданные
        $fields=array(
            array('type'=>'hidden','name'=>'page','value'=>$npage->property['id']),
            array('type'=>'html','value'=>'<tr><td style="width:240px;"></td><td></td></tr>'),
            array('type'=>'textarea','name'=>'pg[keywords]', 'cols'=>80, 'id'=>'metakey', 'rows'=>4, 'class'=>'input', 'label'=>MESSAGE('pages','LABEL_keywords').'<br><b>META KEYWORDS</b>','value'=>$npage->property['keywords'],'style'=>'width:650px; height:120px;','maxlength'=>1000, 'lengthcomment'=>MESSAGE('pages','MESS_KeyMax'), 'counter'=>true, 'onKeyDown'=>'fieldAutosave(this.id,"metaSave()",1)'),
            array('type'=>'html','value'=>'<tr><td>&nbsp;</td><td>
            <div class="buttonarea"><div class="btn" onClick="ajaxGet(\'pages::getKeywords?='.$npage->property['id'].'\')"><i class="ic-sharell"></i>'.MESSAGE('pages','BUTTON_getKeyw').'</div></div>
            <div style="padding-top:6px; float:none; clear:both" id="kList"><div class="info" style="width:650px;">'.MESSAGE('pages','MESS_getKeywDesc').'</div></div></td></tr>'),
            '-',
            array('type'=>'textarea','name'=>'pg[description]','cols'=>80, 'rows'=>3, 'class'=>'input', 'label'=>MESSAGE('pages','LABEL_desc').'<br><b>META DESCRIPTION</b>','value'=>$npage->property['description'],'maxlength'=>200, 'lengthcomment'=>MESSAGE('pages','MESS_DescMax'), 'style'=>'width:650px; height:100px;', 'counter'=>true,  'onKeyDown'=>'fieldAutosave(this.id,"metaSave()",1)')
        );
        $form=new form($fields);
        $form->id='metaForm';
        $form->enctype='multipart/form-data';
        $form->method='POST';
        $form->type="horizontal";
        $metaTab=$form->show();

        // Вкладка ДОПОЛНИТЕЛЬНО
        $array=mysql::getArray("SELECT id,name FROM `templates` WHERE context='site' ORDER BY `order` ASC");
        foreach($array AS $val){
            if(!isset($firsttemplate)) $firsttemplate=$val['id'];
            if($npage->property['template']==0) $npage->property['template']=$val['id'];
            @$alltmpl[$val['id']]=$val['name'];
        }
        /** @noinspection PhpParamsInspection */
        if(count($alltmpl)==1) $disabled=true;
        else $disabled='';
        if(!isset($alltmpl[$npage->property['template']])) $npage->property['template']=$firsttemplate;

        $fields=array(
            array('type'=>'hidden','name'=>'page','value'=>$npage->property['id']),
            array('type'=>'hidden','name'=>'action','value'=>'extendedSave'),
            array('type'=>'html','value'=>'<tr><td><label for="tmpid">'.MESSAGE('pages','LABEL_pageTemplate').'</label></td><td>'),
            array('type'=>'select','noformat'=>true,'options'=>$alltmpl, 'name'=>'pg[template]', 'id'=>'template','value'=>$npage->property['template'],'disabled'=>$disabled, 'style'=>'width:240px;', 'onChange'=>'changeSelect("template",'.$npage->property['id'].');'),
            array('type'=>'html','value'=>'<div style="float:left;"><a class="btn" target="_blank" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/templatePreview.php?template='.$npage->property['template'].'"><i class="ic-format-shapes"></i>'.MESSAGE('pages','BUTTON_viewTemplate').'</a></div></td></tr>'),
            '-',
            array('type'=>'select','options'=>MESSAGE('MESS_userGroups'), 'name'=>'pg[access]','id'=>'access',
            'label'=>MESSAGE('pages','LABEL_pageAcess'),'value'=>$npage->property['access'], 'description'=>MESSAGE('pages','MESS_accessDesc'),'style'=>'width:240px;','onChange'=>'changeSelect("access",'.$npage->property['id'].')'),
            '-',
            array('type'=>'html','value'=>'<tr><td style="width:230px;"><label>'.MESSAGE('pages','LABEL_seoSettings').'</label></td><td><span class="smallgrey margin-top">'.MESSAGE('pages','MESS_seoDescription').'</span></td></tr>'),
            array('type'=>'checkbox','class'=>'switch','id'=>'noindex','name'=>'pg[noindex]','label'=>MESSAGE('pages','LABEL_noindex'),'value'=>$npage->property['noindex'],'onChange'=>'changeCheckbox("noindex",'.$npage->property['id'].')'),
            array('type'=>'checkbox','class'=>'switch','name'=>'pg[nofollow]','id'=>'nofollow','label'=>MESSAGE('pages','LABEL_nofollow'),'value'=>$npage->property['nofollow'],'onChange'=>'changeCheckbox("nofollow",'.$npage->property['id'].')'),
            array('type'=>'checkbox','class'=>'switch','name'=>'pg[noarchive]','id'=>'noarchive','label'=>MESSAGE('pages','LABEL_noarchive'),'value'=>$npage->property['noarchive'],'onChange'=>'changeCheckbox("noarchive",'.$npage->property['id'].')'),
            array('type'=>'select','options'=>MESSAGE('pages','MESS_chfreqList'), 'name'=>'pg[changefreq]','id'=>'changefreq',
            'label'=>MESSAGE('pages','LABEL_changeFreq'),'value'=>$npage->property['changefreq'], 'description'=>MESSAGE('pages','MESS_changeFreq'),'onChange'=>'changeSelect("changefreq",'.$npage->property['id'].')'),
            '-',
            array('type'=>'checkbox','class'=>'switch','name'=>'pg[finalize]','id'=>'finalize','label'=>MESSAGE('pages','LABEL_finalize'),'value'=>$npage->property['finalize'], 'onChange'=>'changeCheckbox("finalize",'.$npage->property['id'].')'),
            array('name'=>'pg[canonical]','id'=>'relcanonical', 'label'=>MESSAGE('pages','LABEL_canonical'), 'class'=>'input size-xl', 'value'=>$npage->property['canonical'], 'onKeyUp'=>'fieldAutosave("relcanonical","saveCanonical('.$npage->property['id'].')")'),
            '-',
            array('type'=>'checkbox','class'=>'switch','name'=>'pg[nocache]','id'=>'nocache','label'=>MESSAGE('pages','LABEL_nocache'),'value'=>$npage->property['nocache'],'onChange'=>'changeCheckbox("nocache",'.$npage->property['id'].')')
        );

        if($disabled===true) $fields[]=array('type'=>'hidden', 'name'=>'pg[template]', 'value'=>$npage->property['template']);
        $form=new form($fields);
        $form->method='AJAX';
        $form->type="horizontal";
        $advTab=$form->show();
        //$mdlActive=false;
        $mainActive=true;
        $reqActive=false;

        // Создаем вкладки
        $adEdbtnStyle=' style="display:block"';
        if($EDcounter>=5) $adEdbtnStyle=' style="display:none"';

        $out.=ui::tabs(array(
            array('name'=>'<i class="ic-newspaper"></i>'.MESSAGE('pages','TAB_main'),'active'=>$mainActive,'content'=>$mainTab),
            array('name'=>'<i class="ic-html"></i>'.MESSAGE('pages','TAB_meta'),'content'=>$metaTab),
            array('name'=>'<i class="ic-cogs"></i>'.MESSAGE('pages','TAB_adv'),'content'=>$advTab.'</form>')
        ),"tb1");

        $out.='<div id="staticPanel">
    <div class="container" style="padding-top:8px;">
     <div class="btn" onclick="savePage(true,\'contentForm\');"><i class="ic-save color-orange"></i>'.MESSAGE('BUTTON_saveExit').'</div>
     <div class="btn" onclick="savePage(false,\'contentForm\');"><i class="ic-save"></i>'.MESSAGE('BUTTON_save').'</div>
     <div class="btn" onclick="ajaxGet(\'pages::init\')" style="margin-left:62px;"><i class="ic-return"></i>'.MESSAGE('BUTTON_exit').'</div>
     <div id="addEditor"'.$adEdbtnStyle.' class="btn" onclick="addEditor('.$item.')"><i class="ic-dplus"></i>'.MESSAGE('pages','BUTTON_addEditor').'</div>
        </div>
    </div>';
        return array('cblock'=>$out);
    }

    // Перемещение страницы вверx или вниз
    static function pgMove(){
        global $item, $direction;
        $out='';
        $parent=mysql::getValue("SELECT parent FROM `pages` WHERE id='".escape($item)."' LIMIT 1");
        if($parent!=false){
            if($direction=='down') $order='ASC';
            else $order='DESC';
            $array=mysql::getArray("SELECT id,`order` FROM `pages` WHERE parent='".escape($parent)."' ORDER BY `order` ".$order);
            if($array!=false){
                foreach($array AS $key=>$val){
                    if($val['id']==$item){
                        if(isset($array[($key+1)])){
                            mysql::query("UPDATE `pages` SET `order`='".escape($array[($key+1)]['order'])."' WHERE id='".escape($item)."' LIMIT 1");
                            mysql::query("UPDATE `pages` SET `order`='".escape($val['order'])."' WHERE id='".escape($array[($key+1)]['id'])."' LIMIT 1");
                        }
                    break;
                    }
                }
                return array('AXplist'=>self::refresh(true));
            }
        }
        return true;
    }


    // Обновление списка разделов
    // Если $treeShow=true, то возвращается дерево категорий
    // Иначе - просто true
    static function refresh($treeShow=false){
        global $settings, $catarr, $show;

        $out='';// Основное дерево категорий
        $out2='';// Простое дерево категорий
        $allPages=page::getPages(true);
		
		// Удалим все "битые" страницы
		if($allPages!=false){
			$notIn=array();
			foreach($allPages AS $val) $notIn[]=$val['id'];
			if(isset($notIn[0])){
				mysql::query("DELETE FROM `pages` WHERE id NOT IN(".implode(",",$notIn).")");
				mysql::query("DELETE FROM `pages_modules` WHERE page_id NOT IN(".implode(",",$notIn).")");
				mysql::query("DELETE FROM `pages_text` WHERE page_id NOT IN(".implode(",",$notIn).")");
			}
		}
		
		$category_arr=array();
		if($allPages!=false){ foreach($allPages AS $value) $category_arr[$value['lang']][]=$value; }

		if($allPages!=false){
            $out.='<table id="AXPgFullist" class="cmstable2" cellpadding="0" cellspacing="0"><tr style="display:none;"><th style=\'width:16px;\'></th><th style="width:500px;" align="left"></th><th style="width:16px;"></th><th style="width:20px;"></th><th style="width:50px;"></th><th style="width:90px; min-width:90px;"></th><th></th><th style="width:50px;"></th></tr>';
            $out2.='<table class="cmstable2" style="width:300px; padding:0; margin:0;" cellpadding="0" cellspacing="0"><tr><th style=\'width:16px;\'></th><th></th></tr>';
		    // Создадим массив папок
		    $folders=mysql::getArray("SELECT folders.id AS folder, alias AS folderalias, `order` AS folderorder,folders.default FROM `folders` ORDER BY `order` ASC");
		    //foreach($allPages AS $val){ if($val['folder']!=$l){ $folders[]=$val; $l=$val['folder']; } }
            $allLangs=array();
		    $z=explode(",",$settings['siteLangs']);
		    foreach($z AS $val){ list($l,)=explode(":",$val); $allLangs[]=$l; }

            // Формируем список разделов сайта
            foreach($folders AS $fkey=>$folder){
                if($folder['default']==1) $folderIcon='home';
                else $folderIcon='folder';
                $folderMove='';
                if(isset($folders[($fkey-1)])) $folderMove.='<i class="ic-up" onClick="pa(\'mfu\','.$folder['folder'].')"></i>';
                if(isset($folders[($fkey+1)])) $folderMove.='<i class="ic-down" onClick="pa(\'mfd\','.$folder['folder'].')"></i>';
                $out.='<tr><td><i class="ic-'.$folderIcon.'"></i></td><td class="fn"><span onclick="pa(\'ef\','.$folder['folder'].')">'.$folder['folderalias'].'</span><div id="f'.$folder['folder'].'" class="mbtn" onclick="fms('.$folder['folder'].')"></div></td><td colspan="5"></td><td class="cnt">'.$folderMove.'</td></tr>';
                $out2.='<tr><td><i class="ic-'.$folderIcon.'"></i></td><td class="fn"><span>'.$folder['folderalias'].'</span></td></tr>';
                // По очереди пробегаем по всем доступным языкам для сайта
                // и формируем структуру разделов
                foreach($allLangs AS $lang){
                    if(isset($category_arr[$lang])){
                        $catarr=$category_arr[$lang];
                         // Расстановка глубины веток !!!!!
                        self::deepTree(0, $folder['folder'], 0);
                        // Формирование иерархической структуры
                        $out.=self::outTree(0, $folder['folder']);
                        $out2.=self::outTree(0, $folder['folder'], true);
                    }
                }
            }
            $out.='</table>';
            $out2.='</table>';
        }

        // Сохранение в базу
        mysql::query("UPDATE `settings` SET `value`='".escape($out)."' WHERE name='pages' LIMIT 1");
        mysql::query("UPDATE `settings` SET `value`='".escape($out2)."' WHERE name='kpages' LIMIT 1");
        if(isset($show)){
            if($show==1) {
                return array('AXplist'=>$out);
            }
        }
        if($treeShow!=false) return $out;
        else return true;
    }

// Получение массива разделов в порядке сортировки
static function getOrdered($lang){
    static $ocatarr;
    global $catarr;
    if(!isset($ocatarr[$lang])) $ocatarr[$lang]=page::allPageChilds(0,$catarr);
    return $ocatarr[$lang];
}


// есть ли элементы на той же глубине сверху
static function existTopElement($id,$deep,$folder,$lang){
    $ocatarr=self::getOrdered($lang);
    $ret=0;
	foreach($ocatarr AS $key=>$val){
	    if($val['id']==$id) {
	        // Начинаем подниматься вверх по массиву пока не найдем совпадение
            for($n=$key; $n>=0; $n--){
                if(!isset($ocatarr[$n])) break;
                else {
                    if($ocatarr[$n]['folder']!=$folder) break;
                    if($ocatarr[$n]['deep']==$deep && $ocatarr[$n]['lang']==$lang){ $ret=1; break; }
                }
            }
            break;
        }
	}
	return $ret;
}

// есть ли элементы на той же глубине  ниже
static function existBottomElement($id,$deep,$folder,$lang){
    $ret=0;
    $ocatarr=self::getOrdered($lang);
	foreach($ocatarr AS $key=>$val){
	    if($val['id']==$id) {
            // Спускаемся вниз по массиву, пока не найдем первое совпадение или не закончится папка
            for($n=$key; $n<=count($ocatarr); $n++){
                if($ocatarr[$n]['folder']!=$folder) break;
                if($n!=$key){
                    if($ocatarr[$n]['deep']==$deep && $ocatarr[$n]['lang']==$lang){ $ret=1; break; }
                }
            }
            break;
        }
	}


	return $ret;
	}


// Формирование дерева
static function outTree($parent_id, $folder, $smalltree=false){
    global $catarr;
    
    $tree='';
	$deepArray=array();
	$deepArray[]='none';
    foreach($catarr as $key=>$value){
        if($value['parent']==$parent_id && $value['folder']==$folder){
            $moveactions='';
            // Отрисовка ветвей дерева
            $treeImages="";
            for($m=0; $m<=$value['deep']; $m++){
                $icon='';
                if($m>=1){
			        if(self::existTopElement($value['id'],$m,$value['folder'],$value['lang'])==1 && self::existBottomElement($value['id'],$m,$value['folder'],$value['lang'])==1) {
                        if($value['deep']==$m) $icon='trn';
                        else $icon='tnb';
                    }
                    elseif(self::existTopElement($value['id'],$m,$value['folder'],$value['lang'])==1 && self::existBottomElement($value['id'],$m,$value['folder'],$value['lang'])==0){
                        if($value['deep']==$m) $icon='tne';
                        else $icon='none';
                    }
                    $treeImages.='<div class="pg pg_'.$icon.'"></div>';
                }
	        }

            if($smalltree===false){
                // Перемещение
	            $mv=page::existBrothers($key,$value['folder'],$value['parent'],$value['lang']);
	            if($mv==1 || $mv==3) $moveactions.='<i class="ic-up" onClick="pa(\'mu\','.$value['id'].')"></i>';
	            if($mv==2 || $mv==3) $moveactions.='<i class="ic-down" onClick="pa(\'md\','.$value['id'].')"></i>';
            }

	        $icon='ic-file-empty';
	        $lang='';
	        $styleSuffix='';
            if($value['parent']==0) {
                $icon='ic-file-text2';
                $lang='<span class="lang">'.$value['lang'].'</span>';
                $styleSuffix='b';
            }
            if($value['hidden']==1) $icon='ic-lock2';
            if($value['lastmod']==1) $icon='ic-red '.$icon;


            // Публикация по расписанию
            $cron='';
            if($value['starttime']!=0 || $value['stoptime']!=0){
                $cron='<i class="ic-alarm-2" title="';
                if($value['starttime']!=0) $cron.=MESSAGE('pages','MESS_publish').': '.date("d.m.Y H:i",$value['starttime']);
                if($value['stoptime']!=0) $cron.=MESSAGE('pages','MESS_unpublish').': '.date("d.m.Y H:i",$value['stoptime']);
                $cron.='"></i>';
            }
            // Обрезание слишком длинных названий раздела
            $pagename=$value['pagename'];
            $pgncomment='';
            if(mb_strlen($value['pagename'],'utf-8')>48) {
                $pagename=trim(mb_substr($value['pagename'],0,48,'utf-8')).'&hellip;';
                $pgncomment=' title="'.$value['pagename'].'"';
            }

            if($smalltree===false) $tree.='<tr id="r'.$value['id'].'"><td class="pgSel" data-id="'.$value['id'].'"></td><td>'.$treeImages.'<i class="'.$icon.'"></i><span class="pn'.$styleSuffix.'" onClick="pa(\'pe\','.$value['id'].')"'.$pgncomment.'>'.$pagename.'</span><div id="p'.$value['id'].'" class="mbtn" onClick="pms('.$value['id'].')"></div></td><td class="ac">'.$cron.'</td><td>'.$lang.'</td><td class="cnt">'.$moveactions.'</td><td class="dt">'.date("d.m.Y H:i",$value['modify']).'</td><td class="dt2" id="pu'.$value['id'].'" onClick="pa(\'pv\','.$value['id'].')">'.$value['url'].'</td><td></td></tr>';
            else $tree.='<tr><td></td><td>'.$treeImages.'<i class="'.$icon.'"></i><span class="pn'.$styleSuffix.'" onClick="pa(\'pe\','.$value['id'].')"'.$pgncomment.'>'.$pagename.'</span></td></tr>';
            $childs=self::outTree($value['id'], $folder, $smalltree);
            if($childs!='') $tree.=$childs;
        }
    }
  return $tree;
  }

  // Расстановка глубины веток
  // На этом шаге идет только проход по дереву и расстановка глубины
  // для каждого элемента массива $val['deep']
  static function deepTree(){
        global $catarr;
        foreach($catarr as $key=>$value){
            $path=page::getPath($value['id']);
            $catarr[$key]['url']=$path;
            $deep=(count(explode('/',$path))-2);
            $catarr[$key]['deep']=$deep;
            $pel=explode('/',$path);
            // Если первый элемент массива длиной 2 символа, то это язык. Следовательно, глубину скорректируем
            if(strlen($pel[0])==2) $catarr[$key]['deep']--;
            if($catarr[$key]['deep']<0) $catarr[$key]['deep']=0;
        }
  }


//static function treeMaker($cats, $parent_id){
//  if(is_array($cats) and count($cats[$parent_id])>0){
//    $tree = '<ul>';
//    foreach($cats[$parent_id] as $cat){
//       $tree .= '<li>'.$cat['name'];
//       $tree .=  self::treeMaker($cats,$cat['id']);
//       $tree .= '</li>';
//    }
//    $tree .= '</ul>';
//  }
//  else return null;
//  return $tree;
//}

    // Сохранение свойств папки
    static function folderSave(){
        global $settings,$pg;
        $folder=$_POST['folder'];
        if($folder['id']==0) $action='edit';
        else $action='init';
        if(!preg_match('/^[a-z0-9_]{3,32}+$/', $folder['alias'])){
            $folder['alias']=translit($folder['alias']);
            echo '<div id="splashMessage">'.MESSAGE('pages','MESS_aliasIsTranslit').'</div>';
        }
        if($folder['alias']=='') error(MESSAGE('pages','ERROR_aliasEmpty'));
        else{
            if($folder['default']==1) mysql::query("UPDATE `folders` SET `default`='0' WHERE `default`='1'");
            if($folder['id']==0) $folder['order']=mysql::newOrder('folders');
            if(mysql::getValue("SELECT alias FROM `folders` WHERE id!='".escape($folder['id'])."' AND alias='".escape($folder['alias'])."' LIMIT 1")!=false) error(MESSAGE('pages','ERROR_aliasExist'));
            else {
                if(!mysql::saveArray("folders",$folder)) error(mysql::error());
                if($folder['id']==0){
                    // Сохраняем временную страницу
                    $folder['id']=mysql::insertId();
                    $template=mysql::getValue("SELECT id FROM `templates` ORDER BY `order` ASC LIMIT 1");
				    mysql::query("INSERT INTO `pages` SET folder='".mysql::escape($folder['id'])."', template='".escape($template)."', alias='index', urlhash='".mysql::escape(md5($folder['alias'].'/'))."', lang='".mysql::escape($settings['siteDefaultLang'])."', btname='".MESSAGE('pages','MESS_defaultPageName')."',nosaved='1', created=NOW()");
				    $pageId=mysql::insertId();
				    mysql::query("INSERT INTO `pages_modules` SET page_id='".mysql::escape($pageId)."', module='showText', tmplvar='pageText', pause='0', `comment`='".mysql::escape(MESSAGE('pages','MESS_mdlPageTextComment'))."'");
				    mysql::query("INSERT INTO `pages_text` SET page_id='".mysql::escape($pageId)."'");
                }
            }
        }
        if(error()) {
            echo error();
            $GLOBALS['item']=$folder['id'];
            return self::folderEdit();
        }
        else {
            self::refresh();
            if($action=="init") return self::init();
            else {
                $folderId=mysql::insertId();
                $pg=new page;
                /** @noinspection PhpUndefinedVariableInspection */
                $pg->setParam(array('id'=>$pageId,'folder'=>$folderId));
                $GLOBALS['item']=$pageId;
                return self::pageEdit();
            }
        }
    }

    // Редактирование папки
    static function folderEdit(){
        global $item;
        $folder['id']=$item;
        $folder['alias']='';
        $folder['default']=0;
        $out='';
        $noFolders=false;
        if(!isset($_POST['folder'])){
            if($folder['id']!=0){
                $folder=mysql::getArray("SELECT * FROM `folders` WHERE id='".escape($folder['id'])."' LIMIT 1",true);
            }
            else {
                // Узнаем, есть ли на сайте хотябы одна папка
                if(mysql::getValue("SELECT id FROM `folders` LIMIT 1")==false) {
                    $noFolders=true;
                    $folder['alias']='index';
                }
            }
        }
        else $folder=$_POST['folder'];
        if($folder['default']==1 || $noFolders===true) $checked=' checked="checked" ';
        else $checked='';
        $out.='<h2>'.MESSAGE('pages','folderProps').'</h2>
<form method="POST" id="folderForm" enctype="multipart/form-data">
<input type="hidden" name="action" value="folderSave">
<input type="hidden" name=folder[id] value="'.$folder['id'].'">
<input type="hidden" name=folder[default] value="0">
<div class="row">
    <div id="setBreadCrumbs"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('pages','pgName').'</a></li><li><span>'.MESSAGE('pages','folderProps').'</span></li></ul></div>
</div>
<div class="row"><input id="fdef" class="switch" type="checkbox" name="folder[default]" value="1" '.$checked.'/><label for="fdef">'.MESSAGE('pages','messDefault').'</label>
</div>
<div class="info">'.MESSAGE('pages','folderCreateDesc').'</div>
<div class="field">
      <div class="btn-group">
       <div class="label"><i class="ic-folder"></i>'.MESSAGE('ALIAS').':</div>
       <input type="text" name=folder[alias] class="size-m" maxlength="64" value="'.htmlspecialchars($folder['alias']).'">
       <div class="btn" onClick="ajaxPost(\'folderForm\',\'pages::folderSave\')"><i class="ic-save"></i>'.MESSAGE('BUTTON_save').'</div>
       <div class="btn" onClick="ajaxGet(\'pages::init\',\'cblock\')"><i class="ic-undo"></i>'.MESSAGE('BUTTON_cancel').'</div>
      </div>
</div>
</form>
</div>
';
        return array('cblock'=>$out);
    }

    // Выподающее меню для страницы
    static function showPageMenu(){
        global $item;
        $out='';
        $move='';
        $array=mysql::getArray("SELECT * FROM `pages` WHERE id='".escape($item)."' LIMIT 1",true);
        if($array!=false){
            // Получим все страницы с тем же родителем, чтобы дать возможность перемещения
            $ne=mysql::getArray("SELECT * FROM `pages` WHERE parent='".escape($array['parent'])."' AND parent>0 ORDER BY `order` ASC");
            if($ne!=false){
                foreach($ne AS $key=>$val){
                    if($val['id']==$item){
                        if($key>=1) $move.='<li onClick="pa(\'mu\','.$item.')"><i class="ic-up"></i>'.MESSAGE('LABEL_moveUp').'</li>';
                        if(isset($ne[($key+1)])) $move.='<li onClick="pa(\'md\','.$item.')"><i class="ic-down"></i>'.MESSAGE('LABEL_moveDown').'</li>';
                        break;
                    }
                }
            }
            $out.='<li>'.$array['btname'].'</li><li onClick="pa(\'pv\','.$item.')"><i class="ic-eye"></i>'.MESSAGE('VIEW').'</li><li onClick="pa(\'pvd\','.$item.')"><i class="ic-bug"></i>'.MESSAGE('DEBUG').'</li><li onClick="pa(\'pl\','.$item.')"><i class="';
            if($array['hidden']==1) $out.='ic-unlock"></i>'.MESSAGE('UNLOCK').'</li>';
            else $out.='ic-lock"></i>'.MESSAGE('LOCK').'</li>';
            $out.='<li onClick="pa(\'pe\','.$item.')"><i class="ic-editdoc"></i>'.MESSAGE('EDIT').'</li><li onClick="pa(\'pc\','.$item.')"><i class="ic-dplus"></i>'.MESSAGE('pages','ACTS_pageChild').'</li>'.$move.'
<li onClick="pa(\'pd\','.$item.')"><i class="ic-red ic-delete"></i>'.MESSAGE('DELETE').'</li>';
            }
        $out.='<li onClick="domRemove(\'pageDropMenu\')"><i class="ic-exit"></i>'.MESSAGE('BUTTON_cancel').'</li>';
        return array('ddMenu'=>$out);
    }

    // Всплывающее меню для папки
    static function showFolderMenu(){
        global $item,$settings;
        $acts='';
        $langCount=count(explode(",",$settings['siteLangs']));
        $pageCount=mysql::getValue("SELECT COUNT(*) FROM `pages` WHERE folder='".escape($item)."' AND parent='0'");
        if($pageCount!=$langCount) $acts.='<li onClick="pa(\'fl\','.$item.')"><i class="ic-createfile"></i>'.MESSAGE('pages','MESS_defaultPageName').'</li>';
        $fl=mysql::getArray("SELECT * FROM `folders` ORDER BY `order` ASC");
        foreach($fl AS $key=>$val){
            if($val['id']==$item){
                $folder=$val;
                if($val['default']==0) $acts.='<li onClick="pa(\'fh\','.$folder['id'].')"><i class="ic-home"></i>'.MESSAGE('pages','messDefault').'</li>';
                if(isset($fl[$key-1])) $acts.='<li onClick="pa(\'mfu\','.$val['id'].')"><i class="ic-up"></i>'.MESSAGE('LABEL_moveUp').'</li>';
                if(isset($fl[$key+1])) $acts.='<li onClick="pa(\'mfd\','.$val['id'].')"><i class="ic-down"></i>'.MESSAGE('LABEL_moveDown').'</li>';
                break;
            }
        }
        return array('ddMenu'=>'<li>'.$folder['alias'].'</li>
        <li onClick="pa(\'ef\','.$folder['id'].')"><i class="ic-cogs"></i>'.MESSAGE('pages','folderProps').'</li>'.$acts.'<li onClick="pa(\'fd\','.$folder['id'].')"><i class="ic-red ic-delete"></i>'.MESSAGE('pages','ACTS_folderDelete').'</li><li onclick="domRemove(\'pageDropMenu\')"><i class="ic-exit"></i>'.MESSAGE('BUTTON_cancel').'</li>');
    }

    // Инициализация модуля. Загрузка скриптов, определение шаблона страницы
    // и все прочие настройки
    static function init(){
        global $admin, $settings, $selectMode; // Режим множественного выбора элементов
        /** $selectMode - Режим отображения списка разделов. Если true, то с чекбоксами для множественных операций */

        $out='';
        if(isset($admin->url['pages'][1])){
            if($admin->url['pages'][1]=="selectMode") $selectMode=true;
        }
        if(isset($admin)){
            $admin->addJs($settings['protocol'].$settings['siteUrl'].'/admin/plugins/ckeditor/ckeditor.js');
            $admin->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/Sortable.js');
            $admin->addHeadScript('
var prevExecTime=0;
var contentIsSaved=false;
var scInterval=0;
var lastSavedTime='.time().';
var intIsStarted=false;
var shSaveCounter=false;		
			
	function pa(act,id){
    domRemove("pageDropMenu");
	if (act==\'mfu\') ajaxGet(\'pages::folderMove?=\'+id+\'&direction=up\');
	if (act==\'mfd\') ajaxGet(\'pages::folderMove?=\'+id+\'&direction=down\');
	if (act==\'fd\') dialogConfirm(\''.MESSAGE('pages','delFolder').'\',\'folderDelete(\'+id+\')\');
	if (act==\'pd\') dialogConfirm(\''.MESSAGE('pages','delPage').'\',\'pageDelete(\'+id+\')\');
	if (act==\'pl\') ajaxGet(\'pages::pageLock?=\'+id);
	if (act==\'pc\') {
		window.clearInterval(shSaveCounter);
		var lastSavedTime='.time().';
		ajaxGet(\'pages::pageAddChild?=\'+id);
	}
	if (act==\'pv\') {
        var url=getId(\'pu\'+id).innerHTML;
        if(url=="/") url="'.$settings['protocol'].$settings['siteUrl'].'/";
        else url="'.$settings['protocol'].$settings['siteUrl'].'/"+url;
        window.open(url);
	}
	if (act==\'pvd\'){
	    var url=getId(\'pu\'+id).innerHTML;
	    if(url=="/") url="'.$settings['protocol'].$settings['siteUrl'].'/"+"AXIOMdebug-'.$settings['securityCode'].'/";
	    else url="'.$settings['protocol'].$settings['siteUrl'].'/"+url+"AXIOMdebug-'.$settings['securityCode'].'/";
	    window.open(url);
	}
	if (act==\'np\') setAction(\'pages::addPage&folderId=\'+id);
	if (act==\'mu\') ajaxGet(\'pages::pgMove?=\'+id+\'&direction=up\');
	if (act==\'md\') ajaxGet(\'pages::pgMove?=\'+id+\'&direction=down\');
	if (act==\'pe\') {
	    var lb=\'\';
	    if(leftBlockExpanded!=undefined){
	        if(leftBlockExpanded==true) lb=\'&leftBlock=1\';
	    }
	    ajaxGet(\'pages::pageEdit?=\'+id+lb);
	}
	if (act==\'ef\') ajaxGet(\'pages::folderEdit?=\'+id);
	if (act==\'fl\') ajaxGet(\'pageAddChild?=0&folder=\'+id);
	if (act==\'fh\') ajaxGet(\'pages::folderSetMain?=\'+id);
 	}
function pageDelete(id){
    ajaxGet("pages::pageDelete?="+id);
}
function folderDelete(id){
    ajaxGet(\'pages::folderDelete?=\'+id);
}
function addp(par,fol,lan){
	setAction(\'addPage&parentId=\'+par+\'&folderId=\'+fol+\'&fixedLang=\'+lan+\'\');
}
function edp(id,fld,lan){
	if(lan!=undefined) langlink=\'&fixedLang=\'+lan;
	else langlink=\'\';
	setAction(\'editPage?id=\'+id+\'&folderId=\'+fld+langlink);
}
// Dropdown menu for pages
function pms(id){
    domRemove("pageDropMenu");
	var mn="pageDropMenu";
	domCreate(mn);
	getId(mn).innerHTML=\'<ul id="ddMenu"><li>Пожалуйста, подождите...</li></ul>\';
	ajaxGet(\'pages::showPageMenu?=\'+id);
	getId(mn).className="axiomDropdownMenu";
    getId(mn).style.display="inline-block";
    var coords=getOffset(getId("p"+id));
    var vleft=coords["left"]-10;
    if(vleft<0) vleft=0;
	getId(mn).style.left=vleft+"px";
	getId(mn).style.top=(coords["top"]-12)+"px";
	getId(\'cblock\').onmouseover=pmhide;
}

/* SHORT MODULE MENU */
function mdlMenu(id,name,pause){
    domRemove("pageDropMenu");
	var mn="pageDropMenu";
	domCreate(mn);
	var cont=\'\';
	cont+=\'<li>\'+name+\'</li>\';
	var checked=\'\';
	if(pause==1) { checked=\' checked="checked"\'; }
	cont+=\'<li><input id="mdlLock" type="checkbox"\'+checked+\' style="float:left;" onchange="ajaxGet(\\\'pages::setMdlCheck?=\'+id+\'&var=pause\\\')"><label for="mdlLock">Заблокировать</label></li>\';
	if(name!=\'showText\' && name!=\'showTextBlock\'){
	    name=str_replace(":","/",name);
	    cont+=\'<li><a target="_blank" href="\'+document.location.protocol+\'//\'+document.location.host+\'/admin/editor.php?file=\'+urlencode(\''.$_SERVER['DOCUMENT_ROOT'].'/_modules/\'+name+\'.php\')+\'"><i class="ic-editdoc"></i>Редактировать</a></li><li onclick="mdlDelete(\'+id+\')"><i class="ic-delete color-red"></i>Удалить</li>\';
	}
	cont+=\'</ul>\';
	getId(mn).innerHTML=cont;
	getId(mn).className="axiomDropdownMenu";
    getId(mn).style.display="inline-block";
    var coords=getOffset(getId("d"+id));
    var vleft=coords["left"];
    if(vleft<0) vleft=0;
	getId(mn).style.left=vleft+13+"px";
	getId(mn).style.top=(coords["top"]-1)+"px";
	getId(\'cblock\').onmouseover=pmhide;
}

function fms(id){
    domRemove("pageDropMenu");
	var mn="pageDropMenu";
	domCreate(mn);
	getId(mn).innerHTML=\'<ul id="ddMenu"><li>...'.MESSAGE('LOADING').'...</li></ul>\';
	ajaxGet(\'pages::showFolderMenu?=\'+id);
	getId(mn).className="axiomDropdownMenu";
    getId(mn).style.display="inline-block";
    var coords=getOffset(getId("f"+id));
    var vleft=coords["left"]-10;
    if(vleft<0) vleft=0;
	getId(mn).style.left=vleft+"px";
	getId(mn).style.top=(coords["top"]-12)+"px";
	getId(\'cblock\').onmouseover=pmhide;
}
function pmhide(){
    domRemove("pageDropMenu");
}
// Add visual editor area
function addEditor(npage){
    var added=false;
    var edCounter=0;
    for(var m=2; m<=5; m++){
        if(added==false){
            if(getId("ckedit"+m)) edCounter++;
            else {
                ajaxGet("pages::addEditor?="+m+"&page="+npage);
                var parentElem = getId("content"+m);
                var newDiv = document.createElement(\'div\');
                newDiv.innerHTML =\'<label class="ckEdLabel"><span>'.MESSAGE('pages','LABEL_txtBlock').' \'+m+\'</span></label><textarea id="ckedit\'+m+\'" name="page[text\'+m+\']" class="axiom" style="width:'.$settings['editorWidth'].'; height: 250px;"></textarea>\';
                parentElem.appendChild(newDiv);
                added=true;
                edCounter++;
                currentDrag=false;
            }
        }
    }
    searchEditors();
    if(edCounter>=5){
        getId("addEditor").style.display="none";
    }
}

var isSaved=false;
function savePage(exit,formid){
    metaSave();// Сохраняем метаданные
	var lastSavedTime='.time().';
    contentIsSaved=true;
    lastSavedTime=time();
    window.clearInterval(shSaveCounter);
    intIsStarted=false;
	getId("saveCounter").innerHTML=\'<i class="ic-white ic-disk" style="margin-top:6px;"></i>\';
    if(formid=="contentForm"){
        var errmess="";
        getId("pgname").style.background="";
        if(getId("pgname").value.length<3){
            errmess+="Заполните поле \'пункт меню\'!\n";
            getId("pgname").style.background="#ffafaf";
        }
        if(errmess!="") dialogAlert(errmess);
        else{
            isSaved=true;
            if(getId("ckedit1")) getId("ckedit1").innerHTML=CKEDITOR.instances.ckedit1.getData();
            if(getId("ckedit2")) getId("ckedit2").innerHTML=CKEDITOR.instances.ckedit2.getData();
            if(getId("ckedit3")) getId("ckedit3").innerHTML=CKEDITOR.instances.ckedit3.getData();
            if(getId("ckedit4")) getId("ckedit4").innerHTML=CKEDITOR.instances.ckedit4.getData();
            if(getId("ckedit5")) getId("ckedit5").innerHTML=CKEDITOR.instances.ckedit5.getData();
            if(exit==true) {
                setValue("exitflag","1");
                ajaxPost("contentForm","pages::pageSave","cblock");
            }
            else {
                ajaxPost("contentForm","pages::pageSave","hiddenblock");
            }
        }
    }
}
// Add keyword to MetaKeywords
function addKeyw(keyw){
    var v=getId("metakey").value;
    if(v=="") getId("metakey").value+=keyw;
    else getId("metakey").value+=\', \'+keyw;
    fieldAutosave("metakey","metaSave()",2);
    getId("delimitermetakey").innerHTML=getId("metakey").value.length;
}

// AJAX при смене значения селект
function changeSelect(selid,pageId){
    var v=getSelectValue(selid);
    ajaxGet(\'pages::updateDBValue?=\'+selid+\'&pageId=\'+pageId+\'&value=\'+v);
}
// Модуль: Смена переменной шаблона
function changeMdlTmp(mdlId){
    var v=getSelectValue("md"+mdlId);
    getId("mdlv"+mdlId).innerHTML=v;
    ajaxGet(\'pages::updateMdlVar?=\'+mdlId+\'&value=\'+encodeURIComponent(v));
}
// AJAX при смене значения чекбокса
function changeCheckbox(checkbx,pageId){
    var v=getId(checkbx).checked;
    if(v===true) v=1;
    else v=0;
    ajaxGet(\'pages::updateDBValue?=\'+checkbx+\'&pageId=\'+pageId+\'&value=\'+v);
}
function saveCanonical(pageId){
    var v=urlencode(getId("relcanonical").value);
    ajaxGet(\'pages::saveCanonical?=\'+pageId+\'&canonical=\'+v);
}

function mdlDelete(module,submit){
    if(submit==undefined){
        dialogConfirm("'.MESSAGE('pages','MESS_mdlOff').'","mdlDelete("+module+",true)");
    }
    else {
         domRemove("d"+module);
         ajaxGet(\'pages::moduleDelete?=\'+module);
    }
}

var selectedElements=[];
var cbSelShow=false;
function renewSelect(){
    if(cbSelShow===false) { cbSelShow=true; }
    else { cbSelShow=false; }
    selectedElements=[];
    var m=document.getElementsByClassName("pgSel");
    for (var i = 0; i < m.length; i++) {
        var id=m[i].getAttribute("data-id");
        if(cbSelShow===false) {
            m[i].innerHTML = "";
            getId("r"+id).style.background="transparent";
            getId("selectActions").innerHTML=\'<div class="btn-disabled"><i class="ic-menu"></i></div>\';
        }
        else {
            m[i].innerHTML = \'<input id="c\'+id+\'" type="checkbox" onClick="ps(\'+id+\')" />\';
        }
	}
}
// Множественный выбор
function ps(id){
    var sel=getCheckboxValue("c"+id);
    if(sel==1) {
        getId("r"+id).style.background="#ccddff";
        selectedElements[id]=id;
    }
    else {
        getId("r"+id).style.background="#ffffff";
        delete selectedElements[id];
    }
    var sp=implode(",",selectedElements);
    if(sp!="") getId("selectActions").innerHTML="<div class=\'btn\' id=\'groupActions\' onClick=\'showMPActs()\' title=\''.MESSAGE('SELACTIONS').'\'><i class=\'ic-menu\' style=\'margin-right:0\'></i></div>";
    else getId("selectActions").innerHTML=\'<div class="btn-disabled"><i class="ic-menu"></i></div>\';
}

function showMPActs(){
	var mn="pageDropMenu";
	domRemove(mn);
	domCreate(mn);
	getId(mn).innerHTML=\'<ul id="ddMenu"><li>'.MESSAGE('pages','ACTS_elementsActions').'</li><li onClick="groupAct(\\\'lock\\\')"><i class="ic-lock"></i>'.MESSAGE('LOCK').'</li><li onClick="groupAct(\\\'unlock\\\')"><i class="ic-unlock"></i>'.MESSAGE('UNLOCK').'</li><li onClick="groupAct(\\\'temp\\\')"><i class="ic-border-all"></i>'.MESSAGE('pages','setHTMLtemplate').'</li><li onClick="groupAct(\\\'delete\\\')"><i class="ic-red ic-delete"></i>'.MESSAGE('pages','ACTS_delSelectedPages').'</li><li onclick="domRemove(\\\'pageDropMenu\\\')"><i class="ic-exit"></i>Отмена</li></ul>\';
	getId(mn).className="axiomDropdownMenu";
    getId(mn).style.display="inline-block";
    var coords=getOffset(getId("groupActions"));
    var vleft=coords["left"];
    if(vleft<0) vleft=0;
	getId(mn).style.left=vleft+"px";
	getId(mn).style.top=(coords["top"])+"px";
	getId(\'cblock\').onmouseover=pmhide;
}



function groupAct(act){
    domRemove("pageDropMenu");
    var sp=implode(",",selectedElements);
    if(act=="lock") ajaxGet(\'pagesLock?=\'+urlencode(sp)+\'&lock=1\');
    if(act=="unlock") ajaxGet(\'pagesLock?=\'+urlencode(sp)+\'&lock=0\');
    if(act=="delete") {
        var act=\'ajaxGet("pagesDelete?=\'+urlencode(sp)+\'")\';
        dialogConfirm("'.MESSAGE('pages','CONF_pagesDelete').'",act);
    }
}

function multiselectOff(){
    selectedElements=[];
    //ajaxGet(\'init\');
}
function pgAutotranslit(translit){
    if(translit==1) autotranslit(\'pgname\',\'pgalias\');
    autocopy(\'pageName\',\'pageTitle\');
}
function metaSave(){
    ajaxPost("metaForm","pages::metaSave","hiddenblock");
}
function showSnippets(){
    if(getId("snippets").innerHTML==""){
        ajaxGet("tools::showSnippetList?=");
    }
}
');
    }

    $selectBtn='<div class="btn" onClick="renewSelect()" title="'.MESSAGE('MULTISELECT').'"><i class="ic-boxes" style="margin-right:0"></i></div>';
    $buttons='<div class="row" id="btnfield">
<div class="btn" onClick="ajaxGet(\'pages::folderEdit?=0\')"><i class="ic-folder-plus"> </i>'.MESSAGE('pages','BUTTON_newFolder').'</div>
<div class="btn" onClick="ajaxGet(\'pages::refresh?show=1\')"><i class="ic-tree"></i>'.MESSAGE('pages','BUTTON_renew').'</div>
    <!--div class="btn-group">
        '.$selectBtn.'
        <div id="selectActions"><div class="btn-disabled"><i class="ic-menu"></i></div></div>
    </div -->
</div>';
    $pages=mysql::getValue('SELECT value FROM `settings` WHERE name=\'pages\' LIMIT 1');
    if($pages=='') {
        // Проверим, существует ли хоть один шаблон
        if(mysql::getValue("SELECT id FROM `templates` LIMIT 1")!=false) $pages='<div class="info">'.MESSAGE('pages','MESS_pgListNoExist').'</div>';
        else {
            $pages='<div class="info">'.MESSAGE('pages','MESS_templatesNoExist').'</div>';
            $buttons='';
        }
    }
    // Если включен SELECT MODE, то надо вставить чекбоксы
    if(isset($selectMode)){
        $pages=preg_replace('#<!--(.*?)-->#', '<input id="c$1" type="checkbox" onClick="ps($1)" />', $pages);
    }
    $buttons='<h2>Разделы сайта</h2><div class="row" id="setBreadCrumbs"><ul class="breadCrumbs"><li><span>'.MESSAGE('pages','pgName').'</span></li></ul></div>'.$buttons;
    $out.='<div class="row" id="AXplist">'.$pages.'</div>';
    $out='<div id="AXpglist" style="display:block; float:none; clear:both;">'.$buttons.$out.'</div>';
    if(isset($admin)) return $out;
    else return array('cblock'=>$out);
    }
}