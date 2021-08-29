<?php
// 01.06.2015   Окончательный переход на MYSQLi
class mysql{
	
	static $numRows=0;
	public static $host='';
	public static $user='';
	public static $db='';
	public static $query='';
	static $queryLog=true;
	static $log=array();
	static $mysqli;
	static $resID;

	static function connect($host="", $user="", $pass="", $db=""){
	    global $settings;
		if($host=="" && $user=="" && $pass=="" && $db==""){
			if (getenv("COMSPEC")) {
				$host = $settings['databaseLocalHost'];
				$db   = $settings['databaseLocalName'];
				$user = $settings['databaseLocalUser'];
				$pass = $settings['databaseLocalPass'];
			} else {
				$host = $settings['databaseHost'];
				$db   = $settings['databaseName'];
				$user = $settings['databaseUser'];
				$pass = $settings['databasePass'];
			}
		}
		mysql::$host=$host;
		mysql::$user=$user;
		mysql::$db=$db;
		mysql::$mysqli=mysqli_connect($host,$user,$pass,$db) or die(mysqli_connect_error());
		mysqli_set_charset(mysql::$mysqli,'utf8') or die("Error: " . mysqli_error(mysql::$mysqli));
		if(isset($settings['debug'])){ if($settings['debug']==1){ mysql::$queryLog=true; }}
	}
		
	// public function getCounter(){
		// return $this->counter;
		// }
		
	// Отображение SHOW TABLE
	static function showCreateTable($tablename){
		$array=mysql::getArray("SHOW CREATE TABLE `".escape($tablename)."`",true);
		if($array!=false) return $array['Create Table'];
		return false;
	}

    /**
     * Сохранение ассоциативного массива в базу
     * @param $table - Имя таблицы
     * @param $array - Массив полей таблицы
     * @param string $keyName - Имя поля-ключа, по которому производится сравнение
     * @param bool $forceInsert - если TRUE, то операция INSERT даже если ID не равно 0 (для таблиц где ID без автоинкремента)
     * @return bool|mysqli_result
     */
    static function saveArray($table,$array,$keyName='id',$forceInsert=false){
		if(isset($array[$keyName])) {
			$id=$array[$keyName];
			if($forceInsert==false) unset($array[$keyName]);
			if($id==0 || $forceInsert!=false){
				$query="INSERT INTO `".escape($table)."` SET ";
				$where="";
			}
			else {
				$query="UPDATE `".escape($table)."` SET ";
				$where=" WHERE `".$keyName."`='".escape($id)."' LIMIT 1";
			}
			$fields=array();
			foreach($array AS $key=>$val) $fields[]="`".trim($key)."`='".escape($val)."'";
			return mysql::query($query.implode(",",$fields).$where);
		}
		else return false;
	}
		
	// Получение информации о полях таблицы
	static function showColumns($tablename){
		return mysql::getArray("SHOW FULL COLUMNS FROM `".escape($tablename)."`");
	}

	// Существует ли в заданной таблице поле $fieldname
	static function fieldExists($tablename,$fieldname){
	    $array=mysql::showColumns($tablename);
	    foreach($array AS $val){
	        if($val['Field']==$fieldname) return true;
	    }
	    return false;
	}
		
	// Информация о поле таблицы
	static function fieldInfo($table, $fieldname=false){
		$fields=false;
		$field=array();
		$array=mysql::showColumns($table);
		
		foreach($array AS $val){
			$field['field']=$val['Field'];
			$field['null']=0;
			$field['index']="---";
			$field['value']=$val['default'];
			$field['default']="As defined:";
			$field['attributes']="";
			$field['ai']=0;
			$field['comments']=$val['Comment'];
			if($val['Default']!=""){
				$field['default']="As defined:";
				$field['value']=$val['Default'];
			}
			if($val['Null']=="YES") $field['null']=1;
			if($val['Key']=="PRI") $field['index']="PRIMARY";
			if($val['Key']=="UNI") $field['index']="UNIQUE";
			if($val['Key']=="FULLTEXT") $field['index']="FULLTEXT";
			if($val['Key']=="MUL") $field['index']="INDEX";
			// Тип поля
			$type=str_replace("(", " (", $val['Type']);
			$type=str_replace(")", ") ", $type);
			$type=str_replace("  ", " ", $type);
			$a=explode(" ",$type);
			$field['type']=strtoupper(trim($a[0]));
			if(isset($a[1])){
				if (preg_match('!\((.*?)\)!',$a[1],$l)){
					if($l[1]!=false) $field['len']=$l[1];
				}
			}
			if(strpos($type, "unsigned zerofill")!==false) $field['attributes']="UNSIGNED ZEROFILL";
			if(strpos($type, "unsigned")!==false) $field['attributes']="UNSIGNED";
			if(strpos($type, "unsigned zerofill")!==false) $field['attributes']="UNSIGNED ZEROFILL";
			if(strpos($val['Extra'], "auto_increment")!==false) $field['ai']=1;
			if(strpos($val['Extra'], "CURRENT_TIMESTAMP")!==false) {
				$field['attributes']="on update CURRENT TIMESTAMP";
				$field['default']="As defined:";
			}
			if($fieldname!=false){
				//$val['Field']==$fieldname;
				return $field;
			}
			else $fields[$field['field']]=$field;
		}
	return $fields;
	}
	
	// Получение списка таблиц БД
	static function showTables($db=false, $renew=false){
	    static $dbTables=false;
		if($db===false) $db=mysql::$db;
		if($dbTables===false || $renew!=false){
            $dbTables=mysql::getArray("SELECT TABLE_NAME as `name`,
ENGINE as `engine`, ROW_FORMAT as `format`, TABLE_ROWS as `rows`, AVG_ROW_LENGTH as `rowlength`, 
DATA_LENGTH as `datalength`, INDEX_LENGTH as `indexlength`, AUTO_INCREMENT as `ai`, 
unix_timestamp(CREATE_TIME) as `crtime`, unix_timestamp(UPDATE_TIME) as `uptime`, TABLE_COLLATION as `collation`, TABLE_COMMENT as `comment` 	
FROM `information_schema`.`tables`
WHERE `table_schema` = '".escape($db)."' 
ORDER BY TABLE_NAME ASC");
        }
        return $dbTables;
	}

	// Возвращает список внешних ключей таблицы
	static function getForeignKeys($tablename){
	    $array = mysql::getArray("SHOW CREATE TABLE `".$tablename."`",true);
        $lines = explode ("\n",$array['Create Table']);
        $out=array();
        foreach ($lines as $l){
            if (strpos($l , 'FOREIGN KEY') !== false){
                $v = $l;
                $p = strpos($l , 'FOREIGN KEY');
                $l = substr($l , $p + (strlen('FOREIGN KEY')));
                $z = explode("`",$v);
                $ll = $z[1];
                $l = trim ($l);
                $p = strpos($l , ' ');
                $keyName=trim (substr($l , 0 , $p) , "('`\"[{}]`') ");
                $p = strpos($l , 'REFERENCES');
                $l = trim(substr($l , $p + (strlen('REFERENCES'))));
                $p = strpos($l , ' ');
                $masterTable=trim(substr($l , 0 ,  $p), "('`\"[{}]`') ");
                $l = substr($l , $p + 1);
                $p = strpos($l , ')');
                $masterField= trim(substr($l , 0 ,  $p), "('`\"[{}]`') ");
                $acts=explode(')',str_replace(',','',$l));
                $out[$keyName]=array('key'=>trim($ll), 'link'=>trim($masterTable).'.'.trim($masterField), 'acts'=>trim($acts[1]));
            }
        }
        return $out;
	}

    // Существует ли таблица
	static function tableExists($tablename,$renew=false){
	    static $tables;
	    if(!isset($tables) || $renew!=false) {
	        $tables=array();
            $tables=mysql::showTables();
        }
	    foreach($tables AS $val){
	        if($val['name']==$tablename) return true;
	    }
	    return false;
	}

	// Вывод EXPLAIN с рекомендациями
	static function explain($query){
	    $explain=mysql::getArray("EXPLAIN EXTENDED ".str_ireplace("extended","",str_ireplace("explain","",$query)));
	    $out='';
	    $headers='';
	    $fields='';
	    $hnames=array('id'=>'порядковый идентификатор каждого SELECT, находящегося внутри запроса (в случае использования вложенных подзапросов)','select_type'=>'Тип запроса','table'=>'Таблица, к которой относится текущая строка','type'=>'Тип связывания таблиц','possible_keys'=>'Возможные индексы для поиска данных. Значение помогает оптимизировать запросы. Если NULL, то индексы не используются.', 'key'=>'Текущие ключи. Могут отображаться индексы, отсутствующие в possible_keys. Оптимизатор MySQL всегда пытается найти оптимальный ключ.','key_len'=>'Длина ключа. По значению длины можно определить, сколько частей составного ключа в действительности будет использовать MySQL', 'ref'=>'Поля и константы, которые используются совместно с ключом, указанным в key', 'rows'=>'Количество строк, которые анализируются в процессе запроса. Это важный показатель, указывающий на необходимость оптимизации запросов, особенно тех, которые содержат JOIN и подзапросы.','Extra'=>'Доп. информация о процессе выполнения запроса. Если значениями этого столбца являются ”Using temporary”, “Using filesort” - запрос требует оптимизации.','filtered'=>'Показывает примерно, какой процент строк таблицы будет удовлетворять условию и присоединится к результату.');
	    // Тип выборки
	    $info['select_type']=array(
	        'SIMPLE'=>'Простая выборка, без подзапросов и UNION\'ов|000000',
	        'PRIMARY'=>'Внешний запрос в JOIN|000000',
	        'DERIVED'=>'Запрос SELECT - часть подзапроса внутри выражения FROM|333333',
	        'SUBQUERY'=>'Первый SELECT в подзапросе|333333',
	        'DEPENDENT SUBQUERY'=>'Первый SELECT, зависящий от внешнего подзапроса|333333',
	        'UNCACHEABLE SUBQUERY'=>'Некэшируемый подзапрос|333333',
	        'UNION'=>'SELECT является вторым или последующим в UNION|333333',
	        'DEPENDENT UNION'=>'SELECT является вторым или последующим запросом в UNION и зависит от внешних запросов|333333',
	        'UNION RESULT'=>'SELECT является результатом UNION\'а|333333'
	    );
	    // Тип связывания таблиц
	    $info['type']=array(
	        'system'=>'Cодержит только одну строку (системная таблица)|52cd00',
	        'const'=>'Cодержит не более одной соответствующей строки, которая будет считываться в начале запроса, поэтому оптимизатор в дальнейшем может расценивать значения этой строки в столбце как константы. Таблицы const - очень быстрые, т.к. они читаются только однажды|52cd00',
	        'eq_ref'=>'Для каждой комбинации строк из предыдущих таблиц будет cчитываться 1 строка этой таблицы. Это наилучший тип связывания среди типов, отличных от const. Применяется, когда все части индекса используются для связывания, а сам индекс - UNIQUE или PRIMARY KEY|52cd00',
	        'ref'=>'Из таблицы считываются ВСЕ строки с совпадающими значениями индексов для каждой комбинации строк из предыдущих таблиц. Применяется, если для связывания используется только крайний левый префикс ключа, или если ключ не является UNIQUE или PRIMARY KEY (другими словами, если на основании значения ключа для связывания не может быть выбрана одна строка). Работает хорошо, если используемый ключ соответствует только нескольким строкам|c0d100',
	        'fulltext'=>'Объединение, использующее полнотекстовый (FULLTEXT) индекс таблиц|000000',
	        'ref_or_null'=>'Из таблицы считываются ВСЕ строки с совпадающими значениями индексов для каждой комбинации строк из предыдущих таблиц. Применяется, если для связывания используется только крайний левый префикс ключа, или если ключ не является UNIQUE или PRIMARY KEY (другими словами, если на основании значения ключа для связывания не может быть выбрана одна строка). Работает хорошо, если используемый ключ соответствует только нескольким строкам|c0d100',
	        'index_merge'=>'Объединение, использующее список индексов для получения результата запроса|000000',
	        'unique_subquery'=>'Результат подзапроса в выражении IN. Возвращает одну строку, используемую в качестве первичного ключа|000000',
	        'index_subquery'=>'Результат подзапроса в выражении IN. Возвращает несколько строк, используемых в качестве первичного ключа|000000',
	        'range'=>'В запросе происходит сравнение ключевого поля с диапазоном значений (используются операторы BETWEEN,IN, >, >=)|000000',
	        'index'=>'Cканируется только дерево индексов.|52cd00',
	        'ALL'=>'Cканируются все записи таблицы. Худший тип объединения, обычно указывает на отсутствие индексов в таблице|FF0000'
	    );
        // EXTRA
	    $info['Extra']=array(
	        'Distinct'=>'Cодержит только одну строку (системная таблица)|52cd00',
	        'Not exists'=>'MySQL смог осуществить оптимизацию LEFT JOIN для запроса и после нахождения одной строки, соответствующей критерию LEFT JOIN, не будет искать в этой таблице последующие строки для предыдущей комбинации строк.|c0d100',
	        'range checked for each record'=>'MySQL не нашел достаточно хорошего индекса для использования. Вместо этого для каждой комбинации строк в предшествующих таблицах он будет проверять, какой индекс следует использовать (если есть какой-либо индекс), и применять его для поиска строк в таблице. Это делается не очень быстро, но таким образом таблицы связываются быстрее, чем без индекса.|000000',
	        'Using filesort'=>'Выполняется просмотр всех строк согласно типу связывания|ff0000',
	        'Using index'=>'Используется только индекс и нет необходимости производить собственно чтение записи|52cd00',
	        'Using temporary'=>'Создается временная таблица. Это происходит например, если ORDER BY выполняется для набора столбцов, отличного от того, который используется в предложении GROUP BY|ff0000',
	        'Using where'=>'WHERE будет использоваться для выделения строк, сопостовляемых со следующей таблицей или тех, которые будут посланы клиенту. Если этой информации нет, а таблица имеет тип ALL или index - в вашем запросе есть какая-то ошибка (если вы не собираетесь делать выборку/тестирование всех строк таблицы)|333333'
	        );


	    if($explain!=false){
	        $rcount=1;
	        foreach($explain AS $k=>$v){
	            $fields.='<tr>';
	            foreach($v AS $key=>$val){
	                if($k==0) {
	                    $htitle='';
	                    if(isset($hnames[$key])) $htitle=' title="'.$hnames[$key].'"';
                        $headers.='<th'.$htitle.'>'.$key.'</th>';
                    }
                    $comment='';
                    $color='000000';
                    if($key=="type" || $key=="select_type"){
                        foreach($info[$key] AS $kk=>$vv){
                            if($val==$kk){
                                list($comment,$color)=explode("|",$vv);
                                break;
                            }
                        }
                        if($comment!='') $comment=' title="'.$comment.'"';
                        $fields.='<td style="color:#'.$color.'"'.$comment.'>'.$val.'</td>';
                    }
                    elseif($key=="Extra"){
                        foreach($info['Extra'] AS $kk=>$vv){
                            //list($comment,$color)=explode("|",$vv);
                            if (preg_match("/".$kk."/i", $val)) {
                                list($comment,$color)=explode("|",$vv);
                                $val=str_ireplace($kk,'<span style="color:#'.$color.'" title="'.$comment.'">'.$kk.'</span>',$val);
                            }
                        }
                        $fields.='<td>'.$val.'</td>';
                    }
                    else $fields.='<td'.$comment.'>'.$val.'</td>';
                    if($key=='rows') $rcount=$rcount*$val;
	            }
	            $fields.='</tr>';
	        }
	        $out.='<table border="1" cellpadding="4" cellspacing="0" style="width:100%; border:1px solid #000000; font:normal 12px \"Arial\", sans-serif; color:#333333; border-collapse:collapse"><tr style="background:#eeeeee">'.$headers.'</tr>'.$fields.'<tr><td colspan="8"></td><td style="background:#000000; color:#ffffff;" title="Примерное количество строк, проанализированных в процессе запроса. В идеале, их количество не должно превышать количество строк результата">'.$rcount.'</td><td colspan="2"></td></tr></table>';
	    }
	    return $out;
	}

    // Возвращает список индексов таблицы
    static function showIndex($table){
        return mysql::getArray("SHOW INDEX FROM `".$table."`");
    }


	// Начало транзакции
	static function startTransaction(){
		mysqli_query(mysql::$mysqli,"SET AUTOCOMMIT = 0");
		mysqli_query(mysql::$mysqli,"START TRANSACTION");
    }
		
	static function stopTransaction(){
		mysqli_query(mysql::$mysqli,"SET AUTOCOMMIT = 1");
	}
	
	// обычный запрос
	static function query($query){
		//echo $query."<br>";
		mysql::$query=$query;
		return mysql::queryProcess($query);
    }
		
	
	static function counter($inc=false){
		static $counter=0;
		if($inc==true) $counter++;
		return $counter;
	}
		
	// Возвращает или устанавливает код последней ошибки MySQL
	static function errno($errcode=false){
		static $errno=0;
		if($errcode!==false) $errno=$errcode;
		return $errno;
	}
		
	static function error($err=false){
		static $error=array();
		if($err!=false) $error[]=$err;
		else {
			if(isset($error[0])) return implode('<br>',$error);
		}
		return false;
	}
	
	// Возвращает ID последней вставленной записи
	static function insertId(){
		return mysqli_insert_id(mysql::$mysqli);
    }
		
	// Кол-во затронутых рядов
	static function affectedRows(){
		return mysqli_affected_rows(mysql::$mysqli);
    }
		
	// Кол-во рядов удовлетворяющих условию
	// Если в запросе указано SQL_CALC_FOUND_ROWS, то MySQL возвращает также к-во рядов удовлетворя
	static function foundRows(){
		return mysql::getValue('SELECT FOUND_ROWS()');
	}
		
	// Кол-во рядов в результате	
	static function numRows(){
		return mysql::$numRows;
	}
		
	static function queryProcess($query){
	    global $settings;
	    if(isset($settings['debug'])){ if($settings['debug']==1){ mysql::$queryLog=true; }}
		$count=true;
		if(function_exists("cacheClear")){
            if(stripos($query,"DELETE ")!==false) cacheClear();
            if(stripos($query,"UPDATE ")!==false) cacheClear();
            if(stripos($query,"INSERT INTO ")!==false) cacheClear();
            if(stripos($query,"DROP ")!==false) cacheClear();
        }
		if(stripos($query,"SET NAMES")!==false) $count=false;
		if($query=="SELECT last_insert_id()") $count=false;
		if($query=="commit" || $query=="COMMIT" || $query=="rollback" || $query=="ROLLBACK") $count=false;
		if($count==true) mysql::counter(true);
		ob_start();
		$result=mysqli_query(mysql::$mysqli,$query) or mysql::error(mysqli_error(mysql::$mysqli));
		$errno=mysqli_errno(mysql::$mysqli);
		if($query=="commit" || $query=="COMMIT" || $query=="rollback" || $query=="ROLLBACK") mysql::stopTransaction();
		mysql::$query=$query;
        if(mysql::$queryLog==true) mysql::$log[]=$query;
        ob_get_contents();
		mysql::errno($errno);
		if($errno!=0) {
			@mysql::$numRows=mysqli_num_rows($result);
			echo '<div class="error">'.mysqli_error(mysql::$mysqli).' <br>In query:<br>'.$query.'</div>';
			if (getenv ("COMSPEC")) {
			    $errorlog=$_SERVER['DOCUMENT_ROOT'].'/axiom_error.log';
			    if(!file_exists($errorlog)) $f='';
			    else {
                    if(filesize($errorlog)<100000) $f=trim(implode('',file($errorlog)));
                    else $f='';
                }
			    if(mb_strlen($f,'utf-8')>=50000) $f=mb_substr($f,20000,10000,'utf-8');
			    $f.='
'.date("d.m.Y H:i:s",time())."\n".str_replace("\n","", mysqli_error(mysql::$mysqli))."\n IN QUERY \n\n".$query."\n-------------------------------------------------------------\n\n";
			    $f=str_replace('

','
',$f);
                file::save($errorlog,$f);
            }
			return false;
		}
		return $result;
	}

	// Возвращает ассоциативный массив
	// Если first=true то возвращает только первый элемент массива
	static function getArray($query,$first=false){
		global $site;
		global $settings;
		// Система кэширования
		$caching=$settings['enableCaching'];
		if(isset($site->page['nocache'])){ if($site->page['nocache']==1) $caching=0; }
		if($caching==1){
			if(stripos($query,"SELECT ")) $caching=1;
			if(stripos($query,"last_insert_id")) $caching=0;
			if($caching==1){
				$cache=cacheRead($query);
				if($cache!==false) return $cache;
			}
		}
		$result=mysql::queryProcess($query);
		if(@mysqli_num_rows($result)>=1){
			for($array=array(); $row=mysqli_fetch_assoc($result); $array[]=$row);
			mysqli_free_result($result);
			if($first==false) $return=$array;
			else $return=$array[0];
			//if($caching==1) cacheSave($query,$return);
		}
		else $return=false;
		return $return;
	}

	// Функция извлекает из массива поля ID и NAME и возвращает
	// массив в виде ID => NAME
	static function getSpecial($query){
	    $array=mysql::getArray($query);
	    if($array!=false){
	        if(isset($array[0]['id']) && isset($array[0]['name'])){
	            $out=array();
	            foreach($array AS $val){
	                $out[$val['id']]=$val['name'];
	            }
	            return $out;
	        }
	        else {
	            mysql::error("mysql::getSpecial() ERROR: Fields ID and NAME not exists in query: ".$query."<br>");
                return false;
            }
	    }
	    else return false;
	}
		
	// Возвращает массив-список	
	static function getList($query){
		$result=mysql::queryProcess($query);
		if(@mysqli_num_rows($result)>=1){
			for($array=array(); $row=mysqli_fetch_row($result); $array[]=$row[0]);
			mysqli_free_result($result);
			return $array;
		}
		else return false;
	}
		
	// Получение значения одного элемента из базы
	// пример: SELECT name FROM `users` WHERE id=1 LIMIT 1
	static function getValue($query){
		$result=mysql::queryProcess($query);
		if(@mysqli_num_rows($result)>=1){
			$ret=mysql::result($result,0,0);
			mysqli_free_result($result);
			return $ret;
		}
		else return false;
	}

	// Аналог устаревшей функции mysql_result для mysqli
	static function result($result,$row,$field=0) {
        if ($result===false) return false;
        if ($row>=mysqli_num_rows($result)) return false;
        if (is_string($field) && !(strpos($field,".")===false)) {
            $t_field=explode(".",$field);
            $field=-1;
            $t_fields=mysqli_fetch_fields($result);
            for ($id=0;$id<mysqli_num_fields($result);$id++) {
                if ($t_fields[$id]->table==$t_field[0] && $t_fields[$id]->name==$t_field[1]) {
                    $field=$id;
                    break;
                }
            }
            if ($field==-1) return false;
        }
        mysqli_data_seek($result,$row);
        $line=mysqli_fetch_array($result);
        return isset($line[$field])?$line[$field]:false;
    }
		
	// Получение списка, состоящего из одного поля (первого)
	// пример: SELECT name FROM `users` WHERE id IN(1,25,1000)
	static function getColumn($query){
		$result=mysql::queryProcess($query);
		if(@mysqli_num_rows($result)>=1){
			for($array=array(); $row=mysqli_fetch_row($result); $array[]=$row[0]);
			mysqli_free_result($result);
			return $array;
		}
		else return false;
	}
	
	// Получение нового значения ORDER для таблицы
	static function newOrder($tablename,$where=false){
        if($where===false) $where='';
        else $where=' WHERE '.$where.' ';
		$order=mysql::getValue("SELECT MAX(`order`) FROM `".escape($tablename)."`".$where." LIMIT 1");
        if($order===false) $order=1;
		$order++;
		return $order;
	}

    // Сохранение ORDER для рядов таблицы
    // $table - Имя таблицы MySQL
    // $idString - строка упорядоченных ID элементов, например 15,17,19,28,29,30,31,32
    // На выходе - к-во перемещенных рядов таблицы (к-во запросов к базе)
    static function orderUpdate($table,$idString){
        $old=mysql::getArray("SELECT `id`,`order` FROM `".escape($table)."` WHERE id IN (".escape($idString).") ORDER BY `order` ASC");
        $ordered=0;
        $order=explode(',',$idString);
        if($old!=false && count($order)==count($old)){
            foreach($order AS $key=>$val){
                if($old[$key]['id']!=$val){
                    $ordered++;
                    mysql::query("UPDATE `".escape($table)."` SET `order`='".escape($old[$key]['order'])."' WHERE id='".escape($val)."' LIMIT 1");
                }
            }
        }
        return $ordered;
    }
	
	// Автоматическое экранирование в зависимости от типа данных
	static function escape($string){
		// Если данные являются числом, или числовой строкой
		if(is_numeric($string)){
			if(is_int($string)) return intval($string);
			else return mysqli_real_escape_string(mysql::$mysqli,$string);
		}
		return mysqli_real_escape_string(mysql::$mysqli,$string);
	}
	
	// Получение размера БД
	static function dbSize(){
		return mysql::getValue('SELECT(SUM(DATA_LENGTH) + SUM(INDEX_LENGTH)) AS size FROM information_schema.tables WHERE table_schema =  "'.mysql::$db.'"');
	}


    // Удаление индекса по заданному полю
    static function indexDrop($table,$field,$renew=false){
        static $existIndexes;
        if(!isset($existIndexes) || $renew==true) $existIndexes=array();
        if(!isset($existIndexes[$table])){
            // Если нет данных по заданной таблице, то получим список индексов
            $i=mysql::getArray("SHOW INDEX FROM  `".$table."`");
            if($i!=false){
                $array=array();
                foreach($i AS $val) $array[$val['Key_name']]=$val['Column_name'];
                if(!empty($array)) $existIndexes[$table]=$array;
            }
        }
        if(isset($existIndexes[$table][$field])){
            mysql::query("ALTER IGNORE TABLE `".$table."` DROP INDEX `".$field."`");
            unset($existIndexes[$table][$field]);
            return true;
        }
        return false;
    }

    // Добавление индекса по заданному полю
    static function indexAdd($table, $field){
        static $existIndexes;
        if(!isset($existIndexes)) $existIndexes=array();
        if(!isset($existIndexes[$table])){
            // Если нет данных по заданной таблице, то получим список индексов
            $i=mysql::getArray("SHOW INDEX FROM  `".$table."`");
            if($i!=false){
                $array=array();
                foreach($i AS $val) $array[$val['Key_name']]=$val['Column_name'];
                if(!empty($array)) $existIndexes[$table]=$array;
            }
        }
        if(!isset($existIndexes[$table][$field])){
            mysql::query("ALTER IGNORE TABLE `".$table."` ADD INDEX ( `".$field."` )");
            $existIndexes[$table][$field]=$field;
            return true;
        }
        return false;
    }
}
