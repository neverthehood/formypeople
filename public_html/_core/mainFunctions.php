<?php
@session_start();
/////////////////////////////////////////////////////////////
// Главные функции ядра системы
/////////////////////////////////////////////////////////////////////////////
// 13.05.2016 Добавлена функция форматрирования телефонных номеров
// 26.07.2014 Добавлена функция расстановки "мягких" переносов в тексте hyphenWords()
// 11.06.2014 Добавлена функция changeTextToLink() 
// 26.10.2013 Функция pade переименована в pluralForm и изменена
// 23.10.2013 Адаптировано под PHP 5.2+
// 17.02.2011 добавлена функция pade() выдающая слова в правильном падеже
// Добавлена функция triada, разбивающее целые числа на триады
// 3.12.2011 Добавлена функция paginate() создающая список разделов
if(!isset($settings)) {
    $settings=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/_core/settings.ini');
}
if(!isset($messages)) {
	if(isset($site)) $messages=site::$messages;
}

// Если пришли данные, то почистим в них слеши
if (get_magic_quotes_gpc()) {
	strips($_GET); strips($_POST); strips($_COOKIE); strips($_REQUEST);
	if (isset($_SERVER['PHP_AUTH_USER'])) strips($_SERVER['PHP_AUTH_USER']);
	if (isset($_SERVER['PHP_AUTH_PW'])) strips($_SERVER['PHP_AUTH_PW']);
}
	
/**
 * Форматирование телефонного номера
 * по шаблону и маске для замены
 *
 * @param string $phone
 * @param string|array $format
 * @param string $mask
 * @return bool|string
 * Пример использования:   phone_format('1234567','###-##-##');  ->    123-45-67
 * Пример использования:   phone_format('375291234567');  ->    +375(29) 123-45-67
 */
function phone_format($phone, $format='+###&nbsp;(##)&nbsp;###-##-##', $mask = '#'){
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (is_array($format)) {
        if (array_key_exists(strlen($phone), $format)) {
            $format = $format[strlen($phone)];
        } else {
            return false;
        }
    }
    $pattern = '/' . str_repeat('([0-9])?', substr_count($format, $mask)) . '(.*)/';
    $format = preg_replace_callback(
        str_replace('#', $mask, '/([#])/'),
        function () use (&$counter) {
            return '${' . (++$counter) . '}';
        },
        $format
    );
    return ($phone) ? trim(preg_replace($pattern, $format, $phone, 1)) : false;
}

// Аналог оператора !
function invert($value){
    return (int)!$value;
}

//Создание корректного URL из массива данных
function urlCreate($array){
    global $settings;
    $a=array();
    $a[]=$settings['protocol'].$settings['siteUrl'];
    foreach($array AS $val){
        if(!in_array($val,$a)){
            $a[]=trim($val);
        }
    }
    return implode('/',$a).'/';
}

// Возвращает true, если слово на русском языке
function isRussian($str){
	if(preg_match("/^[а-яА-ЯёЁ«»]+$/ui", $str)) return true;
    else return false;
}

function hyphen($str){
	$s=explode(' ',$str);
	$m=array();
	foreach($s AS $v){
		// Обрабатываем только русские слова 
		if(isRussian($v) && mb_strlen($v,'utf-8')>=10){
			$v=hyphenWords($v);
		}
		$m[]=$v;
	}
	return implode(' ',$m);
}

// Получение гиперссылок на странице
function getLinks($html){
  preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $html, $matches);
  if(isset($matches[1])) return $matches[1];
  else return false;
}

// Расстановка "мягких" переносов в HTML или тексте.
// На вход подается текст или HTML , на выходе - текст с расставленными переносами
// Используются правила написанные Дмитрием Котеровым и Ринатом Назибуллиным
function hyphenWords($s){
    if(!function_exists("_hyphen_words")){
        function _hyphen_words(array &$m){
            if (! array_key_exists(3, $m)) return $m[0];
            $s =& $m[0];
            $l = '(?: \xd0[\x90-\xbf\x81]|\xd1[\x80-\x8f\x91]
                    | [a-zA-Z]
                  )';

            /** @noinspection PhpUnusedLocalVariableInspection */
            $l_en = '[a-zA-Z]';
            /** @noinspection PhpUnusedLocalVariableInspection */
            $l_ru = '(?: \xd0[\x90-\xbf\x81]|\xd1[\x80-\x8f\x91]
                     )';
            $v = '(?: \xd0[\xb0\xb5\xb8\xbe]|\xd1[\x83\x8b\x8d\x8e\x8f\x91]
                    | \xd0[\x90\x95\x98\x9e\xa3\xab\xad\xae\xaf\x81]
                    | (?i:[aeiouy])
                  )';
            $c = '(?: \xd0[\xb1-\xb4\xb6\xb7\xba-\xbd\xbf]|\xd1[\x80\x81\x82\x84-\x89]
                    | \xd0[\x91-\x94\x96\x97\x9a-\x9d\x9f-\xa2\xa4-\xa9]
                    | (?i:sh|ch|qu|[bcdfghjklmnpqrstvwxz])
                  )';
            $x = '(?:\xd0[\x99\xaa\xac\xb9]|\xd1[\x8a\x8c])';
            $rules = array(
                "/($x)                    ($c (?:\xcc\x81)? $l)/sx",
                "/($v (?:\xcc\x81)? $c$c) ($c$c$v)/sx",
                "/($v (?:\xcc\x81)? $c$c) ($c$v)/sx",
                "/($v (?:\xcc\x81)? $c)   ($c$c$v)/sx",
                "/($c$v (?:\xcc\x81)? )   ($c$v)/sx",
                "/($v (?:\xcc\x81)? $c)   ($c$v)/sx",
                "/($c$v (?:\xcc\x81)? )   ($v (?:\xcc\x81)? $l)/sx",
            );
            $s = preg_replace($rules, "$1\xc2\xad$2", $s);
            return $s;
        }
    }
    $re_attrs_fast_safe =  '(?> (?>[\x20\r\n\t]+|\xc2\xa0)+
                                (?>
                                                                 [^>"\']+
                                  | (?<=[\=\x20\r\n\t]|\xc2\xa0) "[^"]*"
                                  | (?<=[\=\x20\r\n\t]|\xc2\xa0) \'[^\']*\'
                                  |                              [^>]+
                                )*
                            )?';
    $regexp = '/(?:
                    <([\?\%]) .*? \\1>
                  | <\!\[CDATA\[ .*? \]\]>
                  | <\! (?>--)?
                        \[
                        (?> [^\]"\']+ | "[^"]*" | \'[^\']*\' )*
                        \]
                        (?>--)?
                    >
                  | <\!-- .*? -->
                  | {.*?}
                  | <((?i:noindex|script|style|comment|button|map|iframe|frameset|object|applet))' . $re_attrs_fast_safe . '> .*? <\/(?i:\\2)>
                  | <[\/\!]?[a-zA-Z][a-zA-Z\d]*' . $re_attrs_fast_safe . '\/?>
                  | &(?>
                        (?> [a-zA-Z][a-zA-Z\d]+
                          | \#(?> \d{1,4}
                                | x[\da-fA-F]{2,4}
                              )
                        );
                     )+
                  | ([^<&]{2,})  #3
                )
               /sx';
    return preg_replace_callback($regexp, '_hyphen_words', $s);
}
	
// Преобразование url адресов в тексте в активную ссылку
// @param string $text - входящий текст
// @return string - текст с замененными ссылками
function changeTextToLink($text){
    global $settings;
    $text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"".$settings['protocol']."$3\" >$3</a>", $text);
    $text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);
    return($text);
}
	
// Получение дня недели по дате
// Возвращает порядковый номер дня недели с 1 до 7 по заданной дате
// Аргументы: дата в виде Д.М.ГГ или ДД.ММ.ГГГГ
function weekDayByDate($date){
	$date=explode(".", $date);
	if(count($date)==3) {
		$day=date("w", mktime(0, 0, 0, $date[1], $date[0], $date[2]));
		if($day==0) return 7;
		else return $day;
		}
	return false;
}

// Возвращает реальный размер максимально допустимый для загрузки
function uploadMaxFilesize(){
    //$max=post_max_size        upload_max_filesize,    memory_limit
    $maxUpload      = return_bytes(ini_get('upload_max_filesize'));
    $maxPost        = return_bytes(ini_get('post_max_size'));
    $memLimit       = return_bytes(ini_get('memory_limit'));
    if($maxUpload<$maxPost) $max=$maxUpload;
    else $max=$maxPost;
    if($memLimit<$max) return $memLimit;
    else return $max;
}


// Кэширование
function fileCacheSave($filename, $content){
    $content=serialize($content);
	file::save($filename,$content,0775);
}

// Читает файл из кэша, или возвращает FALSE при его отсутствии или устаревании
function fileCacheRead($filename, $cacheTime=10){
    $cacheTime=$cacheTime*60;
    if(!file_exists($filename)) return false;
    if(time()>(filemtime($filename)+$cacheTime)){
        return false;
    }
    else return(unserialize(file::read($filename)));
}
	
// Получение к-ва дней в месяце заданного года
function daysInMonth($year,$month){
	return date("t", strtotime($year."-".$month));
}
	
// Очистка кэша
function cacheClear(){
	$array=file::listFiles($_SERVER['DOCUMENT_ROOT'].'/_cache/');
	if($array!=false){
		foreach($array AS $val){
			if(!unlink($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$val)) die('Can\'t delete cache file "'.$val.'"!');
		}
	}
	return true;
}

// Загрузка в кэш	
function cacheSave($name, $value){
	$name=md5($name);
	$value=serialize($value);
	file::save($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$name,$value);
	return true;
}
	
// Чтение из кэша
function cacheRead($name){
	$name=md5($name);
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$name)) return(unserialize(file::read($_SERVER['DOCUMENT_ROOT'].'/_cache/'.$name)));
	else return false;
}

function fileBytes($bytes, $precision = 2) {
    $base = log($bytes) / log(1024);
    $suf = array('b','Kb','Mb','Gb','Tb','Pb','Eb','Zb','Yb');
    return round(pow(1024, $base - floor($base)), $precision) . $suf[floor($base)];
}

// Преобразует размер файла вида 30M в число байт		
function return_bytes ($size_str){
    switch (substr ($size_str, -1)){
        case 'M': case 'm': return (int)$size_str * 1048576;
        case 'K': case 'k': return (int)$size_str * 1024;
        case 'G': case 'g': return (int)$size_str * 1073741824;
        default: return $size_str;
	}
}

// Конвертация BR в символ новой строки
function br2nl( $input ) {
	return preg_replace('/<br(\s+)?\/?>/i', "\n", $input);
}

// Определение, является ли текущий запрос AJAX ?
function isAjaxQuery(){
    if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) return true;
    return false;
}


// Возвращает отформатированную строку 
// Передается строка вида: Вася нашел %1 и бросил %2.
function OMCMSsprintf($string){
	$arguments=func_get_args();
	$source=explode(" ",$string);
	foreach($source AS $val){
		if($val!=""){
			$zz=explode('|',$val);
			// Если есть разделитель |
			if(isset($zz[1])){
				$argkey=str_replace("%","",$zz[0]);
				if(isset($arguments[$argkey])) $value=$arguments[$argkey];
				else $value='error';
				$allargs=explode("/",$zz[1]);
				$out[]=$value.' '.pluralForm($value,$allargs[0]);
				}
			else {
				// Если первым символом идет %, значит надо заменить его аргументом
				if($val{0}=="%"){
					$argkey=str_replace("%","",$zz[0]);
					if(isset($arguments[$argkey])) $out[]=$arguments[$argkey];
					}
				else $out[]=$val;
				}
			}
		}
	if(isset($out)) $out=implode(' ',$out);
	else $out='error';
	$out=str_replace(" :",":",$out);
	$out=str_replace(" .",".",$out);
	$out=str_replace(" ,",",",$out);
	return $out;
}

// Кво элементов на страницу
function perPage($perPage=25,$template=false,$name='',$list='18,20,25,40,50,100,200'){
    global $settings;
    $out='';
    // Если шаблон не задан, то получим его из текущего URL
	if($template==false) {
	    if(!isset($settings)) $settings=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/core/settings.ini');
        $template='<a href="'.$settings['protocol'].str_replace('//','/',$settings['siteUrl'].'/'.str_replace('perPage-'.$perPage.'/','',$_SERVER['REQUEST_URI']).'/perPage-%1/">%1</a>');
    }
    if(!is_array($list)) $list=explode(',',$list);
    foreach($list AS $val){
        $class='';
        if($val==$perPage) $class=' class="current"';
        $out.='<li'.$class.'>'.str_replace('%1',$val,$template).'</li>';
    }
    return '<ul class="AXIOMperpage"><li>'.$name.'</li>'.$out.'</ul>';
}

// Функция создает список страниц по заданному шаблону
// переменные: к-во элементов, страница, к-во на страницу, шаблон
function paginate($items, $page, $template = false, $itemsPerPage = false, $compressPagesList = true, $name = '')
{
    global $site;
    global $settings;
    if (!isset($settings)) {
        if (isset($site->settings)) $settings = $site->settings;
    }
    $plink = '';
    // Если шаблон не задан, то получим его из текущего URL
    if ($template == false) $template = '<a href="'.$settings['protocol'].str_replace('//', '/', $settings['siteUrl'].'/'. str_replace('p-' . $page . '/', '', $_SERVER['REQUEST_URI']) . '/p-%1/">%2</a>');
    if ($compressPagesList !== true) $wide = $compressPagesList;
    else $wide = 4;
    // кол-во отображаемых страниц слева и справа от текущей

    if ($itemsPerPage === false) {
        if (isset($settings['itemsPerPage'])) $itemsPerPage = $settings['itemsPerPage'];
        else $itemsPerPage = 10;
    }
    $allPages = ceil($items / $itemsPerPage);
    if ($allPages > 1) {
        $startPage = $page - $wide;
        if ($startPage < 0) $startPage = 0;
        $endPage = $page + $wide + 1;
        if ($endPage > $allPages) $endPage = $allPages;
        if ($startPage >= 1) {
            $pl = str_replace('%1', '0', $template);
            $plink .= '<li>' . str_replace('%2', '...', $pl) . '</li>';
        }
        for ($i = $startPage; $i < $endPage; $i++) {
            if ($page == $i) $plink .= '<li class="current"><span>' . ($i + 1) . '</span></li>';
            else {
                $pl = str_replace('%1', $i, $template);
                $plink .= '<li>' . str_replace('%2', ($i + 1), $pl) . '</li>';
            }
        }
        if ($allPages > $endPage) {
            //$plink.='<b class="pageslink">...</b>';
            $pl = str_replace('%1', ($allPages - 1), $template);
            $plink .= '<li>' . str_replace('%2', '...', $pl) . '</li>';
            //$plink.='<b class="pageslink">...</b>'.$n;
        }
		if($name!='') $name='<li>'.$name.'</li>';
        if ($plink != '') $plink = '<ul class="AXIOMpagination">'.$name.$plink.'</ul>';
    }
    $plink=str_replace('/p-'.$page.'/','/',$plink);
	$plink=str_replace('/p-0/','/',$plink);
    return $plink;
}

// разбивка чисел на триады. второй параметр - к-во знаков после звпятой, которые нужно показать
function triada($val,$float=0){
	$val=trim(number_format($val,$float,"."," "));
	return str_replace(" ","&nbsp;",$val);
}
	
function escape($str){
	return mysqli_real_escape_string(mysql::$mysqli, $str);
}

// Склонение слова в зависимости от числительного
// Пример использования:
// pluralForm(10,"рубль,рубля,рублей");  =>  "10 рублей"
// или pluralForm(10, array("рубль", "рубля", "рублей"))
// $один, $два, $много
/**
 * @param $number число
 * @param $suffix словоформы, например "рубль,рубля,рублей"
 * @return string
 */
function pluralForm($number, $suffix){
    if (!is_array($suffix)) $suffix = explode(",", $suffix);
    $keys = array(2, 0, 1, 1, 1, 2);
    $mod = $number % 100;
    $suffix_key = ($mod > 7 && $mod < 20) ? 2 : $keys[min($mod % 10, 5)];
    return trim($suffix[$suffix_key]);
}

// Транслитерация
function translit($str){
	$tr = array(
		"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
		"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
		"Й"=>"j","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
		"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
		"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
		"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"y","Ь"=>"",
		"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"",
		"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
		" "=>"_", "."=>"", ","=>"_", "/"=>"_", "-"=>"", "A"=>"a",
		"B"=>"b", "C"=>"c", "D"=>"d", "E"=>"e", "F"=>"f", "G"=>"g", 
		"H"=>"h", "I"=>"i", "J"=>"j", "K"=>"k", "L"=>"l", "M"=>"m",
		"N"=>"n", "O"=>"o", "P"=>"p", "Q"=>"q", "R"=>"r", "S"=>"s", 
		"T"=>"t", "U"=>"u", "V"=>"v", "W"=>"w", "X"=>"x", "Y"=>"y", 
		"Z"=>"z", "ё"=>"e", "Ё"=>"e", ";"=>"_", ":"=>"_"
		);
	$string=strtr($str,$tr);
	$string=str_replace("ks","x",$string);
	if (preg_match('/[^A-Za-z0-9_\-]/', $string)) $string=preg_replace('/[^A-Za-z0-9_\-]/', '', $string);
	return $string;
}


// аналог функции mb_substr для получение N символов из строки
if(!function_exists("mb_substr")){
	function mb_substr($str,$offset,$length){
		$str=utf8_win($str);
		$str=substr($str,$offset,$length);
		return win_utf8($str);
	}
}
	
// UTF8 strlen
if(!function_exists("mb_strlen")){
	function mb_strlen($s) {
		$a = preg_split("//u", $s);
		$i = -2;
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($a as $b) $i++;
		return $i;
	}
}
	


/////////////////////////////////////////////////////////////////////////////
// Функция удаляет (заменяет) запрещенные символы из строки
function cleanText($v){
	$v=str_replace("\"","&quot;",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=trim($v);
	return $v;
}


////////////////////
// очистка данных
////////////////////
function cmsClear($v){
	$v=str_replace("\"","&quot;",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=strip_tags($v,"<br>,<ul>,<li>,<b>,<i>,<u>,<a>,<table>,<tr>,<th>,<td>,<span>");
	$v=trim($v);
	return $v;
}

// функция для борьбы с magic_quotes_gpc
function strips(&$el){
	if (is_array($el)) foreach($el as $k=>$v) strips($el[$k]);
	else $el = stripslashes($el);
}
	
// оставляет от строки только цифры и точку как разделитель дробей
function onlyDigit($string){
	$string=preg_replace('/[^\d.]+/', '', $string);
	if($string=='') $string=0;
	return $string;
}
	
// очистка данных от запрещенных символов
function clearVis($v){
	$v=stripslashes($v);
	$v=str_replace("<p></p>","",$v);// Пустые параграфы
	$v=str_replace("valign=\"&quot;top&quot;\"","valign=\"top\"",$v);
	$v=str_replace("valign=\"&quot;middle&quot;\"","valign=\"middle\"",$v);
	$v=str_replace("valign=\"&quot;bottom&quot;\"","valign=\"bottom\"",$v);
	$v=str_replace("align=\"&quot;left&quot;\"","align=\"left\"",$v);
	$v=str_replace("align=\"&quot;right&quot;\"","align=\"right\"",$v);
	$v=str_replace("align=\"&quot;center&quot;\"","align=\"center\"",$v);
	$v=str_replace("align=\"&quot;justify&quot;\"","align=\"justify\"",$v);
	$v=preg_replace('/<meta[^>]*>/s',"",$v);
	$v=str_replace("\"\&quot;","\"",$v);
	$v=str_replace("\&quot;\"","\"",$v);
	$v=str_replace("<meta http-equiv=\"\&quot;Content-Type\&quot;\" charset=\"utf-8\&quot;\" content=\"\&quot;text/html;\" />","",$v);
	//$v=strip_tags($v,'<a><br><b><i><img><p><div><span><strong><hr><vr><blockquote><table><th><tr><td><center><u><ul><li><ol><h1><h2><h3><h4><h5><h6>');
	//$v=preg_replace('/<p[^>]*>/s',"<p>",$v);
	//$v=preg_replace('/<span[^>]*>/s',"<span>",$v);
	//$v=str_replace("<span>&nbsp;</span>","",$v);
	//$v=str_replace("<span> </span>","",$v);
	$v=str_replace("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","&nbsp;",$v);
	$v=str_replace("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","&nbsp;",$v);
	$v=str_replace("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","&nbsp;",$v);
	$v=str_replace("<span></span>","",$v);
	$v=str_replace("windowtext=\"\"","",$v);
	$v=str_replace("solid=\"\"","",$v);
	//$v=str_replace("style=\"\"","",$v);
	$v=str_replace("border-width:=\"\"","",$v);
	$v=str_replace("border-color:=\"\"","",$v);
	$v=str_replace("medium=\"\"","",$v);
	$v=str_replace("padding:=\"\"","",$v);
	$v=str_replace("width:=\"\"","",$v);
	$v=str_replace("<div></div>","",$v);
	$v=str_replace(" none=\"\"","",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=trim($v);
	return $v;
}
	
// очистка данных от запрещенных символов
function clearFull($v){
	$v=strip_tags($v);
	$v=stripslashes($v);
	$v=htmlspecialchars($v);
	$v=str_replace("\"","&quot;",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=str_replace("  "," ",$v);
	$v=strip_tags($v);
	$v=trim($v);
	return $v;
}
	
//  Проверка корректности адреса электронной почты
function checkMail($mail) {
	if (!preg_match("/^[a-z0-9_.-]{1,64}@(([a-z0-9-]+\.)+(com|net|org|mil|". "edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-". "9]{1,3}\.[0-9]{1,3})$/is",$mail)) return false;
	else return true;
}

// Возвращает аттрибут "checked" для заданного чекбокса
function checkboxSelected($value){
	if($value==1) return ' checked="checked"';
	else return '';
}
	
	
// Перекодировка UTF8->WIN
function utf8_win($s){
	$out="";
	$c1="";
	$byte2=false;
	for($c=0;$c<strlen($s);$c++){
		$i=ord($s[$c]);
		if($i<=127) $out.=$s[$c];
		if($byte2){
			$new_c2=($c1&3)*64+($i&63);
			$new_c1=($c1>>2)&5;
			$new_i=$new_c1*256+$new_c2;
			if ($new_i ==1025) $out_i =168;
			else {
				if($new_i==1105) $out_i=184;
				else $out_i=$new_i-848;
			}
			$out.=chr($out_i);
			$byte2=false;
		}
		if(($i>>5)==6){
			$c1 = $i;
			$byte2 = true;
		}
	}
	return $out;
}
		
// Возвращает название месяца по номеру 1-12 на нужном языке
function monthName($num,$pade=true){
	global $messages;
	$pMonths=explode(',',',января,февраля,марта,апреля,мая,июня,июля,августа,сентября,октября,ноября,декабря');
	$months=explode(',',',январь,февраль,март,апрель,май,июнь,июль,август,сентябрь,октябрь,ноябрь,декабрь');
	$num=(int)$num;
	if($pade==true) return $pMonths[$num];
	else return $months[$num];
}

// Генератор полей безопасности для HTML формы
function secureGen($size=5){
	$time=time();
	$out='<input type="hidden" name=security[] value="'.stringEncode($time,$time).'">';
	for($i=0;$i<$size;$i++){
		$out.='<input type="hidden" name=security[] value="'.stringEncode(rand(1,255),$time).'">';
	}
	return $out;
}

function secureCheck (){
	$value=false;
	$return=false;
	if(!isset($_POST['security'])) return false;
	else {
		foreach($_POST['security'] AS $key=>$val){
			if($key==0) $value=stringDecode($val,$val);
			else $value=stringDecode($val,$value);
		}
	}
	return $return;
}

// Кодирование строки
function stringEncode($String, $Password="MyPaSsWoRd")
{
	//Author: Vladimir Kim (www.vkim.ru) 2010
	//Free for use
	$Salt='BGuxLWQtKweKEMV4';
	global $settings;
	if(isset($settings['securityCode'])) $Salt=$settings['securityCode'];
	$String = substr(pack("H*",sha1($String)),0,1).$String;
	$StrLen = strlen($String);
	$Seq = $Password;
	$Gamma = '';
	while (strlen($Gamma)< $StrLen)
	{
		$Seq = pack("H*",sha1($Seq.$Gamma.$Salt));
		$Gamma.=substr($Seq,0,8);
	}
	return base64_encode($String^$Gamma);
}

// Декодирование строки
function stringDecode($String, $Password="MyPaSsWoRd")
{
	//Author: Vladimir Kim (www.vkim.ru) 2010
	//Free for use
	$Salt='BGuxLWQtKweKEMV4';
	global $settings;
	if(isset($settings['securityCode'])) $Salt=$settings['securityCode'];
	$StrLen = strlen($String);
	$Seq = $Password;
	$Gamma = '';
	while (strlen($Gamma)<$StrLen)
	{
		$Seq = pack("H*",sha1($Seq.$Gamma.$Salt));
		$Gamma.=substr($Seq,0,8);
	}
	$String = base64_decode($String);
	$String = $String^$Gamma;
	$DecodedString = substr($String, 1);
	$Error = ord(substr($String, 0, 1) ^ substr(pack("H*",sha1($DecodedString)),0,1));
	//проверяем
	if ($Error) return false;
	else return $DecodedString;
}

//  Возвращает дату в виде 1 января 2008
function showDate($timestamp,$getTime=0,$hideyear=0){
	global $messages;
	if(!isset($messages['PMONTHS'])) $messages['PMONTHS']=',января,февраля,марта,апреля,мая,июня,июля,августа,сентября,октября,ноября,декабря';
	$months=explode(',',$messages['PMONTHS']);
	$day=date("d",$timestamp);
	if($day[0]=="0") $day=$day[1];// Если число месяца началось с ноля, то уберем этот ноль
	$m=date("m",$timestamp);
	if($m{0}=="0") $m=$m{1};// Если число месяца началось с ноля, то уберем этот ноль
	$month=$months[$m];// получили имя месяца
	$year=date("Y",$timestamp)." ";
	$time=date("H:i",$timestamp);
	if($getTime==0) $time="";
	if($hideyear=='1') $year="";
	return $day." ".$month." ".$year.$time;
}
	
//------------------ Функция перекодировки из WIN в UTF --------------------//
function win_utf8($str) {
	if (function_exists('mb_convert_encoding')) return mb_convert_encoding($str, 'utf-8', 'windows-1251');
	if (function_exists('iconv')) return iconv('windows-1251', 'utf-8', $str);
	$win1251utf8 = array("\xC0"=>"А","\xC1"=>"Б","\xC2"=>"В","\xC3"=>"Г","\xC4"=>"Д","\xC5"=>"Е","\xA8"=>"Ё","\xC6"=>"Ж","\xC7"=>"З","\xC8"=>"И","\xC9"=>"Й","\xCA"=>"К","\xCB"=>"Л","\xCC"=>"М", "\xCD"=>"Н","\xCE"=>"О","\xCF"=>"П","\xD0"=>"Р","\xD1"=>"С","\xD2"=>"Т","\xD3"=>"У","\xD4"=>"Ф","\xD5"=>"Х","\xD6"=>"Ц","\xD7"=>"Ч","\xD8"=>"Ш","\xD9"=>"Щ","\xDA"=>"Ъ", "\xDB"=>"Ы","\xDC"=>"Ь","\xDD"=>"Э","\xDE"=>"Ю","\xDF"=>"Я","\xE0"=>"а","\xE1"=>"б","\xE2"=>"в","\xE3"=>"г","\xE4"=>"д","\xE5"=>"е","\xB8"=>"ё","\xE6"=>"ж","\xE7"=>"з", "\xE8"=>"и","\xE9"=>"й","\xEA"=>"к","\xEB"=>"л","\xEC"=>"м","\xED"=>"н","\xEE"=>"о","\xEF"=>"п","\xF0"=>"р","\xF1"=>"с","\xF2"=>"т","\xF3"=>"у","\xF4"=>"ф","\xF5"=>"х", "\xF6"=>"ц","\xF7"=>"ч","\xF8"=>"ш","\xF9"=>"щ","\xFA"=>"ъ","\xFB"=>"ы","\xFC"=>"ь","\xFD"=>"э","\xFE"=>"ю","\xFF"=>"я");
	return strtr($str, $win1251utf8);
}
	
// Преобразование HTML в текст
// Вырезаются все теги, скрипты, лишние пробелы и т.п.
function html2txt($document){
	$search = array ("'<script[^>]*?>.*?</script>'si","'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'",
"'&quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(\d+);'e");
	$replace = array("","","\\1","\"","&","<",">"," ",chr(161),chr(162),chr(163),chr(169),"chr(\\1)");
	return preg_replace($search, $replace, $document);
}



// Накопление или вывод ошибок
function error($message=false){
    static $error=array();
    if($message===false) {
        if(isset($error[0])) return '<div class="error">'.implode('<br>',$error).'</div>';
        else return false;
    }
    else {
        $error[]=$message;
        return true;
    }
}

// Отображение селекта
function createSelect($name,$value,$options,$other=''){
    $out='';
    if(!is_array($options)) $options=explode(',',$options);
    $out.='<select name='.$name.' '.$other.'>';
    foreach($options AS $key=>$val){
        $selected='';
        if(!is_numeric($value) && !is_numeric($key)){
            if($key==$value) $selected=' selected="selected"';
        }
        else {
            if(!is_numeric($value)){ if($val==$value) $selected=' selected="selected"'; }
            else { if($key==$value) $selected=' selected="selected"'; }
        }
        $out.='<option'.$selected.' value="'.$key.'">'.$val.'</option>';
    }
    $out.='</select>';
    return $out;
}

// Продвинутое вырезание тегов		
function strip_tags_smart($s, array $allowable_tags=null, $is_format_spaces = true, array $pair_tags=array('script', 'style', 'map', 'iframe', 'frameset', 'object', 'applet', 'comment', 'button', 'textarea', 'select'), array $para_tags=array('p', 'td', 'th', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'form', 'title', 'pre'))
{
    static $_callback_type  = false;
    static $_allowable_tags = array();
    static $_para_tags      = array();
    static $re_attrs_fast_safe =  '(?![a-zA-Z\d])  #statement, which follows after a tag
                                   #correct attributes
                                   (?>
                                       [^>"\']+
                                     | (?<=[\=\x20\r\n\t]|\xc2\xa0) "[^"]*"
                                     | (?<=[\=\x20\r\n\t]|\xc2\xa0) \'[^\']*\'
                                   )*
                                   #incorrect attributes
                                   [^>]*+';

    if (is_array($s))
    {
        if ($_callback_type === 'strip_tags')
        {
            $tag = strtolower($s[1]);
            if ($_allowable_tags)
            {
                if (array_key_exists($tag, $_allowable_tags)) return $s[0];
                if (array_key_exists('<' . $tag . '>', $_allowable_tags))
                {
                    if (substr($s[0], 0, 2) === '</') return '</' . $tag . '>';
                    if (substr($s[0], -2) === '/>')   return '<' . $tag . ' />';
                    return '<' . $tag . '>';
                }
            }
            if ($tag === 'br') return "\r\n";
            if ($_para_tags && array_key_exists($tag, $_para_tags)) return "\r\n\r\n";
            return '';
        }
        trigger_error('Unknown callback type "' . $_callback_type . '"!', E_USER_ERROR);
    }

    if (($pos = strpos($s, '<')) === false || strpos($s, '>', $pos) === false)
    {
        return $s;
    }
    $length = strlen($s);
    $re_tags = '~  <[/!]?+
                   (
                       [a-zA-Z][a-zA-Z\d]*+
                       (?>:[a-zA-Z][a-zA-Z\d]*+)?
                   ) #1
                   ' . $re_attrs_fast_safe . '
                   >
                ~sxSX';

    $patterns = array(
        '/<([\?\%]) .*? \\1>/sxSX',
        '/<\!\[CDATA\[ .*? \]\]>/sxSX',
        '/<\!--.*?-->/sSX',
        '/ <\! (?:--)?+
               \[
               (?> [^\]"\']+ | "[^"]*" | \'[^\']*\' )*
               \]
               (?:--)?+
           >
         /sxSX',
    );
    if ($pair_tags)
    {
        foreach ($pair_tags as $k => $v) $pair_tags[$k] = preg_quote($v, '/');
        $patterns[] = '/ <((?i:' . implode('|', $pair_tags) . '))' . $re_attrs_fast_safe . '(?<!\/)>
                         .*?
                         <\/(?i:\\1)' . $re_attrs_fast_safe . '>
                       /sxSX';
    }
    $i = 0;
    $max = 99;
    while ($i < $max)
    {
        $s2 = preg_replace($patterns, '', $s);
        if (preg_last_error() !== PREG_NO_ERROR)
        {
            $i = 999;
            break;
        }

        if ($i == 0)
        {
            $is_html = ($s2 != $s || preg_match($re_tags, $s2));
            if (preg_last_error() !== PREG_NO_ERROR)
            {
                $i = 999;
                break;
            }
            if ($is_html)
            {
                if ($is_format_spaces)
                {
                    $s2 = preg_replace('/  [\x09\x0a\x0c\x0d]++
                                         | <((?i:pre|textarea))' . $re_attrs_fast_safe . '(?<!\/)>
                                           .+?
                                           <\/(?i:\\1)' . $re_attrs_fast_safe . '>
                                           \K
                                        /sxSX', ' ', $s2);
                    if (preg_last_error() !== PREG_NO_ERROR)
                    {
                        $i = 999;
                        break;
                    }
                }
                if ($allowable_tags) $_allowable_tags = array_flip($allowable_tags);
                if ($para_tags) $_para_tags = array_flip($para_tags);
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        if ($is_html)
        {
            $_callback_type = 'strip_tags';
            $s2 = preg_replace_callback($re_tags, __FUNCTION__, $s2);
            $_callback_type = false;
            if (preg_last_error() !== PREG_NO_ERROR)
            {
                $i = 999;
                break;
            }
        }
        if ($s === $s2) break;
        $s = $s2; $i++;
    }
    if ($i >= $max) $s = strip_tags($s);
    if ($is_format_spaces && strlen($s) !== $length)
    {
        $s = preg_replace('/\x20\x20++/sSX', ' ', trim($s));
        $s = str_replace(array("\r\n\x20", "\x20\r\n"), "\r\n", $s);
        $s = preg_replace('/[\r\n]{3,}+/sSX', "\r\n\r\n", $s);
    }
    return $s;
}

// Проверка корректности URL адреса.
// Корректным считается URL содержащий протокол
// и валидный адрес
function urlCheck($url) {
   if (!preg_match('#^http\://[\w\-]+\.[\w]+$|^http\://www\.[\w\-]+\.[\w]+$|^https\://[\w\-]+\.[\w]+$|#',$url)) return false;
   // если нет протокала - добавить
   if (!strstr($url,"://")) return false;
   return true;
}

// Отображение иконки ORDER в соответствии с правилами
//function showOrderIcon($order,$number){
//    list($n,$sort)=explode('-',$order);
//    $icon='';
//    if($number==$n) {
//        if($sort=='a') return '<i class="ordersign ic-down"></i>';
//        else return '<i class="ordersign ic-up"></i>';
//    }
//    return $icon;
//}

// Переключатель ORDER
// functionName = Имя функции, 
//   $order = сортировка: a (ASC) или d (DESC),
//   $number = индекс в массиве сортировок,
//   $other = доп. параметры  
//function orderSwitch($functionName,$order,$number,$other=''){
//    list($n,$sort)=explode('-',$order);
//    if($number==$n) {
//        if($sort=='a') $sort='d';
//        else $sort='a';
//    }
//    else $sort='d';
//    return ' onClick="ajaxGet(\''.$functionName.'?='.$other.'&changeOrder='.$number.'-'.$sort.'\',\'cblock\')" ';
//}


// Вывод служебных сообщений
function MESSAGE($class=false,$mess=false){
	global $messages,$uiLang;

	if(!isset($uiLang)) $uiLang='ru';
	if(isset($_SESSION['uiLang'])) $uiLang=$_SESSION['uiLang'];
	if($mess===false) {
        $mess=$class;
        if(!isset($messages[$mess])) return $mess;
        else return $messages[$mess];
    }
    else {
        if(!isset($messages[$class])) {
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.ini')) $messages+=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.ini',true);
        }
        if(!isset($messages[$class][$mess])) return $mess;
        else return $messages[$class][$mess];
    }
}


// Добавление адреса домена и используемого протокола к настройкам
function settingsCorrect(){
	$GLOBALS['settings']['siteUrl']=$_SERVER['SERVER_NAME'];
	$protocol='http://';
	// Protocol
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $protocol = 'https://';
	$GLOBALS['settings']['protocol']=$protocol;
}

