<?php
class ajax{

    // Всплывающие окна
    static function window($content=false, $modal=false, $id=false){
        static $wins=array();
        if($content!=false){
            $wins[]=array('content'=>$content, 'modal'=>$modal, 'id'=>$id);
        }
        else return $wins;
        return true;
    }

    // Установка значения SELECT
    static function selectSetValue($id=false, $value=false){
        static $selset=array();
        if($id!=false && $value!=false){
            $selset[]=array('id'=>$id, 'value'=>$value);
        }
        else return $selset;
        return true;
    }

    // Установка CSS класса для элемента DOM
    static function classAdd($id=false, $className=false){
        static $classAdd=array();
        if($id!=false && $className!=false){
            $classAdd[]=array('id'=>$id, 'className'=>$className);
        }
        else return $classAdd;
        return true;
    }

    // Удаление CSS стилей элемента DOM
    static function classRemove($id=false, $className=false){
        static $classRemove=array();
        if($id!=false && $className!=false){
            $classRemove[]=array('id'=>$id, 'className'=>$className);
        }
        else return $classRemove;
        return true;
    }

    // Вставка и вывод всплывающих сообщений в правом верхнем углу
    static function message($message=false, $background='#000000', $time=3){
        static $messages=array();
        if($message!=false){
            if($message!='') $messages[]=array('message'=>$message, 'background'=>$background, 'time'=>$time);
        }
        else return $messages;
        return true;
    }

    // Вывод сообщений в консоль браузера
    static function consoleLog($src=false){
        static $rem=array();
        if($src!=false){
            if($src!='') $rem[]=$src;
        }
        else return $rem;
        return true;
    }

    // Вывод ошибок в консоль браузера
    static function consoleError($src=false){
        static $rem=array();
        if($src!=false){
            if($src!='') $rem[]=htmlspecialchars(strip_tags($src));
        }
        else return $rem;
        return true;
    }

    // Загрузка JavaScript по требованию
    static function includeScript($src=false){
        static $scripts=array();
        if($src!=false){
            if($src!='') $scripts[]=$src;
        }
        else return $scripts;
        return true;
    }

    // Включаемые скрипты JavaScript
    static function javascript($src=false){
        static $scripts=array();
        if($src!=false){
            if($src!='') $scripts[]=$src;
        }
        else return $scripts;
        return true;
    }

    // Удаляемые элементы DOM
    static function domRemove($src=false){
        static $rem=array();
        if($src!=false){
            if($src!='') $rem[]=$src;
        }
        else return $rem;
        return true;
    }

    // Вызов окна подтверждения действия
    // В окне две кнопки: "Да" и "Отмена".
    // action = действие для кнопки "ДА"
    static function dialogConfirm($query=false, $action=false){
        static $dialog=array();
        if($query!=false && $action!=false){
            $dialog[]=array('query'=>$query,'click'=>$action);
        }
        else return $dialog;
        return true;
    }

    // Вызов окна ALERT
    static function dialogAlert($mess=false){
        static $dialog=array();
        if($mess!=false){
            $dialog[]=$mess;
        }
        else return $dialog;
        return true;
    }

    // Воспроизведение звука
    // ajax::sound('sys');     notify, ring, alert, sys
    static function sound($name){
        static $sound=array();
        if($name!=false) {
            $sound[0]=$name;
            return true;
        }
        else return $sound;
    }

    // Редирект
    static function redirect($url=false){
        static $redir='';
        if($url!=false) {
            $redir=$url;
            return true;
        }
        else return $redir;
    }

    // Установка CSS стилей для  элемента
    static function styleSet($id=false, $cssText=false){
        static $style=array();
        if($id!=false && $cssText!=false){
            $style[]=array('id'=>$id, 'cssText'=>$cssText);
        }
        else return $style;
        return true;
    }

}
