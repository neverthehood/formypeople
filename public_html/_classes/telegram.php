<?php
class telegram {
	// Отправка текстового сообщения в чат Телеграм
	static function chatMessage($text){
		global $settings;
		global $error;
	
		if(!isset($settings['telegramBotToken']) || !isset($settings['telegramChatId'])){
			$error.='Класс telegram: В файле settings.ini не найдены настройки telegramBotToken или telegramChatId<br>';
			return false;
		}
		$resp=file_get_contents('https://api.telegram.org/bot'.$settings['telegramBotToken'].'/sendMessage?chat_id='.$settings['telegramChatId'].'&parse_mode=html&text='.urlencode(trim($text)));
		
		$resp=json_decode($resp);
		echo '<pre>';
		print_r($resp);
		echo '</pre>';
		
		if($resp->ok != true){
			$error.='Не удалось отправить сообщение в чат Telegram<br>';
			return false;
		}
		return true;
	}
}