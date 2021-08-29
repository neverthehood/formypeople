<?php
// Класс для работы с HTML формами
// 27.09.2014 Добавлена поддержка параметра AUTOCOMPLETE разрешающего или запрещающего отображение
// истории при наборе
/**
 * @property mixed id
 * @property int fieldCounter
 */
class form {
	var $method="POST"; 	// 'POST', 'GET', 'AJAX', 'dynamic', false
	var $action=false;  	// Скрипт, который получит данные
							// В случае AJAX формы - событие onClick. Если false, то данные получит 
							// текущая страница
	var $enctype='';		// Если будет поле типа FILE, то enctype "multipart/form-data"
	var $arrname='';		// Если не пустая строка, то генерируется массив this->arrname[ячейка]
	var $fields=array();
	var $id;			    // ID формы (инкрементное поле)
	var $prefix="fr";		// Префикс для ID формы
	var $error='';			// Накопитель сообщений об ошибках
	var $type='vertical';	// вертикальная или горизонтальная vertical / horizontal 
	var $errorTemplate='<div class="error">%%</div>';	// Шаблон сообщения об ошибке
	var $errorFieldClass='frmError'; // CSS Класс для не правильно заполненных элементов форм
	var $isVisual=false;	// Флаг, который поднимается при использовании CKeditor
	var $style=false;		// CSS Стиль для таблицы формы
	var $fieldCounter=0; // Счетчик полей, ID полей по умолчанию
	static $ckCounter=0;    // Счетчик для полей визредактора
	private $hiddens='';	// Скрытые поля формы
	private $fieldTypes=array('checkbox'=>1,'radio'=>1,'text'=>1,'textarea'=>1,'ckeditor'=>1,'password'=>1,'button'=>1,'hidden'=>1,'select'=>1,'submit'=>1,'file'=>1,'calendar'=>1,'datetime'=>1,'html'=>1);
	// Типы текстовых полей
	private $events=array('onclick'=>1,'ondblclick'=>1,'onmouseover'=>1,'onmouseout'=>1,'onmouseup'=>1,'onmousedown'=>1,'onmove'=>1,'ondragdrop'=>1,'onresize'=>1,'onkeypress'=>1,'onkeydown'=>1,'onkeyup'=>1,'onmousemove'=>1,'onselect'=>1,'onchange'=>1,'onfocus'=>1,'onblur'=>1);
	
	function __construct($fields=array()){
		$this->fields=$fields;
	}

	// Отображение формы
	public function show(){
		global $settings;
		$out='';
		//static $frmId;
		if(!isset($frmid)) $frmid=0;
		$frmid++;
		if($this->id=='') $this->id='fr'.$frmid;

		if(!isset($settings['currentFormId'])) $settings['currentFormId']=1;
		else $settings['currentFormId']++;
//        if (!empty($settings['currentFormId'])) {
//            $this->id=$settings['currentFormId'];
//        }
		$this->fieldCounter=0;
		if($this->style!='') $style=' style="'.$this->style.'"';
		else $style='';
		$out.='<table class="formtable" cellpadding="4" cellspacing="4" border="0"'.$style.'>';
		$inlineScripts='';
		$vname=false;
		if($this->arrname!='') @$_POST=$_POST[$this->arrname];
		foreach($this->fields AS $val){
			if(is_array($val)){
				$this->fieldCounter++;
				$type=$val['type'];

				// События JS
				if(isset($val['events'])){
				    if(is_array($val['events'])){
				        foreach($val['events'] AS $ek=>$ev){
				            $val[$ek]=$ev;
				        }
				    }
				}


				if(!isset($val['dopparams'])) $val['dopparams']='';
				if($this->arrname!='') {
					$vname=$val['name'];
					$val['name']=$this->arrname.'['.$val['name'].']';
				}
				// Получим события для JavaScript
				foreach($val AS $k=>$v){
					$ks=strtolower($k);
					if(isset($this->events[$ks]) && $v!='') $val['dopparams'].=' '.$k.'=\''.$v.'\'';
				}
				if(isset($val['autocomplete'])){
				    $acval='off';
				    if($val['autocomplete']===true || $val['autocomplete']===1) $acval='on';
				    $val['dopparams'].=' autocomplete="'.$acval.'"';
				}
				if(isset($val['required'])){
				    if($val['required']!=false) $val['dopparams'].=' required="required"';
				    if($val['label']!='') $val['label'].='*';
				}
				if(isset($val['placeholder'])){
				    if($val['placeholder']!='') $val['dopparams'].=' placeholder="'.htmlspecialchars($val['placeholder']).'"';
				}
				
				if(isset($val['description'])){
					if($val['description']!='') $val['description']='<div class="smallgrey margin-top">'.$val['description'].'</div>';
				}
				
				if(isset($val['disabled'])){
					if($val['disabled']!=false) $val['dopparams'].=' disabled="disabled"';
				}
				
				if(isset($val['class'])) $val['dopparams'].=' class="'.$val['class'].'"';
				if(isset($val['style'])) $val['dopparams'].=' style="'.$val['style'].'"';
				if(!isset($val['id'])) $val['id']=$this->prefix.$this->id.'-'.$this->fieldCounter;
				if($this->arrname!='') {
                    if(isset($_POST[$vname])) $val['value']=$_POST[$vname];
				}
				else {
				    if($val['type']!='html'){
					    if(isset($_POST[$val['name']])) $val['value']=$_POST[$val['name']];
                    }
				}
				if(isset($val['type'])){
					if($val['type']=='textarea') {
						if(isset($val['maxlength'])){
							if(!isset($settings['textareaDelimiterIsInit'])) $settings['textareaDelimiterIsInit']=true;
						}
					}
					if($val['type']=='ckeditor') {
						$this->isVisual=true;
						if(!isset($val['toolbar'])) $val['toolbar']='Full';
						$out.=$this->ckeditor($val);
					}
					else {
						if(isset($this->fieldTypes[$type])) $out.=$this->$type($val);
						else $this->error.='Type &laquo;'.$val['type'].'&raquo; is not corrected!<br>';
					}
				}
				else $out.=$this->text($val);
			}
			else {
				if($val=='-'){
					if($this->type=='vertical') $out.='<tr><td><hr /></td></tr>';
					else $out.='<tr><td colspan="2"><hr /></td></tr>';
				}
			}
		}
			
		if($this->action!=false) $action=' action="'.$this->action.'"';
		else $action='';
		$wrap='';
		$enc='';


		if($this->enctype!='') $enc=' enctype="'.$this->enctype.'"';
		if($this->method!='AJAX') $wrap='<form id="'.$this->id.'" name="'.$this->id.'" method="'.$this->method.'"'.$enc.' '.$action.'>';
		if($this->method=="dynamic") $wrap='<form method="POST" id="AJAXform" name="AJAXform" enctype="multipart/form-data">
		<input type="submit" style="display:none;" />
		<input type="hidden" id="AJAXaction" name="action" value="">
		<input type="hidden" id="AJAXbackend" name="backend" value="">';
		// Если используются поля CKeditor, то загрузим набор настроек CKeditor по умолчанию
		// если он еще не был инициализирован ранее  ( $settings['ckEditorIsInit'] ) 
		$vconf='';
		if($this->isVisual==true) {
			if(!isset($settings['ckEditorIsInit'])) $vconf='';
			else $settings['ckEditorIsInit']=true;
			}
		$out=$inlineScripts.$wrap.$this->hiddens.$out.'</table>';
		if($this->method!='AJAX') $out.='</form>';
		if($this->method=="dynamic") $out.='</form>';
		return $vconf.$out;
	}
	
	// Добавление стиля для формы
	public function style($style){
		$this->style=$style;
	}

	
	// Вставка в форму произвольного HTML кода
	public function html($val){
		return $val['value'];
	}
		
	public function checkbox($val){
		$checked='';
		$class='';
		//$val['noformat']=true;
		if($val['value']==1) $checked=' checked="checked"';
		$tooltip='';
		if(isset($val['tooltip'])){
		    $tooltip='<span class="mini-info tooltip" data-tooltip="'.htmlspecialchars($val['tooltip']).'">i</span>';
		}
		if(isset($val['class'])) $class=' class="'.$val['class'].'"';
		$this->hiddens.='<input type="hidden" name="'.$val['name'].'" value="0" />';
		$desc=@$val['description'];

		if(@$val['noformat']==true){
			return '<div style="float:left;"><input'.$class.' type="checkbox" id="'.$val['id'].'" name="'.$val['name'].'" value="1"'.$checked.' '.$val['dopparams'].'/><label for="'.$val['id'].'">'.$val['label'].$tooltip.'</label></div>';
		}
		else {
			if(@$val['description']!='') {
				$out='<table cellpadding="0" cellspacing="0"><tr><td><input'.$class.' type="checkbox" id="'.$val['id'].'" name="'.$val['name'].'" value="1"'.$checked.' '.$val['dopparams'].'/><label for="'.$val['id'].'">'.$val['label'].'</label></td></tr><tr><td>'.$desc.'</td></tr></table>';
				$desc='';
			}
			else $out='<input'.$class.' type="checkbox" id="'.$val['id'].'" name="'.$val['name'].'" value="1"'.$checked.' '.$val['dopparams'].'/><label for="'.$val['id'].'">'.$val['label'].'</label>';
		}
		if($this->type=='vertical') return '<tr><td>'.$out.$desc.'</td></tr>';
		else return '<tr><td>&nbsp;</td><td>'.$out.$desc.'</td></tr>';
	}
		
	public function calendar($val){
		if(isset($val['value'])) $value=htmlspecialchars($val['value']);
		else $value=date("d.m.Y",time());
		$months='';
		if(isset($val['months'])){
			if(is_int($val['months'])) $months='numberOfMonths: '.$val['months'].',';
			}
		$size=' size="16"';
		$maxlength='';
		if(isset($val['size'])) $size=' size="'.htmlspecialchars($val['size']).'"';
		$out='<script type="text/javascript">
/*<![CDATA[*/
        $(function() {
                $( "#'.$val['id'].'" ).datepicker({
					//changeMonth: true,
					//changeYear: true,
					constraintInput: true,'.$months.'
					defaultDate: \''.$val['value'].'\'
				});
				
        });
/* ]]> */
</script>
<input id="'.$val['id'].'" type="text" name="'.$val['name'].'" value="'.$value.'"'.$size.$maxlength.$val['dopparams'].' />';
		if($val['noformat']==true) return $out;
		if($this->type=='vertical') return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td>'.$val['description'].$out.'</td></tr>';
	}
		
	public function text($val){
		if(isset($val['value'])) $value=htmlspecialchars($val['value']);
		else $value='';
		$size='';
		$maxlength='';
		if(isset($val['size'])) $size=' size="'.htmlspecialchars($val['size']).'"';
		if(isset($val['maxlength'])) $maxlength=' maxlength="'.htmlspecialchars($val['maxlength']).'"';
		$out='<input id="'.$val['id'].'" type="text" name="'.$val['name'].'" value="'.$value.'"'.$size.$maxlength.$val['dopparams'].' />';
		if(@$val['noformat']==true) return $out;
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($this->type=='vertical') return '<tr><td'.$tdparams.'><label for="'.$val['id'].'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td'.$tdparams.'>'.@$val['description'].$out.'</td></tr>';
	}
		
	public function file($val){
		$size='';
		$this->enctype=' enctype="multipart/form-data"';
		if(isset($val['size'])) $size=' size="'.htmlspecialchars($val['size']).'"';
		$out='<input id="'.$val['id'].'" type="file" name="'.$val['name'].'"'.$size.$val['dopparams'].' />';
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($this->type=='vertical') return '<tr><td'.$tdparams.'><label for="'.$val['id'].'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td'.$tdparams.'>'.$val['description'].$out.'</td></tr>';
	}
		
	public function password($val){
		if(isset($val['value'])) $value=htmlspecialchars($val['value']);
		else $value='';
		$size='';
		if(isset($val['size'])) $size=' size="'.htmlspecialchars($val['size']).'"';
		if(isset($val['maxlength'])) $maxlength=' maxlength="'.htmlspecialchars($val['maxlength']).'"';
		else $maxlength=' maxlength="64"';
		$out='<input id="'.$val['id'].'" type="password" name="'.$val['name'].'" value="'.$value.'"'.$size.$maxlength.$val['dopparams'].' />';
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($this->type=='vertical') return '<tr><td'.$tdparams.'><label for="'.$val['id'].'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td'.$tdparams.'>'.$val['description'].$out.'</td></tr>';
	}
			
	public function hidden($val){
	    global $settings;
		if(isset($val['value'])){
			if(!is_array($val['value'])) $value=htmlspecialchars($val['value']);
			else $value=$val['value'][$settings['siteDefaultLang']];
		}
		else $value='';
		if(isset($val['id'])) $id=' id="'.$val['id'].'"';
		else $id='';
		$this->hiddens.='<input type="hidden"'.$id.' name="'.$val['name'].'" value="'.$value.'" />';
	}
		
	public function textarea($val){
	    $out='';
		if(isset($val['value'])) $value=$val['value'];
		else $value='';
		$cols=' cols="64"';
		$rows=' rows="4"';
		if(isset($val['label'])) $label='<label for="'.$val['id'].'">'.$val['label'].'</label>';
		else $label='';
		if(isset($val['cols'])) $cols=' cols="'.htmlspecialchars($val['cols']).'"';
		if(isset($val['rows'])) $rows=' rows="'.htmlspecialchars($val['rows']).'"';
		if(!isset($val['description'])) $val['description']='';
		
		if(isset($val['maxlength'])) $val['dopparams'].=' onKeyDown="textareaLimiter(this,\''.$val['maxlength'].'\',\''.$val['id'].'\');" 
onKeyUp="textareaLimiter(this,\''.$val['maxlength'].'\',\''.$val['id'].'\');"';
		if(isset($val['counter']) && $val['counter']>0){
			if(!isset($val['lengthcomment'])) $val['lengthcomment']='Не более '.$val['maxlength'].' символов. Введено ';
			$out='<textarea id="'.$val['id'].'" name="'.$val['name'].'"'.$cols.$rows.$val['dopparams'].'>'.$value.'</textarea>
			<div class="textareaMaxlength">'.$val['lengthcomment'].'<span class="textareaCounter" id="delimiter'.$val['id'].'">'.mb_strlen($value,'utf-8').'</span></div>';
		}
		else $out.='<textarea id="'.$val['id'].'" name="'.$val['name'].'" '.$cols.$rows.$val['dopparams'].'>'.$value.'</textarea>';
		if(strlen($val['description'])>=4) $val['description']='<div style="float:none; clear:both;">'.$val['description'].'</div>';
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($this->type=='vertical') return '<tr><td'.$tdparams.'>'.$label.$val['description'].$out.'</td></tr>';
		else return '<tr><td>'.$label.$val['description'].'</td><td'.$tdparams.'>'.$out.'</td></tr>';
	}
		
	public function ckeditor($val){
	    static $ckCounter;
	    if(!$ckCounter) $ckCounter=0;
	    $ckCounter++;
	    $valid='ckedit'.$ckCounter;
		if(isset($val['value'])) $value=$val['value'];
		else $value='';
		$cols='';
		$rows='';
		if(!isset($val['description'])) $val['description']='';
		$hidden='';
		if(isset($val['hidden'])){
		    if($val['hidden']==true) $hidden=' style="display:none;"';
		}
		if(isset($val['cols'])) $cols=' cols="'.htmlspecialchars($val['cols']).'"';
		if(isset($val['rows'])) $rows=' rows="'.htmlspecialchars($val['rows']).'"';
		$out='<textarea id="'.$valid.'" name="'.$val['name'].'"'.$cols.$rows.$val['dopparams'].'>'.$value.'</textarea>';
		if(@$val['noformat']==true) return $out;
		if(isset($val['width'])) $out='<div style="width:'.$val['width'].'px;">'.$out.'</div>';
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($this->type=='vertical') return '<tr><td><label for="'.$valid.'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		else {
			if(isset($val['colspan'])){
			    if($val['label']!='') $label='<label class="ckEdLabel" for="'.$valid.'"><span>'.$val['label'].'</span></label>';
			    else $label='';
				return '<tr id="row'.$valid.'"><td colspan="2" style="padding-top:8px;"><div id="content'.$ckCounter.'"'.$hidden.'>'.$label.$out.'</div></td></tr>';
			}
			else return '<tr><td><label for="'.$valid.'">'.$val['label'].'</label></td><td'.$tdparams.'>'.$val['description'].$out.'</td></tr>';
		}
	}
		
	static function cktoolbar($toolbar){
		if($toolbar=="full") return "toolbar :
			[
				{ name: 'document', items : [ 'Source','DocProps','Preview','Print'] },
				{ name: 'paragraph', items : [ 'NumberedList','BulletedList' ] },
				{ name: 'tools', items : [ 'Maximize','-','About' ] }
			]
			";
		elseif($toolbar=="omcms" || $toolbar=="axiom") return "
		toolbar :
			[
		['Source','DocProps','Preview','Print','-','Cut','Copy','Paste','PasteText', 'PasteFromWord','-','Undo','Redo','-','Find','Replace','-','SelectAll','Bold', 'Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat', 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv', '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor','-','Image','Flash','Table','HorizontalRule','-','Smiley','SpecialChar', 'PageBreak','Iframe','-','Styles','-','Format','-','Font','-','FontSize','TextColor','BGColor','-','Maximize','ShowBlocks','Youtube','Uppod','Spoiler']
			]
			";
		elseif($toolbar=="basic") return "
		toolbar :
			[
		['Source','DocProps','Preview','-','Paste','PasteText', 'PasteFromWord','-','Undo','Redo','-','Bold', 'Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat', 'NumberedList','BulletedList','-','Blockquote','Link','Unlink', 'Anchor','-','Image','Table','Smiley','SpecialChar','-','Format','FontSize','-', 'TextColor','ShowBlocks']
			]
			";
		elseif($toolbar=="mini") return "
		toolbar :
			[
		['PasteText','Bold', 'Italic','Underline','Strike','Subscript','Superscript','Link','Unlink','Smiley','SpecialChar']
			]
			";
		else return "";
		}
	
	// name(string) 
	// multiple(boolean) 
	// value(array/string) 
	// options(array) 
	// size(к-во строк) 
	// style
	// class
	public function select($val){
		if(isset($val['value'])) $value=$val['value'];
		else $value='';
		// Size- к-во отображаемых строк выпадающего списка
		if(isset($val['size'])) $size=' size="'.$val['size'].'"';
		else $size='';
		$multiName='';
		if(isset($val['multiple'])) {
			$multiple=' multiple="multiple"';
			$multiName='[]';
		}
		else $multiple='';
		$options=$this->getOptions($value,$val['options'],"select");
		$out='<select id="'.$val['id'].'" name="'.$val['name'].$multiName.'"'.$multiple.$size.$val['dopparams'].'>'.$options.'</select>';
		if(isset($val['template'])) $out=str_replace("%",$out,$val['template']);
		// if($this->type=='vertical') return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label>'.$val['description'].$out.'</td></tr>';
		// else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td>'.$val['description'].$out.'</td></tr>';
		if(isset($val['description']) && strlen($val['description'])>=4) $val['description']='<div style="float:none; clear:both;">'.$val['description'].'</div>';
		else $val['description']='';
		if(isset($val['noformat']) && $val['noformat']==true) return $out;
		$tdparams='';
		if(isset($val['div'])) $tdparams.=' id="'.$val['div'].'"';

		if($this->type=='vertical') return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label><div'.$tdparams.'>'.$val['description'].$out.'</div></td></tr>';

		else return '<tr><td><label for="'.$val['id'].'">'.$val['label'].'</label></td><td'.$tdparams.'>'.$out.$val['description'].'</td></tr>';
	}
		
	// radio (группа радиокнопок)
	public function radio($val){
		$out='';
		$style='';
		$mclass='';
		$tdparams='';
		if(isset($val['style'])){
			if($val['style']!='') $style=' style="'.$val['style'].'"';
		}
		if(isset($val['class'])){
			if($val['class']!='' && $val['noformat']==true) $mclass=' class="'.$val['class'].'"';
		}
		foreach($val['options'] AS $key=>$v){
			$selected='';
			
			if($val['value']==$key || $val['value']==$v) $selected=' checked="checked"';
			$out.='<div'.$style.'><input id="'.$val['id'].'r'.$key.'" type="radio" name="'.$val['name'].'"'.$selected.$mclass.' value="'.$key.'" '.$tdparams.'/><label for="'.$val['id'].'r'.$key.'">'.$v.'</label></div>';
			}

		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($val['noformat']==true) return '<div class="line"'.$tdparams.'>'.$out.'</div>';
		if($this->type=='vertical') return '<tr><td><label>'.$val['label'].'</label>'.$val['description'].'<div'.$tdparams.'>'.$out.'</div></td></tr>';
		else return '<tr><td><label>'.$val['label'].'</label></td>'.$val['description'].'<div'.$tdparams.'>'.$out.'</div></td></tr>';
	}
		
	public function submit($val){
	    $out='';
		if(isset($val['value'])) $value=htmlspecialchars($val['value']);
		else $value='';
		$out.='<input type="submit" value="'.$value.'"'.$val['dopparams'].' />';
		$tdparams='';
		if(isset($val['div'])) $tdparams=' id="'.$val['div'].'"';
		if($val['noformat']==true) return '<div'.$tdparams.'>'.$out.'</div>';
		if($this->type=='vertical') return '<tr><td'.$tdparams.'>'.$out.'</td></tr>';
		else return '<tr><td>&nbsp;</td><td'.$tdparams.'>'.$out.'</td></tr>';
	}
		
	// Возвращает список опций в нужном виде
	// selected(array/string) - Включенные опции. Если массив, то 
	// options(array) - все опции
	// type=
	public static function getOptions($value,$options,$type="select"){
		$opts=array();
		$notArray=false;



		if(!is_array($options)) {
			$options=explode(',',$options);
			$notArray=true;
		}
		foreach($options AS $key=>$val){
			$selected='';
			if(is_array($value)){
				foreach($value AS $s){
					if($s==$key || $s==$val) {
						$selected=' selected="selected"';
						break;
						}
					}
				}
			else {
				if($notArray==true){
					if($value==$key) $selected=' selected="selected"';
				}
				//ToDo !!!! Вот тут очевидно, скрывается ошибка, когда в селекте не определяется текущее значение
				else {
					if($value==$val || $value==$key) $selected=' selected="selected"';
				}
			}
			if($type=="select") $opts[]='<option id="sop'.$key.'" '.$selected.' value="'.$key.'">'.$val.'</option>';
		}
		return implode('',$opts);
	}
		
	//////////////////////////////////////////////////////////////////////////////////
	// Если есть запрещенные слова, то возвращает их список
	//////////////////////////////////////////////////////////////////////////////////	
	static function isBadWords($string,$badWords=false){
	    $staticBW=' бля, блядь, блядун,ебать,ебаный,ёбаный,ёбанный,ебанный,заебись,отъебись,ебал,ебали,ебать,заёбаный, хуй,хуевый,хуёвый, хуев, хуярь,хуярит,хуярить, нахуй, пидор, пизда, пиздец, хер, отхерачить, нахер,похер';
		if($badWords===false) $badWords=explode(',',$staticBW);
		$ret=false;
		foreach($badWords as $val){
			if (preg_match("/".$val."/",$string)==true) $ret.=$val;
		}
		return $ret;
	}
}