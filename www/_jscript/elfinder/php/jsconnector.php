<?php
$settings=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/_core/settings.ini');
error_reporting(0); // Set E_ALL for debuging
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';
/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

$opts = array(
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => $_SERVER['DOCUMENT_ROOT'].'/_jscript/',
			'URL'           => 'http://'.$settings['siteUrl'].'/_jscript/',
			'accessControl' => 'access'
		),
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => $_SERVER['DOCUMENT_ROOT'].'/_modules/', 
			'URL'           => 'http://'.$settings['siteUrl'].'/_modules/',
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		),
		array(
			'driver'        => 'LocalFileSystem',
			'path'          => $_SERVER['DOCUMENT_ROOT'].'/uploaded/',
			'URL'           => 'http://'.$settings['siteUrl'].'/_uploaded/',
			'accessControl' => 'access'
		)
        // array(
            // 'driver' => 'FTP',
            // 'host'   => '192.168.1.1',
            // 'user'   => 'eluser',
            // 'pass'   => 'elpass',
            // 'path'   => '/'
        // )
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();