<?php
/*
2015-10-27 更新


*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Upload Save</title>
<script type="text/javascript" src="../js/uploadSave.js"></script>
</head>
<body>
<?php
$CKEditorFuncNum = (int)$_GET['CKEditorFuncNum'];
$ut = $_GET['ut'];

require_once 'chk_logged.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/uploader.class.php');

$uploadFolder = "../../../../upload/";
if(!is_dir($uploadFolder))
{
	mkdir($uploadFolder);
}
$uploadFolder .= "ckeditor/";
if(!is_dir($uploadFolder))
{
	mkdir($uploadFolder);
}

$uploadConfig = array(
	'maxSize'=>(1024 * 1024 * 10),//单位：byte；乘1024之后单位是kb；再乘1024之后单位是mb；10M
	'uploadFolder'=>$uploadFolder,
);
$upload = new CUploader($uploadConfig);
$uploadStateArr = $upload->FUploadOne('upload');

echo 'Status:Uploaded';
echo '<script type="text/javascript">';
if ($uploadStateArr['state'] == 'success'){
	if($ut == 'file'){
		echo 'UploadedCall(' . $CKEditorFuncNum . ',"' . $uploadStateArr['file'] . '","");';
	}else if ($ut == 'flash' ){
		echo 'UploadedCall(' . $CKEditorFuncNum . ',"' . $uploadStateArr['file'] . '","");';
	}else{
		//image
		echo 'UploadedCall(' . $CKEditorFuncNum . ',"' . $uploadStateArr['file'] . '","");';
	}
}else{
	echo 'UploadedCall(' . $CKEditorFuncNum . ',"","上传失败，请重试。\r\n参考原因：' . $uploadStateArr['msg'] . '");';
}
echo '</script>';