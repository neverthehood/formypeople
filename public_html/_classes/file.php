<?php
// Класс для работы с файловой системой
class file{

	/**
	*   Получение уникального имени для файла
	*   @param string $path - путь к папке, где будет осуществлена проверка
	*   @param string $fileName - исходное имя файла
	*   @return string - уникальное имя файла
	*/
	static function getUniqName($path, $fileName){
		$num = 1; // счетчик
		if(file_exists($path.$fileName)){
			while(file_exists($path.$num . '_' . $fileName)) $num ++;
			return $num . '_' . $fileName;
		}
		else return $fileName;
	}

    // Получение нового уникального имени файла,
    // Актуально, если файл с заданным именем существует
    // myfile.jpg
    // myfile_0.jpg
    // myfile_1.jpg
    function file_newname($path, $filename){
        if ($pos = strrpos($filename, '.')){
            $name = substr($filename, 0, $pos);
            $ext = substr($filename, $pos);
        }
        else {
            $name = $filename;
            $ext='';
        }
        $newpath = $path.'/'.$filename;
        $newname = $filename;
        $counter = 0;
        while (file_exists($newpath)){
            $newname = $name .'_'. $counter . $ext;
            $newpath = $path.'/'.$newname;
            $counter++;
        }
        return $newname;
    }
	
	
	// Возвращает true если папка существует
	static function folderExists($folder){
        $path = realpath($folder);
        return ($path !== false AND is_dir($path)) ? $path : false;
    }

	// Если не задан массив-список доступных расширений - Возвращает расширение файла
	// А если задан массив-список разрешенных, то:
	// расширение - если оно допустимо, или false
	// Корректно обрабатывает множественные расширения, например file.txt.exe
	static function getExtension($fname,$allowExt=false){
		$ext=strtolower(preg_replace("/.*?\./", '', $fname));
		if($allowExt===false) return $ext;
		else {
			if(!is_array($allowExt)) {
				$allowExt=str_replace(' ','',$allowExt);
				$allowExt=explode(',',str_replace('.','',$allowExt));
		    }
			if(is_array($allowExt)){
				foreach($allowExt AS $val){ if($val==$ext) { return $val; break; } }
			}
		}
		return false;
	}
	
		
	// возвращает все файлы каталога и вложенных в него
	//////////////////////////////////////////////////////////////////////////////
	static function listFiles($from,$showDir=false){
		global $error;
		if(!file_exists($from)){
			$error.="Directory $from is not found!<br>";
			return false;
		}
		else {
			if(!is_dir($from)) return false;
			$files = array();
			$dirs = array( $from);
			while( NULL !== ($dir = array_pop( $dirs))){
			    if( $dh = opendir($dir)){
				    while( false !== ($file = readdir($dh))){
					    if( $file == '.' || $file == '..') continue;
						$path = $dir . '/' . $file;
						if( is_dir($path)) {
                            $dirs[] = $path;
                            if($showDir!=false){
                                $files[]=$file;
                            }
                        }
					    else $files[] = $file;
					}
				    closedir($dh);
			    }
			}
			asort($files);
			return $files;
		}
	}

	// Получение всех файлов и папок в заданной директории
	// Если задан параметр $recursive=true,
	// То будут получены ВСЕ ВЛОЖЕННЫЕ файлы и папки
	static function dir($addr,$recursive=false){
	    $out=array();
	    if(is_dir($addr)){
	        $m=scandir($addr);
	        if(is_array($m)){
	            foreach($m AS $val){
	                if($val!='.' && $val!='..'){
	                    $array=array();
	                    $array['name']=$val;
	                    if(is_dir($addr.'/'.$val)) {
                            $array['is_dir']=true;
                            if($recursive!=false){
                                $array['files']=file::dir($addr.'/'.$val.'/');
                            }
                        }
                        else {
                            $array['is_dir']=false;
                            $n=stat($addr.'/'.$val);
                            if($n!=false){
                                $array['uid']=$n['uid'];// Владелец
                                $array['gid']=$n['gid'];// Группа владельца
                                $array['size']=$n['size'];//
                                $array['atime']=$n['atime'];// время доступа
                                $array['mtime']=$n['mtime'];// время модификации
                                $array['сtime']=$n['сtime'];
                                $array['chmod']=file::perms($addr.'/'.$val);// Режим защиты
                                $array['writable']=is_writable($addr.'/'.$val);
                            }
                        }
                        $out[]=$array;
                    }
	            }
	            return $out;
	        }
	    }
	    return false;
	}

	// Получение прав на файл в виде 0775
	static function perms($filename){
        return substr(sprintf('%o', fileperms($filename)), -4);
	}

    // alias для функции chmod (не понятно, зачем.....)
	static function chmod($filename,$chmod=0644){
	    global $error;
		@chmod($filename,$chmod) or $error.="Can't execute CHMOD command!<br>";
	}
	
	// сохранение файла
	// Если файла нет, то он создается
	static function save($filename,$string='',$chmod=false){
		global $error;
		fclose(fopen($filename,"a+b"));
		$file=fopen($filename,"r+") or $error.="Can't open file $filename!<br>";
		flock ($file,LOCK_EX);
		ftruncate($file,0);
		fwrite($file,$string);
		fflush($file);
		fclose($file);
		if($chmod!=false) file::chmod($filename,$chmod);
		return true;
	}
		
	// Возвращает правильное название b, kb, mb и т.д.
	static function bytes($bytes,$precision=2){
		$units = array('b.', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes?log($bytes):0)/log(1024));
		$pow = min($pow, count($units)-1);
		$bytes /= pow(1024, $pow);
		return round($bytes,$precision).' '.$units[$pow];
	}
	
	// Быcтрое чтение файла
	static function read($file){
		global $error;
		if(file_exists($file)) return implode('',file($file));
		else {
			$error.=sprintf(MESSAGE('ERROR_fileNotFound'),$file);
			return false;
		}
	}
	
	
	// Рекурсивное удаление директории и всего, что в ней
	static function dirDelete($dire){
		global $error;
		$ret=true;
		if(is_dir($dire)){
			$dir  = opendir($dire);  
			while (false !== ($filename = readdir($dir))){
				if($filename!='.' && $filename!='..'){
					if(is_file($dire."/".$filename))  @unlink($dire."/".$filename); 
					if(is_dir($dire."/".$filename))  file::dirDelete($dire."/".$filename); 
				} 
			}  
			closedir($dir); 
			rmdir($dire); 
		} 
		if(is_dir($dire)) {
			$error.='file :: dirRemove() ERROR: Can\'t delete directory '.$dire.' <br>';
			$ret=false;
		}
		return $ret;
	}
	
}
