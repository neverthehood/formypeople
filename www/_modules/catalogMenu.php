<?php
function catalogMenu(){
	global $site, $settings;
	
	$cacheFileName='catalogMenu';
	$out=cache::read($cacheFileName);
	if($out!=false){
		return $out;
	}
	
	// Получаем Брэнды и тип кофе, которые есть в наличии
	$array=mysql::getArray("SELECT DISTINCTROW t1.brand, t3.name AS brandName, t1.ctype, t4.value AS ctypeName
	FROM
		`data_coffee` AS t1
		JOIN `entity` AS t2 ON t2.id=t1.id
		JOIN `entity` AS t3 ON t3.id=t1.brand
		JOIN `options` AS t4 ON t4.id=t1.ctype
	WHERE
		t2.hidden='0'
	");
	if($array!=false){
		$brands=array();
		$ctypes=array();
		foreach($array AS $val){
			if(!isset($brands[$val['brand']])){
				$brands[$val['brand']]=$val['brandName'];
			}
			if(!isset($ctypes[$val['ctype']])){
				$ctypes[$val['ctype']]=$val['ctypeName'];
			}
		}
	}
	
	$out='<div class="cols2">
					<div class="amhead"><a href="'.$settings['protocol'].$settings['siteUrl'].'/coffee/">Кофе</a><i id="str0" class="ic-right" onClick="subMenuSwitch(0)"></i></div>
					<div class="smenu" id="smenu0">
						<div id="smenuBody0">
							<div class="smDiv">';
	
							if(!empty($brands)){
								foreach($brands AS $key=>$val){
									$out.='<a href="'.$settings['protocol'].$settings['siteUrl'].'/coffee/brand-'.$key.'/">'.$val.'</a>';
								}
							}
								
	
							$out.='</div>
							<div class="smDiv">';
								if(!empty($ctypes)){
									foreach($ctypes AS $key=>$val){
										$out.='<a href="'.$settings['protocol'].$settings['siteUrl'].'/coffee/ctype-'.$key.'/">'.$val.'</a>';
									}
								}
							$out.='</div>
						</div>
					</div>
				</div>
				<div class="cols1">
					<div class="amhead"><a href="'.$settings['protocol'].$settings['siteUrl'].'/tea/">Чай</a><i id="str1" class="ic-right" onClick="subMenuSwitch(1)"></i></div>
					<div class="smenu" id="smenu1">
						<div id="smenuBody1">
							<div class="smDiv">';
	
								$array=mysql::getArray("SELECT DISTINCTROW t1.type,t3.value AS name
								FROM
									`data_tee` AS t1
									JOIN `entity` AS t2 ON t2.id=t1.id
									JOIN `options` AS t3 ON t3.id=t1.type
								WHERE
									t2.hidden='0'
								");
								if($array!=false){
									foreach($array AS $val){
										$out.='<a href="'.$settings['protocol'].$settings['siteUrl'].'/tea/type-'.$val['type'].'/">'.$val['name'].'</a>';
									}
								}

							$out.='</div>
						</div>
					</div>
				</div>
				<div class="cols3">
					<div class="amhead"><a href="'.$settings['protocol'].$settings['siteUrl'].'/accesories/">Посуда и аксессуары</a><i id="str2" class="ic-right" onClick="subMenuSwitch(2)"></i></div>
					<div class="smenu" id="smenu2">
						<div id="smenuBody2">';
							
							$array=mysql::getArray("SELECT t1.id,t2.name,t1.alias
							FROM
								`data_category` AS t1
								JOIN `entity` AS t2 ON t2.id=t1.id
							WHERE
								t2.parent_id=8224
								AND t2.hidden='0'
							ORDER BY
								t1.order ASC");
							if(is_array($array)){
								$count=count($array);
								$inField=ceil($count/3);
								$key=0;
								foreach($array AS $val){
									if($key==0) $out.='<div class="smDiv">';
									$out.='<a href="'.$settings['protocol'].$settings['siteUrl'].'/accesories/'.$val['alias'].'/">'.$val['name'].'</a>';
									$key++;
									if($key==4){
										$out.='</div>';
										$key=0;
									}
								}
								if($key!=4){
									$out.='</div>';
								}
							}
	
	
						$out.='</div>
					</div>
				</div>
				<div class="cols2 mobileOnly">
					<div class="amhead">
						<a href="./home/">Для дома</a>
						<a href="./office/">Для офиса</a>
						<a href="./busines/">Для бизнеса</a>
						<a href="./articles/">Читать</a>
						<a href="./payment/">Оплата</a>
						<a href="./delivery/">Доставка</a>
						<a href="./return/">Возврат</a>
					</div>
				</div>';
				cache::save($cacheFileName,$out);
				
	return $out;
}