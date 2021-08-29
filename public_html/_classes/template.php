<?php
class template {
	public $id=0;				//
	public $name='';			//
	public $content='';			// 
	public $blocks=array(); 	// Переменные шаблона {{blockName}}
	public $chunks=array(); 	// Чанки
	public $error;				// Ошибки
	public $context='site';		// 
	public $author='';
	
	function __construct($options=false){
		if($options!=false){
			if(is_array($options)){
				foreach($options AS $key=>$val){
					$this->$key=$val;
					}
				}
			}
		else {
			$this->context='site';
			$this->author=0;
			if(isset($_SESSION['userId'])) $author=$_SESSION['userId'];
			$this->content='<html>
 <head>
  <title>{{pageTitle}}</title>
 </head>
 <body>
  {{pageText}}<!-- Главный текстовый блок -->
 </body>
</html>';
			}
		}
		
	public function error($message){
		$this -> error .= $message .'<br />';
		}
		
	public function parse($content){
		
		}
	
	// Возвращает контент шаблона по имени страницы
	static function getContent($name){
		return mysql::getValue("SELECT content FROM `templates` WHERE id='".mysql::escape($name)."' AND context='site' LIMIT 1");
		}
	
	// Возвращает контент шаблона по ID страницы
	static function getContentByPage($pageId){
		return mysql::getValue("SELECT t1.content
		FROM
			`templates` AS t1
			JOIN `pages` AS t2 ON t2.template=t1.id
		WHERE
			pages.id='".mysql::escape($pageId)."' LIMIT 1");
		}
	
	// Возвращает список переменных шаблона
	static function getVars($content){
		$l=false;
		if (preg_match_all("!{{(.*?)}}!",$content,$l)){ if($l[1]!=false) $finded=$l[1]; }
		// запрет обработки некоторых переменных шаблона
		$closedBlocks=explode(',','pageTitle,pageKeywords,pageDescription');
		if(isset($finded)){
			foreach ($finded as $val){
				$m=0;
				foreach($closedBlocks as $closed){ if($val==$closed) { $m=1; break; } }
				if($m==0) @$allBlocks[]=$val;
				}
			}
		if(!isset($allBlocks)) $allBlocks=false;
		return $allBlocks;
		}
}
?>