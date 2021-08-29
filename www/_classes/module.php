<?php
////////////////////////////////////////////////////////////////////
// Класс для работы с модулями

class module {


// Возвращает массив подключенных к странице модулей
static function getActive($pageId){
    static $md=array();
    if(!isset($md[$pageId])){
	    $md[$pageId]=mysql::getArray("SELECT t1.*, t2.btname AS pagename, t2.template
		FROM
			`pages_modules` AS t1
			JOIN `pages` AS t2 ON t2.id=t1.page_id
		WHERE
			t1.page_id='".escape($pageId)."'
		ORDER BY
			t1.`order` ASC");
    }
	return $md[$pageId];
}


	// возвращает список доступных модулей
	static function getList(){
		$modules=false;
		$array=file::listFiles($_SERVER['DOCUMENT_ROOT'].'/_modules/',true);
		if($array!=false){
			foreach($array as $val){
				$modules[]=$val;
			}
		}
		return $modules;
	}

	// Добавление модуля к странице
	static function moduleAdd($pageId,$array){
	    if(is_array($array)){
	        $order=onlyDigit(mysql::getValue("SELECT MAX(`order`) FROM `pages_modules` WHERE page_id='".escape($pageId)."'"));
	        $order++;
	        mysql::query("INSERT INTO `pages_modules` SET page_id='".escape($pageId)."', module='".escape($array['module'])."', tmplvar='".escape($array['tmplvar'])."', pause='".escape($array['pause'])."', `order`='".escape($order)."', `comment`='".escape($array['comment'])."', `settings`='".escape(serialize($array['settings']))."'");
	        echo mysql::error();
        }
	}
		
	

}
?>
