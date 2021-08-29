<?php
function showCatalog(){
	global $site, $settings;
	$out='';
	$folder=$site->page['folderalias'];
	if($folder!='coffee' && $folder!='tea'){
		return '';
	}
	$p=0;
	$perPage=50;
	$brand=$site->getUrlVars('brand');
	$ctype=$site->getUrlVars('ctype');
	$type=$site->getUrlVars('type');
	
	// Кофе
	if($folder=='coffee'){
		$where=array();
		if($brand!=false){
			$where[]="t1.brand=".$brand;
			$site->setName(mysql::getValue("SELECT name FROM `entity` WHERE id=".escape($brand)." LIMIT 1"));
		}
		if($ctype!=false){
			$where[]="t1.ctype=".$ctype;
			$site->setName(mysql::getValue("SELECT value FROM `options` WHERE id=".escape($ctype)." LIMIT 1"));
		}
		
		$wh=false;
		if(!empty($where)){
			$wh=implode(" AND ",$where);
		}
		
		$array=coffee::getCoffeeArray($wh,false,false);
		if($array!=false){
			$items=coffee::coffeeToCards($array);
		}
		
		$out.='
		<div class="container">
		    <div class="tovarCards" id="tovarCards">
		        '.$items.'
		    </div>
		</div>';
	}
	elseif($folder=='tea'){
		
		if($type!=false){
			$where[]="t1.type=".$type;
			$site->setName("Чай: ".mysql::getValue("SELECT value FROM `options` WHERE id=".escape($type)." LIMIT 1"));
		}
		
		
		$wh=false;
		if(!empty($where)){
			$wh=implode(" AND ",$where);
		}
		
		$array=coffee::getTeaArray($wh);
		if($array!=false){
			$items=coffee::teeToCards($array);
		}
		$out.='
		<div class="container">
		    <div class="tovarCards" id="tovarCards">
		        '.$items.'
		    </div>
		</div>';
	}
	
	return $out;
}