<?php
if(!session_id()){
    $sessLife=time()+(8*60*60);
    session_set_cookie_params($sessLife);
    session_save_path($_SERVER['DOCUMENT_ROOT'].'/_session');
    session_start();
}
/////////////////////////////////////////////////////////////////////////////////////
// AxiomCMS 
// Version   : 9.3
// Author    : One Man / axiom.genius@gmail.com / ИП Ковалеров А.С. (Минск)
//              Контактный телефон: +37529 631-20-33 
//              По всем вопросам обращаться на представленный выше e-mail
// ----------------------------------------------------------------------------------
// Update    25 июня 2016
//           1) Добавлено управление мета-тегом rel="canonical"
// ----------------------------------------------------------------------------------
// Update    21 мая 2016 
//           1) Исправлена функция включения файлов через сниппеты. Не обрабатывались файлы во вложенных папках
//           !!!!!! TODO !!!!!! Причесать эту функцию - слишком много буков!!!
// ----------------------------------------------------------------------------------
// Update    19 декабря 2015
//           1) Изменен внешний вид страницы в режиме отладки
// Update    2) Теперь не обязательно при включении php файлов через инструкцию вида [[fileName.php]] 
//              чтобы в файле была одноименная функция. При ее отсутствии файл просто исполняется, 
//              а если он имел вывод, то результат отображается в месте включения
// ----------------------------------------------------------------------------------
// Update    05 июня 2015
//           1) В режиме отладки теперь отображаются все используемые запросы SQL
//              а также EXPLAIN по ним и информация об использовании оперативки
// ----------------------------------------------------------------------------------
// Update    28 мая 2015
//           1) Добавлена обработка текстов Типографом и автоматическая расстановка
//              "мягких" переносов строк, если в файле settings включены соответствующие
//              компоненты
// ----------------------------------------------------------------------------------
// Update    25 мая 2015
//           1) Добавлена возможность передачи массивов-списков через кв. скобки
//              в URL вида /variable-[value1,value2,value3]/
// Update	 12 января 2013
// 			 1) Добавлена возможность парсить внешние PHP файлы, содержащие
//              одноименные функции. Для включенния файла PHP надо ввести в код
//              псевдо-сниппет в виде [[fileName.php]]
// ----------------------------------------------------------------------------------
// Update    16 ноября 2013
//           1) Исправлена отдача заголовка 404 для несуществующей страницы
// ----------------------------------------------------------------------------------
// Update    10 ноября 2013
//           1) Существенно переработано ядро. Исправлено множество ошибок
// Update    5  ноября 2013
//           1) Используемые внешними модулями переменные и методы сделаны статичными
//           2) Исправлены ошибки
// ----------------------------------------------------------------------------------
// Update    23 октября 2013
//           1) Исправлены ошибки совместимости с PHP 5.3+
// Update    4 июня 2013
//           1) В модуль DynamicSeo добавлено управление заголовками <h1>
// ----------------------------------------------------------------------------------
// Update    7 апреля 2013
//           1) Добавлена финализация страниц сайта. 
//				При включении финализации генерация 
//              страниц требует +1 доп. запрос к MySQL
//			 	При включении финализации определенного раздела сайта, 
//				вся остальная часть URL адреса будет считаться значениями переменных,
//				Например: 
//					http://www.site.com/page/2013/4/26/ в случае, если страница
//					с псевдонимом 'page' была отмечена в админке как финализированная,
//					то в переменные $site->url['vars'] попадут следующие значения:			
//					[0]=2013;
//					[1]=4;
//					[2]=26;
// ----------------------------------------------------------------------------------
// Update    31 марта 2013
//           1) Добавлен модуль DynamicSEO для управления настройками индексирования
//              динамических страниц. ВНИМАНИЕ! При включении этого модуля генерация 
//              страниц требует +1 доп. запрос к MySQL
// ----------------------------------------------------------------------------------
// Update    8 марта 2013
//           1) Включена система авто-кэширования (MySQL запросы + генератор статики)
// ----------------------------------------------------------------------------------
// Update    16 декабря 2012
//           1) Исправлена ошибка с сессиями
//           2) Исправлена ошибка с буфером обмена
// ----------------------------------------------------------------------------------
// Update    6 декабря 2012
//			 1) Для ясности чанки переименованы в HTML-сниппеты
// ----------------------------------------------------------------------------------
// Update    27 ноября 2012
//           1) Добавлена функция includeScript, для добавления внешних JS
// ----------------------------------------------------------------------------------
// Update    26 ноября 2012
//           1) Исправлена замена Keywords и Description при динамическом добавлении  
//              ключевых слов и описания из модулей
// ----------------------------------------------------------------------------------
// Update    21 ноября 2012
//			 1) Исправлена функция parse_modules()
//				- добавлена unserialize массива параметров 
//				- добавлена буферизация вывода в модулях и 
//                вывод выходного потока в месте отображения модуля на странице
// ----------------------------------------------------------------------------------
// Update    6 ноября 2012
//           1) Добавлено определение переменных в URL вида /?name=value&name2=value2
//           2) Добавлена замена адресов вида src="./" на абсолютный URL
// ----------------------------------------------------------------------------------
define('SECURITY','secureCode');
$error = '';
error_reporting(E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'] . '/_core/mainFunctions.php');
function CLASS_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php')) include_once $_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php';
}
function MDL_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_modules/'.$class.'/'.$class.'.php')) include_once $_SERVER['DOCUMENT_ROOT'].'/_modules/'.$class.'/'.$class.'.php';
}
spl_autoload_register('CLASS_autoloader');
spl_autoload_register('MDL_autoloader');

$site = new site();
$settings = parse_ini_file ($_SERVER['DOCUMENT_ROOT'] . "/_core/settings.ini");
//ini_set ("session.gc_probability", ($settings['sessionGcProbability']));
//ini_set ("session.gc_divisor", ($settings['sessionGcDivisor']));
//ini_set ("session.gc_maxlifetime", ($settings['sessionGcMaxlifetime']));
//ini_set ("session.cookie_lifetime", ($settings['sessionCookieLifetime']));
//ini_set ("session.use_cookies", ($settings['sessionUseCookies']));
//ini_set ("session.use_only_cookies", ($settings['sessionUseOnlyCookies']));
//session_save_path($_SERVER['DOCUMENT_ROOT'] . '/' .$settings['sessionSavePath']);
mysql::connect();
$site->create();

class site
{
    static $settings = '';                  // Настройки сайта
    static $error = '';                     // Накопитель ошибок
    static $criticalErrorFlag = false;      // Флаг критической ошибки, при true будет выход
    static $criticalError = array();        // Накопитель критических ошибок
    static $debug = false;                  // Режим отладки
    static $debugInfo = array();            // При включении режима debug в этот массив попадет
                                            // вся информация по вызову классов и методов
    static $cache = false;                  // ?
    var $blocks = array();                  // Контент модулей
    var $reqlist = false;                   // Список всех присоединяемых файлов
    var $jscripts = array();                // Список Урлов загружаемых JavaScripts
                                            //      Массив вида:
                                            //	    'top'=>'scriptUrl', - вверху страницы (по умолч.)
                                            //	    'bottom'=>'scriptUrl' - внизу страницы
    public static $jstext = array();               // Массив вида:
                                            //      'top'=>'JavaScript code', - включение вверху страницы (внутри <head>)
                                            //	    'bottom=>'JavaScript code' - включение внизу страницы (перед </body>)
    var $css = array();                     // Список URL стилевых таблиц
    public $canonical = false;                 // Адрес канонической страницы
    static $cssText = '';                   // Список кода стилей в <style></style>
    static $lang = 'ru';                    // язык
    public $url = array();                  //
    public $uri = '';                       // Полный URI
    public $path = '';                      // реальный адрес URI (без хоста и переменных)
    public $breadCrumbsEnable = false;      // Включить хлебные крошки
    public $breadCrumbs = '';               // Хлебные крошки
    public $pageId = '';                    // ID текущего раздела сайта
    public $accessLevel = 0;                // Уровень доступа 0-все, 1-зарег. e.t.c.
    protected $hideMainText = false;        // ?
    public $template = 'default';           // Шаблон по умолчанию
    static $timeStart = 0;                  // Время старта генерации скрипта
    static $memoryStart = 0;                // Начальное значение используемой памяти
    static $timeEnd = 0;                    // Время остановки генерации скрипта
    static $messages = array();             // Сюда будут копироваться сообщения ядра [core]
                                            // и модулей [modules][modulname]
                                            // при наличии у модулей языкового файла
    public $page = array();                 // Все части полученной страницы
    public $textLocked = false;             // Если какой либо модуль блокирует основной текст страницы
	public $headerLocked = false;			// Если какой либо модуль блокирует заголовок страницы
    public $mainText = '';                  // Основной текст
    protected $lockText = false;

    // Конструктор класса
    public function __construct($debug = false)
    {
        global $settings;
        site::$timeStart = round((microtime(true)), 3);
        $settings['debug'] = $debug;
        site::$memoryStart = memory_get_usage();
    }

    // Главная функция: генерируем страницу
    public function create()
    {
        global $settings;
        $cache = false;
        $this->parseUrl(); // Парсим URL
        $_GET_COPY = $_GET;
        unset($_GET_COPY['q']);
        // Если страница есть в кэше, то получим ее
        if ($settings['enableCaching'] == 1) {
            if (!empty($_POST) || !empty($_GET_COPY)) $settings['enableCaching'] = 0;
            else $cache = cacheRead('page' . $this->uri);
        }
        if ($cache === false) {

            //$this->addCSS('http://'.$settings['siteUrl'].'style.css'); // Основная таблица стилей
            $this->getLang();           // Получаем язык страницы из URL
            $this->getPage();           // Получаем контент: текст, шаблон, настройки
            $this->tmplSetHead();       // Установка html шапки в шаблон
            $this->parseModules();      // Парсим модули
			$this->parseInclude();      // Парсим файлы PHP, подключенные из контента
            $this->dynamicSEO();        // Получаем метаданные для дин. страницы
            $this->tmplParseVars();     // Парсинг переменных %%name%%
            $this->parseExternal();     // Подключение внешних JavaScript и CSS
            $this->parseIntJs();        // Обработка вложенных в тело страницы скриптов
            if($settings['debug']==true) $this->addStyle('
div.AX_debugBlock{
display:block;
float:none;
border:1px solid #000000;
background:#cccccc;
color:#000000;
font:normal 11px "Arial", sans-serif;
}
div.AX_debugBlockName{
background:#444444;
color:#ffffff;
padding:4px;
font:normal 14px "Arial", sans-serif;
}
ul.AX_debugInfo{
list-style:none;
padding-left:0px;
}
ul.AX_debugInfo li b{
color:#555555;
}
table.AX_debugInfo{
display:table !important;
border:1px solid #000000;
background:#ffffff;
padding:0;
border-collapse:collapse;
float:none;
clear:both;
}
table.AX_debugInfo tr th, table.AX_debugInfo tr td{
border:1px solid #000000;
margin:0;
}
table.AX_debugInfo tr th{
background:#eeeeee;
padding:4px;
font-weight:bold;
}
table.AX_debugInfo tr td{
padding:4px;
}
table.AX_debugInfo tr td:last-child{
text-align:right !important;
}
.error{
padding:16px;
background:#cc0000;
color:#ffffff;
}
');
            $this->parseStyles(); //            Обработка вложенных стилей CSS
            $this->tmplParseBlocks(); //        Парсинг блоков шаблона {{blockName}}
            $this->finalCountdown(); //         Финальная обработка шаблона
            $this->parseSnippet(false); //      Обработка HTML сниппетов
			$this->parseInclude();      // 		Парсим файлы PHP, подключенные из контента (на всякий случай, если подключения были в сниппетах)
			$this->parseSnippet(false); //      Обработка HTML сниппетов
            $this->parseInclude(true);//        Парсим файлы PHP, подключенные из контента
            $this->goBabyGo(); //               Отображение страницы
            // Запуск системы статистики
            if($settings['enableStat']==true){
                if(!isset($_SESSION['statCounter'])) $_SESSION['statCounter']=0;
                else $_SESSION['statCounter']=$_SESSION['statCounter']+1;
                echo 'PAGES IN SESSION='.$_SESSION['statCounter'].'<br>';
                echo 'USER IP='.user::getIp();
            }
        } else echo $cache;
    }

    // Установка ключевых слов
    public function setKeywords($keywords){
        $this->page['keywords']=$keywords;
        return true;
    }
    // Возвращает ключевыес слова
    public function getKeywords(){
        return $this->page['keywords'];
    }

     // Установка meta desc
    public function setDescription($desc){
        $this->page['description']=$desc;
        return true;
    }
    // Возвращает meta desc
    public function getDescription(){
        return $this->page['description'];
    }

    public function setTitle($title){
        $this->page['title']=$title;
        return true;
    }

    public function getTitle(){
        return $this->page['title'];
    }

    public function setName($name){
        if($this->headerLocked===false) $this->page['name']=$name;
        return true;
    }

    public function getName(){
        return $this->page['name'];
    }

    public function getUrlVars($var=false){
        if($var===false) return $this->url['vars'];
        else {
            if(isset($this->url['vars'][$var])) return $this->url['vars'][$var];
            else return false;
        }
    }


    public function setCanonical($url){
        $this->canonical = $url;
    }

    public function getCanonical(){
        return $this->canonical;
    }


    // Печать текста, парсинг адресов
    private function finalCountdown()
    {
        global $settings;
        //$this->page['template'] = str_replace('{{pageHeader}}', $this->page['name'], $this->page['template']);
        if (site::$error != false) $this->page['template'] = str_replace('{{siteError}}', '<div class="error" style="display:block; float:none; clear:both;">' . site::$error . '</div>', $this->page['template']);
        else $this->page['template'] = str_replace('{{siteError}}', '', $this->page['template']);
        if($this->canonical!=false) $this->page['template']=str_ireplace("<head>", "<head><link rel=\"canonical\" href=\"".$this->canonical."\" />", $this->page['template']);
        $this->page['template'] = str_ireplace("<!--AXIOMKeywords-->", htmlspecialchars($this->page['keywords']), $this->page['template']);
        $this->page['template'] = str_ireplace("<!--AXIOMDescription-->", htmlspecialchars($this->page['description']), $this->page['template']);
        $this->page['template'] = str_ireplace("src=\"../", "src=\"".$settings['protocol'].$settings['siteUrl'].'/', $this->page['template']);
        $this->page['template'] = str_ireplace("src=\"./", "src=\"".$settings['protocol'].$settings['siteUrl'].'/', $this->page['template']);
        $this->page['template'] = str_ireplace("src=\"_jscript/", "src=\"".$settings['protocol']. $settings['siteUrl'] . "/_jscript/", $this->page['template']);
        $this->page['template'] = str_ireplace("href=\"_jscript/", "href=\"".$settings['protocol']. $settings['siteUrl'] . "/_jscript/", $this->page['template']);
        if ($this->textLocked == true) $this->mainText = '';
		// Обработка HTML символов для содержимого TITLE
        $this->page['template'] = str_replace("{***AXIOMtext***}", $this->mainText, $this->page['template']);
		$ttl=str_replace('&amp;','&',$this->page['title']);
		$ttl=str_replace('>','&gt;',$ttl);
		$ttl=str_replace('<','&lt;',$ttl);
		$ttl=str_replace('&laquo;','«',$ttl);
		$ttl=str_replace('&raquo;','»',$ttl);
		$ttl=str_replace('&quot;','"',$ttl);
		$this->page['template'] = str_ireplace('{{pageTitle}}', $ttl, $this->page['template']);
    }

    // Финальная обработка шаблона
    private function goBabyGo()
    {
        global $settings;
		$this->page['template']=str_replace('crm.bystar.by', 'bystar.by', $this->page['template']);
		if($settings['protocol']=='https://'){
			$this->page['template']=str_replace('http://'.$settings['siteUrl'], $settings['protocol'].$settings['siteUrl'], $this->page['template']);
		}
		else {
			$this->page['template']=str_replace('http://'.$settings['siteUrl'], $settings['protocol'].$settings['siteUrl'], $this->page['template']);
		}
		$this->page['template'] = str_replace('{{pageHeader}}', $this->page['name'], $this->page['template']);
        site::$timeEnd = microtime(true);
        $start = site::$timeStart;
        $end = site::$timeEnd;
        $gTime = round($end - $start, 4);
		
        // Кэширование
        if ($settings['enableCaching'] == 1) {
			$this->page['template'] = preg_replace ('!\{\{(.*?)\}\}!', '', $this->page['template']);
            cacheSave('page' . $this->uri, str_ireplace('</body>', '
<!-- MySQL queryes: 0-->
<!-- Generation time before caching ' . $gTime . ' -->
<!-- FROM LOCAL CACHE -->
<!-- Memory usage: '.memory_get_peak_usage().'-->
</body>', $this->page['template']));
        }

        $this->page['template'] = str_ireplace('{{siteError}}', site::$error, $this->page['template']);
        $this->page['template'] = str_ireplace('</body>', '<!-- MySQL queryes: ' . mysql::counter(false) . '-->
<!-- Generation time: ' . $gTime . ' -->
<!-- Memory usage: '.memory_get_peak_usage().'-->
</body>', $this->page['template']);

        // Если небыло отправлено HTTP заголовков, то включаем сжатие
		// Очистим пустые переменные шаблона
		$this->page['template'] = preg_replace ('!\{\{(.*?)\}\}!', '', $this->page['template']);
		$this->page['template'] = str_replace("&nbsp;</i>","</i>",$this->page['template']);
		$dbg='';
        if ($settings['debug']==1) {
            $dbg.= '<table class="AX_debugInfo"><tr><th>Time</th><th>Function</th><th>RAM Func.</th><th>RAM ALL</th></tr>'.implode('',site::$debugInfo).'</table>';
            $dbg.= '<ul class="AX_debugInfo">'.implode('</li><li>',mysql::$log).'</ul>';
            $dbg.= '<br><br>EXPLAIN QUERYES:<br>';
            foreach(mysql::$log AS $query){
                $dbg.= '<br>'.$query.'<br>'.mysql::explain($query).'<br>Memory usage: '.memory_get_usage().'<br>';
            }
            $src='<div style="display:table; float:none; clear:both; "><pre style="font-family:monospace; font-size:11px; line-height:12px; background:#ffffff; padding:16px; margin:20px 0; border:2px solid #000000"><div style="float:none; clear:both; margin-bottom:30px; background:#000000; color:#ffffff;">HTML SOURCE</div>'.htmlspecialchars($this->page['template']).'</pre>'.$dbg.'</div>';
			echo $src.$this->page['template'];
        }
		else {
			$this->page['template'] = preg_replace ('!\{\{(.*?)\}\}!', '', $this->page['template']);
			echo $this->page['template'];
		}
        flush();
    }



    // Получаем данные DynamicSEO
    private function dynamicSEO()
    {
        global $site, $settings;
        if ($settings['enableDynamicSeo'] == 1) {
            $array = mysql::getArray("SELECT * FROM `dynamic_seo` WHERE urlhash='" . escape(md5(trim(str_replace('//', '', '/' . $this->uri)))) . "' LIMIT 1", true);
            if ($array != false) foreach ($array AS $aname => $avalue) {
				if($avalue!=''){
					$this->page[$aname] = $avalue;
				}
			}
			if(isset($array['content']) && strlen($array['content'])>=24) {
                $this->mainText='<p>'.$array['content'].'</p>';
                $pageText['text']='<p>'.$array['content'].'</p>';
            }
            if(isset($array['canonical']) && $array['canonical']!=''){
            	$site->setCanonical($array['canonical']);
            }
        }
    }

    // Парсинг блоков шаблона {{block}}
    // Если модули выводили что-то, то вставим результат их работы в шаблон
    private function tmplParseBlocks()
    {
        global $settings;
        if (preg_match_all('!{{(.*?)}}!', $this->page['template'], $l)) {
            if ($l[1] != false) {
                foreach ($l[1] as $val) {
                    if ($val != 'siteError') {
                        if (!isset($this->blocks[$val])) $this->blocks[$val] = ''; // Удаляем пустые
                        if(!empty($this->blocks[$val])) {
                            if($settings['debug']!=true) $this->page['template'] = str_replace('{{' . $val . '}}', implode('', $this->blocks[$val]), $this->page['template']);
                            else $this->page['template']=str_replace('{{'.$val.'}}', '<div class="AX_debugBlock"><div class="AX_debugBlockName">TemplateVar: '.$val.'</div>'.implode('', $this->blocks[$val]).'</div>', $this->page['template']);
                        }
                    } else site::error($settings['core']['error_spTmplVarsWrite']);
                }
            }
        }
        $this->blocks = 'PARSED';
        if ($settings['debug'] == true) site::debug(__METHOD__);
    }

    // Парсим включаемые модули вида [[fileName.php]]
    // Парсим включаемые модули вида [[fileName.php?name=value]]
    public function parseInclude($last=false){
        global $settings;
        $modulesPath=$_SERVER['DOCUMENT_ROOT'].'/_modules/';
        if (preg_match_all ('!\[\[(.*?)\.php(.*?)\]\]!', @$this->page['template'], $l)) if ($l[1] != false) {
            $finded = str_replace(']]','',str_replace('[[','',$l[0]));
        }
        if (isset($finded)) {
            foreach($finded AS $val){
                if($last===false && $val{0}=='#') continue;
                $lPrefix='';
                if($last!==false) {
                    $lPrefix='#';
                    $val=str_replace($lPrefix,'',$val);
                }

                $replacer='[['.$lPrefix.$val.']]';
                list($val,$param)=explode('.php',$val);

                // Получаем параметры, если оные переданы в виде mdl.php?name=value&name2=value2
                $findParams = false;
                if(strlen($param)>=2){
                    $p_rm=parse_url($param);
                    $p_rm=explode('&',$p_rm['query']);
                    foreach($p_rm AS $pv){
                        list($ppn,$ppv)=explode('=',$pv);
                        $findParams[$ppn]=$ppv;
                    }
                }

                $mc='';
                $separator=':';
                if(stripos($val,'/')!==false) {
                    $separator='/';
                    $val=str_replace('/',':',$val);
                }

                @list($m,$s)=explode(':',$val);
                if(isset($s)){
                    $modulFile=$modulesPath.trim($m.'/'.$s).'.php';
                    $funcName=$m.'_'.$s;
                }
                else {
                    $modulFile=$modulesPath.trim($val).'.php';
                    $funcName=$m;
                }
                if($last!=false) $modulFile=str_replace($lPrefix,'',$modulFile);
                if (!file_exists($modulFile)) site::error('File not found "<b>'.$modulFile.'</b>"');
                else {
                    // На всякий случай, закидываем результат в буфер
                    ob_start();
                    include_once $modulFile;
                    $mc.=ob_get_contents();
                    ob_end_clean();
                }
                // В модуле должна существовать главная функция exec()
                if(function_exists($funcName)){
                    ob_start();
                    $out=call_user_func($funcName,$findParams);
                    $out.=ob_get_contents();
                    $this->page['template']=str_replace($replacer,$out,$this->page['template']);
                    ob_end_clean();
                    if ($settings['debug'] == true) site::debug('<b>PHP Module include '.$val.'.php : '.$val.'::Exec()</b>');
                }
                else {
                    if($mc=='') $this->page['template']=str_replace(str_replace(':',$separator,$replacer),'<div class="error">Не найден подключаемый PHP файл &laquo;'.$modulFile.'&raquo;!</div>',$this->page['template']);
                    else $this->page['template']=str_replace(str_replace(':',$separator,$replacer),$mc,$this->page['template']);
                    if ($settings['debug'] == true) site::debug('PHP include <b>'.$replacer.'</b>');
                }


            }
        }
    }


    // Обработка сниппетов
    private function parseSnippet($delEmpty = true)
    {
        global $settings;
        if (preg_match_all('!\[\[(.*?)\]\]!', $this->page['template'], $l)) {
            if ($l[1] != false)
            {
                $array = mysql::getArray("SELECT name,value FROM `snippet` WHERE name IN ('" . implode("', '", $l[1]) . "')");
                if ($array != false) {
                    foreach ($array AS $val) {
                        if ($settings['debug'] == true) {
                            $this->page['template'] = str_replace('[[' . $val['name'] . ']]', '<div class="AX_debugBlock"><div class="AX_debugBlockName">[[' . $val['name'] . ']]</div>' . $val['value'] . '</div>', $this->page['template']);
                            site::debug('Snippet [[' . $val['name'] . ']]');
                        } else $this->page['template'] = str_replace('[[' . $val['name'] . ']]', $val['value'], $this->page['template']);
                    }
                }
            }
        }
        if ($delEmpty != false) $this->page['template'] = preg_replace('!\[\[(.*?)\]\]!', '', $this->page['template']);
    }

    // Встроенные CSS стили
    public function parseStyles()
    {
        global $settings;
        if (isset(site::$cssText[0])) {
            $this->page['template'] = str_ireplace('</head>', '
<style type="text/css"  media="all">
' . implode('
', site::$cssText) . '
</style>
</head>', $this->page['template']);
            if ($settings['debug'] == true) site::debug("Inline CSS Styles is Parsed");
        }
    }

    // Добавление вложенного JavaScript
    public function addScript($script, $top = true)
    {
        global $settings;
		//global $site;
        if ($script) {
            $script = str_ireplace('<script type="text/javascript">', '', $script);
            $script = str_ireplace('</script>', '', $script);
            if ($top == true) site::$jstext['top'][] = $script;
            else site::$jstext['bottom'][] = $script;
            if ($settings['debug'] == true) site::debug(__METHOD__);
            return true;
        }
        return false;
    }

    // Блокировка основного текста страницы
    public function lockText()
    {
        $this->textLocked = true;
        return true;
    }
	
	// Блокировка заголовка страницы
	public function lockHeader(){
		$this->headerLocked = true;
		return true;
	}

    // Добавление внешних CSS таблиц
    public function addCSS($style)
    {
        global $settings;
        if ($style) {
            $this->css[] = $style;
            if ($settings['debug'] == true) site::debug(__METHOD__);
            return true;
        }
        return false;
    }

    public function includeScript($file, $top = true)
    {
        global $settings;
		static $incScripts;
		if(!isset($incScripts)) $incScripts[]='';
        if ($file) {
			if(!in_array($file,$incScripts)){
				if ($settings['debug'] == true) site::debug('Include JavaScript: <b>'.$file.'</b>');
				$incScripts[]=$file;
				if ($top) $this->jscripts['top'][] = $file;
				else $this->jscripts['bottom'][] = $file;
				if ($settings['debug'] == true) site::debug(__METHOD__);
				return true;
			}
        }
        return false;
    }

    // Добавление вложенного стиля CSS
    public function addStyle($style)
    {
        global $settings;
        if ($style) {
            $style = str_ireplace('<style type="text/css">', '', $style);
            $style = str_ireplace('<style>', '', $style);
            $style = str_ireplace('</style>', '', $style);
            site::$cssText[] = $style;
            if ($settings['debug'] == true) site::debug(__METHOD__);
            return true;
        }
        return false;
    }
	
    // Парсинг вложенных JavaScript
    public function parseIntJs()
    {
        global $settings;
		//global $site;
        if (isset(site::$jstext['top'][0])) {
			
            $this->page['template'] = str_ireplace('</head>', '
<script type="text/javascript">
' . implode('
', site::$jstext['top']) . '
</script>
</head>', $this->page['template']);
            if ($settings['debug'] == true) site::debug(__METHOD__);
        }
        if (isset(site::$jstext['bottom'][0])) {
            $this->page['template'] = str_ireplace('</body>', '
<script type="text/javascript">' . implode('
', site::$jstext['bottom']) . '
</script>
</body>', $this->page['template']);
            if ($settings['debug'] == true) site::debug(__METHOD__);
        }
        site::$jstext = 'PARSED';
    }

    // Парсинг списка присоединяемых JS и CSS
    public function parseExternal()
    {
        global $settings;
        // Получим скрипты и стилевые таблицы присоединенные к странице сайта
        //$array = mysql::getArray("SELECT type,file,content,insertTo FROM `resources` WHERE pageid='" . escape($this->page['id']) . "' ORDER BY id ASC");
        //if ($array != false) {
        //    foreach ($array AS $val) {
        //        if ($val['type'] == 'css') $this->addCSS($val['file']);
        //        elseif ($val['type'] == 'js') {
        //            if ($val['insertTo'] == 'top') $top = true;
        //            else $top = false;
        //            $this->includeScript($val['file'], $top);
        //        }
        //    }
        //}
        if (isset($this->css[0])) {
            $this->page['template'] = str_ireplace('</head>', '<!-- StyleSheets -->
<link rel="stylesheet" type="text/css" media="all" href="' . implode('" />
<link rel="stylesheet" type="text/css" media="all" href="', $this->css) . '" />
</head>', $this->page['template']);
            if ($settings['debug'] == true) site::debug(__METHOD__);
        }
        if (isset($this->jscripts['top'][0])) {
            $this->page['template'] = str_ireplace('</head>', '<!-- headScripts --><script type="text/javascript" src="' . implode('"></script><script type="text/javascript" src="', $this->jscripts['top']) . '"></script>
</head>', $this->page['template']);
        }
        if (isset($this->jscripts['bottom'][0])) {
            $this->page['template'] = str_ireplace('</body>', '<!-- BodyScripts --><script type="text/javascript" src="' . implode('"></script><script type="text/javascript" src="', $this->jscripts['bottom']) . '"></script>
</body>', $this->page['template']);
        }
        if ($settings['debug'] == true) site::debug("Including JS & CSS is PARSED");
        $this->jscripts = 'PARSED';
    }

    // Получает из массива список включаемых файлов JS и CSS
    // 0=>array('type'=>'js','url'='jscript.js', ['top']),
    // 1=>array('type'=>'css','url'='style.css');
    public function parseRequired($array)
    {
        global $settings;
        if ($settings['debug'] == true) site::debug(__METHOD__);
        if (is_array($array)) {
            foreach ($array AS $val) {
                if (!isset($this->reqlist[$val[1]])) {
                    $this->reqlist[$val[1]] = 1;
                    if ($val[0] == 'js') {
                        if ($val[2] == 'bottom') $this->jscripts['bottom'][] = $val[1];
                        else $this->jscripts['top'][] = $val[1];
                    } else $_GLOBALS['site']['css'][] = $val[1];
                    if ($settings['debug'] == true) site::debug('parseRequired::' . $val[1]);
                }
            }
        }
    }

    // Получает список динамических модулей страницы  и заполняет переменные шаблона
    // динамическим содержимым
    function parseModules()
    {
        global $settings, $site;
        $modulesPath = $_SERVER['DOCUMENT_ROOT']."/_modules/";
        $modules = mysql::getArray("SELECT id,module,tmplvar,settings FROM `pages_modules` WHERE page_id='" . escape($this->page['id']) . "' AND pause='0' ORDER BY `order` ASC");
        if ($modules != false) {
            foreach ($modules as $key=>$val) {
                @list($modulName,$submodulName)=explode(':',$val['module']);
                if(!isset($submodulName)) {
                    $modulFile=$modulesPath.$modulName.'.php';
                    $functionName=$modulName;
                }
                else {
                    $modulFile=$modulesPath.$modulName.'/'.$submodulName.'.php';
                    $functionName=str_replace(':','_',$val['module']);
                }
                $set=unserialize($val['settings']);
                $s['moduleId']=$val['id'];
                $s['pageId']=$this->page['id'];
                $s['folderId']=$this->page['folder'];
                $s['moduleName']=$val['module'];
                $s['tmplVar']=$val['tmplvar'];
                $set['moduleSettings']=$s;
                
//                echo '<pre>pageText=';
//                print_r($this->blocks[$val['tmplvar']]);
//                echo '</pre>';
				
                if ($modulName == "showText") {
                	if($this->blocks[$val['tmplvar']]=='') {
		                $this->blocks[$val['tmplvar']]=array();
	                }
                    $this->blocks[$val['tmplvar']][] = '{{siteError}}<div style="display:block; float:none; clear:both;">{***AXIOMtext***}</div>';
                    if($this->lockText==false) $this->mainText = $this->postProcessing($this->page['text'],$set);
                    $this->page['text'] = '';
                }
                else if($modulName == "showTextBlock"){
                    ob_start();
                    $out=$site->page[$set['content']];
                    $out.=ob_get_contents();
                    $this->blocks[$val['tmplvar']][]=$this->postProcessing($out,$set);
                    ob_end_clean();
                }
                else {
                    // Простые модули могут лежать в корне
                    //$modulFileOld=$modulesPath.$modulName.".php";
                    // В компонентом модуле должна существовать главная функция exec()
                    if($modulName===$submodulName){
                        if (!file_exists($modulFile)) site::error(site::$messages['module']['error_mdlFileNtFnd'].' "<b>'.$modulFile.'</b>"');
                        else {
                            include_once $modulFile;
                            if(method_exists($modulName, 'exec')){
                                ob_start();
                                $out=call_user_func($modulName.'::exec',$set);
                                $out.=ob_get_contents();
                                $this->blocks[$val['tmplvar']][]=$this->postProcessing($out,$set);
                                ob_end_clean();
                            }
                            else site::error(sprintf(site::$messages['module']['error_noModuleFunc'], $modulName.'::exec()', $modulName));
                        }
                    }
                    else {
                        // Иначе, обрабатываем как простой модуль. А значит в нем
                        // должна быть функция с именем файла
                        if (!file_exists($modulFile)) site::error('Модуль "<b>'.$modulFile.'</b>" не найден');
                        else include_once $modulFile;
                        if(function_exists($functionName)){
                            ob_start();
                            $out=call_user_func($functionName,$set);
                            $out.=ob_get_contents();
                            $this->blocks[$val['tmplvar']][]=$this->postProcessing($out,$set);
                            ob_end_clean();
                        }
                        else site::error(sprintf(site::$messages['module']['error_noModuleFunc'], $functionName.'()', $modulName));
                    }
                    if ($settings['debug'] == true) site::debug('<b>Module '.$modulName.'::exec()</b>');
                }
            }
            if ($settings['debug'] == true) site::debug('parseModules');
            return true;
        }
        else return false;
    }

    // Включение постпроцессинга
    // 1 вставка неразрывных пробелов
    // 2 обработка типографом
    public function postProcessing($content,$mdlSettings){
        global $settings;
        $process=true;
		if(isset($mdlSettings['moduleSettings']['moduleName'])) $cn='<b>'.$mdlSettings['moduleSettings']['moduleName'].'</b>->';
		else $cn='';
        if(isset($mdlSettings['postProcessing'])) $process=$mdlSettings['postProcessing'];
        return $content;
    }

    // Парсинг переменных вида %%name%%
    public function tmplParseVars()
    {
        global $settings;
        if (preg_match_all("!%%(.*?)%%!", $this->page['template'], $l)) {
            if ($l[1] != false) {
                // Заменяем переменные значениями
                foreach ($l[1] AS $val) {
                    if (isset($this->$val)) $this->page['template'] = str_replace('%%' . $val . '%%', $this->$val, $this->page['template']);
                    elseif (isset($settings[$val])) $this->page['template'] = str_replace('%%' . $val . '%%', $settings[$val], $this->page['template']);
                    elseif (isset($this->page[$val])) $this->page['template'] = str_replace('%%' . $val . '%%', $this->page[$val], $this->page['template']);
                }
            }
        }
        if ($settings['debug'] == true) site::debug(__METHOD__);
    }


    // Вставка хедеров и обработка основных переменных шаблона
    public function tmplSetHead()
    {
        global $settings;
        $noind = 'index';
        $nofol = 'follow';
        if ($this->page['noindex'] == 1) $noind = 'noindex';
        if ($this->page['nofollow'] == 1) $nofol = 'nofollow';
        $this->page['template'] = str_ireplace('href="./', 'href="'.$settings['protocol'].$settings['siteUrl'].'/', $this->page['template']);
        //$this->page['template']=str_replace('</head>','</head>',$this->page['template']);
        $this->page['template'] = str_ireplace('<html>', '<!DOCTYPE html>
<html lang="'.site::$lang.'" dir="ltr">', $this->page['template']);
        if (stripos($this->page['template'], '<head>') !== false) {
            $this->page['template'] = str_ireplace('<head>', '<head>
<title>{{pageTitle}}</title>
<base href="'.$settings['protocol'].$settings['siteUrl'].'/">
<meta charset="UTF-8">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="3 days">
<meta name="robots" content="' . $noind . '/' . $nofol . '">
<meta name="keywords" content="<!--AXIOMKeywords-->">
<meta name="description" content="<!--AXIOMDescription-->">
<meta name="generator" content="' . htmlspecialchars($settings['cmsName']) . '">
<meta name="revisit-after" content="5 days">
', $this->page['template']);
        } else site::error(site::$messages['template']['error_noTitle'], 1);
        if (strpos($this->page['template'], '{{pageText}}') !== false) {
            // Проверим, есть ли переменная для отображения ошибки, и если нет - вставим
            if (strpos($this->page['template'], '{{siteError}}') == false) $this->blocks['template'] = str_replace('{{pageText}}', '<div class="AX_error">{{siteError}}</div>{{pageText}}', $this->page['template']);
            if (!isset($this->blocks['pageText'])) $this->blocks['pageText'] = '';
            $this->blocks['template'] = str_replace('{{pageText}}', '<div style="float:none; clear:both;">' . $this->blocks['pageText'] . '</div>', $this->page['template']);
        } else site::error(site::$messages['template']['error_noTextBlock'], 1);

        $this->page['template'] = str_replace('[[siteUrl]]', $settings['siteUrl'], $this->page['template']);
        //if(strlen($this->page['title'])<=4) $this->page['title']=$this->page['name'];
        //$this->page['template'] = str_replace('{{pageTitle}}', $this->page['title'], $this->page['template']);
        if (strpos($this->page['template'], '{{templateCss}}') !== false && isset($this->page['templateCss'])) $this->page['template'] = str_replace('{{templateCss}}', $this->page['templateCss'], $this->page['template']);
        //$this->page['template'] = str_replace('{{pageHeader}}', $this->page['name'], $this->page['template']);
        if ($settings['debug'] == true) site::debug(__METHOD__);
    }

    // Получение текста, шаблона и всех параметров страницы по URL
    private function getPage()
    {
        global $settings;
        $page = new page;
        $array = $page->getByUrl($this->path);
        if ($array != false) {
            $this->page = $array;
            site::$lang = $array['lang'];
        } else {
            site::error($page->error);
            site::error(__METHOD__, true);
            site::error404();
        }
        unset($page); // Удаляем лишние объекты и очищаем память
        unset($array);
        if ($settings['debug'] == true) site::debug(__METHOD__);
    }

    // Переадресация на 404 страниицу в случае если страница не найдена
    public function error404()
    {
        global $settings;
        $errorPage = $settings['protocol'].$settings['siteUrl'].'/404.php';
        header("HTTP/1.0 404 Not Found");
        echo(file_get_contents($errorPage));
        exit();
    }

    // Добавляет ошибку. Если критическая, то поднимается соответствующий флаг
    static function error($message, $critical = false)
    {
        site::$error .= $message . '<br />';
        if ($critical != false)
        {
            site::$criticalError[] = $message . '<br />';
            site::$criticalErrorFlag = true;
        }
        return true;
    }

    // Функция ДЕБАГЕР: регистрирует запускаемые методы и время их запуска
    static function debug($method)
    {
        global $settings;
        static $lastTime;
        static $last;
        static $lastMem;
        $currentMemUsage=memory_get_usage();
        if (!isset($last)) $last = site::$timeStart;
        if (!isset($lastMem)) $lastMem = $currentMemUsage;
        if ($settings['debug'] == true) {
            if (!isset($lastTime)) $lastTime = site::$timeStart;
            $raznitsa = microtime(true) - $last;
            $mRaznitsa = $currentMemUsage - $lastMem;
            if ($mRaznitsa < 0) $mRaznitsa  =0;
            $lastMem=$currentMemUsage;
            if ($raznitsa < 0.0001) $raznitsa = 0.0001;
            site::$debugInfo[] = '<tr><td>'.round($raznitsa, 6) . '</td><td>' . $method .'</td><td>'.triada($mRaznitsa).'</td><td>'.triada($currentMemUsage).'</td></tr>';
            $lastTime = microtime(true);
            $last = $lastTime;
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // Возвращает язык сайта из УРЛ, или язык по умолчанию
    public function getLang()
    {
        global $settings;
        $lang = false;
        $a = explode(',', $settings['siteLangs']);
        foreach ($a AS $langs) {
            list($l, $lname) = explode(':', $langs);
            if ($lang === false) $lang = $l;
            $allangs[$l] = $lname;
            if ($l == $settings['siteDefaultLang']) $lang = $l;
            }
        $m = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($m[1]) && preg_match("/^[a-z]{2}$/", $m[1]) && isset($allangs[$m[1]])) $lang = $m[1];
        if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/_core/' . $lang . '.ini')){
            site::$messages = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/_core/ru.ini', true);
        }
        else site::$messages = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/_core/' . $lang . '.ini', true);
        site::$lang = $lang;
        if ($settings['debug'] == true) site::debug(__METHOD__);
        return true;
    }


    // Return current path
    public function getPath(){
        return trim($this->path);
    }
	
	// eturn page Alias
	public function getAlias(){
		return trim($this->page['alias']);
	}
	
	// eturn page Alias
	public function getFolderAlias(){
		return trim($this->page['folderalias']);
	}

    // Парсинг URL адреса
    //      /aasd/ - Части URL
    //		/a-6/  - переменная - значение
    //		/-6/8/9/10/11/12/
    //      /name-[value1,value2,value3]/ - массивы-списки
    public function parseUrl()
    {
        global $settings;
		list($uri,)=explode("?q=",$_SERVER['REQUEST_URI']);//!!! НЕ ТРОГАТЬ!!! Обработка 301 редиректа
		if(isset($_REQUEST['redirect'])) $uri=$settings['siteUrl'].$_REQUEST['redirect'];
        $this->uri = $uri;
        $pathFinded = false;
        
        if ($settings['enableFinalize'] == 1) {
            $finalized = mysql::getArray("SELECT id,urlhash FROM `pages` WHERE finalize='1'");
            $urlexample = trim($uri);
            if ($finalized != false) {
                $m = explode("/", $urlexample);
                $fragment = '';
                foreach ($m AS $mm) {
                    $fragment .= $mm . "/";
                    $fragment=trim($fragment);
					if($fragment!='/'){
						if(substr($fragment,0,1)=="/"){
							$fragment=substr_replace($fragment,'',0,1);
						}
					}
                    $hash = md5(trim($fragment));
                    if ($pathFinded === false) {
                        foreach ($finalized AS $val) {
                            // Если найдено совпадение!
                            if ($hash == $val['urlhash']) {
                                $this->path = $fragment;
                                $pathFinded = true;
                                break;
                            }
                        }
                    } else {
                        $parse = true;
                        // Извлекаем из УРЛ переменные
                        // переменная - значение
                        if (preg_match("/^.{0,}-.{1,}$/", $mm)) {
                            $parse = false;
                            list($var, $value) = explode('-', $mm);
                            $value = trim(str_ireplace('<script', '', $value));
                            $zz=explode('.',$value);
                            if(count($zz)>=2) $value=$zz;
                            $this->url['vars'][$var] = $value;
                        } elseif (preg_match("/^\?.{0,}=.{1,}$/", $mm)) {
                            $parse = false;
                            $b = explode("&", $mm);
                            foreach ($b AS $v) {
                                list($var, $value) = explode('=', $v);
                                $value = trim(str_ireplace("<script", "", $value));
                                $var = str_ireplace("?", "", $var);
                                $var = str_replace("&", "", $var);
                                $this->url['vars'][$var] = $value;
                            }
                        }
                        if ($parse != false) {
                            if ($mm != '') $this->url['vars'][] = trim($mm);
                        }
                    }
                }
            }
        }
        if ($pathFinded === false) {
            $path = array();
            if ($uri != '/') {
                $a = explode('/', $uri);
                $parse = true;
                foreach ($a AS $key => $val) {
                    if ($val != '') {
                        if ($key == 1 && preg_match("/^.{2}$/", $val)) site::$lang = $val;
                        // переменная - значение
                        elseif (preg_match("/^.{0,}-.{1,}$/", $val)) {
                            $parse = false;
                            list($var, $value) = explode('-', $val);
                            $value=trim(str_ireplace('<script', '', $value));
                            $value=trim(str_ireplace('%3Cscript', '', $value));
                            // Обработаем массивы вида значение1.значение2.значениеN
                            $zz=explode('.',$value);
                            if(count($zz)>=2) $value=$zz;
                            $this->url['vars'][$var] = $value;
                        } elseif (preg_match("/^\?.{0,}=.{1,}$/", $val)) {
                            $parse = false;
                            $b = explode("&", $val);
                            foreach ($b AS $v) {
                                list($var, $value) = explode('=', $v);
                                $value = trim(str_ireplace("<script", "", $value));
                                $var = str_replace("?", "", $var);
                                $var = str_replace("&", "", $var);
                                $this->url['vars'][$var] = $value;
                            }
                        }
                        if ($parse == true) {
                            $this->url['pages'][] = $val;
                            $path[] = $val;
                        }
                    }
                }
            }
            if(isset($path[0])){
                if ($path[0] == $settings['siteDefaultLang']) unset($path[0]);
            }
            $this->path = implode('/', $path) . '/';
        }

        // Получим все массивы


        // Добавляем переменные в массив REQUEST
        if(isset($this->url['vars'])){
            foreach($this->url['vars'] AS $key=>$val){
                if($val{0}=="["){
                    $vval=str_replace("[","",$val);
                    $vval=str_replace("]","",$vval);
                    $val=explode(",",$vval);
                    $this->url['vars'][$key]=$val;
                }
                $_REQUEST[$key]=$val;
            }
        }


        // Если в адресе содержится AXIOMdebug-SECURITY CODE/, принудительно включем режим дебага
        if(isset($this->url['vars']['AXIOMdebug'])){
            if($this->url['vars']['AXIOMdebug']==$settings['securityCode']){
                $settings['debug']= 1;
                site::$debug=1;
                unset($this->url['vars']['AXIOMdebug']);
                $this->uri=str_replace('AXIOMdebug-'.$settings['securityCode'].'/','',$this->uri);
            }
        }
        if ($settings['debug'] == 1) {
            echo 'URL VARIABLES:<pre>';
            print_r($this->url);
            echo '$site->path='.$this->path.'
$site->uri='.$this->uri.'
</pre>';
            site::debug(__METHOD__);
        }
        return true;
    }

}

// Добавление JavaScript в HEAD страницы
// Текст скрипта передается в переменной script
function AXIOMaddStyle($script)
{
    global $site;
    return $site->addStyle($script);
}

// Добавление JavaScript в HEAD страницы
// Текст скрипта передается в переменной script
function AXIOMaddScript($script, $top = true)
{
    global $site;
    return $site->addScript($script, $top);
}

// Добавление внешнего скрипта
function AXIOMincludeScript($url, $top = true)
{
    global $site;
    return $site->includeScript($url, $top);
}
