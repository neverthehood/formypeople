<?php
class contact{

    // Заказ звонка
    function callSend(){
        global $settings;
        if(isset($_POST['skf'])){
            $skf=$_POST['skf'];
            $message='<html><head><title>e-climat.by Заказан звонок</title>
                        <style>
                        *{ font-family:Arial, sans-serif; font-size:16px; color:#333333; }
                        table{ border-collapse:colapse; background:#f8f8f8; border:1px solid #999999; }
                        table tr td{ padding:6px 12px; border:1px solid #cccccc; border-collapse:collapse; color:#000000 }
                        table tr td a{ color:#007dbd !important; text-decoration:underline; }
                        </style>
                        </head>
                        <body>
                        <p>'.date("d.m.Y",time()).' в '.date("H:i",time()).' получена заявка на звонок. </p>
                        <p>Информация:</p>
                        <p>
                        <table style="background:#f8f8f8; color:#000000; font-family:Arial, sans-serif; font-size:14px; border:1px solid #000000; border-collapse:colapse;">
                            <tr><td>Форма: </td><td>Заказан звонок</td></tr>
                            <tr><td>Имя: </td><td>'.$skf['name'].'</td></tr>
                            <tr><td>Телефон: </td><td>'.$skf['tel'].'</td></tr>
                            <tr><td>Сообщение/Вопрос: </td><td>'.$skf['qwestion'].'</td></tr>
                        </table>
                        </body>
                        </html>';
            $tel=trim($skf['tel']);
            $tel=onlyDigit($tel);
            mail::mailSender($settings['adminMail'],$settings['adminName'],'Заказан звонок',$message);
	        //mail::mailSender('axiom.genius@gmail.com','Александр','Заказан звонок',$message);
	        //sms::send(375295900609,'В '.date("H:i",time()).' пользователь '.$skf['name'].' заказал звонок на телефон '.$tel.' .');
            return array(
                'callWindowForm'=>'<div class="okMessage"><p>Заявка на звонок отправлена нашим менеджерам. В самое ближайшее время мы перезвоним вам.</p><p><b>ВНИМАНИЕ!</b> Если заявка на звонок оставлена в выходной или праздничный день, то мы перезвоним вам в первой половине следующего рабочего дня. </p></div>'
            );
        }
        return array(
            'stwError'=>'<div class="error">Произошла ошибка. Попробуйте отправить данные еще раз.</div>'
        );
    }
    
}