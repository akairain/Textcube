<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo htmlspecialchars($blog['title'])?> &gt; <?php echo _t('글관리')?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path']?>"; var blogURL = "<?php echo $blogURL?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/byTattertools.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/EAF.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/gallery.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/owner.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default-popup-editor.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default-opera.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default-popup-editor-opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default-ie.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path']?>/style/default/default-popup-editor-ie.css" /><![endif]-->
</head>
<body<?php echo (empty($htmlBodyEvents) ? '' : $htmlBodyEvents)?>>
	<div id="temp-wrap">
		<div id="all-wrap">
			<div id="layout-header">
				<h1><span>태터툴즈 관리 페이지</span></h1>
			</div>
			
			<hr class="hidden" />
