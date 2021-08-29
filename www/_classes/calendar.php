<?php
// Функции для работы с датой, временем, календарем
class calendar{


	// Получение дня недели по дате
	// Возвращает порядковый номер дня недели с 1 до 7 по заданной дате
	// Аргументы: дата в виде Д.М.ГГ или ДД.ММ.ГГГГ
	static function weekDayByDate($date){
		$date=explode(".", $date);
		if(count($date)==3) {
			$day=date("w", mktime(0, 0, 0, $date[1], $date[0], $date[2]));
			if($day==0) return 7;
			else return $day;
			}
		return false;
	}
		
	// Получение к-ва дней в месяце заданного года
	static function daysInMonth($year, $month){
		return date("t", strtotime($year."-".$month));
	}




    /** Возвращает массив-список, содержащий даты указанного месяца
     * @param $year int (with leadeing zero)
     * @param $month int (with leadeing zero)
     */
    static function getDaysArray($year, $month){
	    $date = "01.".$month.".".$year;
        $unix = strtotime($date);
        $month=explode(',',',Январь,Февраль,Март,Апрель,Май,Июнь,Июль,Август,Сентябрь,Октябрь,Ноябрь,Декабрь');
        list($day,$month,$year,$days,$dayOfMonth)=explode(".",date('j.n.Y.t.N', $unix));
        echo ''
	}


}