<?php
if(!session_id()){
	$sessLife=time()+(8*60*60);
	session_set_cookie_params($sessLife);
	session_save_path($_SERVER['DOCUMENT_ROOT'].'/_session');
	session_start();
}
if(!isset($_SESSION['user']['active'])) die('access error');
$settings=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/_core/settings.ini');
include_once($_SERVER['DOCUMENT_ROOT'].'/_core/mainFunctions.php');
function CLASS_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php')) include $_SERVER['DOCUMENT_ROOT'].'/_classes/'.$class.'.php';
}
function ADMIN_autoloader($class) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.php')) include $_SERVER['DOCUMENT_ROOT'].'/admin/'.$class.'.php';
}
spl_autoload_register('CLASS_autoloader');
spl_autoload_register('ADMIN_autoloader');

$id=0;
$topFields='';// поля над текстовым редактором
$botFields='';// Поля под текстовым редактором
$file='';
$mode='html';
$type='default';
$filetype=false;
if(isset($_REQUEST['type'])) $type=urldecode($_REQUEST['type']);
if(isset($_REQUEST['filetype'])) $filetype=urldecode($_REQUEST['filetype']);
$postAction='fileSave';
$dopButtons='';
// Если type='default', то идет редактирование файла
if($type=='default'){
    $topMargin=34;
    if(isset($_REQUEST['file'])){
        $file=urldecode($_REQUEST['file']);
        $ext=file::getExtension($file);
        if($ext=='php') $mode='php';
        elseif($ext=='html') $mode='html';
        elseif($ext=='js') $mode='javascript';
        elseif($ext=='css') $mode='css';
        elseif($ext=='ini') $mode='ini';
    }
    $cont=@file_get_contents($file);
    if($cont===false) $cont='';
    $editorHeader=$file;
	$topFields.='<input type="hidden" id="id" name="id" value="'.htmlspecialchars($file).'">';
}
elseif($type=='sfile'){
    $topMargin=34;
    if(isset($_REQUEST['file'])){
        $file=urldecode($_REQUEST['file']);
        list($fn,$ext)=explode(".",$file);
        if($ext=='php') $mode='php';
        elseif($ext=='html') $mode='html';
        elseif($ext=='js') $mode='javascript';
        elseif($ext=='css') $mode='css';
        elseif($ext=='ini') $mode='ini';
		elseif($ext=='xml') {
		    $mode='xml';
		    $ioio=explode("/",$file);
		    if($ioio[(count($ioio)-1)]=='sitemap') $filetype='sitemap';
        }
		elseif($ext=='txt') $mode='txt';
		else $mode='txt';
    }
    $cont=trim(@file_get_contents($file));
    if($cont===false) $cont='';
        if($cont==''){
		// Robots.txt
		if($filetype=='robots') $cont='User-agent: *
Disallow: /_session/
Disallow: /admin/
Disallow: /_modules/
Disallow: /_core/
Disallow: /cgi-bin
Disallow: /_classes/
Disallow: /_jscript/
Disallow: /_cache/
Allow: /
Sitemap: '.$settings['protocol'].$settings['siteUrl'].'/sitemap.xml

User-agent: Yandex
Disallow: /_session/
Disallow: /admin/
Disallow: /_modules/
Disallow: /_core/
Disallow: /cgi-bin
Disallow: /_classes/
Disallow: /_jscript/
Disallow: /_cache/
Allow: /
Host: '.$settings['protocol'].$settings['siteUrl'].'
Sitemap: '.$settings['protocol'].$settings['siteUrl'].'/sitemap.xml
';
		// Sitemap.XML
		elseif($filetype=='sitemap') {
			mysql::connect();
			$array=mysql::getArray("SELECT pages.id, pages.btname, pages.parent, pages.changefreq, folders.default, pages.modify 
			FROM `pages`,`folders` 
			WHERE pages.hidden='0' AND pages.access='0' AND pages.noindex='0' AND folders.id=pages.folder 
			ORDER BY folders.order ASC, pages.order ASC, pages.parent ASC");

			if(!isset($page['changefreq'])) $page['changefreq']=3; 
			$freq=explode(",","always,hourly,daily,weekly,monthly,yearly,never");
			$langs=explode(",",$settings['siteLangs']);
			foreach($langs AS $val){
				list($n,$nn)=explode(":",$val);
				@$l[$n]=$n;
			}
	
			if($array!=false) foreach($array AS $val){
				$changefreq=$freq[$val['changefreq']];
				$priority="0.4";
				if($val['parent']==0) $priority="0.8";
				if($val['changefreq']<=2) $priority="0.6";
				if($val['changefreq']>=4) $priority="0.3";
				if($val['changefreq']>=5) $priority="0.1";
				if($val['default']==1 && $val['parent']==0) $priority="1.0";
				@$cont.="<url>
 <loc>".$settings['protocol'].str_replace('//','/',$settings['siteUrl'].'/'.page::getPath($val['id']))."</loc>
 <lastmod>".str_replace(" ","T",$val['modify'])."+03:00</lastmod>
 <changefreq>".$changefreq."</changefreq>
 <priority>".$priority."</priority>
</url>
";
			}
			if($out!=""){
				$cont=str_replace("//</","/</",$out);
				// Получаем дополнительные sitemap сгенерированные модулями
				$array=mysql::getArray("SELECT * FROM `sitemaps` WHERE 1=1");
				if($array!=false){
					foreach($array AS $val){
						if(strlen($val['value'])>=16) @$cont.=$val['value'];
					}
				}
			}
			$cont="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
".$cont."</urlset>";
			file::save($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml',$cont,0775);
		}
	}
    $editorHeader=$file;
	$topFields.='<input type="hidden" id="id" name="id" value="'.htmlspecialchars($file).'">';
}
elseif($type=='snippet'){
    $topMargin=100;
    $editorHeader='Сниппеты HTML';
    if(isset($_REQUEST['file'])) $file=onlyDigit($_REQUEST['file']);
    else $file=0;
	if(isset($_REQUEST['tools'])) $postAction="snipSave";
    mysql::connect();
    $cont='';
    $id=0;
    $name='';
    $info='';
    $hidden=0;
    if($file>0){
        $s=mysql::getArray("SELECT * FROM `snippet` WHERE id='".escape(onlyDigit($file))."' LIMIT 1",true);
        if($s!=false){
            $cont=$s['value'];
            $id=$s['id'];
			$topFields='<input type="hidden" id="id" name="id" value="'.$id.'">';
            $name=$s['name'];
            $info=$s['info'];
            $hidden=$s['hidden'];
            $editorHeader.=': '.$s['name'];
        }
    }
	else $topFields='<input type="hidden" id="id" name="id" value="0">';
	$topFields.='<input type="hidden" name=hidden value="'.onlyDigit($hidden).'"><table>
        <tr><td><label>&nbsp;'.MESSAGE('NAME').'</label></td><td><input id="name" type="text" style="width:280px;" name=name value="'.htmlspecialchars($name).'" onKeyUp="fieldAutosave(\'name\',\'snippetName()\',0.5)"></td></tr>
        <tr><td><label>&nbsp;'.MESSAGE('DESCRIPTION').'</label></td><td><input type="text" style="width:700px;" name=info value="'.htmlspecialchars($info).'"></td></tr>
        </table>';
}
else if($type=='template'){
    $topMargin=70;
    $editorHeader='Шаблоны HTML';
    if(isset($_REQUEST['file'])) $file=onlyDigit($_REQUEST['file']);
	if(isset($_REQUEST['tools'])) $postAction="templateSave";
    else $file=0;
    mysql::connect();

    $cont='';
    $id=0;
    $name='';
    $info='';
    $hidden=0;
	
    if($file>0){
        $s=mysql::getArray("SELECT * FROM `templates` WHERE id='".escape(onlyDigit($file))."' LIMIT 1",true);
        if($s!=false){
            $cont=$s['content'];
            $id=$s['id'];
			$topFields='<input type="hidden" id="id" name="id" value="'.$id.'">';
            $name=$s['name'];
        }
    }
	else $topFields='<input type="hidden" id="id" name="id" value="0">';
	$topFields.='<input id="name" type="text" style="width:280px;" name=name value="'.htmlspecialchars($name).'" onKeyUp="fieldAutosave(\'name\',\'templateName()\',0.5)">';

}
echo '<!DOCTYPE html>
<html lang="en">
    <head>
	    <meta charset="utf-8">
        <title>'.$file.' Ace codeEditor</title>
        <style type="text/css">
            label, #saved{ font-family:"Arial", sans-serif; font-size:16px; }
			.codeEditor{ width:100%; }
			.aceedit{ position:absolute; }
            #codeEditor{
                margin: 20px 0 0 0;
                position: absolute;
                top: '.$topMargin.'px;
                bottom: 0;
                left: 0;
                right: 0;
			    font:normal 15px/18px "Courier New","Lucida Console","Anonymous Pro","Monaco","Terminus","Courier New",monospace;
            }
            label{
                width:120px !important;
                line-height:22px;
            }
            input[type="text"]{
                font-size:16px;
                line-height:22px;
                padding:0 6px;
                border-radius:3px;
                border:1px solid #dddddd;
            }
            input[type="text"]{-webkit-appearance: none;-moz-appearance: none;appearance: none; }
            input[type="text"]:focus {border: 1px solid #aaaaaa;outline: 0; }
            input[type="text"]{ height:30px; outline: none; box-sizing:border-box; float:left; background:#eeeeee; }
            input[type="text"]{float:left;display:inline-block;margin: 0 6px 0 0;padding: 2px 4px;background: #ffffff;border-radius: 2px;border: 1px solid #cccccc;box-shadow: inset 0 2px 2px rgba(77,77,77,0.1);box-sizing: border-box;width: 300px;}
            input[type="text"]:focus{background-color: #fefeee;}
            
            .error{
                display:inline-block !important;
                padding: 1px 12px 1px 12px !important;
            }
            .row{ margin-bottom:4px; clear:both; }
            .btn{ display:block; box-sizing: border-box; height:30px; line-height:30px; float:left; text-decoration:none; padding:5px 8px 0 8px; border-radius: 4px;
  font:normal 14px "Tahoma","Helvetica", sans-serif; border:1px solid #c9c9c9; color:#000000; background:#f8f8f8; cursor:pointer; margin-right:6px; box-shadow: inset 1px -14px 9px rgba(0,0,0,0.1); text-shadow:1px 1px 2px rgba(255,255,255,0.5);
  -webkit-user-select:none; user-select:none }
            @font-face { font-family:"Axiom16Full"; src: url("'.$settings['protocol'].$settings['siteUrl'].'/fonts/Axiom16Full.eot"); src:  url("'.$settings['protocol'].$settings['siteUrl'].'/fonts/Axiom16Full.eot?#iefix") format("embedded-opentype"), url("'.$settings['protocol'].$settings['siteUrl'].'/fonts/Axiom16Full.ttf") format("truetype"), url("'.$settings['protocol'].$settings['siteUrl'].'/fonts/Axiom16Full.woff") format("woff"); font-weight: normal; font-style: normal;
}
            i {  font-family: "Axiom16Full" !important;  speak: none;  font-style: normal;  font-weight: normal;  font-variant: normal; text-transform: none;  line-height: 1; margin-right:6px;
            /* Better Font Rendering =========== */
            -webkit-font-smoothing: antialiased;  -moz-osx-font-smoothing: grayscale; font-size:16px; }
            .ic-save:before { content: "\e900";}
            .ic-pilcrow:before { content: "\ea73";}
            .ic-return:before { content: "\f051";}
            .ic-next:before { content: "\e9a1";}
            .ic-indent-inc:before { content: "\ea7b";}
            .ic-robot:before { content: "\eaa7";}
            .btn:hover{color:#444444;background:#ffffff;border:1px solid #b6b6b6;text-shadow:1px 1px 2px rgba(255,255,255,0.95);}
            .btn:active{background:#dddddd;box-shadow:none;border:1px solid #999999;}
            
        </style>
		<!-- link rel="stylesheet" type="text/css" href="axstyle.css" -->
		<script src="plugins/ace/ace.js" type="text/javascript" charset="utf-8"></script>
		<script src="'.$settings['protocol'].$settings['siteUrl'].'/admin/js/axiom.js" type="text/javascript"  charset="utf-8"></script>
    </head>
    <body onload="activateAce();">
		<div style="padding: 0 0 4px 0;height: 32px;width: 100%;margin: 0;display: block;">
			<div class="btn" onClick="fileSave()"><i class="ic-save"></i>Сохранить</div>
			<div class="btn" onClick="showInvis()" title="Отображать скрытые символы"><i class="ic-pilcrow" style="margin-right:0;"></i></div>
			<div class="btn" onClick="codeEditor.toggleCommentLines()" title="Comment/Uncomment selection">//</div>
			<div class="btn" onClick="codeEditor.undo()" title="Отмена"><i class="ic-return" style="margin-right:0;"></i></div>
			<div class="btn" onClick="codeEditor.redo()" title="Вернуть"><i class="ic-next" style="margin-right:0;"></i></div>
			<div class="btn" onClick="codeEditor.alignCursors()" title="Выровнять"><i class="ic-indent-inc" style="margin-right:0;"></i></div>
			'.$dopButtons.'
			<div id="saved" style="display:inline; line-height:28px; color:#dd0000;"></div>
		</div>
		<div class="row">
            <form method="POST" id="contentForm">
                <input type="hidden" id="type" name="type" value="'.htmlspecialchars($type).'">
                '.$topFields.'
                <input type="hidden" name="action" value="'.$postAction.'">
                <input style="display:none;" type="text" name="save[file]" value="'.htmlspecialchars($file).'">
                '.$botFields.'
                <textarea id="code" style="display:none;" name="save[content]">'.htmlspecialchars($cont).'</textarea>
            </form>
		</div>
	    <div class="codeEditor">
        <pre id="codeEditor" class="aceedit">'.htmlspecialchars($cont).'</pre>
</div>


        <script>
function controlId(){
    if(getId("newId")) {
		var m=parseInt(getId("newId").innerHTML);
		if(m>0){
			if(getId("id").value=="0") {
				setValue("id", getId("newId").innerHTML);
			}
		}
	}
}
setInterval(controlId, 100);

var codeEditorChanged=false;
var showInvisibles=false;
function fileSave(){
	document.getElementById("code").innerHTML=codeEditor.getValue();
	ajaxPost("contentForm","tools::fileSave");
}
// Проверка сниппета на существование
function snippetName(){
    var name=getId("name").value;
    var id=getId("id").value;
    ajaxGet(\'tools::snipNameValid?=\'+id+\'&name=\'+urlencode(name));
}


// Change show invisible characters
function showInvis(){
	if(showInvisibles==false){
		codeEditor.setShowInvisibles(true);
		showInvisibles=true;
	}
	else {
		codeEditor.setShowInvisibles(false);
		showInvisibles=false;
	}
}
function activateAce(){
	parent.getId("AxiomWinName").innerHTML="'.$editorHeader.'<i class=\"AxiomWinCloseBtn ic12 ic-close\" onclick=\"dClose(\'AxiomWinDialog\')\"></i>";
            codeEditor = ace.edit("codeEditor"); 
			codeEditor.setTheme("ace/theme/twilight");// dark
			codeEditor.getSession().setMode("ace/mode/'.$mode.'");
			codeEditor.getSession().setTabSize(4);
			codeEditor.focus();// ставим фокус
			// Получение к-ва строк и установка курсора в конце текста
			// session = codeEditor.getSession();
			// count = session.getLength();
			// codeEditor.gotoLine(count, session.getLine(count-1).length);
			//codeEditor.setShowFoldWidgets(true);
			codeEditor.getSession().setUseSoftTabs(true); // использования "мягкого" выравнивания Tab-ами
			codeEditor.getSession().setUseWrapMode(true); // включаем text wrapping
			codeEditor.getSession().setNewLineMode("unix");// windows/unix/auto
			codeEditor.setShowPrintMargin(false); // полоска-граница (40, 80 или свободно число символов)
			//codeEditor.setOptions({ maxLines: Infinity }); // Высота редактора: Infinity или к-во строк 
			codeEditor.setShowInvisibles(showInvisibles);
			codeEditor.getSession().on(\'change\', function(e) {
					codeEditorChanged=true;
			});
			// My command
			codeEditor.commands.addCommand({
					name: \'myCommand\',
					bindKey: {win: \'Ctrl-S\',  mac: \'Command-S\'},
					exec: function(codeEditor) {
						fileSave();
					},
					readOnly: true // false if this command should not apply in readOnly mode
			});
			
		}
</script>
	<div id="hiddenblock" style="display:none;"></div>
<div style="display:none;">
	<audio id="AxiomAudio_sys">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.ogg" type="audio/ogg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.mp3" type="audio/mpeg">
		<source src="'.$settings['protocol'].$settings['siteUrl'].'/p/sys.wav" type="audio/wav">
	</audio>
</div>
    </body>
</html>';