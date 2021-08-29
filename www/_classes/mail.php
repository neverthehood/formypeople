<?php
class mail{
	public $mailCron=false;		// Отправка почты с помощью CRON или обычная
	
	//  Проверка корректности адреса электронной почты
	static function checkMail($mail) {
	   if (!preg_match("/^[a-z0-9_.-]{1,64}@(([a-z0-9-]+\.)+(com|net|org|mil|".
	   "edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-".
	   "9]{1,3}\.[0-9]{1,3})$/is",$mail)) return false;
	   else return true;
	   }
	
	public function mail_utf8($from, $fromName, $to, $subject = '', $message = '') {
		$header = 'MIME-Version: 1.0' . "\n" . 'Content-type: text/plain; charset=UTF-8'
		. "\n" . 'From: '.$fromName.' <' . $from . ">\n";
		mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header);
		}

	// Функция добавляет сообщение к списку исходящей почты (он будет рассылаться CRONTABS'ом
	//  $name, $email, $subject, $message    
	//  Все в кодировке UTF-8 (при отправке она изменится на Win1251) 
	public function send($name,$email,$subject,$message){
		$out=false;
		// Если в установке указана отправка через КРОН, то просто сохраним сообщение в таблицу
		if($this->mailCron==1){
			if(mysql::query("INSERT INTO `mail` (`name`,`email`,`subject`,`message`) 
				VALUES ('".mysql::escape($name)."','".mysql::escape($email)."','".mysql::escape($subject)."','".mysql::escape($message)."')")) $out=true;
			}
		else {
			// Иначе, отправляем через SENDMAIL
			if(mail::mailSender($email,$name,$subject,$message)) $out=true;
			}
		return $out;
		}
		
		
	// Безопасная отправка почты 
	// Актуально для отправки с помощью AJAX
	public function secureSend($name,$email,$subject,$message){
		// Если работам под Юниксом, то отправляем
		if(!getenv("COMSPEC")) {
			if($this->send($name,$email,$subject,$message)) return true;
			else return false;
			}
		else return true;
		}
		
	
	//  Отправка почты (насильно отправляется через SENDMAIL)
	static function mailSender($to,$toname,$subject,$body){
		global $settings;
		if(!isset($settings['mailCron'])) $settings=parse_ini_file($_SERVER['DOCUMENT_ROOT']."/_core/settings.ini") or die ("Не могу найти файл <b>../_core/settings.ini</b>!");
		$to=utf8_win($to);
		$toname=utf8_win($toname);
		$subject=utf8_win($subject);
		//$body="<html><head><title>Сообщение</title></head><body>".$body."</body></html>";
		//$body=strip_tags(str_replace("<p>","/n/n",$body));
		$body=utf8_win($body);
		$subject = '=?windows-1251?B?'.base64_encode($subject).'?=';
		$from=$settings['mailDaemonEmail'];
		$fromName='=?windows-1251?B?'.base64_encode($settings['mailDaemonName']).'?=';
		$sendmail="/usr/sbin/sendmail -i -f $from -- $to /etc/sendmail.orig.cf"; 
		$fd=popen($sendmail,"w");
		fputs($fd, "To: $toname <$to>\n");
		fputs($fd, "From: $fromName <$from>\n");
		fputs($fd, "Reply-To: <".$settings['adminMail'].">\n");
		fputs($fd, "Subject: $subject\n");
		fputs($fd, "X-Mailer: OmMail[4.2]\n");
		fputs($fd, "MIME-Version: 1.0\n");
		fputs($fd, "Content-Transfer-Encoding: 8bit\n");
		fputs($fd, "Content-type: text/html; charset=windows-1251\n\n");
		fputs($fd, $body);
		$result=pclose($fd);
        if (getenv("COMSPEC")) return true;
		if($result==0) return true;
		else return false;
		}
	
	}

?>