<?php

function catalogFilter(){
	global $site, $settings;
	$out='';
	$folder=$site->page['folderalias'];
	if($folder!='coffee' && $folder!='tea'){
		return '';
	}
	$brand=$site->getUrlVars('brand');
	
	// Фильтр для кофе
	if($folder=='coffee'){
		$flt=array('ctype'=>false, 'type'=>false, 'brand'=>array(), 'country'=>array(), 'acid'=>array());
		if(!isset($_SESSION['flt'])){
			$_SESSION['flt']=$flt;
		}
		$flt['ctype']=$site->getUrlVars('ctype');
		$flt['type']=$site->getUrlVars('type');
		$flt['acid']=$site->getUrlVars('acid');
		if($flt['acid']!=false){
			if(!is_array($flt['acid'])){
				$flt['acid']=explode(',',$flt['acid']);
				$_SESSION['flt']['acid']=$flt['acid'];
			}
		}
		if($flt['brand']!=false){
			if(!is_array($flt['brand'])){
				$flt['brand']=explode(',',$flt['brand']);
				$_SESSION['flt']['brand']=$flt['brand'];
			}
		}
		if($flt['country']!=false){
			if(!is_array($flt['country'])){
				$flt['country']=explode(',',$flt['country']);
				$_SESSION['flt']['country']=$flt['country'];
			}
		}
		
//		echo '<pre>';
//		print_r($flt);
//		echo '</pre>';
		
		// Получим массив данных для формирования фильтра
		$filterData=coffee::getCoffeeFilterData();
		$out.='
		<div class="container catFilter" id="catFilter">
			<div id="catFilterBody">
				<div class="row">
				<form id="filterForm">
					<input type="hidden" name="filter[category]" value="coffee">
					<div>
						<b>Кофе:</b>';
						if(!empty($filterData['ctype'])){
							foreach($filterData['ctype'] AS $key=>$val){
								$checked='';
								if($flt['ctype']==$key) $checked='checked="checked" ';
								$out.='<input class="radioCtype" id="rad'.$key.'" type="radio" '.$checked.' onChange="filterStart()" name="filter[ctype]" value="'.$key.'"><label for="rad'.$key.'">'.$val.'</label>';
							}
						}
					$out.='
					</div>
					<div>
						<b>Тип кофе:</b>';
						if(!empty($filterData['type'])){
							foreach($filterData['type'] AS $key=>$val){
								$checked='';
								if($flt['type']==$key) $checked='checked="checked" ';
								$out.='<input id="rad'.$key.'" type="radio" onChange="filterStart()" '.$checked.' name="filter[type]" value="'.$key.'"><label for="rad'.$key.'">'.$val.'</label>';
							}
						}
					$out.='</div>
					<div>
						<b>Кислотность:</b>';
						
						if(!empty($filterData['acidity'])){
							foreach($filterData['acidity'] AS $key=>$val){
								$checked='';
								if(is_array($flt['acid']) && in_array($key,$flt['acid'])) $checked='checked="checked" ';
								$out.='<input id="checkbx'.$key.'" type="checkbox" '.$checked.' onChange="filterStart()" name="filter[acid][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
						
					$out.='</div>
					<div>
						<b>Обжарщик:</b>';
						if(!empty($filterData['brand'])){
							foreach($filterData['brand'] AS $key=>$val){
								$out.='<input id="checkbx'.$key.'" type="checkbox"';
								if($brand==$key) $out.=' checked="checked" ';
								$out.=' onChange="filterStart()" name="filter[brand][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
					$out.='
					</div>
					<div>
						<b>Произрастание:</b>';
						if(!empty($filterData['country'])){
							foreach($filterData['country'] AS $key=>$val){
								$out.='<input id="checkbx'.$key.'" type="checkbox" onChange="filterStart()" name="filter[country][]" value="'.$key.'"><label for="checkbx'.$key.'">'.$val.'</label>';
							}
						}
						
					$out.='
					</div>
				</form>
				</div>
				<div class="row">
					<span class="fbutton" style="float:left; margin-bottom:20px;" onClick="ajaxGet(\'coffee::clearForm\')">Очистить фильтр</span>
				</div>
				
			</div>
		</div>';
		
		$site->addScript('
function filterStart(){
	ajaxPost("filterForm","coffee::filterSend");
}
');
	}
	
	return $out;
}