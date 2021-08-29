<?php
class db{
    // Добавление базовых полей для сущностей
    static function createBaseSettings(){
	    global $item;
	    $tblAlias=mysql::getValue("SELECT `entity_type_alias` FROM `entity_type` WHERE id=".escape($item)." LIMIT 1");
	
	    $images='a:2:{s:7:"fileext";s:12:"jpg,jpeg,png";s:8:"imgsizes";s:20:":1000*1000,s:400*400";}';
	    $files='a:2:{s:7:"fileext";s:37:"zip,rar,7z,doc,pdf,djvu,docx,xls,xlsx";s:8:"imgsizes";s:19:":1200*800,s:400*300";}';
	
	
	    $sql="INSERT INTO `attributes` (`attr_group`, `default`, `alias`, `element`, `type`, `attr_source`, `view`, `edit`, `maxlength`, `cssclass`, `style`, `name`, `multiple`, `entity_type_id`, `frontend`, `backend`, `required`, `sort`, `frontend_list`, `backend_list`, `crop_in_list`, `frontend_order`, `backend_order`, `unit`, `icon`, `source`, `editSource`, `events`, `dbsize`, `dop`, `hidden`, `folderhide`, `childhide`, `filter`, `filterOrder`, `filterType`, `javascript`, `optorder`)
        VALUES
('default', '', 'art', 'varchar', 'varchar', 0, '', '0', 24, 'size-m', '', 'Артикул / Код', '0', ".$item.", '1', '0', '0', '0', '1', '0', 0, 10, 9, '', 'ic-barcode', NULL, '', '', '(24)', NULL, '0', '0', '0', '0', 0, '0', '', '0'),
('default', '27', 'exist', 'select', 'int', 0, '', '0', 0, '', '', 'Наличие', '0', ".$item.", '1', '1', '0', '0', '1', '1', 0, 11, 10, '', '', 'options.id.value.attr_id=110', '', '', '', NULL, '0', '0', '0', '0', 0, '3', '', '0'),
('default', '0', 'price', 'decimal', 'decimal', 0, '', '0', 9, 'size-s', '', 'Цена', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 12, 11, 'Руб. коп.', 'ic-money', NULL, '', '', '(7,2)', NULL, '0', '0', '0', '1', 0, '1', '', '0'),
('default', '0', 'discount', 'decimal', 'decimal', 0, '', '0', 9, 'size-s', '', 'Цена со скидкой', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 12, 12, 'Руб. коп.', 'ic-pig', NULL, '', '', '(7,2)', NULL, '0', '0', '0', '0', 0, '0', '', '0'),
('default', '0', 'brand', 'select', 'int', 0, '', '0', 0, '', '', 'Бренд', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 14, 14, '', 'ic-office', 'entity.id.name.entity_type IN(0,45)', '', '', '', NULL, '0', '0', '0', '1', 0, '6', '', '0'),
('default', '', 'description', 'textarea', 'textarea', 0, '', '0', 10000, 'basic', 'width:100%; height:200px;', 'Описание', '0', ".$item.", '0', '0', '0', '0', '0', '0', 0, 15, 15, '', '', NULL, '', '', '', NULL, '0', '0', '0', '0', 0, '0', '', '0'),
('default', '', 'photo', 'file', 'file', 0, 'proportial', '0', 32, '', '', 'Изображения', '1', ".$item.", '0', '1', '0', '1', '0', '1', 0, 16, 16, '', 'ic-image', NULL, '', '', '', '".escape($images)."', '0', '0', '0', '0', 0, '0', '', '0'),
('default', '', 'files', 'file', 'file', 0, '', '0', 32, '', '', 'Файлы для скачивания', '1', ".$item.", '0', '0', '0', '0', '0', '0', 0, 17, 17, '', '', NULL, '', '', '', '".escape($files)."', '0', '0', '0', '0', 0, '0', '', '0'),
('default', '', 'youtube', 'varchar', 'varchar', 0, 'youtube', '0', 255, '', '', 'Видео Youtube', '1', ".$item.", '0', '0', '0', '0', '0', '0', 0, 18, 18, '', 'ic-play', NULL, '', '', '(255)', NULL, '0', '0', '0', '0', 0, '0', '', '0'),
('default', '0', 'action', 'checkbox', 'enum', 0, '', '0', 0, '', '', 'Товар на акции', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 20, 19, '', 'ic-star', NULL, '', '', '', NULL, '0', '0', '0', '1', 0, '7', '', '0'),
('default', '0', 'hot', 'checkbox', 'enum', 0, '', '0', 0, '', '', 'Горящий', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 21, 21, '', 'ic-bomb', NULL, '', '', '', NULL, '0', '0', '0', '1', 0, '7', '', '0'),
('default', '0', 'rek', 'checkbox', 'enum', 0, '', '0', 0, '', '', 'Рекомендуемый', '0', ".$item.", '0', '1', '0', '1', '0', '1', 0, 22, 22, '', 'ic-tup', NULL, '', '', '', NULL, '0', '0', '0', '1', 0, '7', '', '0')";
	
	    mysql::query($sql);
	
	
	    // Добавляем параметры в основную таблицу данных
	    $sql=array();
	
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `art` varchar(24) DEFAULT '' COMMENT 'Артикул / Код'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `exist` bigint(20) UNSIGNED DEFAULT '27' COMMENT 'Наличие'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `price` decimal(7,2) DEFAULT '0.00' COMMENT 'Цена'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `discount` decimal(7,2) DEFAULT '0.00' COMMENT 'Цена со скидкой'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `action` ENUM('0','1') DEFAULT '0' COMMENT 'Акция'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `brand` bigint(20) UNSIGNED DEFAULT '0' COMMENT 'Бренд'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `photo` varchar(32) DEFAULT '' COMMENT 'Изображения'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `files` varchar(32) DEFAULT '' COMMENT 'Файлы для скачивания'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `youtube` varchar(255) DEFAULT '' COMMENT 'Видео Youtube'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `hot` ENUM('0','1') DEFAULT '0' COMMENT 'Горящий'";
	    $sql[]="ALTER TABLE `data_".$tblAlias."` ADD COLUMN `rek` ENUM('0','1') DEFAULT '0' COMMENT 'Рекомендуемый'";
	
	
	    // Ключи
	    $sql[]="ALTER TABLE `data_".$tblAlias."`
  ADD KEY `exist` (`exist`),
  ADD KEY `brand` (`brand`)";
	
	    //$sql[]="ALTER TABLE `data_".$tblAlias."`
//  ADD CONSTRAINT `data_".$tblAlias."_ibfk_1` FOREIGN KEY (`id`) REFERENCES `entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION";
	
	    $sql[]="ALTER TABLE `text_".$tblAlias."` ADD COLUMN `description` text COMMENT 'Описание'";
	
	    foreach($sql AS $val){
		    mysql::query($val);
	    }
	
	    // Отмечаем, что базовые поля скопированы
	    $upd='if($array[\'name\']==\'\' || $array[\'name\']==\'-БЕЗ НАЗВАНИЯ-\') $error.=\'Не задано наименование<br>\';
if($array[\'price\']<=0) $error.=\'Недопустимая цена<br>\';
if($array[\'brand\']==0) $error.=\'Не задан бренд<br>\';
if($array[\'discount\']==0 || $array[\'discount\']>$array[\'price\']){
    $array[\'discount\']=$array[\'price\'];
}
';
	    mysql::query("UPDATE `entity_type` SET base='1', before_save='".escape($upd)."' WHERE id=".escape($item));
	
	    ajax::message("Изменения внесены. Не забудьте принудительно обновить параметры сущности");
	
	    $item=0;
	    echo mysql::error();
	    return data::settings();
    }
}