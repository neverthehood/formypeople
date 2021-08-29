<?php

class shop{
	
	// Папки с категориями товара
	static function getCatalogMenu(){
		static $catalogMenu=false;
		if($catalogMenu===false){
			$catalogMenu=mysql::getArray("SELECT * FROM `data_category` AS t1 JOIN `entity` AS t2 ON t2.id=t1.id WHERE t2.parent_id=43 AND t2.hidden='0' ORDER BY t1.order ASC");
		}
		return $catalogMenu;
	}
	
	// Отображение окна с политикой конфиденциальности
	static function showPolicy(){
		global $site;
		$m=mysql::getValue("SELECT `text` FROM
		`pages` AS t1
		JOIN `pages_text` AS t2 ON t2.page_id=t1.id
		WHERE t1.alias='politika'
		LIMIT 1");
		if($m!=false){
			ajax::window('<h2>Политика конфиденциальности</h2>'.$m,true,'policy');
		}
		return false;
	}
	
	static function itemSigns($val){
		global $currentAction;
		$out='';
		if(isset($val['hot']) && $val['hot']==1) $out.='<span class="spnew" title="Новинка"></span>';
		if(isset($val['action']) && $val['action']==1) $out.='<span class="sphot" title="Акция: '.htmlspecialchars($currentAction['name']).'"></span>';
		/*if(isset($val['hot']) && $val['hot']==1) $out.='<span class="sphot" title="Хит продаж"></span>';*/
		if(isset($val['rek']) && $val['rek']==1) $out.='<span class="sprek" title="Рекомендуемый"></span>';
		if($out!='') $out='<div class="spSigns">'.$out.'</div>';
		return $out;
	}
	
	// Получение текущей акции
	static function getCurrentAction(){
		// Получаем данные по акции
		global $currentAction;
		if(!isset($currentAction)){
			$currentAction=mysql::getArray("SELECT * FROM `data_actions` AS t1
	  JOIN `entity` AS t2 ON t2.id=t1.id
	  JOIN `text_actions` AS t3 ON t3.id=t2.id
	  WHERE t2.hidden='0' LIMIT 1", true);
		}
		return true;
	}
	
	// Возвращает новую цену товара с учетом акций и скидок
	// Ключам массива 'price' и 'discount' пристваиваются новые значения
	static function tovarCorrectPrice($val){
		global $currentAction;
		if(!isset($val['discount'])){
			$val['discount']=$val['price'];
		}
		// Если есть текущая акция, то посчитаем цену с учетом акции
		shop::getCurrentAction();
		if(isset($currentAction['discount']) && isset($val['action']) && $val['action']==1){
			$actPrice=$val['price']-(($val['price']/100)*$currentAction['discount']);
			// Если цена товара по акции меньше, чеи собственная скидка на товар
			if($actPrice<$val['discount']){
				$val['discount']=$actPrice;
			}
		}
//		else {
//			// Если пользователь зарегистрирован на сайте, то делаем ему скидку 1%
//			if(isset($_SESSION['client']['id'])){
//				$val['discount']=$val['price']-($val['price']/100);
//			}
//		}
		return $val;
	}

    // Возвращает путь по ID нужной папки
    static function folderPathById($id,$path=false){
        global $settings, $site;
        // Получаем пути к папкам
        // Работа с кэшем
        static $array;
        $cacheLife=24*60*60;
        $array=cache::read("CatFoldersTree");
        if($array===false || time()>$array['cacheLifeTime']){
            $array=array();
            $folders=mysql::getArray("SELECT t1.id, t2.parent_id, t2.name, t1.alias, t1.child_entity_type AS entity, t1.izobrazhenie AS image, t3.entity_type AS child_type, t3.entity_type_alias AS child_alias 
        FROM `data_category` AS t1
            JOIN `entity` AS t2 ON t2.id=t1.id
            JOIN `entity_type` AS t3 ON t3.id=t1.child_entity_type
        WHERE
            t2.hidden='0'
        ORDER BY
            t1.order ASC");
            $array['content']=$folders;
            $array['cacheLifeTime']=time()+$cacheLife;
            cache::save("CatFoldersTree",$array);
        }
        else $folders=$array['content'];

        if($path===false) $path=array();

        // Начинаем искать заданную папку, перебирая фрагменты URI
        // Сначала ищем корневую папку каталога
        foreach($folders AS $rt){
            if($rt['id']==$id){
                $path[]=$rt['alias'];
                if($rt['parent_id']==43) return $path;
                $path=self::folderPathById($rt['parent_id'],$path);
                break;
            }
        }
        return $path;
    }

    static function prokatSendFull(){
//        echo '<pre>';
//        print_r($_POST);
//        echo '</pre>';
        $prokat=$_POST['prokat'];
        $error='';
        if(!isset($prokat['user'])) $error.='Некорректные данные для заказа! ';
        else{
            $tel=onlyDigit($prokat['user']['tel']);
            if(strlen($tel)<12) $error.='Недопустимый номер телефона! Пожалуйста, введите номер телефона в международном формате. ';
        }
        $price=0;
        $itemIds=array();
        $count=0;
        $zakazItems=array('Комплекты'=>array(), 'Персонал'=>array());
        foreach($prokat AS $key){
            // Комплекты
            if($key=='complect'){
                foreach($prokat[$key] AS $k=>$v){
                    $count++;
                    $itemIds[]=$k;
                    $price+=$v;
                    $zakazItems[$k]=array(

                    );
                }
            }
        }
        return false;
    }

    static function prokatZakazForm(){
        $out='<hr>
        <div class="row" id="dfError"></div>
        <div class="row">
            <div class="six columns">
                <label>Ваше имя</label>
                <input type="text" name="prokat[user][name]" placeholder="Иван Иванов" style="width:100%; margin-bottom:6px;">
                <label>Телефон</label>
                <input type="text" name="prokat[user][tel]" style="width:100%; margin-bottom:6px;" placeholder="+37529 000-00-00" onkeyup="telFormat(this)" oninput="telFormat(this)">
            </div>
            <div class="six columns">
                <label>Вопрос или комментарий</label>
                <textarea name="prokat[user][description]" style="width:100%; height:116px; margin-bottom:6px;"></textarea>
                <div class="button button-primary" style="width:100%;" onClick="ajaxPost(\'prokatForm\',\'shop::prokatSendFull\')">Отправить заявку</div>
            </div>
        </div>
        ';

        return array(
            'pzForm'=>$out
        );
    }

    // Подсчет стоимости проката
    static function prokatSend(){

        if(isset($_POST['prokat'])){
            $prokat=$_POST['prokat'];
            $price=0;
            $count=0;
            $items=array();

            // Комплекты
            if(isset($prokat['complect'])){
                foreach($prokat['complect'] AS $key=>$val){
                    $price+=$val['price'];
                    $items[]=$key;
                    $count++;
                }
            }

            // Персонал
            if(isset($prokat['personal'])){
                foreach($prokat['personal'] AS $key=>$val){
                    $price+=$val['price'];
                    $items[]=$key;
                    $count++;
                }
            }

            // Доставка
            if(isset($prokat['dostavka'])){
                foreach($prokat['dostavka'] AS $key=>$val){
                    $price+=$val['price'];
                    $items[]=$key;
                    $count++;
                }
            }

            // Оборудование
            if(isset($prokat['items'])){
                foreach($prokat['items'] AS $key=>$val){
                    if(isset($val['price'])){
                        $price+=($val['price']*$val['count']);
                        $items[]=$key;
                        $count++;
                    }
                }
            }

            $dopForm='';
            $priceMess='';
            if($price>200){
                $dopForm.='<div class="twelve columns">
                    <div class="button" onClick="ajaxPost(\'pzForm\',\'shop::prokatZakazForm\')">Оформить заказ</div>
                </div>';
            }
            else{
                $priceMess.='<div class="error">К сожалению, минимальная стоимость заказа составляет 200 рублей. Рекомендуем дополнить свой заказ.</div>';
            }

            return array(
                'prokatResult'=>'<div class="row"><hr></div>
                <div class="row">
                    <div class="six columns">
                        '.$priceMess.'&nbsp;
                    </div>
                    <div class="six columns">
                        <div class="prokatPrice">Стоимость (руб.):<b>'.$price.'</b></div>
                    </div>
                </div>
                <div class="row" id="pzForm">
                    '.$dopForm.'
                </div>
                '
            );

        }

        return false;
    }

    // Смена к-ва товаров в корзине
    static function changeCount(){
        global $item, $count;
        $count=onlyDigit($count);
        if(isset($_SESSION['cart']['items'][$item]) && $count>=1){
            $_SESSION['cart']['items'][$item]['count']=$count;
            if($count==0){
                unset($_SESSION['cart']['items'][$item]);
            }
            self::cartRecount();
            return array(
                $item.'price'=>triada($count*$_SESSION['cart']['items'][$item]['price'],2),
                'ttfPrice'=>triada($_SESSION['cart']['price'],2),
                'cartPrice'=>'<span>'.triada($_SESSION['cart']['price'],2).'</span>руб',
                'cartCount'=>'<span>'.$_SESSION['cart']['count'].'</span>'
            );
        }
        return false;
    }

    // Отправка запроса на снижение цены
    static function discountSend(){
        global $settings;
        $item=$_POST['zakaz']['id'];
        $zakaz=$_POST['zakaz'];

        $array=mysql::getArray("SELECT * FROM `entity` AS t1 JOIN `entity_cache` AS t2 ON t2.id=t1.id WHERE t1.id=".escape($item)." LIMIT 1",true);
        if($array!=false){
            $image='<img style="width:50px;" src="'.$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg" alt="">';
            if(strlen($array['photo'])>=8){
                $image='<img style="width:50px;" src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($array['id']/100).'/'.$array['photo'].'" alt="">';
            }
            $price=$array['price'];
            if($array['discount']<$array['price']){
                $price=$array['discount'];
            }
            $price=triada($price,2);
	
	        $alias=translit($array['name']).'_'.$array['id'];
	        $alias=str_replace('__','_',$alias);

            $body=date("d.m.Y", time()).' в '.date("H:i").' получен запрос на снижение цены от пользователя <b>'.$zakaz['name'].'</b>.<br><br>
Имя: <b>'.$zakaz['name'].'</b><br>
Телефон: <b>'.$zakaz['tel'].'</b><br>
Ссылка на более низкую цену: <b><a href="'.$zakaz['link'].'">'.$zakaz['link'].'</a></b>
<hr>
<br>
<br>Товар:<br><br>
<p><table style="width:100%; max-width:800px; border:1px solid #999999; border-collapse:collapse;">
<tr><th>Фото</th><th>Наименование</th><th>Цена</th></tr>
<tr><td>'.$image.'</td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">'.$array['name'].'</a></td><td>'.$price.'</td></tr>
<tr><td colspan="2"></td><td><b>'.$price.'</b> руб.</td></tr>
</table></p>';

            mail::mailSender($settings['adminMail'],'Администратор','Пользователь '.htmlspecialchars($zakaz['name']).' запросил снижение цены',$body);

//            sms::send(375296312033,'В '.date("H:i",time()).' получен запрос на снижение цены');
//            sms::send(375293262323,'В '.date("H:i",time()).' получен запрос на снижение цены');
//            sms::send(375333262323,'В '.date("H:i",time()).' получен запрос на снижение цены');
            return array(
                'oneClickWin'=>'<p>Спасибо! В самое ближайшее время мы свяжемся с вами и сообщим о возможности снижения цены.</p>'
            );
        }
        return false;
    }

    static function OneClickSend(){
        $item=$_POST['zakaz']['id'];
        $zakaz=$_POST['zakaz'];
        global $settings;
        $array=mysql::getArray("SELECT * FROM `entity` AS t1 JOIN `entity_cache` AS t2 ON t2.id=t1.id WHERE t1.id=".escape($item)." LIMIT 1",true);
        if($array!=false){
            $ealias=mysql::getValue("SELECT `entity_type_alias` FROM `entity_type` WHERE id=".$array['entity_type']." LIMIT 1");
            $array['discount']=mysql::getValue("SELECT discount FROM `data_".$ealias."` WHERE id=".escape($array['id'])." LIMIT 1");
            $image='<img style="width:50px;" src="'.$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg" alt="">';
            if(strlen($array['photo'])>=8){
                $image='<img style="width:50px;" src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($array['id']/100).'/'.$array['photo'].'" alt="">';
            }
            $price=$array['price'];
            if($array['discount']<$array['price']){
                $price=$array['discount'];
            }
            $price=triada($price,2);
	
	        $alias=translit($array['name']).'_'.$array['id'];
	        $alias=str_replace('__','_',$alias);

            $body=date("d.m.Y", time()).' в '.date("H:i").' получен заказ от пользователя <b>'.$zakaz['name'].'</b>.<br><br>
Имя: <b>'.$zakaz['name'].'</b><br>
Телефон: <b>'.$zakaz['tel'].'</b><br>
<hr>
<br>
<br>Список заказанных товаров:<br><br>
<p><table style="width:100%; max-width:800px; border:1px solid #999999; border-collapse:collapse;">
<tr><th>Фото</th><th>Наименование</th><th>Цена</th><th>К-во</th><th>Стоимость</th></tr>
<tr><td>'.$image.'</td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">'.$array['name'].'</a></td><td>'.$price.'</td><td>1</td><td>'.$price.'</td></tr>
<tr><td colspan="4"></td><td><b>'.$price.'</b> руб.</td></tr>
        </table></p>';

            mail::mailSender($settings['adminMail'],'Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
            //mail::mailSender('axiom.genius@gmail.com','Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
            //mail::mailSender('info@mediaexpert.pro','Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
            //sms::send(375296312033,'В '.date("H:i",time()).' получен новый заказ на сумму '.$price.' руб.');
            //sms::send(375293262323,'В '.date("H:i",time()).' получен новый заказ на сумму '.$price.' руб.');
            //sms::send(375333262323,'В '.date("H:i",time()).' получен новый заказ на сумму '.$price.' руб.');

            return array(
                'oneClickWin'=>'<p>Спасибо! Информация о заказе отправлена. В самое ближайшее время мы свяжемся с вами.</p>'
            );
        }
        return false;
    }

    static function setYurLicoFields(){
        return array(
            'yurLicoDiv'=>'<hr><h3>Данные юридического лица</h3>
            <div class="row">
            <div class="six columns">
                <label>Организация</label>
                <input type="text" name="udata[orgname]" value="">
                <label>УНП</label>
                <input type="text" name="udata[unp]" value="">
            </div>
            <div class="six columns">
                <label>Реквизиты</label>
                <textarea name="udata[rek]" style="width:100%;">
Юридический адрес:             
Расчетный счет: 
Банк: 
Код банка: 
Адрес банка: 
ФИО руководителя: 
Должность руководителя: 
Действует на основании: </textarea>
            </div>
            </div>
            '
        );
    }

    static function cartSent(){
        global $item, $settings;
        $udata=array(
            'name'=>'',
            'tel'=>'',
            'mail'=>'',
            'city'=>'Минск',
            'addr'=>'',
            'comment'=>'',
            'dostavka'=>'Доставка курьером',
            'oplata'=>'Оплата при получении'
        );

        $do=explode(',','Оплата при получении,Банковский перевод (Для юр. лиц),Оплата через ЕРИП');
        $dm=explode(',','Доставка курьером,Самовывоз из магазина,Доставка почтой');
        if(isset($_SESSION['udata'])) $udata=$_SESSION['udata'];
        if(isset($_POST['udata'])) $udata=$_POST['udata'];
        if($item==1){
            $opts='';
            foreach($dm AS $val){
                $selected='';
                if($val==$cart['dostavka']) $selected=' selected="selected"';
                $opts.='<option'.$selected.' value="'.$val.'">'.$val.'</option>';
            }
            $opts2='';
            foreach($do AS $val){
                $selected='';
                if($val==$cart['oplata']) $selected=' selected="selected"';
                $opts2.='<option'.$selected.' value="'.$val.'">'.$val.'</option>';
            }

            // Шаг 1: Отображаем форму для вставки личных данных
            $out.='<div class="row">
            <div class="six columns">
                <label>* Ваше имя</label>
                <input type="text" name="udata[name]" value="'.$udata['name'].'">
                <label>* Телефон</label>
                <input type="text" name="udata[tel]" value="'.$udata['tel'].'" placeholder="+37529 111-22-33">
                <label>* E-mail</label>
                <input type="text" name="udata[mail]" value="'.$udata['mail'].'" placeholder="mail@domain.com">
                <div class="row" style="margin-top:10px; height:40px;">
                    <label><input type="checkbox" name="udata[subscribe]" value="Подписка на рассылку" checked="checked">Подписаться на рассылку</label>
                </div>
               
                <label>* Город / Нас. пункт</label>
                <input type="text" name="udata[city]" value="'.$udata['city'].'">
            </div>
            <div class="six columns">
                <label>* Улица, Дом, Корпус, Квартира/Офис</label>
                <input type="text" name="udata[addr]" value="'.$udata['addr'].'" placeholder="Красная, 10, корпус 2, кв 111">
                <select id="opList" name="udata[oplata]" onChange="setOplata()">'.$opts2.'</select>
                <select name="udata[dostavka]">'.$opts.'</select>
                <label>Комментарий или вопрос</label>
                <textarea name="udata[comment]">'.$udata['comment'].'</textarea>
            </div>
        </div>
        <div id="yurLicoDiv">
        </div>
        <div class="row">
            <div class="button" onClick="cartSent(2)">Подтвердить заказ</div>
        </div>
        ';
            return array(
                'zkDiv'=>'<form id="cartForm" method="POST"><h3>Контактные данные</h3><hr><div id="crtError"></div>'.$out.'</form>'
            );
        }
        // Подтверждение заказа
        elseif($item==2){
            $err='';
            $udata=$_POST['udata'];
            if($udata['name']==''){
                $err.='Не заполнено имя!<br>';
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
                $zakTable='<p><table style="width:100%; max-width:800px; border:1px solid #999999; border-collapse:collapse;">
        <tr><th>Фото</th><th>Наименование</th><th>Цена</th><th>К-во</th><th>Стоимость</th></tr>';
                $fullCost=0;
                $bg='fcfcfc';
                foreach($_SESSION['cart']['items'] AS $key=>$val){
                    if($bg=='fcfcfc') $bg='ffffff';
                    else $bg='fcfcfc';
	                $alias=translit($val['name']).'_'.$key;
	                $alias=str_replace('__','_',$alias);
                    $zakTable.='<tr><td style="background:#'.$bg.'"><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($key/100).'/'.$val['photo'].'" alt="" style="width:50px;"></td><td style="background:#'.$bg.'"><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">'.$val['name'].'</a></td><td style="background:#'.$bg.'">'.triada($val['price'],2).'</td><td style="background:#'.$bg.'">'.$val['count'].'</td><td style="background:#'.$bg.'">'.triada(($val['price']*$val['count']),2).'</td><tr>';
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


                $body=date("d.m.Y", time()).' в '.date("H:i").' получен заказ от пользователя <b>'.$zakaz['name'].'</b>.<br><br>
Имя: <b>'.$zakaz['name'].'</b><br>
Телефон: <b>'.$zakaz['tel'].'</b><br>
E-mail: <b>'.$zakaz['mail'].'</b><br>
'.$podpiska.'
Способ оплаты: <b>'.$zakaz['oplata'].'</b><br>
Способ доставки: <b>'.$zakaz['dostavka'].'</b><br>
<hr>
<b>Доставка по адресу: </b><br>
Город: <b>'.$zakaz['city'].', <br>
Адрес: '.$zakaz['addr'].'</b><br>
'.$bankData.'
<hr>
<br>
<br>Список заказанных товаров:<br><br>'.$zakTable;
                mail::mailSender($settings['adminMail'],'Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);
                //mail::mailSender('info@mediaexpert.pro','Администратор','Получен заказ от пользователя '.htmlspecialchars($zakaz['name']),$body);

                //sms::send(375296312033,'В '.date("H:i",time()).' получен новый заказ на сумму '.$_SESSION['cart']['price'].' руб.');
                //sms::send(375293262323,'В '.date("H:i",time()).' получен новый заказ на сумму '.$_SESSION['cart']['price'].' руб.');
                //sms::send(375333262323,'В '.date("H:i",time()).' получен новый заказ на сумму '.$_SESSION['cart']['price'].' руб.');

                unset($_SESSION['cart']);
                return array(
                    'cBlock'=>'<p><b>Ваш заказ успешно отправлен!</b></p>
                    <p>В ближайшее время мы свяжемся с вами для подтверждения состава заказа и вариантов оплаты и доставки.</p>',
                    'cartPrice'=>'',
                    'cartCount'=>''
                );
            }
            else{
                return array(
                    'crtError'=>'<div class="error">'.$err.'</div>'
                );
            }

            return false;
        }
    }

    static function showShopingCart(){
        global $settings;
        $out='';
        if(isset($_SESSION['cart']['price']) && $_SESSION['cart']['items']>0){
            $out.='<div class="row"><p>Если нужно, скорректируйте количество заказываемых товаров, а затем переходите к оформлению заказа.</p></div>
            <div class="row"><table class="tvrTable">
        <tr><th style="width:80px;">Фото</th><th>Наименование</th><th style="width:80px;">Количество</th><th class="tdlast">Стоимость</th><th class="tdic">&nbsp;</th></tr>';
            foreach($_SESSION['cart']['items'] AS $key=>$val){
                if($val['photo']!=''){
                    $photo='<a rel="iLoad" href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($key/100).'/'.$val['photo'].'"><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($key/100).'/'.$val['photo'].'"></a>';
                }
                else {
                    $photo='<img src="'.$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg" alt="">';
                }
	            $alias=translit($val['name']).'_'.$key;
	            $alias=str_replace('__','_',$alias);
                $out.='<tr id="crtel'.$key.'"><td>'.$photo.'</td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">'.$val['name'].'</a></td><td><input type="number" value="'.$val['count'].'" min="1" id="crtcnt'.$key.'" onClick="cartChangeCount('.$key.')"></td><td id="'.$key.'price" class="tdlast">'.triada(($val['price']*$val['count']),2).'</td><td class="tdic"><i class="ic-close" title="Удалить" onClick="deleteFromCart('.$key.')"></i></td></tr>';
            }
            $out.='<tr><td id="ttfPrice" colspan="5">'.triada($_SESSION['cart']['price'],2).' руб</td></tr>
        </table></div>
        <div class="row" id="zkDiv">
            <div class="button" onClick="cartSent(1)">Оформить заказ</div>
        </div>
        
        ';
        }
        else {
            $out.='<p>Ваша корзина заказов пуста. Заполните ее товарами.</p>';
        }
        return $out;
    }

    // Удаление товара
    static function deleteFromCart(){
        global $item;
        ajax::javascript('slowlyDel("crtel'.$item.'")');
        if(isset($_SESSION['cart']['items'][$item])){
            unset($_SESSION['cart']['items'][$item]);
            self::cartRecount();
            if(isset($_SESSION['cart']['count']) && $_SESSION['cart']['count']>0){
                return(
                    array(
                        'cartPrice'=>'<span>'.triada($_SESSION['cart']['price'],2).'</span> руб',
                        'ttfPrice'=>triada($_SESSION['cart']['price'],2).' руб',
                        'cartCount'=>'<span>'.$_SESSION['cart']['count'].'</span>'
                    )
                );
            }
            else {
                return(
                    array(
                        'cartPrice'=>'',
                        'cartCount'=>'',
                        'tvrTable'=>self::showShopingCart()
                    )
                );
            }
        }
    }

    static function quickSearch(){
        global $item, $settings;
        $out='';
        // Быстрый поиск
        if(strlen($item)>=3){
            $search=urldecode(trim($item));
            $array=mysql::getArray("SELECT t1.*, t2.*  
			FROM `entity_cache` AS t2
				JOIN `entity` AS t1 ON t1.id=t2.id
				JOIN `data_category` AS t3 ON t3.id=t1.parent_id
			WHERE 
				(t1.`name` LIKE '%".escape($search)."%' OR t2.`id` LIKE '".escape($search)."%' )
				AND t1.hidden='0' AND t2.price>1
				AND t3.child_entity_type IN(46,162) /* Только для заданных типов */
			ORDER BY t1.`name` ASC  
			LIMIT 50");
            if($array!=false){
                $out.='<table class="srTable">';
                foreach($array AS $key=>$val){
                    if($key<=50){
                        $price=$val['price'];
                        if($val['discount']<$val['price']){
                            $price=$val['discount'];
                        }
                        $image='<img src="'.$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg" alt="">';
                        if($val['photo']!=''){
                            list($fn,$fe)=explode('.',$val['photo']);
                            $image='<img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.str_replace('.'.$fe,'s.'.$fe,$val['photo']).'" alt="">';
                        }
	
	                    $alias=translit($val['name']).'_'.$val['id'];
	                    $alias=str_replace('__','_',$alias);
                        $out.='<tr class="srElem"><td>'.$image.'</td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">'.strip_tags(hyphen($val['name'])).'</a><!--u>(Код: '.$val['art'].')</u --></td><td class="srPr"><b>'.triada($price,2).'</b> руб.</td></tr>';
                    }
                }
                $out.='</table>';
                //$out=preg_replace("#($search)#iu", '<span>$1</span>', $out);
                if(count($array)>48) $out.='<p style="font-size:14px; padding:10px;">Найдено слишком много результатов. Попробуйте изменить поисковую фразу</p>';
            }
            else {
                $out.='<p style="font-size:14px; color:#dd0000; padding:16px;">По вашему запросу ничего не найдено. Попробуйте изменить поисковую фразу</p>';
            }
            return array('qsResult'=>'<div>'.$out.'</div>');
        }
        else {
            return false;
        }
    }

    static function addToCart(){
        global $item, $count, $settings;
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart']=array('items'=>array(), 'count'=>0, 'price'=>0);
        }
        $table='';
        // Если товара еще нет в корзине
        if(!isset($_SESSION['cart']['items'][$item])) {
            // Получим данные сущности, чтобы положить все в корзину
            $array=mysql::getArray("SELECT t1.name, t1.parent_id, t2.entity_type_alias AS entype    
            FROM `entity` AS t1
                JOIN `entity_type` AS t2 ON t2.id=t1.entity_type
            WHERE
                t1.id=".escape($item)."
            LIMIT 1",true);

            if($array!=false){
                // Получаем цену и фото товара
                $data=mysql::getArray("SELECT * FROM `data_".escape($array['entype'])."` WHERE id=".escape($item)." LIMIT 1",true);
                if($data!=false){
                    $array['price']=$data['price'];
                    if($data['discount']<$data['price']){
                        $array['price']=$data['discount'];
                    }
                    $array['photo']=$data['photo'];
                    $array['count']=$count;
                    $_SESSION['cart']['items'][$item]=$array;
                }
            }
        }
        else {
            $_SESSION['cart']['items'][$item]['count']+=$count;
        }
        self::cartRecount();
        if(isset($_SESSION['cart']['price']) && $_SESSION['cart']['items']>0){
        	
	        $alias=translit($_SESSION['cart']['items'][$item]['name']).'_'.$item;
	        $alias=str_replace('__','_',$alias);
	        
	        $table='<div class="row"><table class="addtcTable"><tr class="addtcDop"><td>Фото</td><td>Наименование</td><td>Цена</td><td>К-во</td><td>Стоимость</td></tr><tr><td><img src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($item/100).'/'.str_replace('.','s.',$_SESSION['cart']['items'][$item]['photo']).'"></td><td><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/"><span>'.$_SESSION['cart']['items'][$item]['name'].'</span></a></td><td>'.$_SESSION['cart']['items'][$item]['price'].'</td><td>'.$_SESSION['cart']['items'][$item]['count'].'</td><td>'.$_SESSION['cart']['items'][$item]['count']*$_SESSION['cart']['items'][$item]['price'].' руб</td></tr></table></div>
	        <div class="row">
	            <a class="button button-primary" href="'.$settings['protocol'].$settings['siteUrl'].'/cart/">Оформить заказ</a>
	            <div class="button" onclick="slowlyDel(\'ACWin\')">Закрыть окно</div>
	        </div>';
            return array(
                /*'cartPrice'=>'<span>'.triada($_SESSION['cart']['price'],2).'</span>руб',*/
                'cartCount'=>'<span>'.$_SESSION['cart']['count'].'</span>',
	            'tvAdded'=>$table
            );
        }
        else {
            return array(
                /*'cartPrice'=>'',*/
                'cartCount'=>''
            );
        }
    }



    // Пересчет количества товаров в корзине и стоимости заказа
    static function cartRecount(){
        if(!empty($_SESSION['cart']['items'])){
            $count=0;
            $price=0;
            $forDel=array();
            foreach($_SESSION['cart']['items'] AS $key=>$val){
                if($val['count']<=0) $forDel[]=$key;
                else {
                    $count=$count+$val['count'];
                    $price=$price+($val['price']*$val['count']);
                }
            }
            // Удаляем все элементы, кол-во которых меньше или равно нулю
            if(!empty($forDel)){
                foreach($forDel AS $val){
                   unset($_SESSION['cart']['items'][$val]);
                }
            }
            if(!empty($_SESSION['cart']['items'])){
                $_SESSION['cart']['count']=$count;
                $_SESSION['cart']['price']=$price;
            }
        }
        else {
            unset($_SESSION['cart']);
        }
    }

    static function filterSend(){
        global $settings;
        $perPage=30;
        $count=0;
        if(isset($_POST['p'])) $p=$_POST['p'];
        if(isset($_POST['entityTypeId'])){
            // Получаем атрибуты сущности
            $array=mysql::getArray("SELECT id, alias, name, type, filterType, source, multiple
            FROM `attributes`
            WHERE entity_type_id=".escape($_POST['entityTypeId'])."
                AND filter!='0'
                AND filterType!='0'
            ORDER BY filterOrder ASC");
            // Формируем запрос на получение списка товаров из фильтра
            if($array!=false){
                $query=array('select'=>array(), 'from'=>array(), 'where'=>array(), 'order'=>array());
                $optionCounter=2;
                $multiCounter=0;
                $query['select'][]="t1.id, t1.art, t1.exist, t1.price, t1.action, t1.brand, t1.photo, t1.discount, t2.name, t2.parent_id, t2.entity_type, t2.date_add, t3.name AS parent_name, t1.rating, t1.votes";
                $query['from'][]="`data_".trim($_POST['categoryAlias'])."` AS t1";
                $query['from'][]="`entity` AS t2 ON t2.id=t1.id";
                $query['from'][]="`entity` AS t3 ON t3.id=t2.parent_id";
                $query['order'][]="t1.id DESC";
                $query['where'][]="t2.parent_id=".escape($_POST['categoryId']);
                $query['where'][]="t2.hidden='0'";

                foreach($array AS $key=>$val){
                	
                    // Если в фильтре есть данный алиас
                    if(isset($_POST['filter'][$val['alias']])){
                    	
                        // 1 - Диапазон ОТ - ДО
                        if($val['filterType']==1){
                            $query['where'][]="t1.`".escape($val['alias'])."` BETWEEN ".(float)$_POST['filter'][$val['alias']][0]." AND ".(float)$_POST['filter'][$val['alias']][1];
                        }

                        // Выбор одного варианта из списка
                        elseif($val['filterType']==5){
                        	if ($_POST['filter'][$val['alias']]!='#') {
                        		$query['where'][]="(t1.`".escape($val['alias'])."`=".$_POST['filter'][$val['alias']].")";
	                        }
                        }
                        
                        // 6 - Множественный выбор из списка
                        elseif($val['filterType']==6){
                            if($val['multiple']==0){
                                $optionCounter++;
                                if($val['source'] != '' ) {
                                    list($srcTable, $fId, $fName, $fWhere)=explode(".",$val['source']);
                                    if($val['alias']=='brand') $query['select'][]="`opt".$optionCounter."`.`".$fName."` AS `".$val['alias']."Name`";
                                    $query['from'][]="`".$srcTable."` AS `opt".$optionCounter."` ON `opt".$optionCounter."`.`".$fId."`=`t1`.`".$val['alias']."`";
                                    $query['where'][]="`t1`.`".$val['alias']."` IN (".implode(", ",$_POST['filter'][$val['alias']]).")";
                                }
                                else {
                                    $query['from'][]="`options` AS `opt".$optionCounter."` ON `opt".$optionCounter."`.`id`=`t1`.`".$val['alias']."`";
                                    $query['where'][]="`t1`.`".$val['alias']."` IN (".implode(", ",$_POST['filter'][$val['alias']]).")";
                                    $query['where'][]="`opt".$optionCounter."`.`attr_id`=".escape($val['id']);
                                }
                            }
                            else{
	                            if($val['source'] == '' ) {
                                    foreach($_POST['filter'][$val['alias']] AS $ov){
                                        $multiCounter++;
                                        $query['from'][]="`multidata` AS `mop".$multiCounter."` ON `mop".$multiCounter."`.`entity_id`=t1.id";
                                        $query['where'][]="`mop".$multiCounter."`.`value_int`=".$ov;
                                    }
                                }
                            }
                        }
                        
                        // CHECKBOX
                        elseif($val['filterType']==7){
                            if($source=='') $query['where'][]="`t1`.`".escape($val['alias'])."`='1'";
                        }
                    }
                }
                
                $order='id';
                if(isset($_SESSION['order'])) $order=$_SESSION['order'];
                
                // Если нужно узнать только количество, то не получаем лишние поля
	            if($_POST['filterAct']=='filterPrepare'){
                	$query['select']=array();
                	$query['select'][]='t1.id';
	            }
	            
                $array=mysql::getArray('SELECT DISTINCTROW SQL_CALC_FOUND_ROWS '.implode(",",$query['select'])." FROM ".implode(" JOIN ",$query['from'])." WHERE ".implode(" AND ",$query['where'])." ORDER BY ".shop::getSqlOrder($order)." LIMIT ".($p*$perPage).",".$perPage);
                $count=mysql::foundRows();
                if($_POST['filterAct']=='filterPrepare'){
                    ajax::domRemove("AXIOMpreFilter");
                    if($count==0) {
                        $winClass='CATnotFound';
                        $winMess='Товары не найдены! Измените критерии поиска';
                        $act='';
                    }
                    else {
                        $winClass='CATfound';
                        $winMess='Найден'.pluralForm($count,',о,о').' <b>'.$count.'</b> товар'.pluralForm($count,',а,ов').'.<span class="button">Показать</span>';
                        $act=' onclick="filterStart(\'cBlock\')"';
                    }
                    ajax::javascript('filterWinRemove()');
                    return array($_POST['prepareDivid']=>'<a id="AXIOMpreFilter" name="fltResult" class="'.$winClass.'"'.$act.'><div>'.$winMess.'</div></a>');
                }
                else {

                    $entIds=array();
                    foreach($array AS $e) $entIds[]=$e['id'];
                    
                    // Получаем размеры всех фоток товаров в массиве
                    if(!empty($entIds)) $GLOBALS['entPhotoSizes']=mysql::getSpecial("SELECT file AS id,imagesize AS name FROM `file` WHERE entity_id IN(".implode(",",$entIds).")");
                    else $GLOBALS['entPhotoSizes']=array();
                    
                    ajax::domRemove("AXIOMpreFilter");
                    $template='<a id="pgnt%1" onClick="filterSetP(%1)">%2</a>';
                    $pgn=paginate($count,$p,$template,$perPage);
                    $out='<div class="row">
                        <div class="sortField">
                            '.self::showOrderList($order).'
                        </div>
                        <div class="pgnField">
                            '.$pgn.'
                        </div>
                    </div>
                    <div class="row">'.self::showTovarList($array,'AJAX').'</div>
                    <div class="row">
                        <div class="pgnField">
                            '.$pgn.'
                        </div>
                    </div>';
                    ajax::javascript('getId("filterAct").value="filterPrepare";');
                    if($_POST['lastItem']!='') ajax::javascript('intoView("item'.$_POST['lastItem'].'")');
                    // Отображаем список товаров
                    return(
                        array(
                        	'pageHeader'=>$array[0]['parent_name'],
	                        'cBlock'=>$out
                        )
                    );
                }
            }
            return array($_POST['prepareDivid']=>'<a id="AXIOMpreFilter" name="fltResult" class="CATfound" onclick="filterStart(\'cBlock\')"><div>Найден'.pluralForm($count,',o,o').' <b>'.$count.'</b> товар'.pluralForm($count,',а,ов').'.<br>Показать...</div></a>');
        }
        return false;
    }

    // Отображение товара
    static function itemShow($it=false){
        $out='';
        global $site, $settings, $breadCrumbs, $item;
        if($it!==false) $item=$it;
        // Получим все атрибуты сущности
        $at=mysql::getArray("SELECT t2.id, t2.alias, t2.element, t2.view, t2.name, t2.multiple, t2.source, t2.unit, t3.entity_type_alias AS entalias, t3.entity_type AS entname
        FROM `entity` AS t1
            JOIN `attributes` AS t2 ON t2.entity_type_id=t1.entity_type
            JOIN `entity_type` AS t3 ON t3.id=t1.entity_type
        WHERE t1.id=".escape($item)."
        ORDER BY t2.backend_order ASC");

        $attr=array();
        $typeAlias=$at[0]['entalias'];

        if(!empty($at)){
            foreach($at AS $val){
                $attr[$val['alias']]=$val;
            }
        }


        if(!empty($attr) && $typeAlias!='category') {
            $oCounter = 0;
            $q = array('select' => array(), 'from' => array(), 'where' => array());
            $q['select'][] = "t1.*, t2.*, t3.*, t4.name AS folderName";
            $q['from'][] = "`data_" . escape($typeAlias) . "` AS t1, `entity` AS t2, `text_" . escape($typeAlias) . "` AS t3, `entity` AS t4";
            $q['where'][] = "t1.id=" . escape($item) . " AND t2.id=t1.id AND t3.id=t1.id AND t4.id=t2.parent_id";

            // Формируем запрос на получение данных
            foreach ($attr AS $key => $val) {
                if ($val['element'] == 'select') {
                    if ($val['multiple'] == 0) {
                        $oCounter++;
                        if ($val['source'] == '') {
                            $q['select'][] = "o" . $oCounter . ".`value` AS `" . $val['alias'] . "`";
                            $q['from'][] = "`options` AS o".$oCounter;
                            $q['where'][] = "t1.`" . $val['alias'] . "`=o" . $oCounter . ".id";
                        } else {
                            list($otn, $ofi, $ofn, $ofw) = explode(".", $val['source']);
                            $q['select'][] = "o" . $oCounter . ".`" . $ofn . "` AS `" . $val['alias'] . "`";
                            $q['from'][] = "`" . $otn . "` AS o".$oCounter;
                            $q['where'][] = "t1.`" . $val['alias'] . "`=o".$oCounter.".`" . $ofi . "`";
                        }
                    }
                }
            }

            // Получаем основные данные сущности
            $product = mysql::getArray("SELECT " . implode(",", $q['select']) . " FROM " . implode(",", $q['from']) . " WHERE " . implode(" AND ", $q['where']) . " LIMIT 1", true);

            // Файлы
            $f = mysql::getArray("SELECT id,attribute_id,file,ext,name,description FROM `file` WHERE entity_id=" . escape($item) . " ORDER BY `entity_id` ASC, `order` ASC");
            if ($f != false) {
                foreach ($f AS $val) {
                    foreach ($attr AS $key => $v) {
                        if ($v['id'] == $val['attribute_id']) {
                            if (!isset($product[$key])) $product[$key] = array();
                            if (!is_array($product[$key])) $product[$key] = array();
                            $product[$key][] = $val;
                        }
                    }
                }
            }

            // Множственные значения
            $md = mysql::getArray("SELECT t2.alias, t2.name, t1.value_varchar AS `char`, value_int AS `int` 
    FROM `multidata` AS t1
        JOIN `attributes` AS t2 ON t2.id=t1.attribute_id
    WHERE t1.entity_id=" . escape($item) . "
    ORDER BY t1.id ASC");
            if ($md != false) {
                foreach ($md AS $key => $val) {
                    if (!isset($product[$val['alias']])) $product[$val['alias']] = array();
                    if (!is_array($product[$val['alias']])) $product[$val['alias']] = array();
                    $tv = onlyDigit($val['int']);
                    if ($val['int'] == '') {
                        $product[$val['alias']][] = $val['char'];
                    } else {
                        $product[$val['alias']][] = $val['int'];
                    }
                }
            }

            if (!empty($attr)) {
                foreach ($attr AS $key => $val) {
                    if ($val['multiple'] == 1 && !empty($product[$key])) {
                        if ($val['element'] == "select" && is_array($product[$key])) {
                            $vals = mysql::getList("SELECT value FROM `options` WHERE id IN(" . implode(",", $product[$key]) . ")");
                            if ($vals != false) {
                                $product[$key] = $vals;
                            }
                        }
                    }
                }
            }


            $specialFields = explode(',', 'id,art,exist,price,increase,brand,photo,files,youtube,discount,parent_id,name,entity_type,hidden,date_add,owner,description,action,new,rek,hot');
            $paramTable = '';
            // Выделим служебные поля от табличных
            foreach ($product AS $key => $val) {
                // Если поля нет в списке специальных, то выводим его в таблицу
                if (!in_array($key, $specialFields)) {
                    if (isset($attr[$key])) {
                        $pub = true;
                        $fName =$attr[$key]['name'];
                        if (is_array($val)) {
                            $fValue = implode('<br>',$val);
                        } else {
                            if ($attr[$key]['element'] == 'checkbox') {
                                if ($val == 1) $val = '<i class="ic-check" style="font-size:16px; color:#14bd46"> </span>';
                                else $val = '<i class="ic-nocheck" style="font-size:16px; color:#ff0000"> </span>';
                            }
                            $fValue =$val;
                            if ($attr[$key]['element'] == 'varchar' && $val == '') $pub = false;
                        }
                        if($fValue=='--НЕТ--' || $fValue=='0' || $fValue=='') $pub=false;
                        if ($attr[$key]['unit'] != '') $fValue.='&nbsp;'.$attr[$key]['unit'];
                        if ($pub == true) $paramTable .= '<tr><td>' . $fName . '</td><td>' . $fValue . '</td></tr>';
                    }
                }
            }
            $miniimages = '';
            $firstimage = '';
            $imgLinks ='';
            $mdPhoto='';
            if (!empty($product['photo'])) {
                foreach ($product['photo'] AS $key=>$val) {
                    if($mdPhoto=='') $mdPhoto=$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($product['id']/100).'/'.$val['file'].'.'.$val['ext'];
                    $miniimages .= '<div class="mim" onMouseOver="setImage(\'' . floor($product['id'] / 100) . '/' . $val['file'] . 's.' . $val['ext'] . '\')" style="background-image:url('.$settings['protocol'] . $settings['siteUrl'] . '/uploaded/' . floor($product['id'] / 100) . '/' . $val['file'] . 's.' . $val['ext'].')"></div>';
                    if ($firstimage == '') {
                        $firstimage = '<a rel="iLoad|'.htmlspecialchars($product['name']).'" id="mainImage" href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($product['id']/100).'/'.$val['file'].'.'.$val['ext'].'"><img src="' . $settings['protocol'] . $settings['siteUrl'] . '/uploaded/' . floor($product['id'] / 100) . '/' . $val['file'] . 's.' . $val['ext'] . '" alt="' . htmlspecialchars($product['name']) . ' фото '.($key+1).'"></a>';
                    }
                    else {
                        $imgLinks.= '<a rel="iLoad|'.htmlspecialchars($product['name']).'" id="mainImage" href="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($product['id']/100).'/'.$val['file'].'.'.$val['ext'].'">Фото '.($key+1).'</a>';
                    }
                }
            }
            else {
                $mdPhoto=$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg';
                $firstimage = '<img src="' . $settings['protocol'] . $settings['siteUrl'] . '/p/nophoto.jpg" alt="' . htmlspecialchars($product['name']) . '">';
            }
            if ($paramTable != '') $paramTable = '
            <table class="paramTable">' . $paramTable . '</table>';

            $tradeBlock = '<div class="row">
            <!-- div class="prCode"><span>Код товара: </span><b>' . $product['origid'] . '</b></div -->
            </div>
            <div class="allPrices">';
            $price = $product['price'];
            if ($product['discount'] < $product['price']) {
                //$increase = $product['increase'] - $product['discount'];
                $oldPrice = $product['price'];
                $price = $product['discount'];
                $tradeBlock .= '<span class="oldPrice">' . $oldPrice . ' Руб.</span>';
            }
            $tradeBlock .= '<div class="productPrice"><b>' . triada($price, 2) . '</b><span>Руб.</span></div>';
            $h=(int)date("H",time());
            if($h<=12) $ndTimeCom='cегодня';
            else {
                $n=(int)date("N",time());
                $ndTimeCom='завтра';
                if($n>=6) $ndTimeCom='в понедельник';
            }

            $dostTime = array(
                'В наличии в магазине' => 'Доставка '.$ndTimeCom,
                'В наличии на складе' => 'Доставка от 2 дней',
                'Под заказ' => 'Варианты доставки уточняйте у менеджера'
            );

            $nalColor = array(
                'В наличии' => '#000000',
                'Нет в наличии' => '#333333',
                'Под заказ' => '#dd0000'
            );
            

            
            // Получим список услуг, доступных при продаже
	        $serv=mysql::getArray("SELECT DISTINCTROW t2.id,t2.price,t2.link,t2.icon,t3.name
	        FROM `multidata` AS t1
	            JOIN `data_service` AS t2 ON t2.id=t1.entity_id
	            JOIN `entity` AS t3 ON t3.id=t1.entity_id
	        WHERE
	            t1.attribute_id=2436
	            AND t2.tgroup=".$product['parent_id']."
	         ");
	
//	        echo '<pre>';
//	        print_r($product);
//            print_r($serv);
//            echo '</pre>';
            $dopServiceForm='';
            if($serv!=false){
            	$dopServiceForm.='<div class="row">
            	<div class="dopServices">
            	<b>Дополнительные услуги:</b>
            	<div class="smaller">
            	Заказывая у нас оборудование вместе с дополнительными услугами вы сэкономите средства и не потеряете фирменную гарантию. Выберите все нужные услуги в списке, а затем нажмите на кнопку "заказать".
            	</div>';
            	$count=0;
            	foreach($serv AS $val){
		            $dopServiceForm.='<div class="servDop">
		            <label for="dopServ'.$count.'"><input type="checkbox" id="dopServ'.$count.'" value="'.$val['id'].'" data-price="'.$val['price'].'" onChange="correctPrice(this)">';
            		//if($val['icon']!='') $dopServiceForm.='<i class="'.$val['icon'].'"></i>';
            		$dopServiceForm.='<span class="servName">'.$val['name'].'</span>
            		<span class="servPrice"> (от '.triada($val['price'],2).' руб)</span>
            		</label></div>';
            		$count++;
	            }
            	$dopServiceForm.='
            	</div>
            	</div>';
            }

            $tradeBlock .= '</div>
            <div class="prCount">
                <div style="display:none;">
                    <input id="pCnt" type="number" value="1" min="1" max="100">
                </div>
                <input type="hidden" id="prodPrice" name="prodPrice" value="'.$price.'">
                '.$dopServiceForm.'
                <div class="button buttonCard" onClick="addToCart('.$product['id'].',getId(\'pCnt\').value)"><i class="ic-cart"></i>Заказать</div>
                
            </div>
            <div class="prOneClick">
                <div class="button" onClick="oneClickForm('.$product['id'].')">Заказать в 1 клик</div>
                <div class="button" onClick="discountQuery('.$product['id'].')">Нашли дешевле?</div>
            </div>
            <div class="prExist">
                <hr>
                <div><i class="ic-sklad" style="color:'.$nalColor[$product['exist']].'"></i><span>' . $product['exist'] . '</span></div>
            </div>';

            $out .= '<div class="row tvMain">
                <div class="imgShow">
                    <div class="imgMiniDiv">
                        <div class="forMini">
                        ' . $miniimages . '
                        </div>
                    </div>
                    <div class="imgMaxiDiv">
                        <div>' . $firstimage . '</div>
                    </div>
                    <div class="tvTrade">
                        ' . $tradeBlock . '
                    </div>
                </div>
                <div class="tvOtherImages">
                '.$imgLinks.'
                </div>
            </div>
            <div class="row bmHeaders">
                <span id="bmh1" onClick="bmSwitch(1)" class="bmHeaderActive">Описание</span>
                <span id="bmh2" onClick="bmSwitch(2)" class="bmHeader">Характеристики</span>
                <span id="bmh3" onClick="bmSwitch(3)" class="bmHeader">Отзывы</span>
            </div>
            <div class="row bmBody" id="bmBody1" style="display:block;">
            <h2>Описание</h2>
            ' . $product['description'];
	        if(!empty($product['youtube'])){
		        $out.='<div class="tube">';
		        foreach($product['youtube'] AS $val){
			        $val=trim($val);
			        $out.='<div class="tubeDiv"><div class="thumb-wrap">';
			        if(strpos($val,'watch?v=')){
				        $z=explode('watch?v=',$val);
				        $out.='<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$z[(count($z)-1)].'" frameborder="0" allowfullscreen></iframe>';
			        }
			        else if(strpos($val,'embed/')){
				        $out.='<iframe width="560" height="315" src="'.$val.'" frameborder="0" allowfullscreen></iframe>';
			        }
			        else {
				        $z=explode("/",$val);
				        $out.='<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$z[(count($z)-1)].'" frameborder="0" allowfullscreen></iframe>';
			        }
			        $out.='</div></div>';
		        }
		        $out.='</div>';
	        }
            
            $out.='</div>';

            $out.='<div class="row bmBody" id="bmBody2" style="display:none;">
            <h2>Характеристики</h2>
            '.$paramTable.'
            </div>
	        <div class="row bmBody" id="bmBody3" style="display:none;">
	            <h2>Отзывы</h2>
	            <div id="revList">
	                '.review::tovarReviews($product['id']).'
	            </div>
	        </div>';
	        $alias=translit($product['name']).'_'.$item;
	        $alias=str_replace('__','_',$alias);
            $breadCrumbs.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/"><span>'.$product['name'].'</span></a></li>';

            if(isset($site)){
                $product['name']=trim($product['name']);
                $site->setName($product['name']);
                $site->setTitle($product['name']. ' - купить');
                $site->setDescription(trim($product['name']).'. Звоните нам, если хотите купить '.$product['name'].' в Орше. Параметры, описание, цена и скидка на '.$product['name'].'.');
                $pprice=$product['price'];
                if($product['discount']<$product['price']) $pprice=$product['discount'];
                $microdata='<div class="microdata">
                
<div itemscope itemtype="https://schema.org/Product">
	<meta itemprop="name" content="'.$product['name'].'" />
	<link itemprop="url" href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/" />
	<link itemprop="image" href="'.$mdPhoto.'" />
	<meta itemprop="brand" content="'.$product['brand'].'" />
	<meta itemprop="model" content="'.$product['art'].'" />
	<meta itemprop="manufacturer" content="'.$product['brand'].'" />
	<meta itemprop="productID" content="'.$product['id'].'" />
	<meta itemprop="category" content="'.$product['folderName'].'" />
	<div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
		<meta itemprop="ratingValue" content="'.$product['rating'].'">
		<meta itemprop="reviewCount" content="'.$product['votes'].'">
	</div>
	<div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
		<meta itemprop="priceCurrency" content="BYN" />
		<meta itemprop="price" content="'.$pprice.'" />
		<link itemprop="availability" href="https://schema.org/InStock" />
	</div>
	<meta itemprop="description" content="'.str_replace("\r\n\r\n","\n",htmlspecialchars(strip_tags($product['description']))).'" />
</div>

</div>';
                return $microdata.$out;
            }
            else {
	            ajax::javascript('changeUrl(document.location.protocol+"//"+document.location.host+"/product/'.translit($product['name']).'_'.$product['id'].'/")');
                return array(
                	'pageHeader'=>$product['name'],
                    'cBlock'=>'<div class="row"><div class="button button-grey" onclick="backToSearchResult('.$product['id'].')"><i class="ic-strleft"></i>&nbsp;&nbsp;Вернуться к результатам поиска</div></div>'.$out
                );
            }
        }
        else {
            // Если товар не найден
            if(isset($site)){
                $site->error404();
            }
        }



    }

    // Отображение списка товаров
    // $array - массив с товарами
    // $count - всего элементов соотв. условиям
    // $path - базовый адрес страницы
    // $p - страница в пагинации
    // $perPage - к-во товаров на страницу
    static function showTovarList($array,$path=''){
        global $settings, $currentAction;
        $exTypes=array(
            27=>'<b>Есть в наличии</b>',
            28=>'<b>Нет в наличии</b>',
            29=>'<b>Под заказ</b>'
        );

        $out='';
        foreach($array AS $val){
            if($val['photo']!='') list($fn,$fe)=explode('.',$val['photo']);
	        $val=shop::tovarCorrectPrice($val);
            $cost='';
            if($val['discount']<$val['price'] && $val['discount']!=0) $cost.='<div class="itOldCost">'.triada($val['price'],2).'</div><div class="itCost"><b>'.triada($val['discount'],2).'</b> р</div>';
            else $cost.='<div class="itCost"><b>'.triada($val['price'],2).'</b> руб.</div>';
	
	        $alias=translit($val['name']).'_'.$val['id'];
	        $alias=str_replace('__','_',$alias);
            $onclick='';
            $a='<a href="'.$settings['protocol'].$settings['siteUrl'].'/product/'.$alias.'/">';
            $aend='</a>';
            if($path==='AJAX'){
                $onclick=' onClick="itemShow('.$val['id'].')"';
                $a='';
                $aend='';
            }

            if($val['photo']!=''){
                $className='';
                if(isset($GLOBALS['entPhotoSizes'][$fn])){
                    list($iw,$ih)=explode("x",$GLOBALS['entPhotoSizes'][$fn]);
                    if($iw<$ih) $className=' class="imVert" ';
                }
                $img='<img'.$onclick.' '.$className.'src="'.$settings['protocol'].$settings['siteUrl'].'/uploaded/'.floor($val['id']/100).'/'.$fn.'s.'.$fe.'" alt="'.htmlspecialchars($val['name']).'">';
            }
            else $img='<img'.$onclick.' src="'.$settings['protocol'].$settings['siteUrl'].'/p/nophoto.jpg" alt="'.htmlspecialchars($val['name']).'">';
            if(!isset($val['rate'])) $val['rate']=0;
            $out.='<div class="iElem" id="item'.$val['id'].'">
    <div class="imgdiv">
        <div class="forimg">
            '.$a.$img.$aend.self::itemSigns($val).'
        </div>
    </div>
    <div class="iDesc">
        '.$a.'<span'.$onclick.'>'.$val['name'].'</span>'.$aend.'
        <div class="idCost">'.$cost.self::showStarRating($val['rating']).'</div>
        '.$a.'<div class="button iToCart"'.$onclick.'><i class="ic-cart"></i>Заказать</div>'.$aend.'
        <div class="button oneClick" onClick="oneClickForm('.$val['id'].')">В 1 клик</div>
    </div>
</div>';
        }
        return '<div class="inItemList"><div class="itemList">'.$out.'</div></div>';
    }
    
    static function showStarRating($rate){
		$out='';
    	// Активная звезда <i class="ic-star ac"></i>
	    for ($i=1; $i<=5; $i++){
	    	if($i<$rate) $className=' ac';
	    	else $className='';
	    	$out.='<i class="ic-starfill'.$className.'"></i>';
	    }
    	return '<div class="isRating" title="Рейтинг: '.number_format($rate,1).'">'.$out.'</div>';
    }

    // Меняем сортировку товаров
    static function changeOrder(){
        global $item;
        $_SESSION['order']=$item;
        ajax::javascript('filterStart("cBlock")');
        return false;
    }

    // Возвращает сортировку для запроса MySQL
    static function getSqlOrder($order){
        $orders=array(
            'id'=>'t1.id DESC',
            'priceDesc'=>'t1.price DESC',
            'priceAsc'=>'t1.price ASC',
            'action'=>'t1.action DESC, t1.id DESC'
        );
        if(isset($orders[$order])) {
            return $orders[$order];
        }
        else return $orders['id'];
    }

    // Получение ORDER BY
    static function showOrderList($order=false){
        $orderNames=array(
            'id'=>'Сначала новые',
            'priceDesc'=>'Сначала дорогие',
            'priceAsc'=>'Сначала дешевые',
            'action'=>'Сначала товары на акции'
        );
        $out='<select id="tovarOrder" onChange="changeOrder()">';
        foreach($orderNames AS $key=>$val){
            $out.='<option ';
            if($order==$key) $out.=' selected="selected" ';
            $out.=' value="'.$key.'">'.$val.'</option>';
        }
        $out.='</select>';
        return $out;
    }

//    static function getOrder(){
//        $orderBy="`t1`.`sort` DESC, `t1`.`id` DESC"; // По умолчанию сортировка по популярности
//        if(isset($_SESSION['tovarOrder'])){
//            $f=$_SESSION['tovarOrder'];
//            if($f=='cost') $orderBy="`t1`.`cost` ASC";
//            elseif($f=='name') $orderBy="`t1`.`name` ASC";
//            elseif($f=='popularity') $orderBy="`t1`.`sort` DESC, `t1`.`id` DESC";
//        }
//        return $orderBy;
//    }


    static function filterPrepare(){
        return false;
    }

}