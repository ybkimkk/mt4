<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();
	
	if (!$_POST['create_time']) {
		$_POST['create_time'] = time();
	} else {
		$_POST['create_time'] = strtotime($_POST['create_time']);
	}
	
	$dataArr = array();
	foreach($LangNameList['list'] as $keyL=>$valL){
		$title = FPostStr('title-' . $keyL);
		$content = $_POST['content-' . $keyL];
		if (strlen($title) <= 0) {
			FJS_P_AC(L('请填写主题') . '-' . $valL['title']);
		}
		if (strlen($content) <= 0) {
			FJS_P_AC(L('请填写内容') . '-' . $valL['title']);
		}
		
		$dataArr[$keyL] = array(
			'title'=>$title,
			'content'=>$content,
		);
	}
	
	$data = array(
		//'title'=>FPostStr('title'),
		'cid'=>FPostInt('cid'),
		'create_time'=>FPostStr('create_time'),
		//'content'=>$_POST['content'],
		'sort'=>FPostInt('sort'),
		'f_key'=>FPostStr('f_key'),
	);
	if (!$data['create_time']) {
		FJS_P_AC("发布日期不能为空");
	}
	if($Id <= 0){
		$data['server_id'] = $DRAdmin['server_id'];
		$data['status'] = 1;
		$id = $DB->insert("t_article",$data);
	}else{
		$DB->update("t_article",$data,"where id = '{$Id}'");
		
		$id = $Id;
	}
	
	
	foreach($dataArr as $key=>$val){
		$data = array(
			'f_title'=>$val['title'],
			'f_val'=>$val['content'],
		);
		
		$chkRs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '{$DRAdmin['server_id']}' and f_type = '-_news_-{$id}' and f_lang = '{$key}'");
		if($chkRs){
			$DB->update('t_lang_otherset',$data,"where id = '{$chkRs['id']}'");
		}else{
			$data['f_serverId'] = $DRAdmin['server_id'];
			$data['f_type'] = '-_news_-' . $id;
			$data['f_lang'] = $key;
			$data['f_addTime'] = FNow();
			
			$DB->insert('t_lang_otherset',$data);
		}
	}
	
	

	FJS_P_AT(L('保存成功'),'?');
}else if($Clause == 'forbid'){
	admin_action_log();
	
	$DB->query("update t_article set status = 0 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'resume'){
	admin_action_log();
	
	$DB->query("update t_article set status = 1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("update t_article set status = -1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}

if(!in_array($Clause,array('main','addinfo','showinfo'))){
	$Clause = 'main';
}
require_once('article/' . $Clause . '.php');

