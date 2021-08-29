<?php
// Индикатор того, что в корзине что-то есть
function cartStatus(){
	if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
		return '<span></span>';
	}
	return '';
}