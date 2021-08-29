<?php
// класс для работы с изображениями
/**
 * Class image
 */
class image {

	static $chmod=0777;				// Права на файлы изображений по умолчанию
	static $error='';				// Накопитель ошибок
	static $maxWidth=1000;			// Максимальная ширина
	static $maxHeight=700;			// Максимальная высота
	static $iconMaxWidth=120;		// Макс. ширина миниатюры / Макс. размер миниатюры (квадрат)
	static $iconMaxHeight=120;		// Макс. высота миниатюры
	static $jpgQuality=95;			// Качество JPEG изображений по умолчанию
	static $watermark=false;		// defaultWatermarkImage

// Функция возвращает картинку-превью или true в случае записи, для заданного видеоролика Youtube	
// Если задан второй параметр - имя файла, то изображение также копируется в файл
static function getYoutubePreview($videoUrl,$save=false){
if (preg_match('/[http|https]+:\/\/(?:www\.|)youtube\.com\/watch\?(?:.*)?v=([a-zA-Z0-9_\-]+)/i', $videoUrl, $matches) || preg_match('/(?:www\.|)youtube\.com\/embed\/([a-zA-Z0-9_\-]+)/i', $videoUrl, $matches)) {
		$image = 'https://img.youtube.com/vi/'.$matches[1].'/mqdefault.jpg';
	}
	else $image = false;
	if($image!=false && $save!=false){
		file::save($save,file_get_contents($image),0777);
		return true;
	}
	return $image;
}
	
/*
* Ресайз картинки PNG с сохранением прозрачности
*
* $source – исходное изображение
* $path – путь для сохранения новой картинки
* $height – новая высота
* $width – новая ширина
* $formatImg - расширение картинки, для сохранения.
*/
static function resizePhotoPNG($source, $path, $height, $width, $formatImg = 'png'){
    $rgb = 0xffffff; //цвет заливки фона
    $size = getimagesize($source);//узнаем размеры исходной картинки
    //определяем тип (расширение) картинки
    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
    $icfunc = "imagecreatefrom" . $format;   //определение функции для расшерения файла
    //если нет такой функции, то прекращаем работу скрипта
    if (!function_exists($icfunc)) return false;
    $x_ratio = $width / $size[0]; //пропорция ширины
    $y_ratio = $height / $size[1]; //пропорция высоты
    $ratio = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio); //соотношения ширины к высоте
    $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio); //ширина
    $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio); //высота
    //расхождение с заданными параметрами по ширине
    $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
    //расхождение с заданными параметрами по высоте
    $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
    //создаем вспомогательное изображение пропорциональное картинке
    $img = imagecreatetruecolor($width, $height);
    // делаем его прозрачным
    imagealphablending($img, false); 
    imagesavealpha($img, true);
    $photo = $icfunc($source); //достаем наш исходник
    imagecopyresampled($img, $photo, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]); //копируем на него картинку с учетом расхождений
    $func = 'image'.$formatImg;
    $func($img, $path); //сохраняем результат
    // Очищаем память после выполнения скрипта
    imagedestroy($img);
    imagedestroy($photo);
    // вернем путь для картинки
    return $path;
}
	
	
// Создает скриншот заданного сайта и сохраняет в файл
// @var $url string - адрес сайта
// @var $screen string - размер экрана, может принимать только ширину. И может принимать ширину и высоту - 1024x768
// @var $size integer - ширина масштабированной картинки
// @var $format string - может принимать два значения (JPEG|PNG), по умолчанию "JPEG"
static function getScreenShot($url, $screen, $size, $format = "jpeg", $file=false){
    if($file===false) $filename=$_SERVER['DOCUMENT_ROOT']."/uploaded/screen.jpg";
    else $filename=$file;
    $result = "http://mini.s-shot.ru/".$screen."/".$size."/".$format."/?".$url; // делаем запрос к сайту, который делает скрины
    $pic = file_get_contents($result); // получаем данные. Ответ от сайта
    file_put_contents($filename, $pic); // сохраняем полученную картинку
}

	// Функция повышает резкоcть аналогично фильтру фотошопа UnsharpMask
	// Довольно большая нагрузка на сервер, поэтому выполнять лучше только с миниатюрами.  На входе - идентификатор картинки и параметры amount, radius, threshold
	// $img = imagecreatefromjpeg('photo.jpg');
	// $img = unsharpMask($img, 5, 0.5, 5);
	// header('Content-Type: image/jpeg');
	// imagejpeg($img);
    /**
     * @param $fromimage
     * @param int $amount
     * @param float $radius
     * @param int $threshold
     * @return bool
     */
    static function unsharpMask($fromimage, $amount=40, $radius=0.5, $threshold=3) {
		$err="";
		global $error;
		$img=imageCreateFromJpeg($fromimage);
		if(!$img) $err.="unsharpMask: Не удалось открыть изображение!<br>";
		else {
			if ( $amount>500 ) $amount = 500;
			$amount = $amount * 0.016;
			if ( $radius>50 ) $radius = 50;
			$radius = $radius * 2;
			if ( $threshold>255 ) $threshold = 255;
			$radius = abs(round($radius));
			if ( $radius==0 ) return true;
			$w = imagesx($img);
			$h = imagesy($img);
			$imgCanvas = imagecreatetruecolor($w, $h);
			$imgBlur = imagecreatetruecolor($w, $h);
			if ( function_exists('imageconvolution') ) {
				$matrix = array(
					array(1, 2, 1),
					array(2, 4, 2),
					array(1, 2, 1)
				);
				imagecopy($imgBlur, $img, 0, 0, 0, 0, $w, $h); 
				imageconvolution($imgBlur, $matrix, 16, 0);  
			} else {
				for ( $i=0; $i<$radius; $i++ ) { 
					imagecopy($imgBlur, $img, 0, 0, 1, 0, $w-1, $h);
					imagecopymerge($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50);
					imagecopymerge($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50);
					imagecopy($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 
					imagecopymerge($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 );
					imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25);
				} 
			} 

			if( $threshold>0 ){ 
				for ( $x=0; $x<$w-1; $x++ ) { // each row
					for ($y=0; $y<$h; $y++ ) { // each pixel
						$rgbOrig = ImageColorAt($img, $x, $y); 
						$rOrig = (($rgbOrig >> 16) & 0xFF); 
						$gOrig = (($rgbOrig >> 8) & 0xFF); 
						$bOrig = ($rgbOrig & 0xFF); 
						$rgbBlur = ImageColorAt($imgBlur, $x, $y); 
						$rBlur = (($rgbBlur >> 16) & 0xFF); 
						$gBlur = (($rgbBlur >> 8) & 0xFF); 
						$bBlur = ($rgbBlur & 0xFF); 
						$rNew = ( abs($rOrig-$rBlur)>=$threshold ) ? max(0, min(255, ( $amount*($rOrig-$rBlur) )+$rOrig)) : $rOrig;
						$gNew = ( abs($gOrig-$gBlur)>=$threshold ) ? max(0, min(255, ( $amount*($gOrig-$gBlur) )+$gOrig)) : $gOrig;
						$bNew = ( abs($bOrig-$bBlur)>=$threshold ) ? max(0, min(255, ( $amount*($bOrig-$bBlur) )+$bOrig)) : $bOrig; 
						if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) { 
							$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew); 
							ImageSetPixel($img, $x, $y, $pixCol); 
						} 
					} 
				} 
			} else {
				for ( $x=0; $x<$w; $x++ ) {
					for ( $y=0; $y<$h; $y++ ) {
						$rgbOrig = ImageColorAt($img, $x, $y); 
						$rOrig = (($rgbOrig >> 16) & 0xFF); 
						$gOrig = (($rgbOrig >> 8) & 0xFF); 
						$bOrig = ($rgbOrig & 0xFF); 
						$rgbBlur = ImageColorAt($imgBlur, $x, $y); 
						$rBlur = (($rgbBlur >> 16) & 0xFF); 
						$gBlur = (($rgbBlur >> 8) & 0xFF); 
						$bBlur = ($rgbBlur & 0xFF); 
						$rNew = ( $amount*($rOrig-$rBlur) ) + $rOrig; 
						if ( $rNew>255 ) $rNew=255; elseif ( $rNew<0 ) $rNew=0;
						$gNew = ( $amount*($gOrig-$gBlur) ) + $gOrig;
						if ( $gNew>255 ) $gNew=255; elseif ( $gNew<0 ) $gNew=0;
						$bNew = ( $amount*($bOrig-$bBlur) ) + $bOrig;
						if ( $bNew>255 ) $bNew=255; elseif ( $bNew<0 ) $bNew=0;
						$rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew; 
						ImageSetPixel($img, $x, $y, $rgbNew); 
					} 
				} 
			} 
		imagedestroy($imgCanvas); imagedestroy($imgBlur);
		// сохраняем картинку
		@unlink ($fromimage);
		if(!imageJpeg($img,$fromimage,image::$jpgQuality)) {
			$error.="unsharpMask: Не удалось сохранить изображение!<br>";
			}
		@chmod($fromimage,image::$chmod);
		}
	if($err=="") return true;
	else {
		$error.=$err;
		return false; 
		}
	}

	// перевод цветного изображения в ЧБ посредствам фильтра GDLIB
	// на входе исходный и результирующий файлы 
    /**
     * @param $source
     * @param bool $destination
     * @return bool
     */
    static function greyscale($source,$destination=false){
		$r=false;
		if($destination==false) $destination=$source;
		$im = imagecreatefromjpeg($source);
		if ($im && imagefilter($im, IMG_FILTER_GRAYSCALE)) {
			imagejpeg($im,$destination);
			$r=true;
		}
		else $r=false;
		imagedestroy($im);
		return $r;
	}

	/////////////////////////////////////////////
	// Создание квадратной иконки из изображения файла $from
	// файл-источник, файл-приемник, размер
    /**
     * @param $from
     * @param $file
     * @param bool $size
     * @return bool
     */
    static function makeSquareIcon($from,$to,$size=false){
		$err="";
		if($size==false) $size=image::$iconMaxWidth;
		global $error;
		global $messages;
		if(!isset($messages['ERROR_imgMakeError'])) $messages['ERROR_imgMakeError']="System error";
		@unlink ($to);
		
		// Узнаем MIME тип 
		$sz=getimagesize($from);
        $destExt=file::getExtension($to,array(0=>'jpg',1=>'gif',2=>'png',3=>'jpeg',4=>'webp'));
		list(,$type)=explode('/',$sz['mime']);
		if($type=='jpeg') $old=imageCreateFromJpeg($from);
		elseif($type=='png') $old=imageCreateFromPng($from);
		elseif($type=='gif') $old=imageCreateFromGif($from);
		elseif($type=='webp') $old=imageCreateFromWebp($from);
		else $error.='Invalid Image MIME type<br>';
		
		$w=imageSX($old); 
		$h=imageSY($old);
		if($w>=$h){ $x=($w/2)-($h/2);$y=0;$oldsize=$h;$w_new=$size;$modul=$w/$size; $h_new=ceil($h/$modul); }
		else { $y=($h/2)-($w/2); $x=0; $oldsize=$w; $h_new=$size; $modul=$h/$size; $w_new=ceil($w/$modul); }
		$new=imagecreatetruecolor($size, $size);
        // Сохраняем альфа канал в PNG
        if($type=='png'){
            imageAlphaBlending($new, false);
            imageSaveAlpha($new, true);
        }
		imageCopyResampled($new, $old, 0, 0, $x, $y, $size, $size, $oldsize, $oldsize);
        if($destExt=='jpg' || $destExt=='jpeg'){
            if(@!imageJpeg($new,$to,85)) $err.="makeImage:Не удалось создать файл изображения<br>";
        }
		elseif($destExt=='gif'){
            if(@!imageGif($new,$to)) $err.="makeImage:Не удалось создать файл изображения<br>";
        }
		elseif($destExt=='png'){
	        if(@!imagePng($new,$to)) $err.="makeImage:Не удалось создать файл изображения<br>";
        }
        elseif($destExt=='webp'){
	        if(@!imageWebp($new,$to,85)) $err.="makeImage:Не удалось создать файл изображения<br>";
        }
        imageDestroy($old);
        imageDestroy($new);
		@chmod($to,image::$chmod);
		if($err=="") return true;
		else {
			$error.=$err;
			return false;
		}
	}
		
	////////////////////////////////////////////////////////
	// Создание группы изображений
	// на входе: 
	// from = источник
	// to = array(
	//	'size'=>'800*600',
	//	'wm'=>'./watermark.png',
	//	'file'=>'./uploaded/filename/',
	//  'square'=>true,       
	//	),
	//
    /**
     * @param $from
     * @param $to
     * @return bool
     */
    static function makeGroup($from,$to){
		global $error;
		global $currentImageWidth;
		global $currentImageHeight;
		foreach($to AS $val){
			$watermark=false;
			list($maxw,$maxh)=explode('*',trim($val['size']));
			if($val['wm']!=false) $watermark=$val['wm'];
			if($maxw===$maxh && $val['square']==true) {
				if(!makeSquareIcon($from,$val['file'],$size=false)) return false;
				}
			else {
				if(!makeImage($from,$val['file'],$maxw,$maxh,false,$watermark)) return false;
				}
			}
		return true;
		}
	
	////////////////////////////////////////////////////////
	// Сохранение картинки с изменением размеров
    /**
     * @param $from
     * @param $to
     * @param bool $maxw
     * @param bool $maxh
     * @param bool $quality
     * @param bool $watermark
     * @return bool
     */
    static function makeImage($from,$to,$maxw=false,$maxh=false,$quality=false,$watermark=false){
		$err="";
		global $error;
		global $currentImageWidth;
		global $currentImageHeight;
		if($maxw==false) $maxw=image::$maxWidth;
		if($maxh==false) $maxh=image::$maxHeight;
		if($watermark==false) $watermark=image::$watermark;
		if($quality==false) $quality=image::$jpgQuality;
		@unlink ($to);
		// Узнаем MIME тип 
		$sz=getimagesize($from) or die($from);
		$destExt=file::getExtension($to,array(0=>'jpg',1=>'gif',2=>'png',3=>'jpeg',4=>'webp'));
		list(,$type)=explode('/',$sz['mime']);
		$type=trim($type);
		
		if($type=='jpeg') $old=imageCreateFromJpeg($from);
		elseif($type=='png') $old=imageCreateFromPng($from);
		elseif($type=='webp') {
			if(function_exists("imagecreatefromwebp")) $old=imageCreateFromWebp($from);
			else {
				$error.='Function imageCreateFromWebp is not supported!';
				$err.='Function imageCreateFromWebp is not supported!';
			}
		}
		elseif($type=='gif') {
			if(function_exists("imagecreatefromgif")) $old=imageCreateFromGif($from);
			else {
				$error.='Function imageCreateFromGif is not supported!';
				$err.='Function imageCreateFromGif is not supported!';
			}
		}
		else {
			$error.='Invalid Image MIME type '.$type.'<br>';
			$err.='Invalid Image MIME type '.$type.'<br>';
		}
		if($err==''){
			$w=imageSX($old); $h=imageSY($old);
			if($maxw<$w || $maxh<$h){
				// Горизонтальное изображение
				if($w>$h){
					if($maxw<$w){ $w_new=$maxw; $modul=$w/$w_new; $h_new=round($h/$modul); }
					else { $h_new=$maxh; $modul=$h/$h_new; $w_new=round($w/$modul); }
				}
				// вертикальное
				if($w<$h){
					if($maxh<$h){ $h_new=$maxh; $modul=$h/$h_new; $w_new=round($w/$modul); }
					else { $w_new=$maxw; $modul=$w/$w_new; $h_new=round($h/$modul);}
				}
				// квадрат 
				if($w==$h){
					if($maxh<$maxw) { $h_new=$maxh; $modul=$h/$h_new; $w_new=round($w/$modul); }
					else { $w_new=$maxw; $modul=$w/$w_new; $h_new=round($w/$modul);}
				}
			}
			else {
				$w_new=$w;
				$h_new=$h;
			}
			$currentImageWidth=$w_new;
			$currentImageHeight=$h_new;
			$new=imagecreatetruecolor($w_new, $h_new);
			// Сохраняем альфа канал в PNG
			if($type=='png'){
				imageAlphaBlending($new, false);
				imageSaveAlpha($new, true);
			}
			imageCopyResampled($new, $old, 0, 0, 0, 0, $w_new, $h_new, $w, $h);
			if($watermark!=false){
				if(file_exists($watermark)){
					$imge=false;
					$imgn=false;
					$array=getimagesize($watermark);
					$wmx=$w_new-$array[0];
					$wmy=$h_new-$array[1];
					list($imgn,$imge)=explode('/',$array['mime']);
					if($imge=='jpeg') $imge='jpg';
					if($imge!=false){
						$wmimage=false;
						if($imge=='png') $wmimage = imagecreatefrompng($watermark);
						elseif($imge=='jpg') $wmimage = imagecreatefromjpeg($watermark);
						elseif($imge=='gif') $wmimage = imagecreatefromgif($watermark);
						elseif($imge=='webp') $wmimage = imagecreatefromwebp($watermark);
						if($wmimage!=false) imagecopy($new,$wmimage,$wmx,$wmy,0,0,$array[0],$array[1]);
					}
				}
			}
			if($destExt=='jpg' || $destExt=='jpeg'){
				$destExt='jpg';
                if(@!imageJpeg($new,$to,$quality)) $err.="makeImage:Не удалось создать файл изображения<br>";
			}
			elseif($destExt=='gif'){
                if(@!imageGif($new,$to)) $err.="makeImage:Не удалось создать файл изображения<br>";
			}
			elseif($destExt=='png'){
                if(@!imagePng($new,$to)) $err.="makeImage:Не удалось создать файл изображения<br>";
			}
			elseif($destExt=='webp'){
				if(@!imageWebp($new,$to,$quality)) $err.="makeImage:Не удалось создать файл изображения<br>";
			}
			@chmod($to,image::$chmod);
			imageDestroy($old);
			imageDestroy($new);
			if($err=="") return true;
			else {
                image::$error.=$err;
				return false;
			}
		}
		else return false;
	}
	
	// Исправление глюка GDLib с синима каналом в WEBP
	static function webpBlueChannelFix($img){
		$tmp = imagecreatetruecolor(imagesx($img),imagesy($img));
		$color = imagecolorallocate($tmp, 255, 255, 255);
		imagefill($tmp, 0, 0, $color);
		
		for ($y = 0; $y < imagesy($img); $y++) {
			for ($x=0; $x < imagesx($img); $x++) {
				$rgb = imagecolorat($img, $x, $y);
				$r = ($rgb >> 24) & 0xFF;
				$g = ($rgb >> 16) & 0xFF;
				$b = ($rgb >> 8) & 0xFF;
				$pixelcolor = imagecolorallocate($tmp, $r, $g, $b);
				imagesetpixel($tmp, $x, $y, $pixelcolor);
			}
		}
		return $tmp;
	}

	// Возвращеет массив с EXIF данными изображения, либо false в случае отсутствия оного
    /**
     * @param $file
     * @return bool
     */
    static function getExif($file){
		if(file_exists($file)){
			if(function_exists("exif_read_data")){
				$array=exif_read_data($file);
				if($array!=false){
					$exif['make']=false;
					$exif['camera']=false;
					$exif['iso']=0;
					$exif['shooter']=0;
					$exif['f']=0;
					$exif['focal']="?mm";
					$exif['date']=0;
					if(isset($array['Make'])) $exif['make']=trim($array['Make']); 
					if(isset($array['Model'])) $exif['camera']=trim($array['Model']);
					if(isset($array['ExposureTime'])) $exif['shooter']=$array['ExposureTime'];
					if(isset($array['ISOSpeedRatings'])) $exif['iso']=$array['ISOSpeedRatings'];
					if(is_array($exif['iso'])) $exif['iso']="?";
					if(isset($array['FNumber'])) $exif['f']="f".image::exif_get_float($array['FNumber']);
					if(isset($array['FocalLength'])) $exif['focal']=round(image::exif_get_float($array['FocalLength']));
					if(isset($array['FocalLengthInmmFilm'])) $exif['focal']=$array['FocalLengthInmmFilm'];
					if(isset($array['DateTimeOriginal'])) {
						list ($da,$time)=explode(" ",$array['DateTimeOriginal']);
						list ($y,$m,$d) = explode(":",$da);
						list ($h,$i,) = explode(":",$time);
						$exif['date']="$d.$m.$y $h:$i";
						}
					if(strlen($exif['shooter'])>8) $exif['shooter']="?";
					else {
						if(strpos($exif['shooter'],"/")!==false){
							list($m1,$m2)=explode("/",$exif['shooter']);
							$m1size=strlen($m1);
							$m2size=strlen($m2);
							if($m1size>=2 && $m2size>=3){
								$m1last=$m1[(strlen($m1)-1)];
								$m2last=$m2[(strlen($m2)-1)];
								if($m1last=="0" && $m2last=="0"){
									$exif['shooter']=($m1/10)."/".($m2/10);
									}
								}
							}
						}
					if($exif['camera']!=false) return $exif;
					else return false;
					}
				else return false;
				}
			else return false;
			}
		else return false;
		}


    /**
     * @param $value
     * @return float
     */
    static function exif_get_float($value) {
	  $pos = strpos($value, '/'); 
	  if ($pos === false) return (float) $value; 
	  $a = (float) substr($value, 0, $pos); 
	  $b = (float) substr($value, $pos+1); 
	  return ($b == 0) ? ($a) : ($a / $b); 
	}
	
	// Обрезка изображения
	// Функция работает с PNG, GIF и JPEG изображениями.
	// Обрезка идёт как с указанием абсоютной длины, так и относительной (отрицательной)
    /**
     * @param $from
     * @param $to
     * @param string $crop
     * @param bool $percent
     * @return bool
     */
    static function crop($from, $to, $crop='square', $percent=false){
        $x_o=false;
        $y_o=false;
        list($w_i, $h_i, $type) = getimagesize($from);
        if (!$w_i || !$h_i) return false;
		$types = array('','gif','jpeg','png');
		$ext = $types[$type];
		if ($ext) {
			$func = 'imagecreatefrom'.$ext;
			$img = $func($from);
			} 
		else return false;
        if ($crop == 'square') {
           $min = $w_i;
           if ($w_i > $h_i) $min = $h_i;
           $w_o = $h_o = $min;
			}
		else {
			list($x_o, $y_o, $w_o, $h_o) = $crop;
            if($percent){
               $w_o *= $w_i / 100;
               $h_o *= $h_i / 100;
               $x_o *= $w_i / 100;
               $y_o *= $h_i / 100;
               }
			if ($w_o < 0) $w_o += $w_i;
			$w_o -= $x_o;
            if ($h_o < 0) $h_o += $h_i;
                $h_o -= $y_o;
				}
        $img_o = imagecreatetruecolor($w_o, $h_o);
        imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
        if ($type == 2) return imagejpeg($img_o,$to,100);
		else $func = 'image'.$ext;
		return $func($img_o,$to);
    }

    /**
     * индексирование цветов картинки
     * Функция получает список часто используемых в изображении цветов
     * чем большее значение переменной $chunkSize, тем больше нагрузка
     * @param $imagePath - путь к файлу
     * @param int $colorsIndex - кол-во цветов
     * @return mixed
     */
    static function colorIndex($imagePath,$colorsIndex=8){
    $size = getimagesize($imagePath);//размеры картинки
    $chunkSize=50;
    $image_type=file::getExtension($imagePath);//расширение файла
    switch ($image_type){
        case 'gif': $src = @imagecreatefromgif($imagePath); break;
        default:case 'jpg': $src = @imagecreatefromjpeg($imagePath); break;
        case 'png': $src = @imagecreatefrompng($imagePath); break;
        }
    $source=imagecreatetruecolor($chunkSize, $chunkSize);
    imageCopyResampled($source, $src, 0, 0, 0, 0, $chunkSize, $chunkSize, $size[0], $size[1]);
    //imagegammacorrect ($source,1,0.5);
    $size[0]=$chunkSize;
    $size[1]=$chunkSize;
    $INDEX=array();
    $I=array();
    $b = 1;
    $size1 = $size[1]*2;
    $size2 = $size1+50;
    $size0 = $size[0]*2;

    //перебираем точки картинки по x
    for($y=0;$y<=$size[1]-1;$y++){
        for($x=0;$x<=$size[0]-1;$x++)//перебираем точки по y
        {
        $color=ImageColorAt($source, $x, $y);//берем цвет в точке n
        $rgb_arr_0=imagecolorsforindex($source, $color);//переводим цвет в rgb
        $rgb_arr[0]=$rgb_arr_0['red'];
        $rgb_arr[1]=$rgb_arr_0['green'];
        $rgb_arr[2]=$rgb_arr_0['blue'];
        $I[$color]++;
        $INDEX[$color]=image::rgbHex(implode('.',$rgb_arr));//rgb в hex, данные для построения рейтинга
        $b++;
        }
    $ngb = 0;
    }

    $out_0=array();
    $out=array();
    $outer = array();

    // перебираем все полученые цвета, представим как
    // номер цвета GD в палитре изображения => цвет в hex
    // пишем количество вхождений данного цвета
    // sizeof($out_0[$I[$key]])= к-во н-ого цвета в массиве цветов
    foreach($INDEX as $key=>$color_hex){
        $out_0[$I[$key]][]=$color_hex;
        }

    //переберем все hex суб-массивы в обратном порядке,
    // обеденим многомерный массив в одномерный
    foreach($out_0 as $incolor){
        rsort($incolor);
        $out[]=implode(',',$incolor);
        }

    // две следующие операции - гарантия получения одномерного массива,
    // не смотря на то, что выглядит по-идиотски :)
    $outvalue=array();
    $out=implode(',',$out);
    $out=explode(',',$out);
    $out_0=array();
    $maskcolor = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");
    $maskcolor2 = array("0", "4", "8",  "C");
    foreach ($out as $key => $out_mask){
        $out_mask =  substr($out_mask, 1);
        $outearr = str_split($out_mask, 1);
        $keyw = array_search($outearr[0], $maskcolor)+1;
        $outkey[0] = ceil($keyw/4)-1;
        $outval[0] =  $maskcolor2[$outkey[0]];
        $outval[1] =  $outval[0];
        $keyw = array_search($outearr[2], $maskcolor)+1;
        $outkey[2] = ceil($keyw/4)-1;
        $outval[2] =  $maskcolor2[$outkey[2]];
        $outval[3] =  $outval[2];
        $keyw = array_search($outearr[4], $maskcolor)+1;
        $outkey[4] = ceil($keyw/4)-1;
        $outval[4] =  $maskcolor2[$outkey[4]];
        $outval[5] =  $outval[4];
        $outvalue[$key] = '#'.$outval[0].$outval[1].$outval[2].$outval[3].$outval[4].$outval[5];
    }

    $out = $outvalue;
    $outcount = array_count_values($out);
    $outers=array();
    $outersval=array();
    arsort($outcount);
    reset($outcount);
    while (list($key, $val) = each($outcount)) {
       $outers[] = $key;
       $outersval[] = $val;
    }

    // получим $out_0[0]=hex с max числом вхождений,
    // т.е. самый частовстречающийся цвет по рейтингу
    for($i=sizeof($outers);$i>sizeof($outers)-$colorsIndex;$i--){
        $out_0[]=$outer[$i];
        }

    $outs = array_chunk($outers, $colorsIndex);
    return $outs[0];
    }

    /**
     * Конвертирует цвет RGB в HEX и обратно
     * @param $color
     * @return bool|string
     */
    static function rgbHex($color){
        if(!$color) return false;
        $color = trim($color);
        $out = false;
        if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $color)){
            $color = str_replace('#','', $color);
            $l = strlen($color) == 3 ? 1 : (strlen($color) == 6 ? 2 : false);
            if($l){
                unset($out);
                $out[0] = $out['r'] = $out['red'] = hexdec(substr($color, 0,1*$l));
                $out[1] = $out['g'] = $out['green'] = hexdec(substr($color, 1*$l,1*$l));
                $out[2] = $out['b'] = $out['blue'] = hexdec(substr($color, 2*$l,1*$l));
                }
            else $out = false;
        }
        elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $color)){
            $spr = str_replace(array(',',' ','.'), ':', $color);
            $e = explode(":", $spr);
            if(count($e) != 3) return false;
            $out = '#';
            for($i = 0; $i<3; $i++) $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));
            for($i = 0; $i<3; $i++) $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];
            $out = strtoupper($out);
        }
        else $out = false;
        return $out;
    }

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Масштабирование группы изображений, чтобы они выглядели красиво, 
	// Картинки выравниваются по высоте и выстраиваются в ряд с масштабированием, 
	// чтобы занимать ровно $width пикселей по ширине
	// -----------------------------------------------------------------------------------------------------
	// На входе $images - > массив вида:
	// array 
	//       0 => array 
	//         (
	//         'width'  => 500,
	//         'height' => 284
	//         ),
	//       1 => array 
	//         (
	//         'width'  => 300,
	//         'height' => 184
	//         )
	// И $width = 'ширина блока (INT)' - к-во пикселей, 
	// -------------------------------------------------------------------------------------------------------
	// На выходе - тот же массив, но с измененными размерами изображений
	static function stringScale($images,$blockWidth){
		// Шаг 1: Увеличиваем высоту каждой картинки до 1000 пикселей (приводим все картинки к общей высоте)
		$width=0;
		$blockWidth=$blockWidth-2;
		if(count($images)>1){
			foreach($images AS $key=>$val){
				$mn=2000/$val['height'];
				$images[$key]['height']=2000;
				$images[$key]['width']=$val['width']*$mn;// 3240 * 2000
				$width+=$images[$key]['width'];
			}
			// Теперь узнаем, на сколько ширина превосходит заданную
			$mn=$width/$blockWidth;// делитель
			// Шаг 2: Выполняем финальное масштабирование
			foreach($images AS $key=>$val){
				$images[$key]['width']=$val['width']/$mn;
				$images[$key]['height']=$val['height']/$mn;
			}
		}
		else {
			$m=$blockWidth/$images[0]['width'];
			$images[0]['width']=$blockWidth;
			$images[0]['height']=$images[0]['height']*$m;
		}
		return $images;
	}
	
	/**
	 * Функция определяет, является ли цвет "ярким" при переводе в RGB, или нет
	 * @param $HexRgbColor - цвет в HEX RGB формате
	 * @param $returnIndex - вернет индекс яркости
	 * @return mixed
	*/
	static function isLightColor($HexRgbColor='#000000',$returnIndex=false){
		$r=hexdec(substr($HexRgbColor,1,2));
		$g=hexdec(substr($HexRgbColor,3,2));
		$b=hexdec(substr($HexRgbColor,5,2));
		$lightness=(0.3*$r) + (0.59*$g) + (0.11*$b);
		if($returnIndex!=false){
			return $lightness;
		}
		if($lightness>127) return true;
		else return false;
	}

}