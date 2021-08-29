<?php
class coffee
{
	
	static function teeToCards($array){
		global $settings;
		$out='';
		
		if($array!=false){
			foreach($array AS $val){
				$out.='
				<div id="tc'.$val['id'].'" class="tCard splashCard" style="background:#f1f1f1">
					<div class="dtCardBody" style="background:#f1f1f1">
						<div class="tcHead">
							<a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">'.$val['name'].'</a>
							<span>'.$val['anounce'].'</span>
						</div>
						<div class="tcForm'.$val['id'].'">
							<div class="tcRow">';
				if(isset($val['variant'])){
					$out.='<select id="var'.$val['id'].'" name="tovar[variant]" onChange="correctPrice(this.id)"  style="display:block; margin:0 auto; float:none;">';
					foreach($val['variant'] AS $v){
						$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
					}
					$out.='</select>';
				}
				
				$out.='</div>
							<div class="tcRow">
								<div class="tcCount">
									<i class="ic-minus" onclick="itemCount('.$val['id'].',0)"></i>
									<input id="cnt'.$val['id'].'" value="1">
									<i class="ic-plus" onclick="itemCount('.$val['id'].',1)"></i>
								</div>
								<div class="tcPrice">';
				if(isset($_SESSION['client'])){
					$out.='<div>0<hr/></div>';
				}
				$out.='
									<b id="price'.$val['id'].'">'.$val['price'].'</b>
								</div>
							</div>
							<div class="tcRow tcButtonRow">
								<span class="button button-primary" onClick="addToCart(\''.$val['id'].'\')">В корзину</span>
								<a class="button" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">Подробнее</a>
							</div>
						</div>
					</div>
	
					<div class="tcBody">
						<div class="tcImage">';
				
				//<img src="./p/tovarPhoto.png" alt="Фото товара">
				if($val['image']!=''){
					$out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.str_replace('.png','s.png',$val['image']).'" alt="Фото '.htmlspecialchars($val['name']).'">';
				}
				else $out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/p/tovarPhoto.png" alt="Фото '.htmlspecialchars($val['name']).'">';
				$out.='</div>
						<div class="tcHead">
							<a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">'.$val['name'].'</a>
						</div>
						<form id="tccForm'.$val['id'].'">
							<div class="tcRow">';
				if(isset($val['variant'])){
					$out.='<select id="vard'.$val['id'].'" name="tovar[variant]" onChange="correctPrice(this.id)" style="display:block; margin:0 auto; float:none;">';
					foreach($val['variant'] AS $v){
						$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
					}
					$out.='</select>';
				}
				
				$out.='
							</div>
							<div class="tcRow">
								<div class="tcCount">
									<i class="ic-minus" onclick="itemCount(\'d'.$val['id'].'\',0)"></i>
									<input id="cntd'.$val['id'].'" value="1">
									<i class="ic-plus" onclick="itemCount(\'d'.$val['id'].'\',1)"></i>
								</div>
								<div class="tcPrice">';
				if(isset($_SESSION['client'])){
					$out.='<div>0<hr/></div>';
				}
				$out.='
									<b id="priced'.$val['id'].'">'.$val['price'].'</b>
								</div>
							</div>
							<div class="tcRow tcBtnRow">
								<span class="button button-primary" onClick="addToCart(\'d'.$val['id'].'\')">В корзину</span>
								<a class="button" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">Подробнее</a>
							</div>
						</form>
					</div>
	
	
				</div>';
			}
			$out.='<div id="tc'.$val['id'].'" class="tCard splashCard" style="margin:0; padding:0; opacity:1;height:0;overflow:hidden;"></div>';
		}
		else{
			$out.='<p style="display:block; width:100%; background:#f1f1f1; padding:20px; font-size:18px;">По вашему запросу ничего не найдено! Измените критерии поиска или очистите фильтр.</p>';
		}
		return $out;
	}
	
	// Получаем чай
	static function getTeaArray($where=false,$order=false,$limit=false){
		if($where==false) $where="t2.hidden='0'";
		else $where.=" AND t2.hidden='0' ";
		if($order==false) $order="new";
		if($limit==false) $limit="500";
		
		if($order===false){
			$order='new';
		}
		if(isset($_SESSION['orderStatus'])){
			$order=$_SESSION['orderStatus'];
		}
		
		$orders=array(
			'new'=>'t1.id DESC',
			'hiprice'=>'t1.price DESC',
			'loprice'=>'t1.price ASC'
		);
		
		
		$array=mysql::getArray("SELECT t1.id, t1.anounce, t1.image, t1.price, t1.fasovka, t1.rek, t2.name, t2.hidden, t2.parent_id, t3.*, t4.value AS typeName
		FROM `data_tee` AS t1
			JOIN `entity` AS t2 ON t2.id=t1.id
			JOIN `text_tee` AS t3 ON t3.id=t1.id
			JOIN `options` AS t4 ON t4.id=t1.type
		WHERE
			".$where."
			ORDER BY ".$orders[$order]." LIMIT ".$limit);
		
		if($array!=false){
			$ids=array();
			foreach($array AS $val){
				$ids[]=$val['id'];
			}
			if(!empty($ids)){
				$var=mysql::getArray("SELECT t1.id, t1.price, t2.parent_id, t2.name
				FROM
					`data_variant` AS t1
					JOIN `entity` AS t2 ON t2.id=t1.id
				WHERE
					t2.parent_id IN(".implode(",",$ids).")
					AND t2.hidden='0'
				ORDER BY t1.price ASC");
				
				if(is_array($var)){
					foreach($var AS $k=>$v){
						foreach($array AS $key=>$val){
							if($val['id']==$v['parent_id']){
								if(!isset($array[$key]['variant'])){
									$array[$key]['variant']=array();
									$array[$key]['price']=10000000;
								}
								$array[$key]['variant'][]=$v;
								if($array[$key]['price']>$v['price']){
									$array[$key]['price']=$v['price'];
								}
							}
						}
					}
				}
			}
		}
		return $array;
	}
	
	static function smsPodpiska(){
		global $item;
		return array(
			'podpiskaBlock'=>'Подписка успешно оформлена!'
		);
	}
	
	static function cartSent(){
		global $item, $settings;
		$udata=array(
			'name'=>'',
			'tel'=>'',
			'city'=>'',
			'addr'=>'',
			'comment'=>'',
			'dostavka'=>'Доставка курьером',
			'oplata'=>'Наличными курьеру'
		);
		if(isset($_SESSION['client']['id'])){
			$udata['name']=$_SESSION['client']['name'];
			$udata['tel']=$_SESSION['client']['tel'];
			$udata['city']=$_SESSION['client']['city'];
			$udata['addr']=$_SESSION['client']['addr'];
		}
		
		$do=explode(',','Наличными курьеру,Банковской картой через Интернет,С расчетного счета юр. лица или предпринимателя');
		$dm=explode(',','Доставка курьером по Минску: 9 руб (бесплатно от 50 руб),Доставка курьером по РБ: от 15 руб,Самовывоз г. Минск ул. Кальварийская 42');
		if(isset($_SESSION['udata'])) $udata=$_SESSION['udata'];
		if(isset($_POST['udata'])) $udata=$_POST['udata'];
		if($item==1){
			$opts='';
			$rn=1;
			foreach($dm AS $val){
				$rn++;
				$selected='';
				if($val==$cart['dostavka']) $selected=' checked="checked"';
				$opts.='<input id="rad'.$rn.'" '.$checked.' type="radio" name="filter[dostavka]" value="'.htmlspecialchars($val).'"><label for="rad'.$rn.'">'.htmlspecialchars($val).'</label>';
			}
			
			$opts2='';
			$rn=1;
			foreach($do AS $val){
				$rn++;
				$selected='';
				if($val==$cart['oplata']) $selected=' checked="checked"';
				$opts2.='<input id="radd'.$rn.'" '.$checked.' type="radio" name="filter[oplata]" onChange="setOlplataType(this.value)" value="'.htmlspecialchars($val).'"><label for="radd'.$rn.'">'.htmlspecialchars($val).'</label>';
			}
			
			// Шаг 1: Отображаем форму для вставки личных данных
			$out.='
            <div class="row">
	            <div class="six columns">
	                <input type="text" name="udata[name]" value="'.$udata['name'].'" placeholder="* Ф.И.О.">
	            </div>
	            <div class="six columns">
	                <input type="text" name="udata[tel]" value="'.$udata['tel'].'" placeholder="* Телефон" onkeyup="telFormat(this)" oninput="telFormat(this)">
	            </div>
            </div>
            
            <div class="row">
                <div class="six columns">
	                <input type="text" name="udata[city]" value="'.$udata['city'].'" placeholder="* Город / Нас. пункт" autocomplete="off">
	            </div>
	            <div class="six columns">
                    <input type="text" name="udata[addr]" value="'.$udata['addr'].'" placeholder="* Адрес" autocomplete="off">
	            </div>
            </div>
            <div class="row">
                <h2>Способы доставки</h2>
                '.$opts.'
            </div>
            <div class="row">
                <h2>Способы оплаты</h2>
                '.$opts2.'
            </div>
	        
            
            <div class="row" id="yurLicoDiv">
            </div>
            <div class="row">
                <div class="button" onClick="cartSent(2)">Подтвердить заказ</div>
            </div>
        ';
			return array(
				'zkDiv'=>'<form id="cartForm" method="POST" style="background:#h6h6h6" autocomplete="off"><h2>Контактные данные</h2><div id="crtError"></div>'.$out.'</form><div class="row" id="testBlock"></div>'
			);
		}
		// Подтверждение заказа
		elseif($item==2){
			$err='';
			$udata=$_POST['udata'];
			if($udata['name']==''){
				$err.='Не заполнено Ф.И.О.!<br>';
			}
			if($udata['tel']==''){
				$err.='Не заполнен номер телефона!<br>';
			}
			else {
				$telnum=onlydigit($udata['tel']);
				if(strlen($telnum)<=10) $error='Некорректный номер телефона. Введите номер телефона в международном формате!<br>';
			}
			if(strlen($udata['city'])<3){
				$err.='Недопустимое название города!<br>';
			}
			if(strlen($udata['addr'])<6){
				$err.='Слишком короткий адрес!<br>';
			}
			
			if($err==''){
//                echo '<pre>';
//                print_r($_SESSION);
//                echo '</pre>';
//                echo '<pre>';
//                print_r($_POST);
//                echo '</pre>';
				$zakaz=$_POST['udata'];
				
				$podpiska='Подписка на рассылку: <b>Нет</b><br>';
				if(isset($zakaz['subscribe'])) $podpiska='Подписка на рассылку: <b>Да</b><br>';
				$podpiska='';
				
				$zakTable='<p><table style="width:100%; max-width:800px; border:1px solid #999999; border-collapse:collapse;">
        <tr><th>Фото</th><th>Наименование</th><th>Цена</th><th>К-во</th><th>Стоимость</th></tr>';
				$fullCost=0;
				$bg='fcfcfc';
				
				
				$webPayTovarFields='';
				$webPayTovarCount=0;
				$webPayTotalPrice=0;
				$wpc=0;
				foreach($_SESSION['cart']['items'] AS $key=>$val){
					if($bg=='fcfcfc') $bg='ffffff';
					else $bg='fcfcfc';
					$zakTable.='<tr><td style="background:#'.$bg.'"><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($key/100).'/'.$val['photo'].'" alt="" style="width:50px;"></td><td style="background:#'.$bg.'"><a href="'.$settings['protocol'].$settings['siteUrl'].'/catalog/'.$key.'/">'.$val['name'].'</a></td><td style="background:#'.$bg.'">'.triada($val['price'],2).'</td><td style="background:#'.$bg.'">'.$val['count'].'</td><td style="background:#'.$bg.'">'.triada(($val['price']*$val['count']),2).'</td><tr>';
					$webPayTovarCount=$webPayTovarCount+$val['count'];
					$webPayTotalPrice=$webPayTotalPrice+($val['price']*$val['count']);
					$webPayTovarFields.='<input type="hidden" name="wsb_invoice_item_name['.$wpc.']" value="'.htmlspecialchars($val['name']).'">
                    <input type="hidden" name="wsb_invoice_item_quantity['.$wpc.']" value="'.$val['count'].'">
                    <input type="hidden" name="wsb_invoice_item_price[0]" value="'.number_format($val['price'], 2, '.', '').'">
                    ';
					$wpc++;
				}
				$zakTable.='<tr><td colspan="4"></td><td><b>'.triada($_SESSION['cart']['price'],2).'</b> руб.</td></tr>
        </table></p>';
				$bankData='';
				if($zakaz['orgname']!='' || $zakaz['unp']!=''){
					$bankData.='<hr><b>Данные юридического лица</b><br>
Организация: <b>'.$zakaz['orgname'].'</b><br>
УНП: <b>'.$zakaz['unp'].'</b><br>
Реквизиты: <b>'.$zakaz['rek'].'</b><br>';
				}
				
				// Имя платежной системы
				$psName='none';
				$snPrefix='FS';
				if($zakaz['oplata']=='Банковской картой через Интернет' || $zakaz['oplata']=='Оплата через ЕРИП'){
					$psName='webpay';
					$snPrefix='WP';
				}
				mysql::query("INSERT INTO `pay_count` SET `sys`='".escape($psName)."'");
				$sNumber=$snPrefix.mysql::insertId();
				
				$com='';
				if(mb_strlen($zakaz['comment'],'utf-8')>=4){
					$com='Комментарий: <b>'.$zakaz['comment'].'</b><br>';
				}
				
				$body=date("d.m.Y", time()).' в '.date("H:i").' получен заказ от пользователя <b>'.$zakaz['name'].'</b>.<br><br>
Имя: <b>'.$zakaz['name'].'</b><br>
Телефон: <b>'.$zakaz['tel'].'</b><br>
Номер счета: <b>'.$sNumber.'</b>
'.$podpiska.'
Способ оплаты: <b>'.$zakaz['oplata'].'</b><br>
Способ доставки: <b>'.$zakaz['dostavka'].'</b><br>
<hr>
<b>Доставка по адресу: </b><br>
Город: <b>'.$zakaz['city'].'</b>, <br>
Адрес: <b>'.$zakaz['addr'].'</b><br>
'.$com.'
'.$bankData.'
<hr>
<br>
<br>Список заказанных товаров:<br><br>'.$zakTable;
				
				// Корректность стоимости
				if($webPayTotalPrice<35 && $zakaz['dostavka']=='Доставка курьером'){
					return array(
						'crtError'=>'<div class="error">ВНИМАНИЕ! Сумма заказа менее 35 рублей. При стоимости заказа менее 35 рублей доставка курьером невозможна. Пожалуйста, измените состав заказа, или выберите вариант доставки "самовывоз".</div>'
					);
				}
				
				//mail::mailSender('axiom.genius@gmail.com','Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
				//mail::mailSender('fourseasons@tut.by','Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
				
				//sms::send(375296312033,'В '.date("H:i",time()).' получен новый заказ на сумму '.$_SESSION['cart']['price'].' руб.');
				//sms::send(375296333121,'В '.date("H:i",time()).' получен новый заказ на сумму '.$_SESSION['cart']['price'].' руб.');
				
				unset($_SESSION['cart']);
				
				// Если оплата через ЕРИП или картой, то создаем форму
				if($zakaz['oplata']=='Банковской картой через Интернет' || $zakaz['oplata']=='Оплата через ЕРИП'){
					$webPayRealPrice=$webPayTotalPrice;
					$dostavkaPrice=15;
					
					if($zakaz['dostavka']=='Доставка курьером'){
						// Если стоимость менее 50 рублей, то добавляем стоимость доставки
						if($webPayTotalPrice<50){
							$webPayRealPrice=$webPayTotalPrice+$dostavkaPrice;
							$webPayTotalPrice=$webPayRealPrice;
							$webPayTovarFields.='<input type="hidden" name="wsb_shipping_name" value="Стоимость доставки">
	            		<input type="hidden" name="wsb_shipping_price" value="'.number_format($dostavkaPrice,2,'.','').'">
	            		';
						}
						else {
							if($webPayRealPrice<100){
								$dostavkaprice=7;
								$webPayRealPrice=$webPayTotalPrice+$dostavkaPrice;
								$webPayTotalPrice=$webPayRealPrice;
								$webPayTovarFields.='<input type="hidden" name="wsb_shipping_name" value="Стоимость доставки">
	            		<input type="hidden" name="wsb_shipping_price" value="'.number_format($dostavkaPrice,2,'.','').'">';
								
							}
						}
					}
					
					
					$seed=time();
					$orderNum=$sNumber;
					$returnUrl='https://formypeople.by/payment/success/';      // При успешной операции
					$cancelUrl='https://formypeople.by/payment/cancel/';       // При неудачной операции
					$notifyUrl='https://formypeople.by/payment/notify/';       // Для получения сообщений от платежной системы
					$storeId=000000000; // Идентификатор магазина:
					// Для эквайринга: 827586518
					//  Для тестовой среды (159313946)
					
					if($zakaz['oplata']=='Оплата через ЕРИП'){
						$storeId=000000000;
					}
					
					$secretKey='FourSeas219New';
					$currency='BYN';    // Валюта
					$test=0;            // Тестовый режим
					$total=number_format($webPayRealPrice,2,'.','');       // Общая сумма
					
					$signature=sha1($seed.$storeId.$orderNum.$test.$currency.$total.$secretKey);
					
					
					return array(
						'cBlock'=>'<form action="https://payment.webpay.by/" method="post">
<input type="hidden" name="*scart">
<input type="hidden" name="wsb_version" value="2">
<input type="hidden" name="wsb_language_id" value="russian">
<input type="hidden" name="wsb_storeid" value="'.$storeId.'">
<input type="hidden" name="wsb_store" value="Магазин formypeople.by">
<input type="hidden" name="wsb_order_num" value="'.$orderNum.'">
<input type="hidden" name="wsb_test" value="'.$test.'">
<input type="hidden" name="wsb_currency_id" value="'.$currency.'">
<input type="hidden" name="wsb_seed" value="'.$seed.'">
<input type="hidden" name="wsb_return_url" value="'.$returnUrl.'">
<input type="hidden" name="wsb_cancel_return_url" value="'.$cancelUrl.'">
<input type="hidden" name="wsb_notify_url" value="'.$notifyUrl.'">
'.$webPayTovarFields.'
<input type="hidden" name="wsb_total" value="'.$total.'">
<input type="hidden" name="wsb_signature" value="'.$signature.'">
<div class="row"><span style="font-size:18px; font-weight:bold; color:#000000;">Всего с учетом доставки: '.number_format($webPayRealPrice,2,'.','').' руб.</span></div>
<div class="row"><p>ВНИМАНИЕ! После нажатия кнопки "Оплатить" вы будете перенаправлены на страницу безопасных онлайн платежей системы WEBPAY.</p>
 <p>Пожалуйста, при выполнении платежа внимательно проверяйте корректность введенных Вами данных. В случае возникновения каких-либо сложностей при оплате, просим сообщить нам любым удобным способом.</p></div>
<input type="submit" class="button button-primary" value="ОПЛАТИТЬ">
</form>',
						'cartStatus'=>''
					);
				}
				
				return array(
					'cBlock'=>'<p><b>Ваш заказ успешно отправлен!</b></p>
                    <p>В ближайшее время мы свяжемся с вами для подтверждения заказа.</p>',
					'cartStatus'=>''
				);
			}
			else{
				return array(
					'crtError'=>'<div class="error">'.$err.'</div>'
				);
			}
		}
		return false;
	}
	
	static function deleteFromCart(){
		global $item;
		$item=urldecode($item);
		unset($_SESSION['cart']['items'][$item]);
		$fullPrice=0;
		foreach($_SESSION['cart']['items'] AS $key=>$val){
			$out=floatval($val['price']*$val['count']).' руб';
			$fullPrice=floatval($fullPrice+($val['price']*$val['count']));
			if($val['id']==$item){
				$ret['price'.$val['id']]=$out;
			}
		}
		$ret['crtSumma']=$fullPrice.' руб';
		return $ret;
	}
	
	static function cartCount()
	{
		global $item, $cnt, $cname;
		$cname=urldecode($cname);
		
		if(isset($_SESSION['cart']['items'])){
			$ret=array();
			$fullPrice=0;
			foreach($_SESSION['cart']['items'] AS $key=>$val){
				if($key==$cname){
					$val['count']=$cnt;
					$_SESSION['cart']['items'][$key]['count']=$cnt;
					$out=floatval($val['price']*$cnt).' руб';
					$ret['price'.$key]=$out;
				}
				$fullPrice=floatval($fullPrice+($val['price']*$val['count']));
			}
			$ret['crtSumma']=$fullPrice.' руб';
			$deliveryPrice='Доставка: 10 руб';
			if($fullPrice>100){
				$deliveryPrice='Доставка: Бесплатно';
			}
			$ret['deliveryPrice']=$deliveryPrice;
			return $ret;
		}
		return false;
	}
	
	//
	static function shopingCartFull(){
		global $site, $settings;
		$out='';
		
		if(!empty($_SESSION['cart']['items'])){
			
			$out.='<div class="row">
				<table class="cartTable">
					<tr class="headMobile">
						<th><b>2</b></th>
						<th>Товар</th>
						<th>К-во</th>
						<th style="text-align:center;">Цена</th>
						<th>&nbsp;</th>
					</tr>
					<tr class="headDesktop">
						<th>&nbsp;</th>
						<th>Наименование</th>
						<th>Количество</th>
						<th style="text-align:center;">Цена</th>
						<th>&nbsp;</th>
					</tr>';
			$trk=0;
			
			$fullPrice=0;
			foreach($_SESSION['cart']['items'] AS $key=>$val){
				$trk++;
				$out.='<tr id="tvc'.$trk.'">
					<td>';
				if($val['image']!=''){
					$out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.str_replace('.png','s.png',$val['image']).'" alt="'.htmlspecialchars($key).'">';
				}
				$out.='</td>
					<td class="tcNameField"><a id="cname'.$val['id'].'" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.trim(translit($val['name']).'_'.$val['id']).'/">'.$val['name'].'</a></td>
					<td>
						<div class="crtCount noSelect">
							<i class="ic-minus" onClick="cartCount('.$val['id'].',\''.$val['tid'].'\',0)"></i>
							<input id="cc'.$val['id'].'" type="text" value="'.$val['count'].'" readonly>
							<i class="ic-plus" onClick="cartCount('.$val['id'].',\''.$val['tid'].'\',1)"></i>
						</div>
					</td>
					<td class="crtPrice"><span id="price'.$val['tid'].'">';
					$out.=floatval($val['price']*$val['count']).' руб';
					$fullPrice=floatval($fullPrice+($val['price']*$val['count']));
					$out.='</span></td>
					<td><i class="ic-close" title="Удалить" onClick="delFromCart('.$trk.',\''.$key.'\')"></i></td>
						</tr>';
			}
			$deliveryPrice='Доставка: 10 руб';
			if($fullPrice>100){
				$deliveryPrice='Доставка: Бесплатно';
			}
			$out.='</table>
			</div>
			
			
			<div class="row cartSummField">
				<span>Сумма: </span><span id="crtSumma">'.$fullPrice.' руб</span>
			</div>
			<div class="row deliveryBlock">
				<span id="deliveryPrice">'.$deliveryPrice.'</span>
				<div>
					<span class="button button-primary" onClick="cartSent(1)">Оформить заказ</span>
				</div>
			</div>
			
			
		';
		
		}
		
		
		return $out;
	}
	
	static function addToCart(){
		global $settings,$site;
		$out='';
		
		$item=$_GET['item'];
		
		// Получим из БД всю инфу о товаре

		$entype=mysql::getValue("SELECT t2.entity_type_alias
		FROM `entity` AS t1
				JOIN `entity_type` AS t2 ON t2.id=t1.entity_type
			WHERE t1.id=".escape($item)." LIMIT 1");
			
		$array=mysql::getArray("SELECT t1.id, t1.image, t2.name
			FROM `data_".$entype."` AS t1
				JOIN `entity` AS t2 ON t2.id=t1.id
			WHERE
				t2.id=".escape($item)."
				LIMIT 1",true);
		
			
		$name=$array['name'];
		$tid=$array['id'];
		if(isset($_GET['weight']) && $_GET['weight']!=''){
			$name.='. Фасовка: '.$_GET['weight'];
			$tid.='f'.$_GET['weight'];
		}
		if(isset($_GET['pomol']) && $_GET['pomol']!='Не молоть'){
			$name.='. Помол: '.$_GET['pomol'];
			$tid.='p'.$_GET['pomol'];
		}
		$tid=str_replace(" ","",$tid);
		$tid=str_replace("&nbsp;","",$tid);
		
		$itemarray=array(
			'id'=>$item,
			'tid'=>$tid,
			'name'=>$name,
			'count'=>$_GET['count'],
			'price'=>$_GET['price'],
			'image'=>$array['image']
		);
			
		if(!isset($_SESSION['cart']['items'][$tid])){
			$_SESSION['cart']['items'][$tid]=$itemarray;
		}
		else{
			$_SESSION['cart']['items'][$tid]['count']=$_SESSION['cart']['items'][$tid]['count']+$_GET['count'];
		}
		$out.='<p>Товар добавлен в корзину. Перейдите к оформлению заказа, или закройте это окно, если хотите продолжить работу с магазином.</p>
		<div id="tvAdded">
			<div class="row">
				<table class="addtcTable">
					<tbody>
						<tr class="addtcDop">
							<td>Фото</td>
							<td>Наименование</td>
							<td>Цена</td>
							<td>К-во</td>
							<td>Стоимость</td>
						</tr>
						<tr>
							<td>';
		if($array['image']!=''){
			$out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($item/100).'/'.str_replace('.png','s.png',$array['image']).'">';
		}
		$out.='</td>
							<td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.trim(translit($name).'_'.$item).'/"><span>'.$name.'</span></a></td>
							<td>'.$_GET['price'].'</td>
							<td>'.$_GET['count'].'</td>
							<td>'.($_GET['price']*$_GET['count']).' руб</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="row">
				<a class="button button-primary" href="'.$settings['protocol'].$settings['siteUrl'].'/cart/">Оформить заказ</a>
			</div>
		</div>';
		
		ajax::window($out,true,'cartWindow');
		
		
		return array(
			'cartStatus'=>'<span>&nbsp;</span>'
		);
	}
	
	static function clearForm(){
		$filterData=coffee::getCoffeeFilterData();
		$out.='<input type="hidden" name="filter[category]" value="coffee">
					<div>
						<b>Кофе:</b>';
						if(!empty($filterData['ctype'])){
							foreach($filterData['ctype'] AS $key=>$val){
								$out.='<input class="radioCtype" id="rad'.$key.'" type="radio" onChange="filterStart()" name="filter[ctype]" value="'.$key.'"><label for="rad'.$key.'">'.$val.'</label>';
							}
						}
					$out.='
					</div>
					<div>
						<b>Тип кофе:</b>';
						if(!empty($filterData['type'])){
							foreach($filterData['type'] AS $key=>$val){
								$out.='<input id="rad'.$key.'" type="radio" onChange="filterStart()" name="filter[type]" value="'.$key.'"><label for="rad'.$key.'">'.$val.'</label>';
							}
						}
					$out.='</div>
					<div>
						<b>Кислотность:</b>';
						if(!empty($filterData['acidity'])){
							foreach($filterData['acidity'] AS $key=>$val){
								$out.='<input id="checkbx'.$key.'" type="checkbox" onChange="filterStart()" name="filter[acid][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
					$out.='</div>
					<div>
						<b>Обжарщик:</b>';
						if(!empty($filterData['brand'])){
							foreach($filterData['brand'] AS $key=>$val){
								$out.='<input id="checkbx'.$key.'" type="checkbox" onChange="filterStart()" name="filter[brand][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
					$out.='
					</div>
					<div>
						<b>Произрастание:</b>';
						if(!empty($filterData['country'])){
							foreach($filterData['country'] AS $key=>$val){
								$out.='<input id="checkbx'.$key.'" type="checkbox" onChange="filterStart()" name="filter[country][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
						
					$out.='</div>';
		ajax::javascript('filterStart()');
		return array(
			'filterForm'=>$out
		);
	}
	
	// Действия после отправки фильтра
	static function filterSend(){
		$filter=$_POST['filter'];
		$where=array();
		if(isset($filter['ctype'])){
			$where[]="t1.ctype=".$filter['ctype'];
		}
		if(isset($filter['type'])){
			$where[]="t1.type=".$filter['type'];
		}
		if(isset($filter['acid'])){
			$where[]="t1.acidity IN(".implode(",",$filter['acid']).")";
		}
		if(isset($filter['brand'])){
			$where[]="t1.brand IN(".implode(",",$filter['brand']).")";
		}
		//usleep(600000);
		
		
		if(!empty($where)){
			$wherestr=implode(" AND ",$where);
		}
		else $wherestr=false;
		
		$array=self::getCoffeeArray($wherestr);
		return array(
			'tovarCards'=>self::coffeeToCards($array)
		);
		return false;
	}
	
	// На вход массив товаров кофе, на выходе карточки
	static function coffeeToCards($array){
		global $settings;
		$out='';
		
		$acid=array(
			1070=>33,
			1071=>66,
			1072=>90
		);
		
		if($array!=false){
			foreach($array AS $val){
				$out.='
				<div id="tc'.$val['id'].'" class="tCard splashCard" style="background:'.$val['color'].'">
					<div class="dtCardBody" style="background:'.$val['color'].'">
						<div class="tcHead">
							<a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">'.$val['name'].'</a>
							<span>'.$val['anounce'].'</span>
						</div>
						<div class="tcAcidity">
							<b>Кислотность</b>
							<div>
								<span class="acact" style="width:'.$acid[$val['acidity']].'%"></span>
							</div>
						</div>
						<div class="tcForm'.$val['id'].'">
							<div class="tcRow">';
								if(isset($val['variant'])){
									$out.='<select id="var'.$val['id'].'" name="tovar[variant]" onChange="correctPrice(this.id)">';
									foreach($val['variant'] AS $v){
										$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
									}
									$out.='</select>';
								}
								if($val['pomol']==1){
									$out.='<select id="pomol'.$val['id'].'" name="tovar[pomol]">
										<option value="Не молоть">Не молоть</option>
										<option value="Крупный">Крупный</option>
										<option value="Мелкий">Мелкий</option>
									</select>';
								}

							$out.='</div>
							<div class="tcRow">
								<div class="tcCount">
									<i class="ic-minus" onclick="itemCount('.$val['id'].',0)"></i>
									<input id="cnt'.$val['id'].'" value="1">
									<i class="ic-plus" onclick="itemCount('.$val['id'].',1)"></i>
								</div>
								<div class="tcPrice">';
									if(isset($_SESSION['client'])){
										$out.='<div>36.00<hr/></div>';
									}
									$out.='
									<b id="price'.$val['id'].'">'.$val['price'].'</b>
								</div>
							</div>
							<div class="tcRow tcButtonRow">
								<span class="button button-primary" onClick="addToCart(\''.$val['id'].'\')">В корзину</span>
								<a class="button" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">Подробнее</a>
							</div>
						</div>
					</div>
	
					<div class="tcBody">
						<div class="tcImage">';
							
							//<img src="./p/tovarPhoto.png" alt="Фото товара">
						if($val['image']!=''){
							$out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.str_replace('.png','s.png',$val['image']).'" alt="Фото '.htmlspecialchars($val['name']).'">';
						}
						else $out.='<img src="'.$settings['protocol'].$settings['siteUrl'].'/p/tovarPhoto.png" alt="Фото '.htmlspecialchars($val['name']).'">';
						$out.='</div>
						<div class="tcHead">
							<a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">'.$val['name'].'</a>
							<span>'.$val['obrabotka'].'</span>
						</div>
						<div class="tcAcidity">
							<b>Кислотность</b>
							<div><span style="width:'.$acid[$val['acidity']].'%"></span></div>
						</div>
						<form id="tccForm'.$val['id'].'">
							<div class="tcRow">';
								if(isset($val['variant'])){
									$out.='<select id="vard'.$val['id'].'" name="tovar[variant]" onChange="correctPrice(this.id)">';
									foreach($val['variant'] AS $v){
										$out.='<option value="'.$v['name'].'|'.$v['price'].'">'.$v['name'].'</option>';
									}
									$out.='</select>';
								}
						
								if($val['pomol']==1){
									$out.='
									<select id="pomold'.$val['id'].'" name="tovar[pomol]">
										<option value="Не молоть">Не молоть</option>
										<option value="Крупный">Крупный</option>
										<option value="Мелкий">Мелкий</option>
									</select>';
								}
					
								$out.='
							</div>
							<div class="tcRow">
								<div class="tcCount">
									<i class="ic-minus" onclick="itemCount(\'d'.$val['id'].'\',0)"></i>
									<input id="cntd'.$val['id'].'" value="1">
									<i class="ic-plus" onclick="itemCount(\'d'.$val['id'].'\',1)"></i>
								</div>
								<div class="tcPrice">';
									if(isset($_SESSION['client'])){
										$out.='<div>36.00<hr/></div>';
									}
									$out.='
									<b id="priced'.$val['id'].'">'.$val['price'].'</b>
								</div>
							</div>
							<div class="tcRow tcBtnRow">
								<span class="button button-primary" onClick="addToCart(\'d'.$val['id'].'\')">В корзину</span>
								<a class="button" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.translit($val['name']).'_'.$val['id'].'/">Подробнее</a>
							</div>
						</form>
					</div>
	
	
				</div>';
			}
			$out.='<div id="tc'.$val['id'].'" class="tCard splashCard" style="margin:0; padding:0; opacity:1;height:0;overflow:hidden;"></div>';
		}
		else{
			$out.='<p style="display:block; width:100%; background:#f1f1f1; padding:20px; font-size:18px;">По вашему запросу ничего не найдено! Измените критерии поиска или очистите фильтр.</p>';
		}
		return $out;
	}
	
	// Получаем карточки кофе
	static function getCoffeeArray($where=false,$order=false,$limit=false){
		if($where==false) $where="t2.hidden='0'";
		else $where.=" AND t2.hidden='0' ";
		if($order==false) $order="new";
		if($limit==false) $limit="500";
		
		if($order===false){
			$order='new';
		}
		if(isset($_SESSION['orderStatus'])){
			$order=$_SESSION['orderStatus'];
		}
		
		$orders=array(
			'new'=>'t1.id DESC',
			'hiprice'=>'t1.price DESC',
			'loprice'=>'t1.price ASC'
		);
		
		
		$array=mysql::getArray("SELECT t1.id, t1.color, t2.name, t2.hidden, t1.obrabotka, t1.anounce, t1.price, t1.acidity, t3.value AS acidityName, t1.image, t1.type, t4.value AS typeName, t1.ctype, t5.value AS ctypeName, t1.brand, t6.name AS brandName, t1.country, t8.value AS countryName, t1.pomol, t1.rek
			FROM
			`data_coffee` AS t1
				JOIN `entity` AS t2 ON t2.id=t1.id
				JOIN `options` AS t3 ON t3.id=t1.acidity
				JOIN `options` AS t4 ON t4.id=t1.type
				JOIN `options` AS t5 ON t5.id=t1.ctype
				JOIN `entity` AS t6 ON t6.id=t1.brand
				JOIN `data_brand` AS t7 ON t7.id=t6.id
				JOIN `options` AS t8 ON t8.id=t1.country
			WHERE
				".$where."
			ORDER BY ".$orders[$order]." LIMIT ".$limit);
		// Получаем все множественные данные товаров
		if($array!=false){
			$ids=array();
			foreach($array AS $val){
				$ids[]=$val['id'];
			}
			
			if(!empty($ids)){
				$var=mysql::getArray("SELECT t1.id, t1.price, t2.parent_id, t2.name
				FROM
					`data_variant` AS t1
					JOIN `entity` AS t2 ON t2.id=t1.id
				WHERE
					t2.parent_id IN(".implode(",",$ids).")
					AND t2.hidden='0'
				ORDER BY t1.price ASC");
				
				if(is_array($var)){
					foreach($var AS $k=>$v){
						foreach($array AS $key=>$val){
							if($val['id']==$v['parent_id']){
								if(!isset($array[$key]['variant'])){
									$array[$key]['variant']=array();
									$array[$key]['price']=10000000;
								}
								$array[$key]['variant'][]=$v;
								if($array[$key]['price']>$v['price']){
									$array[$key]['price']=$v['price'];
								}
							}
						}
					}
				}
			}
			return $array;
		}
		return false;
	}
	
	
	// Кеширование данных фильтра для кофе
	static function getCoffeeFilterData(){
		$fname='coffeeFilterData';
		$filterData=cache::read($fname);
		if($filterData!=false){
			return $filterData;
		}
		// Получаем Брэнды и тип кофе, которые есть в наличии
		$array=mysql::getArray("SELECT DISTINCTROW t1.type, t5.value AS typeName, t1.acidity, t6.value AS acidityName, t1.brand, t3.name AS brandName, t1.ctype, t4.value AS ctypeName, t1.country, t8.value AS countryName
	FROM
		`data_coffee` AS t1
		JOIN `entity` AS t2 ON t2.id=t1.id
		JOIN `entity` AS t3 ON t3.id=t1.brand
		JOIN `options` AS t4 ON t4.id=t1.ctype
		JOIN `options` AS t5 ON t5.id=t1.type
		JOIN `options` AS t6 ON t6.id=t1.acidity
		JOIN `data_brand` AS t7 ON t7.id=t1.brand
		JOIN `options` AS t8 ON t8.id=t1.country
	WHERE
		t2.hidden='0'
	");
		
		$filterData=array(
			'brand'=>array(),
			'ctype'=>array(),
			'type'=>array(),
			'acidity'=>array(),
			'country'=>array()
		);
		
		if($array!=false){
			foreach($array AS $val){
				if(!isset($filterData['brand'][$val['brand']])){
					$filterData['brand'][$val['brand']]=$val['brandName'];
				}
				if(!isset($filterData['ctype'][$val['ctype']])){
					$filterData['ctype'][$val['ctype']]=$val['ctypeName'];
				}
				if(!isset($filterData['type'][$val['type']])){
					$filterData['type'][$val['type']]=$val['typeName'];
				}
				if(!isset($filterData['acidity'][$val['acidity']])){
					$filterData['acidity'][$val['acidity']]=$val['acidityName'];
				}
				if(!isset($filterData['country'][$val['country']])){
					$filterData['country'][$val['country']]=$val['countryName'];
				}
			}
			cache::save($fname,$filterData);
		}
		return $filterData;
	}
	
	// При сохранении потомка выполняем нужные функции над родительским элементом кофе
	static function updateParent($child){
		echo '<pre>';
		print_r($child);
		echo '</pre>';
	}
	
	// Возвращает HTML код для списка сортировки товаров
	static function orderStatus(){
		$out='';
		$order='new';
		if(isset($_SESSION['orderStatus'])){
			$order=$_SESSION['orderStatus'];
		}
		
		$s=array(
			'new'=>'Сначала новые',
			'hiprice'=>'Сначала дорогие',
			'loprice'=>'Сначала дешевые'
		);
		
		foreach($s AS $key=>$val){
			$out.='<option ';
			if($key==$order) $out.='selected="selected" ';
			$out.='value="'.$key.'">'.$val.'</option>';
		}
		
		return $out;
	}
}