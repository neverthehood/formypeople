<?php
////////////////////////////////////////////////////////
///    Импорт
////////////////////////////////////////////////////////

class import{

    static $stepStrings=100;// Количество обрабатываемых за 1 раз строк
	
	
	static function export(){
		global $settings;
		// Создаем sitemap
		$sitemap='<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
';
		
		// Разделы сайта
		$array=page::getPages();
		foreach($array AS $val){
			$url=page::getPath($val['id']);
			if($url=='/') $url='';
			$url=$settings['protocol'].$settings['siteUrl'].'/'.$url;
			$sitemap.='<url>
<loc>'.$url.'</loc>
<changefreq>weekly</changefreq>
<lastmod>'.date("Y-m-d").'</lastmod>
<priority>0.5</priority>
</url>
';
		}
		
		// Товары
		$array=mysql::getArray("SELECT t1.*,t2.name, t1.art, t2.parent_id, DATE_FORMAT(t2.date_add,'%Y-%m-%d') AS date_add
		FROM `entity_cache` AS t1
			JOIN `entity` AS t2 ON t2.id=t1.id
		WHERE
			t2.hidden='0'
		ORDER BY t1.id ASC");
		if($array!=false){
			foreach($array AS $val){
				$url=$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'];
				$sitemap.='<url>
<loc>'.$url.'</loc>
<changefreq>weekly</changefreq>
<lastmod>'.$val['date_add'].'</lastmod>
<priority>1.0</priority>
	<image:image>
		<image:loc>'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.$val['photo'].'</image:loc>
		<image:caption>'.htmlspecialchars($val['name']).'</image:caption>
		<image:title>'.htmlspecialchars($val['name']).'</image:title>
	</image:image>
</url>
';
			}
		}
		$sitemap.='</urlset>';
		file::save($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml',$sitemap,0775);
		unset($sitemap);
		
		// Формируем файл YML
		$yml='<?xml version="1.0" encoding="UTF-8"?>
<yml_catalog date="'.date("Y-m-d").'T'.date("H:i:s").'+03:00">
    <shop>
        <name>E-climat</name>
        <company></company>
        <url>'.$settings['protocol'].$settings['siteUrl'].'/</url>
        <currencies>
            <currency id="BYN" rate="1"/>
        </currencies>
        <categories>
<category id="1">Каталог</category>
';
		$cats=mysql::getArray("SELECT t1.id, t1.alias, t2.name
		FROM `data_category` AS t1
			JOIN `entity` AS t2 ON t2.id=t1.id
		WHERE t2.parent_id=43
		AND t2.hidden='0'");
		if($cats!=false){
			foreach($cats AS $val){
				$yml.='<category id="'.$val['id'].'" parentId="1">'.htmlspecialchars($val['name']).'</category>
';
			}
		}
		$yml.='</categories>
        <delivery-options>
            <option cost="5" days="1"/>
        </delivery-options>
        <offers>
';
		if($array!=false){
			foreach($array AS $val){
				$url=$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'];
				$yml.='<offer id="'.$val['id'].'">
<name>'.htmlspecialchars($val['name']).'</name>
<url>'.$url.'</url>
<price>'.$val['discount'].'</price>
<oldprice>'.$val['price'].'</oldprice>
<currencyId>BYN</currencyId>
<categoryId>'.$val['parent_id'].'</categoryId>
<picture>'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.$val['photo'].'</picture>
<delivery>true</delivery>
<barcode>'.htmlspecialchars($val['art']).'</barcode>
<delivery-options>
    <option cost="5" days="1"/>
</delivery-options>
</offer>
';
			}
		}
		$yml.='</offers>
    </shop>
</yml_catalog>
';
		
		file::save($_SERVER['DOCUMENT_ROOT'].'/market.yml',$yml,0775);
		unset($yml);
	}

    static function fileRemove(){
        global $item;
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/uploaded/import/'.$item)){
            unlink($_SERVER['DOCUMENT_ROOT'].'/uploaded/import/'.$item);
        }
        return self::importStart();
    }

    static function fileUpload(){
        $err='';
        if(isset($_FILES['userfile']) && $_FILES['userfile']['name']!=''){
            if($_FILES['userfile']['error']==0){
                list($fname, $fext)=explode(".",$_FILES['userfile']['name']);
                if($fext=='csv'){
                    if(!copy($_FILES['userfile']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/uploaded/import/import'.time().'.csv')){
                        $err='Не удалось загрузить файл. Попробуйте еще раз.';
                    }
                }
                else{
                    $err='Недопустимый формат файла. Разрешен импорт файлов в формате CSV.';
                }
            }
            else $err='Произошла ошибка при загрузке файла';
        }
        else {
            $err='Не выбран файл';
        }
        if($err=='') return self::importStart();
        else {
            ajax::dialogAlert($err);
        }
    }


    // $item= номер текущей строки в файле
    // $file= имя файла CSV
    // $strings= всего строк в файле (для отображения прогресса)
    static function importStart(){
        global $item, $file, $strings, $ftell, $settings, $newItems, $modItems, $newCats, $errors;
        $out = '';


        // Если задано имя файла и указатель в файле, то продолжаем импорт
        if (isset($file) && isset($ftell)) {

            $out .= '<div class="field"><h3>Каталог: Импорт данных из файла</h3><hr></div>';
            $file_name = $_SERVER['DOCUMENT_ROOT'] . '/uploaded/import/' . $file;
            $fileSize = filesize($file_name);
            // В данном случае импорт настроен под формат "Текст с разделителем табуляции"
            //$dataSeparator='	'; // разделитель данных. Для CSV ";" или "	" - Табуляция
            $dataSeparator = ';';

            $dataEnclosure = '"'; // Символ для экранирования служебных символов
            $startString = 1;
            $itemsCount = self::$stepStrings;
            $forImport = array();
            $importCats = array();
            $statShowed = false;
            $fieldsInString = 11; // Правильное количество элементов в строке

            if(!isset($newItems)){
                $newItems=0;
                $modItems=0;
                $newCats=0;
                $errors=0;
            }


            // ШАГ 1:
            // Получаем импортируемые товары в массив $forImport
            if (($handle_f = fopen($file_name, "r")) !== false) {
                if (isset($ftell)) fseek($handle_f, $ftell);// Перемещаем указатель на предыдущее место в файле
                else $ftell = 0;
                $i = 0;
                if (!isset($x)) $x = 0;
                //if ($x === 0) {
                // Создаем файл для хранения ошибок импорта
                //file::save($errorFile, '', 0775);
                //}
                // построчное считывание и анализ строк из файла
                while (($readData = self::fgetcsv($handle_f, 60000, $dataSeparator)) !== false) {
                    if ($x >= $startString) {
                        $itemsCount--;
                        $fCount=count($readData);
                        if($fCount==$fieldsInString || $fCount==($fieldsInString+1)){
                            $im=array(
                                'id'=>onlyDigit(trim($readData[0])),
                                'art'=>trim(iconv('windows-1251','utf-8',$readData[1])),
                                'name'=>trim(iconv('windows-1251','utf-8',$readData[2])),
                                'brand'=>trim(iconv('windows-1251','utf-8',$readData[3])),
                                'folderName'=>trim(iconv('windows-1251','utf-8',$readData[4])),
                                'folder'=>onlyDigit(trim($readData[5])),
                                'price'=>trim(str_replace(",",".",$readData[6])),
                                'discount'=>trim(str_replace(",",".",$readData[7])),
                                'active'=>onlyDigit(trim($readData[8])),
                                'insklad'=>onlyDigit(trim($readData[9])),
                                'inmagaz'=>onlyDigit(trim($readData[10]))
                            );
                            $im['id']=(int)$im['id'];
                            // name не более 128 символов
                            if(mb_strlen($im['name'],'utf-8') > 128){
                                $im['name'] = mb_substr($im['name'],0,128,'utf-8');
                            }
                            // Нужно ли добавлять товары в массив для импрта?
                            $forImport[$im['folder']][$im['id']]=$im;
                        }
                        else{
                            fclose($handle_f);
                            ajax::dialogAlert("Недопустимая структура документа");
                            return false;
                        }
                        if ($itemsCount == 0) {
                            break;
                        }
                    }
                    $x++;
                    $i++;
                }
            }
            else {
                echo 'STOP IMPORT!!!!';
                fclose($handle_f);
            }
            //fclose($handle_f);
            //$fileSize=10000;
            //$ftell=10000;




            // Шаг второй.
            // Если в массиве ForImport есть что-то, значит продолжаем работу
            if(!empty($forImport)){

                // Получим список папок
                $folders=array();
                $f=mysql::getArray("SELECT
                    t1.id, t1.origid, t2.name, t3.entity_type_alias AS entityAlias, t1.child_entity_type AS typeId 
                FROM
                    `data_category` AS t1
                    JOIN `entity` AS t2 ON t2.id=t1.id
                    JOIN `entity_type` AS t3 ON t3.id=t1.child_entity_type
                WHERE 
                    t1.content=16");
                if($f!=false){
                    foreach($f AS $val){
                        if(isset($folders[$val['origid']])){
                            echo 'ОШИБКА!!!! Индексы папок '.$val['name'].' и '.$folders[$val['origid']]['name'].' совпадают!';
                        }
                        else {
                            $folders[$val['origid']]=$val;
                        }
                    }
                }
//                echo '<pre>';
//                print_r($forImport);
//                echo '</pre>';

                // Получим список всех брендов
                $brands=array();
                $b=mysql::getArray("SELECT id,name FROM `entity` WHERE entity_type=45");
                // переводим бренды в uppercase
                foreach($b AS $val){
                    $n=mb_strtoupper($val['name'],'utf-8');
                    $brands[$n]=$val['id'];
                }

                // Создаем бренды, если их нет в списке
                foreach($forImport AS $key=>$val){
                    foreach ($val AS $v){
                        $brandName=mb_strtoupper(trim($v['brand']));
                        if(isset($brands[$brandName])){
                            $brandId=$brands[$brandName];
                        }
                        else{
                            // Создаем новый бренд
                            mysql::query("INSERT INTO `entity` SET `parent_id`=100, `name`='".escape(trim($v['brand']))."', `entity_type`=45, `hidden`='0', `date_add`='".date("Y-m-d H:i:s",time())."', `owner`=22");
                            $brandId=mysql::insertId();
                            mysql::query("INSERT INTO `data_brand` SET id=".escape($brandId));
                            mysql::query("INSERT INTO `text_brand` SET id=".escape($brandId));
                            $brands[$brandName]=$brandId;
                            $newCats++;
                        }
                    }
                }

                // Импорт загруженных позиций
                foreach($forImport AS $key=>$val){
                    if($folders[$key]['entityAlias']!=''){
                        // Получаем все существующие товары текущей группы в массив $existed
                        $e=mysql::getArray("SELECT t1.id, t1.hidden, t1.origid, t2.art, t2.exist, t2.price, t2.discount
                        FROM `entity` AS t1
                            JOIN `data_".escape($folders[$key]['entityAlias'])."` AS t2 ON t2.id=t1.id
                        WHERE
                            t1.origid IN(".implode(",",array_keys($val)).")");
                        $existed=array();
                        if($e!=false){
                            foreach($e AS $v){
                                $existed[$v['origid']]=$v;
                            }
                            unset($e);
                        }

                        // Проходимся по массиву и сверяем данные прайса с данными в базе
                        foreach($val AS $v){
                            $t=array();
                            $brandName=mb_strtoupper(trim($v['brand']));
                            $brandCode=$brands[$brandName];
                            $t['name']=$v['name'];
                            $t['hidden']=0;
                            if($v['active']==0) $t['hidden']=1;
                            $t['brand']=$brandCode;
                            $t['parent_id']=$folders[$key]['id'];
                            $t['entity_type']=$folders[$key]['typeId'];
                            $t['origid']=$v['id'];
                            $t['art']=$v['art'];
                            $t['price']=$v['price'];
                            $t['discount']=$v['discount'];
                            // insklad, inmagaz
                            // ---------------------
                            // Наличие
                            // ---------------------
                            // 28 - На складе, 27 - в магазине, 29 - под заказ
                            $t['exist']=29;
                            if($v['insklad']==1) $t['exist']=28;
                            if($v['inmagaz']==1) $t['exist']=27;

                            if($brandCode!=''){
                                // Если товар есть в базе
                                if(isset($existed[$v['id']])){
                                    $e=$existed[$v['id']];
                                    if($e['exist']!=$t['exist'] || $e['price']!=$t['price'] || $e['discount']!=$t['discount'] || $e['hidden']!=$t['hidden']){
                                        // Активный (hidden)
                                        mysql::query("UPDATE `entity` SET hidden='".$t['hidden']."' WHERE id=".$e['id']." LIMIT 1");
                                        //
                                        mysql::query("UPDATE `data_".$folders[$key]['entityAlias']."` SET 
                                `art`='".escape($t['art'])."',
                                `exist`='".escape($t['exist'])."',
                                `price`=".escape($t['price']).",
                                `discount`=".escape($t['discount'])." 
                                WHERE id=".$e['id']." LIMIT 1");
                                        $modItems++;

                                        // Апдейт таблицы кэша
                                        mysql::query("UPDATE `entity_cache` SET
                                `art`='".escape($t['art'])."',
                                `exist`='".escape($t['exist'])."',
                                `price`=".escape($t['price']).",
                                `discount`=".escape($t['discount'])."
                                WHERE id=".$e['id']." LIMIT 1");
                                    }
                                }
                                else {
                                    $newItems++;

                                    // Создаем новый товар
                                    mysql::query("INSERT INTO `entity` SET
                            name='".escape($t['name'])."',
                            hidden='1',
                            parent_id='".escape($t['parent_id'])."',
                            entity_type='".escape($t['entity_type'])."',
                            date_add='".escape(date("Y-m-d H:i:s"))."',
                            owner=22,
                            origid=".escape($t['origid']));
                                    $id=mysql::insertId();

                                    mysql::query("INSERT INTO `data_".$folders[$key]['entityAlias']."` SET
                            `id`=".escape($id).",
                            `art`='".escape($t['art'])."',
                            `exist`='".escape($t['exist'])."',
                            `price`=".escape($t['price']).",
                            `discount`=".escape($t['discount']).",
                            `brand`=".escape(trim($brandCode)));

                                    mysql::query("INSERT INTO `text_".$folders[$key]['entityAlias']."` SET
                            `id`=".escape($id));

                                    // Апдейтим кэш
                                    mysql::query("INSERT INTO `entity_cache` SET
                            `id`=".escape($id).",
                            `art`='".escape($t['art'])."',
                            `exist`='".escape($t['exist'])."',
                            `price`=".escape($t['price']).",
                            `photo`='',
                            `discount`=".escape($t['discount']));
                                }
                            }
                            else {
                                $errors++;
                                echo 'Не найден бренд '.$brandName.'('.$brands[$brandName].')';
                                echo '<pre>';
                                print_r($t);
                                echo '</pre>';
                            }
                        }
                    }
                    else {
                        //$errors++;
                        echo 'Не найдена таблица '.$folders[$key]['entityAlias'].' ('.$key.')';
                        echo '<pre>';
                        print_r($folders[$key]);
                        echo '</pre>';
                    }
                }


                sleep(1);
                flush();

//                echo '<pre>';
//                print_r($brands);
//                echo '</pre>';

                $out.='<div class="info"><b>ВНИМАНИЕ! В зависимости от размера файла импорт может занимать продолжительное время. Не закрывайте эту страницу, пока импорт не будет завершен. В случае обрыва соединения с сервером просто перезапустите процесс импорта снова, выбрав загруженный ранее файл.</b></div>'.self::importStat($fileSize,$ftell,$x, $newItems, $modItems, $newCats, $errors);
                ajax::javascript('ajaxGet(\'import::importStart?=0&file='.$file.'&x='.$x.'&ftell='.ftell($handle_f).'&newItems='.$newItems.'&modItems='.$modItems.'&newCats='.$newCats.'&errors='.$errors.'\')');
                $statShowed=true;
                return array(
                    'right'=>$out
                );
            }
            else {
                return array(
                    'right'=>'<p>Import complete</p>'.self::importStat($fileSize,$ftell,$x, $newItems, $modItems, $newCats, $errors)
                );
            }

        }
        else {
            // Проверим, есть ли в папке файлы, доступные для импорта
            $array = file::listFiles($_SERVER['DOCUMENT_ROOT'] . '/uploaded/import');
            $importFiles = array();
            if ($array != false) {
                foreach ($array AS $val) {
                    list($name, $ext) = explode(".", $val);
                    if ($ext == 'csv') {
                        $importFiles[] = $val;
                    }
                }
                if (!empty($importFiles)) {
                    $out .= '
                        <div class="row">
                            <h3>Импорт данных из файла CSV</h3>
                        </div>
                        <div class="row">
                            <a class="btn" href="' . $settings['protocol'] . $settings['siteUrl'] . '/admin/catalog/"><i class="ic-return"></i>Вернуться назад</a>
                        </div>
                        <div class="row">
                            <div class="info">Найдены файлы доступные для импорта. Чтобы начать процесс импорта выберите файл. Чтобы загрузить новый файл для импорта удалите неиспользуемые файлы.</div>
                        </div>
                        <div class="row"><table class="cmstable4">
                        <tr>
                            <th style="width:24px;">&nbsp;</th>
                            <th>Файл</th>
                            <th style="width:60px;">Действия</th>
                    </tr>';
                    foreach ($importFiles AS $val) {
                        $out .= '<tr>
                        <td><i class="ic-file-excel"></i></td>
                        <td><b class="hand" onClick="ajaxGet(\'import::importStart?=&file=' . $val . '&ftell=0\')">' . $val . '</b></td>
                        <td><i class="ic-delete" title="Удалить" onClick="ajaxGet(\'import::fileRemove?='.$val.'\')"></i></td>
                        </tr>';
                    }
                    $out .= '</table>
                    </div>';
                }
            }
        }


        if ($out == '') {
            return array('right' => '<div class="row">
                            <h3>Импорт данных из файла CSV</h3>
                        </div>
                        <div class="row">
                            <a class="btn" href="' . $settings['protocol'] . $settings['siteUrl'] . '/admin/catalog/"><i class="ic-return"></i>Вернуться назад</a>
                        </div>
                        <form method="POST" id="importUploadForm">
                <label>Загрузите файл CSV для импорта данных</label><br>
                <div class="btn-group">
                    <div>
                        <input type="file" name="userfile" onChange="ajaxPost(\'importUploadForm\',\'import::fileUpload\')" style="width: 500px;border: 1px solid #cccccc;background: #ffffff;height: 28px;float: left;line-height: 28px;">
                    </div>
                    <div>
                        <div class="btn" onClick="ajaxPost(\'importUploadForm\',\'import::fileUpload\')"><i class="ic-save"></i>Загрузить</div>
                    </div>
                </div>
                </form>');
        } else {
            return array('right' => $out);
        }
    }


    // Аналог функции FGETCSV
    static function fgetcsv($f_handle, $length, $delimiter=';', $enclosure='"') {
        if (!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') return fgetcsv($f_handle, $length, $delimiter, $enclosure);
        //если указатель на файл не задан, то возвращаем false
        if (!$f_handle || feof($f_handle)) return false;
        //если разделитель не задан, то возвращаем false
        if (strlen($delimiter) > 1) $delimiter = substr($delimiter, 0, 1);
        elseif (!strlen($delimiter)) return false;
        if (strlen($enclosure) > 1) $enclosure = substr($enclosure, 0, 1);
        $line = fgets($f_handle, $length);
        if (!$line) return false;
        $result = array();
        $csv_fields = explode($delimiter, trim($line));
        $csv_field_count = count($csv_fields);
        $encl_len = strlen($enclosure);
        for ($i = 0; $i < $csv_field_count; $i++) {
            if ($encl_len && $csv_fields[$i]{0} == $enclosure)
                $csv_fields[$i] = substr($csv_fields[$i], 1);
            if ($encl_len && $csv_fields[$i]{strlen($csv_fields[$i]) - 1} == $enclosure)
                $csv_fields[$i] = substr($csv_fields[$i], 0, strlen($csv_fields[$i]) - 1);
            $csv_fields[$i] = str_replace($enclosure . $enclosure, $enclosure, $csv_fields[$i]);
            $result[] = $csv_fields[$i];
        }
        return $result;
    }

    /* ADVANCED CSV STRING PARSER */
    /* на входе строка CSV файла - на выходе правильно собранный массив */
    static function csvParseString($str, $separator=';', $quote='"'){
        $varr='';
        $i=0;
        $quote_flag=false;
        $line=array();
        while($i<=strlen($str)) {
            if(isset($str[$i])){
                // Окончание значения поля
                if ($str[$i]==$separator && !$quote_flag) {
                    $varr=str_replace("\n","\r\n",$varr); $line[]=$varr; $varr='';
                }
                // Окончание строки
                elseif ($str[$i]=="\n" && !$quote_flag){
                    $varr=str_replace("\n","\r\n",$varr); $line[]=$varr; $varr=''; $parsed[]=$line; $line=Array();
                }
                // Начало строки с кавычкой
                elseif ($str[$i]==$quote && !$quote_flag) { $quote_flag=true; }
                // Кавычка в строке с кавычкой
                elseif ($str[$i]==$quote && $str[($i+1)]==$quote && $quote_flag) {
                    $varr.=$str[$i]; $i++;
                }
                // Конец строки с кавычкой
                elseif ($str[$i]==$quote && $str[($i+1)]!=$quote && $quote_flag) { $quote_flag=false; }
                else { $varr.=$str[$i]; }
            }
            $i++;
        }
        return $line;
    }

    // Отображение статистики импорта
    static function importStat($fileSize, $ftell, $x, $newItems, $modItems, $newCats, $errors){
        $perc=round($ftell/($fileSize/100),1);
        return '<div class="field">
		<div style="height:30px; width:100%; display:block; background:#242834;box-sizing:border-box; border:1px solid #080808; ">
			<div style="display:block;float:left; width:'.$perc.'%; height:24px; line-height:24px; margin:2px; background:#f57301;text-align:center; color:#ffffff;">'.$perc.'%
		</div>
	</div>
</div>
<div class="field">
<table class="cmstable4">
<tr><th>Просмотрено строк</th><th>Добавлено записей</th><th>Изменено записей</th><th>Новые бренды</th><th>Ошибки импорта</th></tr>
<tr><td><b>'.$x.'</b></td><td><b>'.$newItems.'</b></td><td><b>'.$modItems.'</b></td><td><b>'.$newCats.'</td><td style="color:#ff0000">'.$errors.'</td></tr>
</table>
</div>';
    }




}