<?php
class CUploader{
	private $config = array(
		'maxSize'=>10485760,//单位：byte；乘1024之后单位是kb；再乘1024之后单位是mb；(1024 * 1024 * 10) = 10485760 byte = 10M
		'filenameBadChar'=>array('\\','/',':','*','?','"','<','>','|'),
		//'fileMime'=>array('application/vnd.ms-excel','application/x-shockwave-flash','image/jpeg','image/pjpeg','image/jpg','image/gif','image/png','image/x-png','application/msword','application/pdf','application/zip','application/x-zip-compressed'),
		'fileExt'=>array('.swf','.zip','.jpg','.jpeg','.png','.gif','.webp','.doc','.docx','.pdf','.xls','.xlsx','.xml','.pem','.p12'),
		'uploadFolder'=>'../../../upload/',
		'fileAutoRename'=>true,
		'fileCover'=>false,
		'createDateFolder'=>true,
		'uploadIsPic'=>true,
	);

	public function __construct($config){
		$this->FInitConfig($config);
	}
	
	public function __destruct(){
		
	}
	
	private function FInitConfig($config){
		//判断该值是否存在、是否是数组、是否含有记录
		if(isset($config) && is_array($config) && count($config)>0){
			foreach($config as $key=>$val){
				$this->config[$key] = $val;
			}
		}
	}
	
	public function FPicAutoThumb($picpath, $savepath,$maxWidth, $maxHeight){
		$imgInfo = getimagesize($picpath);
		$oW = $imgInfo[0];
		$oH = $imgInfo[1];
		
		//宽和高必须有一个指定值
		if($maxWidth <= 0 && $maxHeight <= 0){
			return '';
		}
		
		if($maxWidth <= 0){
			//如果宽未指定值、高指定了值
			if($oH <= $maxHeight){
				//源图的高没有指定的高值大，那么不缩放，只复制、重命名（不放大，因为在网页中可以直接用html代码拉伸图片，没必要放大）
				$newWidth = $oW;
				$newHeight = $oH;
			}else{
				//源图的高比指定的值大，等比缩小
				$newHeight = $maxHeight;
				$newWidth = round(($oW * $maxHeight) / $oH);
			}
		}else if($maxHeight <= 0){
			//如果高未指定值、宽指定了值
			if($oW <= $maxWidth){
				$newWidth = $oW;
				$newHeight = $oH;
			}else{
				$newWidth = $oW;
				$newHeight = round(($oH * $maxWidth) / $oW);
			}
		}else{
			//宽和高都指定了值
			if ($oW / $oH >= $maxWidth / $maxHeight) {
				//宽度变窄
				if ($oW > $maxWidth) {
					$newWidth = $maxWidth;
					$newHeight = round(($oH * $maxWidth) / $oW);
				} else {
					$newWidth = $oW;
					$newHeight = $oH;
				}
			} else {
				if ($oH > $maxHeight) {
					$newHeight = $maxHeight;
					$newWidth = round(($oW * $maxHeight) / $oH);
				} else {
					$newWidth = $oW;
					$newHeight = $oH;
				}
			}
		}
	
		if($newWidth == $oW && $newHeight == $oH){
			//copy($picpath,$savepath)
			//这里不复制，直接返回原图路径，因为没处理，节省空间
			$savepath = str_replace($_SERVER["DOCUMENT_ROOT"],'',$picpath);
		}else{
			//创建画布
			$temp_canvas = imagecreatetruecolor($newWidth,$newHeight);
			
			switch($imgInfo[2]){
				case 1:
					$temp_create = imagecreatefromgif($picpath);
					imagecopyresampled($temp_canvas,$temp_create,0,0,0,0,$newWidth,$newHeight,$oW,$oH);
					imagegif($temp_canvas,$savepath);
					break;
				case 2:
					$temp_create = imagecreatefromjpeg($picpath);
					imagecopyresampled($temp_canvas,$temp_create,0,0,0,0,$newWidth,$newHeight,$oW,$oH);
					imagejpeg($temp_canvas,$savepath, 80);
					break;
				case 3:
					$temp_create = imagecreatefrompng($picpath);
					imagecopyresampled($temp_canvas,$temp_create,0,0,0,0,$newWidth,$newHeight,$oW,$oH);
					imagepng($temp_canvas,$savepath);
					break;
			}
			imagedestroy($temp_create);
			 
			$savepath = str_replace($_SERVER["DOCUMENT_ROOT"],'',$savepath);
		}
		
		return $savepath;
	}
	
	private function FChkAndDelBadFile($saveFolderTemp){
		if(substr($saveFolderTemp,-1) != '/'){
			$saveFolderTemp .= '/';
		}
		
		$mydir = dir($saveFolderTemp); 
		while($file = $mydir->read()){ 
			if(is_dir($saveFolderTemp . $file) && ($file != '.') && ($file != '..')) {
				//目录
			}else{
				//文件
				if(stripos($file,'.php') !== false || stripos($file,'.asp') !== false || stripos($file,'.jsp') !== false || stripos($file,'.exe') !== false || stripos($file,'.cmd') !== false){
					@unlink($saveFolderTemp . $file);
				}
			}
		} 
		$mydir->close();
	}
	
	private function FChkIsImg($picpath){
		$imgInfo = getimagesize($picpath);
		$isPic = 0;
		switch($imgInfo[2]){
			case 1:
				//gif
				$isPic = 1;
				break;
			case 2:
				//jpg
				$isPic = 1;
				break;
			case 3:
				//png
				$isPic = 1;
				break;
		}
		
		return $isPic;
	}
	
	private function FWebp2jpg($picpath){
		$im = imagecreatefromwebp($picpath);
		$savepath = $picpath . '.webp2jpg.jpg';
		$savefile = imagejpeg($im, $savepath, 60);
		imagedestroy($im);
		
		if($savefile){
			return $savepath;
		}else{
			return false;
		}
	}

	public function FUploadOne($uploadInputName){
		$config = $this->config;
		
		$saveFileName = '';
		$saveFileError = '';
		
		$uploadFile = $_FILES[$uploadInputName];
		if($uploadFile){
			if ($uploadFile['error']){
				switch($uploadFile['error']){
					case 1:
						$saveFileError = '上传的文件大小超出设置（php.ini upload_max_filesize）';
						break;
					case 2:
						$saveFileError = '上传的文件大小超出设置（form MAX_FILE_SIZE）';
						break;
					case 3:
						$saveFileError = '文件只有部分被上传';
						break;
					case 4:
						$saveFileError = '没有选择文件';
						break;
					case 6:
						$saveFileError = '找不到临时文件夹';
						break;
					case 7:
						$saveFileError = '文件写入失败';
						break;
					default:
						$saveFileError = '发生了未知错误(' . $uploadFile['error'] . ')';
						break;
				}
			}else{
				$filename = $uploadFile['name'];
				$fileExt = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));//substr($filename,strrpos($filename,"."));
				$fileTempName = $uploadFile['tmp_name'];
				
				if($filename == ''){
					$saveFileError = '抱歉，文件名不可为空！';
				}else if($fileExt == ''){
					$saveFileError = '抱歉，文件必须有扩展名！';
				}else if($uploadFile['size'] > $config['maxSize']){
					$saveFileError = '抱歉，文件太大不能上传！';
				//}else if(!in_array($uploadFile['type'],$config['fileMime']) && !in_array($fileExt,$config['fileExt'])){
				//	$saveFileError = '抱歉，上传的文件类型不符合规定，不允许上传！';
				}else if(!in_array($fileExt,$config['fileExt'])){
					$saveFileError = '抱歉，上传的文件类型不符合规定，不允许上传(ERR01-' . $fileExt . ')！';
				}else if(stripos($filename,'.php') !== false || stripos($filename,'.asp') !== false || stripos($filename,'.jsp') !== false || stripos($filename,'.exe') !== false || stripos($filename,'.cmd') !== false){
					$saveFileError = '抱歉，上传的文件类型不符合规定，不允许上传(ERR02)！';
				}else{
					//文件名如果自动重命名
					if($config['fileAutoRename']){
						$filename = date("YmdHis") . rand(1000,9999) . $fileExt;
					}
					
					//上传文件夹路径
					$saveFileName = $config['uploadFolder'];
					//如果需要创建日期文件夹
					if($config['createDateFolder']){
						$saveFileName .= date("Y");
						$saveFileName .= '/';
						if(!is_dir($saveFileName)){
							mkdir($saveFileName);
						}
						
						$saveFileName .= date("m");
						$saveFileName .= '/';
						if(!is_dir($saveFileName)){
							mkdir($saveFileName);
						}
						
						$saveFileName .= date("d");
						$saveFileName .= '/';
						if(!is_dir($saveFileName)){
							mkdir($saveFileName);
						}
					}
					//加上文件名
					$saveFolderTemp = $saveFileName;
					$saveFileName .= $filename;
					
					//如果不覆盖，目标文件又存在
					if (!$config['fileCover'] && file_exists($saveFileName)){
						$saveFileError = '抱歉，同名文件已经存在，停止上传！';
					}else{
						//移动
						$fileAct = move_uploaded_file($fileTempName,$saveFileName);
						
						//如果移动失败，那么复制
						if(!$fileAct){
							$fileAct = copy($fileTempName,$saveFileName);
						}
						
						//判断是否操作成功
						if(!$fileAct){
							$saveFileName = '';
							$saveFileError = '抱歉，上传失败，请重新上传！';
						}
					}//if覆盖
					
					//检测是否是图片，手机图片会有问题，因为可能是webp格式，这里加个转换
					if($this->config['uploadIsPic']){
						if(strlen($saveFileName) > 0 && file_exists($saveFileName)){
							$isImg = $this->FChkIsImg($saveFileName);
							if(!$isImg){
								$saveFileName = $this->FWebp2jpg($saveFileName);
								if($saveFileName == false){
									$saveFileError = '抱歉！上传的文件类型不符合规定，不允许上传(ERR03)！';
									$saveFileName = '';
									
									@unlink($saveFileName);
								}
							}
						}
					}
					
					//遍历php、asp、aspx等文件，删除
					$this->FChkAndDelBadFile($saveFolderTemp);
					
				}//if 12345...
			}//if error
		}else{
			$saveFileError = '抱歉，未找到上传的文件！';
		}
		
		//读取已经上传的文件，检查是否有注入关键字

		if($saveFileError){
			return array('state'=>'fail','msg'=>$saveFileError);
		}else{
			//把../全部过滤掉，返回绝对路径
			while(substr($saveFileName,0,3) == '../'){
				$saveFileName = str_replace('../','',$saveFileName);
			}
			$saveFileName = '/' . $saveFileName;
			
			return array('state'=>'success','file'=>$saveFileName);
		}
	}
}