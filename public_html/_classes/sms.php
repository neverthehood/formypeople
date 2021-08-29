<?php
/*
 *      Класс для отправки SMS через сервис ROCKETSMS.BY
 *
 * */
class sms{
    static $name        =   '192072814'     ;
    static $pass        =   'vtJ3vuXG'      ;
    static $result      =   ''              ;// Результат выполнения последнего запроса
    static $error       =   false           ;

    // Отправка сообщения
    // $tel = один телефонный номер, или группа номеров в массиве
    // $tel = [ INT / ARRAY ] array ( 0 => 375296312033, 2 => 375440000000 )
    // $text = Текст сообщения
    // $timestamp = Время отправки сообщения ( UNIX_TIMESTAMP )
    // $senderName = Альфа-имя (имя отправителя, если таковое существует)
    // $priority [ BOOL ] = Если TRUE, то СМС отправится вне очереди
    static function send($tel,$text,$timestamp=false,$senderName=false,$priority=false){
        //echo '<pre>';
        //print_r(debug_backtrace());
        //echo '</pre>';
        if(!is_array($tel)) {
            $nums=array();
            $nums[]=$tel;
        }
        else $nums=$tel;
        $param['text']=$text;
        if($senderName!==false) $param['sender']=$senderName;
        if($timestamp!==false) $param['timestamp']=$timestamp;
        if($priority!==false) $param['priority']=true;
        $result=array();
        if(!getenv("COMSPEC")) {
            foreach ($nums AS $key => $val) {
                $param['phone'] = $val;
                $result[$key] = self::query('send', $param, $method = 'POST');
            }
        }
        else{
            ajax::javascript('alert("sms: '.implode(', ',$nums).'");');
        }
        return $result;
    }


    // Проверка статуса сообщения
    static function status($id){
        $array['id']=$id;
        $result=self::query('status',$array,'GET');
        if($result===false) return false;
        else return $result['status'];
    }


    // Количество оставшихся СМС
    static function limit(){
        $result=self::balance();
        if($result===false) return false;
        else return $result['credits'];
    }

    // Количество оставшихся денег на счету
    static function money(){
        $result=self::balance();
        if($result===false) return false;
        else return $result['balance'];
    }


    // Возвращает список альфа-номеров для аккаунта
    static function senders(){
        $result=self::query('senders',false,'GET');
        if($result===false) return false;
        else {
            $senders=array();
            foreach($result AS $key=>$val){
                $senders[$val['sender']]=$val['verified'];
            }
            return $senders;
        }
    }


    // Получение баланса
    static function balance(){
        $result=self::query('balance',false,'GET');
        return $result;
    }


    // Отправка запроса и получение результата
    static function query($adr,$param,$method='POST'){
        if($param===false) $param=array();
        $param['username']=self::$name;
        $param['password']=self::$pass;
        $query=http_build_query($param);
        $address='https://api.rocketsms.by/simple/'.$adr;
        $curl=curl_init();
        if($method=='POST') $isPost=true;
        else $isPost=false;
        curl_setopt($curl,CURLOPT_URL,$address);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_POST,$isPost);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$query);
        $result=json_decode(curl_exec($curl),true);
        if($result){
            self::$result=$result;
            if(isset($result['error'])) {
                self::$error=$result['error'];
                return false;
            }
            else return $result;
        }
        else {
            self::$result=false;
            self::$error='Service error';
        }
        return false;
    }

}