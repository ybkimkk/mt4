<?php
/*
2015-10-29 更新


*/

class CAdmin{
	function __construct(){
	
	}
	
	function __destruct(){
		
	}
	
	function run(){
		global $clause;
		
		switch($clause){
			case 'add_info':
				$this->menu();
				$this->add_info();
				break;
			case 'save_add_info':
				$this->save_add_info();
				break;
			case 'edit_info':
				$this->menu();
				$this->edit_info();
				break;
			case 'save_edit_info':
				$this->save_edit_info();
				break;
			case 'update_bool':
				$this->update_bool();
				break;
			case 'del_info':
				$this->del_info();
				break;
			case 'log_login':
				$this->menu();
				$this->log_login();
				break;
			default:
				$this->menu();
				$this->main();
				break;
		}
	}

	function del_info(){
		global $DB;
		global $logged_admin_id;
		
		$id = FGetInt('id');
		
		if($id == $logged_admin_id){
			FJS_AB('抱歉，您不可以删除自己，否则您将登录不了！');
		}
	
		$DB->query("delete from `t_admin` where id = {$id}");
		
		FRedirect(FPrevUrl());
	}
	
	function save_edit_info(){
		global $DB;
		global $logged_admin_id;
		
		$id = FGetInt('id');
		$rs = $DB->getDRow("select * from `t_admin` where id = {$id}");
		if(!$rs){
			FJS_AB('抱歉，未找到您指定的管理员！');
		}
	
		$f_username = FPostStr('f_username');
		$f_password = FPostStr('f_password');
		$rePassWord = FPostStr('rePassWord');
		$f_nickname = FPostStr('f_nickname');
		$f_isPass = FPostInt('f_isPass');
		
		$rs = $DB->getDRow("select * from `t_admin` where f_username = '{$f_username}' and id <> {$id}");
		if($rs){
			FJS_AB('抱歉，该管理员帐号已经被使用，请换一个！');
		}
		
		if(!$f_isPass){
			if($id == $logged_admin_id){
				FJS_AB('抱歉，不能锁定自己的帐号，否则您将登录不了！');
			}
		}
	
		if($f_username == ''){
			FJS_AB('抱歉，帐号不能为空');
		}
		if($f_password != ''){
			if($f_password != $rePassWord){
				FJS_AB('抱歉，两次输入的密码不一致');
			}
		}
		if($f_nickname == ''){
			FJS_AB('抱歉，昵称不能为空');
		}
		
		$adminMenu3 = new CAdminMenu3();
		$f_permissions = $adminMenu3->createPostPermissions();
	
		$sqlFVArr = array(
			'f_username'=>$f_username,
			'f_nickname'=>$f_nickname,
			'f_isPass'=>$f_isPass,
			'f_permissions'=>$f_permissions,
		);
		if($f_password != ''){
			$sqlFVArr['f_password'] = md5($f_password);
		}
		$affectId = $DB->update('t_admin',$sqlFVArr,"id = {$id}");
	
		FJS_AT('修改成功！','?');
	}
	
	function edit_info(){
		global $DB;
		
		$id = FGetInt('id');
		$rs = $DB->getDRow("select * from `t_admin` where id = {$id}");
		if(!$rs){
			FJS_AB('抱歉，未找到您指定的管理员！');
		}
		
		$adminMenu3 = new CAdminMenu3();
	
		echo '<form action="?clause=save_edit_info&id=' . $id . '" method="post" name="myForm" id="myForm">';
		echo '<br />';
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">编辑管理员帐号</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">登录帐号：</td>';
		echo '<td class="l85"><input type="text" name="f_username" id="f_username" class="input3" maxlength="16" value="' . $rs['f_username'] . '" /> <span class="mustInput">*</span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">登录密码：</td>';
		echo '<td class="l85"><input type="password" name="f_password" id="f_password" class="input3" maxlength="32" value="" /> 不修改请留空</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">确认密码：</td>';
		echo '<td class="l85"><input type="password" name="rePassWord" id="rePassWord" class="input3" maxlength="32" value="" /> 不修改请留空</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">昵称/姓名：</td>';
		echo '<td class="l85"><input type="text" name="f_nickname" id="f_nickname" class="input3" maxlength="20" value="' . $rs['f_nickname'] . '" /> <span class="mustInput">*</span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">状态：</td>';
		echo '<td class="l85"><input type="checkbox" name="f_isPass" id="f_isPass" value="1"' . ($rs['f_isPass'] ? ' checked="checked"' : '') . ' /><label for="f_isPass">通过审核，允许登录管理系统</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">权限：</td>';
		echo '<td class="l85">';
		echo $adminMenu3->permissionsCheckBox('../left_menu_xml.php',$rs['f_permissions']);
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="2" class="tac">';
		echo '<input type="submit" name="send_btn_" value=" 确定保存 " id="send_btn_" class="bttn" />';
		echo ' <input type="button" name="Submit2" value=" 返回 " class="bttn" onClick="history.back();" />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';
	}
	
	function save_add_info(){
		global $DB;
	
		$f_username = FPostStr('f_username');
		$f_password = FPostStr('f_password');
		$rePassWord = FPostStr('rePassWord');
		$f_nickname = FPostStr('f_nickname');
		$f_isPass = FPostInt('f_isPass');
	
		if($f_username == ''){
			FJS_AB('抱歉，帐号不能为空');
		}
		if($f_password == ''){
			FJS_AB('抱歉，密码不能为空');
		}
		if($f_password != $rePassWord){
			FJS_AB('抱歉，两次输入的密码不一致');
		}
		if($f_nickname == ''){
			FJS_AB('抱歉，昵称不能为空');
		}
		
		$rs = $DB->getDRow("select * from `t_admin` where f_username = '{$f_username}'");
		if($rs){
			FJS_AB('抱歉，该管理员帐号已经被使用，请换一个！');
		}
		
		$adminMenu3 = new CAdminMenu3();
		$f_permissions = $adminMenu3->createPostPermissions();
	
		$sqlFVArr = array(
			'f_username'=>$f_username,
			'f_password'=>md5($f_password),
			'f_nickname'=>$f_nickname,
			'f_isPass'=>$f_isPass,
			'f_permissions'=>$f_permissions,
		);
		$affectId = $DB->insert('t_admin',$sqlFVArr);
	
		FJS_AT('添加成功！','?');
	}
	
	function add_info(){
		$adminMenu3 = new CAdminMenu3();
	
		echo '<form action="?clause=save_add_info" method="post" name="myForm" id="myForm">';
		echo '<br />';
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">添加新管理员帐号</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">登录帐号：</td>';
		echo '<td class="l85"><input type="text" name="f_username" id="f_username" class="input3" maxlength="16" value="" /> <span class="mustInput">*</span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">登录密码：</td>';
		echo '<td class="l85"><input type="password" name="f_password" id="f_password" class="input3" maxlength="32" value="" /> <span class="mustInput">*</span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">确认密码：</td>';
		echo '<td class="l85"><input type="password" name="rePassWord" id="rePassWord" class="input3" maxlength="32" value="" /> <span class="mustInput">*</span> 请再输入一次上方的密码，防止输错</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">昵称/姓名：</td>';
		echo '<td class="l85"><input type="text" name="f_nickname" id="f_nickname" class="input3" maxlength="20" value="" /> <span class="mustInput">*</span></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">状态：</td>';
		echo '<td class="l85"><input type="checkbox" name="f_isPass" id="f_isPass" value="1" checked="checked" /><label for="f_isPass">通过审核，允许登录管理系统</label></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="r15">权限：</td>';
		echo '<td class="l85">';
		echo $adminMenu3->permissionsCheckBox('../left_menu_xml.php','');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="2" class="tac">';
		echo '<input type="submit" name="send_btn_" value=" 确定添加 " id="send_btn_" class="bttn" />';
		echo ' <input type="button" name="btn_back_" value=" 返回 " class="bttn" onClick="history.back();" />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';
	}
	
	function update_bool(){
		global $DB;
		global $logged_admin_id;
		global $id;
		
		$field = FGetStr('field');
		$state = FGetInt('state');
		
		if($field == 'f_isPass' && $state == 0 && $id == $logged_admin_id){
			FJS_AB('抱歉，不能锁定自己的帐号，否则您将登录不了！');
		}
	
		$DB->query("update `t_admin` set {$field} = {$state} where id = {$id}");
		
		FRedirect(FPrevUrl());
	}
	
	function menu(){
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="2" class="title">相关操作</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="2" class="tac">';
		echo '<a href="?clause=add_info">添加管理员</a>';
		echo ' | <a href="?">管理员列表</a>';
		echo ' || <a href="?clause=log_login">登录日志</a>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
	}
	
	function main(){
		global $DB;
		
		echo '<br />';
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="4" class="title">所有管理员</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="sTitle" width="25%">帐号</td>';
		echo '<td class="sTitle" width="25%">昵称</td>';
		echo '<td class="sTitle" width="25%">状态</td>';
		echo '<td class="sTitle">相关操作</td>';
		echo '</tr>';
		
		$recordCount = $DB->counter('t_admin','id','');
		$pagerSize = 10;
		$pager_config = array(
			"record_count"=>$recordCount,
			"pager_size"=>$pagerSize,
			"pager_index"=>(int)$_GET["page"],
			"show_front_btn"=>false,
			"show_last_btn"=>false,
			"skin"=>'blue'
		);
		$pager = new CPager2($pager_config);
		$sqlRecordStartIndex = $pager->getSqlRecordStartIndex();
		unset($query);
		if($recordCount > 0){
			$query = $DB->query("select * from `t_admin` order by f_addTime desc LIMIT {$sqlRecordStartIndex},{$pagerSize}");
		}
		if($recordCount > 0){
			while($rs = $DB->fetch_array($query)){
				echo '<tr>';
				echo '<td class="tac">' . $rs['f_username'] . '</td>';
				echo '<td class="tac">' . $rs['f_nickname'] . '</td>';
				echo '<td class="tac">';
				if($rs['f_isPass']){
					echo '<a href="?clause=update_bool&field=f_isPass&state=0&id=' . $rs['id'] . '">正常，可登录</a>';
				}else{
					echo '<a href="?clause=update_bool&field=f_isPass&state=1&id=' . $rs['id'] . '" style="color:#ff0000;">锁定，不可登录</a>';
				}
				echo '</td>';
				echo '<td class="tac"><div>';
				echo '<a href="?clause=edit_info&id=' . $rs['id'] . '">修改</a>';
				echo ' | ';
				echo '<a href="#nolink" onClick="HintAndTurn(\'确定要删除吗？请谨慎操作，一旦删除，没有办法恢复！\',\'?clause=del_info&id=' . $rs['id'] . '&prevUrl=' . urlencode(FGetCurrentUrl(4)) . '\');">删除</a>';
				echo '</div></td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr>
					<td class="tac" colspan="100">未找到相关数据</td>
				  </tr>';
		}
		echo '</table>';	
		if($recordCount > 0){
			echo '<div class="br"></div>';
			echo '<div class="pager-c">' . $pager->pager() . '</div>';
		}
	}
	
	function log_login(){
		global $DB;
		global $act;
		
		if($act == 'rd7'){
			$DB->query("delete from `t_log_admin_login` where timestampdiff(day ,date_format(f_addTime,'%Y-%m-%d'),date_format(sysdate(),'%Y-%m-%d')) > 7");
			
			FRedirect(FPrevUrl());
		}else if($act == 'clear'){
			$DB->query("delete from `t_log_admin_login`");
			
			FRedirect(FPrevUrl());
		}
		
		echo '<br />';
		echo '<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" class="borderTable">';
		echo '<tr>';
		echo '<td colspan="4" class="title">登录日志</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class="sTitle" width="25%">帐号</td>';
		echo '<td class="sTitle" width="25%">状态</td>';
		echo '<td class="sTitle" width="25%">IP</td>';
		echo '<td class="sTitle">时间</td>';
		echo '</tr>';
		
		$recordCount = $DB->counter('t_log_admin_login','id','');
		$pagerSize = 10;
		$pager_config = array(
			"record_count"=>$recordCount,
			"pager_size"=>$pagerSize,
			"pager_index"=>(int)$_GET["page"],
			"show_front_btn"=>false,
			"show_last_btn"=>false,
			"skin"=>'blue'
		);
		$pager = new CPager2($pager_config);
		$sqlRecordStartIndex = $pager->getSqlRecordStartIndex();
		unset($query);
		if($recordCount > 0){
			$query = $DB->query("select * from `t_log_admin_login` order by f_addTime desc LIMIT {$sqlRecordStartIndex},{$pagerSize}");
		}
		if($recordCount > 0){
			while($rs = $DB->fetch_array($query)){
				echo '<tr>';
				echo '<td class="tac">' . $rs['f_username'] . '</td>';
				echo '<td class="tac">';
				if($rs['f_state'] == '登陆成功'){
					echo '<img src="/js/MyAdmin/images/yes.gif" align="absmiddle" /> ';
				}else{
					echo '<img src="/js/MyAdmin/images/no.gif" align="absmiddle" /> ';
				}
				echo $rs['f_state'] . '</td>';
				echo '<td class="tac">';
				echo '<a href="http://www.ip138.com/ips138.asp?ip=' . $rs['f_ip'] . '&action=2" target="_blank">' . $rs['f_ip'] . '</a>';
				echo '</td>';
				echo '<td class="tac">';
				echo $rs['f_addTime'];
				echo '</td>';
				echo '</tr>';
			}
		}
		else
		{
			echo '<tr>
					<td class="tac" colspan="100">未找到相关数据</td>
				  </tr>';
		}
		echo '</table>';
		
		echo '<table width="98%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin-top:5px;">';
		echo '<tr>';
		echo '<td>';
		echo '<input type="button" onClick="HintAndTurn(\'确定只保留7天内日志，其它删除吗？\',\'?clause=log_login&act=rd7&prevUrl=' . urlencode(FGetCurrentUrl(4)) . '\');" class="bttn" value="只保留7天内日志，其它删除" />';
		echo ' <input type="button" onClick="HintAndTurn(\'确定删除所有日志吗？\',\'?clause=log_login&act=clear&prevUrl=' . urlencode(FGetCurrentUrl(4)) . '\');" class="bttn" value="删除所有日志" />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
			
		if($recordCount > 0){
			echo '<div class="br"></div>';
			echo '<div class="pager-c">' . $pager->pager() . '</div>';
		}
	}
}