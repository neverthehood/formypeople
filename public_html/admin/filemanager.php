<?php
if(!defined("SECURITY")) define("SECURITY",true);
$CLASS = basename(__FILE__, ".php");
require_once "axiom_req.php";


class filemanager {
	
	static function init(){
		global $admin;
		global $settings;
		$out='';
		return '<div class="row"><ul class="breadCrumbs"><li><span>'.MESSAGE('fileManager').'</span></li></ul></div>
		<div class="row" style="width:100%; height:600px;"><iframe class="fileEditorFrame" src="'.$settings['protocol'].$settings['siteUrl'].'/admin/plugins/elfinder/elfinder.php" width="100%" height="100%">'.MESSAGE('framesNotSupported').'</iframe></div>';
	}

}