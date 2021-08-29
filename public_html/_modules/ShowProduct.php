<?php
function ShowProduct(){
	global $site, $settings, $breadCrumbs;
	$out='';
	$item=$site->getUrlVars(0);
	if($item!=false){
		$item=explode('_',$item);
		$item=(int)$item[(count($item)-1)];
		if(!is_numeric($item)) $site->error404();
		
		// Узнаем тип товара
		$ttype=mysql::getValue("SELECT t2.entity_type_alias FROM `entity` AS t1 JOIN entity_type AS t2 ON t2.id=t1.entity_type AND t1.id=".escape($item)." LIMIT 1");
		
		// Кофе
		if($ttype=='coffee'){
			$product=mysql::getArray("SELECT t1.id, t1.price, t1.image, t1.pomol, t1.color, t1.anounce, t1.brand, t7.name AS brandName, t2.name, t2.hidden, t1.obrabotka, t1.acidity, t3.*, t4.value AS typeName,t5.value AS acidityName, t6.value AS ctypeName, t1.country, t8.value AS countryName
			FROM `data_coffee` AS t1
				JOIN `entity` AS t2 ON t2.id=t1.id
				JOIN `text_coffee` AS t3 ON t3.id=t1.id
				JOIN `options` AS t4 ON t4.id=t1.type
				JOIN `options` AS t5 ON t5.id=t1.acidity
				JOIN `options` AS t6 ON t6.id=t1.ctype
				JOIN `entity` AS t7 ON t7.id=t1.brand
				JOIN `options` AS t8 ON t8.id=t1.country
			WHERE
				t1.id=".escape($item)."
			LIMIT 1",true);
			
			if($product===false){
				$site->error404();
			}
			
			// Получим множественные данные
			$product['fasovka']=mysql::getArray("SELECT t1.id, t1.name, t2.price
				FROM `entity` AS t1
					JOIN `data_variant` AS t2 ON t2.id=t1.id
				WHERE
					t1.parent_id=".escape($product['id'])."
				ORDER BY
					t2.price ASC");
			
			$site->setName($product['name']);
			$site->setTitle($product['name'].' '.$product['brandName'].' купить в Минске');
			$site->setDescription($product['name'].' '.$product['brandName'].'. '.$product['typeName'].', '.$product['acidityName'].' кислотность. Страна произрастания: '.$product['countryName']);
			$breadCrumbs.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/coffee/"><span>Кофе</span></a></li>';
			$breadCrumbs.='<li><span>'.$product['name'].'</span></li>';
			
			$out.='
			<div class="row" style="background:#f5f5f5;padding-bottom:40px;">
		        <div class="container">
		            <div class="six columns productShapka">
		                <div class="row">
		                    <h1>'.$product['name'].'</h1>
		                    <p class="productAnounce">'.$product['anounce'].'</p>
		                </div>
		                <div class="row">
		                    <div class="tcRow" style="width:fit-content;">';
						$price=$product['price'];
						$fasovkaList=array();
						if(!empty($product['fasovka'])){
							$price=$product['fasovka'][0]['price'];
							$out.='
		                        <select id="var'.$product['id'].'" name="tovar[variant]" onchange="correctPrice(this.id)" style="margin:6px 20px 0 0;">';
							foreach($product['fasovka'] AS $v){
								$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
								$fasovkaList[]=$v['name'];
							}
							$out.='
								</select>';
						}
						// Если есть выбор вариантов помола
						if($product['pomol']==1){
							$out.='<select id="pomol'.$product['id'].'" name="tovar[pomol]" style="margin:6px 0 0 0;">
										<option value="Не молоть">Не молоть</option>
										<option value="Крупный">Крупный</option>
										<option value="Мелкий">Мелкий</option>
									</select>';
						}
						
		                $out.='
		                    </div>
		                </div>
		                <div class="tcRow" style="width:260px; margin-top:20px;">
		                    <div class="tcCount">
								<i class="ic-minus" onclick="itemCount('.$product['id'].',0)"></i>
								<input id="cnt'.$product['id'].'" value="1">
								<i class="ic-plus" onclick="itemCount('.$product['id'].',1)"></i>
							</div>
							<div class="tcPrice"><b id="price'.$product['id'].'" style="display:block; font-size:30px;margin-top:6px;">'.$price.'</b>
						</div>';
						
		                $out.='
		                </div>
		                <div class="row">
		                    <span class="button button-primary" style="width:240px;height:50px;line-height:46px;" onclick="addToCart(\''.$product['id'].'\')">В корзину</span>
		                </div>
		            </div>
		            <div class="six columns">';
		                if($product['image']!=''){
		                	$out.='<img class="productImage" src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($product['id']/100).'/'.$product['image'].'" alt="'.htmlspecialchars($product['name']).'">';
		                }
		            $out.='
		            </div>
		        </div>
		    </div>
		    
		    <div class="row" style="background:#ffffff; padding:40px 0 60px 0;">
		        <div class="container">
		            <div class="six columns">
		                <h2>Описание</h2>
		                '.$product['description'].'
		            </div>
		            <div class="six columns">
		                <h2>Характеристики</h2>
		                <table class="paramTable">
		                    <tr><td>Обжарщик</td><td>'.$product['brandName'].'</td></tr>
		                    <tr><td>Произрастание</td><td>'.$product['countryName'].'</td></tr>
		                    <tr><td>Обработка</td><td>'.$product['obrabotka'].'</td></tr>
		                    <tr><td>Кислотность</td><td>'.$product['acidityName'].'</td></tr>
		                    <tr><td>Тип</td><td>'.$product['typeName'].'</td></tr>
		                    <tr><td>Предназначение</td><td>'.$product['ctypeName'].'</td></tr>';
		                if(!empty($fasovkaList)){
		                	$out.='<tr><td>Фасовка</td><td>'.implode(', ',$fasovkaList).'</td></tr>';
		                }
		                $out.='</table>
		            </div>
		        </div>
		    </div>
		    
		    ';
		}
		
		// Кофе
		if($ttype=='tee'){
			$product=mysql::getArray("SELECT t1.id, t1.anounce, t1.image, t1.price, t1.fasovka, t1.rek, t2.name, t2.hidden, t2.parent_id, t3.*, t4.value AS typeName
		FROM `data_tee` AS t1
			JOIN `entity` AS t2 ON t2.id=t1.id
			JOIN `text_tee` AS t3 ON t3.id=t1.id
			JOIN `options` AS t4 ON t4.id=t1.type
			WHERE
				t1.id=".escape($item)."
			LIMIT 1",true);
			
//			if($product===false){
//				$site->error404();
//			}
			
			// Получим множественные данные
			$product['fasovka']=mysql::getArray("SELECT t1.id, t1.name, t2.price
				FROM `entity` AS t1
					JOIN `data_variant` AS t2 ON t2.id=t1.id
				WHERE
					t1.parent_id=".escape($product['id'])."
				ORDER BY
					t2.price ASC");
			
			$site->setName($product['name']);
			$site->setTitle($product['typeName'].' '.$product['name'].' купить в Минске');
			$site->setDescription($product['typeName'].' '.$product['name']);
			$breadCrumbs.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/tea/"><span>Чай</span></a></li>';
			$breadCrumbs.='<li><span>'.$product['name'].'</span></li>';
			
			$out.='
			<div class="row" style="background:#f5f5f5;padding-bottom:40px;">
		        <div class="container">
		            <div class="six columns productShapka">
		                <div class="row">
		                    <h1>'.$product['name'].'</h1>
		                    <p class="productAnounce">'.$product['anounce'].'</p>
		                </div>
		                <div class="row">
		                    <div class="tcRow" style="width:fit-content;">';
			$price=$product['price'];
			$fasovkaList=array();
			if(!empty($product['fasovka'])){
				$price=$product['fasovka'][0]['price'];
				$out.='
		                        <select id="var'.$product['id'].'" name="tovar[variant]" onchange="correctPrice(this.id)" style="margin:6px 20px 0 0;">';
				foreach($product['fasovka'] AS $v){
					$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
					$fasovkaList[]=$v['name'];
				}
				$out.='
								</select>';
			}
			
			$out.='
		                    </div>
		                </div>
		                <div class="tcRow" style="width:260px; margin-top:20px;">
		                    <div class="tcCount">
								<i class="ic-minus" onclick="itemCount('.$product['id'].',0)"></i>
								<input id="cnt'.$product['id'].'" value="1">
								<i class="ic-plus" onclick="itemCount('.$product['id'].',1)"></i>
							</div>
							<div class="tcPrice"><b id="price'.$product['id'].'" style="display:block; font-size:30px;margin-top:6px;">'.$price.'</b>
						</div>';
			
			$out.='
		                </div>
		                <div class="row">
		                    <span class="button button-primary" style="width:240px;height:50px;line-height:46px;" onclick="addToCart(\''.$product['id'].'\')">В корзину</span>
		                </div>
		            </div>
		            <div class="six columns">';
			if($product['image']!=''){
				$out.='<img class="productImage" src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($product['id']/100).'/'.$product['image'].'" alt="'.htmlspecialchars($product['name']).'">';
			}
			$out.='
		            </div>
		        </div>
		    </div>
		    
		    <div class="row" style="background:#ffffff; padding:40px 0 60px 0;">
		        <div class="container">
		            <div class="six columns">
		                <h2>Описание</h2>
		                '.$product['description'].'
		            </div>
		            <div class="six columns">
		                <h2>Характеристики</h2>
		                <table class="paramTable">
		                    <tr><td>Тип</td><td>'.$product['typeName'].'</td></tr>';
			if(!empty($fasovkaList)){
				$out.='<tr><td>Фасовка</td><td>'.implode(', ',$fasovkaList).'</td></tr>';
			}
			$out.='</table>
		            </div>
		        </div>
		    </div>
		    
		    ';
			
		}
		
	}
	
//	echo '<pre>';
//	print_r($product);
//	echo '</pre>';
	
	return '<div class="row" style="background:#f5f5f5;">
        <div class="container">
            <div class="row">
                [[Breadcrumbs.php]]
            </div>
    	</div>
    </div>'.$out;
}