<?php
// Универсальный класс для работы с данными.
// Симбиоз EAV & Flat tables
// -----------------------------------------
// 04-07-2018 UPDATE    Исправлены ошибки с отображением миниатюр в форме админки в зависимости от выбранного типа
// 04-07-2018 UPDATE    Исправлены проблемы с загрузкой PNG изображений с альфа-каналом
// 04-07-2018 UPDATE    Для вставки изображений через плейсхолдеры теперь используется не ID файла, а его порядковый номер в пределах родителя
// 11-01-2018 UPDATE    Исправлена ошибка с сохранением карты GoogleMap
// 29-12-2017 UPDATE    Просмотр миниатюр через iLoad
//                      Редактирование свойств изображения в окне
// 10-12-2016 UPDATE	Календарь (date) теперь может содержать пустое значение
///////////////////////////////////////////////////////////////////////////////
class data{

    static $fieldTypes=array(
        'checkbox'=>array( 'dbType'=>'ENUM','dbSize'=>"('0','1')",'dbNull'=>'NULL','dbDefault'=>'DEFAULT \'0\'','dbDop'=>''),
        'varchar'=>array('dbType'=>'VARCHAR','dbSize'=>'(255)','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'\'','dbDop'=>''),
        'decimal'=>array('dbType'=>'DECIMAL','dbSize'=>'(18,2)','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'0.0\'','dbDop'=>''),
        'int'=>array('dbType'=>'BIGINT','dbSize'=>'(20)','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'0\'','dbDop'=>'',),
        'child'=>array('dbType'=>'BIGINT','dbSize'=>'(20)','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'0\'','dbDop'=>'',),
        'text'=>array('dbType'=>'TEXT','dbSize'=>'','dbNull'=>'NULL','dbDefault'=>''), // Поле TEXT в MySQL не может иметь значение  по-умолчанию!!!!!!'dbDop'=>'',),
        'textarea'=>array('dbType'=>'TEXT','dbSize'=>'','dbNull'=>'NULL','dbDefault'=>''),
        'enum'=>array('dbType'=>'ENUM','dbSize'=>'("0","1")','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'0\'','dbDop'=>''),
        'date'=>array('dbType'=>'DATE','dbNull'=>'NULL','dbDefault'=>'NULL','dbDop'=>''),
        'dateandtime'=>array('dbType'=>'DATETIME','dbNull'=>'NULL','dbDefault'=>'NULL','dbDop'=>''),
        'file'=>array('dbType'=>'VARCHAR','dbSize'=>'(64)','dbNull'=>'NULL','dbDefault'=>'DEFAULT \'\'','dbDop'=>'')
    );
    
    // Сохранение данных при быстром редактировании
    static function saveQuickEdit(){
    	global $item, $attrId, $value;
    	$value=urldecode($value);
    	// Получим ве значения аттрибута и одновременно алиас сущности, для того чтобы понять, в какую таблицу надо записывать данные
	    $attr=mysql::getArray("SELECT * FROM `attributes` WHERE id=".escape($attrId)." LIMIT 1",true);
	    $entype=mysql::getValue("SELECT t2.entity_type_alias
	    FROM `entity` AS t1
	        JOIN `entity_type` AS t2 ON t2.id=t1.entity_type
	        WHERE t1.id=".escape($item)." LIMIT 1");
	    if($attr!=false && $entype!=false){
	    	if($attr['multiple']==0){
	    		// Одиночные значения, хрантмые в основной таблице сущности
	    		if($attr['type']=='decimal' || $attr['type']=='int' || $attr['type']=='varchar'){
	    		    mysql::query("UPDATE `data_".$entype."` SET `".$attr['alias']."`=".trim($value)." WHERE id=".escape($item)." LIMIT 1");
	    		    ajax::message('Изменения сохранены.');
			    }
		    }
	    }
    	return false;
    }
    
    // Сохраняем порядок опций в фильтре
    static function filterOrderSave(){
    	global $item;
    	if(isset($_REQUEST['dragStatus'])){
		    mysql::query("UPDATE `entity_type` SET filter_order='".escape(trim($_REQUEST['dragStatus']))."' WHERE id=".escape($item)." LIMIT 1");
	    }
    	return false;
    }
    
    
    static function filterOrder(){
    	global $settings, $item;
    	$out='';
    	
    	// Получим данные инфоблока и порядок опций фильтра, если таковой имеется
    	$o=mysql::getArray("SELECT entity_type AS type, entity_type_alias AS typeAlias,icon,filter_order FROM `entity_type` WHERE id=".escape($item)." LIMIT 1", true);
    	$array=mysql::getArray("SELECT id,alias,name,icon,filter FROM `attributes`
    	WHERE entity_type_id=".escape($item)." AND filter='1' ORDER BY `backend_order` ASC");
    	
    	$or=explode(',',$o['filter_order']);
    	// Проверим, какие элементы не упорядочены
	    
	    $sort=true;
	    $narr=array();
	    if(count($array)!=count($or)){
	    	$sort=false;
	    }
	    $sorted=array();
	    foreach($or AS $val){
	    	foreach($array AS $v){
	    		if($v['id']==$val){
	    			$narr[]=$v;
	    			$sorted[]=$v['id'];
			    }
		    }
	    }
	    
	    // Добавляем элементы массива, которые еще не сортированы
	    foreach($array AS $v){
		    if(!in_array($v['id'],$sorted)){
			    $narr[]=$v;
		    }
	    }
	    
	    $options='';
	    foreach($narr AS $val){
			$icon='';
			$options.='<tr id="f'.$val['id'].'">
			    <td style="width:24px;" class="drag"><i class="ic ic-move-v"></i></td>
			    <td class="small drag">'.$val['id'].'</td>
			    <td class="drag">'.$icon.'</td>
                <td class="drag"><b>'.$val['name'].'</b></td>
                <td></td>
                </tr>';
	    }
	    
    	
    	
    	// Показываем список опций фильтра для заданного инфоблока
    	if($array!=false){
		    $out.='<h3>'.$o['type'].'</h3>';
		    
		    if($sort===false) {
			    $out .= '<div class="row">
		            <div class="error">ВНИМАНИЕ! Параметры фильтра упорядочены по умолчанию. Для смены порядка отсортируйте параметры вручную.</div>
		        </div>';
		    }
		    
		    $out.='<div class="row">
		        <div class="btn" onclick="ajaxGet(\'data::settings?=0&edited='.$item.'\')"><i class="ic-return"></i>Вернуться к атрибутам инфоблока</div>
		    </div>
		    <table id="elFields" class="cmstable4" onmouseover="dragTableInit(this.id,\'data::filterOrderSave?='.$item.'\')">
    	<tr class="nodrag nodrop"><th style="width:24px;"><i class="ic-mouse"></i></th><th style="width:24px;">ID</th><th style="width:24px;">&nbsp;</th><th>Поле</th><th style="width:60px;">Действия</th></tr>';
		    $out.=$options;
    		$out.='</table>';
	    }
    	return array(
    		'right'=>$out
	    );
    }

    // Окно настроек компилятора
    static function startCompiler(){
        global $item;
        $comp=array(
            'id'=>0,
            'name'=>'',
            'alias'=>'',
            'description'=>''
        );
        $out='
        <form method="POST" id="cmplForm" style="width:100%;">
            <input type="hidden" name="comp[id]" value="'.$comp['id'].'">
            <table class="formtable" style="width:100%;">
                <tr><td><label>Название</label></td><td><input type="text" name="comp[name]" maxlength="64" id="cmpName" value="'.$comp['name'].'"></td></tr>
                <tr><td><label>Alias</label></td><td><input type="text" name="comp[alias]"  value="'.$comp['alias'].'" maxlength="64" id="cmpName"></td></tr>
                <tr><td><label>Краткое описание</label></td><td>
                <textarea style="height:80px;" name="comp[description]">'.$comp['description'].'</textarea>
                </td></tr>
                <tr><td><label>Иконка</label></td><td id="iconSelector"><div class="btn-group"><input type="text" name="comp[icon]" id="iconSelField" class="size-m" value="'.$array['icon'].'"><div class="btn" onclick="iconWindow(previousSibling.id)" title="Выбрать иконку"><i class="'.$array['icon'].'"></i></div></div></td></tr>
            </table>
        </form>';

        ajax::window('<div data-close="closeSelectEditor('.$atrId.')" style="width:480px; height:340px; overflow:hidden;"><h2>Компиляция</h2>
        <p>При компиляции из выбранной папки и ее содержимого будет создан отдельный компонент, представленный виджетом на рабочем столе.</p>'.$out.'</div>',true,'cmpWindow');
        return false;
    }

    // Копирование данных в таблицу кэша
    static function entityCacheUpdate($id){
        $cacheFields=explode(',','id,art,exist,price,action,discount,hot,rek,rating,votes');
        // Получим основные данные сущности
	    $tableName=mysql::getValue("SELECT
	    t2.entity_type_alias
	        FROM `entity` AS t1
	            JOIN `entity_type` AS t2 ON t2.id=t1.entity_type
	            WHERE t1.id=".escape($id)." LIMIT 1");
	    
	    if($tableName!==false){
	    	// Получаем все данные сущности
	    	$array=mysql::getArray("SELECT * FROM `entity` AS t1 JOIN `data_".$tableName."` AS t2 ON t2.id=t1.id WHERE t1.id=".$id." LIMIT 1",true);
		    $errFields=array();
		    $insertFields=array();
		    $insertValues=array();
		    $updateFields=array();
		    
		    foreach($cacheFields AS $val){
			    if(!isset($array[$val])) $errFields[]=$val;
			    else {
				    $insertFields[]=$val;
				    $insertValues[]=$array[$val];
				    if($val!='id'){
					    if($val=='price' || $val=='discount') $updateFields[]="`".$val."`=".$array[$val];
					    else $updateFields[]="`".$val."`='".$array[$val]."'";
				    }
			    }
		    }
		
		    if(empty($errFields)){
			    $insertFields[]='photo';
			    $photo=mysql::getValue("SELECT photo FROM `data_".$tableName."` WHERE id=".$array['id']." LIMIT 1");
			    $insertValues[]=$photo;
			    $updateFields[]="`photo`='".$photo."'";
			    mysql::query("INSERT INTO `entity_cache` (`".implode("`,`",$insertFields)."`) VALUES ('".implode("','",$insertValues)."') ON DUPLICATE KEY UPDATE ".implode(", ",$updateFields));
			    return true;
		    }
		    else {
		    	if($tableName!='category'){
				    ajax::consoleError('data::entityCacheUpdate error: fields '.implode(", ",$errFields));
				    return false;
			    }
			    else return true;
		    }
	    }
	    return false;
    }

    // Получение данных папки
    static function getFolderData($cat){
        return mysql::getArray("SELECT e1.id, e1.parent_id, e1.name, e1.entity_type, e1.hidden, e1.owner,
            e2.parent_id AS parent_parent_id, e2.name AS parent_name, e2.entity_type AS parent_entity_type, e2.hidden AS parent_hidden, e2.owner AS parent_owner, e3.backend_select AS param, e3.buffer, e3.icon, e4.*
            FROM `entity` AS `e1`
             JOIN `entity` AS `e2` ON e2.id=e1.parent_id
             JOIN `entity_type` AS `e3` ON e3.id=e1.entity_type
             JOIN `data_category` AS `e4` ON e4.id=e1.id
            WHERE
                e1.id='".escape($cat)."' LIMIT 1", true);
    }

    // Получение списка товаров в заданной папке
    // $whereArray - Дополнительные условия для выборки
    // $igonredFields - Поля, которые не надо получать
    static function itemList($cat, $p, $folder, $moduleAlias, $itemsOrder, $contentDiv, $whereArray=false, $ignoredFields=false ){
        global $ppSelector, $paginator, $itemCounter, $perPage, $changeOrder;
        $out='';
        // Получаем аттрибуты товара
        $entityAttr=mysql::getArray("SELECT `backend_select`,`copy`,`delete`,`buffer`,`icon` FROM `entity_type` WHERE id='".escape($folder['child_entity_type'])."' LIMIT 1",true);
        $param=unserialize($entityAttr['backend_select']);
        unset($entityAttr['backend_select']);
        if($param!=false){
            // Если передан массив полей, которые не надо отображать в списке, то пометим их
            if($ignoredFields!=false && isset($ignoredFields[0])){
                foreach($ignoredFields AS $val){
                    if(isset($param['attr'][$val])){
                        $param['attr'][$val]['backend_list']=0;
                    }
                }
            }

            // Количество элементов на странице
            $perPage=30;
            $ppVar=$moduleAlias.':itemspp';
            if(isset($_SESSION[$ppVar])) $perPage=onlyDigit($_SESSION[$ppVar]);
            if(isset($_REQUEST['perPage'])) $perPage=onlyDigit($_REQUEST['perPage']);
            if(isset($_REQUEST['perPage'])){ $p=0; $perPage=onlyDigit($perPage); $_SESSION[$ppVar]=$perPage; }
            $ppSelector=perPage($perPage,'<span onClick="ajaxGet(\''.$moduleAlias.'::items?=&perPage=%1&cat='.$cat.'\')">%1</span>','На странице: ','20,30,40,50,100');

            // Допустимые варинаты сортировки таблицы
            $orders=$param['orders'];
            $order=$itemsOrder;
            $manualOrderKey=false;// Ключ, соответсвующий ручной сортировке
            $dragTableName='';// Имя таблицы для сохранения значений ORDER
            // Проверим, есть ли поле типа ORDER для ручной сортировки,
            // и если есть, то включим ее как сортировку по-умолчанию
            foreach($param['attr'] AS $val){
                if($val['element']=='order'){
                    foreach($orders AS $k=>$v){
                        if(strpos($v,'`'.$val['alias'].'` ASC')){
                            $manualOrderKey=$k;
                            $dragTableName=$val['dbTable'];
                            break;
                        }
                    }
                    break;
                }
            }
            if($manualOrderKey!=false) $order=$manualOrderKey;
            $orderVar=$moduleAlias.':itemsorder'.$folder['child_entity_type'];
            if(isset($_SESSION[$orderVar])) $order=$_SESSION[$orderVar];
            if(isset($changeOrder)) {
                $p=0;
                if(onlyDigit($order)!=onlyDigit($changeOrder)) $changeOrder=onlyDigit($changeOrder).'-a';
                $_SESSION[$orderVar]=$changeOrder;
                $order=$changeOrder;
            }
            else $changeOrder=false;
            if(!isset($orders[$order])) { $z=array_keys($orders); $ord=$orders[$z[0]]; }
            else $ord=$orders[$order];
            if($ord=='') $ord='`t1`.`id` ASC';

            $param['where'][]='`t2`.`parent_id`='.escape($cat);
            // Добавляем условия из фильтра
            if(isset($_SESSION['filter'][$folder['child_entity_type']])){
                foreach($_SESSION['filter'][$folder['child_entity_type']] AS $key=>$val){
                    $param['where'][]=$key."=".$val;
                }
            }

            // Условия для поиска
            if($whereArray!=false)$param['where'][]=$whereArray;
            $query="SELECT SQL_CALC_FOUND_ROWS ".implode(',', $param['select'])." FROM ".implode(' JOIN ', $param['from'])." WHERE ".implode(' AND ', $param['where'])." ORDER BY ".$ord." LIMIT ".($p*$perPage).",".$perPage;
            $array=mysql::getArray($query);
            $count=mysql::foundRows();
            $itemCounter=$count;
            if($array===false && $p>0){
                $p=($count/$perPage)-1;
                if($p<0) $p=0;
                $array=mysql::getArray($query);
                $count=mysql::foundRows();
            }

            // Формируем заголовки полей таблицы
            if($array!=false){
                $itemsFound=true;
                if($count>$perPage) $paginator=paginate($count,$p,'<span onClick="ajaxGet(\''.$moduleAlias.'::items?=&cat='.$cat.'&p=%1\')">%2</span>',$perPage,3,'Страницы');
                $dragHeader=false;
                $dragField=false;
                if($order==$manualOrderKey){
                    $dragHeader='<i class="ic-mouse"></i>';
                    $dragField='<td><i class="ic-move-v"></i></td>';
                }


                // Отображаем заголовки таблицы
                $out.='<div class="row">
                    <table class="cmstable4" onmouseover="dragInit(\'t_'.$moduleAlias.'_item\',\'data::orderSave?='.$dragTableName.'&entityTypeId='.$folder['child_entity_type'].'&cat=\'+openedCat)"><thead><tr class="nodrag nodrop"><th></th>'.self::adminTableHeaders($moduleAlias, $param['attr'], false, $param['orderRel'], $order, 'items?=&cat='.$cat, $contentDiv, $dragHeader);

                // В зависимости от допустимых действий увеличим ширину поля "действия"
                $fSize=60; if($entityAttr['copy']==1) $fSize+=20; if($entityAttr['buffer']==1) $fSize+=20; if($entityAttr['delete']!=0) $fSize+=20;

                $out.='<th style="width:'.$fSize.'px !important;">Действия</th></tr></thead><tbody id="t_'.$moduleAlias.'_item">';
                $lastSavedId=0;
                $scrollIntoView='';

                if(isset($_SESSION['lastSavedId'])){
                    $lastSavedId=$_SESSION['lastSavedId'];
                    $_SESSION['lastSavedId']=9999999;
                }
                foreach($array AS $key=>$val){
                    // Подсветка самого последнего сохраненного элемента
                    if($val['id']==$lastSavedId) { $class=' yellow'; $scrollIntoView=true; }
                    else $class='';
                    if($dragField!==false) $class='drag'.$class;
                    $out.='<tr class="'.$class.'" id="ct'.$val['id'].'"><td><i class="'.$entityAttr['icon'].'"></i></td>';
                    // Служебные поля
                    foreach($val AS $v=>$k) $out.=self::adminTableField($moduleAlias, false, $val, $param, $v, '<b class="hand" onClick="ajaxGet(\'data::edit?='.$val['id'].'&mn='.$moduleAlias.'&contentDiv=right&entity='.$val['entity_type'].'&cat='.$val['parent_id'].'&p='.$p.'\')">'.$val['name'].'</b>',$dragField);
                    $out.='<td><i class="ic-editdoc" title="Редактировать" onClick="ajaxGet(\'data::edit?='.$val['id'].'&mn='.$moduleAlias.'&contentDiv=right&entity='.$val['entity_type'].'&cat='.$val['parent_id'].'&p='.$p.'\')"></i>';
                    if($entityAttr['copy']==1) $out.='<i class="ic-copy" title="Создать копию" onClick="itemCopy('.$val['id'].','.$val['entity_type'].','.$val['parent_id'].')"></i>';
                    if($entityAttr['buffer']==1) $out.='<i class="ic-clipboard" title="Копировать в буфер" onClick="ajaxGet(\'data::toBuffer?='.$val['id'].'&type='.$val['entity_type'].'&parent='.$val['parent_id'].'&mn='.$moduleAlias.'\')"></i>';
                    if($entityAttr['delete']!=0){
                        if($entityAttr['delete']==1) $dlConfirm='';
                        else $dlConfirm=',1';
                        $out.='<i class="ic-delete" title="Удалить" onClick="itemDel('.$val['id'].','.$cat.','.$p.$dlConfirm.')"></i>';
                    }
                    $out.='</td></tr>';
                }
                $out.='</tbody></table></div>';
                if($scrollIntoView===true){
                    ajax::javascript('getId("ct'.$lastSavedId.'").scrollIntoView(true)');
                }
            }
        }
        else $out='<div class="error">Не удалось получить аттрибуты группы товаров!</div>'.$out;
        return $out;
    }



    // Возвращает вложенные папки внутри заданной категории
    static function catList($folder, $cat,$moduleAlias, $moduleName, $categoryParentId){
        global $catList;

        // Получаем аттрибуты категории и список всех ее полей
        $param=unserialize($folder['param']);

        if($param!=false){
            // Получаем список вложенных папок
            $param['where'][]='t2.`parent_id`='.escape($cat);
            $param['order']='t1.`order` ASC';

            $array=mysql::getArray("SELECT ".implode(',', $param['select'])." FROM ".implode(' JOIN ', $param['from'])." WHERE ".implode(' AND ', $param['where'])." ORDER BY ".$param['order']." LIMIT 5000");
            // Формируем заголовки полей таблицы
            if($array!=false){
                $catsFound=true;
                $orderField=false;
                // Узнаем, присутствует ли поле типа ORDER для сортировки элементов
                foreach($param['attr'] AS $p=>$v) {
                    if($v['element']=='order') {
                        if($v['entity_type_id']==0) $orderField=$moduleAlias.'_entity';
                        else $orderField=$moduleAlias.'_data_'.$v['entity_type_alias'].'.'.$v['alias'];
                        break;
                    }
                }

                // Если разрешена ручная сортировка
                if($orderField!=false) $catList.='<table id="t_'.$moduleAlias.'_cats" class="cmstable4" onmouseover="dragTableInit(this.id,\'data::orderSave?=data_category&entityTypeId=7&cat='.$cat.'\')"><thead><tr class="nodrag nodrop"><th style="width:24px;" title="Ручная сортировка"><i class="ic-mouse"></i></th><th></th>';
                else $catList.='<table class="cmstable4"><tr class="nodrag nodrop"><th></th>';
                $catList.=self::adminTableHeaders($moduleAlias, $param['attr'], true);
                $catList.='<th style="width:90px!important;">Действия</th></tr></thead><tbody>';
                $lastSavedId=0;
                $scrollIntoView='';
                if(isset($_SESSION['lastSavedId'])) $lastSavedId=$_SESSION['lastSavedId'];
                foreach($array AS $key=>$val){
                    if($val['hidden']==1) $folderIcon='ic-dirlock';
                    else $folderIcon='ic-folder';
                    // Подсветка самого последнего сохраненного элемента
                    if($val['id']==$lastSavedId) {
                        $class=' yellow';
                        $scrollIntoView=true;
                    }
                    else $class='';
                    $catList.='<tr class="'.$class.'" id="ct'.$val['id'].'">';
                    if($orderField!=false) $catList.='<td class="drag"><i class="ic-move-v"></i></td>';
                    $catList.='<td style="width:24px;"><i id="fl'.$val['id'].'" class="'.$folderIcon.'"></i></td>';// Служебные поля
                    foreach($val AS $v=>$k) $catList.=self::adminTableField($moduleAlias, true, $val, $param, $v, '<b class="hand" onClick="catOpen(\''.$val['id'].'\')">'.$val['name'].'</b>');
                    $catList.='<td><i class="ic-fldset" title="Свойства папки" onClick="ajaxGet(\'data::edit?='.$val['id'].'&mn='.$moduleAlias.'&contentDiv=right&entity='.$val['entity_type'].'&cat='.$val['parent_id'].'\')"></i>';
                    if($folder['buffer']==1) $catList.='<i class="ic-clipboard" title="Копировать в буфер" onClick="ajaxGet(\'data::toBuffer?='.$val['id'].'&type='.$val['entity_type'].'&parent='.$val['parent_id'].'&mn='.$moduleAlias.'\')"></i>';
                    if($_SESSION['user']['group']<=1) $catList.='<i class="ic-delete" title="Удалить" onClick="catDel('.$val['id'].')"></i>';
                    $catList.='</td></tr>';
                }
                $catList.='</tbody></table>';
                //if($scrollIntoView===true && ((time()-$_SESSION['lastSavedTime'])<5)) $out.='<div id="addScript">getId("ct'.$lastSavedId.'").scrollIntoView(true);</div>';
            }
        }
        return $catList;
    }

    // Применение фильтра
    static function filterApply(){
        if(isset($_POST['filter'])){
            foreach($_POST['filter'] AS $key=>$val){
                // Если значение равно нулю, то сбросим текущий фильтр
                if($val==0){
                    unset($_SESSION['filter'][$_POST['item']][$key]);
                    if(empty($_SESSION['filter'][$_POST['item']])){
                        unset($_SESSION['filter'][$_POST['item']]);
                    }
                }
                else {
                    $_SESSION['filter'][$_POST['item']][$key]=$val;
                }
                if(empty($_SESSION['filter'][$_POST['item']])){
                    unset($_SESSION['filter'][$_POST['item']]);
                }
            }
        }
        $category=$_POST['category'];
        if($category){
            ajax::javascript('catOpen('.$category.')');
        }
        return false;
    }

    // Удаление фильтра из сессии
    static function filterRemove(){
        global $item, $cat;
        unset($_SESSION['filter'][$item]);
        ajax::javascript('catOpen('.$cat.')');
        return false;
    }

    // Отображение фильтра в админ-панели
    static function showFilter($type=false,$ren=false, $cat=false){
        global $item, $renew, $category;
        $filterLiveTime=3600;// Время актуальности фильтра в секундах
        $isArray=true;
        $filter=array();
        if($type!==false){
            $item=$type;
            $renew=$ren;
            $isArray=false;
            $category=$cat;
        }

        $renew=true;/* !!!!!!!!! ПРИНУДИТЕЛЬНОЕ ОБНОВЛЕНИЕ  */
        unlink($_SERVER['DOCUMENT_ROOT'].'/_filter/'.$item.'.acache');

        $out='';
        if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/_filter/'.$item.'.acache')) $renew=true;
        else {
            $filter=file($_SERVER['DOCUMENT_ROOT'].'/_filter/'.$item.'.acache');
            $filter=unserialize(implode('',$filter));
        }


        if($renew===true || time()>($filter['createTime']+$filterLiveTime)){
            //ajax::consoleLog('renew');
            $filter=array(
                'createTime'    =>time(),
                'filterFields'  =>array()
            );
            // Получим аттрибуты заданного типа сущности
            $param=mysql::getArray("SELECT id,entity_type, entity_type_alias, backend_list, onsave,`copy`,`delete`,`buffer`,icon FROM `entity_type` WHERE id=".escape($item)." LIMIT 1",true);
            $prop=unserialize($param['backend_list']);
            // Формируем фильтр
            $table='entity';
            $tableAlias='item';
            $fieldName='';
            $userTableCounter=0;
            $optionsTableCounter=0;

            foreach($prop['attr'] AS $key=>$val){
                // Обрабатываем только то, что выводится в backend_list и в фильтры
                if($val['backend_list']==1 && $val['filter']==1){

                    // В зависимости от типа данных корректируем таблицу и поле источника данных
                    // Если это SELECT
                    if($val['element']=='select'){
                        $optionsTableCounter++;
                        if($val['multiple']==0){
                            $srcValuesTable='data_'.$param['entity_type_alias'];
                            $table='options';
                            $tableAlias='options'.$optionsTableCounter;
                            // Таблица где лежит значение
                            if($val['attr_group']=='default') $tableAlias='item2';
                            else $tableAlias='TEST!!!!';
                            // Обычный список из перечисленных значений
                            if($val['source']==''){
                                $optlist=mysql::getSpecial("SELECT t2.id,t2.value AS name FROM `".$srcValuesTable."` AS t1 JOIN `".$table."` AS t2 ON t2.id=t1.`".$val['alias']."` WHERE t2.attr_id IN (0, ".$val['id'].") ORDER BY name ASC");
                                if(!isset($optlist[0])){
                                    $optlist=array(0=>'--НЕТ--')+$optlist;
                                }
                            }
                            // А если список из другой таблицы
                            else {
                                list($tb,$idf,$idn,$wh)=explode(".",$val['source']);
                                $optlist=array(0=>'--НЕТ--');
                                $optlist+=mysql::getSpecial("SELECT t2.`".$idf."`, t2.`".$idn."` AS `name` FROM `".$tb."` AS t2 JOIN `".$srcValuesTable."` AS t1 ON t2.id=t1.`".$val['alias']."` WHERE t2.".$wh." ORDER BY `".$idn."` ASC");
                            }

                            // Получаем все доступные опции списка
                            $filter['fields'][$key]=array(
                                'field'         => $val['name'],        /* Название поля */
                                'table'         => $srcValuesTable,     /* Таблица где хранится значение */
                                'tableValues'   => 'options',           /* Таблица где лежат оригинальные значения */
                                'tableAlias'    => $tableAlias,         /* Таблица с опциями */
                                'filterType'    => 0,
                                'options'       => $optlist
                            );
                        }
                    }
                }
            }
            // Сохраняем данные фильтра в кэш
            file::save($_SERVER['DOCUMENT_ROOT'].'/_filter/'.$item.'.acache',serialize($filter));
        }

        if(!empty($filter['fields'])){
            foreach($filter['fields'] AS $key=>$val){
                $out.='<div class="btn-group">
                <div class="label">'.$val['field'].'</div>
                <select class="size-xm" name="filter[`'.$val['tableAlias'].'`.`'.$key.'`]" onChange="ajaxPost(\'filterForm\',\'data::filterApply?='.$item.'&category='.$category.'\')">';
                foreach($val['options'] AS $ok=>$ov){
                    $selected='';
                    if($_SESSION['filter'][$item]['`'.$val['tableAlias'].'`.`'.$key.'`']==$ok){
                        $selected='selected="selected" ';
                    }
                    $out.='<option '.$selected.'value="'.$ok.'">'.$ov.'</option>';
                }
                $out.='</select>
                </div>';
            }
        }


        $out='<div class="filterDiv"><b>Фильтр:</b>
        <div class="btn-group" style="float:right; margin-right:0;"><div class="label tooltip" data-tooltip="Время обновления">'.date("d.m.Y H:i:s",$filter['createTime']).'</div><div class="btn"><i class="ic-refresh" style="margin-right:0;"></i></div><div class="btn" onClick="ajaxGet(\'data::filterRemove?='.$item.'&cat='.$category.'\')"><i class="ic-x2" style="margin-right:0;"></i></div></div>
        <form method="POST" id="filterForm"><input type="hidden" name="item" value="'.$item.'"><input type="hidden" name="category" value="'.$category.'">'.$out.'</form></div>';
        if($isArray===true) return array('searchFilter'=>$out);
        return $out;
    }

    // Сохранение потомка
    static function childSave(){
        global $error;
        self::entitySave($_POST['array']);
        if($error===false){
            ajax::sound("click"); ajax::domRemove("childEdWin");
            return array('field'.$_POST['attrId']=>self::showChildList($_POST['array']['parent_id'], $_POST['array']['entity_type'], $_POST['attrId']));
        }
        return false;
    }

    // Изменение глобальных аттрибутов и перезапись всех инфоблоков
    static function checkBoxGlobal(){
        global $item, $field;
        cacheClear();
        list($table,$field)=explode(".",$field);
        // Зависимые чекбоксы
        $specialFields=array(
            'backend0'      =>" backend='0', backend_list='0', sort='0' ",
            'backend_list0' =>" backend_list='0', sort='0' ",
            'backend_list1' =>" backend_list='1', backend='1' ",
            'sort1'         =>" backend='1', backend_list='1', sort='1' ",
        );
        $value=invert(mysql::getValue("SELECT `".escape($field)."` FROM `".escape($table)."` WHERE id='".escape($item)."' LIMIT 1"));
        if(isset($specialFields[$field.$value])) mysql::query("UPDATE `".escape($table)."` SET ".$specialFields[$field.$value]." WHERE id='".escape($item)."' LIMIT 1");
        else mysql::query("UPDATE `".escape($table)."` SET `".escape($field)."`='".escape($value)."' WHERE id='".escape($item)."' LIMIT 1");
        data::entityesUpdate();
        return false;
    }

    // Сохранение чекбокса
    static function checkbox(){
        global $item, $field, $entity, $opened;
        cacheClear();
        ajax::sound('click');
        list($table,$field)=explode(".",$field);
        // Зависимые чекбоксы
        $specialFields=array(
            'backend0'      =>" backend='0', backend_list='0', sort='0' ",
            'backend_list0' =>" backend_list='0', sort='0' ",
            'backend_list1' =>" backend_list='1', backend='1' ",
            'sort1'         =>" backend='1', backend_list='1', sort='1' ",
        );
        $value=invert(mysql::getValue("SELECT `".escape($field)."` FROM `".escape($table)."` WHERE id='".escape($item)."' LIMIT 1"));
        if(isset($entity) && isset($specialFields[$field.$value])) $uqw=$specialFields[$field.$value];
        else $uqw="`".escape($field)."`='".escape($value)."'";
	    mysql::query("UPDATE `".escape($table)."` SET ".$uqw." WHERE id='".escape($item)."' LIMIT 1");

        // Меняем данные в папке с кешем
	    self::entityCacheUpdate($item);
        // Если меняем что-либо в таблице с аттрибутами, то заставим перерисовать список полей
        if(isset($opened) && isset($entity)){
            data::queryGenerator($entity);
            ajax::javascript('catOpen('.$opened.')');
            return false;
        }
        // Если это папка
        if($entity==7 && $field=='hidden'){
            // Меняем CSS классы у данной папки в дереве слева и в списке папок
            if($value==1) {
                ajax::classRemove('a'.$item,'y');
                ajax::classRemove('fl'.$item,'ic-folder');
                ajax::classAdd('a'.$item,'g');
                ajax::classAdd('fl'.$item,'ic-dirlock');
            }
            else {
                ajax::classRemove('a'.$item,'g');
                ajax::classRemove('fl'.$item,'ic-dirlock');
                ajax::classAdd('a'.$item,'y');
                ajax::classAdd('fl'.$item,'ic-folder');
            }
            return false;
        }
        return false;
    }

    // Отображение полей сущности для выбора нужных в экспорте
    static function showEntityFields($id=false){
        global $item;
        if($id===false) $id=$item;
        $array=mysql::getArray("SELECT id, alias, element, type, view, name, multiple, entity_type_id, icon, source, dop, hidden FROM `attributes` WHERE entity_type_id IN (0,".escape($id).") ORDER BY `entity_type_id` ASC, `frontend_order` ASC");
        $ar=array();
        foreach($array AS $val){
            $ar[$val['id']]=$val;
        }
        $out='<table class="formtable" style="border:none; width:100%;">';
        foreach($array AS $val){
            $out.='<tr>
            <td style="width:30px"><input type="checkbox" name="array[fields]['.$val['id'].'][enabled]"></td>
            <td>'.$val['name'].'</td>
            <td><input type="text" style="width:120px;" name="array[fields]['.$val['id'].'][outname]"></td>
            <td><textarea style="width:200px;height:60px;" name="array[fields]['.$val['id'].'][php]"></textarea></td>
            </tr>';
        }
        $out.='</table>';
        if($id!==false) return $out;
        else return array('exFields'=>$out);
    }

    // Редактирование сценария экспорта
    static function exportEdit($mn,$id){
        $entList=mysql::getSpecial("SELECT id,entity_type AS name FROM `entity_type` WHERE id>0 ORDER BY `entity_type` ASC");
        $k=array_keys($entList);
        $array=array(
            'id'=>0,
            'name'=>'',
            'entity_type_id'=>$k[0],
            'type'=>'csv',
            'encoding'=>'windows',
            'fields'=>array()
        );
        $out='<form id="ibForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name=array[id] value="'.$array['id'].'"> 
        <table class="formtable" style="width:100%">
        <tr><td>Сценарий:</td><td><input id="field3" type="text" name="array[name]" value="'.$array['name'].'" maxlength="64" class="size-xl"></td></tr>
        <tr><td>Инфоблок</td><td>'.createSelect('array[entity_type_id]',$array['entity_type_id'],$entList,' id="ibSelect" onChange=\'ajaxGet("data::showEntityFields?="+getSelectValue("ibSelect"))\'').'</td></tr>
        <tr><td>Формат</td><td>'.createSelect('array[type]',$array['type'],array('csv'=>'CSV','xml'=>'xml','php'=>'Массив PHP','txt'=>'Текстовый файл с разделителем табуляции')).'</td></tr>
        <tr><td>Кодировка</td><td>'.createSelect('array[encoding]',$array['encoding'],array('windows'=>'Windows-1251','unicode'=>'UTF-8 Unicode')).'</td></tr>
        <tr><td>Свойства</td><td id="exFields">'.self::showEntityFields($array['entity_type_id']).'</td></tr>
        </table>
        </form>';

        return $out;
    }

    // ЭКСПОРТ
    static function exportMenu($mn){
        static $types=array(
            'csv'=>'CSV',
            'xml'=>'XML',
            'php'=>'Массив PHP',
            'txt'=>'Текстовый файл с разделителем табуляции'
        );
        static $enc=array(
            'windows'=>'Windows-1251',
            'unicode'=>'UTF-8 Unicode'
        );
        $out='<div class="row">
            <div class="btn-group"><div class="btn" onClick="ajaxGet(\'exportEdit?=0\',\'right\',\''.$mn.'\')"><i class="ic-plus"></i>Добавить</div></div>
        </div>
        <div class="row"><h4>Сценарии экспорта</h4></div>
        <div class="row">
            <table class="cmstable4">
                <tr><th style="width:24px;"></th><th>Сценарий</th><th>Инфоблок</th><th>Формат</th><th>Дата/время</th><th style="width:60px;">Действия</th></tr>';
        $array=mysql::getArray("SELECT t1.*, UNIX_TIMESTAMP(t1.timestamp) AS stamp, t2.entity_type AS entity_name FROM `export` AS t1 JOIN `entity_type` AS t2 ON t2.id=t1.entity_type_id ORDER BY t1.id ASC");
        if($array!=false){
            $out.='<tr><td><i class="ic-php"></td><td><b class="name">'.$val['name'].'</b></td><td>'.$val['entity_name'].'</td><td>'.$types[$val['type']].'</td><td>'.$enc[$val['encoding']].'</td><td>'.date("d.m.Y H:i",$val['stamp']).'</td><td>Действия</td></tr>';
        }
        $out.='</table>
        </div>';
        return $out;
    }

    // Сохранение нового значения для SELECT
    static function selectOptionSave(){
        $out='';
        $opt=$_POST['opt'];
        $array=mysql::getArray("SELECT * FROM `options` WHERE attr_id=".escape($opt['attr_id']));
        $out='<option value="0">-НЕ ЗАДАНО-</option>';
        if($array!=false){
            foreach($array AS $v){
                $out.='<option id="sop'.$v['id'].'" value="'.$v['id'].'">'.$v['value'].'</option>';
                if($v['value']==trim($opt['name'])) {
                    ajax::dialogAlert('Опция с таким значением уже есть в списке!');
                    return false;
                }
            }
            mysql::query("INSERT INTO `options` SET attr_id=".$opt['attr_id'].", value='".escape(trim($opt['name']))."'");
            $id=mysql::insertId();
            $newOption='<option id="sop'.$id.'" value="'.$id.'">'.trim($opt['name']).'</option>';
            $out.=$newOption;
        }
        ajax::sound("sys");
        if($opt['multiple']==0){
            return array(
                'tblOptTable'=>self::selOptionsTable($opt['attr_id']),
                'edSel'.$opt['attr_id']=>$out
            );
        }
        else {
            $func='var Nparent=getId("field'.$opt['attr_id'].'");
            var Nnode=document.createElement("option");
            Nnode.id="sop'.$id.'";
            Nnode.value="'.$id.'";
            Nnode.innerHTML="'.trim($opt['name']).'";
            Nparent.appendChild(Nnode);';
            ajax::javascript($func);
            return array(
                'tblOptTable'=>self::selOptionsTable($opt['attr_id'])
            );
        }
    }

    // Сохранение аттрибутов изображения
    static function imgAttrSave(){
        mysql::query("UPDATE `file` SET name='".escape(trim($_POST['image']['name']))."', description='".escape(trim($_POST['image']['description']))."', filesize=".escape($_POST['image']['filesize']).", imagesize='".escape(trim($_POST['image']['imagesize']))."' WHERE id=".escape($_POST['image']['id'])." LIMIT 1");
        ajax::javascript('domRemove("AxiomDialogMask")');
        ajax::domRemove('imgEditWin');
        return false;
    }

    // Delete Select option
    static function selectOptionDelete(){
        global $item;
        if($item!=0){
            ajax::sound("click");
            // Получим данные атрибута и инфоблока, чтобы получить данные таблицы
            $atributes=mysql::getArray("SELECT t2.*, t3.entity_type_alias 
                FROM `options` AS t1
                 JOIN `attributes` AS t2 ON t2.id=t1.attr_id
                 JOIN `entity_type` AS t3 ON t3.id=entity_type_id
            WHERE t1.id=".escape($item)."
            LIMIT 1",true);
            if($atributes!=false){
                mysql::query("UPDATE `data_".$atributes['entity_type_alias']."` SET `".atribute['alias']."`=0 WHERE `".atribute['alias']."`=".escape($item));
                // Удаляем множественные атрибуты
                if($atributes['multiple']!=0) {
                    mysql::query("DELETE FROM `multidata` WHERE value_int=".escape($item));
                }
                mysql::query("DELETE FROM `options` WHERE id=".escape($item));
                ajax::domRemove("sopt".$item);
                ajax::domRemove("sop".$item);
            }
        }
        return false;
    }

    // select options editor window
    static function selectOptionsEditor(){
        global $item, $atrId;
        $atributes=mysql::getArray("SELECT * FROM `attributes` WHERE id=".escape($atrId)." LIMIT 1",true);
        ajax::window('<h2>Опции списка &laquo;'.$atributes['name'].'&raquo;</h2><div data-close="closeSelectEditor('.$atrId.')" style="width:480px; height:340px; overflow:hidden;">
        <div id="tblOptList" style="width:100%; max-height:210px; overflow-y:scroll; background:#eeeeee; padding:0 !important; margin-bottom:10px;">
        <table id="tblOptTable" class="cmstable4" style="margin:0;">
            '.self::selOptionsTable($atrId).'
        </table>
        </div>
        <hr>
        <form id="atrEdFrm" method="POST">
        <div class="btn-group">
           <div class="label"><i class="ic-plus" style="margin-top:6px;"></i></div>
           <input type="hidden" name="opt[attr_id]" value="'.$atrId.'">
           <input type="hidden" name="opt[item]" value="'.$item.'">
           <input type="hidden" name="opt[multiple]" value="'.$atributes['multiple'].'">
           <input id="newOptionValue" type="text" name="opt[name]" style="width:290px;" maxlength="64" value="">
           <div class="btn" onclick="selectOptionSave()"><i class="ic-save"></i>Сохранить</div>
        </div>
        </form>
        </div>',true);
        return false;
    }

    // Возвращает опции выпадающего списка SELECT
    static function showSelectOptions($atrId=false){
        global $item, $fieldId;
        if($atrId===false) $atrId=$item;
        $o=mysql::getArray("SELECT * FROM `catalog_options` WHERE id=0 || attr_id=".escape($atrId)." ORDER BY `value` ASC");
        $options='';
        foreach($o AS $val){
            $options.='<option id="sop'.$val['id'].'" value="'.$val['id'].'">'.$val['value'].'</option>';
        }
        if(isset($fieldId)){
            return array($fieldId=>$options);
        }
        else return $options;
    }

    // Возвращает таблицу со списокм значений SELECT
    static function selOptionsTable($atrId){
        $o=mysql::getArray("SELECT * FROM `options` WHERE id=0 || attr_id=".escape($atrId)." ORDER BY `value` ASC");
        $options='';
        foreach($o AS $val){
            $options.='<tr id="sopt'.$val['id'].'"><td id="sopd'.$val['id'].'">';
            if($val['id']!=0) $options.='<b class="hand" onClick="selEditor('.$val['id'].',this.innerHTML)">'.$val['value'].'</b><i class="ic-delete" onClick="ajaxGet(\'data::selectOptionDelete?='.$val['id'].'\')"></i>';
            else $options.=$val['value'];
            $options.='</td></tr>';
        }
        return '<tbody>'.$options.'</tbody>';
    }

    // Окно свойств изображения
    static function imageEdit(){
        global $item;
        $image=mysql::getArray("SELECT * FROM `file` WHERE id=".escape($item)." LIMIT 1",true);
        $file=$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.floor($image['entity_id']/100).'/'.$image['file'].'.'.$image['ext'];
        if(file_exists($file)){
            $isize=getimagesize($file);
            if($isize[0]>=$isize[1]) $is='width:130px;';
            else $is='height:130px; ';
            $filesize=filesize($file);
            $furl=$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($image['entity_id']/100).'/'.$image['file'].'.'.$image['ext'];
            ajax::window('<div id="openWindow" title="Аттрибуты изображения" style="width:480px; height:240px; overflow:hidden;"><div style="width:134px; height:180px; float:left; margin: 5px 12px 8px 6px; "><div style="width:134px; height:134px; background:#eeeeee; position:relative;border: 1px solid #ffffff;"><a rel="iLoad" href="'.$furl.'"><img src="'.$furl.'" alt="" style="position:absolute; left:50%; top:50%; transform: translate(-50%, -50%);'.$is.'"></a></div><div style="text-align:center; font-size:12px; padding:6px 0;">'.strtoupper($image['ext']).': <b>'.$isize[0].' x '.$isize[1].'</b><br>'.file::bytes($filesize,1).'</div></div><form id="imageEdForm" name="imageEdForm" method="POST" enctype="multipart/form-data" style="width:310px; float:left; clear:none;"><input type="hidden" name="image[filesize]" value="'.$filesize.'"><input type="hidden" name="image[imagesize]" value="'.$isize[0].'x'.$isize[1].'"><input type="hidden" name="image[id]" value="'.htmlspecialchars($image['id']).'"><table class="formtable" cellpadding="4" cellspacing="4" border="0" style="width:100%"><tbody><tr><td><input type="text" name="image[name]" value="'.htmlspecialchars($image['name']).'" maxlength="64" placeholder="Название (alt)" autocomplete="off" class="input" style="width:300px;"></td></tr><tr><td><textarea id="frCnt" name="image[description]" style="width:300px; height:100px; resize:none;" onkeydown="textareaLimiter(this,\'255\',\'frCnt\');" onkeyup="textareaLimiter(this,\'255\',\'frCnt\');" placeholder="Описание (description)">'.$image['description'].'</textarea></td></tr><tr><td style="padding-top:4px;"><span class="smallgrey">Не более 255 символов. Введено </span><span class="smallgrey textareaCounter" id="delimiterfrCnt">'.mb_strlen($image['description']).'</span></td></tr></tbody></table><div class="btn" onclick="ajaxPost(\'imageEdForm\',\'data::imgAttrSave\')"><i class="ic-save"></i>Сохранить</div><div class="btn" onclick="domRemove(\'imgEditWin\')"><i class="ic-undo"></i>Отмена</div></td></tr></tbody></table></form></div>',true,'imgEditWin');
            return false;
        }
        else {
            ajax::dialogAlert("Изображение не найдено!");
        }
        return false;
    }

    // Перемещение товаров из буфера в заданную папку
    static function pasteFromBuffer($category=false){
        $out='';
        global $item;
        if($category===false) $category=$item;

        // Узнаем, товары каких групп присутствуют в буфере
        $errorCounter=0;
        $pasted=array();
        $folderName='';
        if(isset($_SESSION['buffer'])){
            // Получим тип товаров, который находится в папке-приемнике
            $ent=mysql::getArray("SELECT t1.child_entity_type AS type, t2.name FROM `data_category` AS t1 JOIN `entity` AS t2 ON t2.id=t1.id WHERE t1.id=".escape($category)." LIMIT 1", true);
            $entityType=$ent['type'];
            $folderName=$ent['name'];
            foreach($_SESSION['buffer'] AS $key=>$val){
                if($val['entity_type']==$entityType) {
                    $pasted[]=$key;
                    unset($_SESSION['buffer'][$key]);
                }
                else $errorCounter++;
            }
            if(!empty($pasted)){
                // Переносим товары в папку
                mysql::query("UPDATE `entity` SET parent_id=".escape($category)." WHERE id IN(".implode(",",$pasted).")");
            }
        }
        if(count($pasted)>=1){
            ajax::window('<div style="width:400px;"><div class="okblock">В папку '.pluralForm(count($pasted),'перемещен,перемещено,перемещено').' '.count($pasted).' '.pluralForm(count($pasted),'объект,объекта,объектов').' из буфера обмена.</div></div>');
        }
        if($errorCounter>0){
            ajax::dialogAlert('<div class="error">ВНИМАНИЕ! Не удалось переместить '.$errorCounter.' '.pluralForm($errorCounter,'объект,объекта,объектов').' из буфера обмена, так как '.pluralForm($errorCounter,'его,их,их').' тип не соответствует типу содержимого папки &laquo;'.$folderName.'&raquo;.</div>');
        }
        return false;
    }

    // Удаление из буфера
    static function bufferDelete(){
        global $item;
        $count=0;
        if(isset($_SESSION['buffer'][$item])){
            unset($_SESSION['buffer'][$item]);
            $count=count($_SESSION['buffer']);
            ajax::javascript('if(getId("buf'.$item.'")!=false){ domRemove("buf'.$item.'");}');
            return array('fufCount'=>$count);
        }
        $GLOBALS['asArray']=true;
        return false;
    }

    // Вставка в буфер
    static function toBuffer(){
        global $item, $type, $parent;
        //global $session;
        if(!isset($_SESSION['buffer'])) $_SESSION['buffer']=array();
        if(!isset($_SESSION['buffer'][$item])){
            $ar=mysql::getArray("SELECT entity_type_alias,icon FROM `entity_type` WHERE id=".escape($type)." LIMIT 1",true);
            $array=mysql::getArray("SELECT t1.*, t2.* FROM `entity` AS t1 JOIN `data_".$ar['entity_type_alias']."` AS t2 ON t2.id=t1.id WHERE t1.id=".$item." LIMIT 1",true);
            $array['icon']=$ar['icon'];
            $_SESSION['buffer'][$item]=$array;
        }
        $GLOBALS['asArray']=true;
        return self::bufferShow();
    }

    // Отображение содержимого буфера
    static function bufferShow(){
        global $asArray;
        $buff='';
        if(isset($_SESSION['buffer']) && !empty($_SESSION['buffer'])){
            $buff.='<div class="row" style="margin:6px;"><div class="btn" title="Перенести в текущую папку" onClick="pasteFromBuffer()"><i class="ic-paste" style="margin-right:0"></i></div></div>
            <div class="bufContent">
		        <table class="cmstable4" style="margin-right:20px;"><tr><th style="width:24px !important;">&nbsp;</th><th>Наименование</th><th style="width:24px !important;"> </th></tr>';
            foreach($_SESSION['buffer'] AS $val){
                $name=mb_substr($val['name'],0,32,'utf-8');
                $comment='';
                if(mb_strlen($val['name'])>32) {
                    $name.='...';
                    $comment=' title="'.htmlspecialchars($val['name']).'"';
                }
                $buff.='<tr id="buf'.$val['id'].'"><td><i class="'.$val['icon'].'"></i></td><td><b class="hand"'.$comment.' onClick="itemEdit('.$val['id'].','.$val['entity_type'].','.$val['parent_id'].')">'.$name.'</b></td><td style="width:24px !important"><i class="ic-cancel color-red" onClick="bufDel('.$val['id'].')"></i></td></tr>';
            }
            $buff.='</table>
            </div>';
        }
        else {
            $buff='';
            ajax::styleSet("bufferBlock","right:10000px;");
            ajax::message("Буфер обмена пуст!");
        }
        if(isset($asArray)){
            return array('bufferBlock'=>$buff, 'fufCount'=>count($_SESSION['buffer']));
        }
        else return $buff;
    }

    // Копия потомка
    static function childCopy(){
        global $item, $entity, $cat, $asChild;
        $id=self::entityCopy();
        //echo 'item='.$item.' cat='.$cat.' asChild='.$asChild;
        ajax::javascript('editChild('.$id.','.$entity.','.$cat.','.$asChild.')');
        return array(
            'field'.$asChild=>self::showChildList($cat, $entity, $asChild)
        );
    }

    // Полная копия инфоблока
    static function entityCopy(){
        global $item, $mn, $entity, $cat, $p, $contentDiv, $asChild;
        $data=mysql::getArray("SELECT * FROM `entity` WHERE id=".escape($item)." LIMIT 1",true);

        if($data!=false){
            $srcParent=$data['parent_id'];
            $srcEntity=$data['entity_type'];
            $entityAlias=mysql::getValue("SELECT entity_type_alias FROM `entity_type` WHERE id=".escape($srcEntity)." LIMIT 1");
            // Получаем все аттрибуты полей типа FILE для этого инфоблока
            $fileParams=mysql::getSpecial("SELECT id,dop AS name FROM `attributes` WHERE entity_type_id=".escape($srcEntity)." AND `type`='file'");
            if($fileParams!=false){
                foreach($fileParams AS $key=>$val){
                    $v=unserialize($val);
                    $v=explode(",",$v['imgsizes']);
                    $vvv=array();
                    $vvv[]='sys';
                    foreach($v AS $vv){
                        list($suffix,)=explode(":",$vv);
                        $vvv[]=$suffix;
                    }
                    $fileParams[$key]=$vvv;
                }
            }


            $insert=array();
            foreach($data AS $key=>$val){
                if($key!='id'){
                    if($key=='name') $val;
                    elseif($key=='date_add') $val=date("Y-m-d H:i:s",time());
                    elseif($key=='owner') $val=$_SESSION['user']['id'];
                    $insert[]="`".$key."`='".escape($val)."'";
                }
            }
            mysql::query("INSERT INTO `entity` SET ".implode(",",$insert));
            $entityId=mysql::insertId();

            // Данные для основных таблиц инфоблока DATA и TEXT
            $tbls=explode(",","data,text");
            foreach($tbls AS $ts){
                $data=mysql::getArray("SELECT * FROM `".$ts."_".$entityAlias."` WHERE id=".escape($item)." LIMIT 1", true);
                if($data!=false){
                    $insert=array();
                    foreach($data AS $key=>$val){
                        if($key=='id') $val=$entityId;
                        $insert[]="`".$key."`='".escape($val)."'";
                    }
                    mysql::query("INSERT INTO `".$ts."_".$entityAlias."` SET ".implode(",",$insert));
                }
            }


            $filePath=$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.floor($entityId/100);
            $oldFilePath=$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.floor($item/100);

            // Множественные данные для таблиц FILE, MULTIDATA
            // catalog_attributes.dop - параметры файлов
            // setMainFile(10762,3614);

            $tbls=explode(",","multidata,file");
            foreach($tbls AS $ts){
                $data=mysql::getArray("SELECT * FROM `".$ts."` WHERE entity_id=".escape($item));
                if($data!=false){
                    $fnameTmp=$_SESSION['user']['id'].'-'.time();
                    $fcounter=0;
                    foreach($data AS $dk=>$dv){
                        $insert=array();
                        foreach($dv AS $key=>$val){
                            if($key!='id'){
                                if($key=='entity_id') $val=$entityId;
                                elseif($key=='file'){
                                    // Существует ли папка
                                    if(!file::folderExists($filePath)) mkdir($filePath,0777);
                                    $val=$fnameTmp.'-'.$fcounter;
                                    // Копирование файла
                                    if($dv['type']=='image' && isset($fileParams[$dv['attribute_id']])){
                                        foreach($fileParams[$dv['attribute_id']] AS $suf) {
                                            if(copy($oldFilePath.'/'.$dv['file'].$suf.'.'.$dv['ext'], $filePath.'/'.$fnameTmp.'-'.$fcounter.$suf.'.'.$dv['ext'])){
                                                chmod($filePath.'/'.$fnameTmp.'-'.$fcounter.$suf.'.'.$dv['ext'], 0775);
                                            }
                                        }
                                    }
                                    if($dv['type']!='image') {
                                        if(copy($oldFilePath.'/'.$dv['file'].'.'.$dv['ext'], $filePath.'/'.$fnameTmp.'-'.$fcounter.'.'.$dv['ext'])){
                                            chmod($filePath.'/'.$fnameTmp.'-'.$fcounter.'.'.$dv['ext'], 0775);
                                        }
                                    }
                                    $fcounter++;
                                }
                                $insert[]="`".$key."`='".escape($val)."'";
                            }
                        }
                        mysql::query("INSERT INTO `".$ts."` SET ".implode(",",$insert));
                        // Делаем первый файл основным
                        if($fcounter==1) self::setMainFile($entityId,mysql::insertId());
                    }
                }
            }
        }
        cacheClear();
        if(!$asChild){
            $contentDiv='right';
            $mn=$entityAlias;
            $item=$entityId;
            $entity=$srcEntity;
            $p=0;
            return self::edit();
        }
        else{
            return $entityId;
        }
    }

    // Формирование пути к изображению на основе ID элемента
    static function path($mn,$id){
        global $settings;
        return $settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($id/100).'/';
    }

    // Перед добавлением нового аттрибута проверяем, не существует ли он уже
    static function checkAttribute(){
        global $item, $alias, $type;
        $array=mysql::getValue("SELECT id FROM `attributes` WHERE entity_type_id IN(0,".escape($type).") AND (alias='".escape(trim($alias))."' OR name='".escape(trim($item))."') LIMIT 1");
        if($array!==false) {
            return array('fstatus'=>'<div class="error">Аттрибут с таким именем или псевдонимом уже существует!</div>');
        }
        return array('fstatus'=>'');
    }

    // Окно для выбора типа добавляемого аттрибута
    static function attrSelect(){
        global $item;
        $out='';

        $types=array(
            'NONE'=>'--НЕ ЗАДАНО--',
            'int'=>'Целое число (INT)',
            'decimal'=>'Дробное число (DECIMAL)',
            'checkbox'=>'Чекбокс',
            'varchar'=>'Строка',
            'textarea'=>'Многострочный текст',
            'youtube'=>'Видео YOUTUBE',
            'googlemap'=>'Карта Google',
            'select'=>'Выпадающий список',
            'link'=>'Ссылка на сущность',
            'date'=>'Дата',
            'dateandtime'=>'Дата и время',
            'child'=>'Вложенный элемент',
            'file'=>'Файл',
            'order'=>'Индекс сортировки',
            'button'=>'Кнопка (админпанель)',
	        'color'=>'Выбор цвета'
        );

        $out.='<div class="row">
            <div class="btn" onclick="ajaxGet(\'data::settings?=0\')"><i class="ic-boxes"></i>Инфоблоки</div>
            </div>
        <div class="row">
        <form name="ftSelect" id="ftSelect" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="entTypeId" name="attr[entity_type_id]" value="'.$item.'">
        <input type="hidden" name="attr[id]" value="0">
        <div id="fstatus"></div>
        <div class="btn-group">
            <div class="label">Название</div>
            <input id="ffname" type="text" name="attr[name]" value="" maxlength="64" style="width:300px" onKeyUp="checkAttrName()">
            <div class="label">Alias</div>
            <input id="ffalias" style="width:200px" type="text" name="attr[alias]" value="" maxlength="64">
            '.createSelect("attr[element]","NONE",$types,'style="width:200px;"  id="ftlist" disabled="disabled" onChange=\'ajaxPost("ftSelect","data::fieldSel")\'').'
        </div>
        </form><div id="fprop"></div>
        </div>';
        return array('newField'=>$out);
    }


    // Сохранение порядка следования аттрибутов
    static function attrOrder(){
        global $item,$mn, $entity, $opened, $dragStatus, $noscript;
        $d=array();
        foreach(explode(',',$dragStatus) AS $s){ if($s!='') $d[]=$s;}
        $dragStatus=implode(',',$d);
        $old=mysql::getArray("SELECT `id`,`backend_order` AS `order` FROM `".escape($item)."` WHERE id IN (".escape($dragStatus).") ORDER BY `backend_order` ASC");
        $ordered=0;
        $order=explode(',',$dragStatus);
        if($old!=false && count($order)==count($old)){
            foreach($order AS $key=>$val){
                if($old[$key]['id']!=$val){
                    $ordered++;
                    mysql::query("UPDATE `".escape($item)."` SET `backend_order`='".escape($old[$key]['order'])."' WHERE id='".escape($val)."' LIMIT 1");
                }
            }
        }
        if(isset($noscript)) {
            $ret=self::entityesUpdate();
            if(isset($entity) && isset($opened)) {
                ajax::javascript('catOpen('.$opened.')');
            }
        }
        else {
            self::queryGenerator($entity);
            ajax::javascript('catOpen('.$opened.')');
        }
        ajax::sound("click");
        cacheClear();
        return false;
    }

    // Сохранение измененного порядка сущностей или файлов
    // $item - таблица ( 'data' )
    // $entityTypeId - тип сущностей
    // $cat - ID открытой в данный момент папки (для папки)
    // $isFiles - признак того, что сортируются ФАЙЛЫ
    static function orderSave(){
        global $item, $entityTypeId, $cat, $isFiles;
        mysql::orderUpdate($item,$_REQUEST['dragStatus']);
        cacheClear();
        ajax::sound("click");
        if(!isset($isFiles)){
            return array('side'=>self::getTree($entityTypeId, $cat));
        }
        else return false;
    }


    // Выбор иконки
    static function moduleIconSelect($item){
        $out='';
        $allIcons=ui::getIcons();
        foreach($allIcons AS $val){
            $val='ic-'.$val;
            if($val==$item) $style='color-red';
            else $style='';
            $out.='<i class="'.$val.$style.'" onClick="icSelect(\''.$val.'\')" title="'.str_replace("ic-","",$val).'"></i>';
        }
        return '<div id="openWindow" title="Выберите иконку" style="width:800px; height:400px;"><div class="iconselector">'.$out.'</div></div>';
    }

    // Информация о том, в каких папках используется сущность
    static function entityUsed(){
        global $item;
        $array=mysql::getArray("SELECT t1.id, t1.alias, t2.name FROM `data_category` AS t1 JOIN `entity` AS t2 ON t2.id=t1.id WHERE t1.child_entity_type=".escape(onlyDigit($item))." ORDER BY t2.name ASC");
        if($array!=false){
            // Получим к-во записей для всех папок
            $counts=array();
            $ids=array();
            foreach($array AS $val) $ids[]="SELECT '".$val['id']."' AS id, COUNT(*) AS name FROM `entity` WHERE parent_id=".$val['id'];
            if(!empty($ids)) $counts=mysql::getSpecial(implode(" UNION ",$ids));
            $out='<table class="cmstable4"><tr><th style="width:24px;">&nbsp;</th><th>Папка</th><th style="width:60px; text-align:right;">К-во</th></tr>';
            foreach($array AS $val) $out.='<tr><td><i class="ic-folder"></i></td><td><b class="hand" onClick="catOpen('.$val['id'].')">'.$val['name'].'</b></td><td  style="text-align:right;">'.$counts[$val['id']].'</td></tr>';
            $out.='</table>';
            ajax::window('<div style="width:600px;">'.$out.'</div>',true,'used'.$item);
        }
        return false;
    }

    // Отображение списка типов сущностей
    static function settings(){
        global $moduleAlias, $moduleName, $edited;
        $out='';

        // Получим сущности, которые присоединены к чему-либо
        $f=mysql::getArray("SELECT id,child_entity_type AS type FROM `data_category` WHERE child_entity_type!=0");
        $folderData=array();
        if($f!=false){
            foreach($f AS $val){
                if(!isset($folderData[$val['type']])) $folderData[$val['type']]=array();
                $folderData[$val['type']][]=$id;
            }
        }

        $array=mysql::getArray("SELECT id, entity_type, entity_type_alias, base, CHAR_LENGTH(before_save) AS before_save, CHAR_LENGTH(onsave) AS onsave, `copy`, `delete`, `buffer`, `icon`,`filter_order`  FROM `entity_type` ORDER BY `id`='0' DESC, `entity_type` ASC");
        if($array!=false){



            $out.='<table class="cmstable4" id="elFields">
            <tr><th style="width:32px;"></th><th style="width:32px;">#</th><th>Инфоблок</th><th style="width:200px;">Alias</th><th style="width:30px; text-align:center;" title="PHP обработка данных перед сохранением"><i class="ic-dstar"></i></th><th style="width:30px;  text-align:center;"  title="PHP обработка данных после сохранения"><i class="ic-dright"></i></th><th style="width:20px;" title="Используется в папках"><i class="ic-folder-open"></i></th><th style="width:24px;"><i class="ic-filter" title="Порядок опций в фильтре"></i></th><th style="width:100px;">Действия</th></tr>';
            foreach($array AS $val){
                if($val['id']==0) $val['entity_type']='_system: Общие аттрибуты';
                $out.='<tr id="ent'.$val['id'].'" style="opacity:1;">
                    <td><i class="ic-package"></i></td>
                    <td>'.$val['id'].'</td>
                    <td><b class="hand" onClick="ajaxGet(\'data::attr?='.$val['id'].'&mn='.$moduleAlias.'\')">'.$val['entity_type'].'</b></td>
                    <td class="smallgrey">'.$val['entity_type_alias'].'</td>
                    <td>';
                if($val['before_save']>8) $out.='<span title="PHP обработка перед сохранением" style="display:inline-block; font-size:8px; font-weight:bold; color:#fff; background:#49699d; line-height:17px; height:16px; padding:0 4px; border-radius:3px; overflow:hidden;">PHP</span>';
                $out.='</td>
                <td>';
                if($val['onsave']>8) $out.='<span title="PHP обработка после сохранения" style="display:inline-block; font-size:8px; font-weight:bold; color:#fff; background:#49699d; line-height:17px; height:16px; padding:0 4px; border-radius:3px; overflow:hidden;">PHP</span>';
                $out.='</td>
                    <td>';
                if(isset($folderData[$val['id']])){
                    $out.='<b class="hand" style="font-size:14px;font-weight:bold;" onClick="ajaxGet(\'data::entityUsed?='.$val['id'].'\')" title="Информация об использовании">'.count($folderData[$val['id']]).'</b>';
                }
                else $out.='&nbsp;';
                $out.='</td><td>';
	            if($val['filter_order']!=''){
					$out.='<i class="ic-filter" onClick="ajaxGet(\'data::filterOrder?='.$val['id'].'\')"></i>';
	            }
                $out.='</td><td><i class="ic-calckey" title="Обновить аттрибуты инфоблока" onClick="ajaxGet(\'data::queryGenerator?='.$val['id'].'\')"></i><i class="ic-cogs" title="Свойства инфоблока" onClick="ajaxGet(\'data::entityTypeEditForm?='.$val['id'].'&mn='.$moduleAlias.'&moduleName='.$moduleName.'\')"></i>';
                    if($val['base']==0) $out.='<i class="ic-server" title="Создание базовой структуры" onClick="ajaxGet(\'db::createBaseSettings?='.$val['id'].'\')"></i>';
                    if($val['id']>0) $out.='<i class="ic-delete" onClick="entityTypeDelete('.$val['id'].')"></i>';
                    $out.='</td>
                </tr>';
            }
            $out.='</table>';
        }
        $array=array();
        $array['right']='<h3>Инфоблоки</h3>
        <div class="row">
            <div class="btn-group" id="newEntity">
                <div class="btn" onClick="entityForm(\'newEntity\',1)"><i class="ic-plus"></i>Добавить инфоблок</div>
                </div>
            </div>
        </div>
        <div class="row" id="mdlset">'.$out.'</div>';
        if(isset($moduleAlias) && isset($moduleName)){
            $array['breadCrumbs']='<ul class="breadCrumbs">
            <li onClick="catOpen(4)"><span>'.$moduleName.'</span></li>
            <li onClick="ajaxGet(\'data::settings?=0&moduleAlias='.$moduleAlias.'&moduleName='.$moduleName.'\')"><span>Инфоблоки</span></li></ul>';
        }
        // Если в переменной "edited" есть что-либо,
        // то надо отмотать экран к элементу
        if(isset($edited)){
            ajax::javascript('var scIntoV=getId("ent'.$edited.'");scIntoV.style.background="#ffe9c8";scIntoV.scrollIntoView();');
        }
        return $array;
    }

    // Сохранение настроек инфоблока
    static function entypeSave(){
        if(strlen($_POST['ent']['entity_type'])>=2){
            $ent=$_POST['ent'];
            mysql::query("UPDATE `entity_type` SET `entity_type`='".escape($ent['entity_type'])."',`before_save`='".escape($ent['before_save'])."', `onsave`='".escape($ent['onsave'])."', `copy`='".escape($ent['copy'])."', `delete`='".escape($ent['delete'])."', `buffer`='".escape($ent['buffer'])."', `icon`='".escape($ent['icon'])."' WHERE id=".escape($ent['id'])." LIMIT 1");
            return self::settings();
        }
        else {
            ajax::dialogAlert("Слишком короткое название типа!");
            return false;
        }
    }

    // Свойства инфоблока
    static function entityTypeEditForm(){
        global $mn, $item, $moduleName;
        $out='';
        $array=mysql::getArray("SELECT id,entity_type_alias,entity_type,before_save,onsave,`copy`,`delete`,buffer,icon FROM `entity_type` WHERE id=".escape($item)." LIMIT 1",true);
        if($array!=false){
            $out.='<form id="entedform" name="entedform" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="ent[id]" value="'.$array['id'].'">
            <input type="hidden" name="action" value="entypeSave">
            <input type="hidden" name="ent[delete]" value="0">
            <input type="hidden" name="ent[buffer]" value="0">
            <input type="hidden" name="ent[copy]" value="0">
            <table class="formtable" style="width:100%;">
                <tr><td>Инфоблок</td><td><input type="text" name="ent[entity_type]" value="'.htmlspecialchars($array['entity_type']).'"></td></tr>
                <tr><td>Alias</td><td><input type="text" disabled value="'.htmlspecialchars($array['entity_type_alias']).'"></td></tr>
                <tr><td>&nbsp;</td><td><input id="fdel" class="switch" type="checkbox" name="ent[delete]" value="1" '.checkboxSelected($array['delete']).'><label for="fdel">Разрешить удаление</label></td></tr>
                <tr><td>&nbsp;</td><td><input id="fcopy" class="switch" type="checkbox" name="ent[copy]" value="1" '.checkboxSelected($array['copy']).'><label for="fcopy">Разрешить создание копии</label></td></tr>
                <tr><td>&nbsp;</td><td><input id="fbuff" class="switch" type="checkbox" name="ent[buffer]" value="1" '.checkboxSelected($array['buffer']).'><label for="fbuff">Разрешить вставку в буфер</label></td></tr>
                <tr><td><label>Иконка</label></td><td id="iconSelector"><div class="btn-group"><input type="text" name="ent[icon]" id="iconSelField" class="size-m" value="'.$array['icon'].'"><div class="btn" onclick="iconWindow(previousSibling.id)" title="Выбрать иконку"><i class="'.$array['icon'].'"></i></div></div></td></tr>
				<tr><td><label>BeforeSave PHP<br>(php код, исполняемый перед сохранении элементов инфоблока. Контроль ошибок и т.п.)</label></td><td>
				<div class="aceEditor" id="codeEditor1" data-lang="php" data-inline="1">'.htmlspecialchars($array['before_save']).'</div>
				<textarea id="codeEditorField1" class="code" name="ent[before_save]" style="display:none">'.htmlspecialchars($array['before_save']).'</textarea></td></tr>
                <tr><td><label>OnSave PHP<br>(php код, исполняемый при сохранении элементов инфоблока)</label></td><td>
                <div class="aceEditor" id="codeEditor2" data-lang="php" data-inline="1">'.htmlspecialchars($array['onsave']).'</div>
                <textarea class="code" name="ent[onsave]" style="display:none;">'.htmlspecialchars($array['onsave']).'</textarea></td></tr>
                <tr><td>&nbsp;</td><td><div class="btn" onclick="ajaxPost(\'entedform\',\'data::entypeSave\')"><i class="ic-save"></i>Сохранить</div><div class="btn" onclick="ajaxGet(\'data::settings?=0&moduleAlias='.$mn.'&moduleName='.$moduleName.'\')"><i class="ic-undo"></i>Отмена</div></td></tr>
            </table></form>';
        }
        return array('right'=>$out);
    }


    // Поиск по базе
    static function searchResult(){
        global $item, $mn, $search;
        $out='';
        // Получим все данные текущей папки
        if($item!=4) $qw="SELECT e3.backend_list AS param, e1.child_entity_type
        FROM `data_category` AS `e1` JOIN `entity_type` AS `e3` ON e3.id=e1.child_entity_type
        WHERE e1.id='".escape($item)."' LIMIT 1";
        else $qw="SELECT backend_list AS param FROM `entity_type` WHERE id=0 LIMIT 1";
        $folder=mysql::getArray($qw, true);
        if($folder!=false){
            $param=unserialize(mysql::getValue("SELECT `backend_list` FROM `entity_type` WHERE id='".escape($folder['child_entity_type'])."'"));
            if($param!=false){
                $ord='`item`.`name` ASC';
                $param['select'][]='item.entity_type AS entityType';
                $param['select'][]='item.parent_id AS parentId';
                if($item!=0 && $item!=4) $param['where'][]='`item`.`parent_id`='.escape($item);
                if($item==0 || $item==4) {
                    // Для поиска по всем элементам добавим доп. поля
                    $param['from'][]='`entity` AS `prTable`';
                    $param['where'][]='prTable.id=item.parent_id';
                    $param['select'][]='prTable.name AS parentName';
                    $param['attr']['parentName']=$param['attr']['name'];
                    $param['attr']['parentName']['name']='&nbsp;';
                }
                $param['where'][]='`item`.`name` LIKE ("%'.escape($search).'%")';
                $array=mysql::getArray("SELECT ".implode(',', $param['select'])." FROM ".implode(',', $param['from'])." WHERE ".implode(' AND ', $param['where'])." ORDER BY ".$ord." LIMIT 100");

                $out.='<div style="border:2px solid #ffffff; width:100%; background:#fff3d0; padding:12px 6px; border-radius:4px;">';
                if($item!=4) $dop='<div class="row"><div class="btn" onClick="quickSearch(4);"><i class="ic-search"></i>Искать везде</div></div>';
                // Формируем заголовки полей таблицы
                if($array!=false){
                    $prefix='';
                    $suffix='';
                    $count=count($array);
                    $mCount=$count;
                    if(count==100) $mCount=105;
                    if($mCount>100) {
                        $prefix='более ';
                        $suffix=' Измените запрос для более точного поиска.';
                    }
                    $out.='<div class="row"><b>По вашему запросу найден'.pluralForm($mCount,',o,o').' '.$prefix.$count.' результат'.pluralForm($mCount,',а,ов').$siffix.'</b><br></div>'.$dop.'
                    <table id="t_item" class="cmstable4"><tr class="nodrag nodrop"><th style="width:24px;">&nbsp;</th>'.data::adminTableHeaders($mn, $param['attr'], false, false, false, 'items?=&cat='.$item, "searchArea").'<th style="width:90px!important;">Действия</th></tr>';
                    foreach($array AS $key=>$val){
                        $out.='<tr id="ct'.$val['id'].'"><td style="width:24px;"><i class="ic-file-empty"></i></td>';// Служебные поля
                        foreach($val AS $v=>$k) $out.=data::adminTableField($mn, false, $val, $param, $v, '<b class="hand" onClick="ajaxGet(\'data::edit?='.$val['id'].'&mn='.$mn.'&contentDiv=right&entity='.$val['entityType'].'&cat='.$val['parentId'].'&p=0\')">'.$val['name'].'</b>');
                        $out.='<td><i class="ic-editdoc" title="Редактировать" onClick="ajaxGet(\'data::edit?='.$val['id'].'&mn='.$mn.'&contentDiv=right&entity='.$val['entityType'].'&cat='.$val['parentId'].'&p=0\')"></i>';
                        if($_SESSION['user']['group']<=1) $out.='<i class="ic ic-delete" title="Удалить" onClick="itemDel('.$val['id'].','.$item.',0)"></i>';
                        $out.='</td></tr>';
                    }
                    $out.='</table>';
                }
                else $out.='<div class="row"><p>По вашему запросу ничего не найдено.</p></div>'.$dop;
                $out.='</div>';
            }
        }
        return array('searchblock'=>$out);
    }

    // Генерируем запросы на получение данных из БД
    static function queryGeneratorLIST($attr, $end){
        $select=array();// Поля, которые придут из БД
        $from=array();
        $where=array();
        $optionsCounter=0;
        $linkCounter=0;
        $usersCounter=0;
        $activeFields=array();
        $systemFields=explode(',','id,parent_id,entity_type,hidden,name,date_add,owner');
        $orders=array();// Допустимые поля для сортировки
        $orderRel=array();
        $orderCounter=0;
        $order='';
	    
		// Идем по списку, чтобы сначала получить ГЛАВНУЮ таблицу данных сущности
	    foreach($attr AS $kKey=>$val) {
			if($val['entity_type_id']!=0){
			    $valueTable='data_'.$val['entity_type_alias'];
			    $from["`".$valueTable."` AS t1"]=true;
			    break;
			}
	    }

	    

	    $tCounter=2;
        foreach($attr AS $kKey=>$val){
            if(in_array($val['alias'],$systemFields)==true || $val[$end]==1) $sel=true;
            else $sel=false;
            $wh=false;
            
            // Определяем исходную таблицу
            if($val['entity_type_id']==0) {
                $table='entity';
                $tableAs='t2';
	            $where['t2.id=t1.id']=1;
	            $from['`'.$table.'` AS t2 ON t2.id=t1.id']=1;
            }
            else {
                $table='data_'.$val['entity_type_alias'];
                $tableAs='t1';
                if($sel==true){
                    $from['`'.$table.'` AS t1']=1;
                }
            }
            if($val['type']=='text' || $val['type']=='textarea') {
                $table='text_'.$val['entity_type_alias'];
                $tableAs='t3';
                if($sel===true){
	                $from['`'.$table.'` AS t3 ON t3.id=t1.id']=1;
	                $wh='t3.id=t1.id';
                }
            }

            // Из селектов обработаем только тот, который не содержит ИСТОЧНИК
            if($val['element']=='select') {
                $optionsCounter++;
                if(strlen($val['source'])<6){
                    $table='options';
                    if($sel==true) {
                        $from['`'.$table.'` AS o'.$optionsCounter.' ON o'.$optionsCounter.'.id='.$tableAs.'.`'.$val['alias'].'`']=1;
                        $select[]='`o'.$optionsCounter.'`.value AS `'.$val['alias'].'`';
                    }
                    if(($val[$end]==1 || $end=='select') && $val['sort']==1 && $val['multiple']==0) {
                        $orders[]='`o'.$optionsCounter.'`.`value`';
                        $orderRel[$val['alias']]=$orderCounter;
                        $orderCounter++;
                    }
                }
                else {
                    list($table,$key,$field,$wh)=explode('.',$val['source']);
                    $table=str_replace('%moduleAlias','',$table);
                    if($sel==true) {
                        $from['`'.$table.'` AS o'.$optionsCounter.' ON o'.$optionsCounter.'.`'.$key.'`='.$tableAs.'.`'.$val['alias'].'`']=1;
                        $select[]='o'.$optionsCounter.'.`'.$field.'` AS `'.$val['alias'].'`';
                        $where["o".$optionsCounter.".".$wh]=1;
                    }
                    if(($val[$end]==1 || $end=='select') && $val['sort']==1 && $val['multiple']==0) {
                        $orders[]='o'.$optionsCounter.'.`'.$field.'`';
                        $orderRel[$val['alias']]=$orderCounter;
                        $orderCounter++;
                    }
                }
            }

            $attr[$kKey]['dbTable']=$table;
            $activeFields[$val['alias']]=$val;
            if($val['element']=='user_id'){
                $usersCounter++;
                if($sel==true){
                    $from['`users` AS u'.$usersCounter.' ON u'.$usersCounter.'.id=`'.$tableAs.'`.`'.$val['alias'].'`']=1;
                    $val['dbTable']='users';
                }
                if(($val[$end]==1 || $end=='select') && $val['sort']==1 && $val['multiple']==0) {
                    $orders[]='u'.$usersCounter.'.`name`';
                    $orderRel[$val['alias'].':name']=$orderCounter;
                    $orderCounter++;
                }
            }

            // Преобразование некоторых типов
            $field=$tableAs.'.`'.$val['alias'].'`';
            if($val['type']=='datetime') $field='DATE_FORMAT('.$field.', \'%d.%m.%Y %H:%i:%s\') AS `'.$val['alias'].'`';
            if($val['element']=='id') $order=$field.' DESC ';
            if($val['element']=='order') $order=$field.' ASC ';
            if(($val[$end]==1 || $end=='select') && $val['element']!='select' && $val['element']!='link') {
                // В случае получения списка из текстового поля делаем ему обрезание
                if($val['type']=='text') {
                    if($val['crop_in_list']!=0) {
                        if($sel==true) $select[]='LEFT ('.$field.','.$val['crop_in_list'].') AS `'.$val['alias'].'`';
                    }
                    else {
                        if($sel==true) $select[]=$field;
                    }
                }
                else {
                    if($sel==true) $select[]=$field;
                }
                // сортировка
                if(($val[$end]==1 || $end=='select') && $val['sort']==1 && $val['multiple']==0) {
                    $orders[]=$tableAs.'.`'.$val['alias'].'`';
                    $orderRel[$val['alias']]=$orderCounter;
                    $orderCounter++;
                }
                $activeFields[$val['alias']]=$val;
                if($wh!=false) {
                    if($sel==true) $where[$wh]=1;
                }
            }

            if($val['element']=='link'){
                $linkCounter++;
                list($table,$key,$field,$wh)=explode('.',$val['source']);
                $table=str_replace('%moduleAlias','',$table);
                if($sel==true) {
                    $from['`'.$table.'` AS l'.$linkCounter]=1;
                    $select[]='l'.$linkCounter.'.`'.$field.'` AS `'.$val['alias'].'`';
                    $where['l'.$linkCounter.'.`'.$key.'`=t1.`'.$val['alias'].'`']=1;
                    $where["l".$linkCounter.".".$wh]=1;
                }
            }

        }
        // Формируем массив аттрибутов
        foreach($attr AS $val){
            if(isset($activeFields[$val['alias']])){
                unset($val['crop_in_list']);
                unset($val['frontend_order']);
                unset($val['backend_order']);
                $a=$val['alias'];
                //Удалимм лишние аттрибуты для фронтенда
                if($end=='frontend' || $end=='select'){
                    unset($val['default']);
                    unset($val['view']);
                    unset($val['edit']);
                    unset($val['maxlength']);
                    unset($val['frontend']);
                    unset($val['backend']);
                    unset($val['frontend_list']);
                    unset($val['backend_list']);
                    unset($val['icon']);
                    unset($val['events']);
                    unset($val['dbsize']);
                    unset($val['cssclass']);
                    unset($val['style']);
                }
                if($end=='select'){
                    unset($val['dop']);
                    unset($val['hidden']);
                    unset($val['folderhide']);
                    unset($val['childhide']);
                    unset($val['alias']);
                    unset($val['filterOrder']);
	                unset($val['filterclose']);
                    unset($val['filterType']);
                    unset($val['entity_type']);
                    unset($val['dbTable']);
                }
                $activeFields[$a]=$val;
            }
        }
        // Обработаем сортировку
        $o=array();
        foreach($orders AS $key=>$val){
            $o[$key.'-a']=$val.' ASC';
            $o[$key.'-d']=$val.' DESC';
        }
        return array(
            'select'=>$select,
            'from'=>array_keys($from),
            'where'=>array_keys($where),
            'order'=>$order,
            'attr'=>$activeFields,
            'orders'=>$o,
            'orderRel'=>$orderRel
        );
    }

    // Удаление файла
    static function fileDelete($id=false){
        global $item;
        if($id===false){
            if(isset($item)) $id=$item;
        }
        if(is_array($id)) $where=" IN(".implode(", ",$id).") ";
        else $where="=".$id;
        $array=mysql::getArray("SELECT t1.id, t1.file, t1.ext, t1.entity_id, t1.attribute_id, t2.alias, t2.dop, t2.default, t2.multiple, t2.entity_type_id, t3.entity_type_alias
        FROM `file` AS t1
            JOIN `attributes` AS t2 ON t2.id=t1.attribute_id
            JOIN `entity_type` AS t3 ON t3.id=t2.entity_type_id
        WHERE
            t1.id".$where." LIMIT 1", true);
        if($array!=false){
            $activeFolder=floor($array['entity_id']/100);
            if($array['entity_type_id']==0) $tblName='entity';
            else $tblName='data_'.$array['entity_type_alias'];
            if(strlen($array['dop'])>=12) {
                $z=unserialize($array['dop']);
                if(in_array($array['ext'],array('jpg','jpeg','png','gif','bmp','webp'))){
                    $vv=explode(',',$z['imgsizes']);
                    foreach($vv AS $v){
                        list($suffix,)=explode(':',$v);
                        @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].$suffix.'.'.$array['ext']);
                        // Если изображение в формате WEBP, то удаляем также его копию в формате PNG
	                    @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].$suffix.'.png');
                    }
                    @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].'sys.'.$array['ext']);
	                @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].'sys.png');
                }
                else {
                    @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].'.'.$array['ext']);
	                @unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$array['file'].'.png');
                }
            }
            mysql::query("DELETE FROM `file` WHERE id".$where);
            // Получим имя файла из таблицы инфоблока. В случае его удаления необходимо его принудительно обновить, или заменить на значение по-умолчанию
            $m=mysql::getValue("SELECT `".escape($array['alias'])."` FROM `".escape($tblName)."` WHERE id=".escape($array['entity_id'])." LIMIT 1");
            // Получим первый попавшийся файл и прописывваем его
            if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$activeFolder.'/'.$m) || $m==$array['default']){
                $file=$array['default'];
                $arr=mysql::getArray("SELECT file,ext,type FROM `file` WHERE entity_id=".escape($array['entity_id'])." AND attribute_id=".escape($array['attribute_id'])." ORDER BY `order` ASC");
                if($arr!=false){
                    foreach($arr AS $v){ if($v['type']=='image' && $file=='') { $file=$v['file'].'.'.$v['ext']; break; } }
                    if($file=='') $file=$arr[0]['file'].'.'.$arr[0]['ext'];
                }
                mysql::query("UPDATE `".escape($tblName)."` SET `".escape($array['alias'])."`='".escape($file)."' WHERE id=".escape($array['entity_id'])." LIMIT 1");
            }
        }
        return false;
    }

    // Загрузка файлов (универсальная)
    static function fileUpload($attrId=false){
        global $item;
        if(isset($item)){ $attrId=$item; }
        $out='';
        if(isset($_REQUEST['array'])){
            $entityId=$_REQUEST['array']['id'];
            $workCat=floor($entityId/100);
            // Получаем все аттрибуты инфоблока
            $a=self::entityBackendAttributes($_POST['array']['entity_type']);
            if(isset($a['backend_list'])){
                $aa=unserialize($a['backend_list']);// Получили аттрибуты полей
                $attr=$aa['attr'];
                foreach($attr AS $key=>$v){
                    if($v['element']=='file') {
                        // Если элемент множественный, то надо получить текущее значение ORDER
                        $order=onlyDigit(mysql::getValue("SELECT MAX(`cnt`) FROM `file` WHERE entity_id=".escape($entityId)))+1;
                        $fileParam=unserialize($v['dop']);
                        $fp=explode(',',$fileParam['imgsizes']);
                        $mainFileName='';
                        // Смотрим, есть ли в массиве FILES данное поле
                        if(isset($_FILES[$key])){
                            $insert=array();
                            // Проверим, существует ли папки для хранения файлов
                            if(!file::folderExists($_SERVER['DOCUMENT_ROOT'].'/uploaded/')) mkdir($_SERVER['DOCUMENT_ROOT'].'/uploaded/',0777);
                            if(!file::folderExists($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat)) mkdir($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat,0777);
                            if(is_array($_FILES[$key]['name']) && $_FILES[$key]['name'][0]!='') {
                                foreach($_FILES[$key]['name'] AS $k=>$z){
                                    if($_FILES[$key]['error'][$k]==0){
                                        $fname=$v['alias'].$entityId.time().'_'.$k;
                                        // Получаем расширение файла
                                        $ext=file::getExtension($_FILES[$key]['name'][$k]);
                                        $filedesc='';
                                        if(in_array($ext,array('jpg','jpeg','png','gif','webp'))) {
                                            $ftype='image';
                                            image::makeSquareIcon($_FILES[$key]['tmp_name'][$k],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$fname.'sys.'.$ext,120);
                                            $fsize=0;
                                            foreach($fp AS $f){
                                                list($suffix,$s)=explode(':',$f);
                                                list($w,$h)=explode('*',$s);
                                                if($ext=='jpeg') $ext='jpg';
                                                $file=$fname.$suffix.'.'.$ext;
                                                image::makeImage($_FILES[$key]['tmp_name'][$k],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$file,$w,$h,90);
                                                $imagesize=$GLOBALS['currentImageWidth'].'x'.$GLOBALS['currentImageHeight'];
                                                if($mainFileName=='') { $mainFileName=$file; }
                                                if($fsize==0) $fsize=filesize($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$file);
                                            }
                                        }
                                        else {
                                            $imagesize='0x0';
                                            $ftype='other';
                                            list($filedesc,)=explode('.',$_FILES[$key]['name'][$k]);
                                            copy($_FILES[$key]['tmp_name'][$k],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$fname.'.'.$ext);
                                            $fsize=$_FILES[$key]['size'][$k];
                                        }
                                        if($ext=='jpeg') $ext='jpg';
                                        $insert[]="(".$v['id'].", ".$entityId.", '".escape($fname)."', '".escape($ext)."', '".escape($ftype)."', ".$fsize.", '".$imagesize."', '".escape($filedesc)."', ".$order.", ".$order.")";
                                        $order++;
                                    }
                                    else error("Произошла ошибка при загрузке файла!");
                                }
                            }
                            else {
                                if($_FILES[$key]['error']==0){
                                    $fname=$v['alias'].$entityId.time();
                                    $ext=file::getExtension($_FILES[$key]['name']);
                                    $imagesize='';
                                    if(in_array($ext,array('jpg','jpeg','png','gif','webp'))) {
                                        $ftype='image';
                                        image::makeSquareIcon($_FILES[$key]['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$fname.'sys.'.$ext,120);
                                        $fsize=0;
                                        foreach($fp AS $f){
                                            list($suffix,$s)=explode(':',$f);
                                            list($w,$h)=explode('*',$s);
                                            if($ext=='jpeg') $ext='jpg';
                                            $file=$fname.$suffix.'.'.$ext;
                                            image::makeImage($_FILES[$key]['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$file,$w,$h,90);
                                            if($mainFileName=='') {
                                                $mainFileName=$file;
                                                $imagesize=$GLOBALS['currentImageWidth'].'x'.$GLOBALS['currentImageHeight'];
                                            }
                                            if($fsize==0) $fsize=filesize($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$file);
                                        }
                                    }
                                    else {
                                        $ftype='other';
                                        $imagesize='0x0';
                                        copy($_FILES[$key]['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$fname.'.'.$ext);
                                        $fsize=$_FILES[$key]['size'];
                                    }
                                    if($ext=='jpeg') $ext='jpg';
                                    $insert[]="(".$v['id'].", ".$entityId.", '".escape($fname)."', '".escape($ext)."', '".escape($ftype)."', ".$fsize.", '".$imagesize."', '', ".$order.", ".$order.")";
                                }
                            }
                            if(isset($insert[0])) mysql::query("INSERT INTO `file` (`attribute_id`,`entity_id`,`file`,`ext`,`type`,`filesize`,`imagesize`,`name`,`order`,`cnt`) VALUES ".implode(",",$insert));
                            // Имя таблицы
                            if($v['entity_type_id']==0) $tableName='entity';
                            else $tableName='data_'.$v['entity_type_alias'];
                            // файл для удаления
                            $array=mysql::getValue("SELECT `".escape($v['alias'])."` FROM `".escape($tableName)."` WHERE id=".escape($entityId)." LIMIT 1",true);
                            $save=false;
                            if($array!=""){
                                if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploaded/'.$workCat.'/'.$array)) $save=true;
                            }
                            else $save=true;
                            if($save===true) mysql::query("UPDATE `".escape($tableName)."` SET `".escape($v['alias'])."`='".escape($mainFileName)."' WHERE id=".escape($entityId));
                        }
                    }
                }
            }

            if($attrId!==false){
                $array=mysql::getArray("SELECT * FROM `file` WHERE entity_id=".escape($_POST['array']['id'])." AND attribute_id=".escape($attrId)." ORDER BY `order` ASC");
                if($array!=false) {
                    $file=mysql::getArray("SELECT 
                    t1.alias AS attralias, 
                    t1.multiple AS multiple, 
                    t1.`default` AS attrdefault, 
                    t1.name AS attrname, 
                    t1.view,
                    t1.entity_type_id AS attr_entity_type, 
                    t1.dop AS attrdop, 
                    t2.entity_type_alias 
                        FROM `attributes` AS t1
                            JOIN `entity_type` AS t2 ON t2.id = t1.entity_type_id
                        WHERE t1.id = ".escape($attrId)."
                    LIMIT 1",true);
                    // Получим основной файл
                    if($file['attr_entity_type']==0) $tblName='entity';
                    else $tblName='data_'.$file['entity_type_alias'];
                    $mainFile=mysql::getValue("SELECT ".escape($file['attralias'])." FROM `".escape($tblName)."` WHERE id=".escape($_POST['array']['id'])." LIMIT 1");
                    $out.=self::showFiles($array, $mainFile, $file['view'], unserialize($file['attrdop']));
                    if($file['multiple']==1) $out.='<div class="row"><input type="file" multiple="multiple" name="'.$file['attralias'].'[]" onChange="ajaxPost(\'itemForm'.$file['attr_entity_type'].'\',\'data::fileUpload?='.$attrId.'&attrAlias='.$file['attralias'].'\')"></div>';
                }
                cacheClear();
                return array('field'.$attrId=>$out);
            }
        }
    }



    // Форма редактирования аттрибута
    static function attributeForm($array){
        $attr['id']=0;
        $attr['default']='';
        $attr['alias']='';
        $attr['element']='id';
        $attr['type']='int';
        $attr['dbsize']='';
        $attr['edit']=0;
        $attr['cssclass']='';
        $attr['style']='';
        $attr['name']='';
        $attr['attr_group']='default';
        $attr['multiple']=0;
        $attr['entity_type_id']=0;
        $attr['frontend']=1;
        $attr['backend']=1;
        $attr['sort']=0;
        $attr['frontend_list']=1;
        $attr['backend_list']=1;
        $attr['crop_in_list']=0;
        $attr['unit']='';
        $attr['icon']='';
        $attr['source']='';
        $attr['editSource']='';
        $attr['folderhide']=0;
        $attr['childhide']=0;
        $attr['filter']=0;
        $attr['filterOrder']=0;
	    $attr['filterclose']=0;
        $attr['filterType']=3;
        $attr['javascript']='';
        $attr['required']=0;
        $attr['optorder']=0;
        $attr['quickEdit']=0;
        $elCounter=0;
        $optCounter=0;
        $form=array();
        $iconSelector=false;
        $lockCss=false;
        $lockStyle=false;
        $lockHidden=true;
        $filterEnable=false;
        $lockFolder=false;
        $lockChilds=false;

        if($array['id']!=0){
            $array=mysql::getArray("SELECT * FROM `attributes` WHERE id='".escape($array['id'])."' LIMIT 1", true);
            $form[]=array('name'=>'attr[oldalias]', 'type'=>'hidden', 'value'=>$array['alias']);
        }
        else {
            if($attr['type']=='decimal') $attr['dbsize']='(18,2)';
        }


        foreach($array AS $key=>$val){ $attr[$key]=$val; }
        $form[]=array('name'=>'action', 'id'=>'action', 'type'=>'hidden', 'value'=>'attributeSave');
        $form[]=array('name'=>'attr[id]', 'type'=>'hidden', 'value'=>$attr['id']);
        $form[]=array('name'=>'attr[element]', 'type'=>'hidden', 'value'=>$attr['element']);
        if($attr['element']=='link'){
            $form[]=array('name'=>'attr[realElement]', 'type'=>'hidden', 'value'=>'link');
        }
        $form[]=array('name'=>'attr[required]', 'type'=>'checkbox', 'class'=>'checkbox', 'label'=>'Обязательно к заполнению', 'value'=>$attr['required']);
        $form[]=array('name'=>'attr[name]', 'label'=>'Название', 'class'=>'size-xl', 'maxlength'=>128, 'type'=>'text', 'value'=>$attr['name']);
        $form[]=array('name'=>'attr[alias]', 'label'=>'Переменная', 'type'=>'text', 'value'=>$attr['alias']);
        $form[]=array('name'=>'attr[entity_type_id]', 'type'=>'hidden', 'value'=>$attr['entity_type_id']);
        $form[]=array('name'=>'attr[attr_group]', 'label'=>'Аттрибут в группе', 'class'=>'size-m', 'maxlength'=>32, 'type'=>'text', 'value'=>$attr['attr_group']);

        // CHECKBOX
        if($array['element']=='checkbox'){
            $iconSelector=true;
            $filterEnable=true;
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'enum');
            $form[]=array('name'=>'attr[default]', 'label'=>'Состояние по-умолчанию', 'type'=>'checkbox', 'value'=>$attr['default']);
        }
        // SELECT, RADIO, LINK
        elseif($array['element']=='select' || $array['element']=='radio' || $array['element']=='link'){
            $iconSelector=true;
            $filterEnable=true;
            $elCounter++;
            $options='';
            
            $vals=array();
            if($attr['id']!=0){
                // Получаем значения
                $v=mysql::getSpecial("SELECT id,value AS name FROM `options` WHERE attr_id=".$attr['id']." ORDER BY id ASC");
                if(is_array($v)) $vals=array_values($v);
            }
            if(is_array($vals)){
                foreach($vals AS $val){
                    $optCounter++;
                    $options.='<div id="opt'.$optCounter.'" class="btn-group"><input type="text" name="attr[options][]" class="size-xl" maxlength="64" value="'.htmlspecialchars($val).'"><div class="btn" onclick="domRemove(\'opt'.$optCounter.'\')"><i class="ic-minus"></i></div></div>';
                }
            }

            if($array['element']!='link') $form[]=array('name'=>'attr[unit]', 'label'=>'Единица измерения', 'type'=>'text', 'value'=>$attr['unit']);
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'int');
            $form[]='-';

            if($array['element']!='link') $form[]=array('type'=>'html', 'value'=>'<tr><td valign="top"><label>Значения</label></td><td><div id="sgrp'.$elCounter.'">'.$options.'</div><div class="row"><div class="btn" onClick="newAttrSelect(\'sgrp'.$elCounter.'\',\''.$attr['alias'].'\')"><i class="ic-plus"></i>Добавить значения</div></div></td></tr>');

            if(strlen($attr['source'])>=16) $srcdat=explode(".",$attr['source']);
            else $srcdat=array(0=>'',1=>'',2=>'');
            $form[]=array('type'=>'html','value'=>'<tr><td><label>Получить значения из БД:</label></td><td><div class="info">Для правильного функционирования необходимо извлечь массив с ключами id и name. Пример запроса: <br>SELECT `key` AS `id`, `value` AS `name` FROM `table` WHERE 1=1</div></td></tr>
                <tr><td></td><td>
                    <div class="btn-group">
                        <div class="label">SELECT</div><input type="text" id="fldsel1" onKeyUp="updateListSrc()" onKeyPress="updateListSrc()"  style="width:150px" value="'.$srcdat[1].','.$srcdat[2].'">
                        <div class="label">FROM</div><input type="text" id="fldsel0" onKeyUp="updateListSrc()" onKeyPress="updateListSrc()"  style="width:150px" value="'.$srcdat[0].'">
                        <div class="label">WHERE</div><input type="text" id="fldsel2" onKeyUp="updateListSrc()" onKeyPress="updateListSrc()"  style="width:150px" value="'.$srcdat[3].'">
                    </div>
                    <input type="hidden" id="listsrc" name=attr[source] value="'.$attr['source'].'">
                </td>
            </tr>
            ');
            // Добавим функцию JS для вставки значения в поле listsrc

            $form[]=array('name'=>'attr[default]', 'label'=>'Значение по-умолчанию', 'type'=>'text', 'value'=>$attr['default']);
            $form[]=array('name'=>'attr[optorder]', 'label'=>'Сортировка значений', 'type'=>'select', 'value'=>$attr['optorder'], 'options'=>array(0=>'В порядке возрастания значений', 1=>'В порядке создания'));
            if($array['element']!='link') $form[]=array('name'=>'attr[edit]', 'label'=>'Редактирование списка', 'type'=>'checkbox', 'value'=>$attr['edit'], 'class'=>'switch');
            $form[]='-';
            $form[]=array('name'=>'attr[element]', 'label'=>'Отображать как', 'type'=>'select', 'value'=>$attr['element'], 'options'=>array('select'=>'Список','radio'=>'Радио-кнопки'));
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        // INT
        elseif($array['element']=='int' || $array['element']=='decimal'){
            $iconSelector=true;
            $filterEnable=true;
            $lockHidden=false;
            if(!isset($attr['maxlength'])) $attr['maxlength']=20;
            if($array['id']==0) {
                $attr['default']=0;
                if($array['element']=='decimal') {
                    $attr['default']=0.0;
                    $attr['dbsize']='(12,2)';
                }
            }
            $form[]=array('name'=>'attr[unit]', 'label'=>'Единица измерения', 'type'=>'text', 'value'=>$attr['unit']);
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>$array['element']);
            $form[]=array('name'=>'attr[maxlength]', 'label'=>'Максимальная длина (символов)', 'maxlength'=>2, 'type'=>'text', 'class'=>'size-xs', 'value'=>$attr['maxlength'], 'onkeypress'=>'return inputNumber(event)');
            if($array['element']=='decimal') $form[]=array('name'=>'attr[dbsize]', 'label'=>'Размер в БД (целые, дробные)', 'type'=>'text', 'value'=>$attr['dbsize']);
            $form[]=array('name'=>'attr[default]', 'label'=>'Значение по-умолчанию', 'type'=>'text', 'maxlength'=>20, 'value'=>$attr['default'], 'class'=>'size-s');
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
	        $form[]=array('name'=>'attr[quickEdit]', 'class'=>'checkbox', 'label'=>'Разрешить быстрое редактирование', 'type'=>'checkbox', 'value'=>$attr['quickEdit']);
        }
        // CHILD
        elseif($array['element']=='child'){
            $iconSelector=false;
            $lockCss=true;
            $lockStyle=true;
            if($array['id']==0) { $attr['multiple']=1; $attr['backend_list']=0; $attr['backend']=0; $attr['frontend']=0; $attr['frontend_list']=0; }
            $form[]=array('type'=>'html','value'=>'<tr><td></td><td><div class="info">ВНИМАНИЕ! Этот тип аттрибута представляет возможность присоединить к элементу группу дочерних элементов (например, для того, чтобы создать товар, состоящий из группы других товаров).</div></td></tr>');
            $form[]=array('name'=>'attr[attr_source]', 'label'=>'Тип потомка', 'type'=>'select', 'value'=>$array['attr_source'], 'options'=>array(0=>'-НЕ ЗАДАНО-')+mysql::getSpecial("SELECT id, entity_type AS name FROM `entity_type` WHERE id>0 ORDER BY `entity_type` ASC"));
            $form[]=array('name'=>'attr[maxlength]', 'type'=>'hidden', 'value'=>9);
            $form[]=array('name'=>'attr[default]', 'type'=>'hidden', 'value'=>0);
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>$array['element']);
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        // VARCHAR и все его подтипы
        elseif($array['element']=='varchar'){
            $iconSelector=true;
            if(!isset($attr['maxlength'])) $attr['maxlength']=128;
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'varchar');
            if($attr['view']=='youtube'){
                if($attr['id']==0) $attr['icon']="ic-play";
                $form[]=array('name'=>'attr[view]', 'type'=>'hidden', 'value'=>'youtube');
                $form[]=array('name'=>'attr[maxlength]', 'type'=>'hidden', 'value'=>255);
                $form[]=array('name'=>'attr[default]', 'type'=>'hidden', 'value'=>$attr['default']);
            }
            elseif($attr['view']=='googlemap'){
                if($attr['id']==0) $attr['icon']="ic-earth";
                $form[]=array('name'=>'attr[view]', 'type'=>'hidden', 'value'=>'googlemap');
                $form[]=array('name'=>'attr[maxlength]', 'type'=>'hidden', 'value'=>128);
                $form[]=array('name'=>'attr[default]', 'type'=>'hidden', 'value'=>$attr['default']);
            }
            else {
                $lockHidden=false;
                $form[]=array('name'=>'attr[unit]', 'label'=>'Единица измерения', 'type'=>'text', 'value'=>$attr['unit']);
                $form[]=array('name'=>'attr[maxlength]', 'label'=>'Макс. длина строки', 'type'=>'text', 'class'=>'size-s', 'value'=>$attr['maxlength'], 'onkeypress'=>'return inputNumber(event)');
                $form[]=array('name'=>'attr[default]', 'label'=>'Значение по-умолчанию', 'type'=>'text', 'value'=>$attr['default']);
            }
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
            if($attr['multiple']==1) $lockHidden=true;
        }
        elseif($array['element']=='textarea'){
            $lockCss=true;// Блокируем поле КЛАСС CSS
            $lockStyle=true;// Блокируем поле КЛАСС CSS
            if($attr['id']==0) {
                $attr['cssclass']='axiom';
                $attr['style']='width:100%; height: 120px;';
            }
            $form[]=array('name'=>'attr[cssclass]', 'id'=>'css'.$array['id'], 'label'=>'Отображать как', 'type'=>'select', 'value'=>$attr['cssclass'], 'options'=>array(''=>'Обычное поле','axiom'=>'Редактор','basic'=>'Редактор-basic', 'mini'=>'Редактор-mini'), 'onchange'=>'changeTextareaType('.$array['id'].')');
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'textarea');
            $form[]=array('name'=>'attr[maxlength]', 'label'=>'Максимальная длина', 'type'=>'text', 'class'=>'size-s', 'value'=>$attr['maxlength'], 'onkeypress'=>'return inputNumber(event)');
            $form[]=array('name'=>'attr[style]', 'id'=>'class'.$array['id'], 'label'=>'Стиль CSS', 'type'=>'text', 'class'=>'size-xl', 'value'=>$attr['style']);
            $form[]=array('name'=>'attr[default]', 'label'=>'Значение по-умолчанию', 'type'=>'textarea', 'maxlength'=>64000, 'value'=>$attr['default'], 'class'=>'size-xl', 'style'=>'height:120px;');
        }
        // BUTTON Кнопка в админ-панели
        elseif($array['element']=='button'){
            $lockCss=true;// Блокируем поле КЛАСС CSS
            $lockStyle=true;// Блокируем поле КЛАСС CSS
            $filterEnable=false; // Блокируем фильтр
            $iconSelector=true; // Блокируем выбор иконки
            $attr['frontend']=0;
            $attr['backend']=0;
            $attr['frontend_list']=0;
            $attr['backend_list']=0;
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'button');
        }
        // FILE
        elseif($array['element']=='file'){
            $iconSelector=true;
            if($array['id']==0){
                $attr['default']='';
                $attr['fileext']='jpg,jpeg,png,webp,zip,rar,7z,doc,docx,pdf,djvu,xls,xlsx';
                $attr['imgsizes']=':1200*800,s:400*300';
            }
            else {
                if(strlen($attr['dop'])>=16) {
                    $d=unserialize($attr['dop']);
                    $attr['fileext']=$d['fileext'];
                    $attr['imgsizes']=$d['imgsizes'];
                }
            }
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'file');
            $form[]=array('name'=>'attr[maxlength]', 'type'=>'hidden', 'value'=>'32');
            $form[]=array('name'=>'attr[fileext]', 'label'=>'Разрешенные типы файлов', 'type'=>'text', 'class'=>'size-l', 'value'=>$attr['fileext']);
            $form[]=array('name'=>'attr[imgsizes]', 'label'=>'Размеры изображений', 'description'=>'Через запятую задаются размеры для преобразования изображений в следующем формате: Суффикс_имени_файла:Ширина*Высота', 'type'=>'text', 'class'=>'size-l', 'value'=>$attr['imgsizes']);
            $form[]=array('name'=>'attr[view]', 'id'=>'view'.$array['id'],'label'=>'Миниатюры в форме (Админ)', 'type'=>'select', 'class'=>'size-xl', 'value'=>$attr['view'], 'options'=>array(''=>'Системная миниатюра, обрезанный квадрат','proportial'=>'Без обрезки'));
            $form[]=array('name'=>'attr[default]', 'type'=>'hidden', 'value'=>$attr['default']);
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        // DATEPICKER
        elseif($array['element']=='date'){
            $iconSelector=true;
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'date');
            $form[]=array('name'=>'attr[default]', 'label'=>'Значение по-умолчанию<br>', 'type'=>'text', 'value'=>$attr['default'], 'placeholder'=>'ДД.ММ.ГГГГ, %today%, или пустое поле');
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        elseif($array['element']=='dateandtime'){
            $iconSelector=true;
	        $lockCss=true;// Блокируем поле КЛАСС CSS
	        $lockStyle=true;// Блокируем поле КЛАСС CSS
	        $filterEnable=false; // Блокируем фильтр
            $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'dateandtime');
            $form[]=array('name'=>'attr[default]', 'type'=>'hidden', 'value'=>$attr['default']);
            $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        elseif($array['element']=='color'){
	        $iconSelector=true;
	        $lockFolder=true;
	        $lockChilds=true;
	        $form[]=array('name'=>'attr[type]', 'type'=>'hidden', 'value'=>'varchar');
	        $form[]=array('name'=>'attr[dbsize]', 'type'=>'hidden', 'value'=>'16');
	        $form[]=array('name'=>'attr[default]', 'type'=>'text', 'label'=>'Значение по умолчанию', 'value'=>$attr['default'], 'class'=>'size-s');
	        $form[]=array('name'=>'attr[multiple]', 'label'=>'Множественный', 'type'=>'checkbox', 'class'=>'switch', 'value'=>$attr['multiple']);
        }
        if($lockCss===false) $form[]=array('name'=>'attr[cssclass]', 'label'=>'Класс CSS', 'type'=>'text', 'value'=>$attr['cssclass']);
        if($lockStyle===false) $form[]=array('name'=>'attr[style]', 'label'=>'Стиль CSS', 'type'=>'text', 'class'=>'size-xl', 'value'=>$attr['style']);
        $form[]='-';
        if($lockHidden===false) $form[]=array('name'=>'attr[hidden]', 'label'=>'Скрытое поле', 'class'=>'checkbox', 'type'=>'checkbox', 'value'=>$attr['hidden']);
        $form[]=array('name'=>'attr[frontend]', 'label'=>'Извлекать на сайте', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['frontend']);
        $form[]=array('name'=>'attr[frontend_list]', 'label'=>'Отображать в списке на сайте', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['frontend_list']);
        $form[]=array('name'=>'attr[backend]', 'label'=>'Извлекать в админке', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['backend']);
        $form[]=array('name'=>'attr[backend_list]', 'label'=>'Отображать в списке админ-панели', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['backend_list']);
        
        if($lockFolder===false) $form[]=array('name'=>'attr[folderhide]', 'label'=>'Не отображать в свойствах папки', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['folderhide']);
        if($lockChilds===false) $form[]=array('name'=>'attr[childhide]', 'label'=>'Не отображать в свойствах потомков', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['childhide']);
        $form[]='-';
        if($filterEnable==true) {
            $form[]=array('name'=>'attr[filter]', 'label'=>'Фильтрация по параметру', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['filter']);
	        $form[]=array('name'=>'attr[filterclose]', 'label'=>'Раскрывающийся блок в фильтре', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['filterclose']);
            $form[]=array('name'=>'attr[filterOrder]','label'=>'Порядок в фильтре','type'=>'text', 'class'=>'size-s', 'value'=>$attr['filterOrder'],'maxlength'=>4,'onkeypress'=>'return inputNumber(event)');
            $form[]=array('name'=>'attr[filterType]','label'=>'Вид фильтра','type'=>'select', 'options'=>array(0=>'-НЕ ЗАДАНО-',1=>'Диапазон (от - до)',2=>'Диапазон (от)',3=>'Диапазон (до)',4=>'2 поля ввода цифр (от - до)',5=>'Выбор из списка',6=>'Выбор всех элементов из списка',7=>'Чекбокс'), 'value'=>$attr['filterType']);
        }
        $form[]='-';
        if($attr['element']!='textarea') $form[]=array('name'=>'attr[sort]', 'label'=>'Сортировка', 'type'=>'checkbox', 'class'=>'checkbox', 'value'=>$attr['sort']);
        else $form[]=array('name'=>'attr[sort]', 'type'=>'hidden', 'value'=>0);

        if($iconSelector===true){
            $form[]=array('type'=>'html', 'value'=>'<tr><td><label>Иконка</label></td><td id="iconSelector"><div class="btn-group"><input type="text" name="attr[icon]" id="iconSelField" class="size-m" value="'.htmlspecialchars($attr['icon']).'"><div class="btn" onclick="iconWindow(previousSibling.id)" title="Выбрать иконку"><i class="'.$attr['icon'].'"></i></div></div></td></tr>');
        }
        $form[]=array('type'=>'textarea', 'name'=>'attr[javascript]', 'label'=>'События JS', 'value'=>htmlspecialchars($attr['javascript']), 'class'=>'size-xl code');
        $form[]=array('type'=>'textarea', 'name'=>'attr[editSource]', 'label'=>'Получение  данных из БД при редактировании', 'value'=>htmlspecialchars($attr['editSource']), 'class'=>'size-xl code', 'style'=>'height:100px;');
        return $form;
    }

    // Получаем форму редактирования аттрибута
    static function fieldSel(){
        global $item, $entity;

        if(isset($item) && isset($entity)){
            $_POST['attr']['id']=$item;
            $_POST['attr']['entity_type_id']=$entity;
        }
        $attr=$_POST['attr'];
        $entityTypeId=$attr['entity_type_id'];

        // Если это индекс сортировки, то сразу создадим поле order и присвоим ему все нужные значения
        if($attr['element']=='order'){
            if($attr['id']==0){
                $m=mysql::getArray("SELECT id FROM `attributes` WHERE alias='order' AND element='order' AND entity_type_id=".$attr['entity_type_id']." LIMIT 1");
                if($m===false){
                    mysql::startTransaction();
                    $typeAlias=mysql::getValue("SELECT `entity_type_alias` AS alias FROM `entity_type` WHERE id=".escape($attr['entity_type_id'])." LIMIT 1",true);
                    $o=mysql::getArray("SELECT MAX(frontend_order) AS `front`, MAX(backend_order) AS `back` FROM `attributes` WHERE `entity_type_id`=".escape($attr['entity_type_id']),true);
                    if($o===false){ $o=array('front'=>1, 'back'=>1); }
                    mysql::query("INSERT INTO `attributes` (`attr_group`, `default`, `alias`, `element`, `type`, `attr_source`, `view`, `edit`, `maxlength`, `cssclass`, `style`, `name`, `multiple`, `entity_type_id`, `frontend`, `backend`, `required`, `sort`, `frontend_list`, `backend_list`, `crop_in_list`, `frontend_order`, `backend_order`, `unit`, `icon`, `source`, `editSource`, `events`, `dbsize`, `dop`, `hidden`, `folderhide`, `childhide`, `filter`, `filterOrder`, `filterclose`, `filterType`, `javascript`, `optorder`) VALUES ('default', '1', 'order', 'order', 'int', '0', '', '0', '0', '', '', 'Порядок сортировки', '0', '".escape($attr['entity_type_id'])."', '1', '1', '0', '1', '1', '1', '0', '".escape($o['front'])."', '".escape($o['back'])."', '', '', '', '', '', '', '', '0', '0', '0', '0', '0', '0', '', '0')");
                    mysql::query("ALTER TABLE `data_".escape($typeAlias)."` ADD `order` bigint(20) NULL DEFAULT '1' COMMENT 'Сортировка'");
                    mysql::indexAdd('data_'.$typeAlias, 'order');
                    mysql::stopTransaction();
                    self::queryGenerator($attr['entity_type_id']);
                    $GLOBALS['item']=$attr['entity_type_id'];
                    return self::attr();
                }
                else {
                    ajax::dialogAlert('ВНИМАНИЕ! Аттрибут "order" уже есть в этой сущности!');
                    return false;
                }
            }
        }

        if($attr['id']==0) $formName='Добавление аттрибута: '.$attr['element'];
        else $formName='Свойства аттрибута';

        // Преобразование типа поля
        if($attr['element']=='youtube' || $attr['element']=='googlemap'){
            $attr['view']=$attr['element'];
            $attr['element']='varchar';
        }

        $fields=self::attributeForm($attr);
        $fields[]=array('type'=>'html', 'value'=>'<tr><td>&nbsp;</td><td><div class="btn" onClick="ajaxPost(\'atForm\',\'data::attributeSave\')"><i class="ic-save"></i>Сохранить</div><div class="btn" onclick="ajaxGet(\'data::attr?='.$entityTypeId.'&edited='.$item.'\')"><i class="ic-undo"></i>Отмена</div></td></tr>');

        $fr=new form($fields);
        $fr->id='atForm';
        $fr->enctype='multipart/form-data';
        $fr->type="horizontal";
        $fr->method='POST';
        return array('right'=>'<div class="movable">
        <div class="row" style="background:#dddddd; margin-bottom:20px; padding:6px;">'.$formName.'</div>
        <div id="fstatus"></div>'.$fr->show().'</div>');
    }

    // Формирование формы редактирования сущности
    static function edit(){
        global $mn, $contentDiv, $item, $entity, $parent, $p, $cat, $asChild, $attrId, $lastSavedId;
        if(!isset($asChild)) $asChild=false;
        if(!isset($p)) $p=0;
        $link='&p='.$p;
        $out='';
        $array=array();
        if(isset($item)) $array['id']=$item;
        if(isset($parent)) $array['parent_id']=$parent;


        $ajax='ajaxGet(\'items?='.$item.$link.'\',\''.$contentDiv.'\',\''.$mn.'\')';
        if(isset($cat)) { $ajax='catOpen('.$cat.','.$p.')'; $array['parent_id']=$cat; }

        // Получаем аттрибуты инфоблока для генерации формы
        $fields=data::editForm($mn,$entity,$array);

        // Если ID=0 то добавим еще один параметр, чтобы удалить сущность, если менеджер
        // отменит сохранение
        if($array['id']==0 && $entity!=7){
            if(isset($cat)){
                $ajax='lastRemove('.$cat.','.$p.','.$lastSavedId.')';
            }
        }
        if(isset($cat)) $fields[]=array('type'=>'hidden', 'name'=>'cat', 'value'=>$cat);
        if(isset($p)) $fields[]=array('type'=>'hidden', 'name'=>'p', 'value'=>$p);
        $buttons='<div class="btn-big orange" onClick="ajaxPost(\'itemForm'.$entity.'\',\''.$mn.'::save\')"><i class="ic-save"></i>Сохранить</div><div class="btn-big" onClick="'.$ajax.'"><i class="ic-undo"></i>Отмена</div>';
        if($_SESSION['user']['status']<=1){
            $buttons.='<div class="button" style="float:right; margin-right:20px;" title="Атрибуты инфоблока" onClick="ajaxGet(\'data::attr?='.$entity.'&mn='.$mn.'\')"><i class="ic-tools" style="margin-right:0 !important;"></i></div>';
        }


        if($asChild!=false){
            $fields[]=array('type'=>'hidden', 'name'=>'asChild', 'value'=>$asChild);// При сохранении дочернего элемента добавляем поле-признак
            $fields[]=array('type'=>'hidden', 'name'=>'attrId', 'value'=>$attrId);
            $buttons='<div class="btn-big" onClick="ajaxPost(\'itemForm'.$entity.'\',\'data::childSave\')"><i class="ic-save"></i>Сохранить</div><div class="btn-big" onclick="domRemove(\'childEdWin\')"><i class="ic-undo"></i>Отмена</div>';
        }
        else{
            $buttons='<div style="position:fixed; bottom:0; left:0; padding:20px 0 10px 0; background:rgba(24,26,34,0.85); width:100%; text-align:center; box-sizing:border-box; text-align:center;">'.$buttons.'</div>';
        }
        $fields[]=array('type'=>'hidden', 'name'=>'action', 'value'=>'save');
        $fields[]=array('type'=>'html', 'value'=>'<tr><td>&nbsp;</td><td style="height:100px;">&nbsp;'.$buttons.'</td></tr>');

        $f=new form($fields);
        $f->id='itemForm'.$entity;
        $f->enctype='multipart/form-data';
        $f->type="horizontal";
        $f->method='POST';
        $out.=$f->show();
        if($asChild===false){
            return array($contentDiv=>$out);
        }
        else {
            ajax::window('<div style="width:1000px; height:600px; overflow-x:scroll;">'.$out.'</div>',true,'childEdWin');
            return false;
        }
    }

    /**
     * Удаление только что созданной сущности при отмене
     */
    static function lastDelete(){
        global $item, $cat, $p, $noShow;
        $noShow=true;
        if(isset($_SESSION['lastSavedId']) && $_SESSION['lastSavedId']==$item){
            unset($_SESSION['lastSavedId']);
            self::delete($item);
        }
        ajax::javascript('catOpen('.$cat.','.$p.')');
        return false;
    }

    /**
     * Удаление сущности
     * @param $mn
     * @param $entityId
     * @return bool
     */
    static function delete($entityId=false){
        global $item, $cat, $p, $noShow, $frontend;

        if($entityId===false && isset($item)) $entityId=$item;
        if(!is_array($entityId)){
            $entityId=array($entityId);
        }
        // Получим идентификаторы всех потомков элемента, чтобы удалить и их
        $entityId=mysql::getList("SELECT id FROM `entity` WHERE id IN(".implode(",",$entityId).") OR parent_id IN(".implode(",",$entityId).")");
        if($ids!=false) $entityId=$entityId+$ids;


        $where=' IN ('.implode(', ',$entityId).')';
        foreach($entityId AS $val){
            ajax::domRemove("ct"+$val);
            ajax::domRemove("ctl"+$val);
        }
        // Удаляем файлы и множественные данные инфоблока
        $array=mysql::getList("SELECT id FROM `file` WHERE entity_id".$where);
        if($array!=false) self::fileDelete($array);
        mysql::query("DELETE FROM `multidata` WHERE entity_id".$where);
        mysql::query("DELETE FROM `entity` WHERE id".$where);
        cacheClear();
        if(!isset($noShow)) ajax::javascript("catOpen(".$cat.")");
        else {
            if(isset($frontend)){
                ajax::javascript('ajaxGet(\''.$frontend.'::items?=&cat='.$cat.'&p='.$p.'\')');
            }
        }
        return true;
    }

// Создание нового инфоблока
static function entityCreate(){
    global $item, $alias, $nextAct;
    $out='';
    $entityTypeId=0;
    if(mb_strlen($item,'utf-8')<3) {
        ajax::dialogAlert('Слишком короткое имя');
        return false;
    }
    else {
        $entityTypeId=self::typeAdd($item,$alias);
        if($entityTypeId!=false) self::attributeSave(array('id'=>0, 'alias'=>'editor', 'type'=>'int', 'element'=>'user_id', 'name'=>'Редактор','frontend'=>0,'backend'=>1,'sort'=>0,'entity_type_id'=>$entityTypeId,'default'=>0));
    }
    // Если тип инфоблока создается из свойств папки
    if(!isset($nextAct)){
        $out.=createSelect("array[child_entity_type]",$entityTypeId,mysql::getSpecial("SELECT id, entity_type AS name FROM `entity_type` WHERE id>0 ORDER BY name ASC"));
    }
    else {
        $item=0;
        return self::settings();
    }
    return array('right'=>$out);
}

// Отображение списка настроек модуля
static function attr(){
    global $item, $entityFields, $ent, $nowindow, $edited;
    $nowindow=true;// Запрет вывода атрибутов в окне
    $info=''; $out='';
    $ent=$item;
    $pageName='Глобальные аттрибуты';
    if(!$item || $item==0) {
        $item=0;
        $info='<div class="info">Внимание! Перечисленные здесь аттрибуты являются ОБЩИМИ для всех инфоблоков данного модуля. Это значит, что добавление новых параметров в эту таблицу приведет к добавлению соответствующих характеристик ко всем эелементам модуля.</div>';
    }
    if($item!=0) $pageName=mysql::getValue("SELECT CONCAT(entity_type,' <span>(',entity_type_alias,')</span>') AS entity_type FROM `entity_type` WHERE id='".escape($item)."' LIMIT 1").' - Аттрибуты инфоблока';
    $out.='<div id="newField" class="row">
    <div class="btn" onClick="ajaxGet(\'data::settings?=0&edited='.$item.'\')"><i class="ic-return"></i>Вернуться к списку инфоблоков</div>
    <div class="btn" onClick="ajaxGet(\'data::attrSelect?='.$item.'\')"><i class="ic-plus"></i>Добавить аттрибут</div>
    <div class="btn" onClick="ajaxGet(\'data::filterOrder?='.$item.'\')"><i class="ic-filter"></i>Порядок опций в фильтре</div>
    </div>'.data::entityFieldsList();
    // Если в переменной "edited" есть что-либо,
    // то надо отмотать экран к элементу
    if(isset($edited)){
        ajax::javascript('var scIntoV=getId("fl'.$edited.'");scIntoV.style.background="#ffe9c8";scIntoV.scrollIntoView();');
    }
    return array('right'=>'<h3>'.$pageName.'</h3>'.$info.'<div class="row" id="mdlset">'.$out.'</div>');
}

    /**
     * Сохранение сущности в БД
     * @param $array
     * @return bool|int|string
     * @internal param $mn
     */
    static function entitySave($array){
        global $error;
        $entityType=$array['entity_type'];
        $error=false;
        $param=self::entityBackendAttributes($entityType);
        $opt=unserialize($param['backend_list']);
        // Предварительная обработка некоторых полей,
        // Значение поля типа DATEENDTIME формируется из значений $_POST[$key.'-date'] и $_POST[$key.'-time']
        foreach($array AS $key=>$val) {
            if ($opt['attr'][$key]['element'] == 'dateandtime' && $opt['attr'][$key]['multiple'] == 0) {
                $val = $_POST[$key.'-date'];
                $tval = $_POST[$key.'-time'];
                if ($val != '') {
                    $ddl = explode(".", $val);
                    if (strlen($ddl[0]) == 2) $val = $ddl[2] . '-' . $ddl[1] . '-' . $ddl[0];
                    else $val = $ddl[0] . '-' . $ddl[1] . '-' . $ddl[2];
                    if ($tval != '') $val .= ' ' . $tval . ':00';
                }
                else $val = NULL;
                $array[$key] = $val;
            }
        }
        // Проверяем поля обязательные для заполнения 'AxiomREQFIELDS'
        $AxiomREQFIELDS=array();
        if(isset($array['AxiomREQFIELDS'])){
            $AxiomREQFIELDS=$array['AxiomREQFIELDS'];
            unset($array['AxiomREQFIELDS']);// Из массива $array удаляем AxiomREQFIELDS, его не надо сохранять
        }
        // Выполняем действия перечисленные в $param['before_save']
        // и если получена ошибка, то выводим окно и останавливаемся
        if(strlen($param['before_save'])>=6) eval($param['before_save']);
        if($error!=false){
            ajax::dialogAlert($error);
            return false;
        }
        $tables=array();
        // Обрабатываем служебные поля
        $specialFields=array('parent_id','entity_type','datetime','owner','editor','order');
        $multiExists=false;
        $multiOld=array();
        $allMultiFields=array();// Список всех существующих множественных полей
        // Получаем все множественные значения инфоблока
        $mfData=mysql::getArray("SELECT id,attribute_id,value_varchar,value_date,value_int,value_decimal FROM `multidata` WHERE entity_id=".escape($array['id']));
        // Узнаем, есть ли поля со множественными значениями
        $mfTypes=array();
        foreach($opt['attr'] AS $val){
            if($val['multiple']==1){
                $allMultiFields[]=$val['id'];
                $mfTypes[$val['id']]=$val['type'];
                $multiExists=true;
            }
        }
        $multiVal=array();
        // Проверка поля ALIAS
        if(isset($array['alias'])) {
            if($array['alias']=='') $array['alias']=translit(trim($array['name']));
            else $array['alias']=translit(trim($array['alias']));
        }
        

        
        // Формируем список таблиц, в которые будут записаны данные
        foreach($array AS $key=>$val){
            $element=$opt['attr'][$key]['element'];
            $entityId=$opt['attr'][$key]['entity_type_id'];
            $sourceTable='entity';
            $multiple=$opt['attr'][$key]['multiple'];
            $attrId=$opt['attr'][$key]['id'];
            $attrView=$opt['attr'][$key]['view'];
            if($entityId!=0) $sourceTable='data_'.$opt['attr'][$key]['entity_type_alias'];
            if($element=='textarea') $sourceTable='text_'.$opt['attr'][$key]['entity_type_alias'];
            if(isset($multiVal[$attrId][0])) $val=$multiVal[$attrId][0];
            // Для множественного элемента сохраняем первое значение массива в основной таблице
            if($multiple==1) $multiVal[$attrId]=$val;
            if($element=="file" && $multiple==1) $sourceTable='file';
            if($element=="user_id" && $val==0) {
                $val=$_SESSION['user']['id'];
                $array[$key]==$val;
            }
            if($element=='date' && $val!='' && $multiple==0){
                $ddl=explode(".",$val);
                if(strlen($ddl[0])==2) $val=$ddl[2].'.'.$ddl[1].'.'.$ddl[0];
                else $val=$ddl[0].'.'.$ddl[1].'.'.$ddl[2];
            }
            if(is_array($val)){
                if(isset($val[0])) $val=$val[0];
                else $val='';
            }
            if($array['id']==0){
                if(!isset($tables[$sourceTable]['id'])) $tables[$sourceTable]['id']=$array['id'];
                if($element=='order'){
                    // Получаем новое значение ORDER
                    if($entityId==0) $val=mysql::getValue("SELECT MAX(`".escape($key)."`) FROM `".escape($sourceTable)."` WHERE parent_id=".escape($array['parent_id']));
                    else $val=onlyDigit(mysql::getValue("SELECT MAX(`".escape($sourceTable)."`.`".escape($key)."`) FROM `entity`, `".escape($sourceTable)."` WHERE entity.parent_id=".escape($array['parent_id'])." AND ".escape($sourceTable).".id=entity.id"))+1;
                }
                if($element=="user_id") $val=$_SESSION['user']['id'];
                ajax::consoleLog($tables[$sourceTable][$key]);
            }
            else {
                if(!in_array($element,$specialFields)) {
                    if(!isset($tables[$sourceTable]['id'])) $tables[$sourceTable]['id']=$array['id'];
                    $tables[$sourceTable][$key]=$val;
                }
            }
        }

        // Любая сущность предусматривает обязательное хранение информации типа TEXT,
        // поэтому, если ID=0 но при этом отсутствуют поля для записи в эти таблицы,
        // принудительно добавляем эти поля
        $forceInsert=false;
        if($array['id']==0){
            $forceInsert=true;
            // Сохраняем данные сначала в главную таблицу entity
            if(isset($tables['entity'])){
                if(!isset($tables['entity']['owner'])) $tables['entity']['owner']=$_SESSION['user']['id'];
                mysql::saveArray('entity',$tables['entity']);
                if(mysql::error()==false) {
                    $array['id']=mysql::insertId();
                    $newEntityId=$array['id'];
                    $tables['text_'.$param['entity_type_alias']]['id']=$array['id'];
                    $tables['data_'.$param['entity_type_alias']]['id']=$array['id'];
                    unset($tables['entity']);
                    // Сохраняем файлы
                    $_REQUEST['array']['id']=$newEntityId;
                }
                else {
                    $newEntityId=false;
                    $error.=mysql::error();
                }
            }
        }

        $saved=array();
        // Если множественные значения пусты, то необходимо почистить их первый элемент,
        // который находится в основной таблицы данных _data_ вставив пустые значения
        // Кроме типов FILE и CHILD
        if(!empty($allMultiFields)){
            foreach($allMultiFields AS $v){
                foreach($opt['attr'] AS $k=>$kk){
                    if($kk['id']==$v && $kk['source']=='' && $kk['type']!='file' && $kk['type']!='child'){
                        if(!isset($tables[$kk['dbTable']][$k])) $tables[$kk['dbTable']][$k]='';// Вставили пустое значение
                        break;
                    }
                }
            }
        }
        
        // Сравнение данных в сохраняемом массиве и в БД. Удаление дубликатов. Удаление неактуальных данных в БД
        // Создадим массив, в который сложим все множественные значения из БД для последующего сравнения
        $dbMulti=array();
        $forDelete=array();
        if($multiVal===false){ $multiVal=array(); }
        if(!empty($mfData)){
            foreach($mfData AS $val){
                $type=$mfTypes[$val['attribute_id']];
                $value=$val['value_'.$type];
                if($type=='date'){
                    $ev=str_replace(" ","-",$value);
                    list($y,$m,$d)=explode("-",$ev);
                    $value=$d.".".$m.".".$y;
                }
                $dbMulti[$val['attribute_id']][$val['id']]=$value;
            }
        }
        if(!empty($multiVal)) foreach ($multiVal AS $k => $v) $multiVal[$k] = array_unique($v);// Удаляем ненужные дубликаты множественных значений
        else $multiVal=array();
        // Удалим из списка для сохренения те значения, которые уже есть в БД
        foreach($dbMulti AS $tk=>$tv){
            foreach($tv AS $kk=>$vv){
                // Если значение есть в выходном массиве и в БД, то удалим его, чтобы не пересохранять
                if(isset($multiVal[$tk])){
                    $key=array_search($vv,$multiVal[$tk]);
                    unset($multiVal[$tk][$key]);// Убрали из массива для сохранения
                    unset($dbMulti[$tk][$kk]);// Убрали из массива старых
                }
                else $forDelete[]=$kk; // А если в БД такое значение есть, а в списке для сохранения оно не найдено, то добавим ключ к списку для удалдения из БД
            }
        }
        if(!empty($forDelete)) mysql::query("DELETE FROM `multidata` WHERE id IN(".implode(",",$forDelete).")");

        // Сохраняем множественные значения
        if(count($multiVal)>=1){
            //$forSave=array();
            foreach($multiVal AS $k=>$v){
                if(count($v)>=1) {
                    foreach($v AS $vv) {
                        // Перед сохранением обработаем множественные поля, в зависимости от типа
                        if($vv!=='' && $vv!==false) {
                            // В зависимости от типа данных модицицируем значения и сохраняем в БД
                            $qFragment="";
                            if($mfTypes[$k]=='date') {
                                list($d,$m,$y)=explode(".",$vv);
                                $qFragment="date='".$y."-".$m."-".$d." 00:00:00'";
                            }
                            elseif($mfTypes[$k]=='datetime') {
                                $sValue=str_replace(" ",".",$sValue);
                                list($d,$m,$y,$sTime)=explode(".",$sValue);
                                $qFragment="date='".$y.'-'.$m.'-'.$d.' '.$sTime."'";
                            }
                            elseif($mfTypes[$k]=='int' || $mfTypes[$k]=='child') $qFragment="int=".$vv;
                            elseif($mfTypes[$k]=='decimal') $qFragment="decimal=".$vv;
                            else $qFragment="varchar='".escape(trim($vv))."'";
                            mysql::query("INSERT INTO `multidata` SET entity_id=".escape($array['id']).", attribute_id=".escape(trim($k)).", value_".$qFragment);
                        }
                    }
                }
            }
        }
        
        
        // Соханяем данные
        foreach($tables AS $key=>$v){
            mysql::saveArray($key,$v,'id',$forceInsert);
            if(mysql::errno()!=0){
                $error.='<div class="error">Ошибка MYSQL '.mysql::errno().' : '.mysql::error().'</div>';
            }
            $newEntityId=true;
        }
        // Запоминаем последнюю сохраненную сущность и время сохранения, для перемещения к ней в списке
        $_SESSION['lastSavedId']=$_REQUEST['array']['id'];
        $_SESSION['lastSavedTime']=time();
        data::fileUpload();
	    self::entityCacheUpdate($array['id']);
        // Исполнение кода, при сохранении инфоблока
        if(strlen($param['onsave'])>=6) eval($param['onsave']);
        //$out.=$newEntityId;// Возвращаем ID новой сущности, если все было ок, или FALSE
        return '';
    }


    // Отображение PHP виджета КАЛЕНДАРЬ
    static function showPhpCalendar($month, $year, $val, $value){
        $out='';
        $date='01.'.$month.'.'.$year;

        list($Сday,$Сmonth,$Сyear,$Сdays,$СdayOfWeek,$monthNum,$monthTmp)=explode(".",date('j.n.Y.t.N.n.m', strtotime($date)));
        $monthNames=explode(',',',Январь,Февраль,Март,Апрель,Май,Июнь,Июль,Август,Сентябрь,Октябрь,Ноябрь,Декабрь');
        $monthTmp='.'.$monthTmp.'.'.$year;

        $dCh=0;
        $wdc=1;// Счетчик к-ва дней недели
        $selected='';
        // Если месяц начинается не с понедельника, то добавляем пустые дни
        if($СdayOfWeek!=1){
            for($i=1; $i<$СdayOfWeek; $i++){
                if($i==1) $out.='<tr>';
                $out.='<td class="hd"></td>';
                $wdc++;
                if($i==8) $out.='</tr>';
            }
        }

        // Основной блок календаря - даты месяца
        for($Cm=1 ; $Cm<=$Сdays; $Cm++){
            $zdn=$Cm;
            $class=='';
            if($Cm<10) $zdm='0'.$Cm;
            else $zdm=$Cm;
            if(in_array($zdm.$monthTmp, $value)) {
                $class='sel';
                $selected.='<input id="fl'.$val['id'].'-'.$zdm.$monthTmp.'" type="hidden" name="array['.$val['alias'].'][]" value="'.$zdm.$monthTmp.'">';
            }
            else $class='';
            if($wdc==1) $out.='<tr>';
            $out.='<td class="'.$class.'" id="d'.$val['id'].'-'.$zdm.$monthTmp.'" onClick="calSet(this.id)">'.$Cm.'</td>';
            $wdc++;
            if($wdc==8) {
                $out.='</tr>';
                $wdc=1;
            }
        }

        if($wdc!=1){
            for($i=$wdc; $i<=7; $i++){
                if($i==1) $out.='<tr>';
                $out.='<td class="hd"></td>';
                $wdc++;
                if($i==8) $out.='</tr>';
            }
        }

        $out='<div class="axCal2"><table><tr class="mheight"><th colspan="7">'.$monthNames[$monthNum].' '.$Сyear.'</th></tr><tr><th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Вс</th></tr>'.$out.'</table></div>'.$selected;
        return $out;
    }


    /**
     * Генератор формы редактирования инфоблока
     * @param $mn - module alias
     * @param $entityType
     * @param $array
     * @return array
     * @internal param $value
     */
    static function editForm($mn, $entityType, $array){
        global $asChild, $settings, $lastSavedId;
        // Получаем всю инфу о инфоблоке
        $param=mysql::getArray("SELECT * FROM `entity_type` WHERE id=".escape($entityType)." LIMIT 1",true);
        $isTemporary=false;
        $opt=unserialize($param['backend_select']);// Аттрибуты
        if($array['id']==0 && isset($array['editor'])) $array['editor']=$_SESSION['user']['id'];

        // Предварительное сохранение нового товара КРОМЕ ПАПКИ!!!!!
        if($array['id']==0 && $opt['attr']['child_entity_type']['entity_type_id']!=7) {
            $orderField='';
            mysql::saveArray('entity',array('id'=>0,'name'=>'-БЕЗ НАЗВАНИЯ-','entity_type'=>$entityType,'parent_id'=>$array['parent_id'],'hidden'=>1,'owner'=>$_SESSION['user']['id'],'date_add'=>date("Y-m-d H:i:s")),'id',true);
            $array['id']=mysql::insertId();
            $lastSavedId=$array['id'];
            $savingArray=array('id'=>$array['id']);
            // Узнаем, есть ли поле типа ORDER
            foreach($opt['attr'] AS $val){
                if($val['element']=='order'){
                    // Получаем максимальное значение ORDER для данной инфоблока
                    $savingArray[$val['alias']]=onlyDigit(mysql::getValue("SELECT MAX(t1.".$val['alias'].") FROM `".$val['dbTable']."` AS t1 JOIN `entity` AS t2 ON t2.id=t1.id WHERE t2.parent_id=".$array['parent_id']))+1;
                    break;
                }
            }
            mysql::saveArray('data_'.$param['entity_type_alias'],$savingArray,'id',true);
            mysql::saveArray('text_'.$param['entity_type_alias'],array('id'=>$array['id']),'id',true);
            $isTemporary=true;
        }

        if($array['id']!=0){
            $_SESSION['lastSavedId']=$array['id'];
            // Зная alias инфоблока можно составить запрос на получение всех основных данных заданного товара
            // Если идентификатор товара !=0 то надо получать все данные из базы, иначе - просто отрисуем форму
            $array=mysql::getArray("SELECT * FROM
                `entity` AS t1
                JOIN `".escape('data_'.$param['entity_type_alias'])."` AS t2 ON t2.id=t1.id
                JOIN `".escape('text_'.$param['entity_type_alias'])."` AS t3 ON t3.id=t1.id
                WHERE t1.id=".escape($array['id']),true);
            if($array==false) {
                ajax::dialogAlert('Критическая ошибка! Не удалось получить массив данных! '.mysql::error());
            }
            if($isTemporary===true){
                $array['hidden']=0;
                $array['name']='';
            }
        }
        if(isset($asChild)){
            // Для инфоблока типа "потомок" скрываем некоторые поля
            foreach($opt['attr'] AS $key=>$val){
                if($val['childhide']==1) unset($opt['attr'][$key]);
            }
        }
        // Поля, которые запрещено редактировать, поэтому при редактировании ранее созданного элемента они
        // опускаются и не отображаются в форме
        //$permanentFields=array('date_add','owner','order');
        $selectList=array();
        $selects=array();
        $dbSelectList=array();
        $multiData=array();
        $multi=array();
        $files=array();
        $mfTypes=array();
        $fileFieldsExists=false;
        $links=array();

        // Получим список полей типа SELECT для одновременного получения опций из БД
        foreach($opt['attr'] AS $val){
            if($val['multiple']==1) {
                $multi[]=$val['id'];
                $mfTypes[$val['id']]=$val['type'];
            }
            if($val['element']=='select') {
                // Селекты бывают 3 видов
                if(strlen($val['source'])<=12) $selectList[]=$val['id']; // Обычный селект - хранит данные в таблице %moduleAlias_options
                else $dbSelectList[]=$val['alias']; // Селект, указывающий на внешнюю таблицу
            }
            if($val['element']=='entity_select') $dbSelectList[]=$val['alias'];
            if($val['element']=='file') $fileFieldsExists=true;
        }

        // Получаем из БД все списки типа SELECT
        if(isset($selectList[0])){
            $a=mysql::getArray("SELECT * FROM `options` WHERE attr_id IN(".implode(',',$selectList).") ORDER BY value ASC");
            if($a!=false){
                foreach($a AS $val){
                    if(!isset($selects[$val['attr_id']])) $selects[$val['attr_id']][0]='-НЕ ЗАДАНО-';
                    $selects[$val['attr_id']][$val['id']]=$val['value'];
                }
            }
        }

        // Обработка селектов, хранящих данные во внешних таблицах
        if(isset($dbSelectList[0])){
            foreach($dbSelectList AS $val){
                if(stripos($opt['attr'][$val]['editSource'],'SELECT')!==false){
                    $t=$opt['attr'][$val]['editSource'];
                    // Меняем плейсхолдеры вида [name]
                    // на переменные вида $array['name']
                    $finded=false;
                    if (preg_match_all ('!\[(.*?)\]!', $t, $l)) if ($l[1] != false) $finded = $l[1];
                    if($finded!=false){
                        foreach($finded AS $ff){
                            $t=str_ireplace('['.$ff.']',$array[$ff],$t);
                        }
                    }
                    $a=mysql::getArray($t);
                }
                else {
                    if($opt['attr'][$val]['element']=='entity_select') $t='entity_type.id.entity_type.id>0';// ТИП entity_select
                    else $t=str_replace('%moduleAlias','',$opt['attr'][$val]['source']);
                    list($table,$id,$name,$where)=explode('.',$t);
                    if($where) $wh=' WHERE '.$where.' ';
                    else $wh='';
                    $a=mysql::getArray("SELECT `".escape(trim($id))."` AS id, `".escape(trim($name))."` AS name FROM `".escape(trim($table))."` ".trim($wh)." ORDER BY `name` ASC");
                }
                if(is_array($a)){
                    foreach($a AS $v){
                        $ad=$opt['attr'][$val]['id'];
                        if($v['name']!='-БЕЗ НАЗВАНИЯ-') $selects[$ad][$v['id']]=$v['name'];
                    }
                }
            }
        }

        // Сортировка опций в селектах
        if(!empty($selects)){
            foreach($selects AS $k=>$v){
                foreach($opt['attr'] AS $kk){
                    if($kk['id']==$k){
                        // Сортировка по ключу
                        if($kk['optorder']==1) ksort($selects[$k]);
                        break;
                    }
                }
            }
        }

        // Обработка всех множественных элементов
        if(isset($multi[0])){
            $m=mysql::getArray("SELECT attribute_id, value_varchar, value_date, value_int, value_decimal FROM `multidata` WHERE entity_id='".escape($array['id'])."'");
            if(isset($m[0])){
                foreach($m AS $val){

                    $sValue=trim($val['value_varchar']);
                    if($mfTypes[$val['attribute_id']]=='date') {
                        list($sValue,)=explode(" ",$val['value_date']);
                        list($y,$m,$d)=explode("-",$sValue);
                        $sValue=$d.'.'.$m.'.'.$y;
                    }
                    elseif($mfTypes[$val['attribute_id']]=='dateandtime') {
                        list($sValue,$sTime)=explode(" ",$val['value_date']);
                        list($y,$m,$d)=explode("-",$sValue);
                        $sValue=$d.'.'.$m.'.'.$y.' '.$sTime;
                    }
                    elseif($mfTypes[$val['attribute_id']]=='int' || $mfTypes[$val['attribute_id']]=='child') {
                        $sValue=$val['value_int'];
                    }
                    elseif($mfTypes[$val['attribute_id']]=='decimal') {
                        $sValue=$val['value_decimal'];
                    }
                    else{
                        $sValue=$val['value_varchar'];
                    }
                    $multiData[$val['attribute_id']][]=$sValue;
                }
            }
        }

        // Обработка файлов, если найдены поля с типом file
        if($array['id']!=0 && $fileFieldsExists==true){
            $m=mysql::getArray("SELECT * FROM `file` WHERE entity_id=".escape($array['id'])." ORDER BY `order` ASC");
            if(isset($m[0])){
                foreach($m AS $val){ $files[$val['attribute_id']][]=$val; }
            }
        }

        $fields=array();
        $requiredFields=array();// Cписок полей обязательных для заполнения
        foreach($opt['attr'] AS $val){
            if($val['required']==1) $requiredFields[]=$val['id'];
            if($val['folderhide']==0 || $entityType!=7){
                if(strlen($val['dop'])>=6){
                    $z=unserialize($val['dop']);
                    $val+=$z;
                    unset($val['dop']);
                }
                $style=$val['style'];
                $class=$val['class'];
                if($val['unit']!='') $val['name'].=' <b>('.$val['unit'].')</b>';
                $events=array();
                if($val['events']!='') $events=unserialize($val['events']);
                if(!isset($array[$val['alias']])) $value=$val['default'];
                else $value=$array[$val['alias']];
                if( $val['element']=='entity_type' ) $value=$entityType;

                if( $val['element']=='id' ) $fields[]=array('type'=>'hidden', 'name'=>'array['.$val['alias'].']', 'id'=>'AXIOM_ELEMENT_ID', 'value'=>$value);
                elseif( $val['element']=='hidden' ||
                    $val['element']=='parent_id' ||
                    $val['element']=='entity_type' ||
                    $val['element']=='order') {
                    if($val['alias']=='child_entity_type') $value=$CHILD_ENTITY_TYPE;
                    $fields[]=array('type'=>'hidden', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'value'=>$value);
                }

                // DATE_ADD (служебное поле)
                elseif($val['element']=='datetime' && $val['alias']=='date_add'){
                    $curTime=date("Y-m-d H:i:s",time());
                    $fields[]=array('type'=>'hidden', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'value'=>date("Y-m-d H:i:s",time()));
                }

                // CALENDAR
                if($val['element']=='date') {
                    if($val['multiple']==1){
                        if(!isset($multiData[$val['id']])) $multiData[$val['id']]=array();
                        $today=date("d.m.Y",time());
                        $month=date("n",time());
                        $year=date("Y",time());
                        $calList='';
                        for($i=0; $i<12; $i++){
                            $calList.=self::showPhpCalendar($month,$year,$val,$multiData[$val['id']]);
                            if($month==12) {
                                $month=1;
                                $year=$year+1;
                            }
							else $month++;
                        }
                        // Начинаем с текущего месяца, отображаем календари
                        $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td id="field'.$val['id'].'" data-alias="'.$val['alias'].'">'.$calList.'</td></tr>');
                        $fields[]='-';
                    }
                    else {
                        if($value=='%today%') $value=date("d.m.Y",time());
                        else {
                            // Проверка и коррекция значения. Это необходимо из-за различий в форматах даты
							if(strlen($value)==10){
								$value=str_replace('-','.',$value);
								list($vd,$vm,$vy)=explode('.',$value);
								if(strlen($vd)==4) $value=$vy.'.'.$vm.'.'.$vd;
							}
                        }
						if($value=='00.00.0000') $value='';
                        $fields[]=array('type'=>'text', 'label'=>$val['name'], 'name'=>'array['.$val['alias'].']', 'class'=>'date', 'id'=>'field'.$val['id'], 'onClick'=>'createCalendar(this.id)', 'onkeydown'=>'this.blur()', 'value'=>$value, 'autocomplete'=>false);
                    }
                }

                elseif($val['element']=='dateandtime'){
                    if($value=='%today%') {
                        $value=date("d.m.Y H:i:s",time());
                        $time=date("H:i",time());
                    }
                    else {
                        // Проверка и коррекция значения. Это необходимо из-за различий в форматах даты
                        if(strlen($value)>=19){
                            list($value,$time)=explode(" ",$value);
                            $value=str_replace('-','.',$value);
                            list($vd,$vm,$vy)=explode('.',$value);
                            if(strlen($vd)==4) $value=$vy.'.'.$vm.'.'.$vd;
                        }
                    }
                    if(!isset($time)) $time='00:00';
                    if($value=='00.00.0000') {
                        $value='';
                        $time='00:00';
                    }

                    $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td><div class="btn-group"><input name="'.$val['alias'].'-date" id="field'.$val['id'].'" type="text" value="'.$value.'" onclick="createCalendar(this.id)" onkeydown="this.blur()" autocomplete="off" class="date"><input name="'.$val['alias'].'-time" class="time" type="time" value="'.$time.'"><input type="hidden" value=" " name="array['.$val['alias'].']"></div></td></tr>');
                }

                // SELECT
                elseif($val['element']=='select') {
                    if($val['multiple']==1) {
                        if(!isset($multiData[$val['id']])) $multiData[$val['id']]=array();
                        if($val['edit']==1){
                            if(strpos($style,'width')===false) $style.=';width:500px;';
                            if(strpos($style,'height')===false) $style.=';margin-right:0; height:140px;';
                            $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'<br><span class="small">Удерживайте CTRL для выбора нескольких элементов</span></label></td><td><div id="grp'.$val['id'].'">');
                            $fields[]=array('type'=>'select', 'noformat'=>true, 'id'=>'edSel'.$val['id'], 'multiple'=>1, 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'label'=>$val['name'], 'class'=>$class, 'style'=>$style.'; border-radius:0', 'value'=>$multiData[$val['id']], 'options'=>$selects[$val['id']], 'dopparams'=>$val['dopparams']);
                            $fields[]=array('type'=>'html', 'value'=>'<div class="btn" style="padding:4px 0 0 0; border-radius:0 4px 4px 0;" title="Редактировать список" onClick="ajaxGet(\'data::selectOptionsEditor?='.$array['id'].'&atrId='.$val['id'].'\')"><i class="ic-server"><i></div></div></td></tr>');
                        }
                        else {
                            $fields[]=array('type'=>'select', 'name'=>'array['.$val['alias'].']', 'multiple'=>$val['multiple'], 'style'=>'width:500px; height:140px;', 'id'=>'field'.$val['id'], 'label'=>$val['name'].'<br><span class="small">Удерживайте CTRL для выбора нескольких элементов</span>', 'class'=>$class, 'value'=>$multiData[$val['id']], 'options'=>$selects[$val['id']]);
                        }
                    }
                    else {
                        if(strlen($val['javascript'])>=6) $val['dopparams']=$val['javascript'];

                        if(count($selects[$val['id']])<=20){
                            $a=array('type'=>'select', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'label'=>$val['name'], 'class'=>$class, 'style'=>$style, 'value'=>$value, 'options'=>$selects[$val['id']], 'dopparams'=>$val['dopparams']);
                        }
                        else {
                            $a=array(
                                'type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td>
                                <td><div id="grp'.$val['id'].'" class="btn-group"><div class="axiomCombo" id="comboselect'.$val['id'].'">'.createSelect('array['.$val['alias'].']',$value,$selects[$val['id']],'id="edSel'.$val['id'].'"').'<input type="text" id="sel'.$val['id'].'txt" value="'.$selects[$val['id']][$value].'" onfocus="comboClear(this)" onkeyup="comboEmulator(this.id,this.value)"><span class="hsep"></span></div></td></tr>'
                            );
                        }
                        if($val['edit']==1){
                            $a['noformat']=true;
                            $a['id']='edSel'.$val['id'];
                            $fields[]=array(
                                'type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</td><td><div id="grp'.$val['id'].'" class="btn-group"><div class="axiomCombo" id="comboselect'.$val['id'].'">'.createSelect('array['.$val['alias'].']',$value,$selects[$val['id']],'id="edSel'.$val['id'].'"').'<input type="text" id="sel'.$val['id'].'txt" value="'.$selects[$val['id']][$value].'" onfocus="comboClear(this)" onkeyup="comboEmulator(this.id,this.value)"><span class="hsep"></span><div class="btn" style="padding:4px 0 0 0" title="Редактировать список" onClick="ajaxGet(\'data::selectOptionsEditor?='.$array['id'].'&atrId='.$val['id'].'\')"><i class="ic-server"><i></div></div></td></tr>'
                            );
                        }
                        else {
                            $fields[]=$a;
                        }
                    }
                }

                // LINK (ссылка на сущность)
                elseif($val['element']=='link') {
                    if(strlen($val['javascript'])>=6) $val['dopparams']=$val['javascript'];
                    $icon='';
                    if($val['icon']!='') $icon='<div class="label"><i class="'.$val['icon'].'"></i></div>';
                    $fields[]=array(
                        'type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td>
                    <td>
                        <div id="grp'.$val['id'].'" class="btn-group">
                            '.$icon.'
                            <input id="link'.$val['id'].'" type="text" style="width:600px;" disabled="disavled" value="'.$selects[$val['id']][$value].'"><input type="hidden" name="array['.$val['alias'].']" value="'.$value.'">
                        </div>
                    </td>
                </tr>'
                    );
                }

                // ENTITY-SELECT - выбор типа инфоблока
                elseif($val['element']=='entity_select') {
                    // Если у папки есть уже созданные потомки, запретим изменять тип дочерних элементов
                    $childrens=mysql::getArray("SELECT id FROM `entity` WHERE parent_id=".escape($array['id'])." && parent_id!=0 LIMIT 1");
                    if($childrens===false){
                        $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td><div class="btn-group" id="entSel">');
                        $fields[]=array('type'=>'select', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'class'=>$class, 'style'=>$style, 'value'=>$value, 'options'=>$selects[$val['id']], 'noformat'=>true);
                        $fields[]=array('type'=>'html', 'value'=>'<div class="btn" title="Новый тип" onClick="entityForm(\'entSel\')"><i class="ic-plus"></i></div></div></td></tr>');
                    }
                    else {
                        $editLink='';
                        if($_SESSION['user']['group']<=1){
                            $editLink='<div class="btn" style="float:right; margin:0;" onClick="ajaxGet(\'data::attr?='.array_search($selects[$val['id']][$value],$selects[$val['id']]).'&mn='.$mn.'\')"><i class="ic-tools" style="margin:0;"></i></div>';
                        }
                        $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td><div class="btn-disabled" style="padding:3px 16px; margin-right:20px;" id="entSel" >'.$selects[$val['id']][$value].'</div><span class="small">Изменение недоступно, так как папка содержит вложенные элементы</span>'.$editLink.'</td></tr>');
                    }
                }

                // VARCHAR / INT / DECIMAL
                elseif($val['element']=='varchar' || $val['element']=='int' || $val['element']=='decimal') {
                    if(isset($val['events'])) $events=$val['events'];
                    $comment='';
                    if($val['view']=='youtube') {
                        //$comment='<div class="info">Вставьте URL адрес видеоролика Youtube</div>';
                        if($val['cssclass']=='') $val['cssclass']='size-xl';
                    }
                    elseif($val['view']=='googlemap'){
                        ajax::includeScript('https://maps.googleapis.com/maps/api/js?key='.$settings['googleMapsApiKey'].'&sensor=false&libraries=geometry');
                    }
                    if( $val['multiple'] == 1 ){
                        if($comment!='') $comment='<tr><td></td><td>'.$comment.'</td></tr>';
                        if(!isset($multiData[$val['id']])) $multiData[$val['id']]=array();
                        $fields[]='-';
                        if($val['view']=='googlemap') {
                            $fields[]=array('type'=>'html','value'=>$comment.'<tr><td><label>'.$val['name'].'</label></td><td>'.self::formMultiField('<input type="hidden" id="field'.$val['id'].'-%%k%%" name=array['.$val['alias'].'][] value="%%v%%"><div class="btn" onClick="googleMapSimpleEditor(\'field'.$val['id'].'-%%k%%\')" %%dop%%>Карта GoogleMaps<i class="ic-earth"></i></div>', $val, $value, $multiData).'</td></tr>');
                        }
                        else $fields[]=array('type'=>'html', 'value'=>$comment.'<tr><td><label>'.$val['name'].'</label></td><td>'.self::formMultiField('<input id="fld'.$val['id'].'-%%k%%" type="text" name="array['.$val['alias'].'][]" value="%%v%%" %%dop%%>', $val, $value, $multiData).'</td></tr>');
                        $fields[]='-';
                    }
                    else {
                        if($val['view']=='googlemap') $fields[]=array('type'=>'html','value'=>'<tr><td><label>'.$val['name'].'</label></td><td>'.$comment.'<div class="btn-group"><input type="hidden" id="field'.$val['id'].'" name=array['.$val['alias'].'] value="'.htmlspecialchars($value).'"><div class="btn" onClick="googleMapSimpleEditor(\'field'.$val['id'].'\')">Карта GoogleMaps<i class="ic-earth"></i></div></div></td></tr>');
                        else {
                            if($val['hidden']==1) $fields[]=array('type'=>'hidden', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'value'=>$value);
                            else {
                                // Если не задан CSS класс и стиль, то добавить некоторые параметры для некоторых типов полей
                                if($val['cssclass']=='' && $val['style']==''){
                                    if($val['element']=='int' || $val['element']=='decimal'){
                                        if(strlen($val['cssclass'])<=3) $val['cssclass']='size-m';
                                        $events="onkeypress=\'return inputNumber(event)\'";
                                    }
                                }
                                $dopparams=$val['dopparams'];
                                if(strlen($val['javascript'])>=8) $dopparams.=$val['javascript'];
                                $fields[]=array('type'=>'text', 'label'=>$val['name'], 'class'=>$val['cssclass'], 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'value'=>$value, 'events'=>$events, 'description'=>$comment, 'maxlength'=>$val['maxlength'], 'dopparams'=>$dopparams);
                            }
                        }
                    }
                }
                // CHECKBOX
                elseif($val['element']=='checkbox') {
                    $dopparams=$val['dopparams'];
                    if(strlen($val['javascript'])>=8) $dopparams.=$val['javascript'];
                    $fields[]=array('type'=>'checkbox', 'name'=>'array['.$val['alias'].']', 'id'=>'field'.$val['id'], 'label'=>$val['name'],  'class'=>$class, 'style'=>$style, 'value'=>$value, 'dopparams'=>$dopparams);
                }

                // TEXTAREA
                elseif($val['element']=='textarea'){
                    $maxlength=false;
                    $counter=false;
                    if($val['maxlength']!='' && $val['maxlength']!=0) {
                        $maxlength=$val['maxlength'];
                        $counter=true;
                    }
                    $ftype='textarea';
                    if($val['cssclass']=='') $class='input';
                    else {
                        $class=$val['cssclass'];
                        $ftype='ckeditor';
                    }
                    if($style==''){
                        if($class=='axiom') $style='height:400px;';
                        elseif($class=='basic') $style='height:300px;';
                        elseif($class=='mini') $style='height:300px;';
                    }
                    $fields[]=array('type'=>$ftype, 'description'=>$val['unit'], 'name'=>'array['.$val['alias'].']', 'label'=>$val['name'],  'class'=>$class, 'style'=>$style, 'value'=>$value,'maxlength'=>$maxlength, 'id'=>'field'.$val['id'], 'counter'=>$counter);
                }

                // FILE
                elseif($val['element']=='file'){
                    $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td id="field'.$val['id'].'">'.self::showFormFiles($mn,$val,$array, $files).'</td></tr>');
                    $fields[]='-';
                }
                
                // COLORPICKER
                elseif($val['element']=='color'){
	                $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td id="field'.$val['id'].'"><input id="field'.$val['id'].'" name="array['.$val['alias'].']" type="color" value="'.$value.'" onInput="this.setAttribute(\'value\',this.value)"></td></tr>');
                }
                
                // BUTTON
                elseif($val['element']=='button'){
                    $script=$val['javascript'];
                    if(isset($script)){
                        // Меняем плейсхолдеры вида [name]
                        // на переменные вида $array['name']
                        $finded=false;
                        if (preg_match_all ('!\[(.*?)\]!', $script, $l)) if ($l[1] != false) $finded = $l[1];
                        if($finded!=false){
                            foreach($finded AS $ff){
                                $script=str_ireplace('['.$ff.']',$array[$ff],$script);
                            }
                        }
                    }
                    $icon='';
                    if($val['icon']!='') $icon='<i class="'.$val['icon'].'"></i>';
                    $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>&nbsp;</label></td><td id="field'.$val['id'].'"><div class="btn" '.$script.' style="padding-left:16px; padding-right:16px;">'.$icon.$val['name'].'</div></td></tr>');
                }

                // Тип элемента - СПИСОК ПООМКОВ (CHILD)
                elseif($val['element']=='child'){
                    $fields[]='-';
                    $fields[]=array('type'=>'html', 'value'=>'<tr><td><label>'.$val['name'].'</label></td><td id="field'.$val['id'].'">'.self::showChildList($array['id'], $val['attr_source'], $val['id']).'</td></tr>');
                    $fields[]='-';
                }
            }
        }
        $fields[]=array('type'=>'hidden', 'value'=>implode(',',$requiredFields), 'id'=>'AxiomREQFIELDS', 'name'=>'array[AxiomREQFIELDS]');
        return $fields;
    }

// Список потомков элемента
    /**
     * @param int $parentId
     * @param array $val Field Parameter Array
     * @param int $type ChildTypeId
     * @param int $attrId Attribute Id
     * @return string
     */
    static function showChildList($parentId, $type, $attrId){
    $out='';
    if($parentId==0) $out.='<div class="info">Сохраните товар</div>';
    else {
        $out.='<div class="row"><div class="btn" onClick="editChild(0,'.$type.','.$parentId.','.$attrId.')"><i class="ic-plus"></i>Добавить</div></div>';
        //Получаем аттрибуты товара
        $atr=mysql::getArray("SELECT `backend_select`,`before_save`,`onsave`,`copy`,`delete`,`buffer`,`icon` FROM `entity_type` WHERE id=".escape($type)." LIMIT 1",true);
        $param=unserialize($atr['backend_select']);
        if($param!=false) {
            $param['where'][]="t2.parent_id=".escape($parentId);
            $childs=mysql::getArray("SELECT ".implode(",", $param['select'])." FROM ".implode(" JOIN ",$param['from'])." WHERE ".implode(" AND ",$param['where'])." ORDER BY t2.id ASC");
            if($childs!=false){
                $w=70;
                if($atr['copy']==1) $w+=15;
                if($atr['buffer']==1) $w+=15;
                $out.='<table class="cmstable4"><tr class="nodrag nodrop"><th style="width:24px;">&nbsp;</th>'.data::adminTableHeaders($mn, $param['attr'],'CHILD').'<th style="width:'.$w.'px!important;">Действия</th></tr>';
                foreach($childs AS $ck=>$cv){
                    $out.='<tr id="child'.$cv['id'].'"><td style="width:24px;"><i id="fl'.$cv['id'].'" class="'.$atr['icon'].'"></i></td>';// Служебные поля
                    foreach($cv AS $k=>$v) $out.=data::adminTableField($mn, 'CHILD', $cv, $param, $k, '<b class="hand" onClick="editChild('.$cv['id'].','.$type.','.$parentId.','.$attrId.')">'.$cv['name'].'</b>');
                    $out.='<td><i class="ic-editdoc" onClick="editChild('.$cv['id'].','.$type.','.$parentId.','.$attrId.')" title="Изменить"></i>';
                    if($atr['copy']==1) $out.='<i class="ic-copy" title="Создать копию" onClick="ajaxGet(\'data::childCopy?='.$cv['id'].'&entity='.$cv['entity_type'].'&cat='.$cv['parent_id'].'&asChild='.$attrId.'\')"></i>';
                    if($atr['buffer']==1) $out.='<i class="ic-clipboard" title="Копировать в буфер" onClick="ajaxGet(\'data::toBuffer?='.$cv['id'].'&type='.$cv['entity_type'].'&parent='.$cv['parent_id'].'\')"></i>';
                    $out.='<i class="ic-delete" title="Удалить" onClick="childDel('.$cv['id'].','.$parentId.')"></i></td></tr>';
                }
                $out.='</table>';
            }
        }
    }
    return $out;
}


// Отображение списка аттрибутов инфоблока
static function entityFieldsList(){
    global $item, $ent, $opened, $nowindow;
    $out='';
    if($ent!==false) {
        $item=$ent;
        $cboxAct='globalCbox';
    }
    else $cboxAct='cbox';
    $array=self::attributes($item);
    uasort($array, 'data::attrBackendListSort');

    $entityName='';
    $defaultFields='';
    $entityFields='';
    if($opened) $opened='&entity='.$item.'&opened='.$opened;
    $permanentFields=array('id','parent_id','entity_type','hidden','owner');
    $noDeleteFields=array('id','parent_id','name','entity_type','hidden','date_add','owner');

    foreach($array AS $val){
        $field='';
        $dis='';
        $elemTypeDop='';
        $attrLink='<b class="hand" onClick="ajaxGet(\'data::fieldSel?='.$val['id'].'&entity='.$item.'\')">'.$val['name'].'</b>';
        if($val['entity_type_id']==0) {
            $dis=' disabled="disabled" ';
            if($val['entity_type_id']==0 && in_array($val['alias'],$permanentFields)) $attrLink='<b class="red" title="Системный аттрибут">'.$val['name'].'</b>';
        }
        else { if($entityName=='') $entityName=$val['entity_type']; }
        // Если поле entityName='', значит у инфоблока нет доп. полей и их необходимо создать
        if($entityName=='') $entityName=mysql::getValue("SELECT entity_type FROM `entity_type` WHERE id=".escape($item)." LIMIT 1");

        $field.='<td class="small">'.$val['id'].'</td><td style="padding:0;">';
        if($val['icon']!=''){
            $field.='<i class="'.$val['icon'].'" title="'.$val['icon'].'"></i>';
        }
        $field.='</td>
        <td>'.$attrLink.'</td>
        <td class="small">'.$val['attr_group'].'</td>
        <td class="small">'.$val['alias'].'</td>';

        if(strlen($val['source'])>8){
            list($etTable,$etId,$etValue,$etWhere)=explode(".",$val['source']);
            $field.='<td class="small" style="padding:0 6px 0 4px; color:#000000; cursor:pointer; background:#fff697;" title="Источник - внешняя таблица: 
SELECT '.$etId.', '.$etValue.' FROM `'.$etTable.'` WHERE '.$etWhere.'">'.$val['element'].'<i class="ic-db" style="font-size:12px;margin-left:6px;"></i></td>';
        }
        else $field.='<td class="small">'.$val['element'].'</td>';
        $field.='<td style="padding:0; text-align:center;">';
        if($val['multiple']==1) $field.='<i class="ic-boxes" title="Множественное поле" style="margin:0;"></i>';
        $field.='</td><td style="padding:0; text-align:center;">';
        if($val['javascript']!='') $field.='<span title="JavaScript" style="display:inline-block; font-size:9px; font-weight:bold; color:#fff; background:#ea9a00; line-height:17px; height:16px; padding:0 4px; border-radius:3px; overflow:hidden;">JS</span>';
        $field.='</td><td style="padding:0; text-align:center;">';
        if($val['editSource']!='') $field.='<span title="PHP" style="display:inline-block; font-size:9px; font-weight:bold; color:#fff; background:#49699d; line-height:17px; height:16px; padding:0 4px; border-radius:3px; overflow:hidden;">PHP</span>';
        $field.='</td><td style="text-align:center;"><input '.$dis.' type="checkbox" id="frontend'.$val['id'].'" style="float:none" '.checkboxSelected($val['frontend']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.frontend\')"></td>
        <td style="text-align:center;"><input type="checkbox" id="frontend_list'.$val['id'].'" style="float:none"  '.checkboxSelected($val['frontend_list']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.frontend_list\')"></td>
        <td style="text-align:center;"><input type="checkbox" id="filter'.$val['id'].'" style="float:none" '.checkboxSelected($val['filter']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.filter\')"></td><td class="small">'.$val['filterOrder'].'</td>
        <td style="text-align:center; background:rgba(55,55,55,0.05)" title="Извлекать"><input '.$dis.' type="checkbox" id="backend'.$val['id'].'" style="float:none"  '.checkboxSelected($val['backend']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.backend\')"></td>
        <td style="text-align:center; background:rgba(55,55,55,0.05)" title="Отображать в списке"><input type="checkbox" id="backend_list'.$val['id'].'" style="float:none"  '.checkboxSelected($val['backend_list']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.backend_list\')"></td>
        <td style="text-align:center; background:rgba(55,55,55,0.05)" title="Разрешить сортировку"><input type="checkbox" id="sort'.$val['id'].'" style="float:none"  '.checkboxSelected($val['sort']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.sort\')"></td>
        <td style="text-align:center; background:rgba(55,55,55,0.05)" title="Обязательно к заполнению"><input type="checkbox" id="req'.$val['id'].'" style="float:none"  '.checkboxSelected($val['required']).'" onChange="ajaxGet(\'data::checkbox?='.$val['id'].'&field=attributes.required\')"></td>';

        $delLink='<i class="ic-delete" onClick="attrDel('.$val['id'].',\''.$opened.'\')" title="Удалить"></i>';
        if($ent===false){
            if($val['entity_type_id']==0) $defaultFields.='<tr id="fl'.$val['id'].'" class="nodrag nodrop" style="background:#e6e6e6"><td style="width:24px !important;">&nbsp;</td>'.$field.'<td style="width:24px;">&nbsp;</td></tr>';
            else {
                if($val['entity_type_id']==0 && in_array($val['alias'],$noDeleteFields)) $delLink='&nbsp;';
                $entityFields.='<tr id="fl'.$val['id'].'"><td style="width:24px;" class="drag"><i class="ic ic-move-v"></i></td>'.$field.'<td>'.$delLink.'</td></tr>';
            }
        }
        else {
            if($val['entity_type_id']!=$ent) $defaultFields.='<tr id="fl'.$val['id'].'" class="nodrag nodrop" style="background:#e6e6e6"><td>&nbsp;</td>'.$field.'<td>&nbsp;</td></tr>';
            else {
                if($val['entity_type_id']==0 && in_array($val['alias'],$permanentFields)) $delLink='&nbsp;';
                $entityFields.='<tr id="fl'.$val['id'].'"><td style="width:24px;" class="drag"><i class="ic ic-move-v"></i></td>'.$field.'<td>'.$delLink.'</td></tr>';
            }
        }
    }


    $out.='<table id="elFields" class="cmstable4" onmouseover="dragTableInit(this.id,\'data::attrOrder?=attributes'.$opened.'&noscript=1\')">
        <thead>
        <tr class="nodrag nodrop">
         <th style="width:24px;" rowspan="2"><i class="ic-mouse"></i></th>
         <th rowspan="2">ID</th>
         <th rowspan="2" style="width:16px;">&nbsp;</th>
         <th rowspan="2">Поле</th>
         <th rowspan="2">Группа</th>
         <th rowspan="2">alias</th>
         <th rowspan="2">Тип</th>
         <th rowspan="2" style="text-align:center; padding:0;" title="Множественное поле"><i class="ic-boxes"></i></th>
         <th rowspan="2" style="text-align:center; padding:0;" title="JavaScript">JS</th>
         <th rowspan="2" style="text-align:center; padding:0;" title="JavaScript">PHP</th>
         <th colspan="4" style="text-align:center;">На сайте</th>
         <th colspan="4" style="text-align:center; background:rgba(0,0,0,0.25)">В админ-панели</th>
         <th rowspan="2" style="width:24px">&nbsp;</th>
        </tr>
        <tr>
         <th style="text-align:center;padding:0;" title="Извлекать"><i class="ic-upload3"></i></th>
         <th style="text-align:center;padding:0;" title="Отображать в списке"><i class="ic-eye"></i></th>
         <th colspan="2" style="text-align:center;padding:0;" title="Фильтр / Порядок в фильтре"><i class="ic-filter"></i><i class="ic-sort-aasc" style="float:right; padding-right:6px;"></i></th>
         <th style="text-align:center;padding:0; background:rgba(0,0,0,0.25)" title="Извлекать"><i class="ic-upload3"></i></th>
         <th style="text-align:center;padding:0; background:rgba(0,0,0,0.25)" title="Отображать в списке"><i class="ic-eye"></i></th>
         <th style="text-align:center;padding:0; background:rgba(0,0,0,0.25)" title="Разрешить сортировку"><i class="ic-sort-aasc"></i></th>
         <th style="text-align:center;padding:0; background:rgba(0,0,0,0.25)" title="Обязательно к заполнению"><i class="ic-exclam"></i></th>
        </tr>
        </thead>
        <tbody id="elFields">
            '.$defaultFields.$entityFields.'
        </tbody>
        </table>';

    if($ent!==false && !isset($nowindow)) {
        ajax::window('<div id="openWindow" title="'.$entityName.' : Отображение полей в списке" style="width:1000px; height:500px; overflow:scroll;">'.$out.'</div>');
        return false;
    }
    return $out;
}


	// Форма загрузки файлов
    static function showFormFiles($mn, $val, $array, $files){
        $out='';
        $mainFile=$array[$val['alias']];
        if(isset($files[$val['id']])) $array[$val['alias']]=$files[$val['id']];

	    if(is_array($array[$val['alias']]) && count($array[$val['alias']])>=1) {
	    	$out.=self::showFiles($array[$val['alias']],$mainFile, $val['view'], $val);
	    }
        if($out=='' || $val['multiple']==1) $disp='block';
        else $disp='none';

        // Если ID=0, то обычная загрузка, иначе - асинхронная
        if($array['id']!=0) $ajax=' onChange="ajaxPost(\'itemForm'.$array['entity_type'].'\',\'data::fileUpload?='.$val['id'].'&attrAlias='.$val['alias'].'\')" ';
        else $ajax='';
        $out.='<div id="upl'.$val['id'].'" class="row" style="display:'.$disp.'"><input type="file" multiple="multiple" name="'.$val['alias'].'[]"'.$ajax.'></div>';
        return $out;
    }

    // Назначение главного файла для поля элемента
    static function setMainFile($entityId=false, $fileId=false){
        global $entity,$item;
        if($entityId!==false && $fileId!==false){
            $entity=$entityId;
            $item=$fileId;
        }
        // Получим файл и аттрибуты поля
        $file=mysql::getArray("SELECT t1.attribute_id, t1.entity_id, t1.file, t1.ext, t1.type, t1.filesize, t1.name, t2.alias AS attralias, t2.default AS attrdefault, t2.name AS attrname, t2.entity_type_id AS attr_entity_type, t2.dop AS attrdop, t3.entity_type_alias
        FROM `file` AS t1
            JOIN `attributes` AS t2 ON t2.id = t1.attribute_id
            JOIN `entity_type` AS t3 ON t3.id = t2.entity_type_id
        WHERE t1.id=".escape($item)."
        LIMIT 1",true);
        if($file!=false){
            // Узнаем имя таблицы
            if($file['attr_entity_type']==0) $tableName='entity';
            else $tableName='data_'.$file['entity_type_alias'];
            // Получаем текущее значение
            mysql::query("UPDATE `".escape($tableName)."` SET `".escape($file['attralias'])."`='".escape($file['file'].'.'.$file['ext'])."' WHERE id=".escape($entity)." LIMIT 1");
        }
        cacheClear();
        return true;
    }

    // Парсинг размеров изображения из аттрибута и получение массива с данными
    static function imgAttrParse($stringData){
        $img=array();
        $v=explode(",",$stringData);
        foreach($v AS $k=>$v){
            $v=str_replace("*",":",$v);
            list($suffix,$width,$height)=explode(":",$v);
            $img[]=array("suffix"=>$suffix, "width"=>$width, "height"=>$height);
        }
        return $img;
    }

    // Отображение файлов заданного поля инфоблока
    static function showFiles($files, $mainFile='', $view='', $attr=false){
        global $settings;
        $imgList='';
        $filesList='';
        $out='';
        $renew='';

        $fileAttr=array();

        if(is_array($attr)) {
            $fileAttr=self::imgAttrParse($attr['imgsizes']);
        }

        if(is_array($files)){
            if(count($files)==1) $renew=','.$files[0]['attribute_id'];
            else $renew='';
        }
        $firstImageKey=false;
        $firstFileKey=false;
        if(is_array($files)) foreach($files AS $key=>$v){
			if(in_array($v['ext'],array('jpg','jpeg','png','gif','bmp','webp'))) {
			    if($firstImageKey===false) $firstImageKey=$key;
			    // Квадратные миниатюры
			    if($view==''){
                    if($v['file'].'.'.$v['ext']==$mainFile) $icodisplay='block';
                    else $icodisplay='none';
                    $imgList.='<li id="d'.$v['id'].'" class="drag"><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($v['entity_id']/100).'/'.$v['file'].'sys.'.$v['ext'].'"><i id="mico'.$v['id'].'" class="bm ic-file-picture" style="display:'.$icodisplay.';" title="Обложка"></i><span>'.$v['cnt'].'</span><div><i class="ic-editdoc" onclick="ajaxGet(\'data::imageEdit?='.$v['id'].'\')" title="Редактировать"></i><i class="ic-file-picture" onclick="setMainFile('.$v['id'].','.$v['entity_id'].')" title="Переместить на обложку"></i><i class="color-red ic-delete" style="float:right; margin-top: 4px;" onclick="fileDelete('.$v['id'].$renew.')" title="Удалить"></i></div></li>';
                }
                // Пропорциональные миниатюры: вписываем в квадрат
			    else {
			        $suffix=$fileAttr['suffix'];
                    if($v['file'].'.'.$v['ext']==$mainFile) $icodisplay='block';
                    else $icodisplay='none';
                    list($iw,$ih)=explode("x",$v['imagesize']);

                    // Горизонтальное
                    if($iw>$ih){
                        $h=round(95*$ih/$iw);
                        $imgStyle='width:95px; height:'.$h.'px; margin-top:'.round((95-$h)/2).'px;';
                    }
                    // Вертикальное
                    else {
                        $h=round(95*$iw/$ih);
                        $imgStyle='height:95px; width:'.$h.'px; margin-left:'.round((95-$h)/2).'px;';
                    }
                    $imgList.='<li id="d'.$v['id'].'" class="drag"><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($v['entity_id']/100).'/'.$v['file'].$suffix.'.'.$v['ext'].'" style="'.$imgStyle.'"><i id="mico'.$v['id'].'" class="bm ic-file-picture" style="display:'.$icodisplay.';" title="Обложка"></i><span>'.$v['cnt'].'</span><div><i class="ic-editdoc" onclick="ajaxGet(\'data::imageEdit?='.$v['id'].'\')" title="Редактировать"></i><i class="ic-file-picture" onclick="setMainFile('.$v['id'].','.$v['entity_id'].')" title="Переместить на обложку"></i><i class="color-red ic-delete" style="float:right; margin-top: 4px;" onclick="fileDelete('.$v['id'].$renew.')" title="Удалить"></i></div></li>';
                }
            }
            else {
                if($firstFileKey===false) $firstFileKey=$key;
                if($v['name']=='') $v['name']='Без названия';
                $filesList.='<tr id="d'.$v['id'].'"><td class="drag"><i class="ic-move-v"></i></td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($v['entity_id']/100).'/'.$v['file'].'.'.$v['ext'].'">'.$v['name'].'</a></td><td style="width:200px;"><b>'.$v['ext'].' '.file::bytes($v['filesize'],2).'</b></td><td style="width:30px;"><i class="color-red ic-delete" onclick="fileDelete('.$v['id'].$renew.')" title="Удалить"></i></td></tr>';
            }
	    }
		if($imgList!='' && isset($files[$firstImageKey]['attribute_id'])) $out.='<ul id="img_'.$files[$firstImageKey]['attribute_id'].'" class="gallery" style="width:100% !important;" onmouseover="dragInit(this.id,\'data::orderSave?=file&isFiles=1\')">'.$imgList.'</ul>';

		if($filesList!='' && isset($files[$firstFileKey]['attribute_id'])) $out.='<table id="fls_'.$files[$firstFileKey]['attribute_id'].'" style="width:100%;" class="cmstable4" onmouseover="dragTableInit(this.id,\'data::orderSave?=file&isFiles=1\')"><tbody>'.$filesList.'</tbody></table>';
		return $out;
    }


    /**
     * Отображение множественных полей
     * @param $template
     * @param $val
     * @param $value
     * @param $multiData
     * @return string
     */
    static function formMultiField($template, $val, $value, $multiData){
        $dop='';
        if( $val['style'] == '' && $val['cssclass']=='' ) $val['style']='width:200px;';
        if( $val['cssclass'] !='' ) $dop.=' class="'.$val['cssclass'].'" ';
        if( $val['style'] !='' ) $dop.=' style="'.$val['style'].'" ';
        if( $val['view']!='googlemap' && $val['view']!='youtube'){
            if( $val['maxlength'] !=0 ) $dop.=' maxlength="'.$val['maxlength'].'" ';
            if( $val['type']=='int' || $val['type']=='decimal' ) $dop.=' onkeypress="return inputNumber(event)" ';
        }

        $out='<div id="multiEl'.$val['id'].'">';
        $viewed=array();
        if(!is_array($value)){
            if(isset($multiData[$val['id']])) $value=$multiData[$val['id']];
            else { if(isset($array['value'])) $value=$array['value']; }
            if(!is_array($value)) $value[]=$value;
        }

        if(is_array($value)){
            foreach($value AS $k=>$v) {
                if(!isset($viewed[$v])) {
                    $t=$template;
                    $out.='<div id="opt'.$val['id'].'-'.$k.'" class="btn-group">';
                    $t=str_replace('%%k%%',$k,$t);
                    $t=str_replace('%%v%%',htmlspecialchars($v),$t);
                    $t=str_replace('%%dop%%',$dop,$t);
                    $out.=$t;
                    $out.='<div class="btn" style="padding:5px 0 0 6px" onclick="domRemove(\'opt'.$val['id'].'-'.$k.'\')" title="Удалить"><i class="ic-delete color-red"></i></div></div>';
                    $viewed[$v]=1;
                }
            }
        }

        $click='';
        if($val['element']=='date') {
            $click='onclick="newAttr(\'multiEl'.$val['id'].'\',\'array['.$val['alias'].'][]\','.htmlspecialchars('\'onclick="createCalendar(this.id)" onkeydown="this.blur()" class="date"').'\',\'calendar\')"';
        }
        elseif($val['element']=='varchar'){
            if($val['view']=='googlemap') $click='onclick="newAttr(\'multiEl'.$val['id'].'\',\'array['.$val['alias'].'][]\','.htmlspecialchars('\'onclick="createGooglemap(this.id)"').'\',\'googlemap\')"';
            else $click='onclick="newAttr(\'multiEl'.$val['id'].'\',\'array['.$val['alias'].'][]\',\''.htmlspecialchars($dop).'\',\'varchar\')"';
        }
        elseif($val['element']=='int' || $val['element']=='decimal') $click='onclick="newAttr(\'multiEl'.$val['id'].'\',\'array['.$val['alias'].'][]\',\''.htmlspecialchars($dop).'\',\''.$val['element'].'\')"';

        $out.='</div><div class="row"><div class="btn" '.$click.'><i class="ic-plus"></i>Добавить</div>';
        return $out;
    }


    /**
     * Получение списка элементов
     * @param char $mn псевдоним модуля
     * @param array|bool $fields
     * @param bool $where
     * @return array|bool
     */
    static function entityesList($mn, $fields=false, $where=false){
        $f=array(); $w=array();
        if(!is_array($fields)){
            if($fields===false) $f[]="*";
            else $f[]=$fields;
        }
        if(!is_array($where)){
            if($where===false) $w[]=" 1=1 ";
        }
        return mysql::getArray("SELECT ".implode(",",$fields)." FROM  `".escape($mn."_entity")."` AS  `e`
	INNER JOIN  `entity_post` AS  `ep`
	ON  `e`.`id` =  `ep`.`id` ");
    }


    /**
     * Возвращает список сттрибутов инфоблока и свойства аттрибутов в виде ассоциативного массива
     * @param int $typeId
     * @return array
     */
    static function attributes($typeId){
        return mysql::getArray("SELECT t1.*, t2.entity_type, t2.entity_type_alias
        FROM `attributes` AS t1
            JOIN `entity_type` AS t2 ON t2.id=t1.entity_type_id
        WHERE t1.entity_type_id IN(0, ".escape($typeId).")
        ORDER BY t1.frontend_order ASC");
    }

    static function entityBackendAttributes($entityType){
        static $allParams=array();
        if(!isset($allParams[$entityType])){
            // Получаем все аттрибуты заданной инфоблока
            $param=mysql::getArray("SELECT entity_type, entity_type_alias, backend_list, before_save, onsave  FROM `entity_type` WHERE id='".escape($entityType)."' LIMIT 1",true);
            $allParams[$entityType]=$param;
        }
        else $param=$allParams[$entityType];
        return $param;
    }


    // Апдейт всех сущностей
    static function entityesUpdate(){
        ajax::message(MESSAGE('MESS_chIsSaved'));
        // Если меняем что-либо в таблице с аттрибутами, то заставим переписовать список полей
        $array=mysql::getList("SELECT id FROM `entity_type` WHERE id>0");
        if($array!=false){
            foreach($array AS $val){
                self::queryGenerator($val);
            }
        }
        return true;
    }



    /**
     * Добавление нового типа инфоблока
     * Функция создает для нового инфоблока таблицу свойств
     * @param string $name
     * @param string|bool $alias
     * @return bool|int|string
     */
    static function typeAdd($name, $alias=false){
        if(!data::typeExists($name)){
            if($alias===false) $alias=$name;
            $alias=translit($alias);
            mysql::query("INSERT INTO `entity_type` SET `entity_type`='".escape($name)."', `entity_type_alias`='".escape($alias)."'");
            if(!mysql::error()){
                $typeId=mysql::insertId();
                mysql::startTransaction();
                // Основная таблица для хранения параметров инфоблока
                mysql::query("CREATE TABLE IF NOT EXISTS `data_".$alias."` (`id` bigint(20) unsigned NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
                mysql::query("ALTER TABLE `data_".$alias."` ADD CONSTRAINT FOREIGN KEY (`id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
                // Также добавляем таблицу для хранения текстовых элементов инфоблока
                mysql::query("CREATE TABLE `text_".$alias."` ( `id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
                mysql::query("ALTER TABLE `text_".$alias."` ADD CONSTRAINT FOREIGN KEY (`id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
                mysql::stopTransaction();
                self::queryGenerator($typeId);
                return $typeId;
            }
        }
        return false;
    }


    /**
     * Удаление типа инфоблока
     * @param int $entityId
     * @return bool|string
     */
    static function typeDelete(){
        global $item;
        $item=onlyDigit($item);
        if($item>0){
            $alias=mysql::getValue("SELECT `entity_type_alias` FROM `entity_type` WHERE id='".escape($item)."' LIMIT 1");
            if($alias!=false){
                ajax::sound("sys");
                mysql::query("DELETE FROM `entity_type` WHERE id='".escape($item)."' LIMIT 1");
                // Удаляем таблицы данных инфоблока
                mysql::query("DROP TABLE `data_".$alias."`");
                mysql::query("DROP TABLE `text_".$alias."`");
                return !mysql::error() ? true : mysql::error();
            }
        }
        else {
            ajax::dialogAlert("Нельзя удалить служебный инфоблок!");
        }
        return false;
    }


    /**
     * Возвращает true, если в таблице $mn."_entity_type" есть такой тип инфоблока
     * @param $mn
     * @param $entity
     * @return bool
     */
    static function typeExists($entity){
        return !mysql::getValue("SELECT id FROM `entity_type` WHERE entity_type='" . escape($entity) . "' LIMIT 1") ? false : true;
    }


    /**
     * Удаление данных для множественного аттрибута
     * @param $mn
     * @param $attribute
     * @return bool
     */
    static function multidataClear($attribute){
        mysql::query("DELETE FROM `multidata` WHERE `attribute_id`=".escape($attribute['id']));
        return true;
    }


    /**
     * Сохранение аттрибута заданного инфоблока
     * @param array $attribute
     * @return int | bool
     */
    static function attributeSave($attribute=false){
        global $item;
        if(isset($_POST['attr'])) $attribute=$_POST['attr'];
        if(isset($_POST['attr']['entity_type_id'])) $item=$_POST['attr']['entity_type_id'];


        if(isset($attribute['realElement'])){
            $attribute['element']=$attribute['realElement'];
            unset($attribute['realElement']);
        }

        if(data::attributeExists($attribute['entity_type_id'], $attribute['name'], $attribute['alias'])==$attribute['id']) {
            $attribute['dbsize']=str_replace(".",",",$attribute['dbsize']);
            if($attribute['dbsize']!='') {
                $attribute['dbsize']=str_replace('(','',$attribute['dbsize']);
                $attribute['dbsize']=str_replace(')','',$attribute['dbsize']);
                $attribute['dbsize']='('.$attribute['dbsize'].')';
            }

            if($attribute['id']==0){
                if(!isset($attribute['entity_type_id'])) {
                    error('data::attributeSave error: Field "entity_type_id" not defined!');
                    return false;
                }
                unset($attribute['frontend_order']);
                unset($attribute['backend_order']);
                $m=mysql::getArray("SELECT MAX(`frontend_order`) AS frontend_order, MAX(`backend_order`) AS backend_order FROM `attributes` WHERE entity_type_id IN (0, ".escape($attribute['entity_type_id']).")",true);
                $attribute['frontend_order']=onlyDigit($m['frontend_order'])+1;
                $attribute['backend_order']=onlyDigit($m['backend_order'])+1;
            }
            else {
                // Если новый аттрибут не множественный а раньше он был множественным,
                // то необходимо удалить значения из таблицы с множественными элементами
                if($attribute['multiple']==0) self::multidataClear($attribute);
            }

            // Обрабатываем специальные поля для типа ФАЙЛ
            if($attribute['type']=='file'){
                if(isset($attribute['fileext'])){
                    $attribute['dop']=serialize(array('fileext'=>$attribute['fileext'],'imgsizes'=>$attribute['imgsizes']));
                    unset($attribute['fileext']);
                    unset($attribute['imgsizes']);
                }
                if($attribute['multiple']==0) $attribute['dbsize']='(32)';
            }
            elseif($attribute['type']=='varchar'){
                if(isset($attribute['maxlength'])) {
                    if($attribute['maxlength']!='') $attribute['dbsize']='('.trim($attribute['maxlength']).')';
                }
            }

            $null=data::$fieldTypes[$attribute['type']]['dbNull'];// значение NULL для БД
            $dbDop=data::$fieldTypes[$attribute['type']]['dbDop'];// Доп параметры для БД
            $entity=mysql::getArray("SELECT * FROM `entity_type` WHERE id='".escape($attribute['entity_type_id'])."' LIMIT 1",true);
            // Если entity_type_id = 0, то создаем поле в таблице с ОБЩИМИ данными
            // А если поле типа TEXT, то добавим ячейку в таблицу для хранения текстовых данных
            if ($attribute['entity_type_id'] == 0) $tableName = 'entity';// Общая таблица для всех сущностей
            else $tableName = 'data_'.$entity['entity_type_alias'];// Персональная таблица инфоблока
            if ($attribute['type'] == 'textarea') $tableName = 'text_'.$entity['entity_type_alias'];// таблица для TEXT

            // Если entity_type_id == 0, то предварительно проверим, существует ли таблица
            if(!mysql::tableExists($tableName)) {
                ajax::dialogAlert('Сущность (entity_type=0) не может содержать поле типа TEXT. Если вы хотите, чтобы такая возможность была доступна, создайте таблицу самостоятельно, затем воссоздайте заново все параметры инфоблока');
                return false;
            }
            $oldAlias=false;
            if(isset($attribute['oldalias'])) {
                $oldAlias=$attribute['oldalias'];
                unset($attribute['oldalias']);
            }
            $atCopy=$attribute;
            if($atCopy['source']=='.' || $atCopy['source']=='..') $atCopy['source']='';
            unset($atCopy['options']);
            $ret=true;


            mysql::saveArray('attributes', $atCopy);
            if($attribute['id']==0) $attrId=mysql::insertId();
            else $attrId=$attribute['id'];

            //////////////////////////////////////////////////////////
            // ДОБАВЛЕНИЕ ПОЛЯ В БД
            //////////////////////////////////////////////////////////
            // Обрабатываем SELECT
            if(strlen($attribute['source'])<=8 || $attribute['source']==NULL) unset($attribute['source']);
            if($attribute['element']=='select'){
                $null=' NOT NULL ';
                $dbDop=' UNSIGNED ';
                // Сохраняем все опции
                if(!$attribute['source']) {
                    self::attributeOptionsSave($attrId, $attribute['options']);
                    $attribute['options']=mysql::getArray("SELECT id,value FROM `options` WHERE attr_id=".escape($attrId));
                    // Есть ли в списке элемент, выбранный по-умолчанию
                    $defVal=0;
                    foreach($attribute['options'] AS $o){ if($o['value']==$attribute['default']){ $defVal=$o['id']; break; } }
                    $attribute['default']=$defVal;
                }
                else {
                    $null=' NULL ';
                    if($attribute['default']=='') $attribute['default']=null;
                }
            }
            elseif($attribute['type']=='date'){
                if($attribute['multiple']==0){
                    if($attribute['default']=='') $attribute['default']=NULL;
                    else{
                        list($itD,$itM,$itY)=explode('.',$attribute['default']);
                        $attribute['default']=$itY.'-'.$itM.'-'.$itD;
                    }
                }
            }
            elseif($attribute['type']=='dateandtime'){
                if($attribute['multiple']==0){
                    if($attribute['default']=='') $attribute['default']=NULL;
                    else{
                        list($atrdef,$deftime)=explode(" ",$attribute['default']);
                        list($itD,$itM,$itY)=explode('.',$atrdef);
                        $attribute['default']=$itY.'-'.$itM.'-'.$itD.' '.$deftime;
                    }
                }
            }

            unset($attribute['options']);
            $default=data::$fieldTypes[$attribute['type']]['dbDefault'];
            if(isset($attribute['default'])) $default=" DEFAULT '".$attribute['default']."' ";

            if($attribute['id'] == 0) {
                $ret = $attrId;
                $attribute['id'] = $ret;
                $dbSize='';
                if(isset($attribute['dbsize'])){
                    if($attribute['dbsize']=='') $dbSize=self::$fieldTypes[$attribute['type']]['dbSize'];
                    else $dbSize=$attribute['dbsize'];
                }
                if(isset($attribute['maxlength'])) $dbSize=' ('.$attribute['maxlength'].') ';
                if($attribute['type']=='textarea') $dbSize='';
                if($attribute['type']=='decimal') $dbSize=$attribute['dbsize'];
                $dbSize=str_replace("((","(",$dbSize);
                $dbSize=str_replace("))",")",$dbSize);
                mysql::query("ALTER TABLE `".escape($tableName)."` ADD `".escape($attribute['alias'])."` ".data::$fieldTypes[$attribute['type']]['dbType'].$dbSize." ".$dbDop." ".$null." ".$default." COMMENT '".escape($attribute['name'])."'");
                if($attribute['element']=='select' && (!isset($attribute['source']) || strlen($attribute['source'])>8) ) {
                    // Добавляем внешний ключ для СЕЛЕКТа
                    //mysql::query("ALTER TABLE  `".escape($tableName)."` ADD INDEX (  `".escape($attribute['alias'])."` )");
                    //mysql::query("ALTER TABLE  `".escape($tableName)."` ADD CONSTRAINT FOREIGN KEY (  `".escape($attribute['alias'])."` ) REFERENCES  `".escape($mn.'_options')."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
                }
            }
            if($oldAlias!=false) {
                if($attribute['element']=='select' || $attribute['element']=='link' && (!isset($attribute['source']) || strlen($attribute['source'])>8)) {
                    // Узнаем название внешнего ключа
                    $kt=mysql::getForeignKeys($tableName);
                    if($oldAlias!=$attribute['alias'] && isset($kt[$oldAlias])){
                        mysql::query("ALTER TABLE `".escape($tableName)."` DROP FOREIGN KEY `".escape($kt[$oldAlias]['key'])."`");
                        mysql::query("ALTER TABLE `".escape($tableName)."` DROP KEY `".escape($oldAlias)."`");
                    }
                    mysql::query("ALTER TABLE `".escape($tableName)."` CHANGE COLUMN `".escape($oldAlias)."` `".escape($attribute['alias'])."`  ".data::$fieldTypes[$attribute['type']]['dbType']." ".data::$fieldTypes[$attribute['type']]['dbSize']." ".$dbDop." ".$null." ".$default." COMMENT '".escape($attribute['name'])."'");
                    if($oldAlias!=$attribute['alias']){
                        mysql::query("ALTER TABLE `".escape($tableName)."` ADD INDEX (  `".escape($attribute['alias'])."` )");
                        //mysql::query("ALTER TABLE `".escape($tableName)."` ADD CONSTRAINT FOREIGN KEY (  `".escape($attribute['alias'])."` ) REFERENCES  `".escape($mn.'_options')."` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
                    }
                }
            }
            cacheClear();
            self::queryGenerator($attribute['entity_type_id']);
            $item=$attribute['entity_type_id'];
            $GLOBALS['nowindow']=true;
            $GLOBALS['edited']=$attribute['id'];
            return self::attr();
        }
        else return false;
    }

    // Сохранение опций для аттрибута типа SELECT (выпадающий список)
    static function attributeOptionsSave($id, $options){
        // Сначала получим все опции
        $dbOptions=mysql::getSpecial("SELECT id, value AS name FROM `options` WHERE attr_id=".escape($id)." ORDER BY id ASC");
        // Не учитываем опции, которые присутствуют и в БД и в списке
        foreach($options AS $key=>$val){
            if($dbOptions!=false) foreach($dbOptions AS $k=>$v){
                if($v==$val){
                    unset($options[$key]);
                    unset($dbOptions[$k]);
                    break;
                }
            }
        }
        // Удаляем ненужные опции из БД
        if(is_array($dbOptions)){
            $dKeys=array_keys($dbOptions);
            if(count($dKeys)>=1) mysql::query("DELETE FROM `options` WHERE id IN (".implode(",",$dKeys).") AND id>0");
        }
        // Сохраняем массив новых опций
        if(count($options)>=1){
            $f=array();
            foreach($options AS $val) {
                if($val!='') $f[]="(".$id.", '".escape(trim($val))."')";
            }
            if(count($f)>=1) mysql::query("INSERT INTO `options` (`attr_id`, `value`) VALUES ".implode(", ",$f));
        }
        return true;
    }

    // Сохранение нового значения опций SELECT
    static function attributeAddValue($attributeId=false, $attributeName=''){
        global $item, $name;
        if($attributeId===false && $attributeName==''){
            $attributeId=$item;
            $attributeName=$name;
        }
        $attributeName=trim($attributeName);
        $m=mysql::getArray("SELECT t1.alias,t1.multiple,t1.attr_source,t1.source,
        t2.alias AS palias,t2.multiple AS pmultiple, t2.attr_source AS pattr_source, t2.source AS psource
        FROM `attributes` AS t1
            JOIN `attributes` AS t2 ON t2.id=t1.attr_source
        WHERE t1.id=".escape($attributeId)."
        LIMIT 1",true);
        // Если аттрибут является ссылкой на другой аттрибут, то обработаем это
        if($m['attr_source']!=0) {
            $attributeId=$m['attr_source'];
            $m['alias']=$m['palias'];
            $m['multiple']=$m['pmultiple'];
            $m['attr_source']=$m['pattr_source'];
            $m['source']=$m['psource'];
        }
        if($m['multiple']==0) $tblName='options';
        else $tblName='multidata';
        // Получаем все опции аттрибута
        $array=mysql::getSpecial("SELECT id,value AS name FROM `".escape($tblName)."` WHERE attr_id IN(0,".$attributeId.") ORDER BY value ASC");
        $selected=false;
        $options='';
        foreach($array AS $key=>$val){ if($val==$attributeName) $selected=$key; }
        // Добавляем опцию
        if($selected===false){
            mysql::query("INSERT INTO `".escape($tblName)."` SET attr_id=".escape($attributeId).", value='".escape($attributeName)."'");
            $elid=mysql::insertId();
            $array[$elid]=$attributeName;
            $selected=$elid;
        }
        asort($array);
        foreach($array AS $key=>$val){
            if($selected===$key) $sel=' selected="selected" ';
            else $sel='';
            $options.='<option id="sop'.$key.'" '.$sel.' value="'.$key.'">'.$val.'</option>';
        }
        cacheClear();
        $fld='edSel'.$attributeId;
        return array($fld=>$options);
    }


    /**
     * Удаление аттрибута
     * @param int $attributeId
     * @return bool
     */
    static function attributeDelete($attributeId=false){
        global $item;
        if($attributeId===false) $attributeId=$item;
        if($_SESSION['user']['group']<=1){
            $atr=mysql::getArray("SELECT t1.id, t1.alias, t1.entity_type_id, t2.entity_type_alias
        FROM `attributes` AS t1
         JOIN `entity_type` AS t2 ON t2.id=t1.entity_type_id
        WHERE t1.id=".escape($attributeId)."
        LIMIT 1", true);
            if($atr!=false){
                $tblName = $atr['entity_type_id'] == 0 ? 'entity' : 'data_'.$atr['entity_type_alias'];
                $kt=mysql::getForeignKeys($tblName);
                mysql::startTransaction();
                mysql::query("DELETE FROM `options` WHERE attr_id=".escape($attributeId));
                if(isset($kt[$atr['alias']])){
                    mysql::query("ALTER TABLE `".escape($tblName)."` DROP FOREIGN KEY `".escape($atr['alias'])."`");
                }
                // Если поле индексное, то сначала удалим индекс
                mysql::indexDrop($tblName,$atr['alias']);
                mysql::query("ALTER TABLE `".escape($tblName)."` DROP `".escape($atr['alias'])."`");
                mysql::query("DELETE FROM `multidata` WHERE attribute_id=".escape($attributeId));
                mysql::query("DELETE FROM `attributes` WHERE id=".escape($attributeId)." LIMIT 1");
                mysql::stopTransaction();
                data::queryGenerator($atr['entity_type_id']);
                return true;
            }
            else return false;
        }
        else {
            ajax::dialogAlert("У вас нет прав на удаление атрибутов!");
        }
    }


    /**
     * Проверка, существует ли заданный аттрибут у инфоблока
     * Если аттрибут найден, то возвращается его ID, иначе - false
     * @param int $entityTypeId
     * @param string $attributeName
     * @param string $attributeAlias
     * @return int | bool
     */
    static function attributeExists($entityTypeId, $attributeName, $attributeAlias){
        if(mysql::getValue("SELECT id FROM `attributes` WHERE `entity_type_id` IN('0','".escape($entityTypeId)."') AND `name`='" . escape($attributeName) . "' LIMIT 1")!=false) return true;
        return mysql::getValue("SELECT id FROM `attributes` WHERE `entity_type_id` IN('0','".escape($entityTypeId)."') AND `alias`='".escape($attributeAlias)."' LIMIT 1");
    }


    /**
     * Вывод ячейки таблицы элементов в админке в зависимости от типа и содержимого поля
     * @param string $mn
     * @param array $entity
     * @param array $attr
     * @param string $attributeAlias
     * @param string $editLink - ссылка на редактирование элемента (для аттрибута NAME)
     * @return string
     */
    static function adminTableField($mn, $typeControl, $entity, $attr, $attributeAlias, $editLink, $orderContent=false){
        $ret='';
        global $settings;
        if(!isset($attr['attr'][$attributeAlias])) return '';
        else $attr=$attr['attr'][$attributeAlias];
        if($attr['entity_type_id']==0) $valueTable='entity';
        else $valueTable='data_'.$attr['entity_type_alias'];
        if($orderContent===false) $orderContent='<td class="small">'.$entity[$attributeAlias].'</td>';

        $hide=0;
        if($typeControl===true && $attr['folderhide']==1) $hide=1;
        if($typeControl=='CHILD' && $attr['childhide']==1) $hide=1;
        $fileFolder=floor($entity['id']/100);

        //if(isset($entity[$attributeAlias])){
            if($attr['backend_list']==1 && $hide==0){

                // В зависимости от типа аттрибута отображаем ячейку таблицы
                if($attr['element']=='id') $ret='<td class="small">'.$entity[$attributeAlias].'</td>';
                elseif ($attr['element']=='order') $ret=$orderContent;
                elseif ($attr['element']=='user_id'){
                    if(isset($entity[$attr['alias'].':name'])) $ret='<td>'.$entity[$attributeAlias.':name'].'</td>';
                    else $ret='<td>'.$entity[$attributeAlias].'</td>';
                }
                elseif ($attr['element']=='checkbox') $ret='<td><input type="checkbox" '.checkboxSelected($entity[$attributeAlias]).' onchange="ajaxGet(\'data::checkbox?='.$entity['id'].'&field='.$valueTable.'.'.$attributeAlias.'&entity='.$entity['entity_type'].'\')" title="'.htmlspecialchars($attr['name']).'"></td>';
                elseif ($attr['element']=='file') {
                    $c='';
                    if(strlen($entity[$attributeAlias])>=6) {
                        list($fname,$fext)=explode('.',$entity[$attributeAlias]);
                        
                        if($fext=='jpg' || $fext=='gif' || $fext=='jpeg' || $fext=='png' || $fext=='webp') $c='<a rel="iLoad" href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.$fileFolder.'/'.$fname.'.'.$fext.'"><img class="imagemini" src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.$fileFolder.'/'.$fname.'sys.'.$fext.'" alt=""></a>';
                        else $c='<a href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.$fileFolder.'/'.$fname.'.'.$fext.'"><i class="ic-download"></i></a>';
                    }
                    $ret='<td>'.$c.'</td>';
                }
                elseif ($attr['element']=='varchar'){
                    if($attr['view']=='googlemap'){
                        $outValue=$entity[$attributeAlias];
                        $v=explode('|',$entity[$attributeAlias]);
                        if(count($v)==5){
                            $outValue='<i class="ic-earth"></i>'.$v[4];
                        }
                        $ret='<td class="small">'.$outValue.'</td>';
                    }
                    else {
                        if($attributeAlias=='name') $ret='<td>'.$editLink.'</td>';
                        else $ret='<td class="small">'.$entity[$attributeAlias].'</td>';
                    }
                }
                elseif ($attr['element']=='datetime' || $attr['element']=='int' || $attr['element']=='select' || $attr['element']=='link') $ret='<td class="small">'.$entity[$attributeAlias].'</td>';
                elseif ($attr['element']=='date' || $attr['element']=='dateandtime'){
                    $value=$entity[$attributeAlias];
                    if($value==NULL) $value='';
                    list($value,$tvalue)=explode(" ",$value);
                    if($value!=''){
                        $value=str_replace('-','.',$value);
                        if($value=='0000.00.00' || $value=='00.00.0000') $value='';
                        if($value!=''){
                            $ddl=explode('.',$value);
                            if(strlen($ddl[0])==4) $value=$ddl[2].'.'.$ddl[1].'.'.$ddl[0];
                            else $value=$ddl[0].'.'.$ddl[1].'.'.$ddl[2];
                        }
                        if($value!='' && $tvalue!=''){
                            $mv=explode(":",$tvalue);
                            $value.=' '.$mv[0].':'.$mv[1];
                        }
                    }
                    $ret='<td class="small">'.$value.'</td>';
                }
                elseif ($attr['element']=='textarea') $ret='<td><div class="smallgrey" style="padding:6px;">'.$entity[$attributeAlias].'...</div></td>';
                elseif ($attr['element']=='decimal') {
                	if($attr['cssclass']=='') $attr['cssclass']='size-s';
                    if($attr['quickEdit']==0){
                    	$ret='<td class="align-right smallBold" style="padding-right:6px">'.triada($entity[$attributeAlias],2).'</td>';
                    }
                    else {
                    	// Если quickEdit==1 отобразим форму
	                    $ret='<td><input id="qeIn'.$attr['id'].'e'.$entity['id'].'" type="text" class="'.$attr['cssclass'].' qeField" onInput="qeDecimalControl('.$attr['id'].','.$entity['id'].',this.value)" value="'.triada($entity[$attributeAlias],2).'"></td>';
                    }
                }
                elseif($attr['element']=='color'){
	                $ret='<td><div style="width:18px; height:18px; background:'.$entity[$attributeAlias].'" title="'.$entity[$attributeAlias].'">&nbsp;</div></td>';
                }
                else $ret='<td>'.$entity[$attributeAlias].'</td>';
            }
        //}
        return $ret;
    }

    /**
     * Составление заголовков таблицы-списка элементов админки
     * @param string $mn
     * @param array $attr - массив аттрибутов
     * @param bool|array $orderRel - варианты сортировки
     * @param bool|string $order - текущая сортировка
     * @param bool|string $uri
     * @param bool|string $div
     * @return string
     */
    static function adminTableHeaders($mn, $attr, $typeControl, $orderRel=false, $order=false, $uri=false, $div=false, $orderHeader=false){
        $out='';

        $click='';
        //$counter=0;
        $orderKey=0;
        $orderLetter='';

        if($order!=false) list($orderKey,$orderLetter)=explode('-',$order);
        foreach($attr AS $val){
            $hide=0;
            if($val['folderhide']==1 && $typeControl===true) $hide=1;
            if($val['childhide']==1 && $typeControl==='CHILD') $hide=1;
            if($val['backend_list']==1 && $hide==0){
                $click='';
                $fieldSize='';
                $fieldTitle='';
                $fieldValue=$val['name'];
                $orderIcon='';
                $fieldClass='';
                if(isset($orderRel[$val['alias']])){
                    $fieldClass=' class="ordered" ';
                    if($orderRel[$val['alias']]==$orderKey){
                        if($orderLetter=='a') { $orderIcon='ic-movedown'; $otherLetter='d'; }
                        else { $orderIcon='ic-moveup'; $otherLetter='a'; }
                        $click=' onclick="ajaxGet(\''.$mn.'::'.$uri.'&changeOrder='.$orderKey.'-'.$otherLetter.'\')" ';
                    }
                    else $click=' onclick="ajaxGet(\''.$mn.'::'.$uri.'&changeOrder='.$orderRel[$val['alias']].'-a\')" ';
                }
                if ($val['element']=='id') { $fieldSize=54; $fieldTitle=$val['name']; $fieldValue='#'; }
                elseif ($val['element']=='order') {
                    $fieldSize=40; $fieldTitle=$val['name'];
                    if($orderHeader===false) $fieldValue='#';
                    else $fieldValue=$orderHeader;
                }
                elseif ($val['element']=='checkbox' || $val['element']=='file') {
                    $fieldSize=24; $fieldTitle=$val['name']; $fieldValue='&nbsp;';
                    if($val['icon']!='') $fieldValue='<i class="'.$val['icon'].'"></i>';
                }
                elseif($val['element']=='dateandtime') { $fieldSize=110; }


                if($fieldSize!='') $fieldSize=' style="width:'.$fieldSize.'px" ';
                if($fieldTitle!='') $fieldTitle=' title="'.htmlspecialchars($fieldTitle).'"';
                if($orderIcon!='') $orderIcon='<i class="ordersign '.$orderIcon.'"></i>';
                $out.='<th'.$fieldSize.$click.$fieldClass.$fieldTitle.$click.'>'.$fieldValue.$orderIcon.'</th>';
                //$counter++;
            }
        }
        return $out;
    }


    // Генератор, создающий шаблоны MYSQL запросов для получения данных из базы
    static function queryGenerator($typeId=0){
        global $item;
        if($typeId===0 && isset($item)) $typeId=$item;
        $attr=self::attributes($typeId);
        $frontend='';
        $select='';
        // Получаем массив для LIST фронтенда
        uasort($attr, 'data::attrBackendSort');
        $backend=self::queryGeneratorLIST($attr, 'backend');
        $back_list=self::queryGeneratorLIST($attr, 'backend_list');
        
        mysql::query("UPDATE `entity_type` SET `frontend_list`='".escape(serialize($frontend))."', `backend_list`='".escape(serialize($back_list))."', `backend_select`='".escape(serialize($backend))."', `frontend_select`='".escape(serialize($select))."'  WHERE id='".escape($typeId)."' LIMIT 1");
        ajax::sound('click');
        return false;
    }


    /**
     * Сортировка массива аттрибутов для фронтенда
     * @param $a
     * @param $b
     * @return int
     */
    static function attrFrontendSort($a, $b){
        if ($a['frontend_order'] == $b['frontend_order']) return 0;
        return ($a['frontend_order'] < $b['frontend_order']) ? -1 : 1;
    }

    static function attrBackendListSort($a, $b){
        if ($a['backend_order'] == $b['backend_order']) return 0;
        return ($a['backend_order'] < $b['backend_order']) ? -1 : 1;
    }

    // Сортировка массива аттрибутов для бэкэнда
    static function attrBackendSort($a, $b){
        if($a['element']=='checkbox') {
            if($a['element']!=$b['element']) return -1;
            else return ($a['entity_type_id'] < $b['entity_type_id']) ? -1 : 1;
        }
        elseif($b['element']=='checkbox'){
            if($a['element']!=$b['element']) return 1;
            else return ($a['entity_type_id'] < $b['entity_type_id']) ? -1 : 1;
        }
        if ($a['entity_type_id'] == $b['entity_type_id']){
            if ($a['backend_order'] == $b['backend_order']) return 0;
            return ($a['backend_order'] < $b['backend_order']) ? -1 : 1;
        }
        else {
            if ($a['entity_type_id'] > $b['entity_type_id']) return 1;
            else return -1;
        }
    }

    // Возвращает дерево каталога от заданного потомка
	static function getTree($categoryEntityTypeId, $sel=0){
		global $treeDeep;
		global $parray;
		global $selected;
		$selected=$sel;
		global $opened;
		$treeDeep=0;

		// Сначала узнаем, как называется основная таблица параметров категорий
		$parray=mysql::getArray("SELECT t1.id, t1.parent_id AS parent, t1.name, t1.hidden, t2.order
		    FROM `entity` AS t1
		        JOIN `data_category` AS t2 ON t2.id=t1.id
		    WHERE t1.entity_type=".escape($categoryEntityTypeId)."
		    ORDER BY t1.parent_id ASC, t2.`order` ASC");

		if($parray===false) return '<div class="error">Каталог не найден!</div>';
		else {
			// Принудительно открываем активную ветку каталога, а текже первичные
			$start=(count($parray)-1);
			$prevOpened=$selected;
			$opened[]=0;
			$opened[]=1;
			if($start>=1){
				for($i=$start; $i>=0; $i--){
					if($parray[$i]['id']==$prevOpened || $parray[$i]['parent']==$prevOpened){
						$opened[]=$parray[$i]['id'];
						$prevOpened=$parray[$i]['parent'];
					}
				}
			}
		}
		$tree=self::buildTree($parray,1);
		return '<div class="cmstree" id="tree" style="cursor:pointer;">'.$tree.'</div>';
    }

    //Генерация дерева категорий каталога
	static function buildTree($treeArray,$parentId){
		global $tree;
		global $deep;    // текущая глубина в дереве
		global $count;   // номер элемента
		global $opened;  // Массив с ID открытых папок
		global $selected;// Выбранная папка
		global $pluscounter; // Счетчик веток
		if(!isset($pluscounter)) $pluscounter=0;
		if($selected==false) $selected=$treeArray[0]['id'];
		if($parentId===false) $parentId=0;
		if(!isset($count)) $count=0;
		$this_count=0;
		foreach ($treeArray as $element){
			if($element['parent']==$parentId){
				$this_count++;
				if($element['hidden']==1) $folderStyle='g';
				else $folderStyle='y';
				if($element['id']==$selected) $folderStyle='a';
				$link='<li id="ctl'.$element['id'].'"><i id="a'.$element['id'].'" class="'.$folderStyle.'"></i><b id="cn'.$element['id'].'" onClick="catOpen('.$element['id'].')">'.$element['name'].'</b>';
				if($element['id']==1 || $element['parent']==1) $opened[]=$element['id'];
				if(in_array($element['id'],$opened)) {
					$ulstyle='style="display:block"';
					$plusstyle=' minus';
				}
				else {
					$ulstyle='style="display:none" ';
					$plusstyle='';
				}
				$ul='<ul '.$ulstyle.'id="c'.$element['id'].'">';
				if($count==0) $ul='<ul id="cattree">';
				$count++;
				if ($this_count==1) {
					if($pluscounter>0) {
                        if($element['id']>=3) $tree.='<span id="plus'.$element['id'].'" class="show'.$plusstyle.'" onClick="trChang('.$element['id'].')"></span>'.$ul;
                        else $tree.=$ul;
                    }
					else $tree.=$ul;
					$deep++;
					$pluscounter++;
				}
				$tree.=$link;
				self::buildTree($treeArray,$element['id']);
				$tree.='</li>';
			}
		}
		if ($this_count>0){
			$tree.='</ul>';
			$deep--;
		}
		return $tree;
	}
}