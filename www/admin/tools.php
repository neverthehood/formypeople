<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";

class tools {
	

    public static $delTmpConfirm=true; // Подтверждение удаления шаблона
	
	static function nofollow(){
		global $item;
		mysql::query("UPDATE `dynamic_seo` SET nofollow='".escape(invert(mysql::getValue("SELECT nofollow FROM `dynamic_seo` WHERE id='".escape($item)."' LIMIT 1")))."' WHERE id='".escape($item)."' LIMIT 1");
        ajax::sound("click");
        return false;
	}
	static function noindex(){
		global $item;
		mysql::query("UPDATE `dynamic_seo` SET noindex='".escape(invert(mysql::getValue("SELECT noindex FROM `dynamic_seo` WHERE id='".escape($item)."' LIMIT 1")))."' WHERE id='".escape($item)."' LIMIT 1");
		ajax::sound("click");
		return false;
	}
	
	static function dsSave(){
		$error='';
		global $p, $settings, $np;
		if(strlen($_POST['seo']['url'])<=6) $error.='Слишком короткий URL<br>';
		if($error==""){
			$seo=$_POST['seo'];

			$url=str_replace("www.","",$seo['url']);
			$url=str_replace("http://","",$url);
			$url=str_replace("https://","",$url);
			$url=str_replace($settings['siteUrl'].'/',"",$url);
			$url=trim(str_replace('//', '', '/' . trim($url)));
		    if($url{0}=='/') $url=substr($url, 1);

			$urlhash=md5($url);

			if(isset($_POST['p'])) $p=$_POST['p'];

			$curId=mysql::getValue("SELECT id FROM `dynamic_seo` WHERE urlhash='".escape($urlhash)."' LIMIT 1");
			if($curId!=false) $seo['id']=$curId;
			if($curId==0) $p=0;

			$seo['title']=str_replace('&amp;','&',$seo['title']);
			$seo['title']=str_replace('&quot;','"',$seo['title']);
			if($seo['id']==0 || $seo['id']=='') {
				$np=true;
				$seo['id']=0;
			}
			else $np=false;

			mysql::query("INSERT INTO `dynamic_seo` 
				SET id='".escape($seo['id'])."', noindex='".escape($seo['noindex'])."', nofollow='".escape($seo['nofollow'])."', url='".escape($url)."', urlhash='".escape($urlhash)."', title='".escape($seo['title'])."', header='".$seo['header']."', keywords='".escape($seo['keywords'])."', `description`='".escape($seo['description'])."', canonical='".escape($seo['canonical'])."'   
			ON DUPLICATE KEY UPDATE 
				noindex='".escape($seo['noindex'])."', nofollow='".escape($seo['nofollow'])."',  url='".escape($url)."', urlhash='".escape($urlhash)."', title='".escape($seo['title'])."', header='".escape($seo['header'])."', keywords='".escape($seo['keywords'])."',  `description`='".escape($seo['description'])."', canonical='".escape($seo['canonical'])."'");
			return tools::dynSEO();
			}
		else {
			ajax::dialogAlert('<div class="error">'.$error.'</div>');
            return false;
		}
	}

	// Выполнение и тестирование MySQL запросов
	static function queryTest(){
	    $out='';
		$out.='<div class="field"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li>
		<li><span>Тестирование MySQL запросов</span></li></ul></div>';

		$out.='<div class="field">
		<table style="width:100%">
		<tr>
		    <td style="width:800px; min-height:600px;" valign="top">
		        <textarea name="query" style="width:100%; height:100%; min-height:600px; font-size:16px; line-height:24px; font-weight:bold; font-family:monospace;">SELECT *
FROM `catalog_entity`
WHERE id=16
AND name=26
LIMIT 100</textarea>
		    </td>
		    <td style="width:500px;" valign="top">
		    </td>
		</tr>
		</table>
		</div>';


		return $out;
	}
	
	// Удаление URL из Dynamic SEO
	static function delUrl(){
		global $item;
		//global $p;
		mysql::query("DELETE FROM `dynamic_seo` WHERE id='".escape($item)."' LIMIT 1");
		//return tools::dynSeo();
	}
	
	// Добавление URL для DynamicSEO
	static function dsEdit(){
		$out='';
		$out.='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li>
		<li><span onClick="ajaxGet(\'tools::dynSEO?=\')">DynamicSEO</span></li><li><span>Редактирование URL</span></li></ul></div>';
		
		$seo['id']=0;
		$seo['noindex']=0;
		$seo['nofollow']=0;
		$seo['url']='';
		$seo['title']='';
		$seo['header']='';
		$seo['keywords']='';
		$seo['description']='';
		$seo['canonical']='';
		$seo['content']='';
		
		global $p;
		global $item;

		if(!isset($p)){
            if(isset($_POST['p'])) $p=$_POST['p'];
            if($seo['id']==0) $p=0;
        }
		
		if(!isset($_POST['seo']['noindex']) && $item!=0){
			$seo=mysql::getArray("SELECT * FROM `dynamic_seo` WHERE id='".escape($item)."' LIMIT 1",true);
			//$seo['url']=$seo['url'];
			}
		else $seo=$_POST['seo'];
	
		$fields=array(
		array('type'=>'html', 'value'=>'<tr><td style="width:120px;">&nbsp;</td><td>&nbsp</td></tr>'),
		array('type'=>'text', 'class'=>'size-xxl', 'id'=>'url', 'label'=>'URL адрес', 'name'=>'seo[url]', 'value'=>htmlspecialchars($seo['url']), 'maxlength'=>255),
		array('type'=>'hidden','name'=>'seo[id]', 'value'=>htmlspecialchars($seo['id'])),
		array('type'=>'hidden','name'=>'p', 'value'=>htmlspecialchars($p)),
		array('type'=>'checkbox','id'=>'noindex', 'class'=>'checkbox', 'label'=>'NOINDEX', 'name'=>'seo[noindex]', 'value'=>htmlspecialchars($seo['noindex'])),
		array('type'=>'checkbox','id'=>'nofollow', 'class'=>'checkbox', 'label'=>'NOFOLLOW', 'name'=>'seo[nofollow]', 'value'=>htmlspecialchars($seo['nofollow'])),
		array('type'=>'text', 'class'=>'size-xxl', 'id'=>'header', 'label'=>'H1', 'name'=>'seo[header]', 'value'=>htmlspecialchars($seo['header']), 'maxlength'=>255),
		array('type'=>'text', 'class'=>'size-xxl', 'id'=>'title', 'label'=>'TITLE', 'name'=>'seo[title]', 'value'=>htmlspecialchars($seo['title']), 'maxlength'=>255),
		array('type'=>'textarea', 'class'=>'size-xxl', 'id'=>'keywords', 'label'=>'Ключевые слова<br>META keywords', 'name'=>'seo[keywords]', 'value'=>htmlspecialchars($seo['keywords']), 'maxlength'=>1000, 'counter'=>true, 'style'=>'width:700px; height:90px;'),
		array('type'=>'textarea', 'class'=>'size-xxl', 'id'=>'description', 'label'=>'Описание страницы<br>META description', 'name'=>'seo[description]', 'value'=>htmlspecialchars($seo['description']), 'maxlength'=>200, 'counter'=>true, 'style'=>'width:700px;'),
		//array('type'=>'textarea', 'class'=>'input', 'id'=>'cont', 'label'=>'Текст страницы', 'name'=>'seo[content]', 'value'=>htmlspecialchars($seo['content']),'rows'=>10),
		array('type'=>'text', 'class'=>'size-xxl', 'id'=>'url', 'label'=>'Адрес канонической страницы<br>REL="CANONICAL"', 'name'=>'seo[canonical]', 'value'=>htmlspecialchars($seo['canonical']), 'maxlength'=>255),
		array('type'=>'html','value'=>'<tr><td>&nbsp;</td><td>
		<div class="btn" onClick="ajaxPost(\'dseoform\',\'tools::dsSave\');"><i class="ic ic-disk"></i>'.MESSAGE('BUTTON_save').'</div>
		<div class="btn" onClick="ajaxGet(\'tools::dynSEO&p='.escape($p).'\');"><i class="ic ic-undo"></i>'.MESSAGE('BUTTON_cancel').'</div>
		</td></tr>')
		);
		$form=new form($fields);
		$form->id='dseoform';
		$form->enctype='multipart/form-data';
        $form->type="horizontal";
        $form->method='POST';
		$out.='<div class="form">'.$form->show().'</div>';
	    return array('cblock'=>$out);
	}
	
	// Dynamic SEO
	static function dynSEO(){
		global $p;
		global $settings, $np;
		$out='';

		if(!isset($p)) $p=0;
		$paginator='';
		$array=mysql::getArray("SELECT SQL_CALC_FOUND_ROWS id,url,title,noindex,nofollow FROM `dynamic_seo` ORDER BY url ASC LIMIT ".escape(($p*20)).", 20");
		$count=mysql::getValue('SELECT FOUND_ROWS()');
		$allPages = ceil($count / 20);
		if(isset($np)){
		    // Переход на псследнюю страницу после добавления нового урла
		    if($np===true){
		        $p=$allPages-1;
		        $array=mysql::getArray("SELECT SQL_CALC_FOUND_ROWS id,url,title,noindex,nofollow FROM `dynamic_seo` ORDER BY url ASC LIMIT ".escape(($p*20)).", 20");
		    }
		}
		if(($allPages-1)<$p) {
			$p=$p-1;
			if($p<0) $p=0;
			$array=mysql::getArray("SELECT SQL_CALC_FOUND_ROWS id,url,title,noindex,nofollow FROM `dynamic_seo` ORDER BY url ASC LIMIT ".escape(($p*20)).", 20");
			$count=mysql::getValue('SELECT FOUND_ROWS()');
		}
		
		if($array!=false){
			$paginator=paginate($count,$p,'<span onClick="ajaxGet(\'tools::dynSEO&p=%1\');">%2</span>',20,true,'<b>Страницы:</b>');
		}
		$out.='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li>
		<li><span>DynamicSEO</span></li></ul></div>
		<div class="row"><h2>DynamicSEO</h2></div>
		<div class="row">
	 <div class="btn" onClick="ajaxGet(\'tools::dsEdit?=0&p='.$p.'\');"><i class="ic ic-circleadd"></i>'.MESSAGE('tools','addUrl').'</div>
	 <div class="btn" onClick="ajaxGet(\'tools::dsInfo?=0&p='.$p.'\');"><i class="ic ic-help"></i>'.MESSAGE('tools','dseoInfo').'</div>
	 <div style="float:right">'.$paginator.'</div>
	</div>';
	
	if($array!=false){
		$out.='<div class="row">
		<table class="cmstable4">
		<tr><th style="width:50px !important;">Noindex</th><th style="width:50px !important;">Nofollow</th><th>URL</th><th style="width:500px !important;">Title</th><th style="width:40px !important;">&nbsp;</th></tr>';
		foreach($array AS $val){
			$nicheck='';
			$nfcheck='';
			if($val['noindex']==1) $nicheck=' checked="checked"';
			if($val['nofollow']==1) $nfcheck=' checked="checked"';
			$out.='<tr id="ds'.$val['id'].'"><td><input id="s'.$val['id'].'" class="checkbox" type="checkbox"'.$nicheck.' onChange="ajaxGet(\'tools::noindex?='.$val['id'].'\')"><label for="s'.$val['id'].'"></label></td><td><input class="checkbox" id="d'.$val['id'].'" type="checkbox"'.$nfcheck.' onChange="ajaxGet(\'tools::nofollow?='.$val['id'].'\')"><label for="d'.$val['id'].'"></label></td><td><b class="hand" onClick="ajaxGet(\'tools::dsEdit?='.$val['id'].'&p='.$p.'\');">'.$val['url'].'</b></td><td><span class="small" style="display:block;line-height:11px; width:500px !important">'.$val['title'].'</span></td><td><i class="ic ic-red ic-delete" onClick="ajaxGet(\'tools::delUrl?='.$val['id'].'&p='.$p.'\')"></i></td></tr>';
			}
		$out.='</table>
		</div>';
		}
	return array('cblock'=>$out);
	}
	
	static function dsInfo(){
		$out='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li><li><span onClick="ajaxGet(\'tools::dynSEO?=\')">DynamicSEO</span></li>
		<li><span>'.MESSAGE('tools','dseoInfo').'</span></li></ul></div>';
		$out.='<div class="help">
	<h1>DynamicSEO</h1>
	<p><b>Модуль DynamicSEO служит для управления основными параметрами индексирования и поисковой оптимизации страниц динамического сайта.</b></p>
	<p>Если структура сайта содержит множество динамических (генерируемых) страниц, то управлять заголовками и параметрами индексирования этих страниц невозможно стандартными средствами CMS, так как эти страницы физически не существуют на сайте, а создаются автоматически, по параметрам переданным в URL адресе. В этом случае Вам поможет модуль DynamicSEO. С помощью этого модуля Вы сможете задавать следующие параметры:
	<ul>
	 <li><b>TITLE</b> : Заголовок окна браузера</li>
	 <li><b>META KEYWORDS</b> : Ключевые слова страницы</li>
	 <li><b>META DESCRIPTION</b> : Описание страницы</li>
	 <li><b>NOINDEX</b> : Запрет индексации страницы поисковыми системами</li>
	 <li><b>NOFOLLOW</b> : Запрет поисковым системам следовать по гиперссылкам этой страницы</li>
	 <li><b>REL="CANONICAL"</b> : Определение т.н. <a href="https://yandex.by/search/?text=%D1%87%D1%82%D0%BE%20%D1%82%D0%B0%D0%BA%D0%BE%D0%B5%20%D0%BA%D0%B0%D0%BD%D0%BE%D0%BD%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B0%D1%8F%20%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B0%20rel%3D%22canonical%22&lr=157">канонической</a> страницы в случае, если на сайте есть несколько страниц с одинаковым или похожим содержанием.</li>
	</ul>
	</p>
	<p>Просто скопируйте URL адрес страницы сайта, и добавьте его к списку URL адресов DynamicSEO, установив нужные пераметры. При формировании страницы с этим адресом AxiomCMS автоматически заменит эти параметры на установленные Вами значения.</p>
	<p><b>ВНИМАНИЕ!</b> Параметры установленные в DynamicSEO имеют более высокий приоритет, чем те же параметры указанные в настройке страницы сайта или установленные динамическими модулями.</p>
	</div>';
		return $out;
	}
	
	// Удаление шаблона
	static function templateDelete(){
		global $item;
		mysql::query("UPDATE `pages` SET template='".escape(mysql::getValue("SELECT id FROM `templates` WHERE 1=1 ORDER BY `order` ASC LIMIT 1"))."' WHERE template='".escape($item)."'");
		mysql::query("DELETE FROM `templates` WHERE id='".escape($item)."' LIMIT 1");
		ajax::sound("sys");
	}
	
	// Редактор файла
	static function fullFileEdit(){
        global $item;
        global $settings;
		global $filetype;
		$cc=htmlspecialchars($item);
		if($filetype=='sitemap') $cc=sprintf(MESSAGE('tools','spEditFile'),'sitemap.xml');
		if($filetype=='robots') $cc=sprintf(MESSAGE('tools','spEditFile'),'robots.txt');
        return '<ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li>
		<li><span>'.$cc.'</span></li></ul>
		<div style="width:100%; height:700px;"><iframe class="codeEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$item.'&type=sfile&filetype='.$filetype.'" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe></div>';
	}
	
	// Порядок макетов
	static function saveTmpOrder(){
		global $table, $dragStatus;
		$count=mysql::orderUpdate($table,$dragStatus);
		if($count>=1) ajax::message(MESSAGE('MESS_chIsSaved'));
		ajax::sound("click");
		return false;
	}
	
	// Сохранение кода сниппета
    static function templateSave(){
        $error=false;
        $out='';
        // Сниппет
        if($_POST['type']=='template'){
            // Проверка на существование сниппета с таким же именем но другим ID
            $m=mysql::getValue("SELECT id FROM `templates` WHERE (id!='".escape($_POST['id'])."' AND name='".escape($_POST['name'])."') LIMIT 1");
            if($m!==false) {
                $out.= '<div class="error">'.$_POST['id'].' '.$_POST['name'].' : '.sprintf(MESSAGE('tools','templateExists'),urldecode($_POST['name'])).'</div>';
                $error=true;
            }
            if(mb_strlen($_POST['name'],'utf-8')<3) {
                $out.='<div class="error">'.MESSAGE('tools','templateNameToShort').'</div>';
                $error=true;
            }
            if($error===false){
                if(onlyDigit($_POST['id'])>0) mysql::query("UPDATE `templates` SET name='".escape($_POST['name'])."', size='".escape(strlen($_POST['save']['content']))."', content='".escape($_POST['save']['content'])."' WHERE id='".escape($_POST['save']['file'])."'");
                else {
					
                    mysql::query("INSERT INTO `templates` SET name='".escape($_POST['name'])."', size='".escape(strlen($_POST['save']['content']))."', author='".escape($_SESSION['user']['id'])."', content='".escape($_POST['save']['content'])."', `order`='".(mysql::getValue("SELECT MAX(`order`) FROM `templates`")+1)."'");
                    // Возвращаем ID нового шаблона
                    $out.='<div id="newId" style="display:none;">'.mysql::insertId().'</div>';
                }
            }
        }
        if($error==false) $out.='saved: '.date("H.i.s",time());
        return array('saved'=>$out);
    }
	
	// Список шаблонов
	static function templateList(){
		global $settings;
		$out='';
		$out.='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li><li><span>'.MESSAGE('tools','templates').'</span></li></ul></div>';
		
		$out.='<div class="row"><h2>Шаблоны страниц HTML</h2></div>
		<div class="row">
            <div class="btn" onclick="ajaxGet(\'tools::templateEdit?=0\')"><i class="ic ic-plus"></i>'.MESSAGE('tools','templateAdd').'</div>
        </div>';
		
		$array=mysql::getArray("SELECT t1.id, t1.context, t1.name, t1.author, UNIX_TIMESTAMP(t1.timestamp) AS stamp, t2.name AS authorname
		FROM `templates` AS t1
			JOIN `users` AS t2 ON t2.id=t1.author
		ORDER BY t1.order ASC");
		
		if($array!=false){
			
			$used=mysql::getArray("SELECT t1.template, t1.btname, t1.id, t2.alias
			FROM `pages` AS t1
				JOIN `folders` AS t2 ON t2.id=t1.folder
			ORDER BY t2.order ASC");
			
			$out.='<table id="tmp" class="cmstable4" onmouseover="dragTableInit(\'tmp\',\'tools::saveTmpOrder?=&table=templates\')">
			<tr class="nodrag nodrop"><th style="width:24px;">&nbsp;</th><th style="width:24px;"></th><th style="width:160px;">Шаблон</th><th>Использован в разделах</th><th style="width:120px;">'.MESSAGE('author').'</th><th style="width:60px;">&nbsp;</th></tr>';
			foreach($array AS $key=>$val){
				$acts='';
				$ic='<span class="tooltip" data-tooltip="'.MESSAGE('tools','tempDefault').'"><i class="color-red ic-insert-template"></i></span>';
				if($key>0) {
					$acts.='<i class="ic-red ic-delete" onClick="tempDelete('.$val['id'].')"></i>';
					$ic='<i class="ic-insert-template"></i>';
				}
				$out.='<tr id="tmp'.$val['id'].'"><td class="drag"><i class="ic ic-move-v"></i></td><td>'.$ic.'</td><td><b  class="hand" onClick="ajaxGet(\'tools::templateEdit?='.$val['id'].'\')">'.$val['name'].'</b></td><td>';
				if(!empty($used)){
					foreach($used AS $v){
						if($v['template']==$val['id']){
							$out.='<b class="hand" style="display:inline-block;" id="tpl'.$v['id'].'" onclick="ajaxGet(\'pages::pageEdit?='.$v['id'].'\');">'.$v['btname'].'</b> ';
						}
					}
				}
				
				
				$out.='</td><td>'.$val['authorname'].'</td><td><i class="ic-editdoc" onClick="ajaxGet(\'tools::templateEdit?='.$val['id'].'\');"></i>'.$acts.'</td></tr>';
			}
			$out.='</table>';
		}
		
		return array('cblock'=>$out);
	}
	
	// Отображение сниппетов ПОЛНОЕ
	static function showFullSnippetList(){
        $out='';
		$out.='<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li><li><span>'.MESSAGE('tools','snippets').'</span></li></ul></div>
		<div class="row"><h2>Сниппеты</h2></div>';
        $array=tools::getSnippetList();
        $out.='<div class="row">
            <div class="btn"  onclick="ajaxGet(\'tools::snippetEdit?=0\')"><i class="ic ic-plus"></i>'.MESSAGE('tools','snippetAdd').'</div>
            <div class="btn"  onclick="ajaxGet(\'tools::manual?=snippet\')"><i class="ic ic-help"></i>'.MESSAGE('tools','snippetInfo').'</div>
        </div>';
        if($array!=false){
            $out.='<div class="row"><table id="sniptable" class="cmstable4">
            <tr><th style="width:24px;"></th><th style="width:250px;">'.MESSAGE('tools','snippet').'</th><th style="width:300px;">'.MESSAGE('comment').'</th><th style="width:100px;">'.MESSAGE('LABEL_author').'</th><th style="width:80px;">'.MESSAGE('Size').'</th><th style="width:50px;"></th></tr>';
            foreach($array AS $val){
                $acts='<i class="ic ic-docedit" onClick="ajaxGet(\'tools::snippetEdit?='.$val['id'].'\')"></i>';
                $icon='ic-rad-checked';
                if($val['author']==0) $icon='ic-snippet';
                else $acts.='<i class="color-red ic-delete" onClick="ajaxGet(\'tools::snipDelete?='.$val['id'].'\')"></i>';
                $out.='<tr id="snip'.$val['id'].'"><td><i class="ic '.$icon.'"></i></td><td><b class="hand" onClick="ajaxGet(\'tools::snippetEdit?='.$val['id'].'\')">'.$val['name'].'</b></td><td class="smallgrey">'.$val['info'].'</td><td class="small">'.$val['authorname'].'</td><td>'.file::bytes($val['size'],2).'</td><td>'.$acts.'</td></tr>';
            }
            $out.='</table></div>';
        }
        return array('cblock'=>$out);
    }
	
	static function init(){
		global $admin;
		global $settings;
		
		if(getenv("COMSPEC")) { $dbName=$settings['databaseLocalName']; $dbUser=$settings['databaseLocalUser']; $dbPass=$settings['databaseLocalPass']; }
		else { $dbName=$settings['databaseName']; $dbUser=$settings['databaseUser']; $dbPass=$settings['databasePass']; }
		
		if(isset($admin)) {
			$admin->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/jquery.tablednd.js');
			$admin->addBodyScript('
function tempDelete(id,submit){
    if(submit==undefined){
        dialogConfirm("'.MESSAGE('tools','templateDelete').'","tempDelete("+id+",1)");
    }
    else {
         domRemove("tmp"+id);
         ajaxGet("tools::templateDelete?="+id,"hiddenblock","tools");
    }
}	
');
			
		}
		$out='<div class="row">
		    <ul class="breadCrumbs"><li><span>'.MESSAGE('tools','developerTools').'</span></li></ul>
		</div>';

		$dbName=$settings['databaseName'];
		$dbPass=$settings['databasePass'];
		$dbUser=$settings['databaseUser'];
		if(getenv("COMSPEC")){
		    $dbName=$settings['databaseLocalName'];
		    $dbUser=$settings['databaseLocalUser'];
		    $dbPass=$settings['databaseLocalPass'];
        }

		$array=array(
		    0=>array(
		        'name'=>'Сниппеты',
                'description'=>'Сниппеты - часто используемые фрагменты текста, HTML или JavaScript кода.',
                'icon'=>'snippet',
                'click'=>'tools::showFullSnippetList'
            ),
            1=>array(
                'name'=>'Шаблоны страниц HTML',
                'description'=>'Управление макетами HTML, отвечающими за внешний вид и расположение структурных блоков.',
                'icon'=>'insert-template',
                'click'=>'tools::templateList'
            ),
            2=>array(
                'name'=>'Генератор SITEMAP.XML',
                'description'=>'Редактирование служебного файла sitemap.xml для поисковых систем.',
                'icon'=>'cattree',
                'link'=>$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.urlencode($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml').'&type=sfile&mode=sitemap'
            ),
            3=>array(
                'name'=>'Генератор ROBOTS.TXT',
                'description'=>'Редактирование служебного файла robots.txt с инструкциями для поисковых роботов.',
                'icon'=>'robot',
                'link'=>$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.urlencode($_SERVER['DOCUMENT_ROOT'].'/robots.txt').'&type=sfile&mode=robots'
            ),
            4=>array(
                'name'=>'MYSQL ADMIN',
                'description'=>'Управление базой данных MySQL.',
                'icon'=>'mysql',
                'link'=>$settings['protocol'].$settings['siteUrl'].'/admin/plugins/adminer/?username='.$dbUser.'&db='.$dbName.'&password='.$dbPass
            ),
            5=>array(
                'name'=>'Dynamic SEO',
                'description'=>'Управление мета-данными динамических страниц сайта .',
                'icon'=>'seo',
                'click'=>'tools::dynSEO?='
            )
        );

		$out.='<div class="row">';
		foreach($array AS $key=>$val){
		    if(!isset($val['click'])) $link=' target="_blank" href="'.$val['link'].'"';
            else $link=' id="tls'.$key.'" onClick="ajaxGet(\''.$val['click'].'\')"';
		    $out.='<a class="axiomWidget"'.$link.'>
		    <i class="ic-'.$val['icon'].'"> </i>
		    <dl>
		        <dd>'.$val['name'].'</dd>
		        <dt>'.$val['description'].'</dt>
		    </dl>
		    </a>';
        }
        $out.='</div>';
		return $out;
	}

    static function snipDelete(){
        global $item;
        mysql::query("DELETE FROM `snippet` WHERE id='".escape($item)."' LIMIT 1");
        ajax::domRemove('snip'.$item);
        ajax::message(MESSAGE('tools','snippetIsDelete'));
        return false;
    }

    // Проверка валидности имени сниппета
    // Если имя короче 3 символов, или сниппет с этим именем уже создан, то
    // возвращается ошибка
    static function snipNameValid(){
        global $item,$name;
        $out='';
        $m=mysql::getArray("SELECT id FROM `snippet` WHERE id!='".escape($item)."' AND name='".escape(urldecode($name))."' LIMIT 1");
        if($m!==false) $out.='<div class="error">'.sprintf(MESSAGE('tools','snippetExists'),urldecode($name)).'</div>';
        else {
            if(mb_strlen(urldecode($name),'utf-8')<3) $out.='<div class="error">'.$item.' '.$name.' : '.MESSAGE('tools','snippetNameToShort').'</div>';
            else $out='';
        }
        return array('saved'=>$out);
    }

    // Вывод информации из руководства CMS по заданной теме
    // На входе - строка вида theme-subtheme-subsubtheme-subsubsubtheme...
    // и переменная LANG для вывода справки на заданном языке
    // На выходе - всплывающее окно со справкой и ссылка на руководство
    static function manual(){
        //global $item;
        global $info;
        if(!isset($info)) $info=MESSAGE('MESS_manualInfoNotFound');
        return '<div id="openWindowModal" style="width:500px; height:350px;" title="'.MESSAGE('LABEL_cmsManual').'">Сервер справки по системе будет запущен 20.06.2015 в 12:00</div>';
    }

    // Получение списка сниппетов
    static function getSnippetList(){
        return mysql::getArray("SELECT t1.id, t1.name, t1.info, UNIX_TIMESTAMP(t1.timestamp) AS stamp, t1.hidden, t1.size, t1.author, t2.name AS authorname
        FROM `snippet` AS t1 JOIN `users` AS t2 ON t2.id=t1.author
        ORDER BY t1.author ASC, t1.name ASC");
    }

    // Отображение списка сниппетов и кнопок интерфейса
    static function showSnippetList(){
        global $settings;
        $out='';
        $array=tools::getSnippetList();
        $out.='<div class="row">
            <div class="btn tooltip" data-tooltip="'.MESSAGE('tools','snippetAdd').'" onClick="popup(\''.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file=0&type=snippet\')"><i class="ic-plus" style="margin-right:0;"></i>&nbsp;Добавить</div>
            <div class="btn" onClick="ajaxGet(\'tools::showSnippetList?=\')"><i class="ic-reload" style="margin-right:0;"></i>&nbsp;Обновить</div>
        </div>';
        if($array!=false){
            $out.='<div class="row" style="height:630px; overflow-y:scroll;"><table id="sniptable" class="cmstable3">
            <tr><th style="width:24px;"></th><th>'.MESSAGE('tools','snippet').'</th><th style="width:50px;"></th></tr>';
            foreach($array AS $val){
                $acts='<i class="ic-editdoc" onClick="popup(\''.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$val['id'].'&type=snippet\')"></i>';
                if($_SESSION['user']['group']==0) $acts.='<i class="ic-delete color-red float-right" onClick="dialogConfirm(\''.str_replace("%s",$val['name'],MESSAGE('tools','snippetDelConfirm')).'\',\'ajaxGet(\\\'tools::snipDelete?='.$val['id'].'\\\')\')"></i>';
                $out.='<tr id="snip'.$val['id'].'"><td><i class="ic-puzzle"></i></td><td><b class="hand">'.$val['name'].'</b></td><td>'.$acts.'</td></tr>';
            }
            $out.='</table></div>';
        }
        return array('snippets'=>$out);
    }
    

    // Сохранение файла из редактора кода
    static function fileSave(){
        $error=false;
        $out='';
        $newId=0;
        // Сниппет
        if($_POST['type']=='snippet'){
            // Проверка на существование сниппета с таким же именем но другим ID
            $m=mysql::getValue("SELECT id FROM `snippet` WHERE (id!='".escape($_POST['save']['file'])."' AND name='".escape($_POST['name'])."') LIMIT 1");
            if($m!==false) {
                ajax::message(sprintf(MESSAGE('tools','snippetExists'),urldecode($_POST['name'])));
                $error=true;
            }
            if(mb_strlen($_POST['name'],'utf-8')<3) {
                $out.='<div class="error">'.MESSAGE('tools','snippetNameToShort').'</div>';
                $error=true;
            }
            if($error===false){
                if(onlyDigit($_POST['save']['file'])>0) {
                    mysql::query("UPDATE `snippet` SET name='".escape($_POST['name'])."', info='".escape($_POST['info'])."', hidden='".escape($_POST['hidden'])."', size='".escape(strlen($_POST['save']['content']))."', value='".escape($_POST['save']['content'])."' WHERE id='".escape($_POST['save']['file'])."'");
                }
                else {
                    // Если есть сниппет с таким же именем, то запишем его
                    mysql::query("INSERT INTO `snippet` SET name='".escape($_POST['name'])."', info='".escape($_POST['info'])."', hidden='".escape($_POST['hidden'])."', size='".escape(strlen($_POST['save']['content']))."', author='".escape($_SESSION['user']['id'])."', value='".escape($_POST['save']['content'])."', timestamp=NOW()");
                    // Возвращаем ID нового сниппета, а затем
                    $newId=mysql::insertId();
                    ajax::javascript('getId("id").value="'.$newId.'"');
                }
                // в родительском окне запускаем обновление списка сниппетов
                ajax::javascript('opener.ajaxGet("tools::showSnippetList?=")');
            }
        }
        elseif($_POST['type']=='template'){
            ajax::sound("sys");
            return self::templateSave();
        }
        else {
            file::save($_POST['save']['file'],$_POST['save']['content']);
            return false;
        }
        if($error==false) {
            return array('saved'=>'saved: '.date("H.i.s",time()));
        }
        return false;
    }
	
	

    static function fileEdit(){
        global $item;
        global $settings;
        return '<iframe class="codeEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$item.'" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe>';
    }

    // установка паузы на группу модулей
    static function modulesPause(){
        global $item;
        global $pause;
        global $page;
        $pause=onlyDigit($pause);
        mysql::query("UPDATE `pages_modules` SET pause='".escape($pause)."' WHERE id IN(".escape($item).")");
        return pages::modulesActive($page);
    }

    // Удаление группы модулей у страницы
    static function modulesDelete(){
        global $item;
        global $page;
        $out='';
        // Отключаем только модули, которые не отвечают за вывод основного текста
        $array=mysql::getArray("SELECT id, module FROM `pages_modules` WHERE id IN(".escape($item).")");
        $forDel=array();
        $message='';
        foreach($array AS $val){
            if($val['module']=='showText'){
                mysql::query("UPDATE `pages_modules` SET pause='1' WHERE id='".escape($val['id'])."' LIMIT 1");
                $message='; dialogAlert("'.MESSAGE('tools','moduleNoDelPageText').'")';
            }
            else $forDel[]=$val['id'];
        }
        if(isset($forDel[0])) mysql::query("DELETE FROM `pages_modules` WHERE id IN(".escape(implode(",",$forDel)).")");
        $out.='<div id="executeJSFunction">pa(\'pe\','.$page.')'.$message.'</div>';
        return $out;
    }


    // Сохранение настроек
    static function mdlSettingsSave(){
        global $page;
        global $item;
        if(isset($_POST['mdl'])){
            $mdl=$_POST['mdl'];
            if(isset($_POST['moduleSettings'])) $mdl['settings']=serialize($_POST['moduleSettings']);
            else $mdl['settings']=NULL;
            mysql::saveArray('pages_modules',$mdl);
            $page=$mdl['page_id'];
            $item=$page;
            ajax::domRemove("mdlWindow");
            return array(
                'mdlWin'=>tools::moduleSettings(),
                'modules'=>pages::modulesActive()
            );
        }
    }

    // Отображение настроек модуля
    static function moduleSettings(){
        global $item;
        global $win;
        global $settings;
        $out='';
        ob_start();
        // Получаем все свойства модуля
        $mdl=mysql::getArray("SELECT t1.*, t2.btname, t3.content
        FROM
            `pages_modules` AS t1
            JOIN `pages` AS t2 ON t2.id=t1.page_id
            JOIN `templates` AS t3 ON t3.id=t2.template
        WHERE t1.id='".escape($item)."'
        LIMIT 1",true);

        if($mdl!=false){
            $v=template::getVars($mdl['content']);
            $varlist=array();
            foreach($v AS $vv) $varlist[$vv]=$vv;
            $out.='
            <h2>'.$mdl['btname'].' : '.sprintf(MESSAGE('tools','moduleSettings'),$mdl['module']).'</h2>';
            $f=$mdl['module'];
            $functionName=str_replace(':','_',$f);
            $setFunctionName=$functionName.'SETTINGS';
            list($m,$sm)=explode(':',$f);
            $path=$_SERVER['DOCUMENT_ROOT'].'/_modules/';
            if($m!='') $path.=$m.'/';
            if($sm!='') $path.=$sm.'.php';
            if($mdl['module']!='showText'){
                if(file_exists($path)) include_once $path;
                else include_once($_SERVER['DOCUMENT_ROOT'].'/_modules/'.$f.'.php');
            }
            if(strlen($mdl['settings'])>=8) $set=unserialize($mdl['settings']);
            else $set=array();
            
            $fields=array(
                array('type'=>'hidden', 'name'=>'mdl[id]', 'value'=>$mdl['id']),
                array('type'=>'hidden', 'name'=>'mdl[page_id]', 'value'=>$mdl['page_id']),
                array('type'=>'hidden', 'name'=>'mdl[module]', 'value'=>$mdl['module']),
                array('type'=>'checkbox', 'class'=>'checkbox', 'label'=>MESSAGE('LOCK'), 'name'=>'mdl[pause]', 'value'=>$mdl['pause']),
                array('type'=>'text', 'label'=>MESSAGE('comment'), 'name'=>'mdl[comment]', 'value'=>$mdl['comment']),
                array('type'=>'select', 'label'=>MESSAGE('pages','MESS_tmpVar'), 'name'=>'mdl[tmplvar]', 'value'=>$mdl['tmplvar'], 'options'=>$varlist)
            );
            // В модуле должна присутствовать функция $functionName.'SETTINGS'
            if(function_exists($setFunctionName)){
                $f=call_user_func($setFunctionName,$set);
                if(is_array($f)) $fields=array_merge($fields,$f);
            }
            $fields=array_merge($fields,array(array('type'=>'html', 'value'=>'<tr><td>&nbsp;</td><td><div class="btn" onClick="ajaxPost(\'sForm\',\'tools::mdlSettingsSave\')"><i class="ic ic-save"></i>'.MESSAGE('BUTTON_save').'</div><div class="btn" onClick="ajaxGet(\'tools::mdlListCancel?='.$mdl['page_id'].'\')"><i class="ic ic-undo"></i>'.MESSAGE('BUTTON_cancel').'</div></td></tr>')));

            $f=new form($fields);
            $f->id='sForm';
            $f->enctype='multipart/form-data';
            $f->type="horizontal";
            $f->method='POST';
            $out.=$f->show();
        }
        if($win==1) {
            ajax::window('<div id="mdlWindow" style="width:600px; height:100%;" title="'.MESSAGE('tools','LABEL_modules').'">'.ob_get_contents().$out.'</div>',true,'mdlWindow');
            return false;
        }
        return ob_get_contents().$out;
    }

    // Отмена редактирование модулей и закрытие окна
    static function mdlListCancel(){
        global $item;
        ajax::domRemove("mdlWindow");
        return array(
            'modules'=>pages::modulesActive()
        );
    }

    // Отображение модулей подключенных к странице
    static function showModulesActive($page){
        $out='';
        global $site, $settings;
        $array=module::getActive($page);
        ob_start();


        if($array!=false){
            $templateVars=template::getVars(mysql::getValue("SELECT content FROM `templates` WHERE id='".escape($array[0]['template'])."' LIMIT 1"));



            $dopFields='';

            $out.='<h2 style="margin-top:8px;">'.sprintf(MESSAGE('tools','modulesOn'),$array[0]['pagename']).'</h2>
            <table id="incm" class="cmstable4" onmouseover="dragTableInit(\'incm\',\'saveTmpOrder?=&table=pages_modules\',\'hiddenblock\',\''.__CLASS__.'\')">
            <tr class="nodrag nodrop">
            <th style="width:24px;">&nbsp;</th>
            <th style="width:24px;">&nbsp;</th>
            <th style="width:300px;">'.MESSAGE('module').'</th>
            <th>'.MESSAGE('DESCRIPTION').'</th>
            <th style="width:160px;">'.MESSAGE('pages','MESS_tmpVar').'</th>
            <th style="width:90px;">'.MESSAGE('CONDITION').'</th>
            '.$dopFields.'
            <th style="width:65px;"></th>
            </tr>';



            foreach($array AS $val){

                $vopt='';
                foreach($templateVars AS $k){
                    $selected='';
                    if($k==$val['tmplvar']) $selected=' selected="selected"';
                    $vopt.='<option'.$selected.' value="'.$k.'">'.$k.'</option>';
                }

                $checked='';
                if($val['pause']==0) $checked=' checked="checked"';
                $pauseCheck='<input id="mp'.$val['id'].'" type="checkbox" value="1" class="switch"'.$checked.'><label for="mp'.$val['id'].'" style="width:1px;"></label>';
                
                $dopFields='';
                $actions='';
                if($val['module']!='showText') {
                    $path=$_SERVER['DOCUMENT_ROOT'].'/_modules/';
                    list($m,$sm)=explode(':',$val['module']);
                    if($m!='') $path.=$m.'/';
                    if($sm!='') $path.=$sm.'.php';
                    $actions.='<i class="ic ic-docedit" onclick="ajaxGet(\'fileEdit?='.urlencode($path).'&page='.$page.'\',\'AxiomWinDesc\',\'tools\')" title="'.MESSAGE('tools','fileEdit').'"></i>
                    <i class="ic ic-red ic-delete" style="float:right" title="'.MESSAGE('pages','LABEL_mdlOff').'" onClick="mdlDelete('.$page.','.$val['id'].')"></i>';

                }

                $out.='<tr id="m'.$val['id'].'">
                <td class="drag"><i class="ic ic-move-v"></i></td>
                <td><i class="ic ic-plugin"></i></td>
                <td><b class="hand" onClick="ajaxGet(\'tools::moduleSettings?='.$val['id'].'\')">'.$val['module'].'</b></td>
                <td><span>'.$val['comment'].'</span></td>
                <td><select class="size-m" style="height:24px; font-size:14px;">'.$vopt.'</select></td>
                <td>'.$pauseCheck.'</td>
                '.$dopFields.'
                <td>'.$actions.'</td>
                </tr>';
            }
            $out.='</table>';

        }
        $out.=ob_get_contents();
        if(isset($array)) {
            return $out;
        }
        else {
            return array(
                'modules'=>pages::modulesActive()
            );
        }
    }
	
	// Сохранение кода сниппета
    static function snipSave(){
        $error=false;
        $out='';
        // Сниппет
        if($_POST['type']=='snippet'){
            // Проверка на существование сниппета с таким же именем но другим ID
            $m=mysql::getValue("SELECT id FROM `snippet` WHERE (id!='".escape($_POST['id'])."' AND name='".escape($_POST['name'])."') LIMIT 1");
            if($m!==false) {
                $out.= '<div class="error">'.$_POST['id'].' '.$_POST['name'].' : '.sprintf(MESSAGE('tools','snippetExists'),urldecode($_POST['name'])).'</div>';
                $error=true;
            }
            if(mb_strlen($_POST['name'],'utf-8')<3) {
                $out.='<div class="error">'.MESSAGE('tools','snippetNameToShort').'</div>';
                $error=true;
            }
            if($error===false){
                if(onlyDigit($_POST['id'])>0) mysql::query("UPDATE `snippet` SET name='".escape($_POST['name'])."', info='".escape($_POST['info'])."', hidden='".escape($_POST['hidden'])."', size='".escape(strlen($_POST['save']['content']))."', value='".escape($_POST['save']['content'])."' WHERE id='".escape($_POST['save']['file'])."'");
                else {
                    mysql::query("INSERT INTO `snippet` SET name='".escape($_POST['name'])."', info='".escape($_POST['info'])."', hidden='".escape($_POST['hidden'])."', size='".escape(strlen($_POST['save']['content']))."', author='".escape($_SESSION['user']['id'])."', value='".escape($_POST['save']['content'])."', timestamp=NOW()");
                    // Возвращаем ID нового сниппета, а затем
                    // в родительском окне запускаем обновление списка сниппетов
                    $out.='<div id="newId" style="display:none;">'.mysql::insertId().'</div>';
                }
            }
        }
        if($error==false) $out.='saved: '.date("H.i.s",time());
        return $out;
    }
	
	// Редактор сниппетов
	static function snippetEdit(){
		global $item;
		global $settings;
		return array('cblock'=>'<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li><li><span onClick="ajaxGet(\'tools::showFullSnippetList?=\')">'.MESSAGE('tools','snippets').'</span></li><li><span>'.MESSAGE('tools','codeEditor').'</span></li></ul></div>
		<div class="row"><h2>Редктирование сниппета</h2></div>
		<div class="row" style="width:100%; height:700px;"><iframe class="codeEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$item.'&type=snippet&tools=1" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe></div>');
	}
	
	// Редактор шаблонов
	static function templateEdit(){
		global $item;
		global $settings;
		return array('cblock'=>'<div class="row"><ul class="breadCrumbs"><li><a href="./'.__CLASS__.'/">'.MESSAGE('tools','developerTools').'</a></li><li><span onClick="ajaxGet(\'tools::templateList?=\')">'.MESSAGE('tools','templates').'</span></li><li><span>'.MESSAGE('tools','codeEditor').'</span></li></ul></div>
		<div class="row"><h2>Шаблоны страниц HTML</h2></div>
		<div class="row" style="width:100%; height:700px;"><iframe class="codeEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$item.'&type=template&tools=1" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe></div>');
	}

    // Редактирование компонентов CMS во всплывающем окне
    static function editSpecial(){
        global $item;
        global $settings;
        global $type;
        if(!isset($type)) $type='';
        else $type='&type='.$type;
        $out='<div id="openWindowModal" style="width:100%; height:100%;" title="'.MESSAGE('tools','snippets').'">
        <iframe class="codeEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/editor.php?file='.$item.$type.'" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe></div>';
        return $out;
    }

    // Подключение модуля к странице
    static function moduleAddToPage(){
        global $item;
        global $page;
        global $settings;
        global $opened;
        if(!isset($opened)) $opened=false;

        if(isset($item)){
            $comment='';
            list($mdl,$submdl)=explode(':',$item);
            // Простые модули - файл лежит в корне
            if(!isset($submdl)){
                $moduleFile=$_SERVER['DOCUMENT_ROOT'].'/_modules/'.$mdl.'.php';
                $m=file($moduleFile);
                // Если первые два символа 1 строки модуля комментарий, то делаем его описанием модуля
                if($m[1]{0}=='/' && $m[1]{1}=='/') $comment=str_replace('//','',$m[1]);
            }
            else {
                if($mdl!=$submdl){
                    // Дополнительные компоненты композитного модуля
                    $moduleFile=$_SERVER['DOCUMENT_ROOT'].'/_modules/'.$mdl.':'.$submdl.'.php';
                    $m=file($moduleFile);
                    // Если первые два символа 1 строки модуля комментарий, то делаем его описанием модуля
                    if($m[1]{0}=='/' && $m[1]{1}=='/') $comment=str_replace('//','',$m[1]);
                }
                else {
                    // А если это основной файл композитного модуля, то надо получить его параметры
                    // загрузив и обработав класс
                    include_once $_SERVER['DOCUMENT_ROOT'].'/_modules/'.$mdl.'/'.$mdl.'.php';
                    $props= get_class_vars($mdl);
                    if(isset($props['name'])) $comment=$props['name'];
                }
            }


            $set['id']=0;
            $set['page_id']=$page;
            $set['module']=$item;
            $set['tmplvar']='pageText';
            $set['pause']=1;
            $set['order']=mysql::getValue("SELECT MAX(`order`) FROM `pages_modules` WHERE page_id='".escape($page)."' LIMIT 1")+1;
            $set['comment']=$comment;
            $set['settings']=NULL;

            // Узнаем, был ли этот модуль подключен к какой либо странице ранее.
            // И если был, то применим к нему предыдущие настройки
            $old=mysql::getArray("SELECT * FROM `pages_modules` WHERE module='".$mdl."' ORDER BY id DESC LIMIT 1",true);
            if($old!=false){
                $set['tmplvar']=$old['tmplvar'];
                $set['settings']=$old['settings'];
            }
            mysql::saveArray('pages_modules',$set);

            $ret['modules']=pages::modulesActive($page);
            $ret['AxiomWinDesc']=mysql::error().tools::modulesShow(true,$opened);

            return $ret;
        }
        return '';
    }

	static function snippetDelete($id){
		mysql::query("DELETE FROM `snippet` WHERE id='".escape($id)."' LIMIT 1");
		return true;
	}

	// Получение сниппетов
	static function snippetList(){
		return mysql::getArray("SELECT t1.*, UNIX_TIMESTAMP(t1.timestamp) AS stamp, t2.name AS authorname
		FROM `snippet` AS t1
			JOIN `users` AS t2 ON t2.id=t1.author
		ORDER BY t1.name ASC");
	}

	// Сохранение сниппета
	static function snippetSave($snippet){
		if(!isset($snippet['author'])) $snippet['author']=0;
		$snippet['size']=strlen($snippet['value']);
		if(!isset($snippet['info'])) $snippet['info']='';
		if(isset($_SESSION['user']['id'])) $snippet['author']=$_SESSION['user']['id'];
		mysql::query("INSERT INTO `snippet` (`id`,`name`,`value`,`info`,`author`,`timestamp`,`size`)
			VALUES ('".escape($snippet['id'])."', '".escape($snippet['name'])."', '".escape($snippet['value'])."', '".$snippet['info']."', '".escape($snippet['author'])."', NOW(), '".escape($snippet['size'])."')
			ON DUPLICATE KEY UPDATE
			`name`='".escape($snippet['name'])."',
			`value`='".escape($snippet['value'])."',
			`info`='".escape($snippet['info'])."',
			`size`='".escape($snippet['size'])."',
			`author`='".escape($snippet['author'])."',
			`timestamp`=NOW()");
		return mysql::insertId();
	}

	// Возвращает список доступных шаблонов
	static function templatesList($context=false,$author=false,$order=" t1.order ASC"){
		$where=array();
		$where[]="t1.author=t2.id";
		if($author!=false) $where[]="t1.author='".escape($author)."'";
		if($context!=false) $where[]="t1.context='".escape($context)."'";

		$array=mysql::getArray("SELECT t1.id, t1.context, t1.name, t1.author, t1.create, t2.name AS authorname
		FROM `templates` AS t1
			JOIN `users`
		WHERE ".implode(" AND ",$where)." ORDER BY ".$order);
		if($array!==false) return $array;
		else return false;
	}

}