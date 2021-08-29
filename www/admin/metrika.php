<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";

class metrika {


    //ID: f29bcce59a94473fb562459a561630fe
    //Пароль: 6077385482914eeeb59bd64eacb4b747
	
	// ID: bf039ba38ae949e88af82782e382cc3b
    // Пароль: c489db1b62fe4d338244e376d86c1340
	
    //Callback URL: https://oauth.yandex.ru/verification_code

    // Токен: AQAAAAAl7lH-AATx1ydLRnXWakoUgbL2rzjCIJI
	
	// Приложения:
	// 1) Получить отладочный токен: https://oauth.yandex.ru/ 
	// 2) Перейти по адресу: https://oauth.yandex.ru/authorize?response_type=token&client_id=Идентификатор приложения
	// 3) Нажать "Подтвердить"
	// 4) OAuth-сервер перенаправит вас на адрес из поля Callback URL, добавив данные токена после символа #
	// Если токен выдать не удалось, то OAuth-сервер добавляет к адресу код ошибки: 
	// 		http://www.example.com/token#
    // 	    где error=<код ошибки>
	//		Коды ошибок:
	//			access_denied
	//			unauthorized_client  -  приложение откролнено, или ожидает разрешения доступа
	//
	//
	//


    static $token   = "AQAAAAASwjdlAAULMbw28Upm1EouumitrpX75Yc";
	static $apiUrl = "https://api-metrika.yandex.ru/stat/v1/";
	static $counterId = "44943406";  // счетчик 
	static $dateFrom ="";
	static $dateTo ="";

	static function setPeriod(){
	    global $item, $svodka, $date1, $date2;
	    $currentTime=time();
	    $date2=date("Ymd",$currentTime);

	    //$date1=$date2;
	    $daySec=86400;// кво секунд в сутках
	    if($item=='today'){
	        $date1=$date2;
	    }
	    elseif($item=='yesterday'){
	        $date1=date("Ymd",$currentTime-$daySec);
	    }
	    elseif($item=='week'){
	        $date1=date("Ymd",$currentTime-($daySec*7));
	    }
	    elseif($item=='month'){
	        $date1=date("Ymd",$currentTime-($daySec*30));
	    }
	    elseif($item=='quartal'){
	        $date1=date("Ymd",$currentTime-($daySec*90));
	    }
	    elseif($item=='year'){
	        $date1=date("Ymd",$currentTime-($daySec*365));
	    }

        $item=$svodka;
        if($svodka=="default") return self::allStat($date1,$date2);
        return self::show();
	}


	// Установка даты
	static function setDateFrom($date){
	    self::$dateFrom=$date;
	}
	static function setDateTo($date){
	    self::$dateTo=$date;
	}

	/*
	 ЧИСЛО ВИЗИТОВ - ym:s:visits,
ПРОСМОТРОВ СТРАНИЦ - ym:s:pageviews,
ПОСЕТИТЕЛЕЙ - ym:s:users,
ОТКАЗОВ - ym:s:bounceRate,
ГЛУБИНА ПРОСМОТРА - ym:s:pageDepth,
ВРЕМЯ НА САЙТЕ - ym:s:avgVisitDurationSeconds ,
ДОЛЯ РОБОТОВ %% - ym:s:robotPercentage,
СРЕДНЕЕ К-ВО ВИЗИТОВ В ЧАС - ym:s:visitsPerHour,
ДОЛЯ УНИКАЛЬНЫХ ПОСЕТИТЕЛЕЙ %% - ym:s:percentNewVisitors,
КОЛИЧЕСТВО НОВЫХ ПОСЕТИТЕЛЕЙ - ym:s:newUsers,
ДОЛЯ МУЖЧИН В ВИЗИТАХ - ym:s:manPercentage ,
ДОЛЯ ЖЕНЩИН - ym:s:womanPercentage,
ВОЗРАСТ МЕНЕЕ 18 ЛЕТ %% - ym:s:under18AgePercentage,
ВОЗРАСТ 18-24 ГОДА - ym:s:upTo24AgePercentage
ВОЗРАСТ 25-34 ГОДА - ym:s:upTo34AgePercentage,
ВОЗРАСТ 35-44 ГОДА - ym:s:upTo44AgePercentage,
ВОЗРАСТ БОЛЕЕ 45 лет - ym:s:over44AgePercentage,
ДОЛЯ ВИЗИТОВ С МОБИЛЬНЫХ УСТРОЙСТВ - ym:s:mobilePercentage,
	 */

    static $group=array(
        'gender'=>'Пол',
        'age'=>'Возраст'
    );
	static $metrika=array(
	    'ym:s:users'=>array(
	        'name'          =>'Посетители',
	        'description'   =>'Количество уникальных посетителей.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:pv:users'=>array(
	        'name'          =>'Посетители',
	        'description'   =>'Количество уникальных посетителей.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall',
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:s:newUsers'=>array(
	        'name'          =>'Новые посетители',
	        'description'   =>'Количество новых посетителей.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#ffd963',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:s:visits'=>array(
	        'name'          =>'Визиты',
	        'description'   =>'Суммарное количество визитов.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:pv:visits'=>array(
	        'name'          =>'Визиты',
	        'description'   =>'Суммарное количество визитов.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:s:pageviews'=>array(
	        'name'          =>'Просмотры',
	        'description'   =>'Число просмотров страниц на сайте за отчетный период.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:pv:pageviews'=>array(
	        'name'          =>'Просмотры',
	        'description'   =>'Число просмотров страниц на сайте за отчетный период.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'int',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:s:affinityIndexInterests'=>array(
	        'name'          =>'Аффинити-индекс',
	        'description'   =>'Отношение доли посетителей с заданным интересом на этом сайте к среднестатистической доле посетителей с этим же интересом на всех сайтах интернета.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall',
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>false
	    ),
	    'ym:s:bounceRate'=>array(
	        'name'          =>'Отказы',
	        'description'   =>'Доля визитов, в рамках которых состоялся лишь один просмотр страницы, продолжавшийся менее 15 секунд.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmallBlue',
	        'color'         =>'#44a3d0',
	        'type'          =>'perc',
	        'group'         =>false,
	        'svLink'        =>'traffic'
	    ),
	    'ym:s:pageDepth'=>array(
	        'name'          =>'Глубина просмотра',
	        'description'   =>'Количество страниц, просмотренных посетителем во время визита.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmallBlue',
	        'color'         =>'#44a3d0',
	        'type'          =>'double',
	        'group'         =>false,
	        'svLink'        =>'deepness_depth'
	    ),
	    'ym:s:avgVisitDurationSeconds'=>array(
	        'name'          =>'Время на сайте',
	        'description'   =>'Средняя продолжительность визита в минутах и секундах.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmallBlue',
	        'color'         =>'#44a3d0',
	        'type'          =>'double',
	        'group'         =>false,
	        'svLink'        =>'deepness_time'
	    ),
	    'ym:s:robotPercentage'=>array(
	        'name'          =>'Доля роботов',
	        'description'   =>'Доля посетителей, предположительно являющихся роботами.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'chartSmallBlue',
	        'color'         =>'#77b6e7',
	        'type'          =>'perc',
	        'group'         =>false
	    ),
	    'ym:s:visitsPerHour'=>array(
	        'name'          =>'Визитов в час',
	        'description'   =>'Среднее количество визитов за 1 час.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'double',
	        'group'         =>false
	    ),
	    'ym:s:percentNewVisitors'=>array(
	        'name'          =>'Уникальные',
	        'description'   =>'Процент уникальных посетителей, посетивших сайт в отчетном периоде, активность которых включала их самый первый за всю историю накопления данных визит на сайт.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'chartSmallBlue',
	        'color'         =>'#77b6e7',
	        'type'          =>'perc',
	        'group'         =>false
	    ),
	    'ym:s:manPercentage'=>array(
	        'name'          =>'Мужчины',
	        'description'   =>'Доля посетителей мужского пола',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'gender'
	    ),
	    'ym:s:womanPercentage'=>array(
	        'name'          =>'Женщины',
	        'description'   =>'Доля посетителей женского пола',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'gender'
	    ),
	    'ym:s:under18AgePercentage'=>array(
	        'name'          =>'До 18 лет',
	        'description'   =>'Доля визитов посетителей, возраст которых менее 18 лет',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'ym:s:upTo24AgePercentage'=>array(
	        'name'          =>'18 - 24 года',
	        'description'   =>'Доля визитов посетителей, возраст которых от 18 до 24 лет.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'ym:s:upTo34AgePercentage'=>array(
	        'name'          =>'25 - 34 года',
	        'description'   =>'Доля визитов посетителей, возраст которых от 25 до 34 лет.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'ym:s:upTo44AgePercentage'=>array(
	        'name'          =>'35 - 44 года',
	        'description'   =>'Доля визитов посетителей, возраст которых от 35 до 44 лет.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'ym:s:over44AgePercentage'=>array(
	        'name'          =>'45 лет и старше',
	        'description'   =>'Доля визитов посетителей, возраст которых 45 и более лет.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'age'=>array(
	        'name'          =>'Возраст',
	        'description'   =>'Возраст посетителей',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'perc',
	        'group'         =>'age',
	        'svLink'        =>'age'
	    ),
	    'ym:s:mobilePercentage'=>array(
	        'name'          =>'Мобильные',
	        'description'   =>'Доля визитов и хитов, совершенных с мобильных устройств.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'chartSmallBlue',
	        'color'         =>'#77b6e7',
	        'type'          =>'perc',
	        'group'         =>false,
	        'svLink'        =>'tech_devices'
	    ),
	    'ym:pv:mobilePercentage'=>array(
	        'name'          =>'Мобильные',
	        'description'   =>'Доля визитов и хитов, совершенных с мобильных устройств.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'chartSmallBlue',
	        'color'         =>'#77b6e7',
	        'type'          =>'perc',
	        'group'         =>false,
	        'svLink'        =>'tech_devices'
	    ),
	    'ym:s:visitsPerDay'=>array(
	        'name'          =>'Визитов в день',
	        'description'   =>'Среднее количество визитов в день',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'double',
	        'group'         =>false
	    ),
	    'ym:pv:visitsPerDay'=>array(
	        'name'          =>'Визитов в день',
	        'description'   =>'Среднее количество визитов в день',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'double',
	        'group'         =>false
	    ),
	    'ym:pv:pageviewsPerDay'=>array(
	        'name'          =>'Просмотров в день',
	        'description'   =>'Средняя глубина просмотра в день.',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'double',
	        'group'         =>false
	    ),
	    'ym:s:browser'=>array(
	        'name'          =>'Браузеры',
	        'description'   =>'',
	        'diagram'       =>'columns',
	        'diagramSvodka' =>'lineSmall', /* Линии, без точек  */
	        'color'         =>'#8bc554',
	        'type'          =>'double',
	        'group'         =>false
	    ),

	);

	// Отдельные отчеты на основе шаблонов
    static $templates=array(
        'traffic'=>array(
            'name'          =>'Посещаемость',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>true,
            'diagramMax'    =>false,
            'query'         =>false,
            'labelDate'     =>true
        ),
        'sources_search_phrases'=>array(
            'name'          =>'Поисковые фразы',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>5,
            'query'         =>false,
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'conversion'=>array(
            'name'          =>'Конверсии',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>false,
            'query'         =>false,
            'labelDate'     =>false
        ),
        'hourly'=>array(
            'name'          =>'Визиты по времени',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>false,
            'query'         =>false,
            'labelDate'     =>false
        ),
        'geo_country'=>array(
            'name'          =>'География',
            'diagram'       =>'pieBig',
            'order'         =>'ym:pv:pageviews',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:pv:regionCountry&metrics=ym:pv:pageviews,ym:pv:users,ym:pv:mobilePercentage,ym:pv:pageviewsPerDay',
            'labelDate'     =>false
        ),
        'interests'=>array(
            'name'          =>'Интересы',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>4,
            'query'         =>false,
            'labelDate'     =>false
        ),
        'tech_browsers'=>array(
            'name'          =>'Браузеры',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>8,
            'query'         =>'data/?dimensions=ym:s:browser&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'tech_display_groups'=>array(
            'name'          =>'Группы дисплеев',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>16,
            'query'         =>'data/?dimensions=ym:s:screenFormat&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'tech_display'=>array(
            'name'          =>'Разрешение экранов',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>12,
            'query'         =>'data/?dimensions=ym:s:screenWidth,ym:s:screenHeight&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'tech_platforms'=>array(
            'name'          =>'Операционные системы',
            'diagram'       =>'pieBig',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>10,
            'query'         =>'data/?dimensions=ym:s:operatingSystemRoot&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'tech_devices'=>array(
            'name'          =>'Устройства',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>10,
            'query'         =>'data/?dimensions=ym:s:deviceCategory&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'tech_devicebrand'=>array(
            'name'          =>'Модели устройств',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>8,
            'query'         =>'data/?dimensions=ym:s:mobilePhoneModel&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'sources_summary'=>array(
            'name'          =>'ИСТОЧНИКИ',
            'diagram'       =>'pieBig',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>8,
            'query'         =>'data/?dimensions=ym:s:<attribution>TrafficSource&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'search_engines'=>array(
            'name'          =>'Поисковые системы',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>8,
            'query'         =>'data/?dimensions=ym:s:<attribution>SearchEngineRoot&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'sources_social'=>array(
            'name'          =>'Социальные сети',
            'diagram'       =>'pieBig',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:s:<attribution>SocialNetwork,ym:s:<attribution>SocialNetworkProfile&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'age'=>array(
            'name'          =>'Возраст',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:s:ageInterval&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'gender'=>array(
            'name'          =>'Пол',
            'diagram'       =>'pieBig',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:s:gender&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'age_gender'=>array(
            'name'          =>'Пол и возраст',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:s:ageInterval,ym:s:gender&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'deepness_depth'=>array(
            'name'          =>'Глубина просмотра',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>6,
            'query'         =>'data/?dimensions=ym:s:pageViewsInterval&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'deepness_time'=>array(
            'name'          =>'Время просмотра',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>9,
            'query'         =>'data/?dimensions=ym:s:visitDurationInterval&sort=ym:s:visitDurationInterval&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'loyalty_visits'=>array(
            'name'          =>'Общее число визитов',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>9,
            'query'         =>'data/?dimensions=ym:s:userVisitsInterval&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'loyalty_period'=>array(
            'name'          =>'Периодичность визитов',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>9,
            'query'         =>'data/?dimensions=ym:s:userVisitsInterval&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),
        'content_entrance'=>array(
            'name'          =>'Страница входа',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>3,
            'query'         =>'data/?dimensions=ym:s:startURLHash&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'content_exit'=>array(
            'name'          =>'Страница выхода',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>3,
            'query'         =>'data/?dimensions=ym:s:endURLHash&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'popular'=>array(
            'name'          =>'Популярное',
            'diagram'       =>'columns',
            'order'         =>'ym:pv:pageviews',
            'diagramReverse' =>false,
            'diagramMax'    =>3,
            'query'         =>'data/?dimensions=ym:pv:URLHash&metrics=ym:pv:pageviews,ym:pv:users',
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'titles'=>array(
            'name'          =>'Заголовки страниц',
            'diagram'       =>'columns',
            'order'         =>'ym:pv:pageviews',
            'diagramReverse' =>false,
            'diagramMax'    =>3,
            'query'         =>'data/?dimensions=ym:pv:title,ym:pv:URLHash&metrics=ym:pv:pageviews,ym:pv:users',
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'sources_sites'=>array(
            'name'          =>'Сайты',
            'diagram'       =>'columns',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>3,
            'query'         =>'data/?dimensions=ym:s:externalRefererHash&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false,
            'diagramHide'   =>true
        ),
        'tech_cookies'=>array(
            'name'          =>'Наличие Cookies',
            'diagram'       =>'pieBig',
            'order'         =>'ym:s:visits',
            'diagramReverse' =>false,
            'diagramMax'    =>16,
            'query'         =>'data/?dimensions=ym:s:cookieEnabled&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds',
            'labelDate'     =>false
        ),

	);

	// Отображение данных и диаграммы
	static function showMetrika($da,$malias=false, $date1=false, $date2=false){
	    $time=time();
        if($date1===false) $date1=date("Ymd",$time-30*24*60*60);
        if($date2===false) $date2=date("Ymd",$time);

	    $out='';
	    $z=$da->query->metrics;
        $s=$da->data;

        $paginator='';





//        echo '<pre>';
//        print_r($s);
//        echo '</pre>';

	    if(isset($z)){
	        // Формируем массив с данными
	        $graph=array(
	            'label'=>array(),
	            'data'=>array(),
	            'name'=>self::$metrika[self::$templates[$malias]['order']]['name']
	        );

	        $table=array(
	            'headers'=>array(),
	            'counts'=>array(),
	            'data'=>array()
	        );
	        $table['headers'][]='&nbsp;';



	        foreach($z AS $mk=>$mv){
	            //echo 'order='.self::$templates[$malias]['order'].' mv='.$mv;
                $table['headers'][]=self::$metrika[$mv]['name'];
                //$dataType=self::$metrika[$mv]['type'];


	            if($mv==self::$templates[$malias]['order']){
	                //echo 'YEAAA!!';
	                // Нашли ключ массива, по которому формируется график.
	                // Теперь формируем массив с данными
	                foreach($s AS $key=>$val){


	                    $ndata=$val->dimensions[0]->name;
	                    // Обработка некоторых отчетов
	                    if($malias=='tech_display'){
	                        $ndata.=' x '.$val->dimensions[1]->name;
	                    }
	                    elseif($malias=='sources_summary'){
                            $ndata=str_replace("Переходы из поисковых систем","Поисковые системы",$ndata);
                            $ndata=str_replace("Внутренние переходы","Внутр. переходы",$ndata);
                            $ndata=str_replace("Переходы по ссылкам на сайтах","Другие сайты",$ndata);
                            $ndata=str_replace("Переходы из социальных сетей","Соц. сети",$ndata);
                            $ndata=str_replace("Переходы по рекламе","Реклама",$ndata);
                        }
	                    elseif($malias=='age_gender'){
	                        $gnd=$val->dimensions[1]->name;
	                        $gnd=str_replace("мужской","Муж. ",$gnd);
	                        $gnd=str_replace("женский","Жен. ",$gnd);
	                        $gnd=str_replace("Не определено","? ",$gnd);
	                        $ndata=$gnd.' '.$ndata;
	                    }
	                    elseif($malias=='loyalty_period'){
                            $ndata.=' '.pluralForm(onlyDigit($ndata),'день,дня,дней');
                        }


	                    $data=$ndata;
                        if(self::$templates[$malias]['labelDate']==true){
                            list($y,$m,$d)=explode('-',$val->dimensions[0]->name);
	                        $data=$d.'.'.$m.'.'.$y;
	                        $ny=$y;
	                        if(date("Y",time())==$y) $ny=date('y',time());
	                        $ndata=$d.'.'.$m.'.'.$ny;
                        }

	                    $graph['label'][]=$ndata;
                        $graph['data'][]=$val->metrics[$mk];

                        $narr=$val->metrics;

                        // Пагинация для некоторых отчетов
                        if($malias=='sources_search_phrases' || $malias=='content_entrance' || $malias=='content_exit' || $malias=='popular' || $malias=='sources_sites' || $malias=='titles' ){
                            if($key===0){
                                //$limit=$da->query->limit;
                                $offset=$da->query->offset-1;
                                $totals=$da->total_rows;
                                $p=ceil($offset/100);

                                if($totals>100) $paginator=paginate($totals,$p,'<span onClick="getSvodka(\''.$malias.'\',\'&p=%1\')">%2</span>',100,6,'Страницы:');
                            }
                        }

                        if($malias=='sources_search_phrases'){
                            $data='<span class="sengIcon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span><a class="sengLink" target="_blank" href="'.$val->dimensions[0]->url.'">'.$data.'</a>';
                        }
                        elseif($malias=='sources_summary'){
                            $data=str_replace('Поисковые системы','<span class="sengLink" onClick="getSvodka(\'sources_sites\')">Поисковые системы</span>', $data);
                            $data=str_replace('Другие сайты','<span class="sengLink" onClick="getSvodka(\'search_engines\')">Другие сайты</span>', $data);
                            $data=str_replace('Соц. сети','<span class="sengLink" onClick="getSvodka(\'sources_social\')">Соц. сети</span>', $data);
                        }
                        elseif($malias=='content_entrance' || $malias=='content_exit' || $malias=='popular' || $malias=='sources_sites'){


                            $pgurl=$data;
                            if(mb_strlen($pgurl,'utf-8')>64) $pgurl=mb_substr($pgurl,0,64).'...';
                            $data='<span class="sengIcon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span><a class="sengLink" target="_blank" href="'.htmlspecialchars(trim($data)).'">'.$pgurl.'</a>';
                        }
                        elseif($malias=='titles'){
                            $data='<span class="sengIcon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[1]->favicon.'/\')"></span><a class="sengLink" target="_blank" href="'.htmlspecialchars(trim($val->dimensions[1]->name)).'" title="'.htmlspecialchars(trim($val->dimensions[1]->name)).'">'.$data.'</a>';
                        }
                        elseif($malias=='search_engines'){
                            $data='<span class="sengIcon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span><span class="sengLink">'.$data.'</span>';
                        }
                        elseif($malias=='sources_social'){
                            $data='<span class="sengIcon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span>'.$data;
                        }

                        array_unshift($narr,$data);
                        $table['data'][]=$narr;
	                }
	                // Переворачиваем массивы в графике, чтобы даты шли в прямом порядке
	                if(self::$templates[$malias]['diagramReverse']==true){
	                    $graph['label']=array_reverse($graph['label']);
	                    $graph['data']=array_reverse($graph['data']);
	                }

	                $table['counts']=$da->totals;
                    array_unshift($table['counts'],'Итого и средние');
	            }
	        }


//	        echo '<pre>';
//	        print_r($graph);
//	        print_r($table);
//	        echo '</pre>';

            $colored='Blue';
	        // Преобразуем некоторые данные перед выводом
	        // Обрезание длинного массива при формировании чарта
            if(self::$templates[$malias]['diagramMax']!==false){
                $graph['label']=array_slice($graph['label'],0,self::$templates[$malias]['diagramMax'],true);
                $graph['data']=array_slice($graph['data'],0,self::$templates[$malias]['diagramMax'],true);
                $colored='Colored';
            }

            $diagramHide=false;
            if(isset(self::$templates[$malias]['diagramHide'])) $diagramHide=self::$templates[$malias]['diagramHide'];

            $canvas='';
            $diagramType='columns';
            if(self::$templates[$malias]['diagram']=='pieBig') $diagramType='pieBig';
            if($diagramHide===false) {
                if($diagramType=='columns') $canvas='<canvas class="axiomChart" data-chtype="big'.$colored.'Chart" data-labels="'.implode("|",$graph['label']).'" data-values="'.implode('|',$graph['data']).'" id="myChart'.time().'" data-color="#479de0" width="1000px" height="400px" style="padding-top:40px; float:none; clear:both;"></canvas>';
                else $canvas='<canvas class="axiomChart" data-chtype="pieBig" data-labels="'.implode("|",$graph['label']).'" data-values="'.implode('|',$graph['data']).'" id="myChart'.time().'" width="1000px" height="400px" style="padding-top:40px; float:none; clear:both;"></canvas>';
            }

            if($paginator!='') $paginator='<div class="field" style="text-align:right;">'.$paginator.'</div>';

	        $out.='<br><div class="field"><h3>'.self::$templates[$malias]['name'].' за период с '.substr($date1,6,2).'.'.substr($date1,4,2).'.'.substr($date1,0,4).' по '.substr($date2,6,2).'.'.substr($date2,4,2).'.'.substr($date2,0,4).'</h3></div>'.$paginator.'
	               <div class="field" style="background:#ffffff">'.$canvas.'</div>';
	        // Таблица
	        $out.='<table class="cmstable4"><tr><th>'.implode('</th><th style="width:100px; text-align:right; line-height:16px;">',$table['headers']).'</th></tr>';
	        array_unshift($table['data'],$table['counts']);
	        foreach($table['data'] AS $key=>$val){
	            $style='';
	            if($key==0) $style=' style="background:#e0e0e0; height:40px;" ';
	            $nval=array();
	            // Преобразование данных перед выводом
	            foreach($val AS $k=>$v){
	                if($k>=1){
                        $dataType=self::$metrika[$z[($k-1)]]['type'];
                        if($dataType=='perc') $v=round($v,1).' %';
                        elseif($dataType=='double') {
                            // Преобразование секунд в минуты и секунды
                            if($z[($k-1)]=='ym:s:avgVisitDurationSeconds')  $v = date("i:s", mktime(0, 0, $v));
                            else $v=round($v,2);
                        }
                    }
                    $nval[]=$v;
	            };

	            $out.='<tr'.$style.'><td>'.implode('</td><td style="text-align:right;">',$nval).'</td></tr>';
	        }
	        $out.='</table>'.$paginator;

	    }
	    return '<div class="field">'.$out.'</div>';
	}

	// Преобразование даты вида YYYYMMDD в timestamp
	static function dStamp($date){
	    $d=substr($date,6,2);
	    $m=substr($date,4,2);
	    $y=substr($date,0,4);
	    return mktime(0,0,0,$m,$d,$y);
	}

	/* Отображение сводки за заданный период */
    static function showSvodka($da, $date1=false, $date2=false){
        $time=time();
        if($date1===false) $date1=date("Ymd",$time-30*24*60*60);
        if($date2===false) $date2=date("Ymd",$time);
	    $out='';
	    $dop=array();
	        $z=$da->query->metrics;
            $s=$da->data[0];
	        if(isset($z)){
	            //foreach($z)
	            $counter=0;
	            foreach($z AS $key=>$val){
	                if(isset(self::$metrika[$val])){
	                    $counter++;
	                    $mName=self::$metrika[$val]['name'];
	                    $mDescription=self::$metrika[$val]['description'];
	                    $diagram=self::$metrika[$val]['diagramSvodka'];
	                    $mainColor=self::$metrika[$val]['color'];
	                    $totals=$da->totals;
	                    $suffix='';
	                    $type=self::$metrika[$val]['type'];
	                    $value=$totals[0][$key];
	                    if($type=="perc") {
                            $suffix='%';
                            $value=triada($value,2);
                        }
                        if($type=='int'){
                            $value=triada($value);
                        }
                        if($type=='double'){
                            $value=triada($value,1);
                        }

	                    $data=$s->metrics[$key];
	                    $i=$da->time_intervals;
	                    $intervals=array();

	                    if(isset($i)){
	                        foreach($i AS $ik=>$v){
	                            list($y,$m,$d)=explode("-",$v[1]);
	                            $dd=$d.'.'.$m.'.'.$y;
	                            $intervals[]=$dd;
	                        }
	                    }

                        // Обработаем время
                        if($val=='ym:s:avgVisitDurationSeconds') {
                            $mytime = $totals[0][$key];
                            $value = date("i:s", mktime(0, 0, $mytime));
                        }

                        $width=270;
                        $height=100;
                        $class="statDefaultWidget";

                        if($diagram=='lineSmallBlue'){
                            $height=80;
                            $class="statSmallWidget";
                        }

                        if($diagram=='chartSmallBlue'){
                            $height=80;
                            $class="chartSmallBlue";
                        }

                        // Специальная обработка некоторых полей
                        if($val=='ym:s:manPercentage'){
                            $all=$totals[0][0];// Первая метрика - общее число посетителей
                            $manCount=round($all/100*$value);
                            $womenCount=$all-$manCount;
                            $out.='<div class="stWidget statPieWidget">
	                    <div class="field"><a class="mtLink" name="_st_gender" onClick="getSvodka(\'gender\')">Пол</a></div>
	                    <canvas class="axiomChart" width="270px" height="330px" data-color="'.$mainColor.'" data-chtype="pie" data-labels="Мужчины ('.$value.'%)|Женщины ('.(100-$value).'%)" data-values="'.$manCount.'|'.$womenCount.'" id="myChart'.$counter.'"></canvas>
	                    </div>';
                        }
	                    else {
	                        if(self::$metrika[$val]['group']!=false){
	                            //echo '<pre>';
	                            //print_r($da);
	                            //echo '</pre>';
	                            $grpName=self::$metrika[$val]['group'];

	                            if(!isset($dop[$grpName])){
	                                $dop[$grpName]['name']=self::$group[self::$metrika[$val]['group']];
	                                $dop[$grpName]['chtype']='pie';
	                                $dop[$grpName]['labels']=array();
	                                $dop[$grpName]['values']=array();
	                                $dop[$grpName]['suffix']=$suffix;
	                                $dop[$grpName]['id']="myChart".$counter;
	                                $out.='[['.$grpName.']]';
	                            }
	                            $oldValue=$value;
                                if($suffix=='%'){
                                    $all=$totals[0][0];// Первая метрика - общее число посетителей
                                    $value=round($all/100*$value);
                                    $oldValue=' ('.round($oldValue,1).'%) ';
                                }
                                else $oldValue='';
	                            $dop[$grpName]['labels'][]=self::$metrika[$val]['name'].$oldValue;
	                            $dop[$grpName]['values'][]=$value;
	                        }
                            else {
                                $mName=self::$metrika[$val]['name'].' ';
                                if(isset(self::$metrika[$val]['svLink'])){
                                    $mName='<a class="mtLink" name="_'.$val.'" onClick="getSvodka(\''.self::$metrika[$val]['svLink'].'\')">'.$mName.'</a>';
                                }
                                $out.='<div class="stWidget '.$class.'">
	                    <div class="field" title="'.self::$metrika[$val]['description'].'">'.$mName.' : '.$value.$suffix.'</div>
	                    <canvas class="axiomChart" width="'.$width.'px" height="'.$height.'px" data-color="'.$mainColor.'" data-chtype="'.$diagram.'" data-labels="'.implode("|",$intervals).'" data-values="'.implode('|',$data).'" id="myChart'.$counter.'"></canvas>
	                    </div>';
                            }
                        }
	                }

	            }
	        }


	    if(!empty($dop)){
	        foreach($dop AS $key=>$val){
	            $mName=$val['name'];
                if(isset(self::$metrika[$key]['svLink'])){
                    $mName='<a class="mtLink" name="lnk_'.$key.'" title="'.self::$metrika[$key]['description'].'" onClick="getSvodka(\''.self::$metrika[$key]['svLink'].'\')">'.$mName.'</a>';
                }
	            $content='<div class="stWidget statPieWidget"><div class="field">'.$mName.'</div><canvas class="axiomChart" width="270px" height="330px" data-color="" data-chtype="pie" data-labels="'.implode('|',$val['labels']).'" data-values="'.implode('|',$val['values']).'" id="'.$val['id'].'"></canvas></div>';
	            $out=str_replace('[['.$key.']]',$content,$out);
	        }
	    }
	    return $out;
	}

    // Ключевые слова
    static function showKeywords($da){
	    $out='';
        $z=$da->data;
	    if(isset($z)){
	        $out.='<div class="stWidget wideWidget"><div class="field"><a class="mtLink" name="kphr" onClick="getSvodka(\'sources_search_phrases\')">Поисковые фразы</a></div><table class="cmstable3">';
	        foreach($z AS $val){

                $out.='<tr><td><span class="siteicon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span></td><td><a class="smallgrey" target="_blank" href="'.$val->dimensions[0]->url.'" title="'.htmlspecialchars($val->dimensions[1]->name).'">'.$val->dimensions[0]->name.'</a></td><td style="width:60px; text-align:right;">'.$val->metrics[0].'</td></tr>';
	        }
	        $out.='</table></div>';
	    }
	    return $out;
	}

	/* Отображение сводки за заданный период */
    static function showPglist($da){
	    $out='';
        $z=$da->data;
	    if(isset($z)){
	        $out.='<div class="stWidget wideWidget"><div class="field"><a class="mtLink" name="kppop" onClick="getSvodka(\'popular\')">Популярные страницы</a></div><table class="cmstable3">';
	        foreach($z AS $val){
	            $url=$val->dimensions[0]->name;
	            if(mb_strlen($url)>48) $url=mb_substr($url,0,48).'...';
                $out.='<tr><td><span class="siteicon" style="background-image: url(\'//favicon.yandex.net/favicon/'.$val->dimensions[0]->favicon.'/\')"></span></td><td><a class="smallgrey" target="_blank" href="'.$val->dimensions[0]->name.'" title="'.htmlspecialchars($val->dimensions[1]->name).'">'.$url.'</a></td><td style="width:60px; text-align:right;">'.$val->metrics[0].'</td></tr>';
	        }
	        $out.='</table></div>';
	    }
	    return $out;
	}



    // Отображение заданного отчета
	static function show(){
	    global $item, $date1, $date2, $p;
	    if(!isset($p)) $p=0;

	    $out='';
        //echo 'svodka='.$item.'<br>
	    //date1='.$date1.'<br>
	    //date2='.$date2.'<br>';
	    // group=day / month / year


        if(self::$templates[$item]['query']===false) {
            $qw=$item;
            if($p>0) $qw.='&offset='.((100*$p)+1);
            $data=self::presetQuery($qw, $date1, $date2);
        }
        else {
            $qw=self::$templates[$item]['query'];
            if($p>0) $qw.='&offset='.((100*$p)+1);
            $data=self::getMetrika($qw, $date1, $date2);
        }

        if(isset($data->errors[0])){
            $out.='<div class="error">'.$data->errors[0]->message.'</div>';
        }
        else {
            $out.=self::showMetrika($data, $item, $date1, $date2);
        }
	    //$uri="data/bytime?top_keys=3&group=day&metrics=ym:s:visits,ym:s:pageviews,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds,ym:s:robotPercentage,ym:s:visitsPerHour,ym:s:percentNewVisitors,ym:s:newUsers,ym:s:manPercentage,ym:s:womanPercentage,ym:s:under18AgePercentage,ym:s:upTo24AgePercentage,ym:s:upTo34AgePercentage,ym:s:upTo44AgePercentage,ym:s:over44AgePercentage,ym:s:mobilePercentage,ym:s:visitsPerDay";
	    //$data=self::getMetrika($uri);

	    return $out;
	}

    static function init(){
		global $admin, $date1, $date2;
		global $settings;
        //echo 'date1='.$date1.'<br>date2='.$date2; 20160701

        if(!isset($_REQUEST['date1'])){
            if(isset($_SESSION['metrika']['period'])) {
                list($date1,$date2)=explode("-",$_SESSION['metrika']['period']);
            }
        }

        $time=time();
        if(!isset($date1)){
            $d1=date("d.m.Y",($time-30*24*60*60));// По умолчанию сводка за месяц
            $date1=date("Ymd",($time-30*24*60*60));
        }
        else $d1=substr($date1,6,2).'.'.substr($date1,4,2).'.'.substr($date1,0,4);
        if(!isset($date2)){
            $d2=date("d.m.Y",$time);
            $date2=date("Ymd",$time);
        }
        else $d2=substr($date2,6,2).'.'.substr($date2,4,2).'.'.substr($date2,0,4);
        $_SESSION['metrika']['period']=$date1.'-'.$date2;




		if(isset($admin)){
		    $admin->addStyle('
a.mtLink{
color:#000000;
}
a.mtLink:hover{
color:#006acf;
}
.siteicon{
width:16px;
height:16px;
line-height:16px;
display:block;
float:left;
}
ul.lMenu li{
    background:#242834;
    color:#cbd4d6;
    display:block;
    float:none;
    clear:both;
    border-bottom:1px solid #343a4b;
    padding:3px 0 3px 8px;
    cursor:default;
    margin:0;
    font-size:14px;
}
ul.lMenu li i, ul.lMenu li a, ul.lMenu li b{ color:#cbd4d6 }
ul.lMenu li i{ float:left; margin-top:2px; }
ul.lMenu li.sLink{
    cursor:pointer;
    line-height:18px;
}
ul.lMenu li.sLink:hover{
    background:#343a4b;
}
ul.lMenu li.passive{
    background:#000000;
}
ul.lMenu li.passive i{
    color:#f47300;
}
ul.lMenu li.passive b{
    color:#ffffff;
}
.lMenu li.slActive, .lMenu li.slActive:hover{
    background:#f57300 !important;
    color:#ffffff !important;
}
li.slActive i, li.slActive a, li.slActive b{
    color:#ffffff !important;
}
li.sLink a{
    width:100%;
    display:block;
}
.sengIcon{
    width: 16px;
    height: 16px;
    float: left;
    margin:0 8px 0 0;
    display: block !important;
}
.sengLink{
    color:#00457a;
    text-decoration:none;
    border-bottom:1px dotted #718a9d;
    cursor:pointer;
}
.sengLink:hover{
    color:#dd0005;
    border-bottom:1px solid #dd0005;
}
.stWidget{
    display:block;
    float:left;
    border:1px solid #cccccc;
    border-radius:6px;
    padding:10px;
    box-sizing:border-box;
    background:#ffffff;
    margin:0 20px 20px 0;
}
.statDefaultWidget{
    width:300px;
    height:180px;
}
.statPieWidget{
    width:300px;
    height:490px;
}
.statSmallWidget, .chartSmallBlue{
    width:300px;
    height:150px;
}
.bigBlueChart, .bigColoredChart{
    width:1000px;
    height:300px;
    background:#ffffff;
}

.statSmallWidget canvas{
    float:right;
}
.stWidget div.field{
    display:block;
    float:none;
    clear:both;
    border-bottom:1px solid #eeeeee;
    font-weight:bold;
    color:#333333;
    margin:6px 0 10px 0;
    padding:0;
}
.wideWidget{
    width:360px;
    margin-right:0 !important;
}
.wideWidget .cmstable3 tr td a.smallgrey{
    line-height:22px;
}
');
		    $admin->addJs($settings['protocol'].$settings['siteUrl'].'/admin/js/chart.min.js', false);
			$admin->addBodyScript('
getId("ajaxWindow").style.visibility = "visible";
var currentLink="default";
var curPeriod="month";
var dtUpdateEnabled=true;

function setPeriod(pName,perName){
    classRemove("bt-"+curPeriod,"disabled");
    classAdd("bt-"+pName,"disabled");
    curPeriod=pName;
    dtUpdateEnabled=false;
    var d=explode("-",perName);
    getId("date1").value=d[0];
    getId("date2").value=d[1];
    ajaxGet("metrika::setPeriod?="+pName+"&svodka="+currentLink,"right","searchGraph");
    dtUpdateEnabled=true;
}

function getSvodka(tmpName,offset){
    classRemove(currentLink,"slActive");
    classAdd(tmpName,"slActive");
    currentLink=tmpName;
    var d=explode(".",getId("date1").value);
    var d1=d[2]+d[1]+d[0];
    d=explode(".",getId("date2").value);
    var d2=d[2]+d[1]+d[0];
    var qw="show?="+tmpName+"&date1="+d1+"&date2="+d2;
    if(offset!=undefined) qw+=offset;
    ajaxGet("metrika::"+qw,"right","searchGraph");
}

function dataUpdate(){
    var d=explode(".",getId("date1").value);
    var d1=d[2]+d[1]+d[0];
    d=explode(".",getId("date2").value);
    var d2=d[2]+d[1]+d[0];
    if(dtUpdateEnabled===true) ajaxGet("metrika::init?=&date1="+d1+"&date2="+d2,"cblock","searchGraph");
}

function searchGraph(){
    // Ищем все элементы с классом .axiomChart
    var fxlist=getByClass("axiomChart");
    for (key in fxlist)  {
        // Формируем чарты
        var itemLabels=explode("|",getId(fxlist[key].id).getAttribute("data-labels"));
        var itemDatas=explode("|",getId(fxlist[key].id).getAttribute("data-values"));
        var ctx = document.getElementById(fxlist[key].id);
        var diagramType = getId(fxlist[key].id).getAttribute("data-chtype");
        var mainColor=getId(fxlist[key].id).getAttribute("data-color");
        var multicolor=["#97cc64", "#ffd963", "#fd5a3e", "#77b6e7", "#a955b8", "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#ecf0f1", "#95a5a6", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f39c12", "#d35400", "#bdc3c7", "#7f8c8d","#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#ecf0f1", "#95a5a6", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f39c12", "#d35400", "#bdc3c7", "#7f8c8d","#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#ecf0f1", "#95a5a6", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f39c12", "#d35400", "#bdc3c7", "#7f8c8d","#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#f1c40f", "#e67e22", "#ecf0f1", "#95a5a6", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f39c12", "#d35400", "#bdc3c7", "#7f8c8d"];

        if(diagramType=="line"){
            var myChart = new Chart(ctx, {
                type: \'line\',
                data: {
                    labels: itemLabels,
                    datasets: [
                        {
                        data: itemDatas,
                        backgroundColor: "#3498db",
                        tension: 0,
                        pointBorderColor: "#3498db",
                        pointBackgroundColor: "#71b4e2",
                        pointBorderWidth: 2,
                        borderColor:"#3498db",
                        fill: false,
                        }
                    ]
                },
                options: {
                    animation: false,
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: true, // Показывать ли легенду
                }
            });
        }

        if(diagramType=="lineSmall"){
            var myChart = new Chart(ctx, {
                type: \'line\',
                data: {
                    labels: itemLabels,
                    datasets: [
                        {
                        data: itemDatas,
                        backgroundColor: mainColor,
                        borderColor:mainColor,
                        fill: false,
                        tension: 0
                        }
                    ]
                },
                options: {
                    animation: false,
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: true, // Показывать ли легенду
                    scales: {
                        xAxes: [{ display:false }],
                        yAxes: [{
                            gridLines: {
                                display:true,
                                drawBorder: true,
                                drawOnChartArea: true, /* Отображать оси */
                                drawTicks: true,
                                offsetGridLines: true
                            }
                        }]
                    }
                }
            });
        }

        if(diagramType=="lineSmallBlue"){
            var myChart = new Chart(ctx, {
                type: \'line\',
                data: {
                    labels: itemLabels,
                    datasets: [
                        {
                        data: itemDatas,
                        backgroundColor: mainColor,
                        borderColor:mainColor,
                        fill: true,
                        tension: 0.2,
                        borderWidth: 1,
                        backgroundColor: "#addbec",
                        pointBorderColor: "transparent",
                        pointBackgroundColor: "transparent"
                        }
                    ]
                },
                options: {
                    animation: false,
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: true, // Показывать ли легенду
                    scales: { xAxes: [{ display:false }], yAxes: [{ display:false }] }
                }
            });
        }

        if(diagramType=="chartSmallBlue"){
            var myChart = new Chart(ctx, {
                type: \'bar\',
                data: {
                    labels: itemLabels,
                    datasets: [ { data: itemDatas, backgroundColor: mainColor } ]
                },
                options: {
                    animation: false,
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: false, // Показывать ли легенду
                    scales: { xAxes: [{ display:false }], yAxes: [{ display:true }] }
                }
            });
        }

        if(diagramType=="bigBlueChart" || diagramType=="bigColoredChart"){
            var dColor=mainColor;
            if(diagramType=="bigColoredChart") dColor=multicolor;
            var myChart = new Chart(ctx, {
                type: \'bar\',
                data: {
                    labels: itemLabels,
                    datasets: [ { data: itemDatas, backgroundColor: dColor } ]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: false,
                    beginAtZero: true,
                    scales: {
                        xAxes: [{ display:true }],
                        yAxes: [{ ticks: { beginAtZero: true }},{ display:false }]
                    }
                }
            });
        }

        if(diagramType=="pie"){
            var myChart = new Chart(ctx, {
                type: \'pie\',
                data: {
                    labels: itemLabels,
                    datasets: [ { data: itemDatas, backgroundColor: multicolor } ]
                },
                options: {
                    animation: false,
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        labels: { fontColor: "#000000", fontSize: 11, boxWidth: 20 },
                        position: "bottom",
                    }
                }
            });
        }

        if(diagramType=="pieBig"){
            var myChart = new Chart(ctx, {
                type: \'pie\',
                data: {
                    labels: itemLabels,
                    datasets: [ { data: itemDatas, backgroundColor: multicolor } ]
                },
                options: {
                    animation: false,/* { animateScale:true } */
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        labels: { fontColor: "#000000", fontSize: 12, boxWidth: 30 },
                        position: "bottom",
                    }
                }
            });
        }
    }
}
window.onload=function(){
	searchGraph();
	getId("ajaxWindow").style.visibility = "hidden";
}
');
            $admin->addOnload("searchGraph()");
		}


		$menu='<ul class="lMenu">
		    <li class="passive"><i class="ic-chart-alt"></i><b>Общие</b></li>
		    <li id="default" class="sLink slActive"><a style="text-decoration:none;" href="'.$settings['protocol'].$settings['siteUrl'].'/admin/metrika/">Сводка</a></li>
		    <li id="traffic" class="sLink" onClick="getSvodka(this.id)">Посещаемость</li>

		    <li class="passive"><i class="ic-earth"></i><b>Источники</b></li>
		    <li id="sources_summary" class="sLink" onClick="getSvodka(this.id)">Источники, сводка</li>
		    <li id="sources_sites" class="sLink"  onClick="getSvodka(this.id)">Сайты</li>
		    <li id="search_engines" class="sLink" onClick="getSvodka(this.id)">Поисковые системы</li>
		    <li id="sources_search_phrases" class="sLink" onClick="getSvodka(this.id)">Поисковые фразы</li>
		    <li id="sources_social" class="sLink" onClick="getSvodka(this.id)">Социальные сети</li>

            <li class="passive"><i class="ic-users"></i><b>Посетители</b></li>
		    <li id="geo_country" class="sLink" onClick="getSvodka(this.id)">География</li>
		    <!-- li onClick="getSvodka(\'conversion\')">Конверсии</li -->
		    <li id="interests" class="sLink" onClick="getSvodka(this.id)">Интересы</li>
		    <li id="age" class="sLink" onClick="getSvodka(this.id)">Возраст</li>
		    <li id="gender" class="sLink" onClick="getSvodka(this.id)">Пол</li>
		    <li id="age_gender" class="sLink" onClick="getSvodka(this.id)">Пол и возраст</li>
		    <li id="deepness_depth" class="sLink" onClick="getSvodka(this.id)">Глубина просмотра</li>
		    <li id="deepness_time" class="sLink" onClick="getSvodka(this.id)">Время на сайте</li>
		    <li id="hourly" class="sLink" onClick="getSvodka(this.id)">Посещаемость по времени суток</li>
            <li id="loyalty_visits" class="sLink" onClick="getSvodka(this.id)">Общее число визитов</li>
            <li id="loyalty_period" class="sLink" onClick="getSvodka(this.id)">Периодичность визитов</li>

            <li class="passive"><i class="ic-file-text2"></i><b>Содержание</b></li>
            <li id="popular" class="sLink" onClick="getSvodka(this.id)">Популярное</li>
            <li id="content_entrance" class="sLink" onClick="getSvodka(this.id)">Страница входа</li>
            <li id="content_exit" class="sLink" onClick="getSvodka(this.id)">Страница выхода</li>
            <li id="titles" class="sLink" onClick="getSvodka(this.id)">Заголовки страниц</li>

            <li class="passive"><i class="ic-html"></i><b>Технологии</b></li>
		    <li id="tech_browsers" class="sLink" onClick="getSvodka(this.id)">Браузеры</li>
		    <li id="tech_cookies" class="sLink" onClick="getSvodka(this.id)">Наличие Cookies</li>
		    <li id="tech_display" class="sLink" onClick="getSvodka(this.id)">Разрешение дисплея</li>
		    <li id="tech_display_groups" class="sLink" onClick="getSvodka(this.id)">Группы дисплеев</li>
		    <li id="tech_platforms" class="sLink" onClick="getSvodka(this.id)">Операционные системы</li>
		    <li id="tech_devices" class="sLink" onClick="getSvodka(this.id)">Устройства</li>
		    <li id="tech_devicebrand" class="sLink" onClick="getSvodka(this.id)">Модели устройств</li>
		</ul>';

        // Сводный отчет
		$out=self::allStat($date1,$date2);

		list($Cday,$Cmonth,$Cyear,$CCday,$CCmonth,$daysInMonth)=explode(".",date("d.m.Y.j.n.t",time()));

		$perToday=$Cday.'.'.$Cmonth.'.'.$Cyear.'-'.$Cday.'.'.$Cmonth.'.'.$Cyear;
		$perYesterday=date("d.m.Y",(time()-86400)).'-'.date("d.m.Y",(time()-86400));

        //$cMonday=strtotime('monday this week', time());
		$perWeek=date("d.m.Y",(time()-86400*7)).'-'.date("d.m.Y",time());
		$perMonth=date("d.m.Y",(time()-86400*$daysInMonth)).'-'.date("d.m.Y",time());
		$perQuartal=date("d.m.Y",(time()-86400*90)).'-'.date("d.m.Y",time());
		$perYear=date("d.m.Y",(time()-86400*365)).'-'.date("d.m.Y",time());

		$out='<div class="row">
		    
		        <ul class="breadCrumbs"><li><a href="./metrika/">Яндекс Метрика</a></li><li><span>Сводный отчет</span></li></ul>
		    
		    </div>
		    <div class="row">
		    <div style="width:180px; height:100%; min-height:800px; background:#555555; color:#ffffff; float:left; margin-right:20px;">'.$menu.'</div>
		    <div style="float:left; width:1000px;">
                <div class="field">
                    <div class="btn-group">
                        <div id="bt-today" class="btn" onClick="setPeriod(\'today\',\''.$perToday.'\')">Сегодня</div>
                        <div id="bt-yesterday" class="btn" onClick="setPeriod(\'yesterday\',\''.$perYesterday.'\')">Вчера</div>
                        <div id="bt-week" class="btn" onClick="setPeriod(\'week\',\''.$perWeek.'\')">Неделя</div>
                        <div id="bt-month" class="btn" onClick="setPeriod(\'month\',\''.$perMonth.'\')">Месяц</div>
                        <div id="bt-quartal" class="btn" onClick="setPeriod(\'quartal\',\''.$perQuartal.'\')">Квартал</div>
                        <div id="bt-year" class="btn" onClick="setPeriod(\'year\',\''.$perYear.'\')">Год</div>
                    </div>
                    <div class="btn-group" style="margin-left:20px;">
                        <input id="date1" type="text" name="article[createtime]" value="'.$d1.'" onclick="createCalendar(this.id)" onkeydown="this.blur()" onChange="dataUpdate()" class="date"><div class="label">-</div>
                        <input id="date2" type="text" name="article[createtime]" value="'.$d2.'" onclick="createCalendar(this.id)" onkeydown="this.blur()" onChange="dataUpdate()" class="date">
                    </div>
                </div>
		        <div id="right" style="width:1000px;">'.$out.'</div>
		    </div>
		</div>';
		return $out;
    }


    static function getPeriod($date1,$date2){
//        $period='day';
//        $razn=(intval(self::dStamp($date2))-intval(self::dStamp($date1)))/(24*60*60);// к-во дней между датами
//        if($razn<=1) $period='hour';
//        if($razn<=3) $period='hours';
//        if($razn>=4) $period='day';
//        if($razn>=80) $period='week';
//        if($razn>=365) $period='week';
//        if($razn>=366) $period='month';
        $period='auto';
        return $period;
    }

    // Сводная статистика
    static function allStat($date1,$date2){

        //echo 'Теперь даты: '.$date1.' - '.$date2.'<br><br>';

        $period=self::getPeriod($date1,$date2);

        //echo 'Cperiod='.$period;

        // Получаем сводку за заданный срок
	    $data=self::getMetrika("data/bytime?top_keys=3&group=".$period."&metrics=ym:s:users,ym:s:newUsers,ym:s:visits,ym:s:pageviews,ym:s:manPercentage,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds,ym:s:under18AgePercentage,ym:s:upTo24AgePercentage,ym:s:upTo34AgePercentage,ym:s:upTo44AgePercentage,ym:s:over44AgePercentage,ym:s:percentNewVisitors,ym:s:robotPercentage,ym:s:mobilePercentage,ym:s:visitsPerHour,ym:s:visitsPerDay", $date1, $date2);


	    // 10 самых популярных страниц сайта
	    $data2=self::getMetrika("data/?dimensions=ym:pv:URL,ym:pv:title&metrics=ym:pv:pageviews,ym:pv:users&limit=20", $date1, $date2);
	    // Поисковые фразы
	    $data3=self::getMetrika("data?preset=sources_search_phrases&limit=20", $date1, $date2);//"data?preset=sources_search_phrases&limit=10"
	    $out='<div style="width:640px; float:left;">'.self::showSvodka($data, $date1, $date2).'</div>
	    <div>'.self::showPglist($data2).self::showKeywords($data3).'</div>';
	    return '<br><div class="field"><h3>Сводка за период с '.substr($date1,6,2).'.'.substr($date1,4,2).'.'.substr($date1,0,4).' по '.substr($date2,6,2).'.'.substr($date2,4,2).'.'.substr($date2,0,4).'</h3></div>'.$out;
    }


    // Запрос к Метрике по шаблону
    static function presetQuery($preset, $date1=false, $date2=false){
        $time=time();
        if($date1===false) $date1=date("Ymd",$time-30*24*60*60);
        if($date2===false) $date2=date("Ymd",$time);
        $period=self::getPeriod($date2,$date2);
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: OAuth '.self::$token]);
        curl_setopt ($ch, CURLOPT_URL, self::$apiUrl.'data?preset='.$preset.'&pretty=1&id='.self::$counterId.'&group='.$period.'&oauth_token='.self::$token.'&date1='.$date1.'&date2='.$date2);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $metrika = curl_exec ($ch);
        curl_close($ch);
        return json_decode($metrika);
    }

    // Запрос данных у Метрики
    static function getMetrika($uri, $date1=false, $date2=false){
        $time=time();
        if($date1===false) $date1=date("Ymd",$time-30*24*60*60);
        if($date2===false) $date2=date("Ymd",$time);
        $period=self::getPeriod($date2,$date2);
        $ch = curl_init();
		// Пытаемся авторизоваться
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: OAuth '.self::$token]);
        curl_setopt ($ch, CURLOPT_URL, self::$apiUrl.$uri.'&group='.$period.'&ids='.self::$counterId.'&date1='.$date1.'&date2='.$date2.'&oauth_token='.self::$token);
        curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $metrika = curl_exec ($ch);
        curl_close($ch);
        return json_decode($metrika);
    }

}

