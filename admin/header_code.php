<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8" />
        <title><?php echo $webConfig['f_title'];?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="<?php echo $webConfig['f_description'];?>" name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="/favicon.ico">
        <?php
        foreach($LoadCSSArr as $key=>$val){
			echo '<link href="' , $val , '" rel="stylesheet" type="text/css" />';
		}
		?>
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/cz.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/assets/js/cz.js?v=<?php echo time();?>"></script>
<?php
switch($webConfig['f_editor']){
	case 'kindeditor':
		echo '<script type="text/javascript" src="/editor/kindeditor/kindeditor-all-min.js"></script>';
		echo '<script type="text/javascript" src="/editor/kindeditor/lang/zh-CN.js"></script>';
		break;
	case 'ueditor':
		echo '<script type="text/javascript" src="/editor/ueditor/ueditor.config.js"></script>';
		echo '<script type="text/javascript" src="/editor/ueditor/ueditor.all.min.js"></script>';
		break;
	case 'ckeditor':
		echo '<script type="text/javascript" src="/editor/ckeditor/ckeditor.js"></script>';
		break;
}
?>
		<?php
		$czWebSkinName = C('APP_TEMP_SRC');
		if(strlen($czWebSkinName) > 0 && $czWebSkinName != 'default'){
			echo '<link href="/assets/css/skin/' , $czWebSkinName , '.css" rel="stylesheet" type="text/css">';
		}
		?>
		<script>
        var lang_go_download_confirm = '<?php echo L('您可以先搜索出需要的数据，再进行下载');?>';
        var lang_go_download_ok = '<?php echo L('确定下载');?>';
        var lang_go_download_cancel = '<?php echo L('取消');?>';
        var lang_go_download_tips = '<?php echo L('提示');?>';
		var lang_go_download_isSearch = '1';
        </script>
    </head>
    <body>
		<iframe style="display:none;" name="iframe_qpost" id="iframe_qpost"></iframe>
        <!-- Begin page -->
        <div class="wrapper">
