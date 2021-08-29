<?php
$settings=parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/_core/settings.ini');
$adminPath=$settings['siteUrl'].'/admin';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>elFinder 2.0</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<?php echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$settings['protocol'].$adminPath.'/js/jqueryui/themes/blitzer/jquery-ui.css">



<!--script type="text/javascript" src="'.$settings['protocol'].$settings['siteUrl'].'/_jscript/jquery.min.js"></script -->
<script type="text/javascript" src="'.$settings['protocol'].$adminPath.'/js/jqueryui/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="'.$settings['protocol'].$adminPath.'/plugins/elfinder/css/elfinder.min.css">
<script type="text/javascript" src="'.$settings['protocol'].$adminPath.'/plugins/elfinder/js/elfinder.min.js"></script>
<script type="text/javascript" src="'.$settings['protocol'].$adminPath.'/plugins/elfinder/js/i18n/elfinder.ru.js"></script>';?>	
		
		
		
		<!-- 
		<link rel="stylesheet" type="text/css" media="screen" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css">
		
		
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

		<!-- elFinder CSS (REQUIRED)
		<link rel="stylesheet" type="text/css" media="screen" href="css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="css/theme.css">

		<!-- 
		<script type="text/javascript" src="js/elfinder.min.js"></script>

		<!-- elFinder translation (OPTIONAL)
		<script type="text/javascript" src="js/i18n/elfinder.ru.js"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script type="text/javascript" charset="utf-8">
			$().ready(function() {
				var elf = $('#elfinder').elfinder({
					url : 'php/connector.php',  // connector URL (REQUIRED)
					lang: 'ru',             // language (OPTIONAL)
				}).elfinder('instance');
			});
		</script>
		
		<script type="text/javascript" charset="utf-8">
	// Helper function to get parameters from the query string.
		function getUrlParam(paramName) {
			var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
			var match = window.location.search.match(reParam) ;
			return (match && match.length > 1) ? match[1] : '' ;
		}
			
		$().ready(function() {
			var funcNum = getUrlParam('CKEditorFuncNum');
			var mode = getUrlParam('mode');
			var elf = $('#finder').elfinder({
				url : '<?php echo $settings['protocol'].$settings['siteUrl'];?>/admin/plugins/elfinder/php/connector.php?mode=' + mode,
				getFileCallback : function(file) {
					window.opener.CKEDITOR.tools.callFunction(funcNum, file);
					window.close();
				},
				resizable: false,
				lang: 'ru',
				width	: 'auto',
				height  : 560,
				loadTmbs : 50,
				tmbCrop : true,
				defaultView: 'icons',
				copyOverwrite : true,
				uiOptions : {
					// toolbar configuration
					toolbar : [
						['getfile'],
						['back', 'forward'],
						['reload'],
						['home', 'up'],
						['mkdir', 'mkfile', 'upload'],
						['open', 'download','info'],
						['quicklook'],
						['copy', 'paste'],
						['rm'],
						['duplicate', 'rename', 'edit', 'resize'],
						['extract', 'archive'],
						['search'],
						['view']
					]
				}
					
			}).elfinder('instance');
		});
	</script>
		
		
		
	</head>
	<body style="padding:0px; margin:0px;">

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="finder"></div>

	</body>
</html>
