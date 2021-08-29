<?php
function StartPageHit(){
	global $settings;
	$out='';
	$array=coffee::getCoffeeArray("t1.rek='1'",false,3);
	if($array!=false){
		$items=coffee::coffeeToCards($array);
		$out.=$items;
	}
	return $out;
}