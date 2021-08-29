<?php
class csv {
	
	////////////////////////////////////////////////////////////////////////////////////
	// Класс для работы с CSV файлами
	// --------------------------------------------------------------------------------
	// Если файл существует, то производится добавление строк в его конец, а если нет - то 
	// файл создается и заполняется сначала заголовками, а затем данными.
	// --------------------------------------------------------------------------------
	// 
	// Примеры использования класса
	//	$headers = array( 0=>'ID', 1=>'Наименование', 2=>'Стоимость', 3=>'Скрытый' );
	//	$array = array(
	//		0=>array('id'=>1, 'name'=>'Первый тестовый товар', 'value'=>256, 'hidden'=>1),
	//		1=>array('id'=>2, 'name'=>'Второй тестовый товар', 'value'=>124, 'hidden'=>0)
	//	);
	//
	// csv::arrayToCsv( $_SERVER['DOCUMENT_ROOT'].'/cats.csv', $array, array_keys($array[0]), false );

	// Если файл существует, то происходит дописывание данных в него,
	// иначе - файл создается с нуля
    static function arrayToCsv($filename, $array, $headers=false, $unicode=true){
        if(file_exists($filename)) $mode='a+';
		else $mode='w';
        $fp = fopen($filename, $mode);
        //fwrite($fp, "\xEF\xBB\xBF");// UTF-8 BOM
        if($mode=='w') {
			if(is_array($headers)) self::my_fputcsv($fp, array_values($headers), $unicode);
		}
        if($array!=false){
            foreach($array AS $key=>$val){
                self::my_fputcsv($fp, array_values($val), $unicode);
                unset($array[$key]);
            }
        }
        fclose($fp);
        chmod($filename,0775);
    }


    // Обработка и вставка строки
    static function my_fputcsv($fp, $csv_arr, $unicode, $delimiter = ';', $enclosure = '"'){
      if (!is_array($csv_arr)) return(false);
      // обойдем все  элемены массива
      for ($i = 0, $n = count($csv_arr); $i < $n;  $i ++){
        // если это не  число
		if($unicode==false) $csv_arr[$i]=iconv('utf-8','windows-1251',$csv_arr[$i]);
        if (!is_numeric($csv_arr[$i])){
          // вставим символ  ограничения и продублируем его в теле элемента
          $csv_arr[$i] =  $enclosure.str_replace($enclosure, $enclosure.$enclosure,  $csv_arr[$i]).$enclosure;
        }
        // если  разделитель - точка, то числа тоже экранируем
        if (($delimiter == '.') && (is_numeric($csv_arr[$i]))) {
          $csv_arr[$i] =  $enclosure.$csv_arr[$i].$enclosure;
        }
      }
      // сольем массив в строку, соединив разделителем
	  $stringEnd="\n";
	  if($unicode==false) $stringEnd="\r\n";
      $str = implode($delimiter,  $csv_arr).$stringEnd;
      fwrite($fp, $str);
      // возвращаем  количество записанных данных
      return strlen($str);
    }

    // Получение содержимого файла в массив
    function  my_fgetcsv($fp, $length=0, $delimiter = ';', $enclosure = '"',  $escape=true){
      $csv_arr = fgetcsv($fp, $length, $delimiter,  $enclosure, $escape);
      return($csv_arr);
    }

}
