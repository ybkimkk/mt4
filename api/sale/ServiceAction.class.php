<?php
class ServiceAction {
    public $settings;
    public $remindsettings;
    protected $symbols;
    public $users;
    public $lessAmount = 1000;
    public $levels;
    public $levelusers;
    public $order_open_close_interval = 1;
    protected $delayMinute = 15;
    protected $direct_commission_from = 'extra';
    protected $calc_agent_self_commission = 0;
    protected $calc_commission_term = 0;
    protected $calc_remind_commission_term = 0;
    protected $task;
    protected $earnestUsers;
	public $volumePre = 100;
	public $calc_win_loss_deduct = 0;
	public $calc_win_ps_type = 0;
	
	//直客余额或者净值大于等于此金额，才接受返佣
    private function getLessAmount($serverid) {
        $this->lessAmount = 0;
        $lessamount = $this->getConfigValue('MIN_COMMISSION_BALANCE', $serverid);
        if ($lessamount) {
            $this->lessAmount = $lessamount;
        }
    }
	
    public function calcCommission() {
        $ip = FGetClientIP();
        $this->log_("访问IP:" . $ip);
		
        if (!$this->checkSecret($_REQUEST["secret"])) return;
		
        $taskId = $_REQUEST['taskId'];
        $daemon = $_REQUEST['daemon'];
        $this->calc($taskId, $daemon, $_REQUEST["secret"]);
    }
	
	//即时：把结算的数据进行汇总，增加佣金给客户
    private function settleRealTimeCommission($startcalctime, $end, $server, $status) {
		global $DB;
        unset($where);
        $where['STATUS'] = $status;
        $where['SERVER_ID'] = $server['id'];
        $where['BALANCE_TIME'] = array(
            'between',
            '' . $startcalctime . ',' . $end . ''
        );
		//时间段内结算好、未发放佣金的数据 按分组得到 用户
        $GROUPAGENTACCOUNTS = $DB->getDTable("select COMM_MEMBER_ID,sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where) . ' group by COMM_MEMBER_ID');
        $this->log_('开始结算，时间(' . $startcalctime . ',' . $end . ')' . '数据量' . count($GROUPAGENTACCOUNTS) . ' status:' . $status);
        foreach ($GROUPAGENTACCOUNTS as $key => $val) {
            $where['COMM_MEMBER_ID'] = $val["COMM_MEMBER_ID"];
            //$TOTAL = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where));
			$TOTAL = floatval($val["sum1"]);
			{
                if ($TOTAL <= 0) {
                    $this->log_("增加" . $val["COMM_MEMBER_ID"] . "本次返佣汇总批次，总额为0，无需添加到批次中!");
                    continue;
                }
				
				//记录返佣金额
                $balance['AMOUNT'] = $TOTAL;
                $balance['MEMBER_ID'] = $val["COMM_MEMBER_ID"];
                $balance['CREATE_TIME'] = $end;
                $balance['TYPE'] = "0";
                $balance['MEMO'] = "批量推荐返佣";
                $balance['SERVER_ID'] = $server['id'];
                $mt4time = time() + 3600 * floatval(8 - $server['time_zone']);
                $balance['MT4_TIME'] = $mt4time;
                $BALANCE_ID = $DB->insert("t_sale_commission_balance",$balance);
                if ($BALANCE_ID > 0) {
                    $this->log_("成功增加返佣汇总批次，账户" . $val["COMM_MEMBER_ID"] . "总返佣" . $TOTAL);
                } else {
                    $this->log_("增加返佣汇总批次失败，账户" . $val["COMM_MEMBER_ID"] . "总返佣" . $TOTAL);
                }
				
				//更新佣金数据已发放
                $batchdata['STATUS'] = '1';
                $batchdata['BALANCE_ID'] = $BALANCE_ID;
                $batchdata['BALANCE_TIME'] = time();
                $batchdata['MT4_TIME'] = $mt4time;
                $line = $DB->update("t_sale_commission",$batchdata,cz_where_to_str($where));
                if ($line > 0) {
                    $this->log_("批次已经更新，已更新，批次ID=" . $BALANCE_ID);
                } else {
                    $this->log_("--->批次已经更新，更新失败，批次ID=" . $BALANCE_ID);
                    if ($BALANCE_ID > 0) {
                        $ret = $DB->query("update t_sale_commission_balance set TYPE = -1 where ID = '{$BALANCE_ID}'");
                        $this->log_("===>空记录批次ID=" . $BALANCE_ID . "异常更新，更新结果 ： " . $ret);
                    }
                    continue;
                }
				
				//佣金是否加进mt帐号
                $retval = false;
				
				//佣金如果加进mt帐号，执行
                $config2mt4 = $this->getConfigValue('AUTO_2_MT4', $server['id']);
                $this->log_("配置了AUTO_2_MT4参数,值为：" . $config2mt4);
                if ($config2mt4 == '1') $retval = $this->balance2MT4($TOTAL, $val["COMM_MEMBER_ID"], $BALANCE_ID, $server['id'], $server['time_zone']);
				
				//如果佣金没有加进mt帐号，加进crm余额
                if (!$retval) {
                    $mwhere['id'] = $val["COMM_MEMBER_ID"];
                    $mwhere['server_id'] = $server['id'];
                    $memberuser = $DB->getDRow("select * from t_member " . cz_where_to_str($mwhere));
                    if ($memberuser) {
                        $amount = $memberuser['amount'] + floatval($TOTAL);
                        $dbmap['amount'] = $amount;
                        $retmember = $DB->update("t_member",$dbmap,cz_where_to_str($mwhere));
                        if ($retmember > 0) {
                            $this->log_("默认入金失败，用户" . $mwhere['id'] . "余额已经更新，余额=" . $amount);
                        } else {
                            $this->log_("默认入金失败，用户" . $mwhere['id'] . "更新未提现余额失败，余额=" . $amount);
                        }
                    }
                }
            }
        }
    }
	
	//日、周、月结：把结算的数据进行汇总，增加佣金给客户
    private function settleRegularTimeCommission($startcalctime, $end, $server, $status) {
		global $DB;
        unset($where);
        $where['STATUS'] = $status;
        $where['SERVER_ID'] = $server['id'];
        $where['CLOSE_TIME'] = array(
            'between',
            '' . $startcalctime . ',' . $end . ''
        );
        $GROUPAGENTACCOUNTS = $DB->getDTable("select COMM_MEMBER_ID,sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where) . ' group by COMM_MEMBER_ID');
        $this->log_('开始结算，时间(' . $startcalctime . ',' . $end . ')' . '数据量' . count($GROUPAGENTACCOUNTS) . ' status:' . $status);
        foreach ($GROUPAGENTACCOUNTS as $key => $val) {
            $where['COMM_MEMBER_ID'] = $val["COMM_MEMBER_ID"];
            //$TOTAL = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where));
			$TOTAL = floatval($val["sum1"]);
			{
                if ($TOTAL <= 0) {
                    $this->log_("增加" . $val["COMM_MEMBER_ID"] . "本次返佣汇总批次，总额为0，无需添加到批次中!");
                    continue;
                }
                $balance['AMOUNT'] = $TOTAL;
                $balance['MEMBER_ID'] = $val["COMM_MEMBER_ID"];
                $balance['CREATE_TIME'] = time();
                $balance['TYPE'] = "0";
                $balance['MEMO'] = "批量推荐返佣";
                $balance['SERVER_ID'] = $server['id'];
                $mt4time = time() + 3600 * floatval(8 - $server['time_zone']);
                $balance['MT4_TIME'] = $mt4time;
                $BALANCE_ID = $DB->insert("t_sale_commission_balance",$balance);
				
                if ($BALANCE_ID > 0) {
                    $this->log_("成功增加返佣汇总批次，账户" . $val["COMM_MEMBER_ID"] . "总返佣" . $TOTAL);
                } else {
                    $this->log_("增加返佣汇总批次失败，账户" . $val["COMM_MEMBER_ID"] . "总返佣" . $TOTAL);
                }
                $batchdata['STATUS'] = '1';
                $batchdata['BALANCE_ID'] = $BALANCE_ID;
                $batchdata['BALANCE_TIME'] = time();
                $batchdata['MT4_TIME'] = $mt4time;
                $line = $DB->update("t_sale_commission",$batchdata,cz_where_to_str($where));
				
                if ($line > 0) {
                    $this->log_("批次已经更新，已更新，批次ID=" . $BALANCE_ID);
                } else {
                    $this->log_("--->批次已经更新，更新失败，批次ID=" . $BALANCE_ID);
                    if ($BALANCE_ID > 0) {
                        $ret = $DB->query("update t_sale_commission_balance set `TYPE` = -1 where ID = '{$BALANCE_ID}'");
                        $this->log_("===>空记录批次ID=" . $BALANCE_ID . "异常更新，更新结果 ： " . $ret);
                    }
                    continue;
                }
				
                $retval = false;
                $config2mt4 = $this->getConfigValue('AUTO_2_MT4', $server['id']);
                $this->log_("配置了AUTO_2_MT4参数,值为：" . $config2mt4);
                if ($config2mt4 == '1') $retval = $this->balance2MT4($TOTAL, $val["COMM_MEMBER_ID"], $BALANCE_ID, $server['id'], $server['time_zone']);
                if (!$retval) {
                    $mwhere['id'] = $val["COMM_MEMBER_ID"];
                    $mwhere['server_id'] = $server['id'];
                    $memberuser = $DB->getDRow("select * from t_member " . cz_where_to_str($mwhere));
                    if ($memberuser) {
                        $amount = $memberuser['amount'] + floatval($TOTAL);
                        $dbmap['amount'] = $amount;
                        $retmember = $DB->update("t_member",$dbmap,cz_where_to_str($mwhere));
                        if ($retmember > 0) {
                            $this->log_("默认入金失败，用户" . $mwhere['id'] . "余额已经更新，余额=" . $amount);
                        } else {
                            $this->log_("默认入金失败，用户" . $mwhere['id'] . "更新未提现余额失败，余额=" . $amount);
                        }
                    }
                }
            }
        }
    }
	
    private function calc($taskId, $daemon) {
		global $DB;
        ignore_user_abort(true);
        set_time_limit(0);
		
        $start = $this->startTask($taskId, $daemon);
        if ($start == '-1' || $start == '-2') {
            $this->log_("守护线程发现任务正在运行，退出本次守护！");
            exit();
        } elseif ($start == NULL) {
            $this->log_("任务" . $taskId . "不存在！");
            exit();
        } elseif ($start == '0') {
            $this->log_("任务" . $taskId . "更新失败！");
            exit();
        } elseif ($start['Running'] == '-1') {
            $this->log_("任务id:" . $taskId . ' name:' . $start['TaskName'] . "处于停止状态！");
            exit();
        } elseif ($start['Status'] == '0') {
            $this->log_("任务id:" . $taskId . ' name:' . $start['TaskName'] . "处于禁用状态！");
            exit();
        }
		
        if ($start['Running'] == '0') {
            $this->log_("任务id:" . $taskId . ' name:' . $start['TaskName'] . "停止成功！");
            $data['Running'] = '-1';
            $ret = $DB->update("t_task",$data,"where Id = '{$taskId}'");
            exit();
        }
		
        $this->task = $start;
        $servers = $DB->getDTable("select * from t_mt4_server where status = 1");
        if (count($servers) < 1) {
            $this->log_('无可用MT4服务器数，返佣停止!');
            return;
        }
		
        $this->setSingle($taskId, $start['IntervalTime']);
        $this->log_("----------------------返佣计划开始-----------------------------------");
        $this->settings = $this->queryAllSymbolSetting();//内佣、外佣
        $this->remindsettings = $this->queryRemindSymbolSetting();//推荐返佣
        $this->earnestUsers = $this->queryAllEarnest();//对冲会员？
		//遍历所有mt服务器
        foreach ($servers as $keysvr => $server) {
			if($server['ver'] == 5){
				$this->volumePre = 10000;
			}else{
				$this->volumePre = 100;
			}
			
            //是否启用返佣
            $open_commission = $this->getConfigValue('OPEN_COMMISSION', $server['id']);
            if ($open_commission != '1') {
                $this->log_("MT4服务MANAGE " . $server['id'] . $server['mt4_name'] . '未开启返佣，忽略');
                continue;
            }
			
            $this->order_open_close_interval = 0;
			
			//分销模式返佣金额来源
            $this->direct_commission_from = 'extra';
            $this->log_("------开始计算MT4服务器" . $server['id'] . $server['mt4_name'] . '的返佣！------');
			
			//开仓平仓至少间隔
            $OPEN_CLOSE_INTERVAL = $this->getConfigValue('OPEN_CLOSE_INTERVAL', $server['id']);
            if ($OPEN_CLOSE_INTERVAL) {
                $this->order_open_close_interval = floatval($OPEN_CLOSE_INTERVAL);
            }
            $this->log_("开仓平仓时间间隔最少为" . $this->order_open_close_interval . '，为有效订单！');
			
			//分销模式返佣金额来源
            $DIRECT_COMMISSION_FROM = $this->getConfigValue('DIRECT_COMMISSION_FROM', $server['id']);
            if ($DIRECT_COMMISSION_FROM) {
                $this->direct_commission_from = $DIRECT_COMMISSION_FROM;
            }
            $this->log_("分销模式返佣金额来源为：" . $this->direct_commission_from . '，extra：额外的返佣，agent:返佣金额来自上级代理商');
			
			//直客余额或者净值大于等于此金额，才接受返佣
            $this->getLessAmount($server['id']);
            $this->log_("配置MIN_COMMISSION_BALANCE,默认为0， 分销用户接受返佣的最小余额或者净值,本次为：" . $this->lessAmount);
			
			//代理接受自己返佣
            $this->calc_agent_self_commission = 0;
            $calc_agent_self_commission = $this->getConfigValue('CALC_AGNET_SELF_COMMISSION', $server['id']);
            if ($calc_agent_self_commission) {
                $this->calc_agent_self_commission = floatval($calc_agent_self_commission);
            }
            $this->log_("代理接受自己返佣（0：不接受，1：接受）：" . $this->calc_agent_self_commission);
			
			//非推荐返佣结算周期
            $this->calc_commission_term = 0;
            $calc_commission_term = $this->getConfigValue('CALC_COMMISSION_TERM', $server['id']);
            if ($calc_commission_term != NULL) {
                $this->calc_commission_term = $calc_commission_term;
            }
            $this->log_("返佣结算周期（0：实时，1：日结，2：周结，3：月结）：" . $this->calc_commission_term);
			
			//推荐返佣结算周期
            $this->calc_remind_commission_term = 0;
            $remind_calc_commission_term = $this->getConfigValue('CALC_REMIND_COMMISSION_TERM', $server['id']);
            if ($remind_calc_commission_term != NULL) {
                $this->calc_remind_commission_term = $remind_calc_commission_term;
            }
            $this->log_("推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）：" . $this->calc_remind_commission_term);
			
			//返佣最后时间
            $configtime = $this->getConfigValue('CALC_LAST_TIME', $server['id']);
            if ($configtime) {
                $startcalctime = $configtime;
            } else {
                $startcalctime = time();
            }
			//结算：最后返佣时间的前15分钟开始的订单
            $startcalctime = $startcalctime - $this->delayMinute * 60;
			//结算到当前时间为止
            $last_time = time();
			//加上时区换算时间
            $scalccommtime = gmdate("Y-m-d H:i:s", $startcalctime + 3600 * floatval($server['time_zone']));
            $ecalccommtime = gmdate("Y-m-d H:i:s", $last_time + 3600 * floatval($server['time_zone']));
			
			//货币对
            $this->symbols = $DB->getDTable("select symbol.*, type.type_name,svr.mt4_name,type.type as mttype from t_symbol symbol, t_type type,t_mt4_server svr where symbol.type = type.id and type.server_id = svr.id and svr.id=" . $server['id'] . " and  svr.status=1 and type.status=1 order by type.type desc");
            if (!$this->symbols) {
                $this->log_("货币对为空，忽略" . $server['mt4_name'] . ' mt4的返佣！');
                continue;
            }
			
			//读取交易订单来源数据库名
            $dbName = $server['db_name'];
            if (!$dbName) {
                $this->log_("服务器" . $server["mt4_name"] . "无配置mt4数据库，忽略");
                continue;
            }			
			$GLOBALS['deposit_mt4dbname'] = $dbName;
			$GLOBALS['deposit_mt4dbver'] = $server['ver'];
			
            $this->log_("返佣开始GMT时间：" . $scalccommtime . " 结束：" . $ecalccommtime . " 时区：GMT " . $server['time_zone']);
			
            $reportModel = new ReportModel($dbName);
			$depositModel = NULL;
			
			//返佣组
            $array_groups = $this->combineGroups($server['id']);
            $this->log_("返佣组：" . json_encode($array_groups));			
			
			//按盈利返佣（亏损时扣除代理）
            $this->calc_win_loss_deduct = intval($this->getConfigValue('CALC_WIN_LOSS_DEDUCT', $server['id']));
            $this->log_("按盈利返佣（亏损时扣除代理）（0：否，1：是）：" . $this->calc_win_loss_deduct);
			//按盈利返佣（分润自客户）
            $this->calc_win_ps_type = intval($this->getConfigValue('CALC_WIN_PS_TYPE', $server['id']));
            $this->log_("按盈利返佣（分润自客户）（0：否，1：是）：" . $this->calc_win_ps_type);
			
			//遍历所有用户
            $this->users = $DB->getDTable("select * from t_member where server_id = '{$server['id']}' and status = 1");
            foreach ($this->users as $key1 => $user) {
				//获取对应的所有mt帐户
                $mtusers = $DB->getDTable("select * from t_member_mtlogin where member_id = '{$user['id']}' and mtserver = '{$server['id']}' and status = 1");
                $this->setSingle($taskId, $start['IntervalTime']);
                foreach ($mtusers as $key => $val) {
					//获取mt数据库中的mt用户数据
                    $mt4user = getuser($val["loginid"]);
                    if (!$mt4user) {
                        $this->log_("用户" . $val["loginid"] . "，mt4中不存在");
                        continue;
                    }
					
					//获取该用户的 时间段内 所有 已平仓订单
                    $orders = $reportModel->queryClosedOrders($val["loginid"], "", $scalccommtime, $ecalccommtime, $server['ver']);
                    $this->setSingle($taskId, $start['IntervalTime']);
                    $this->log_($val["loginid"] . "订单：" . count($orders));
					
					//遍历订单
                    foreach ($orders as $k => $order) {
                        $this->setSingle($taskId, $start['IntervalTime']);
						//如果已经结算过
                        $retticket = $DB->getDRow("select * from t_sale_commission where TICKET = '{$order['TICKET']}' and SERVER_ID = '{$server['id']}'");
                        if ($retticket) {
                            $this->log_("该订单" . $order['TICKET'] . '不能重复返佣.');
                            continue;
                        }
						
                        $order['member_id'] = $val['member_id'];
                        $opentime = strtotime($order['OPEN_TIME']);
                        $closetime = strtotime($order['CLOSE_TIME']);
						//如果是投机行为
                        if (($closetime - $opentime) < $this->order_open_close_interval) {
                            $unnormaldata['TICKET'] = $order['TICKET'];
                            $unnormaldata['LOGIN'] = $order['LOGIN'];
                            $unnormaldata['BALANCE_TIME'] = time();
                            $unnormaldata['MEMBER_ID'] = $order['member_id'];
                            $unnormaldata['COMM_MEMBER_ID'] = $order['member_id'];
                            $unnormaldata['VOLUME'] = $order['VOLUME'];
                            $unnormaldata['STATUS'] = "0";
                            $unnormaldata['SERVER_ID'] = $server['id'];
                            $unnormaldata['MEMO'] = "出入场间隔不足" . $this->order_open_close_interval . "秒";
                            $unnormaldata['AMOUNT'] = 0;
                            $unnormaldata['STATUS'] = - 1;
                            $unnormalrest = $DB->insert("t_sale_commission",$unnormaldata);
							
                            $this->log_($order['TICKET'] . "，" . $order['OPEN_TIME'] . "开仓," . $order['CLOSE_TIME'] . "平仓,间隔不足" . $this->order_open_close_interval . '出现平仓，鉴定可能是投机行为，忽略！保存状态：' . $unnormalrest);
                            continue;
                        }
						//获取点值
                        $pointValue = $this->getPointValue($order, $server['ver']);
                        $order['POINT_VALUE'] = $pointValue;
						//结算这笔订单
                        $this->calcOne($order, $user, 0, NULL, $mt4user, $depositModel, $server['time_zone'], 0, 0, 'direct', $server['id'], 1000, 0);
                    }
                }
            }
			
			//更新最后一次的返佣时间，下次计算返佣就从这个时间开始
            $ret = $this->saveConfigValue('CALC_LAST_TIME', $server['id'], $last_time);
            if (false === $ret) {
                $this->log_("更新时间" . $last_time . "失败！");
            } else {
                $this->log_($server['mt4_name'] . "更新上次返佣时间为" . $last_time . "！");
            }
			
			//非推荐佣金上次结算时间,代表上一个结算周日执行的具体时间，不能随意改动
            $SETTLE_LAST_TIME = $this->getConfigValue('SETTLE_LAST_TIME', $server['id']);
			//推荐返佣结算最后一次时间,代表上一个结算周日执行的具体时间，不能随意改动
            $SETTLE_REMIND_LAST_TIME = $this->getConfigValue('SETTLE_REMIND_LAST_TIME', $server['id']);
			//非推荐佣金结算日,代表下一个结算周期后的天数，比如1:日结代表当天后的1天，周结代表周六后的1天，月结代表下这个月后的一天，即下个月1号
            $SETTLE_LAST_DAY = $this->getConfigValue('SETTLE_LAST_DAY', $server['id']);
			//推荐返佣结算日,代表下一个结算周期后的天数，比如1:日结代表当天后的1天，周结代表周六后的1天，月结代表下这个月后的一天，即下个月1号
            $SETTLE_REMIND_LAST_DAY = $this->getConfigValue('SETTLE_REMIND_LAST_DAY', $server['id']);
			//非推荐佣金结算时间点,代表在几点结算，只能配置1-24小时，北京时间执行，请根据自己的MT时区设置，建议为8-9点
            $CALC_COMMISSION_TERM_VAL = $this->getConfigValue('CALC_COMMISSION_TERM_VAL', $server['id']);
			//推荐返佣结算时间点,代表在几点结算，只能配置1-24小时，北京时间执行，请根据自己的MT时区设置，建议为8-9点
            $CALC_REMIND_COMMISSION_TERM_VAL = $this->getConfigValue('CALC_REMIND_COMM_TERM_VAL', $server['id']);
			
            $continue_calc = true;
			//非推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
            if ($this->calc_commission_term == '1') {
                $year = date("Y", $SETTLE_LAST_TIME);
                $month = date("m", $SETTLE_LAST_TIME);
                $day = date("d", $SETTLE_LAST_TIME);
                $nextcalctime = mktime($CALC_COMMISSION_TERM_VAL, 0, 0, $month, $day + 1, $year);
                $now = time();
                if ($now < $nextcalctime) {
                    $this->log_("日结佣时间未到：next" . date("Y-m-d H:i", $nextcalctime) . " real" . date("Y-m-d H:i", $now));
                    $continue_calc = false;
                } else {
                    $starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , $day, date('Y')));
                    $endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , $day, date('Y')));
                    $this->log_("开始日结 " . $starttime . "-" . $endtime);
                }
            } else if ($this->calc_commission_term == '2') {
                $year = date("Y", $SETTLE_LAST_TIME);
                $month = date("m", $SETTLE_LAST_TIME);
                $day = date("d", $SETTLE_LAST_TIME);
                $week = date("w", $SETTLE_LAST_TIME);
                $nextcalctime = mktime($CALC_COMMISSION_TERM_VAL, 0, 0, $month, $day - $week + 6 + $SETTLE_LAST_DAY, $year);
                $now = time();
                if ($now < $nextcalctime) {
                    $this->log_("周结佣时间未到：next" . date("Y-m-d H:i", $nextcalctime) . " real" . date("Y-m-d H:i", $now));
                    $continue_calc = false;
                } else {
                    $starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - date('w') - 6, date('y')));
                    $endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - date('w') , date('y')));
                    $this->log_("开始周结 " . $starttime . "-" . $endtime);
                }
            } else if ($this->calc_commission_term == '3') {
                $year = date("Y", $SETTLE_LAST_TIME);
                $month = date("m", $SETTLE_LAST_TIME);
                $day = date("d", $SETTLE_LAST_TIME);
                $nextcalctime = mktime($CALC_COMMISSION_TERM_VAL, 0, 0, $month + 1, $day + $SETTLE_LAST_DAY - 1, $year);
                $now = time();
                if ($now < $nextcalctime) {
                    $this->log_("月结佣时间未到：next" . date("Y-m-d H:i", $nextcalctime) . " real" . date("Y-m-d H:i", $now));
                    $continue_calc = false;
                } else {
                    $starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') - 1, 1, date('y')));
                    $endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , 0, date('y')));
					$this->log_("开始月结 " . $starttime . "-" . $endtime);
                }
            }
            $end = time();
			//发放 非推荐返佣 佣金
            if ($continue_calc) {
                if ($this->calc_commission_term == 0) $this->settleRealTimeCommission($startcalctime, $end, $server, "0");
                else $this->settleRegularTimeCommission($starttime, $endtime, $server, "-2");
                $ret = $this->saveConfigValue('SETTLE_LAST_TIME', $server['id'], $last_time);
                if (false === $ret) {
                    $this->log_("更新时间" . $last_time . "失败！");
                } else {
                    $this->log_($server['mt4_name'] . "更新上次内外返佣时间为" . $last_time . "！");
                }
            }
			
			//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
            if ($this->calc_remind_commission_term == '1') {
                $year = date("Y", $SETTLE_REMIND_LAST_TIME);
                $month = date("m", $SETTLE_REMIND_LAST_TIME);
                $day = date("d", $SETTLE_REMIND_LAST_TIME);
                $remind_nextcalctime = mktime($CALC_REMIND_COMMISSION_TERM_VAL, 0, 0, $month, $day + 1, $year);
                $remind_now = time();
                $this->log_($SETTLE_REMIND_LAST_TIME . "日结推荐返佣时间对比：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                if ($remind_now < $remind_nextcalctime) {
                    $this->log_("日结推荐返佣时间未到：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                    continue;
                }
                $remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , $day, date('Y')));
                $remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , $day, date('Y')));
                $this->log_("开始日结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
            } else if ($this->calc_remind_commission_term == '2') {
                $year = date("Y", $SETTLE_REMIND_LAST_TIME);
                $month = date("m", $SETTLE_REMIND_LAST_TIME);
                $day = date("d", $SETTLE_REMIND_LAST_TIME);
                $week = date("w", $SETTLE_REMIND_LAST_TIME);
                $remind_nextcalctime = mktime($CALC_REMIND_COMMISSION_TERM_VAL, 0, 0, $month, $day - $week + 6 + $SETTLE_REMIND_LAST_DAY, $year);
                $remind_now = time();
                if ($remind_now < $remind_nextcalctime) {
                    $this->log_("周结推荐返佣时间未到：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                    continue;
                }
                $remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - date('w') - 6, date('y')));
                $remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - date('w') , date('y')));
                $this->log_("开始周结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
            } else if ($this->calc_remind_commission_term == '3') {
                $year = date("Y", $SETTLE_REMIND_LAST_TIME);
                $month = date("m", $SETTLE_REMIND_LAST_TIME);
                $day = date("d", $SETTLE_REMIND_LAST_TIME);
                $remind_nextcalctime = mktime($CALC_REMIND_COMMISSION_TERM_VAL, 0, 0, $month + 1, $day + $SETTLE_REMIND_LAST_DAY - 1, $year);
                $remind_now = time();
                if ($remind_now < $remind_nextcalctime) {
                    $this->log_("月结推荐返佣时间未到：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                    continue;
                }
                $remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') - 1, 1, date('y')));
                $remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , 0, date('y')));
                $this->log_("开始月结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
            }
            if ($this->calc_remind_commission_term != 0) $this->settleRegularTimeCommission($remind_starttime, $remind_endtime, $server, "-3");
            $ret = $this->saveConfigValue('SETTLE_REMIND_LAST_TIME', $server['id'], $last_time);
            if (false === $ret) {
                $this->log_("更新时间" . $last_time . "失败！");
            } else {
                $this->log_($server['mt4_name'] . "更新上次推荐返佣时间为" . $last_time . "！");
            }
        }
        $this->log_("----------------------本次返佣计划结束-----------------------------------");
        $this->clearSingle($taskId);
    }
	
    private function calcOne($data, $user, $inlevel, $presetting, $mt4user, $depositModel, $time_zone, $precommissionVal, $standerd_direct_total_value, $precomm_type, $svr_id, $min_level, $agent_remind_level) {
		global $DB;
        $this->setSingle($this->task['Id'], $this->task['IntervalTime']);
		
		//没有上级、首次遍历、代理商不允许拿自己的返佣，退出
        if (($user['parent_id'] == '0' || $user['parent_id'] == '1') && $this->calc_agent_self_commission == 0 && $inlevel == 0) {
            $this->log_("订单" . $data['TICKET'] . ',账户' . $user['nickname'] . " email:" . $user['email'] . "为顶级，本订单返佣结束！");
            return;
        }
		
		//返给谁
        if ($inlevel == 0 && $this->calc_agent_self_commission == 1 && $user['userType'] == 'agent') {
            $this->log_("代理计算自己交易返佣给自己");
            $parentuser = $user;
        }else if ($inlevel == 0 && $user['userType'] == 'direct') {
			//cz2021.09.08增加：直客可以返佣给自己
            $this->log_("直客计算自己交易返佣给自己");
            $parentuser = $user;
        } else {
            $parentuser = $DB->getDRow("select * from t_member where id = '{$user['parent_id']}' and server_id = '{$svr_id}'");
        }
		
        if ($parentuser) {
            if ($user['id'] == $parentuser['id'] && $inlevel > 0 && $this->calc_agent_self_commission == 0) {
                $this->log_("订单" . $data['TICKET'] . ',账户' . $user['nickname'] . "上级为自己，造成死循环，本订单返佣结束，请调整后，再次进行返佣！");
                return;
            }
			
            $this->log_("获得返佣的账户信息：" . $parentuser['nickname'] . ' email:' . $parentuser['email']);
			
            $minlevel = min($user['level'], $min_level);
            $arrobj = $this->calcCommValue($data, $presetting, $parentuser, $user, $inlevel, $mt4user, $time_zone, $precommissionVal, $standerd_direct_total_value, $precomm_type, $svr_id, $depositModel, $min_level, $agent_remind_level);
            
			//如果跳出循环
			if($arrobj === false){
				return;
			}
			
			$setting = $arrobj['setting'];
            $precomm_type = $arrobj['pre_comm_type'];
            $precommissionVal = $arrobj['commval'];
            $commissionLevel = $arrobj['level'];
            $min_level = $arrobj['min_level'];
            $inlevel = $arrobj['inlevel'];
            $agent_remind_level = $arrobj['agent_remind_level'];
        } else {
            $this->log_("订单" . $data['TICKET'] . "给用户" . $user['parent_id'] . "，不存在，忽略返佣 ");
            return;
        }
        $this->log_("订单" . $data['TICKET'] . "给用户" . $user['parent_id'] . " 返佣完成---------------");
		
		//未变参数：
		//$data = 订单数据（数组）
		//$mt4user = 提供佣金的mt4帐号数据（数组）
		//$depositModel = NULL（原来是mt的操作类，目前去除）
		//$time_zone = mt服务器的time_zone
		//$svr_id = mt服id
		
		//已变参数：
		//$parentuser
		//$inlevel
		//$setting
		//$precommissionVal
		//$standerd_direct_total_value
		//$precomm_type
		//$min_level
		//$agent_remind_level
        return $this->calcOne($data, $parentuser, $inlevel, $setting, $mt4user, $depositModel, $time_zone, $precommissionVal, $arrobj['stand_total'], $precomm_type, $svr_id, $min_level, $agent_remind_level);
    }
	
    private function balance2MT4($amount, $memberid, $balanceId, $serverid, $timezone) {
		global $DB;
        if (!is_numeric($amount)) {
            $this->log_("提现金额输入有误！" . $amount);
            return false;
        }

        $server = $DB->getDRow("select * from t_mt4_server where id = '{$serverid}'");
        if (!$server) {
            $this->log_("mt4服务器不存在！" . $serverid);
            return false;
        }
        $mtloginwhere['member_id'] = $memberid;
        $mtloginwhere['status'] = '1';
        $mtloginwhere['mt_type'] = '0';
        $mtusers = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str($mtloginwhere));
        if (!$mtusers) {
            $this->log_("佣金帐户不存在" . $memberid);
            return false;
        }
        $balance['AMOUNT'] = - $amount;
        $balance['MEMBER_ID'] = $memberid;
        $balance['CREATE_TIME'] = time();
        $balance['TYPE'] = "1";
        $balance['REL_ID'] = $balanceId;
        $balance['LOGIN'] = $mtusers['loginid'];
        $balance['IN_TIME'] = time();
        $balance['SERVER_ID'] = $serverid;
        $mt4time = time() + 3600 * floatval(8 - $server['time_zone']);
        $balance['MT4_TIME'] = $mt4time;
        $BALANCE_ID = $DB->insert("t_sale_commission_balance",$balance);
		
        if ($BALANCE_ID > 0) {
            $this->log_($server['mt4_server'] . ' manager' . $server['mt4_manager']);
            $inid = - 1;
            try {
                $mt4api = new MtApiModel($server['ver']);
                $server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
                $retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
                if ($retarry['ret'] != '0') {
                    $this->log_($retarry['info']);
                    return false;
                }
                $this->log_('开始给' . $mtusers['loginid'] . '返佣' . $amount . '美金');
                $inid = $mt4api->balance($mtusers['loginid'], $amount, "Commission maxib#" . $BALANCE_ID, 1);
            }
            catch(Exception $e) {
                $mess = iconv("GB2312", "UTF-8", $e->getMessage());
                $this->log_("连接mt4api接口异常,错误信息：" . $mess);
                return false;
            }
            if ($inid['ret'] < 0 && $inid['ret'] != '-88') {
                $this->log_("连接mt4api接口异常,MT4服务器入金失败,错误代码" . $inid['ret'] . ' msg:' . $inid['info']);
                return false;
            } else {
                $bwhere['ID'] = $BALANCE_ID;
                $bdata['IN_ID'] = $inid['ret'];
                $bdata['MEMO'] = 'MT订单#' . $inid['ret'] . ' 帐户信息#' . $mtusers['loginid'] . '@' . $server['mt4_name'];
                $bret = $DB->update("t_sale_commission_balance",$bdata,cz_where_to_str($bwhere));
                if ($inid['ret'] == '-88') {
                    $this->log_("重复提现！" . $inid['ret'] . ' msg:' . $inid['info']);
                    return true;
                }
                $this->log_("提现成功！" . $inid['ret']);
                return true;
            }
        }
    }
	
    private function calcCommValue($data, $presetting, $parentuser, $user, $inlevel, $mt4user, $time_zone, $commissionVal, $standerd_direct_total_value, $precomm_type, $svr_id, $depositModel, $min_level, $agent_remind_level) {
		global $DB;
		
		//中断：上级非代理，下级为代理的情况下
		if($inlevel > 0 && $parentuser['userType'] != 'agent' && $user['userType'] == 'agent'){
			 return false;
		}
		if($inlevel > 0 && $parentuser['userType'] != 'member' && $user['userType'] == 'member'){
			 return false;
		}
		
		//$data = 订单数据（数组）
		//$presetting = 
		//$parentuser = 获得佣金的客户（数组）
		//$user = $parentuser的下级
		//$inlevel = 当前在第几层（从下往上0、1、2、3……）
		//$mt4user = 提供佣金的mt4帐号数据（数组）
		//$time_zone = mt服务器的time_zone
		//$commissionVal = 
		//$standerd_direct_total_value = 
		//$precomm_type = 下级的客户类型（代理、直客、员工）
		//$svr_id = mt服id
		//$depositModel = NULL（原来是mt的操作类，目前去除）
		//$min_level = 
		//$agent_remind_level = (默认0)
		
        $commdata['TICKET'] = $data['TICKET'];
        $commdata['LOGIN'] = $data['LOGIN'];
        $commdata['BALANCE_TIME'] = time();
        $commdata['MEMBER_ID'] = $data['member_id'];
        $commdata['VOLUME'] = $data['VOLUME'];
        $commdata['STATUS'] = "0";
        $commdata['SERVER_ID'] = $svr_id;
        $commdata['CLOSE_TIME'] = $data['CLOSE_TIME'];
        $commdata['COMM_MEMBER_ID'] = $parentuser['id'];
        $commdata['CREATE_TIME'] = time();
		
        $comm_name = $parentuser['nickname'] == '' ? $parentuser['chineseName'] : $parentuser['nickname'];
        $commission_level = $inlevel;
        $closetime = strtotime($data['CLOSE_TIME']);
        $commissionLevel = $parentuser['level'];
        $iscurrentdirect = false;
        if ($parentuser['userType'] == 'direct') {
            $commissionLevel = $inlevel;
            $iscurrentdirect = true;
            $this->log_("直客订单返佣给" . $parentuser['member_id'] . ",返佣等级" . $commissionLevel);
        }
        $inlevel++;
        unset($settings);
        unset($inersetting);
		//佣金配置：$data['SYMBOL']订单产品、$commissionLevel获得佣金者等级（直客按层级，订单所有者的上家为第1级）、$parentuser['userType']获得佣金者类型（代理、直客、员工）、$mt4user['GROUP']提供佣金者所在组
        $settings = $this->querySymbolSetting($data['SYMBOL'], $parentuser['id'], $closetime, $commissionLevel, $parentuser['userType'], $mt4user['GROUP'], $svr_id, $time_zone);
        if ($parentuser['userType'] == 'agent' && $precomm_type == "agent") {
            $this->log_("remind——level" . $agent_remind_level);
            $agent_remind_level++;
        }
        if ($parentuser['userType'] != 'member' && $min_level <= $parentuser['level'] && $precomm_type == "agent") {
            if (!$this->checkInnerRemind($settings)) {
                $inersetting = $this->getInnerCommissionSetting($settings);
                if (empty($innersetting)) $inersetting = $presetting;
                $this->log_("remind type,but innersetting " . json_encode($innersetting));
                $settings = $this->querySymbolRemindSetting($data['SYMBOL'], $parentuser['id'], $closetime, $agent_remind_level, $parentuser['userType'], $mt4user['GROUP'], $svr_id, $time_zone);
            }
        }
        $innersetting = array();
        $inner_cval = 0;
        $inner_status = 0;
        $inner_pre_comm_type = $parentuser['userType'];
        $this->log_("发现佣金配置" . count($settings) . "个，" . json_encode($settings));
        if ($parentuser['userType'] == 'direct') {
            $commdata['LEVEL_NAME'] = $inlevel . '级直客';
        } else if ($parentuser['userType'] == 'agent') {
            $commdata['LEVEL_NAME'] = $parentuser['level'] . '级代理';
        } else if ($parentuser['userType'] == 'member') {
            $commdata['LEVEL_NAME'] = $parentuser['level'] . '级员工';
        }
        if (count($settings) > 0) {
            foreach ($settings as $key => $setting) {
                unset($where);
                $where['TICKET'] = $data['TICKET'];
                $where['LOGIN'] = $data['LOGIN'];
                $where['COMM_MEMBER_ID'] = $parentuser['id'];
                $where['SERVER_ID'] = $svr_id;
                if ($setting['COMMISSION_PATTERN'] == 1) $where['COMM_TYPE'] = $setting['COMM_TYPE'];
                $where['COMMISSION_PATTERN'] = $setting['COMMISSION_PATTERN'];
                $dbsale = $DB->getDRow("select * from t_sale_commission " . cz_where_to_str($where));
                $cval = 0;
                $incval = 0;
                if (!$dbsale) {
                    if ($setting != NULL) {
                        $thispattern = $setting['COMMISSION_PATTERN'];
                        $commdata['SETTING_ID'] = $setting['ID'];
                        $commdata['COMM_TYPE'] = $setting['COMM_TYPE'];
                        $commdata['LEVEL_SCALE'] = $setting['SCALE'];
                        $commdata['COMMISSION_PATTERN'] = $setting['COMMISSION_PATTERN'];
                        $rankwhere['id'] = $parentuser['rank_id'];
                        $dbrank = $DB->getDRow("select * from t_ib_rank " . cz_where_to_str($rankwhere));
                        $scale = 1;
                        if ($dbrank != NULL) {
                            $commdata['RANK_NAME'] = $dbrank['rank_name'];
                            $commdata['RANK_SCALE'] = $dbrank['scale'];
                            $scale = floatval($dbrank['scale']);
                        }
                        $commtype = "";
                        $this->log_("订单详情：接受返佣账户：" . $parentuser['userType'] . ",上级返佣类型：" . $precomm_type);
                        if ($this->checkEarnestUser($parentuser['id'], $time_zone, $data['CLOSE_TIME'])) {
                            $this->log_("该账户为对冲账户，无法返佣：" . $parentuser['nickname'] . " mid:" . $parentuser['id']);
                            $fixVal = $setting['FIXED'];
                            $commdata['MEMO'] = "对冲账户，无法返佣";
                            $commdata['AMOUNT'] = 0;
                            $commdata['LEVEL_FIXED'] = $fixVal;
                            $commdata['STATUS'] = - 1;
                            $result = $DB->insert("t_sale_commission",$commdata);
                            return array(
                                'status' => '-3',
                                'min_level' => $min_level,
                                'commval' => 0,
                                'stand_total' => $standerd_direct_total_value,
                                'setting' => $setting,
                                'pre_comm_type' => $pre_comm_type_now,
                                'level' => $commissionLevel,
                                'inlevel' => $inlevel
                            );
                        }
                        if ($parentuser['userType'] == 'direct') {
                            $fixVal = $setting['FIXED'];
                            $cval = round(($data['VOLUME'] * $fixVal * $scale) / 100, 2);
                            if ($setting['COMM_TYPE'] == 'SCALE') {
                                $cval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
                                $commtype = ($data['VOLUME'] / 100) . '*' . $fixVal . '%*' . $scale;
                            } elseif ($setting['COMM_TYPE'] == 'POINT') {
                                $cval = round(($data['VOLUME'] * $data['POINT_VALUE'] / 100 * $fixVal * $scale) , 2);
                                $commtype = ($data['VOLUME'] / 100) . '*' . $data['POINT_VALUE'] . '*' . $fixVal . '*' . $scale;
							} elseif ($setting['COMM_TYPE'] == 'WIN') {
								$cval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
                                $commtype = $data['PROFIT'] . '*' . $fixVal . '%*' . $scale;
								if($this->calc_win_loss_deduct <= 0){
									$cval = 0;
								}
                            } else {
                                $commtype = ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
                            }
                            $commdata['LEVEL_FIXED'] = $fixVal;
                            $standerd_direct_total_value = $standerd_direct_total_value + $fixVal;
                            if ($thispattern == 0) $setting = $presetting;
                            $lessamount = $this->queryLessAmount($user['parent_id'], $depositModel, $svr_id);
                            if ($lessamount['status'] != 1) {
                                $commdata['MEMO'] = $lessamount['msg'];
                                $commdata['AMOUNT'] = 0;
                                $commdata['STATUS'] = - 1;
                                $result = $DB->insert("t_sale_commission",$commdata);
                                return array(
                                    'status' => '-2',
                                    'min_level' => $min_level,
                                    'commval' => $cval,
                                    'stand_total' => $standerd_direct_total_value,
                                    'setting' => $setting,
                                    'pre_comm_type' => $pre_comm_type_now,
                                    'level' => $commissionLevel,
                                    'inlevel' => $inlevel
                                );
                            }
                        } elseif ($parentuser['userType'] == 'agent' || $parentuser['userType'] == 'member') {
							//历史最低代理等级 <= 当前上级等级，且在第1层推荐关系以上（A-B-C-D，D的交易订单至少到达B），且上下级的用户类型一致（都是代理或员工），且不是外佣
                            //if ($min_level <= $parentuser['level'] && $commission_level > 0 && $precomm_type == $parentuser['userType'] && $setting['COMMISSION_PATTERN'] != 1) {
							//改为：
							//在第1层推荐关系以上（A-B-C-D，D的交易订单至少到达B），且上下级的用户类型一致（都是代理或员工），且不是外佣
							if ($commission_level > 0 && $precomm_type == $parentuser['userType'] && $setting['COMMISSION_PATTERN'] != 1) {
                                //0:内佣，1:外佣金 2:推荐返佣
								if ($setting['COMMISSION_PATTERN'] == 0) {
									//三个级别，必有一个（从上往下优先级）：
									//越级奖励=上级代理>下级代理
									//平级奖励=上级代理<=下级代理
									//间接客户
                                    if ($parentuser['level'] <= $user['level']) {
										//平级
                                        $fixVal = floatval($setting['EQUAL_SCALE']) / 100;
                                        $commdata['LEVEL_FIXED'] = $setting['EQUAL_SCALE'];
                                        $commdata['COMM_TYPE'] = "EQUAL_SCALE";
                                        $commtype = $commissionVal . '*' . floatval($setting['EQUAL_SCALE']) . '%*' . $scale;
                                    } else {
										//越级
                                        $fixVal = floatval($setting['UP_SCALE']) / 100;
                                        $commdata['LEVEL_FIXED'] = $setting['UP_SCALE'];
                                        $commdata['COMM_TYPE'] = "UP_SCALE";
                                        $commtype = $commissionVal . '*' . floatval($setting['UP_SCALE']) . '%*' . $scale;
                                    }
                                    $cval = round(($commissionVal * $fixVal * $scale) , 2);
                                    $setting['STANDARD_FIXED'] = $presetting['FIXED'];
                                    $setting['FIXED'] = $presetting['FIXED'];
                                    $setting = $presetting;
                                    $this->log_("平级内佣." . $data['TICKET'] . " user：" . $parentuser['nickname'] . " formal " . $commtype . " val:" . $cval);
                                } else if ($setting['COMMISSION_PATTERN'] == 2) {
                                    $this->log_("推荐返佣." . $data['TICKET'] . " user" . $parentuser['nickname'] . " level:" . $agent_remind_level);
                                    $fixVal = $setting['FIXED'];
                                    $cval = round(($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
                                    $commdata['LEVEL_FIXED'] = $setting['FIXED'];
                                    $commdata['COMM_TYPE'] = $setting['COMM_TYPE'];
                                    if ($setting['COMM_TYPE'] == 'SCALE') {
                                        $commtype = ($data['VOLUME'] / 100) . '*' . $setting['FIXED'] . '%*' . $scale;
                                        $cval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
                                    } elseif ($setting['COMM_TYPE'] == 'POINT') {
                                        $commtype = $data['POINT_VALUE'] . "*" . ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
                                        $cval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
									} elseif ($setting['COMM_TYPE'] == 'WIN') {
                                        $commtype = $data['PROFIT'] . '*' . $setting['FIXED'] . '%*' . $scale;
                                        $cval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
										if($this->calc_win_loss_deduct <= 0){
											$cval = 0;
										}
                                    } else $commtype = ($data['VOLUME'] / 100) . '*' . $setting['FIXED'] . '*' . $scale;
                                    $setting['STANDARD_FIXED'] = $innersetting['FIXED'];
                                    $setting['FIXED'] = $innersetting['FIXED'];
                                    $setting = $innersetting;
                                    $this->log_("平级返佣." . $data['TICKET'] . " user：" . $parentuser['nickname'] . " formal " . $commtype . " val:" . $cval);
                                }
                            } else {
                                if ($setting['COMMISSION_PATTERN'] == 0) {
                                    if ($precomm_type == $parentuser['userType']) $min_level = min($parentuser['level'], $min_level);
                                    else $min_level = $parentuser['level'];
                                    $this->log_("处理内佣." . $data['TICKET'] . " set:" . json_encode($setting) . " pre:" . json_encode($presetting));
                                    $this->processInnerAgentCommission($setting, $presetting, $data, $commdata, $precomm_type, $cval, $standerd_direct_total_value, $fixVal, $scale, $commissionVal, $commtype);
                                } else {
                                    if ($this->checkOutCommissionRule($precomm_type, $user, $parentuser)) {
                                        $this->log_("处理外佣." . $data['TICKET'] . " set:" . json_encode($setting));
                                        $this->processOuterAgentCommission($setting, $data, $commdata, $cval, $scale, $commtype);
                                    } else {
                                        $this->log_("非直接客户，无法获得外用，忽略." . " 代理获得自己返佣" . $this->calc_agent_self_commission . ' orderuserid:' . $user['id'] . " userparent:" . $user['parent_id'] . " parentid:" . $parentuser['id']);
                                        continue;
                                    }
                                }
                            }
                        } else {
                            $this->log_("未知客户类型." . $parentuser['userType']);
                            $commdata['MEMO'] = "未知客户类型." . $parentuser['userType'];
                        }
                        $this->log_("本级计算后，" . $min_level . "级返佣标准：" . $setting['STANDARD_FIXED'] . ' 累计标准：' . $setting['FIXED'] . '公式：' . $commtype);
                        if ($cval <= 0) {
                            $commdata['AMOUNT'] = 0;
                            $commdata['STATUS'] = - 1;
                            $commdata['MEMO'] = $dbuser['nickname'] . "返佣金额<=0,忽略本订单返佣";
                            $commdata['FORMULA'] = $commtype;
                            if ($thispattern == 0) $setting = $presetting;
                            $pre_comm_type_now = $precomm_type;
                            $result = $DB->insert("t_sale_commission",$commdata);
                            $status = - 1;
                            $this->log_("订单" . $data['TICKET'] . "计算佣金," . $dbuser['nickname'] . "返佣金额为0或者负值:" . $fixVal . "，忽略本订单返佣");
                        } elseif ($cval > 0) {
                            $commdata['AMOUNT'] = $cval;
                            $commdata['FORMULA'] = $commtype;
                            $mt4time = time() + 3600 * floatval(8 - $time_zone);
                            $commdata['MT4_TIME'] = $mt4time;
                            $pre_comm_type_now = $parentuser['userType'];
                            if ($this->calc_remind_commission_term != 0 && $commdata['COMMISSION_PATTERN'] == 2) {
                                $this->log_("订单" . $data['TICKET'] . "-3");
                                $commdata['STATUS'] = - 3;
                            } else if ($this->calc_commission_term != 0 && $commdata['COMMISSION_PATTERN'] != 2) {
                                $this->log_("订单" . $data['TICKET'] . "-2");
                                $commdata['STATUS'] = - 2;
                            } else {
                                $commdata['STATUS'] = 0;
                            }
                            $status = 1;
                            $result = $DB->insert("t_sale_commission",$commdata);
                            if ($result) $this->log_("订单" . $data['TICKET'] . "计算佣金," . $dbuser['nickname'] . '返佣' . $commdata['AMOUNT'] . ",公式=" . $commdata['FORMULA'] . " status=" . $commdata['STATUS']);
                            else $this->log_("订单" . $data['TICKET'] . "计算佣金," . $dbuser['nickname'] . '返佣' . $commdata['AMOUNT'] . ",公式=" . $commdata['FORMULA'] . " 失败");
                        }
                    } else {
                        if ($thispattern == 0) $setting = $presetting;
                        $pre_comm_type_now = $precomm_type;
                        $cval = $commissionVal;
                        $commdata['AMOUNT'] = 0;
                        $commdata['STATUS'] = - 1;
                        $status = 0;
                        $commdata['MEMO'] = '账户' . $comm_name . "，无返忽略！缺少组：" . $mt4user['GROUP'] . "和" . $commissionLevel . "级" . $parentuser['userType'] . "以及当前交易种类的返佣配置";
                        $result = $DB->insert("t_sale_commission",$commdata);
                        $this->log_("订单" . $data['TICKET'] . ',账户' . $comm_name . "，无返佣配置，无法返佣，已忽略！继承上一级返佣配置");
                    }
                } else {
                    $status = 2;
                    $cval = $dbsale['amount'];
                    $this->log_("订单" . $data['TICKET'] . ' 给用户' . $where['COMM_MEMBER_ID'] . ' name:' . $comm_name . "代理，已经返佣，不能重复返佣已忽略！");
                }
                if ($thispattern == 0) {
                    $inersetting = $setting;
                    $inner_cval = $cval;
                    $inner_status = $status;
                    $inner_pre_comm_type = $pre_comm_type_now;
                    $this->log_($min_level . "级代理，传递给上级返佣." . json_encode($inersetting));
                }
            }
        } else {
            $commdata['AMOUNT'] = 0;
            $commdata['STATUS'] = - 1;
            $commdata['MEMO'] = '账户' . $comm_name . "，无返忽略！缺少组：" . $mt4user['GROUP'] . "和" . $commissionLevel . "级" . $parentuser['userType'] . "以及当前交易种类的返佣配置";
            $result = $DB->insert("t_sale_commission",$commdata);
            $this->log_("订单" . $data['TICKET'] . ',账户' . $comm_name . "，无返佣配置，无法返佣，已忽略！继承上一级返佣配置");
        }
        return array(
            'status' => $inner_status,
            'min_level' => $min_level,
            'commval' => $inner_cval,
            'stand_total' => $standerd_direct_total_value,
            'setting' => $inersetting,
            'pre_comm_type' => $inner_pre_comm_type,
            'level' => $commissionLevel,
            'inlevel' => $inlevel,
            "agent_remind_level" => $agent_remind_level
        );
    }
    
	private function checkInnerRemind($settings) {
        foreach ($settings as $key => $setting) {
            if ($setting['UP_SCALE'] > 0 || $setting['EQUAL_SCALE'] > 0) return true;
        }
        return false;
    }
	
    private function checkOutCommissionRule($precomm_type, $user, $parentuser) {
        if (($precomm_type == 'direct' && $user['userType'] == 'direct') || $user['id'] == $parentuser['id']) return true;
        return false;
    }
    
	private function getInnerCommissionSetting($settings) {
        foreach ($settings as $key => $setting) {
            if ($setting['COMMISSION_PATTERN'] == 0) return $setting;
        }
        return array();
    }
	
    private function processInnerAgentCommission(&$setting, $presetting, $data, &$commdata, $precomm_type, &$cval, $standerd_direct_total_value, &$fixVal, &$scale, &$commissionVal, &$commtype) {
        if ($precomm_type == 'direct') {
            if ($this->direct_commission_from == 'agent') {
                $fixVal = $setting['FIXED'] - $standerd_direct_total_value;
                $cval = round(($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
                if ($setting['COMM_TYPE'] == 'SCALE') {
                    $commtype = ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . $standerd_direct_total_value . ')%*' . $scale;
                    $cval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
                } elseif ($setting['COMM_TYPE'] == 'POINT') {
                    $commtype = $data['POINT_VALUE'] . "*" . ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . $standerd_direct_total_value . ')*' . $scale;
                    $cval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
				} elseif ($setting['COMM_TYPE'] == 'WIN') {
                    $commtype = $data['PROFIT'] . '*(' . $setting['FIXED'] . '-' . $standerd_direct_total_value . ')%*' . $scale;
                    $cval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
					if($this->calc_win_loss_deduct <= 0){
						$cval = 0;
					}
                } else $commtype = ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . $standerd_direct_total_value . ')*' . $scale;
            } else {
                $fixVal = $setting['FIXED'];
                $cval = round(($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
                if ($setting['COMM_TYPE'] == 'SCALE') {
                    $commtype = ($data['VOLUME'] / 100) . '*' . $setting['FIXED'] . '%*' . $scale;
                    $cval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
                } elseif ($setting['COMM_TYPE'] == 'POINT') {
                    $commtype = $data['POINT_VALUE'] . "*" . ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
                    $cval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
				} elseif ($setting['COMM_TYPE'] == 'WIN') {
                    $commtype = $data['PROFIT'] . '*' . $setting['FIXED'] . '%*' . $scale;
                    $cval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
					if($this->calc_win_loss_deduct <= 0){
						$cval = 0;
					}
                } else $commtype = ($data['VOLUME'] / 100) . '*' . $setting['FIXED'] . '*' . $scale;
            }
            $setting['STANDARD_FIXED'] = $fixVal;
            $commdata['LEVEL_FIXED'] = $fixVal;
        } else {
            if ($setting['NEXT_COMM_TYPE'] == 'FIXED') {
                $fixVal = $setting['NEXT_COMM_VALUE'];
                $commtype = ($data['VOLUME'] / 100) . '*' . $fixVal . "*" . $scale;
                $commdata['LEVEL_FIXED'] = $fixVal;
                $cval = round(($data['VOLUME'] * $fixVal * $scale) / 100, 2);
                $setting['STANDARD_FIXED'] = $fixVal;
                $setting['FIXED'] = floatval($presetting['FIXED']) + floatval($setting['NEXT_COMM_VALUE']);
            } elseif ($setting['NEXT_COMM_TYPE'] == 'SCALE') {
                $fixVal = $setting['NEXT_COMM_VALUE'] / 100;
                $commtype = $commissionVal . '*' . $setting['NEXT_COMM_VALUE'] . '%*' . $scale;
                $commdata['LEVEL_SCALE'] = $setting['NEXT_COMM_VALUE'];
                $cval = round(($fixVal * $commissionVal * $scale) , 2);
                $setting['STANDARD_FIXED'] = floatval($presetting['STANDARD_FIXED']) * $fixVal;
                $setting['FIXED'] = floatval($presetting['FIXED']) + floatval($presetting['STANDARD_FIXED']) * $fixVal;
                $commdata['LEVEL_FIXED'] = $fixVal;
            } elseif ($setting['NEXT_COMM_TYPE'] == 'POINT') {
                $fixVal = $setting['NEXT_COMM_VALUE'];
                $commtype = $data['POINT_VALUE'] . "*" . ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
                $cval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
                $commdata['LEVEL_FIXED'] = $fixVal;
			} elseif ($setting['NEXT_COMM_TYPE'] == 'WIN') {
				if($this->calc_win_loss_deduct <= 0){
					$cval = 0;
				}
            } else {
				//DIFFER
                $fixVal = $setting['FIXED'] - floatval($presetting['FIXED']);
                if ($setting['COMM_TYPE'] == 'SCALE') {
                    $commtype = ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . floatval($presetting['FIXED']) . ')%*' . $scale;
                    $cval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
                } elseif ($setting['COMM_TYPE'] == 'POINT') {
                    $commtype = $data['POINT_VALUE'] . '*' . ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . floatval($presetting['FIXED']) . ')*' . $scale;
                    $cval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
				} elseif ($setting['COMM_TYPE'] == 'WIN') {
                    $commtype = $data['PROFIT'] . '*(' . $setting['FIXED'] . '-' . floatval($presetting['FIXED']) . ')%*' . $scale;
                    $cval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
					if($this->calc_win_loss_deduct <= 0){
						$cval = 0;
					}
                } else {
                    $commtype = ($data['VOLUME'] / 100) . '*(' . $setting['FIXED'] . '-' . floatval($presetting['FIXED']) . ')*' . $scale;
                    $cval = round(($data['VOLUME'] * $fixVal * $scale) / 100, 2);
                }
                $setting['STANDARD_FIXED'] = $fixVal;
                $commdata['LEVEL_FIXED'] = $fixVal;
            }
        }
    }
	
    private function processOuterAgentCommission(&$setting, $data, &$commdata, &$incval, $scale, &$commtype) {
        $fixVal = $setting['FIXED'];
        $incval = round(($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
        if ($setting['COMM_TYPE'] == 'SCALE') {
            $commtype = ($data['VOLUME'] / 100) . '*' . $fixVal . '%*' . $scale;
            $incval = round(($data['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
        } elseif ($setting['COMM_TYPE'] == 'POINT') {
            $commtype = $data['POINT_VALUE'] . "*" . ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
            $incval = round($data['POINT_VALUE'] * ($data['VOLUME'] / 100 * $fixVal * $scale) , 2);
		} elseif ($setting['COMM_TYPE'] == 'WIN') {
            $commtype = $data['PROFIT'] . '*' . $fixVal . '%*' . $scale;
            $incval = round(($data['PROFIT'] * $fixVal / 100 * $scale) , 2);
			if($this->calc_win_loss_deduct <= 0){
				$cval = 0;
			}
        } else $commtype = ($data['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
        $setting['STANDARD_FIXED'] = $fixVal;
        $commdata['LEVEL_FIXED'] = $fixVal;
    }
	
    private function getPointValue($order, $ver) {
        if ($ver == 5) {
            $tickSize = pow(0.1, $order['DIGITS']);
            $value = round($order['ContractSize'] * $tickSize * $order['RateProfit'], 2);
            $this->log_("版本：" . $ver . "订单" . $order['TICKET'] . "品种" . $order['SYMBOL'] . ",合约量" . $order['ContractSize'] . "基点" . $tickSize . ' 汇率：' . $order['RateProfit'] . '/' . $order['OPEN_PRICE'] . ' 点值：' . $value);
        } else {
            $symbol = $this->queryOrderSymbol($order['SYMBOL']);
            $tickSize = pow(0.1, $order['DIGITS']);
            if ($symbol['calc_formula'] == 'cfd') $value = round($symbol['contact_size'] * $tickSize * $order['CONV_RATE1'], 2);
            else $value = round($symbol['contact_size'] * $tickSize * $order['CONV_RATE1'] / $order['OPEN_PRICE'], 2);
            $this->log_("版本：" . $ver . "订单" . $order['TICKET'] . "品种" . $order['SYMBOL'] . ",合约量" . $symbol['contact_size'] . "基点" . $tickSize . ' 汇率：' . $order['OPEN_PRICE'] . '/' . $order['CONV_RATE1'] . ' 点值：' . $value);
        }
        return $value;
    }
	
    private function checkEarnestUser($mid, $time_zone, $close_time) {
        foreach ($this->earnestUsers as $key => $val) {
            if ($val['MemberId'] == $mid) {
                $close = strtotime($close_time) + (8 - $time_zone) * 3600;
                $this->log_("检测订单时间和开启对冲时间," . $close . " 是否大于" . $val['CreateTime']);
                if ($close > $val['StartTime']) return true;
                else return false;
            }
        }
        return false;
    }
	
    private function queryLessAmount($memberid, $db_, $svr_id) {
		global $DB;
        $mtloginwhere['mt_type'] = '0';
        $mtloginwhere['member_id'] = $memberid;
        $mtloginwhere['status'] = '1';
        $mtloginwhere['mtserver'] = $svr_id;
        $mtloginuser = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str($mtloginwhere));
        if (!$mtloginuser) {
            $this->log_("用户" . $memberid . "无返佣账户，已忽略！");
            return array(
                'status' => "-1",
                'msg' => "用户" . $memberid . "无返佣账户，已忽略！"
            );
        }
        $mt4user = getuser($mtloginuser["loginid"]);
        if ($mt4user) {
            if ($mt4user['EQUITY'] >= $this->lessAmount) {
                return array(
                    'status' => "1",
                    'msg' => "条件满足"
                );
            } else {
                $this->log_("用户" . $memberid . "净值余额小于" . $this->lessAmount . "，已忽略！");
                return array(
                    'status' => "0",
                    'msg' => "用户" . $memberid . "净值余额小于" . $this->lessAmount
                );
            }
        } else {
            $this->log_("用户" . $memberid . "MT4账户不存在，已忽略！");
            return array(
                'status' => "-2",
                'msg' => "用户" . $memberid . "MT4账户不存在，已忽略！"
            );
        }
    }
	
    private function queryAllSymbolSetting() {
		global $DB;
		
		//COMMISSION_PATTERN 0:内佣，1:外佣金，2:推荐返佣
        $settings = $DB->getDTable("select * from t_sale_setting where STATUS = 1 and COMMISSION_PATTERN <> 2 order by CREATE_TIME desc");
        return $settings;
    }
	
    private function queryRemindSymbolSetting() {
		global $DB;

        $settings = $DB->getDTable("select * from t_sale_setting where STATUS = 1 and COMMISSION_PATTERN = 2 order by CREATE_TIME desc");
        return $settings;
    }
	
    private function queryAllEarnest() {
		global $DB;

        $earnestusers = $DB->getDTable("select * from t_earnest_user where Status in (0,1) order by CreateTime desc");
        return $earnestusers;
    }
	
    public function queryOrderSymbol($symbol) {		
        foreach ($this->symbols as $key => $val) {
            if ($symbol == $val['symbol'] && $val['mttype'] == '0') {
                return $val;
            }
        }
        return array(
            'contact_size' => 0,
            'calc_formula' => 'forex'
        );
    }
	
    public function querySymbolSetting($symbol, $account, $closetime, $level, $modelType, $group, $svr_id, $time_zone) {
        $symbol_type = "";
        $type_name = "";
        $settings = array();
        foreach ($this->symbols as $key => $val) {
            if ($symbol == $val['symbol']) {
                $symbol_type = $val['type'];
                $type_name = $val['type_name'];
                $before = $symbol;
                break;
            }
        }
        $this->log_("symbol=" . $before . "|" . $symbol . " symbol_type=" . $symbol_type . " type_name=" . $type_name . " level=" . $level . " group=" . $group . "  modelType=" . $modelType);
        if ($symbol_type != "") {
			//对某个用户单独设置的，这个目前没有用
            foreach ($this->settings as $key => $val) {
                if ((strpos("," . $val['SYMBOL_TYPE'], "," . $type_name . ',') !== false) && $account == $val['ACCOUNT'] && $level == $val['LEVEL'] && $modelType == $val['MODEL_TYPE'] && (strpos("," . $val['GROUP_NAME'], "," . $group . ',') !== false) && $svr_id == $val['SERVER_ID']) {
                    $this->log_("获取到配置：" . json_encode($val));
                    $settings[] = $val;
                }
            }
			//这个才有效
            foreach ($this->settings as $key => $val) {
                if ((strpos("," . $val['SYMBOL_TYPE'], "," . $type_name . ',') !== false) && $modelType == $val['MODEL_TYPE'] && $level == $val['LEVEL'] && (strpos("," . $val['GROUP_NAME'], "," . $group . ',') !== false) && $svr_id == $val['SERVER_ID']) {
                    $settings[] = $val;
                }
            }
        } else {
            $this->log_($symbol . "所属的交易种类不存在，请确认");
            return array();
        }
        return $settings;
    }
	
    public function querySymbolRemindSetting($symbol, $account, $closetime, $level, $modelType, $group, $svr_id, $time_zone) {
        $symbol_type = "";
        $type_name = "";
        $settings = array();
        foreach ($this->symbols as $key => $val) {
            if ($symbol == $val['symbol']) {
                $symbol_type = $val['type'];
                $type_name = $val['type_name'];
                $before = $symbol;
                $this->log_("symbol=" . $before . "|" . $symbol . " symbol_type=" . $symbol_type . " type_name=" . $type_name . " level=" . $level . " group=" . $group . "  modelType=" . $modelType);
                foreach ($this->remindsettings as $key => $val) {
                    if ((strpos("," . $val['SYMBOL_TYPE'], "," . $type_name . ',') !== false) && $account == $val['ACCOUNT'] && $level == $val['LEVEL'] && $modelType == $val['MODEL_TYPE'] && (strpos("," . $val['GROUP_NAME'], "," . $group . ',') !== false) && $svr_id == $val['SERVER_ID']) {
                        $this->log_("获取到配置：" . json_encode($val));
                        $settings[] = $val;
                    }
                }
                foreach ($this->remindsettings as $key => $val) {
                    if ((strpos("," . $val['SYMBOL_TYPE'], "," . $type_name . ',') !== false) && $modelType == $val['MODEL_TYPE'] && $level == $val['LEVEL'] && (strpos("," . $val['GROUP_NAME'], "," . $group . ',') !== false) && $svr_id == $val['SERVER_ID']) {
                        $settings[] = $val;
                    }
                }
            }
        }
        if ($symbol_type == "") {
            $this->log_($symbol . "所属的交易种类不存在，请确认");
            return array();
        }
        return $settings;
    }
		
    private function combineGroups($svrid) {
        $groups = "";
        foreach ($this->settings as $key => $val) {
            if ($val['SERVER_ID'] == $svrid) {
                $groups = $groups . $val['GROUP_NAME'];
            }
        }
        $array_groups = explode(',', $groups);
        $array_groups = array_unique(array_filter($array_groups));
        return $array_groups;
    }
	
    private function log_($loginfo) {
		if($_REQUEST['debug']){
			echo $loginfo , "<br><br>";
		}
		loginfo('api_sale_service',$loginfo);
    }
	
    private function checkSecret($secret) {
		return true;
		
		global $DB;
		
        if (!$secret) {
            echo '密钥参数为空';
            $this->log_("secret为空:");
            return false;
        }
		
        $configsecret = $DB->getDRow("select * from t_config where `name` = 'TIMER_SECRET' and status = '1'");
		
        if (!$configsecret) {
            echo '未配置TIMER_SECRET';
            $this->log_("未配置TIMER_SECRET");
            return false;
        }
		
        $key = md5($configsecret['value'] . C('APP_WEB_NAME'));

        if ($secret != $key) {
            echo '同步密钥不正确';
            $this->log_("同步密钥不正确. 系统配置" . $key . " 访问来源:" . $secret);
            return false;
        }
        return true;
    }
	
    private function getConfigValue($key, $servetid) {
		global $DB;
		
        $configval = $DB->getDRow("select * from t_config_server where configname = '{$key}' and server_id = '{$servetid}'");
        if ($configval) {
            return $configval['configvalue'];
        }
        $configval = $DB->getDRow("select * from t_config where `name` = '{$key}' and status = '1'");
        if ($configval) {
            return $configval['value'];
        }
        return NULL;
    }
	
    private function saveConfigValue($key, $servetid, $val) {
		global $DB;
		
        $config = $DB->getDRow("select * from t_config_server where configname = '{$key}' and server_id = '{$servetid}'");
        if ($config) {
            $ret = $DB->query("update t_config_server set configvalue = '{$val}',update_time = '" . time() . "' where configname = '{$key}' and server_id = '{$servetid}'");
        } else {
			$data = array();
            $data['configname'] = $key;
            $data['create_time'] = time();
            $data['server_id'] = $servetid;
            $ret = $DB->insert("t_config_server",$data);
			
            $ret1 = $DB->query("update t_config set `value` = '{$val}' where `name` = '{$key}'");
        }
        return $ret1;
    }
		
    public function startTask($taskId, $deamon) {
		global $DB;
		
        if ($this->checkThreadAlive($taskId)) return "-2";
		
        $task = $DB->getDRow("select * from t_task where Id = '{$taskId}' and Status = 1");
        if (!$task) return NULL;
		
        $now = time();
        if ($now - floatval($task['LastTime']) <= floatval(($task['IntervalTime'] + 100)) && $deamon) return '-1';
        $ret = $DB->query("update t_task set LastTime = '{$now}' where Id = '{$taskId}'");
        if ($ret !== FALSE) return $task;
        return '0';
    }
	
    private function setSingle($taskId, $interval) {
		$expired = $interval + 100 + time();
		$result  =   file_put_contents('cache/task_thread_' . $taskId . '.php',$expired);
		
		$this->log_("set task_thread:task_thread_" . $taskId . ' expired:' . $expired);
    }
	
    private function clearSingle($taskId) {
		unlink('cache/task_thread_' . $taskId . '.php');
        $this->log_("clear task_thread:task_thread_" . $taskId);
    }
	
    private function checkThreadAlive($taskId) {
		if(file_exists('cache/task_thread_' . $taskId . '.php')){
			$content    =   file_get_contents('cache/task_thread_' . $taskId . '.php');
			$content = intval($content);
		}else{
			$content = 0;
		}
		if((time() - $content) >= 0){
			return false;
		}else{
			return true;
		}
    }
	
}

