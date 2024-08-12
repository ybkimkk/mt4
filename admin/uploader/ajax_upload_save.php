<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('../conn.php');

if(FGetStr('from') != 'reg'){
	require_once('../chk_logged.php');
}

//-------------------------------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/uploader.class.php');

header('Content-type:text/json');

FP_Main();

function FP_Main(){
	global $DB;
	
	$uploadKey = 'myfile';
	
	$uploadFolder = "../../upload/";
	if(!is_dir($uploadFolder)){
		mkdir($uploadFolder,0777);
	}
	
	$uploadConfig = array(
		'maxSize'=>(1024 * 1024 * 10),//单位：byte；乘1024之后单位是kb；再乘1024之后单位是mb；10M
		'uploadFolder'=>$uploadFolder,
		'fileExt'=>array('.jpg','.jpeg','.png','.gif'),
	);
	$cnUploader = new CUploader($uploadConfig);
	$uploadStateArr = $cnUploader->FUploadOne($uploadKey);
		
	if ($uploadStateArr['state'] == 'success'){
		$uploadStateArr['big'] = $uploadStateArr['file'];
		
		//裁剪或缩略
		$autoCut = 1;
		if($autoCut == 1){
			$width = 800;
			$height = 1200;
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
		
		$savename_ = substr($uploadStateArr['file'],strrpos($uploadStateArr['file'],'/') + 1);
		$savepath_ = str_replace($savename_,'',$uploadStateArr['file']);
		
		$sqlFVArr = array(
			'name'=>$_FILES[$uploadKey]['name'],
			'type'=>$_FILES[$uploadKey]['type'],
			'size'=>$_FILES[$uploadKey]['size'],
			'extension'=>FGetFileExt($_FILES[$uploadKey]['name'],false),
			'savepath'=>$savepath_,
			'savename'=>$savename_,
			'key'=>$uploadKey,
			'module'=>'',
			'record_id'=>0,
			'user_id'=>0,
			'download_count'=>0,
			'hash'=>'',
			'sort'=>0,
			'version'=>'',
			'remark'=>'',
			'create_time'=>time(),
			'update_time'=>time(),
			'status'=>1,
			'tag'=>'',
			'comment'=>0,
		);
		$affectId = $DB->insert('t_attach',$sqlFVArr);
		
		$resultArr = array(
						'status'=>1,
						'info'=>L('上传文件成功'),
						'data'=>array(
									array("name"=>$sqlFVArr['name'],
									"type"=>$sqlFVArr['type'],
									"size"=>$sqlFVArr['size'],
									"key"=>$sqlFVArr['key'],
									"extension"=>$sqlFVArr['extension'],
									"savepath"=>$sqlFVArr['savepath'],
									"savename"=>$sqlFVArr['savename'],
									"id"=>$affectId,),
									),
						);
		
		echo json_encode($resultArr);
	}else{
		$resultArr = array(
						'status'=>-1,
						'info'=>$uploadStateArr['msg'],
						'data'=>array(),
						);
		
		echo json_encode($resultArr);
	}
}


