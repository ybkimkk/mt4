<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');


if($Clause == 'syncsymbol'){
	admin_action_log();

	$server = $DB->getDRow("select * from `t_mt4_server` where `status` = 1 and id = '" . $DRAdmin['server_id'] . "'");
	if(!$server){
		ajaxReturn('',L('服务器不存在'),0);
	}
	
	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0'){
		ajaxReturn('',L($retarry['info']),0);
	}

	$symbols = $mt4api->GetSymbols(1);
	$symbols = $symbols['ret'];
	$arrtype = explode(",",$symbols);
	$count=0;
	$nowtime = time();
	$delSymbolnum = 0;
	
	//自定义品种
	$cutome_types = $DB->getField("select id from `t_type` where `type` = 1 and `server_id` = '" . $DRAdmin['server_id'] . "' and `status` = 1",true);
	if(!$cutome_types){
		$cutome_types = array('0');
	}
	
	foreach($arrtype as $type){
		$arrex = explode("@",$type);
		$typeid=0;
		if($arrex[0]){ //名称
			$typename = trim(iconv("GB2312", "UTF-8", $arrex[0]));

			$exist = $DB->getDRow("select * from `t_type` where `type_name` = '{$typename}' and `server_id` = '{$server['id']}' and `type` = 0 and `status` = 1");
			if($exist){
				$typeid = $exist['id'];

				$DB->query("update `t_type` set `update_time` = '{$nowtime}',`status` = 1,`type` = 0 where id = '{$typeid}'");
			}else{
				$typeid = $DB->query("insert into `t_type` set `type_name` = '{$typename}',`server_id` = '{$server['id']}',`type` = 0,`status` = 1,`create_time` = '{$nowtime}',`update_time` = '{$nowtime}'");
			}
		}
		$arr = explode("|",$arrex[1]);
		
		$total = count($arr)-1;
		
		$symbols_fail='';
		foreach($arr as $u){
			if(empty($u)) continue;
			$symbol_attr = explode("#",$u);
		   
			$calc_formula = "forex";
			$start = 0;
			if(is_array($symbol_attr)){
				if(is_numeric($symbol_attr[1])==''){//货币对存在#号，第二个数据是否为合约量浮点数
					$symbol_attr[0] = $symbol_attr[0]."#".$symbol_attr[1];
					array_splice($symbol_attr,1,1);//移除第二个
				}
				$symbolname = iconv("GB2312", "UTF-8", $symbol_attr[0]);
				$contact_size = intval($symbol_attr[1]);
				$calc_formula = $symbol_attr[2];
			}else{
				$symbolname = iconv("GB2312", "UTF-8", $symbol_attr);
				$contact_size = 0;
			}
		   
			//$symbolname = str_replace("\"","&quot;",$symbolname);
			//$symbolname = str_replace("'","&#039;",$symbolname);
			$symbolname = trim($symbolname);

			if($symbolname&&$typeid){
				$wh_symbolname = trim(addslashes($symbolname));
				$result = $DB->getDTable("select symbol.* from t_symbol symbol, t_type type,t_mt4_server svr where symbol.type = type.id and type.server_id = svr.id and type.type=0 and svr.id='{$server['id']}' and  svr.status=1 and type.status=1 and symbol.symbol = '".$wh_symbolname."'");
			   
				if(count($result)>1){ //历史遗留问题，系统交易种类有多个，则删除后重建
					foreach($result as $samesynbol){
						$delSymbolnum += $DB->query("delete from `t_symbol` where `id` = '{$samesynbol['id']}'");
					}
					$result = null;
				}
				if(count($result)<=0){
					$DB->query("insert into `t_symbol` set `symbol` = '{$symbolname}',`contact_size` = '{$contact_size}',`calc_formula` = '{$calc_formula}',`time` = '{$nowtime}',`type` = '{$typeid}',`server_id` = '{$server['id']}'");
					
					$count++;
				}else{
					$DB->query("update `t_symbol` set `time` = '{$nowtime}',`contact_size` = '{$contact_size}',`calc_formula` = '{$calc_formula}',`server_id` = '{$server['id']}',`type` = '{$typeid}' where `symbol` = '{$symbolname}' and `server_id` = '{$server['id']}' and (`type` not in (" . implode(',',$cutome_types) . "))");
					
					$count++;
				}
			}
		}
	}
	
	//删除未更新的数据
	$delTypenum = $DB->query("update `t_type` set `status` = -1 where update_time < '{$nowtime}' and `server_id` = '{$server['id']}' and `type` = 0 and `status` = 1");
	
	//删除未更新的货币对
	$delSymbolnum += $DB->query("delete from `t_symbol` where `time` < '{$nowtime}' and `server_id` = '{$server['id']}' and (`type` not in (" . implode(',',$cutome_types) . "))");
	 
	 ajaxReturn('',L("同步成功"),1);
}else if($Clause == 'savesysinfo'){
	admin_action_log();

	 foreach ($_POST as $key=>$val){
		 if(substr($key,0,10) == 'stdsymbol_'){
			 $map = explode('_', $key);
			 
			 $val = FPostStr($key);
			 
			 $DB->query("update `t_symbol` set `std_symbol` = '{$val}' where id = '{$map[1]}'");
		 }
	 }
	 
	 FJS_AT(L('保存成功'),'?');
}else if($Clause == 'savelotb'){
	admin_action_log();

	 foreach ($_POST as $key=>$val){
		 if(substr($key,0,7) == 'f_lotB_'){
			 $map = explode('_', $key);
			 
			 $val = FPostStr($key);
			 
			 $DB->query("update `t_symbol` set `f_lotB` = '{$val}' where id = '{$map[2]}'");
		 }
	 }
	 
	 FJS_AT(L('保存成功'),'?');
}else if($Clause == 'viewsymboltype'){
	$Id = FPostInt('id');
	
	$type = $DB->getDRow("select * from `t_type` where id = '{$Id}'");
	
	$data = $DB->getDTable("select symbol from `t_symbol` where `type` = '{$Id}' and server_id = '" . $DRAdmin['server_id'] . "'");
	
	$symbls = "";
	foreach ($data as $symbolkey =>$symbolval){
		$symbls=$symbls.$symbolval['symbol'].",";
	}
	$type['symbols'] = $symbls;
	
	ajaxReturn($type,'',1);
}else if($Clause == 'savesymboltype'){
	admin_action_log();
	
	$type_name = FPostStr('type_name');
	if(strlen($type_name) <= 0){
		ajaxReturn('',L('请填写交易种类名称'),0);
	}
	if(strstr($type_name,",")){
		ajaxReturn('',L('交易种类名称不能包含,特殊字符'),0);
	}
	
	$DB->startTrans();
	
		$symbol_array = $_POST['symbols'];
		if(count($symbol_array)==1&&$symbol_array[0]==''){
			ajaxReturn('',L('请至少选择一种交易品种'),0);
		}
		
		$Id = FPostInt('id');
	
		$resulttype = $DB->getDRow("select * from `t_type` where type_name = '{$type_name}' and server_id = '" . $DRAdmin['server_id'] . "' and `status` = '1'");
		if($resulttype && $resulttype['id'] != $Id){
		   ajaxReturn('',L('交易种类名称已经存在'),0);
		}
	
		if($Id > 0){
		   $type_id = $resulttype['id'];
		}else{
		   $type_id = $DB->query("insert into `t_type` set type_name = '{$type_name}',server_id = '" . $DRAdmin['server_id'] . "',`status` = '1',create_time = '" . time() . "',update_time = '" . time() . "',type = '1'");
		}
	
		$DB->query("delete from `t_symbol` where `type` = '{$type_id}'");
		
		foreach ($symbol_array as $symbolkey =>$symbolval){
		   $symbolval = str_replace("&amp;quot;","\"",$symbolval);//双引号问题
	
		   $wh_symbolval = str_replace("'","\'",$symbolval);
		   $existsybol = $DB->getDRow("select symbol.*,type.type_name from t_symbol symbol, t_type type,t_mt4_server svr where symbol.type = type.id and type.server_id = svr.id and type.type=1 and svr.id='" . $DRAdmin['server_id'] . "' and  svr.status=1 and type.status=1 and symbol.symbol = '".$wh_symbolval."'");
		   if($existsybol){
			   $DB->rollback();
	
			   ajaxReturn('',"【".$symbolval."】".L('已经归属于自定义交易种类')."【".$existsybol['type_name']."】".L('不能重复').'！',0);
		   }
		   
		   $result = $DB->getDRow("select * from `t_symbol` where `symbol` = '{$symbolval}' and `type` = '{$type_id}' and `server_id` = '" . $DRAdmin['server_id'] . "'");
		   if(!$result){
			   $templatesymbol = $DB->getDRow("select * from `t_symbol` where `symbol` = '{$symbolval}' and server_id = '" . $DRAdmin['server_id'] . "'");
			   
			   $DB->query("insert into `t_symbol` set `symbol` = '{$symbolval}',`type` = '{$type_id}',server_id = '" . $DRAdmin['server_id'] . "',`contact_size` = '{$templatesymbol['contact_size']}',`calc_formula` = '{$templatesymbol['calc_formula']}',time = '" . time() . "'");
		   }
		}

	$DB->commit();
	
	ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'delinfo'){
	admin_action_log();

	 $resulttype = $DB->getDRow("select * from `t_type` where id = '{$Id}'");
	 if(!$resulttype){
		 ajaxReturn('',L('交易种类名称不存在'),0);
	 }
	 if($resulttype['type']!='1'){
		 ajaxReturn('',L('交易种类不能删除'),0);
	 }
	 
	 $DB->query("update `t_type` set `status` = -1 where id = '{$Id}'");
	 
	 $DB->query("delete from `t_symbol` where `type` = '{$Id}'");
	 
	 ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main','editsysinfo','editlotb'))){
	$Clause = 'main';
}
require_once('sync_symbol/' . $Clause . '.php');

