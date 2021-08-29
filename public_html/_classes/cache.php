<?php
// Класс для работы с файловым кешем
class cache{

    // Получение данных из файлового кеша
    static function read($filename){
        if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$filename.'.acache')) return false;
        $a=file($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$filename.'.acache');
        if($a===false) return false;
        return unserialize(implode('',$a));
    }

    // Сохранение кеша в файл
    static function save($filename, $content=''){
        file::save($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$filename.'.acache',serialize($content));
    }

}