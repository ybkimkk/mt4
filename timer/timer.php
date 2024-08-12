<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

function timer_save_log(){
	global $logStr;
	$fp = fopen('logs/' . date('Y-m-d-H') . '.txt', 'a+b');
	fwrite($fp, date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']) . "\r\n\r\n");
	fwrite($fp, $logStr);
	fwrite($fp, "----------------\r\n\r\n");
	fclose($fp);
	exit;
}

if(FGetStr('key') != '5798432'){
	echo 'KEY错误';
	exit;
}

function credit_zye($crId = 0){
	global $logStr;
	global $DB;

	//未扣回、且
	if($crId <= 0){
		//每一笔交易只能用于一个赠金转余额的检测，按赠金先后顺序，前面的赠金先累积交易手数，满了（则可以转余额，由后台手工审核），后面的赠金则接着继续累加交易手数。类推。
		//审核时（审核通过转余额、取消赠金、提前结束等），可以指定 交易量转移。
		
		//按用户分组，取各组里最早的那条
		// and f_zye_endTime <= '" . time() . "'
		$minQuery = $DB->query("select * from t_credit_record where Id in (select min(Id) from t_credit_record where (f_endTimeChkEd = 0 and f_endTime > 0 and f_endBackTime = 0 and f_zye_endTimeChkEd = 0 and f_zye_endTime > 0 and f_zye_endBackTime = 0 and Status = 1) or (f_zye_endTimeChkEd = 1 and f_zye_endTimeChkState = 1 and f_zye_endBackTime = 0) group by MtLogin) order by f_zye_lastChkTime asc");
		//遍历查找，如果已经检测过、待后台确认的，跳过，只检测那些还没有检测的
		while($rsMin = $DB->fetchArray($minQuery)){
			if($rsMin['f_zye_endTimeChkEd'] <= 0){
				$crId = $rsMin['Id'];
				break;
			}
		}
	}
	if($crId > 0){
		$rs = $DB->getDRow("select * from t_credit_record where f_endTimeChkEd = 0 and f_endTime > 0 and f_endBackTime = 0 and f_zye_endTimeChkEd = 0 and f_zye_endTime > 0 and f_zye_endBackTime = 0 and Status = 1 and Id = " . $crId);
	}
	if($rs){
		$logStr .= "符合数据" . $rs['Id'] . "<br>\r\n";

		$server  = $DB->getDRow("select * from `t_mt4_server` where Id = '{$rs['ServerId']}'");
		if(!$server){
			$about = "未找到服务器：" . $rs['ServerId'];
			
			//不扣回
			$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_zye_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_zye_lastChkTime = '" . time() . "',f_endBackAbout = '" . $about . "',f_zye_endBackTime = '" . time() . "',f_zye_endBackAbout = '" . $about . "' where Id = '{$rs['Id']}'");

			$logStr .= $about . "<br>\r\n";
			return;
		}
		if($server['ver'] > 4){
			$logStr .= "暂不支持MT5<br>\r\n";
			return;
		}

		if($rs['Result'] <= 0){
			$about = "赠金为负数：" . $rs['Result'];
			
			//不扣回
			$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_zye_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_zye_lastChkTime = '" . time() . "',f_endBackAbout = '" . $about . "',f_zye_endBackTime = '" . time() . "',f_zye_endBackAbout = '" . $about . "' where Id = '{$rs['Id']}'");

			$logStr .= $about . "<br>\r\n";
			return;
		}
		
		$rsCS = $DB->getDRow("select * from t_credit_setting where Id = '" . $rs['CreditId'] . "'");
		if(!$rsCS){
			$about = "未找到赠金规则：" . $rs['CreditId'];
			
			//待扣回
			$DB->query("update t_credit_record set f_zye_endTimeChkEd = 1,f_zye_endBackTime = '" . time() . "',f_zye_lastChkTime = '" . time() . "',f_zye_endBackAbout = '" . $about . "' where Id = '{$rs['Id']}'");

			$logStr .= $about . "<br>\r\n";
			return;
		}

		$loginid = $rs['MtLogin'];
		$number = $rs['Result'];
		
		
		//检测是否达到赠金转余额条件
		
		//取产品列表
		$f_zye_symbol = $rsCS['f_zye_symbol'];
		$f_zye_symbol = trim(trim($f_zye_symbol),',');
		if(strlen($f_zye_symbol) <= 0){
			$about = "赠金规则-产品列表为空：" . $rs['CreditId'];
			
			//待扣回
			$DB->query("update t_credit_record set f_zye_endTimeChkEd = 1,f_zye_endBackTime = '" . time() . "',f_zye_lastChkTime = '" . time() . "',f_zye_endBackAbout = '" . $about . "' where Id = '{$rs['Id']}'");

			$logStr .= $about . "<br>\r\n";
			return;
		}
		$f_zye_symbol = "'" . str_replace(',',"','",$f_zye_symbol) . "'";

		$proStr = '';
		$proArr = array();
		$query1 = $DB->query("select * from t_symbol where `type` in (select id from `t_type` where type_name in ({$f_zye_symbol}) and server_id = '{$rs['ServerId']}' and status = 1) and server_id = '{$rs['ServerId']}'");
		while($rs1 = $DB->fetchArray($query1)){
			if(strlen($rs1['symbol']) <= 0){
				continue;
			}
			if($rs1['f_lotB'] <= 0){
				continue;
			}
			
			$proArr[$rs1['f_lotB']][] = $rs1['symbol'];
			
			if(strlen($proStr) > 0){
				$proStr .= ',';
			}
			$proStr .= $rs1['symbol'];
		}
		
		/*echo $logStr;
		print_r($proArr);
		exit;*/

		$lotInfoArr = array();
		$sum = 0;
		$startTime = date('Y-m-d H:i:s',$rsCS['f_startTime'] + 3600 * floatval(8 - $server['time_zone']));
		$endTime = date('Y-m-d H:i:s',$rsCS['EndTime'] + 3600 * floatval(8 - $server['time_zone']));
		//按先后顺序，累加起来
		{
			//用户、交易
			$where1 = "where LOGIN = '{$loginid}' and CMD in (0,1)";
			//时间段内
			$where1 .= " and OPEN_TIME >= '{$startTime}'";
			$where1 .= " and OPEN_TIME <= '{$endTime}'";
			$where1 .= " and CLOSE_TIME >= '{$startTime}'";
			$where1 .= " and CLOSE_TIME <= '{$endTime}'";
			//产品
			$where1 .= " and SYMBOL in ('" . str_replace(",","','",$proStr) . "')";
			//持单时间
			if($rsCS['f_keepTimeSecond'] > 0){
				$where1 .= " and (UNIX_TIMESTAMP(CLOSE_TIME) - UNIX_TIMESTAMP(OPEN_TIME)) >= " . $rsCS['f_keepTimeSecond'];
			}
			$where1 .= " and (TICKET not in (select f_ticket from t_credit_record_tickets where f_login = '{$loginid}' and f_again = 0))";
			
			$sqlC = "select * from {$server['db_name']}.mt4_trades {$where1} order by CLOSE_TIME asc";
			$queryC = $DB->query($sqlC);
			while($rsC = $DB->fetchArray($queryC)){
				$bs = 0;
				foreach($proArr as $key=>$val){
					if(stripos(',' . implode(',',$val) . ',',',' . $rsC['SYMBOL'] . ',') !== false){
						$bs = $key;
						break;
					}
				}
				if($bs > 0){
					$sum1 = number_format(floatval($rsC['VOLUME']) / floatval($bs),2,'.','');
					$sum += $sum1;
					
					$lotInfoArr['tickets'][$rsC['TICKET']] = $bs;
				}

				if($sum >= floatval($rsCS['f_zye_lot'])){
					break;
				}
			}
		}

		$lotInfoArr['db_name'] = $server['db_name'];
		$lotInfoArr['sum'] = $sum;
		$lotInfoArr['rclot'] = $rsCS['f_zye_lot'];

		if($rs['f_zye_endTime'] < time()){
			//过期
			//更新为未通过
			$data = array();
			//要扣回
			$data['f_zye_endTimeChkEd'] = 1;
			$data['f_zye_endTimeChkState'] = -1;
			$data['f_zye_lot'] = $sum;
			$data['f_zye_lot_info'] = serialize($lotInfoArr);
			$data['f_zye_lastChkTime'] = time();
			$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");

			$logStr .= "检测失败（过期）：" . $sum . "<br>\r\n";
		}else{
			//未过期
			if($sum >= floatval($rsCS['f_zye_lot'])){
				//如果实际交易手数大于 转余额交易手数 要求
				//标识为可转余额，后台审核
				$data = array();
				$data['f_endTimeChkEd'] = 1;//不扣回
				$data['f_zye_endTimeChkEd'] = 1;
				$data['f_zye_endTimeChkState'] = 1;
				$data['f_zye_lot'] = $sum;
				$data['f_zye_lot_info'] = serialize($lotInfoArr);
				$data['f_zye_lastChkTime'] = time();
				$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");

				$logStr .= "检测成功：" . $sum . "<br>\r\n";
			}else{
				//更新统计数据，等等下次检测
				$data = array();
				$data['f_zye_lot'] = $sum;
				$data['f_zye_lot_info'] = serialize($lotInfoArr);
				$data['f_zye_lastChkTime'] = time();
				$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");

				$logStr .= "检测失败（未过期）：" . $sum . "<br>\r\n";
			}
		}
	}else{
		$logStr .= "没有符合的数据<br>\r\n";
	}
}

function credit_discount(){
	global $logStr;
	global $DB;

	$rs = $DB->getDRow("select * from t_credit_record where f_endTimeChkEd = 0 and f_endTime > 0 and f_endBackTime = 0 and f_endTime <= '" . time() . "' and Status = 1 order by f_endTime asc");
	if($rs){
		$logStr .= "符合数据" . $rs['Id'] . "<br>\r\n";

		$server  = $DB->getDRow("select * from `t_mt4_server` where Id = '{$rs['ServerId']}'");
		if(!$server){
			$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = '未找到服务器：" . $rs['ServerId'] . "' where Id = '{$rs['Id']}'");

			$logStr .= "未找到服务器：" . $rs['ServerId'] . "<br>\r\n";
			return;
		}
		if($server['ver'] > 4){
			$logStr .= "暂不支持MT5<br>\r\n";
			return;
		}

		if($rs['Result'] <= 0){
			$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = '赠金为负数：" . $rs['Result'] . "' where Id = '{$rs['Id']}'");

			$logStr .= "赠金为负数：" . $rs['Result'] . "<br>\r\n";
			return;
		}

		$loginid = $rs['MtLogin'];
		$number = $rs['Result'];

		$reportModel = new ReportModel($server['db_name'],$server['ver']);
		$ticket = $reportModel->getTicketByComment('7',"Credit " . $rs['Object'] . " discount#" . $rs['Id'],$server['ver'],$loginid);
		if(!$ticket){
			try {
				$mt4api = new MtApiModel($server['ver']);
				$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
				$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
				if ($retarry['ret'] != 0) {
					$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：" . $retarry['info'] . "' where Id = '{$rs['Id']}'");

					$logStr .= "api错误：" . $retarry['info'] . "<br>\r\n";
					return;
				}
				$inid = $mt4api->credit($loginid, -$number, "Credit " . $rs['Object'] . " discount#" . $rs['Id']); //扣回MT赠金
				$inid = $inid['ret'];
			} catch (Exception $e) {
				$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：连接mtapi接口异常' where Id = '{$rs['Id']}'");

				$logStr .= "api错误：连接mtapi接口异常<br>\r\n";
				return;
			}
			if ($inid == '-88') {//入金失败
				$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：重复赠金' where Id = '{$rs['Id']}'");

				$logStr .= "api错误：重复赠金<br>\r\n";
				return;
			} else if ($inid <= '-1') {//入金失败
				$DB->query("update t_credit_record set f_endTimeChkEd = 1,f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：赠金审核失败' where Id = '{$rs['Id']}'");

				$logStr .= "api错误：赠金审核失败<br>\r\n";
				return;
			}

			$data = array();
			$data['f_endTimeChkEd'] = 1;
			$data['f_endBackTime'] = time();
			$data['f_endBackTicket'] = $inid;
			$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");

			$logStr .= "赠金扣回成功：" . $inid . "<br>\r\n";
		}else{
			$logStr .= "经检测，重复了2：" . $rs['Id'] . "<br>\r\n";
		}
	}else{
		$logStr .= "没有符合的数据<br>\r\n";
	}
}

function sale_commission_other(){
	global $logStr;
	global $DB;

	$server  = $DB->getDRow("select * from `t_mt4_server` where `status` = 1 and `real` = 1 order by id asc");
	if(!$server){
		$logStr .= "未找到服务器<br>\r\n";
		return;
	}
	/*if($server['ver'] > 4){
		$logStr .= "暂不支持MT5<br>\r\n";
		return;
	}*/
	$currMt4Time = time() - 3600 * floatval(8 - $server['time_zone']);
	$logStr .= "mt4时间：" . date('Y-m-d H:i:s',$currMt4Time) . "<br>\r\n";
	
	$rs = $DB->getDRow("select * from t_sale_setting_other where STATUS = 1 and TC_DATE_E < '" . date('Y-m-d H:i:s',$currMt4Time) . "' and f_isJs = 0 order by TC_DATE_E asc");
	if($rs){
		if($server['id'] != $rs['SERVER_ID']){
			$logStr .= "多台服务器，不支持：" . $server['id'] . '|' . $rs['SERVER_ID'] . "<br>\r\n";
			return;
		}

		$logStr .= "符合数据：" . $rs['ID'] . "<br>\r\n";

		//符合 等级、MT分组 的代理，循环这些代理
		//取其 统计团队 成员列表
		//成员的 平仓时间、达到手数 的产品
		//进行返佣计算
		$sql = "select * from t_member where userType = 'agent'";
		if($rs['LEVEL'] > 0){
			$sql .= " and level = '{$rs['LEVEL']}'";
		}

		$groupName = trim($rs['GROUP_NAME'],',');$groupName = "'" . str_replace(",","','",$groupName) . "'";$groupName = str_replace("\\","\\\\",$groupName);

		$dbName = $server['db_name'];	
		if($server['ver'] == 5){
			$groupLoginSql = "select Login as uLogin from " . $dbName . ".mt5_users where `Group` in ({$groupName})";
		}else{
			$groupLoginSql = "select LOGIN as uLogin from " . $dbName . ".mt4_users where `GROUP` in ({$groupName})";
		}
		$sql .= " and id in (select member_id from t_member_mtlogin where loginid in ({$groupLoginSql}) and mt_type = 0)";
		//echo $sql , "\r\n<br>\r\n";
		$query = $DB->query($sql);
		if($DB->numRows($query) > 0){
			$symbolType = trim($rs['SYMBOL_TYPE'],',');$symbolType = "'" . str_replace(",","','",$symbolType) . "'";$symbolType = str_replace("\\","\\\\",$symbolType);
			$query1 = $DB->query("select * from t_symbol where `type` in (select id from t_type where type_name in (" . $symbolType . ") and status = 1 and server_id = " . $server['id'] . ") and server_id = " . $server['id'] . "");
			$symbol = '';
			$symbolSumSql = 'CASE';
			while($rs1 = $DB->fetchArray($query1)){
				if(strlen($symbol)){
					$symbol .= ',';
				}
				$symbol .= "'" . $rs1['symbol'] . "'";

				if($rs1['f_lotB'] > 0){
					$symbolSumSql .= " WHEN SYMBOL = '" . $rs1['symbol'] . "' THEN VOLUME/" . $rs1['f_lotB'];
				}
			}
			$symbolSumSql .= " ELSE 0";
			$symbolSumSql .= ' END';
			if(strlen($symbol) <= 0){
				$DB->query("update t_sale_setting_other set f_isJs = 1,f_jsTime = '" . date('Y-m-d H:i:s') . "',f_jsAbout = '(ERR)货币对为空' where ID = '{$rs['ID']}'");

				$logStr .= "货币对为空<br>\r\n";
				return;
			}

			//把本规则的运算结果集取出（A）
			$query1 = $DB->query("select f_uid from t_sale_commission_other where f_settingId = '{$rs['ID']}'");
			$scoArr = array();
			while($rs1 = $DB->fetchArray($query1)){
				$scoArr[$rs1['f_uid']] = 1;
			}

			//开仓平仓至少间隔
			$order_open_close_interval = 0;
            $OPEN_CLOSE_INTERVAL = getConfigValue('OPEN_CLOSE_INTERVAL', $server['id']);
            if ($OPEN_CLOSE_INTERVAL) {
                $order_open_close_interval = floatval($OPEN_CLOSE_INTERVAL);
            }

			while($rsAgent = $DB->fetchArray($query)){
				
				//检测是否已经在数据库中了（A）
				if($scoArr[$rsAgent['id']]){
					$logStr .= "【" . $rsAgent['id'] . "】已计算，跳过<br>\r\n";
				}else{
					if($rs['GROUP_TYPE'] == 2){
						//伞下
						$childUids = getunderCustomerIds($rsAgent['id'],0);
					}else{
						//直接下级
						$childUids = getunderCustomerIds($rsAgent['id'],1);
					}
					$childLogins = '';
					if($childUids){
						$childLogins = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$childUids) . ") and status = 1");
					}
					if($childLogins){
						if($server['ver'] == 5){
							$calSql = "select sum({$symbolSumSql}) as VOLUME,sum(Profit) as PROFIT";
							$calSql .= " from " . $dbName . ".`v_mt5_trades_pc`";
							$calSql .= " where (CLOSE_TIME between '{$rs['TC_DATE_S']}' and '{$rs['TC_DATE_E']}')";
							$calSql .= " and Login in (" . implode(',',$childLogins) . ")";
							//$calSql .= " and Action in (0,1)";
							//$calSql .= " and Entry = 1";
							if($order_open_close_interval > 0){
								$calSql .= " and OCSecond >= " . $order_open_close_interval;
							}
							$calSql .= " and Symbol in ({$symbol})";
						}else{
							$calSql = "select sum({$symbolSumSql}) as VOLUME,sum(PROFIT) as PROFIT";
							$calSql .= " from " . $dbName . ".`mt4_trades`";
							$calSql .= " where (CLOSE_TIME between '{$rs['TC_DATE_S']}'";
							$calSql .= " and '{$rs['TC_DATE_E']}')";
							$calSql .= " and LOGIN in (" . implode(',',$childLogins) . ")";
							$calSql .= " and CMD in (0,1)";
							if($order_open_close_interval > 0){
								$calSql .= " and (UNIX_TIMESTAMP(CLOSE_TIME)-UNIX_TIMESTAMP(OPEN_TIME)) >= " . $order_open_close_interval;
							}
							$calSql .= " and SYMBOL in ({$symbol})";
						}
						//echo $calSql , "\r\n<br>";
						$rsSum = $DB->getDRow($calSql);
						$VOLUME = floatval($rsSum['VOLUME']);
						if($VOLUME >= $rs['LIMIT_MIN_SS']){
							$cal = calBounsNum_($rs['CAL_TYPE_AGENT'],$rs['CAL_NUM_AGENT'],1,$rsSum);
							$formula = $cal['formula'];$cal = $cal['cval'];if($cal < 0){$cal = 0;}
							$isJs = 0;
							$scoTable = 't_sale_commission_other';
						}else{
							$cal = 0;
							$formula = '';
							$isJs = 1;
							$scoTable = 't_sale_commission_other0';
						}
						$sql2 = "insert into {$scoTable} set f_serverId = '{$rs['SERVER_ID']}',f_settingId = '{$rs['ID']}',f_uid = '{$rsAgent['id']}',f_ss = '{$VOLUME}',f_calType = '{$rs['CAL_TYPE_AGENT']}',f_calNum = '{$rs['CAL_NUM_AGENT']}',f_cal = '{$cal}',f_formula = '{$formula}',f_addTime = '" . date('Y-m-d H:i:s') . "',f_isJs = '{$isJs}',f_sql = '" . $DB->escapeStr($calSql) . "'";
						//echo $sql2 , "\r\n<br>";
						$DB->query($sql2);

						$logStr .= "【" . $rsAgent['id'] . "】计算成功<br>\r\n";
					}else{
						$isJs = 1;
						$scoTable = 't_sale_commission_other0';
						$sql2 = "insert into {$scoTable} set f_serverId = '{$rs['SERVER_ID']}',f_settingId = '{$rs['ID']}',f_uid = '{$rsAgent['id']}',f_ss = '0',f_calType = '{$rs['CAL_TYPE_AGENT']}',f_calNum = '{$rs['CAL_NUM_AGENT']}',f_cal = '0',f_formula = '',f_addTime = '" . date('Y-m-d H:i:s') . "',f_isJs = '{$isJs}',f_sql = ''";
						//echo $sql2 , "\r\n<br>";
						$DB->query($sql2);

						$logStr .= "【" . $rsAgent['id'] . "】没有下级<br>\r\n";
					}
					//echo "\r\n<br>";
				}
			}//while
		}//if

		$DB->query("update t_sale_setting_other set f_isJs = 1,f_jsTime = '" . date('Y-m-d H:i:s') . "',f_jsAbout = '正常计算完成' where ID = '{$rs['ID']}'");
	}
}

//暂不支持pip
function calBounsNum_($calType,$fixVal,$scale,$order){
	//$calType=计算方式
	//$fixVal=数值
	
	$fixVal = $fixVal * 1;
	
	if ($calType == 'SCALE') {
		//%，交易量百分比
		$cval = round(($order['VOLUME'] * $fixVal / 100 * $scale) , 2);
		$formula = ($order['VOLUME']) . '*' . $fixVal . '%*' . $scale;
	} else if ($calType == 'POINT') {
		//pip，点值/每手/金额
		$cval = round(($order['VOLUME'] * $order['POINT_VALUE'] * $fixVal * $scale) , 2);
		$formula = ($order['VOLUME']) . '*' . $order['POINT_VALUE'] . '*' . $fixVal . '*' . $scale;
	} else if ($calType == 'WIN') {
		//%，盈利百分比
		$cval = round(($order['PROFIT'] * $fixVal / 100 * $scale) , 2);
		$formula = $order['PROFIT'] . '*' . $fixVal . '%*' . $scale;
	} else if($calType == 'FIXED'){
		//$，每手/金额
		$cval = round(($order['VOLUME'] * $fixVal * $scale), 2);
		$formula = ($order['VOLUME']) . '*' . $fixVal . '*' . $scale;
	}else{
		$cval = 0;
		$formula = 'ERROR:' . $calType;
	}
	
	return array('cval'=>$cval,'formula'=>$formula);
}

//---------------

$logStr = date('Y-m-d H:i:s') . "<br>\r\n";

$logStr .= "【赠金转余额】<br>\r\n";
credit_zye();

$logStr .= "<br>\r\n";
$logStr .= "【赠金到期扣回】<br>\r\n";
credit_discount();

$logStr .= "<br>\r\n";
$logStr .= "【返佣（补）】<br>\r\n";
sale_commission_other();

echo $logStr;
echo "<br>\r\n-----------------------------本次完毕";
timer_save_log();
