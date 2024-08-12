<?php
$PAYLANG = array('cn','en','vn','id');

$PAYLANG['cn'] = array();
$PAYLANG['en'] = array();
$PAYLANG['vn'] = array();
$PAYLANG['id'] = array();

function GLang($title){
	global $PAYLANG;
	
	if(CC_LANG == 'cn' && substr($title,0,1) != '_'){
		return $title;
	}
	
	return $PAYLANG[CC_LANG][$title];
}

//--------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usdt_base/lang/cn.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usdt_base/lang/en.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usdt_base/lang/vn.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usdt_base/lang/id.php');

//--------------------------------------------------

function member_upload_save_base_img($basedata,$filename){
	global $DB;
	if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $basedata, $result)){
		$savefilePath = '/upload/pay/usdt_base/' . date('Y/m/d') . '/';
		@mkdir($_SERVER['DOCUMENT_ROOT'] . $savefilePath,0777,true);
		
		if(!in_array(strtolower($result[2]),array('jpg','jpeg','gif','png'))){
			return array('status'=>0,'msg'=>'格式错误');
		}
		$filename .= '.' . $result[2];
		
		$savefile = $savefilePath . $filename;
		
		if(!file_put_contents($_SERVER['DOCUMENT_ROOT'] . $savefile, base64_decode(str_replace($result[1], '', $basedata)))){
			return array('status'=>0,'msg'=>'保存失败');
		}
		
		$sqlFVArr = array(
			'name'=>'unknow',
			'type'=>'image/'.strtolower($result[2]),
			'size'=>0,
			'extension'=>strtolower($result[2]),
			'savepath'=>$savefilePath,
			'savename'=>$filename,
			'key'=>'usdt_base',
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
		
		return array('status'=>1,'src'=>$savefile,'id'=>$affectId);
	}else{
		return array('status'=>0,'msg'=>'数据解析出错');
	}
}

