<?php
class ui{

	public $lang='ru';
	public $title='OmCMS';
	public $content='';
	public $js=array();
	public $css=array();

	////////////////////////////////////////////////
	// Диалог выбора цвета
	////////////////////////////////////////////////
	// Отображение Colorpicker
    function colorpicker($color="FFFFFF", $name='color') {
        $color=trim(str_replace("#","",$color));
        static $cpid;
        if(!isset($cpid)) $cpid=0;
        $cpid++;
        return '<INPUT type="text" name='.$name.' id="AXcp'.$cpid.'" class="c-picker" readonly="readonly" onclick="createColorpicker(this.id);" value="#'.$color.'" /><div id="AXcp'.$cpid.'Prev" class="c-pickerPrev" style="background:#'.$color.'" onclick="createColorpicker(\'AXcp'.$cpid.'\');"></div>';
    }

    ////////////////////////////////////////////////
    // Отображение виджета
    ////////////////////////////////////////////////
    static function widget($url=false, $onClick=false, $icon='ic-notification', $name="Виджет", $description="Описание виджета"){
        $out='';
        if($onClick===false) $out.='<a class="axiomWidget" href="'.$url.'">';
        else $out.='<div class="axiomWidget" onClick="'.$onClick.'">';
        $out.='<i class="'.$icon.'"> </i><dl><dd>'.htmlspecialchars($name).'</dd><dt>'.htmlspecialchars($description).'</dt></dl>';
        if($onClick===false) $out.='</a>';
        else $out.='</div>';
        return $out;
    }



	// Генерирует вкладки
	// На входе массив вида
	// [0]
	//   ['name']='Название вкладки';
	//   ['active']=true;
	//   ['content']='Содержание вкладки';
	//   ['onclick']='';
	// -------------------------------------
	// Требует наличия jquery и скрипта tabs
	static function tabs($array,$tabId=false){
	    static $tabCounter;
	    if(!isset($tabCounter)) $tabCounter=0;
	    $tabCounter++;
	    if($tabId===false) $tabId='AXIOMtab'.$tabCounter;
		$tbs='';
		$cnt='';
		if(!isset($val['content'])) $val['content']='';
		foreach ($array AS $key=>$val){
			$class='';
			$class2='';
			if($val['active']==1) {
				$class=' class="current"';
				$class2=' visible';
			}
			$data='';
			if(isset($val['data'])) $data=','.$val['data'];
			if(isset($val['onClick'])) $onClick=' onClick="function AXstTab(){AXsetTab(\''.$tabId.'\','.$key.'); '.$val['onClick'].';};AXstTab();"';
			else $onClick=' onClick="AXsetTab(\''.$tabId.'\','.$key.$data.')"';

			$tbs.='<li'.$class.$onClick.' id="tt'.$tabId.$key.'" onselectstart="return false">'.$val['name'].'</li>';
			$cnt.='<div id="tab'.$tabId.$key.'" class="tabbox'.$class2.'">'.$val['content'].'</div>';
			}
		return '<div class="tabsection"><ul class="tabs" id="'.$tabId.'">'.$tbs.'</ul>'.$cnt.'</div>';
	}
	
	// Возвращает массив доступных иконок UI
	static function getIcons(){
		return explode(",","enter,exit,save,library,home,office,building,shop,trash,trash2,keyboard,mouse,printer,lock,unlock,lock2,edit,editdoc,eyedrop,share,tools,magic,cogs,cog,equalizer,power,calc,chip,abacus,calckey,dice,key,hammer,cap,balance,attach,magnet,puzzle,scissors,calendar,calendar3,calendar4,calendar2,hglass,clock,clock2,eclock,alarmclock,history,timer,binoculars,glasses,search,zoom-in,zoom-out,bug,target2,target3,target,pin,pin2,loc,loc-add,map,compass,earth,sphere,wheelchair,aid,aid2,lifeb,shield,bomb,security,open,sale,cart,cart-add,basket,shipping,box,box-open,boxes,package,road,milestone,truck,phone,phoneup,phone-old,hphone,fax,pig,safe,money,rub,usd,euro,pound,yen,gift,diary,card,card-visa,card-master,card-discover,card-amex,card-paypal,barcode,qrcode,plus,plus2,plus3,ellipsis-h,ellipsis-v,minus,erase,x,cross,delete,cancel,eye,eye-plus,eye-minus,eye-blocked,trophy,snow,star-full,star-half,star-empty,shit,pan_tool,tup,tdown,db,server,stack,drive,download,upload,box-down,box-up,drawer,drawer2,download3,upload3,check,check2,x2,check3,check4,minus2,contrast,stop3,play2,pause,backward,forward2,stop,warning,alert,exclam,stop4,stop5,question,denied,info,info2,cursor,tree,folder,folder-open,folder-plus,folder-minus,folder-download,folder-upload,folder-sub,folder-dir,dirlock,file-empty,copy,paste,clipboard,diff,files-empty,file-text2,file-text,profile,file,file-text3,file2,file-picture,file-music,file-play,file-video,file-zip,file-pdf,file-openoffice,file-word,file-excel,file-loffice,file-text4,file-bin,file-code,file-symlink,file-media,doc-stroke,doc-fill,new-window,versions,newspaper,terminal,image,images,pictures,film,frame,film2,camera,aperture,movie,html,fcamera,play,bookalt,book2,mirror,browser,window,terminal2,david,christ,muslim,male,female,sheriff,agent,usergroup,user2,user,user-plus,user-minus,user-check,users,child,child2,vcard,badge,person-mail,person-phone,contacts,person-calendar,person-pin,person-other,hotel,person-box,person-circle,adrbook,adrbook2,bubble2,bubbles3,bubbles4,chat,comments,book,books,tags,tag,bookmark,bookmarks,flag,cone,ticket,beaker,dashboard,movealt,fs-out,fs-in,move,loop2,next,return,zap,undo,reload,loop3,loop4,shuffle,sharell,narrow,move-v,move-h,infinite,refresh,enlarge,shrink,fork,align-a,align-b,align,moveup,movedown,moveleft,moveright,tab,extlink,dright,dplus,dslash,dstar,dminus,crop,ungroup,screen-full,screen-normal,codepen,pgbreak,pgbreak2,insert-template,table2,table,border-all,border-bottom,border-clear,border-horizontal,border-inner,border-left,border-outer,border-right,border-style,border-top,border-v,flip,flip-back,flip-front,format-shapes,gridoff,gridon,gradient,ruler,ruler2,down,up,left,right,up1,down1,left1,right1,up2,down2,right2,left2,pup,pright,pdown,pleft,up3,right3,down3,left3,ch,ch-check,rad,rad-checked,qmark,id,lquote,rquote,image2,chart-alt,bars,graph,pie-chart,stats-dots,stats-bars2,stats,headphones,mic,hear,ring,alarm,alarm-add,alarm-off,alarm-active,music,bullhorn,volume-down,volume-up,volume-off,volume-mute,loundry,droplet,star,pointer,at,mail-open,mail-close,mail2,mail-read,mail-close2,mail3,mail4,mail-drafts,grid,laptop,tablet,desktop,mobile,tv,socket,socket2,socket3,bat1,bat2,bat3,bat4,steps,heart,heart-broken,spades,clubs,diamonds,connect,spinner,access,numlist,list,menu1,menu,menu2,menu3,menu4,happy,smile,tongue,sad,wink,grin,cool,angry,evil,shocked,baffled,confused,neutral,hipster,wondering,sleepy,frustrated,crying,play3,pause2,stop2,bw,fw,first,last,prev2,next2,eject,move-up,move-down,sort-aasc,sort-adesc,sort-nasc,sort-ndesc,sort-amasc,sort-amdesc,command,shift,ctrl,opt,filter,text-height,text-width,font-size,bold,underline,italic,strikethrough,omega,sigma,spell,super,sub,text-color,format-clear,pilcrow,section,align-left,align-center,align-right,align-justify,indent-inc,indent-dec,newtab,embed,embed2,skype,fill,sim,svideo,usb,google,google-plus,google-drive,facebook,instagram,whatsapp,telegram,twitter,vk,rss,rss2,youtube,youtube2,linux,apple,android,windows,wiki,linkedin,chrome,firefox,ie,edge,safari,opera,squirrel,glass,spoon-knife,telescope,locator,satellite,mug,beer,coffee,lab,pulse,microscope,atom,pcord,plug,bulb,leaf,meter,fire,briefcase2,painter,pacman,rocket,umbrella,avia,avia-takeoff,avia-land,avia-on,avia-off,e-station,game,fingerprint,briefcase,hdmi,gas-station,extinguisher,shower,anchor,transit,rail,bus,moto,traffic,swheel,boat,shuttle,car,carpolice,train,tram,hookup,subway,bike,oven,tele,radio2,refreg,lamp,wash,goat,skelet,sun,weather,rainy,lightning,cloud,ncloud,cloudy,thermo,none,celsius,fahrengate,export");
    }
}