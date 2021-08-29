<?php
// Раздел администратора. Создание HTML шаблона интерфейса
class admin{
	public $lang='ru';
	public $title='AxiomCMS';
	public $buttons=true;
	public $path='';
	public $content='';
	public $url;
	public $uri='';
	public $js=array();
	public $css=array();
	public $type='ajax';		// Ajax / Static
	public $styles=array();
	public $bodyParams='';		// параметры тега BODY. Например, ' onload="doLoad(document.getElementById('f')); return false;"'
	public $headScripts=array();
	public $bodyScripts=array();
    public $template="default";
	
// Скрипты выполняемые после загрузки
public function addOnload($function){
    $this->onload[]=$function;
}

public function setTemplate($tmpName="default"){
    if($tmpName!="default" && $tmpName!="login") $tmpName="default";
    $this->template=$tmpName;
    return $tmpName;
}

public function __construct($type='static'){
	global $settings;
	if($type=='static') $this->type='static';
	else $this->type='ajax';
	$this->addCss($settings['protocol'].$settings['siteUrl'].'/admin/axstyle.css');
	//$this->addJs($settings['protocol'].$settings['siteUrl'].'/_jscript/jquery.min.js');
	$this->parseUrl();
}

// Возвращает содержимое URL	
public function getUrl(){
	return $this->url;
}

public function setContent($content){
    $this->content=$content;
}
	
public function parseUrl(){
	$uri=$_SERVER['REQUEST_URI'];
	$this->uri=$uri;
	$path=array();
	if($uri!='/'){
		$a=explode('/',$uri);
		foreach($a AS $val){
			if($val!=''){
				if(preg_match("/^.{0,}-.{1,}$/", $val)){
					list($var,$value)=explode('-',$val);
					$this->url['vars'][$var]=trim(str_replace('<script','',$value));
					}
				else {
					if($val!='admin'){
						$this->url['pages'][]=trim(htmlspecialchars($val));
						$path[]=$val;
						}
					}
				}
			}
		}
	$this->path=implode('/',$path).'/';
	return true;
}


public function getUrlVars($varname){
    if(!isset($this->url['vars'][$varname])) return false;
    else return $this->url['vars'][$varname];
}

	
public function getTemplate($content=false,$topBlock=true){
	global $settings;
    $this->addJs($settings['protocol'].$settings['siteUrl'].'/_jscript/jquery.min.js',true);
	$this->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/axiom.js');
    $this->addJs($settings['protocol'].$settings['siteUrl'].'/admin/plugins/ckeditor/ckeditor.js');
    $this->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/Sortable.js');
	//$this->addBodyScript('jQuery(document).ready(function() {jQuery("body").upScrollButton({ upScrollButtonText:"Вверх", heightForButtonAppear:100, scrollTopTime: 300});});');
	$this->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/jquery.tablednd.js');

	$headScripts='';
	$bodyScripts='';
	$stylesheets='';
	$styles='';
    global $CLASS;
	if(isset($this->js['top'][0])){
		foreach($this->js['top'] AS $val) $headScripts.='<script src="'.$val.'"></script>
';
	}
	if(isset($this->js['bottom'][0])){
		foreach($this->js['bottom'] AS $val) $bodyScripts.='<script src="'.$val.'"></script>
';
	}
	if(isset($this->css[0])){
		foreach($this->css AS $val) $stylesheets.='<link rel="stylesheet" media="all" href="'.$val.'" />
';
	}
		
	if(isset($this->styles[0])){
		$styles.='<style type="text/css">';
		foreach($this->styles AS $val) $styles.=$val;
		$styles.='</style>';
	}
		
	if(isset($this->headScripts[0])){
		foreach($this->headScripts AS $val) $headScripts.='<script>'.$val.'</script>';
	}
	if(isset($this->bodyScripts[0])){
		foreach($this->bodyScripts AS $val) $bodyScripts.='<script>'.$val.'</script>';
	}
		
	
global $error;
$OMCMSbBlock='';
$OMCMStBlock='';
$header='';
$contentValign=' valign="middle"';
$mysqlerror=mysql::error();
if($mysqlerror!='') $error.=$mysqlerror;
if($error!='') $error='<div class="error">'.$error.'</div>';
// Если определена переменная topblock, то выводим сверху меню а снизу подвал	
if($topBlock==true) {
	$array=mysql::getArray("SELECT * FROM `modules` WHERE alias!='desktop' ORDER BY `id` ASC");
	$cmenu='';
    foreach($array AS $val){
        if($_SESSION['user']['perms']=='all' || $_SESSION['user']['perms'][$val['alias']]>=1) {
            if($val['alias']!='desktop' && $val['alias']!='metrika') $cmenu.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/admin/'.$val['alias'].'/"><i class="ic '.$val['icon'].'"></i>'.$val['name'].'</a></li>';
        }
    }
	$contentValign=' valign="top"';
	if($this->buttons==true) {
	    $btns='<ul id="nav">
		<li class="one"><a href="'.$settings['protocol'].$settings['siteUrl'].'/admin/desktop/">Рабочий стол</a></li>
		<li class="one"><span>Компоненты</span><ul class="two">'.$cmenu.'</ul></li>';
        $btns.='<li class="one"><a href="'.$settings['protocol'].$settings['siteUrl'].'/admin/tools/">Система</a></li>';
        if(isset($settings['metrikaCounter'])) $btns.='<li class="one"><a href="'.$settings['protocol'].$settings['siteUrl'].'/admin/metrika/">Статистика</a></li>';
        $btns.='<li class="one"><a href="'.$settings['protocol'].$settings['siteUrl'].'/">Выход</a></li>
	</ul>';
    }
	else $btns='&nbsp;';
	$OMCMStBlock='
	<div class="two columns">
	    <img class="AXlogo" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/silverlogo.png" alt="'.$settings['cmsName'].'">
	</div>
	<div class="seven columns">
	    <div id="AXtmenu">'.$btns.'</div>
	</div>
	<div class="three columns">
	    <div id="AXuser" onClick="ajaxGet(\'editUser?='.$_SESSION['user']['id'].'\',\'cblock\',\'users\')"><i class="ic ic-white ic-profile"></i>'.$_SESSION['user']['name'].'</div>
	</div>';
	$OMCMSbBlock='';
    $header='<h1>'.$this->title.'</h1>';
}
else $error='';

$audio='
<div style="display:none;">
    <audio id="AxiomAudio_click" preload="auto">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/click.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/click.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/click.wav" type="audio/wav">
	</audio>
	<audio id="AxiomAudio_notify" preload="auto">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/notify.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/notify.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/notify.wav" type="audio/wav">
	</audio>
	<audio id="AxiomAudio_ring" preload="auto">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/ring.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/ring.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/ring.wav" type="audio/wav">
	</audio>
	<audio id="AxiomAudio_alert" preload="auto">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/alert.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/alert.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/alert.wav" type="audio/wav">
	</audio>
	<audio id="AxiomAudio_sys" preload="auto">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.wav" type="audio/wav">
	</audio>
</div>
';

if($this->template=="default"){
return '<!DOCTYPE html><html lang="ru"><head><title>'.strip_tags($this->title).'</title>
<base href="'.$settings['protocol'].$settings['siteUrl'].'/admin/">
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<link rel="icon" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/favicon.ico" type="image/x-icon">
'.$stylesheets.$styles.$headScripts.'</head>
<body '.$this->bodyParams.'>
<div id="fullpage">
    <div class="row AXdark">
        <div class="container">'.$OMCMStBlock.'</div>
    </div>
    <div class="row">
        <div class="container">
        <div id="AXbreadCrumbs"></div>
        <div id="cblock" style="margin-top:10px;">'.$error.($this->content).'</div>
        <div id="hiddenblock"></div>
        <div id="axiom_HeartBitBlock" style="display:none;"></div>
        <div name="result" id="result"></div>
        <input type="hidden" id="defaultBackend" value="'.$CLASS.'">
        <div id="ajaxWindow" style="visibility:hidden;"></div>
    </div>
</div>
'.$audio.$bodyScripts.'
</body>
</html>';
}
if($this->template=="login"){
    return '<!DOCTYPE html><html><head><title>'.strip_tags($this->title).'</title>
<base href="'.$settings['protocol'].$settings['siteUrl'].'/admin/">
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<link rel="icon" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/img/favicon.ico" type="image/x-icon">
'.$stylesheets.$styles.$headScripts.'</head>
<body '.$this->bodyParams.' class="AXauthBody"><script>
  window.onload = function() {
    getId("userMail").focus();
  }
</script>'.$error.($this->content).'
<input type="hidden" id="dragStatus" name="dragStatus" value="">
<div class="row">
    <div id="ajaxWindow" style="visibility:hidden;"></div>
    <div id="hiddenblock"></div>
    <div id="axiom_HeartBitBlock" style="display:none;"></div>
</div>
'.$audio.$bodyScripts.'
</body>
</html>';
    }
}

// Добавление JavaScript	
public function addJs($file,$top=false){
    static $uploadedScripts=array();
    if(!in_array(crc32($file),$uploadedScripts)){
        if($top==true) $this->js['top'][]=$file;
        else  $this->js['bottom'][]=$file;
    }
	return true;
}
	
public function addHeadScript($script){
    static $uploadedScripts=array();
    if(!in_array(crc32($script),$uploadedScripts)) {
        $this->headScripts[] = '
' . $script . '
';
    }
	return true;
}
	
public function addBodyScript($script){
    static $uploadedScripts=array();
    if(!in_array(crc32($script),$uploadedScripts)) {
        $this->bodyScripts[] = '
' . $script . '
';
    }
	return true;
}
	
// Добавление CSS	
function addCss($file){
	$this->css[]=$file;
	return true;
}
	
// Добавление Inline стилей	
function addStyle($style){
	$this->styles[]=$style;
	return true;
}
}