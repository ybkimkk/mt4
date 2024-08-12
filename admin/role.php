<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveaccess'){
	admin_action_log();
	
	$arr = array();
	$more = array();
	foreach($_POST as $key=>$val){
		$left4 = substr($key,0,4);
		$left5 = substr($key,0,5);
		if($left5 == 'menu_' || $left5 == 'item_' || $left4 == 'sub_'){
			$arr[] = $val;
		}
		
		$left9 = substr($key,0,9);
		if($left9 == 'moreitem_'){
			$mir = substr($key,9);
			foreach($_POST as $key1=>$val1){
				if(substr($key1,0,strlen('moresub_' . $mir . '_')) == 'moresub_' . $mir . '_'){
					$more[$val][] = $val1;
				}
			}
		}
	}
	
	//print_r($more);

	//如果主权限没有，其对应的特殊权限不必存在
	foreach($more as $key=>$val){
		if(!in_array($key,$arr)){
			unset($more[$key]);
		}
	}
	
	$arr['more'] = $more;
	
	//客户列表-授权
	$actrole = $_POST['actrole'];
	if(!$actrole){
		$actrole = array();
	}
	
	$arr['actrole'] = $actrole;
	
	
	//其它
	$other = $_POST['other'];
	if(!$other){
		$other = array();
	}
	
	$arr['other'] = $other;
	
	
	//print_r($arr);
	//exit;

	$access = serialize($arr);
	$DB->query("update `t_role` set f_access = '{$access}',f_dataRange = '" . FPostInt('f_dataRange') . "' where id = '{$Id}'");

	FJS_P_AT(L('保存权限信息成功'),'?');
}else if($Clause == 'saveaccessold'){
	admin_action_log();
	
	//删除旧信息
	$DB->query("delete from `t_access` where role_id = '{$Id}' and group_id = 0");

	//保存新信息
	$access = $_POST['node_id'];
	if(!$access){
		$access = array();
	}
	foreach ($access as $value) {
		$module_id = intval($DB->getField("select pid from `t_node` where id = '{$value}'"));

		$DB->query("insert into `t_access` set role_id = '{$Id}',group_id = 0,module_id = '{$module_id}',node_id = '{$value}'");
	}

	FJS_P_AT(L('保存权限信息成功'),'?');
}else if($Clause == 'delinfo'){
	admin_action_log();

	$DB->query("update `t_role` set `status` = -1 where id = '{$Id}' and `status` >= 0");
	
	ajaxReturn('',L('删除数据成功'),1);
}else if($Clause == 'saveinfo'){
	admin_action_log();
	
	$name = FPostStr('name');
	$remark = FPostStr('remark');
	
	if(strlen($name) <= 0) {
		FJS_P_AC(L('名称不能为空'));
	}
	
	if($Id > 0){
		$where = "where id = '{$Id}' and status = 1";
		
		$sqlFVArr = array(
			'name'=>$name,
			'remark'=>$remark,
		);
		$affectId = $DB->update('t_role',$sqlFVArr,$where);
	
		FJS_P_AT(L('更新数据成功'),'?');
	}else{
		$sqlFVArr = array(
			'name'=>$name,
			'pid'=>$DRAdmin['f_roleId'],
			'remark'=>$remark,
			'status'=>1,
			'lock'=>0,
		);
		$affectId = $DB->insert('t_role',$sqlFVArr);
	
		FJS_P_AT(L('新增数据成功'),'?');
	}
}

if(!in_array($Clause,array('main','addinfo','access'))){
	$Clause = 'main';
}
require_once('role/' . $Clause . '.php');

