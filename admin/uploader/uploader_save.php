<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('../conn.php');
require_once('../chk_logged.php');

//-------------------------------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/uploader.class.php');

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文件上传</title>
</head>
<body>
<?php
FP_Main();

function FP_Main(){
	$uploadFolder = "../../upload/";
	if(!is_dir($uploadFolder)){
		mkdir($uploadFolder,0777);
	}
	
	$uploadConfig = array(
		'maxSize'=>(1024 * 1024 * 10),//单位：byte；乘1024之后单位是kb；再乘1024之后单位是mb；10M
		'uploadFolder'=>$uploadFolder,
		'fileExt'=>array('.swf','.zip','.jpg','.jpeg','.png','.gif','.webp','.doc','.docx','.pdf','.xls','.xlsx','.xml','.pem','.p12'),
	);
	$cnUploader = new CUploader($uploadConfig);
	$uploadStateArr = $cnUploader->FUploadOne('myUploadFile');
	
	echo '<script type="text/javascript">';
	if ($uploadStateArr['state'] == 'success'){
		//裁剪或缩略
		$autoCut = intval($_GET['autoCut']);
		if($autoCut == 1){
			$width = intval($_GET['width']);
			$height = intval($_GET['height']);
			if($width > 0 || $height > 0){
				//执行
				$big = $uploadStateArr['file'];
				$allowFileExt = array('.jpg','.jpeg','.png','.gif');
				$bigExt = FGetFileExt($big,true);
				if(in_array($bigExt,$allowFileExt)){
					if(substr($big,0,1) == '/'){
						$big = $_SERVER["DOCUMENT_ROOT"] . $big;
					}
					$fileSavePath = $big . '_' . $width . 'x' . $height . $bigExt;
					$thumbPath = $cnUploader->FPicAutoThumb($big, $fileSavePath, $width, $height);
					if(strlen($thumbPath)){
						$uploadStateArr['file'] = $thumbPath;
					}
				}
			}
		}
		
		echo 'alert("文件上传成功！");window.parent.parent.$("#' , $_GET['tgid'] , '").val("' . $uploadStateArr['file'] . '");';
	}else{
		echo 'alert("上传失败，请重试。\r\n参考原因：' . $uploadStateArr['msg'] . '");';
	}
	echo 'window.parent.parent.WJX_CloseAllArt();';
	echo '</script>';
}
?>
</body>
</html>