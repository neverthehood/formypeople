<?php
/////////////////////////////////////////////////////////////////////
// Класс для работы со страницами сайта
class page{
	public $property=array(
	'id'=>0,				// Идентификатор
	'parent'=>0,			// Файл-родитель
	'folder'=>0,			// ID папки
	'alias'=>'',			// URL alias
	'folderid'=>0,			// ID папки
	'folderalias'=>'',		// ALIAS папки
	'urlhash'=>'',			// md5 хеш URI
	'lang'=>'ru',			// Язык
	'fixedlang'=>'ru',		// Язык (для принудительного назначения)
	'hidden'=>0,			// Скрытая
	'hideinmenu'=>0,		// Не отображать в меню
	'name'=>'',				// Заголовок H1
	'title'=>'',			// Заголовок окна браузера
	'keywords'=>'',			// META keywords Ключевые слова
	'description'=>'',		// META description
	'text'=>'',				// Основной текст страницы
	'text2'=>'',
	'text3'=>'',
	'text4'=>'',
	'text5'=>'',
	'noindex'=>0,			// Не индексировать
	'nofollow'=>0,			// Не идти по ссылкам
	'noarchive'=>0,			// Запрет архивации
	'nocache'=>0,			// Не кэшировать
	'finalize'=>0,			// Финализировать страницу
	'changefreq'=>0,		// Частота обновления
	'order'=>0,				// Порядок
	'btname'=>'',			// Название в меню
	'canonical'=>'',		// Адрес канонической страницы
	'modules'=>0,			// Подключены ли модули
	'required'=>0,			// Подключены ли внешние JS и CSS файлы
	'access'=>0,			// Доступ к странице (пользовательская часть)
	'modifyperm'=>'',		// Группа, которая может редактировать
	'template'=>'default',	// Шаблон внешнего вида
	'author'=>0,			// Автор страницы (id)
	'created'=>0,			// Время создания страницы 
	'modify'=>0,			// Время изменения страницы
	'nosaved'=>0,           // Флаг, поднятый если страница не была сохранена пользователем (устанавливается при создании временной страницы)
	'error'=>'');			// Ошибка
	public $error=false;
	
public function error($message){
	$this -> error .= $message .'<br />';
	}
	
public function __construct(){
	$this->property['id']=0;
	$this->property['parent']=0;
	$this->property['created']=date("j.n.Y H:i",time());
	$this->property['modify']=date("j.n.Y H:i",time());
	}

// 	Возвращает страницу из БД по заданному URL
static function getByUrl($url){
	$array=mysql::getArray("SELECT t1.*,t3.alias AS folderalias,t3.id AS folderid,t3.default,t2.name,t2.title,t2.keywords,t2.description,t2.text,t2.text2,t2.text3,t2.text4,t2.text5,t2.required,t4.content AS template
	FROM `pages` AS t1
		JOIN `pages_text` AS t2 ON t2.page_id=t1.id
		JOIN `folders` AS t3 ON t3.id=t1.folder
		JOIN `templates` AS t4 ON t4.id=t1.template
	WHERE
		t1.urlhash='".md5(trim($url))."'
		AND t4.context='site'
	LIMIT 1",true);
	if($array!=false) return $array;
	else return false;
	}

// 	Возвращает страницу из БД по заданному ID для редактирования
static function getEditedById($id){
	$array=mysql::getArray("SELECT t1.*,t3.id AS folderid,t2.name,t2.title,t2.keywords,t2.description,t2.text,t2.text2,t2.text3,t2.text4,t2.text5,t2.required
	FROM
		`pages` AS t1
		JOIN `pages_text` AS t2 ON t2.page_id=t1.id
		JOIN `folders` AS t3 ON t3.id=t1.folder
	WHERE
		t1.id='".escape($id)."'
	LIMIT 1",true);
	if($array!=false) return $array;
	else return false;
	}

// Устанавливает параметры из переданного массива	
public function setParam($array){
	if(is_array($array)) foreach($array AS $key=>$val){
		if(isset($this->property[$key])) {
			if($key=='alias') {
				$val=translit(trim($val));
				$val=str_replace('__','_',$val);
			}
			if($key=='btname' && $val=='Новая страница') $val='';
			$this->property[$key]=$val;
		}
	}
	return true;
}

// Проверка введенных данных
public function validatePost($array){
	if(mb_strlen($array['btname'])<2) $this->error(MESSAGE('editor','ERROR_btname'));
	if(mb_strlen($array['name'])<2) $this->error(MESSAGE('editor','ERROR_name'));
	if($array['parent']!=0){
		if(strlen($array['alias'])<2) $this->error(MESSAGE('editor','ERROR_alias'));
		}
	if($this->error==true) return false;
	else return true;
	}

// Сохранение страницы	
public function save(){
	$p=$this->property;
	// Проверка, не создана ли ранее страница в этой папке с этим ALIAS и языком
	$array=mysql::getValue("SELECT id FROM `pages` WHERE 
		parent='".escape($p['parent'])."' AND folder='".escape($p['folder'])."' 
		AND alias='".escape($p['alias'])."' AND lang='".escape($p['lang'])."' 
		AND id!='".escape($p['id'])."' LIMIT 1");
	if($array!==false) {
		$this->error(sprintf(MESSAGE('pages','ERROR_aliasExist'),$p['alias']));
		return false;
		}
	else {
		// Если небыло ошибок, то сохраняем страницу
		$datetime=date("d.m.Y H:i",time());
		$insert=false;
		//mysql::query("UPDATE `pages` SET lastmod='0' WHERE lastmod='1'");
		if($p['id']==0){
			// Получить ORDER
			$order=0;
			$allpages=self::getPages();
			foreach($allpages AS $val){
				if($val['parent']==$p['parent'] && $val['order']>$order) $order=$val['order'];
			}
			$order++;
			
			if($p['parent']!=0){
				if(isset($p['fixedlang'])) $p['lang']=$p['fixedlang'];
			}
			
			
			if(!mysql::query("INSERT INTO `pages` 
			SET parent='".escape($p['parent'])."', 
			folder='".escape($p['folder'])."', 
			alias='".escape($p['alias'])."', 
			urlhash='NONE', 
			lang='".escape($p['lang'])."', 
			hidden='".escape($p['hidden'])."', 
			hideinmenu='".escape($p['hideinmenu'])."', 
			noindex='".escape($p['noindex'])."', 
			nofollow='".escape($p['nofollow'])."', 
			noarchive='".escape($p['noarchive'])."', 
			changefreq='".escape($p['changefreq'])."', 
			`order`='".escape($order)."', 
			btname='".escape($p['btname'])."', 
			canonical='".escape($p['canonical'])."', 
			modules='".escape($p['modules'])."', 
			required='".escape($p['required'])."', 
			access='".escape($p['access'])."', 
			modifyperm='".escape($p['modifyperm'])."', 
			template='".escape($p['template'])."', 
			created=NOW(),
			modify=NOW(),
			nocache='".escape($p['nocache'])."', 
			finalize='".escape($p['finalize'])."',
			noedit='0',
			lastmod='1'")){
				$this->error(mysqli_error(mysql::$mysqli));
				return false;
			}
			// Получаем ID сохраненной страницы
			$p['id']=mysql::insertId();
			$insert=true;
			// Вставляем модули по-умолчанию
			mysql::query("INSERT INTO `pages_modules` SET page_id='".escape($p['id'])."', module='showText', tmplvar='pageText', pause='0', `order`='1', `comment`='".MESSAGE('modules','desc_mainShowText')."'");
		}
		else {
			if(!mysql::query("UPDATE `pages` SET alias='".escape($p['alias'])."', 
			lang='".escape($p['lang'])."', hidden='".escape($p['hidden'])."', 
			hideinmenu='".escape($p['hideinmenu'])."', noindex='".escape($p['noindex'])."', 
			nofollow='".escape($p['nofollow'])."', noarchive='".escape($p['noarchive'])."', 
			changefreq='".escape($p['changefreq'])."', btname='".escape($p['btname'])."', 
			canonical='".escape($p['canonical'])."', required='".escape($p['required'])."', 
			access='".escape($p['access'])."', template='".escape($p['template'])."', 
			modify='".escape($datetime)."', nocache='".escape($p['nocache'])."', 
			finalize='".escape($p['finalize'])."', lastmod='1', noedit='0'
			WHERE id='".escape($p['id'])."' LIMIT 1")){
				$this->error(mysqli_error(mysql::$mysqli));
				return false;
			}
		}
		if($insert==true) {
			$insert="INSERT INTO `pages_text` SET page_id='".escape($p['id'])."',";
			$where="";
		}
		else {
			$insert="UPDATE `pages_text` SET ";
			$where=" WHERE page_id='".escape($p['id'])."' LIMIT 1";
		}

		// Вставляем данные в таблицу pages_text
		if(!mysql::query($insert."
		name='".escape($p['name'])."',
		title='".escape($p['title'])."',
		keywords='".escape($p['keywords'])."',
		description='".escape($p['description'])."',
		text='".escape($p['text'])."',
		text2='',
		text3='',
		text4='',
		text5=''".$where)){
			$this->error(mysqli_error(mysql::$mysqli));
			return false;
		}
			
		// вычисляем URL и URLHASH данной страницы
		$url=self::getPath($p['id']);

		if($url=='') $url=self::getPath($p['parent']).$p['alias'].'/';
		mysql::query("UPDATE `pages` SET alias='".escape($p['alias'])."', urlhash='".escape(md5($url))."' WHERE id='".escape($p['id'])."' LIMIT 1");
		
		// Если изменился алиас, значит надо перегенирировать адреса потомков
		//if($renew==true){
			$arr=self::getPages(true);
			@$addrArray[]=$url;// адрес этой страницы
			foreach($arr AS $ke=>$va){
				if($va['id']==$p['id']) {
					$arr[$ke]['alias']=$p['alias'];
					break;
				}
			}
			// Генерируем ALIAS всех потомков
			$childs=self::allPageChilds($p['id'],$arr);
			if($childs!=false){
				foreach($childs AS $val){
					mysql::query("UPDATE `pages` SET urlhash='".escape(md5(self::getPath(trim($val))))."', lang='".escape($p['lang'])."' WHERE id='".escape($val)."' LIMIT 1");
				}
			}
		//	}
		}
	return $p['id'];
	}
	
	// Возвращает список всех страниц сайта
	// Если renew=true, то принудительно обновляется список разделов, иначе он берется из кэша
	static function getPages($renew=false){
		static $array=false;
		if($array===false || $renew==true){
			$array=mysql::getArray("SELECT t2.alias AS folderalias, t2.id AS folder, t2.order AS folderorder, t2.default, t1.id, t1.alias AS pagealias, t1.lang, t1.access, UNIX_TIMESTAMP(t1.modify) AS modify, UNIX_TIMESTAMP(t1.created) AS created, t1.btname AS pagename, t1.hidden, t1.parent, t1.hideinmenu, t1.starttime, t1.stoptime, t1.lastmod
			FROM
				`pages` AS t1
				JOIN `folders` AS t2 ON t2.id=t1.folder
			ORDER BY
			    t2.order ASC,
			    t1.lang DESC,
				t1.parent ASC,
				t1.order ASC");
		}
		return $array;
	}

// есть ли у узла старшие и младшие братья?
// Если есть только старший, то вернет 1
// Если только младший, то 2
// а если оба, то 3
// а если нет - то 0
static function existBrothers($key,$folder,$parent,$lang){
	global $catarr;
	$ret=0;
	// Начинаем подниматься вверх по массиву
	for($n=$key;$n>=0;$n--){
	    if(!isset($catarr[$n])) break;
	    else {
	        if($n!=$key){
	            if($catarr[$n]['folder']!=$folder) break;
	            if($catarr[$n]['parent']==$parent && $catarr[$n]['lang']==$lang){ $ret+=1; break; }
            }
	    }
	}
	// Начинаем спускаться вниз по массиву
	for($n=$key;$n<=(count($catarr)-1);$n++){
	    if(!isset($catarr[$n])) break;
	    else {
	        if($n!=$key){
	            if($catarr[$n]['folder']!=$folder) break;
                if($catarr[$n]['parent']==$parent && $catarr[$n]['lang']==$lang){ $ret+=2; break; }
            }
	    }
	}
	$catarr[$key]['brothers']=$ret;
	return $ret;
}
		
	// Возвращает относительный путь к странице от корня
	static function getPath($pageId,$renew=false){
		global $settings;
		$path=array();
		if($pageId!=false){
			$array=self::getPages($renew);
			$max=count($array)-1;
			$find=$pageId;
			$language=false;
			$lang=$settings['siteDefaultLang'];
			$showMainParent=false;
			// Идем от низа массива к его корню и формируем путь
			for($i=$max; $i>=0; $i--){
				if($find==$pageId){
					if($array[$i]['id']==$find){
						$language=$array[$i]['lang'];
						if($array[$i]['parent']!=0){
							$path[]=$array[$i]['pagealias'];
							$find=$array[$i]['parent'];
							$showMainParent=true;
							}
						else {
							if($array[$i]['default']==1 && $showMainParent==false) $path[]='';
							else $path[]=$array[$i]['folderalias'];
							}
						}
					}
				else {
					if($array[$i]['id']==$find){
						if($array[$i]['parent']==0){
							if($array[$i]['default']==1 && $showMainParent==false) $path[]='';
							else $path[]=$array[$i]['folderalias'];
							}
						else {
							$path[]=$array[$i]['pagealias'];
							$showMainParent=true;
							}
						$find=$array[$i]['parent'];
						}
					}
				if($find==0) break;
				}
			if($language!=false) $lang=$language;
			if($lang!=$settings['siteDefaultLang']) $path[]=$lang;
			if(is_array($path)){
				krsort($path);
				$path=implode('/',$path);
				$path.='/';
				$path=str_replace('//','/',$path);
				$path=str_replace('//','/',$path);
				return $path;
				}
			else return false;
			}
		else return false;
	}
		
	// Получение дерева потомкав заданной страницы
	// На входе массив со всеми страницами сайта
	static function allPageChilds($id,$array=false){
		if($array===false) $array=self::getPages();
		static $childs=false;
		foreach($array as $val){
			if($val['parent']==$id) {
				$childs[]=$val;
				self::allPageChilds($val['id'],$array);
				}
			}
		return $childs;
	}

    // Получение списка ID всех потомкав заданной страницы
	// На входе массив со всеми страницами сайта
	static function allPageChildsId($id,$array=false){
		if($array===false) $array=self::getPages();
		static $childs=false;
		foreach($array as $val){
			if($val['parent']==$id) {
				$childs[]=$val['id'];
				self::allPageChildsId($val['id'],$array);
				}
			}
		return $childs;
	}
	
	// Получение потомков страницы
	// Возвращает массив ближайших потомков по ID страницы (первая ветвь дерева)
	// отсортированный по ORDER
	static function getChilds($pageId){
		$array=self::getPages();
		$childs=array();
		foreach($array as $val){
			if($val['parent']==$pageId) {
				$val['path']=self::getPath($val['id']);
				$childs[]=$val;
				}
			}
		return $childs;
	}

	// Получение главного родителя по ID страницы
	static function getMainParent($pageId){
		$array=self::getPages();
		if($array!=false){
			// Шаг 1: Проходим по массиву пока не найдем данную страницу
			foreach($array AS $val){
				// Нашли страницу с pageId
				if($val['id']===$pageId){
					if($val['parent']==0) return $val['id'];
					else {
						foreach($array AS $v){
							if($v['folder']==$val['folder'] && $v['lang']==$val['lang'] && $v['parent']==0) return $v['id'];
							}
						}
					break;
					}
				}
			}
		return false;
	}
	
	// Формирование списка всех папок
	static function getFolders($update=false){
		static $folders=false;
		if($folders===false){
			$pgs=self::getPages();
			$lastfolder="folderIsNotFound";
			if($pgs!=false){
				foreach($pgs AS $val){
					if($val['folderalias']!=$lastfolder){
						$folder['id']=$val['folder'];
						$folder['alias']=$val['folderalias'];
						$folder['default']=$val['default'];
						$folder['order']=$val['folderorder'];
						$lastfolder=$val['folderalias'];
						$folders[]=$folder;
						}
					}
				}
			}
		if($update==true) $folders=mysql::getArray("SELECT * FROM `folders` WHERE 1=1 ORDER BY `order` ASC");
		return $folders;
	}

		
	// Удаление папки	
	static function delFolder($id){
		// Если папка является папкой по умолчанию, значит надо будет сделать новый раздел
		$default=mysql::getValue("SELECT `default` FROM `folders` WHERE id='".escape($id)."'");
		$array=mysql::getArray("SELECT id FROM `pages` WHERE folder='".escape($id)."' AND parent='0'");
		if($array!=false){
			foreach($array AS $val) {
				self::delete($val['id']);
            }
		}
		mysql::query("DELETE FROM `folders` WHERE id='".$id."' LIMIT 1");
		if($default==1){
			$id=mysql::getValue('SELECT id FROM `folders` WHERE 1=1 ORDER BY `order` ASC LIMIT 1');
			if($id!=false) mysql::query("UPDATE `folders` SET `default`='1' WHERE id='".escape($id)."'");
			}
		return true;
	}
}
