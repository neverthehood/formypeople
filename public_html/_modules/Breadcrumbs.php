<?php
// Хлебные крошки
function Breadcrumbs(){
	global $site, $breadCrumbs, $settings;
	$out='';
	if(!isset($breadCrumbs)) $breadCrumbs='';

	$nameUrl=page::getPages();// comment
	$crumbs='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/" title="Главная"><span>Главная</span></a></li>';
	if($site->page['default']==1) $crumbs='';
	
	$spp=$site->page['parent'];
	foreach($nameUrl as $val){
		if($val['id']==$spp && $val['pagename']!='Все товары'){
			$crumbs.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/'.trim(page::getPath($val['id'])).'"><span>'.$val['pagename'].'</span></a></li>';
			break;
		}
	}
	// max deep id 2
	foreach($nameUrl as $val){
		if($val['id']==$site->page['id']){
			$crumbs.='<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/'.trim($site->path).'"><span>'.$val['pagename'].'</span></a></li>';
			break;
		}
	}
	$crumbs.=$breadCrumbs;

    // Удаляем элемент с урлом вида   domain/catalog/  todo - на некоторых сайтах закомментировать
    $crumbs=str_replace('<li><a href="'.$settings['protocol'].$settings['siteUrl'].'/product/"><span>Каталог</span></a></li>','',$crumbs);

	// add Schema.org Microdata for ListItem Breadcrumbs
	$crumbs=str_replace(trim($settings['siteUrl']).'//',trim($settings['siteUrl']).'/',$crumbs);
	$crumbs=str_replace('<li>','<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">',$crumbs);
	$crumbs=str_replace('<a href=','<a itemprop="item" href=',$crumbs);
	$crumbs=str_replace('<span>','<span itemprop="name">',$crumbs);
	$cl=explode("</li>",$crumbs);
	if(!empty($cl)){
		$crumbs='';
		foreach($cl AS $key=>$val){
			if($val!=''){
				$crumb=$val.'<meta itemprop="position" content="'.($key+1).'" /></li>';
				if(!isset($cl[$key+2])) $crumb=preg_replace('~href="[^"]*"~i', 'name="crumb'.$key.'"', $crumb);
				$crumbs.=$crumb;
			}
		}
	}
	
	return '<ul class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">'.$crumbs.'</ul>';
}
