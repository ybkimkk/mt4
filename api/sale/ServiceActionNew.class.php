<?php
class ServiceAction {
    public $settings;
    protected $symbols;
    public $lessAmount = 0;
    public $order_open_close_interval = 1;
    protected $delayMinute = 15;
    protected $calc_agent_self_commission = 0;
	protected $calc_member_self_commission = 0;
    protected $calc_remind_commission_term = 0;
    protected $task;
	public $config_AUTO_2_MT4 = 0;
	public $config_CALC_WIN_LOSS_DEDUCT = 0;
	public $config_CALC_WIN_PS_TYPE = 0;
	
	public $config_BONUS_DIRECT_FLOOR = array();
	public $config_BONUS_DIRECT_SC_TYPE = '';
	public $config_BONUS_DIRECT_CHILD = array();
	public $config_BONUS_MEMBER_UD_CS = '';
	public $config_BONUS_MEMBER_JJ_SC_TYPE = '';
	public $config_BONUS_MEMBER_JJ_CHILD = array();
	public $config_BONUS_AGENT_UD_CS = '';
	public $config_BONUS_AGENT_JJ_SC_TYPE = '';
	public $config_BONUS_AGENT_JJ_CHILD = array();
	public $config_CALC_DIRECT_SELF_COMMISSION = 0;
	
	public $CZ_IS_DEBUG = 0;
	public $CZ_DEBUG_CLEAR_STABLE = 0;

    public function calcCommission() {
		//调试模式会清空和更改这么多数据，只有本机允许开启，服务器肯定不允许
		if($this->CZ_IS_DEBUG > 0){
			if(stripos($_SERVER['HTTP_HOST'],'127.0.0.1') === false){
				$this->CZ_IS_DEBUG = 0;
			}
		}
		
        $ip = FGetClientIP();
        $this->log_("访问IP:" . $ip);
		
        if (!$this->checkSecret($_REQUEST["secret"])) return;
		
        $taskId = $_REQUEST['taskId'];
        $daemon = $_REQUEST['daemon'];
        $this->calc($taskId, $daemon, $_REQUEST["secret"]);
    }
		
	//日、周、月结：把结算的数据进行汇总，增加佣金给客户
    private function settleRegularTimeCommission($startcalctime, $end, $server, $status) {
		global $DB;
        unset($where);
        $where['STATUS'] = $status;
        $where['SERVER_ID'] = $server['id'];
		if($status == -3){
			//日、周、月结
			$where['CLOSE_TIME'] = array(//-3
				'between',
				'' . $startcalctime . ',' . $end . ''
			);
		}else{
			//即时结算
			$where['BALANCE_TIME'] = array(
				'between',
				'' . $startcalctime . ',' . $end . ''
			);
		}
        $GROUPAGENTACCOUNTS = $DB->getDTable("select COMM_MEMBER_ID,sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where) . ' group by COMM_MEMBER_ID');
        $this->log_('开始结算，时间(' . $startcalctime . ',' . $end . ')' . '数据量' . count($GROUPAGENTACCOUNTS) . ' status:' . $status);
        foreach ($GROUPAGENTACCOUNTS as $key => $val) {
            $where['COMM_MEMBER_ID'] = $val["COMM_MEMBER_ID"];
            //$TOTAL = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission " . cz_where_to_str($where));
			$TOTAL = floatval($val["sum1"]);
			{
                if ($TOTAL == 0) {
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
				
				//佣金是否加进mt帐号
                $retval = false;
				
				//佣金如果加进mt帐号，执行
				if($this->CZ_IS_DEBUG <= 0){
					if ($this->config_AUTO_2_MT4 == '1'){
						$retval = $this->balance2MT4($TOTAL, $val["COMM_MEMBER_ID"], $BALANCE_ID, $server['id'], $server['time_zone']);
					}else{				
						//如果是负的，说明是要扣除用户的，直接执行扣除（如果计算到crm余额，用户不一定愿意去执行该笔扣除）。这种情况目前只有一种：直客分润给代理商
						if($TOTAL <= 0){
							$retval = $this->balance2MT4($TOTAL, $val["COMM_MEMBER_ID"], $BALANCE_ID, $server['id'], $server['time_zone']);
						}
					}
				}
				
				//加进CRM余额
				if(!$retval){
					$mwhere['id'] = $val["COMM_MEMBER_ID"];
					$mwhere['server_id'] = $server['id'];
					$memberuser = $DB->getDRow("select * from t_member " . cz_where_to_str($mwhere));
					if ($memberuser) {
						$amount = $memberuser['amount'] + floatval($TOTAL);
						$dbmap['amount'] = $amount;
						$retmember = $DB->update("t_member",$dbmap,cz_where_to_str($mwhere));
						if ($retmember > 0) {
							$this->log_("未入金至mt，用户" . $mwhere['id'] . "余额已经更新，余额=" . $amount);
						} else {
							$this->log_("未入金至mt，用户" . $mwhere['id'] . "更新未提现余额失败，余额=" . $amount);
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
		if($this->CZ_IS_DEBUG <= 0){
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
		}else{
			$start = array('Id'=>1,'IntervalTime'=>10);
			
			if($this->CZ_DEBUG_CLEAR_STABLE > 0){
				$DB->query("truncate table t_sale_commission");
				$DB->query("alter table t_sale_commission AUTO_INCREMENT=1");
							
				$DB->query("truncate table t_sale_commission_amount0");
				$DB->query("alter table t_sale_commission_amount0 AUTO_INCREMENT=1");
				
				$DB->query("truncate table t_sale_commission_balance");
				$DB->query("alter table t_sale_commission_balance AUTO_INCREMENT=1");
			}
		}
		
        $this->task = $start;
        $servers = $DB->getDTable("select * from t_mt4_server where status = 1");
        if (count($servers) < 1) {
            $this->log_('无可用MT4服务器数，返佣停止!');
            return;
        }
		
        $this->setSingle($taskId, $start['IntervalTime']);
        $this->log_("----------------------返佣计划开始-----------------------------------");
        $this->settings = $this->queryAllSymbolSetting();
		//遍历所有mt服务器
        foreach ($servers as $keysvr => $server) {
            //是否启用返佣
            $open_commission = $this->getConfigValue('OPEN_COMMISSION', $server['id']);
            if ($open_commission != '1') {
                $this->log_("MT4服务MANAGE " . $server['id'] . $server['mt4_name'] . '未开启返佣，忽略');
                continue;
            }
			
			//调试模式
			if($this->CZ_IS_DEBUG > 0){
				$this->saveConfigValue('CALC_LAST_TIME', $server['id'], strtotime('2021-10-05 00:00:00'));
				$this->saveConfigValue('SETTLE_REMIND_LAST_TIME', $server['id'], strtotime('2021-10-05 00:00:00'));
			}

            $this->log_("------开始计算MT4服务器" . $server['id'] . $server['mt4_name'] . '的返佣！------');
			
			//开仓平仓至少间隔
			$this->order_open_close_interval = 0;
            $OPEN_CLOSE_INTERVAL = $this->getConfigValue('OPEN_CLOSE_INTERVAL', $server['id']);
            if ($OPEN_CLOSE_INTERVAL) {
                $this->order_open_close_interval = floatval($OPEN_CLOSE_INTERVAL);
            }
            $this->log_("开仓平仓时间间隔最少为" . $this->order_open_close_interval . '，为有效订单！');
						
			//直客余额或者净值大于等于此金额，才接受返佣
			$this->lessAmount = $this->getConfigValue('MIN_COMMISSION_BALANCE', $serverid);
			if (!$this->lessAmount) {
				$this->lessAmount = 0;
			}
            $this->log_("配置MIN_COMMISSION_BALANCE,默认为0， 分销用户接受返佣的最小余额或者净值,本次为：" . $this->lessAmount);
			
			//代理接受自己返佣
            $this->calc_agent_self_commission = 0;
            $calc_agent_self_commission = $this->getConfigValue('CALC_AGNET_SELF_COMMISSION', $server['id']);
            if ($calc_agent_self_commission) {
                $this->calc_agent_self_commission = intval($calc_agent_self_commission);
            }
            $this->log_("代理接受自己返佣（0：不接受，1：接受）：" . $this->calc_agent_self_commission);
			
			//员工接受自己返佣（未配置，这里默认为不允许）
			$this->calc_member_self_commission = 0;
					
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
			
            $this->log_("返佣开始GMT时间：" . $scalccommtime . " 结束：" . $ecalccommtime . " 时区：GMT " . $server['time_zone'] . ' 服务器时间：' . date('Y-m-d H:i:s'));
			
            $reportModel = new ReportModel($dbName);
			
			//返佣组
            $array_groups = $this->combineGroups($server['id']);
            $this->log_("返佣组：" . json_encode($array_groups));			
			
			//按盈利返佣（亏损时扣除代理）
            $this->config_CALC_WIN_LOSS_DEDUCT = intval($this->getConfigValue('CALC_WIN_LOSS_DEDUCT', $server['id']));
            $this->log_("按盈利返佣（亏损时扣除代理）（0：否，1：是）：" . $this->config_CALC_WIN_LOSS_DEDUCT);
			//按盈利返佣（分润自客户）
            $this->config_CALC_WIN_PS_TYPE = intval($this->getConfigValue('CALC_WIN_PS_TYPE', $server['id']));
            $this->log_("按盈利返佣（分润自客户）（0：否，1：是）：" . $this->config_CALC_WIN_PS_TYPE);
			
			//其它新配置
			$this->config_BONUS_DIRECT_FLOOR = explode(',',$this->getConfigValue('BONUS_DIRECT_FLOOR', $server['id']));
			$this->log_("计算直客的层级，可以算为1层的：" . implode(',',$this->config_BONUS_DIRECT_FLOOR));
			$this->config_BONUS_DIRECT_SC_TYPE = $this->getConfigValue('BONUS_DIRECT_SC_TYPE', $server['id']);
			$this->log_("计算直客的层级，下级：" . $this->config_BONUS_DIRECT_SC_TYPE);
			$this->config_BONUS_DIRECT_CHILD = explode(',',$this->getConfigValue('BONUS_DIRECT_CHILD', $server['id']));
			$this->log_("计算直客的层级，下级允许是：" . implode(',',$this->config_BONUS_DIRECT_CHILD));
			
			$this->config_BONUS_MEMBER_UD_CS = $this->getConfigValue('BONUS_MEMBER_UD_CS', $server['id']);
			$this->log_("计算上下级的级别大小，员工：" . $this->config_BONUS_MEMBER_UD_CS);
			$this->config_BONUS_MEMBER_JJ_SC_TYPE = $this->getConfigValue('BONUS_MEMBER_JJ_SC_TYPE', $server['id']);
			$this->log_("计算间接客户，员工的下级：" . $this->config_BONUS_MEMBER_JJ_SC_TYPE);
			$this->config_BONUS_MEMBER_JJ_CHILD = explode(',',$this->getConfigValue('BONUS_MEMBER_JJ_CHILD', $server['id']));
			$this->log_("计算间接客户，员工的下级允许是：" . implode(',',$this->config_BONUS_MEMBER_JJ_CHILD));
			
			$this->config_BONUS_AGENT_UD_CS = $this->getConfigValue('BONUS_AGENT_UD_CS', $server['id']);
			$this->log_("计算上下级的级别大小，代理：" . $this->config_BONUS_AGENT_UD_CS);
			$this->config_BONUS_AGENT_JJ_SC_TYPE = $this->getConfigValue('BONUS_AGENT_JJ_SC_TYPE', $server['id']);
			$this->log_("计算间接客户，代理的下级：" . $this->config_BONUS_AGENT_JJ_SC_TYPE);
			$this->config_BONUS_AGENT_JJ_CHILD = explode(',',$this->getConfigValue('BONUS_AGENT_JJ_CHILD', $server['id']));
			$this->log_("计算间接客户，代理的下级允许是：" . implode(',',$this->config_BONUS_AGENT_JJ_CHILD));
			
			$this->config_CALC_DIRECT_SELF_COMMISSION = $this->getConfigValue('CALC_DIRECT_SELF_COMMISSION', $server['id']);
			$this->log_("直客获得自己交易返佣：" . $this->config_CALC_DIRECT_SELF_COMMISSION);
			
			
			
			$this->config_AUTO_2_MT4 = $this->getConfigValue('AUTO_2_MT4', $server['id']);
			$this->log_("配置了AUTO_2_MT4参数，值为：" . $this->config_AUTO_2_MT4);
			
			//遍历所有用户
            $users = $DB->getDTable("select * from t_member where server_id = '{$server['id']}' and status = 1");
            foreach ($users as $key1 => $user) {
				//获取对应的所有mt帐户
                $mtusers = $DB->getDTable("select * from t_member_mtlogin where member_id = '{$user['id']}' and mtserver = '{$server['id']}' and status = 1");
                $this->setSingle($taskId, $start['IntervalTime'],"获取所有mt帐号：" . $user['id']);
                foreach ($mtusers as $key => $val) {
					//获取mt数据库中的mt用户数据
                    $mt4user = getuser($val["loginid"]);
                    if (!$mt4user) {
                        $this->log_("用户" . $val["loginid"] . "，mt4中不存在");
                        continue;
                    }
					
					//获取该用户的 时间段内 所有 已平仓订单
                    $orders = $reportModel->queryClosedOrders($val["loginid"], "", $scalccommtime, $ecalccommtime, $server['ver']);
                    $this->setSingle($taskId, $start['IntervalTime'],$val["loginid"] . "订单：" . count($orders));
					
					//遍历订单
                    foreach ($orders as $k => $order) {
                        $this->setSingle($taskId, $start['IntervalTime'],"订单遍历：" . $order['TICKET']);
						//如果已经结算过
                        $retticket = $DB->getDRow("select * from t_sale_commission where TICKET = '{$order['TICKET']}' and SERVER_ID = '{$server['id']}'");
                        if ($retticket) {
                            $this->log_("该订单" . $order['TICKET'] . '不能重复返佣');
                            continue;
                        }
						$retticket = $DB->getDRow("select * from t_sale_commission_amount0 where TICKET = '{$order['TICKET']}' and SERVER_ID = '{$server['id']}'");
                        if ($retticket) {
                            $this->log_("该订单" . $order['TICKET'] . '不能重复返佣0');
                            continue;
                        }
						
                        $order['member_id'] = $val['member_id'];
						//如果是投机行为
                        if ((strtotime($order['CLOSE_TIME']) - strtotime($order['OPEN_TIME'])) < $this->order_open_close_interval) {
                            $unnormaldata['TICKET'] = $order['TICKET'];
                            $unnormaldata['LOGIN'] = $order['LOGIN'];
                            $unnormaldata['BALANCE_TIME'] = time();
                            $unnormaldata['MEMBER_ID'] = $order['member_id'];
                            $unnormaldata['COMM_MEMBER_ID'] = $order['member_id'];
                            $unnormaldata['VOLUME'] = $order['VOLUME'];
                            $unnormaldata['STATUS'] = "0";
                            $unnormaldata['SERVER_ID'] = $server['id'];
							
                            $unnormaldata['AMOUNT'] = 0;
                            $unnormaldata['STATUS'] = -1;
                            $unnormaldata['MEMO'] = "出入场间隔不足" . $this->order_open_close_interval . "秒";
                            $unnormalrest = $DB->insert("t_sale_commission_amount0",$unnormaldata);
							
                            $this->log_($order['TICKET'] . "，" . $order['OPEN_TIME'] . "开仓，" . $order['CLOSE_TIME'] . "平仓，间隔不足" . $this->order_open_close_interval . '，鉴定可能是投机行为，忽略：' . $unnormalrest);
                            continue;
                        }
						
						//获取点值
                        $pointValue = $this->getPointValue($order, $server['ver']);
                        $order['POINT_VALUE'] = $pointValue;
						
						//结算这笔订单
						//结算里，按推荐关系、从下往上推，每一层都会计算：代理、直客、员工 得到的佣金
                        //$this->calcOne($order, $user, 0, NULL, $mt4user, $server['time_zone'], 0, 0, 'direct', $server['id'], 1000, 0, $user);
						
						
						//2021.10.19
						//新的计算函数
						$this->calcOne_new($order, 
										$user, 
										0,
										0,
										$server,
										array('oUser'=>$user,'oMt4User'=>$mt4user), 
										array(
												'agent'=>array(
																'lineMaxLv'=>0,
																'bonusIsStop'=>0,
																'lastSetting'=>array(),
																'lastSUser'=>array(),
																'lastBounsNum'=>0,
																'lastJC'=>array(),
																),
												'member'=>array(
																'lineMaxLv'=>0,
																'bonusIsStop'=>0,
																'lastSetting'=>array(),
																'lastSUser'=>array(),
																'lastBounsNum'=>0,
																'lastJC'=>array(),
																),
												'direct'=>array(
																'currFloor'=>0,
																'bonusIsStop'=>0,
																'lastSetting'=>array(),
																'lastBounsNum'=>0,
																),
											)
										);
						
						
						
						$this->log_("\r\n++++++++++++++++++++++++++++++++++++++++\r\n");
                    }
                }
            }
			
			//更新最后一次的返佣时间，下次计算返佣就从这个时间开始
            $this->saveConfigValue('CALC_LAST_TIME', $server['id'], $last_time);
            $this->log_($server['mt4_name'] . "更新上次返佣时间为" . $last_time . "！");
			
			//-------------------------------------------------------------------------------------------------
			
			//推荐返佣结算最后一次时间,代表上一个结算周日执行的具体时间，不能随意改动
            $SETTLE_REMIND_LAST_TIME = $this->getConfigValue('SETTLE_REMIND_LAST_TIME', $server['id']);
			
			//推荐返佣结算日,代表下一个结算周期后的天数，比如1:日结代表当天后的1天，周结代表周六后的1天，月结代表下这个月后的一天，即下个月1号
            $SETTLE_REMIND_LAST_DAY = $this->getConfigValue('SETTLE_REMIND_LAST_DAY', $server['id']);
			
			//推荐返佣结算时间点,代表在几点结算，只能配置1-24小时，北京时间执行，请根据自己的MT时区设置，建议为8-9点
            $CALC_REMIND_COMMISSION_TERM_VAL = $this->getConfigValue('CALC_REMIND_COMM_TERM_VAL', $server['id']);

			$mustJS = 1;
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
                    $mustJS = 0;
                }else{
					$remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , $day, date('Y')));
					$remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , $day, date('Y')));
					$this->log_("开始日结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
				}
            } else if ($this->calc_remind_commission_term == '2') {
                $year = date("Y", $SETTLE_REMIND_LAST_TIME);
                $month = date("m", $SETTLE_REMIND_LAST_TIME);
                $day = date("d", $SETTLE_REMIND_LAST_TIME);
                $week = date("w", $SETTLE_REMIND_LAST_TIME);
                $remind_nextcalctime = mktime($CALC_REMIND_COMMISSION_TERM_VAL, 0, 0, $month, $day - $week + 6 + $SETTLE_REMIND_LAST_DAY, $year);
                $remind_now = time();
                if ($remind_now < $remind_nextcalctime) {
                    $this->log_("周结推荐返佣时间未到：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                    $mustJS = 0;
                }else{
					$remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - date('w') - 6, date('y')));
					$remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - date('w') , date('y')));
					$this->log_("开始周结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
				}
            } else if ($this->calc_remind_commission_term == '3') {
                $year = date("Y", $SETTLE_REMIND_LAST_TIME);
                $month = date("m", $SETTLE_REMIND_LAST_TIME);
                $day = date("d", $SETTLE_REMIND_LAST_TIME);
                $remind_nextcalctime = mktime($CALC_REMIND_COMMISSION_TERM_VAL, 0, 0, $month + 1, $day + $SETTLE_REMIND_LAST_DAY - 1, $year);
                $remind_now = time();
                if ($remind_now < $remind_nextcalctime) {
                    $this->log_("月结推荐返佣时间未到：next" . date("Y-m-d H:i", $remind_nextcalctime) . " real" . date("Y-m-d H:i", $remind_now));
                    $mustJS = 0;
                }else{
					$remind_starttime = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') - 1, 1, date('y')));
					$remind_endtime = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , 0, date('y')));
					$this->log_("开始月结推荐返佣 " . $remind_starttime . "-" . $remind_endtime);
				}
            }
			if($mustJS > 0){
				if ($this->calc_remind_commission_term == 0){
					$this->settleRegularTimeCommission($startcalctime, time(), $server, 0);
				}else{				
					$this->settleRegularTimeCommission($remind_starttime, $remind_endtime, $server, -3);
				}
			
				$this->saveConfigValue('SETTLE_REMIND_LAST_TIME', $server['id'], $last_time);
				$this->log_($server['mt4_name'] . "更新上次推荐返佣时间为" . $last_time . "！");
			}

        }
		
        $this->log_("----------------------本次返佣计划结束-----------------------------------");
		
        $this->clearSingle($taskId);
    }
	
	
	
	//结算这笔订单
	//结算里，按推荐关系、从下往上推，每一层都会计算：代理、直客、员工 得到的佣金
    private function calcOne_new($order, $childUser, $floor,$tFloor, $server, $oArr, $calArr) {
		global $DB;
        $this->setSingle($this->task['Id'], $this->task['IntervalTime'],"calcOne_new");

		$cWinner = array();
		
		//首次拿自己的返佣
		//代理、员工 拿自己的返佣时，这算第1层，因为不可能自己拿直客、上家又拿一次直客
		//直客 拿自己的返佣时，这算额外的、第0层，上级代理仍然按正常的走（该直客是上级代理的第1层）
		if($floor == 0){
			if($childUser['userType'] == 'agent'){
				//订单持有者是 代理
				if($this->calc_agent_self_commission == 1){
					$this->log_("代理（全部）计算自己交易返佣给自己");
            		$cWinner = $childUser;
				}else if($this->calc_agent_self_commission == 2){
					if($childUser['f_isS'] > 0){
						$this->log_("代理（仅标识(S)）计算自己交易返佣给自己");
						$cWinner = $childUser;
					}
				}
			}else if($childUser['userType'] == 'member'){
				//订单持有者是 员工
				if($this->calc_member_self_commission == 1){
					$this->log_("员工计算自己交易返佣给自己");
            		$cWinner = $childUser;
				}
			}else if($childUser['userType'] == 'direct'){
				//订单持有者是 直客
				if($this->config_CALC_DIRECT_SELF_COMMISSION == 1){
					$this->log_("直客（全部）计算自己交易返佣给自己");
					$cWinner = $childUser;
					$calArr['direct']['currFloor'] = 0;
					
					//这是一个特殊情况，该变量仅仅为了修正一个特殊情况而存在：当直客拿自己的佣金时，这个佣金是额外拿的（直客拿自己佣金算0级），直客之上的代理、仍然会拿该直客算为直客的佣金
					$tFloor = -1;
				}else if($this->config_CALC_DIRECT_SELF_COMMISSION == 2){
					if($childUser['f_isS'] > 0){
						$this->log_("直客（仅标识(S)）计算自己交易返佣给自己");
						$cWinner = $childUser;
						$calArr['direct']['currFloor'] = 0;
						
						$tFloor = -1;
					}
				}
			}
		}
				
		//如果没有 拿自己的返佣者，就找上家
		if(!$cWinner){
			//0层如果获得自己的佣金，不会进入到这里
			//进入到这里的是 0层未获得自己的佣金，或者0层以上
			
			if($childUser['parent_id'] <= 0){
				$this->log_("订单" . $order['TICKET'] . "，上家不存在，忽略返佣");
            	return;
			}
			
			$cWinner = $DB->getDRow("select * from t_member where id = '{$childUser['parent_id']}' and server_id = '{$server['id']}'");
			if(!$cWinner){
				$this->log_("订单" . $order['TICKET'] . "，上家" . $childUser['parent_id'] . "(ser_id=" . $server['id'] . ")未找到，忽略返佣");
            	return;
			}
			
			//检测推荐关系是否有误
			if ($cWinner['id'] == $childUser['id']) {
				$this->log_("订单" . $data['TICKET'] . '，用户' . $oArr['oUser']['id'] . " 上级为自己，造成死循环，忽略返佣，请调整后，再次进行返佣");
				return;
			}			
			
			if($cWinner['userType'] == 'agent'){
				//如果获得者是代理
				//允许下级的角色，不允许的角色时 是否终止佣金继续发放
				//但如果：1级时、下级是直客，这种情况不判断
				if($floor <= 1 && $childUser['userType'] == 'direct'){
					
				}else{
					if($calArr['agent']['bonusIsStop'] <= 0){
						if(!in_array($childUser['userType'],$this->config_BONUS_AGENT_JJ_CHILD)){
							if($this->config_BONUS_AGENT_JJ_SC_TYPE == 'stop'){
								$calArr['agent']['bonusIsStop'] = 1;
								$this->log_("代理返佣终止：" . $floor . '，' . $childUser['userType'] . '，' . implode(',',$this->config_BONUS_AGENT_JJ_CHILD));
							}
						}
					}
				}
				
				//如果之前的下家是直客，这里要加一层直客的层数给它，不然下一次遇到直客，那个直客的层数并没有增加
				if($childUser['userType'] == 'direct'){
					$calArr['direct']['currFloor']++;
				}
			}else if($cWinner['userType'] == 'member'){
				//如果获得者是员工
				if($floor <= 1 && $childUser['userType'] == 'direct'){
					
				}else{
					if($calArr['member']['bonusIsStop'] <= 0){
						if(!in_array($childUser['userType'],$this->config_BONUS_MEMBER_JJ_CHILD)){
							if($this->config_BONUS_MEMBER_JJ_SC_TYPE == 'stop'){
								$calArr['member']['bonusIsStop'] = 1;
								$this->log_("员工返佣终止：" . $floor . '，' . $childUser['userType'] . '，' . implode(',',$this->config_BONUS_MEMBER_JJ_CHILD));
							}
						}
					}
				}
				
				//如果之前的下家是直客，这里要加一层直客的层数给它，不然下一次遇到直客，那个直客的层数并没有增加
				if($childUser['userType'] == 'direct'){
					$calArr['direct']['currFloor']++;
				}
			}else if($cWinner['userType'] == 'direct'){
				//如果获得者是直客
				if($calArr['direct']['bonusIsStop'] <= 0){
					if(!in_array($childUser['userType'],$this->config_BONUS_DIRECT_CHILD)){
						if($this->config_BONUS_DIRECT_SC_TYPE == 'stop'){
							$calArr['direct']['bonusIsStop'] = 1;
							$this->log_("员工返佣终止：" . $floor . '，' . $childUser['userType'] . '，' . implode(',',$this->config_BONUS_DIRECT_CHILD));
						}
					}
				}
				if(in_array($childUser['userType'],$this->config_BONUS_DIRECT_FLOOR)){
					$calArr['direct']['currFloor']++;
				}
			}
		}

		//如果全部终止结算
		if($calArr['agent']['bonusIsStop'] > 0 && $calArr['member']['bonusIsStop'] > 0 && $calArr['direct']['bonusIsStop'] > 0){
			$this->log_("订单" . $order['TICKET'] . '，代理、员工、直客 全部终止结算，终止继续返佣');
			return;
		}
		
		//判断下级用户类型
		if(!in_array($childUser['userType'],array('agent','member','direct'))){
			$this->log_("订单" . $order['TICKET'] . '，下级用户' . $childUser['id'] . " userType 不明，终止继续返佣");
			return;
		}
		if(!in_array($cWinner['userType'],array('agent','member','direct'))){
			$this->log_("订单" . $order['TICKET'] . '，佣金获得用户' . $cWinner['id'] . " userType 不明，终止继续返佣");
			return;
		}
		
		//层数累加
		$floor++;
		$tFloor++;
		
		//到达这里，肯定是 有找到拿佣金的用户
		$this->log_("----------");
        $this->log_("获得返佣的账户信息：floor=" . $floor . "，tFloor=" . $tFloor . "(" . $cWinner['id'] . ")" . $cWinner['nickname'] . ' email:' . $cWinner['email']);
		
		//代理或员工 获得本次运算相关的 最大等级
		if($childUser['userType'] == 'agent'){
			$calArr['agent']['lineMaxLv'] = max($calArr['agent']['lineMaxLv'],$childUser['level']);
			
			//如果未终止佣金结算，下家又是代理，替换掉最后一位代理的数据缓存
			if($calArr['agent']['bonusIsStop'] <= 0){
				$calArr['agent']['lastSUser'] = $childUser;
			}
		}else if($childUser['userType'] == 'member'){
			$calArr['member']['lineMaxLv'] = max($calArr['member']['lineMaxLv'],$childUser['level']);
			
			//如果未终止佣金结算，下家又是员工，替换掉最后一位员工的数据缓存
			if($calArr['member']['bonusIsStop'] <= 0){
				$calArr['member']['lastSUser'] = $childUser;
			}
		}else if($childUser['userType'] == 'direct'){
			//员工按层级，没有最大等级说法
		}
		
		//然后就执行结算了
		$newCalArr = $this->calCurrFloor($order, $cWinner,$childUser, $floor,$tFloor, $server, $oArr, $calArr);
		
		if($newCalArr === false){
			$this->log_("订单" . $data['TICKET'] . "，完成，终止继续返佣");
			return;
		}

		//继续
		$this->calcOne_new($order, $cWinner, $floor,$tFloor, $server, $oArr, $newCalArr);		
	}
	
	
	
    private function calCurrFloor($order, $cWinner, $childUser, $floor,$tFloor, $server, $oArr, $calArr){
		global $DB;
				
		$mt4time = time() + 3600 * floatval(8 - $server['time_zone']);
		
        $commdata['TICKET'] = $order['TICKET'];
        $commdata['LOGIN'] = $order['LOGIN'];
        $commdata['BALANCE_TIME'] = time();
        $commdata['MEMBER_ID'] = $order['member_id'];
        $commdata['VOLUME'] = $order['VOLUME'];
        $commdata['STATUS'] = "0";
        $commdata['SERVER_ID'] = $server['id'];
        $commdata['CLOSE_TIME'] = $order['CLOSE_TIME'];
        $commdata['COMM_MEMBER_ID'] = $cWinner['id'];
        $commdata['CREATE_TIME'] = time();
		
		//佣金获得者的信息
		$winnerUIN = $cWinner['userType'] . '(' . $cWinner['id'] . ')' . (strlen($cWinner['nickname']) ? $cWinner['nickname'] : $cWinner['realname']);
		
		//当前是第几级或第几层
		$calLevel = $cWinner['userType'] == 'direct' ? $calArr['direct']['currFloor'] : $cWinner['level'];
		$calGroup = $oArr['oMt4User']['GROUP'];
		
		//取得 符合佣金获得者 的所有佣金配置
        $settings = $this->querySymbolSetting($order['SYMBOL'], $cWinner['id'], $calLevel, $cWinner['userType'], $calGroup, $server['id'], $calArr);
		$this->log_("发现佣金配置" . count($settings) . "个，" . json_encode($settings));
		
        if ($cWinner['userType'] == 'direct') {
            $commdata['LEVEL_NAME'] = $calLevel . '层直客';
        } else if ($cWinner['userType'] == 'agent') {
            $commdata['LEVEL_NAME'] = $calLevel . '级代理';
        } else if ($cWinner['userType'] == 'member') {
            $commdata['LEVEL_NAME'] = $calLevel . '级员工';
        }
		
		//如果不需要发放，执行下一个
		if(count($settings) <= 0){
            $commdata['AMOUNT'] = 0;
            $commdata['STATUS'] = -1;//-1不必发放；-3待时间到了发放；-2（已废除）；0需要实时发放；1已经发放
            $commdata['MEMO'] = '账户' . $winnerUIN . '，无返忽略：' . $calGroup . '、LV（' . $calLevel . '）、' . $order['SYMBOL'];
			$commdata['LEVEL_SCALE'] = 1;
            $result = $DB->insert("t_sale_commission_amount0",$commdata);
			
            $this->log_("订单" . $order['TICKET'] . '，LV=' . $calLevel . '，' . $winnerUIN . "，无返佣配置，无法返佣，已忽略：" . $result);
			
			return $calArr;
		}

		foreach ($settings as $key => $setting) {
			//同一个mt服务器里的同一笔订单
			//只能发放给：返佣模式（获得者决定返佣模式，所以返佣可以用获得者代替为检索条件）+返佣模式，一笔或多笔（代理或员工的内佣可能多笔，因为它还多包括了一笔团队返佣）
			//但是多笔的情况，它也是在一次的运行过程中，直接多笔发放，所以只要找到该订单：返佣模式+返佣模式 存在，就不必发放
			
			/*$tempWhere_ = "where TICKET = '{$order['TICKET']}' and COMM_MEMBER_ID = '{$cWinner['id']}' and SERVER_ID = '{$server['id']}' and COMMISSION_PATTERN = '{$setting['BONUS_TYPE']}'";
			$dbsale = $DB->getDRow("select * from t_sale_commission {$tempWhere_}");
			if ($dbsale) {
				$this->log_("订单" . $order['TICKET'] . ' 给用户 ' . $winnerUIN . " 已经过返佣，不能重复返佣，已忽略");
				continue;
			}
			$dbsale = $DB->getDRow("select * from t_sale_commission_amount0 {$tempWhere_}");
			if ($dbsale) {
				$this->log_("订单" . $order['TICKET'] . ' 给用户 ' . $winnerUIN . " 已经过返佣，不能重复返佣，已忽略0");
				continue;
			}*/
			
			$this->log_("订单详情：接受返佣账户：" . $winnerUIN . "，下级用户类型：" . $childUser['userType']);

			$cval = 0;//返佣金额
			$formula = '';//返佣计算公式
			$scale = 1;//返佣折扣，100%
			
			$commdata['SETTING_ID'] = $setting['ID'];
			$commdata['COMM_TYPE'] = $setting['COMM_TYPE'];
			$commdata['LEVEL_SCALE'] = $scale;
			$commdata['COMMISSION_PATTERN'] = $setting['BONUS_TYPE'];
			
			if ($cWinner['userType'] == 'direct') {
				//如果获得佣金的是 直客
				$this->log_("获得佣金的是 直客");
				
				//不必判断是否终止返佣，因为获取返佣配置时已经判断过

				$calType = $setting['CAL_TYPE_ZK'];
				$fixVal = $setting['CAL_NUM_ZK'];
				$tempCal = $this->calBounsNum($calType,$fixVal,$scale,$cWinner['userType'],$order);
				$cval = $tempCal['cval'];
				$formula = $tempCal['formula'];

				$commdata['LEVEL_FIXED'] = $fixVal;
				$commdata['bonusKey'] = '直接客户返佣';
				
				//直客：有返佣账户、有mt账户、净值符合等，符合才能返
				$lessamount = $this->queryLessAmount($cWinner['id'], $server['id']);
				if ($lessamount['status'] != 1) {
					$commdata['AMOUNT'] = 0;
					$commdata['STATUS'] = -1;
					$commdata['MEMO'] = $lessamount['msg'];
					$result = $DB->insert("t_sale_commission_amount0",$commdata);
					
					$this->log_($lessamount['msg'] . '：' . $result);
				}else{
					$commdata['AMOUNT'] = $cval;
					if ($this->calc_remind_commission_term == 0) {
						//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
						$commdata['STATUS'] = 0;
					} else {
						$commdata['STATUS'] = -3;
					}
					
					$commdata['FORMULA'] = $formula;
					$commdata['MT4_TIME'] = $mt4time;
					if($commdata['AMOUNT'] == 0){
						$result = $DB->insert("t_sale_commission_amount0",$commdata);
					}else{
						$result = $DB->insert("t_sale_commission",$commdata);
					}
					
					$this->log_("订单" . $order['TICKET'] . "计算佣金，" . $winnerUIN . '返佣' . $commdata['AMOUNT'] . "，公式=" . $commdata['FORMULA'] . "，status=" . $commdata['STATUS'] . '：' . $result);
				}
				
				//直客 不会产生级差
			} elseif ($cWinner['userType'] == 'agent' || $cWinner['userType'] == 'member') {
				//如果获得佣金的是代理或员工
				$this->log_("获得佣金的是 " . $cWinner['userType']);
				
				//不必判断是否终止返佣，因为获取返佣配置时已经判断过
				
				//内外佣
				if($setting['BONUS_TYPE'] == 1){
					//外佣（符合等级或层级直接获得佣金，没有其它运算）
					
					if($floor == 1 || $tFloor == 1){
						$this->log_("外佣，拿：直客");
						
						//拿：直客
						$calType = $setting['CAL_TYPE_ZK'];
						$fixVal = $setting['CAL_NUM_ZK'];
						$tempCal = $this->calBounsNum($calType,$fixVal,$scale,$cWinner['userType'],$order);
						$cval = $tempCal['cval'];
						$formula = $tempCal['formula'];
						
						$commdata['LEVEL_FIXED'] = $fixVal;
						$commdata['bonusKey'] = '外佣-直接客户返佣';
						
						$commdata['AMOUNT'] = $cval;
						if ($this->calc_remind_commission_term == 0) {
							//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
							$commdata['STATUS'] = 0;
						} else {
							$commdata['STATUS'] = -3;
						}
						
						$commdata['FORMULA'] = $formula;
						$commdata['MT4_TIME'] = $mt4time;
						if($commdata['AMOUNT'] == 0){
							$result = $DB->insert("t_sale_commission_amount0",$commdata);
						}else{
							$result = $DB->insert("t_sale_commission",$commdata);
						}
					}else{
						$this->log_("外佣，拿：直客，本层非直客：floor=" . $floor . "，tFloor=" . $tFloor);
					}
					
					//外佣 不会产生级差
				}else{
					//内佣
					
					//floor是按层级，假如直客不拿自己返佣的情况
					if($floor == 1 || $tFloor == 1){
						//拿：直客
						$this->log_("内佣，拿：直客：" . $floor);
						
						$calType = $setting['CAL_TYPE_ZK'];
						$fixVal = $setting['CAL_NUM_ZK'];
						$tempCal = $this->calBounsNum($calType,$fixVal,$scale,$cWinner['userType'],$order);
						$cval = $tempCal['cval'];
						$formula = $tempCal['formula'];
						
						$commdata['LEVEL_FIXED'] = $fixVal;
						$commdata['bonusKey'] = '内佣-直接客户返佣';
						
						$commdata['AMOUNT'] = $cval;
						if ($this->calc_remind_commission_term == 0) {
							//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
							$commdata['STATUS'] = 0;
						} else {
							$commdata['STATUS'] = -3;
						}
						
						$commdata['FORMULA'] = $formula;
						$commdata['MT4_TIME'] = $mt4time;
						if($commdata['AMOUNT'] == 0){
							$result = $DB->insert("t_sale_commission_amount0",$commdata);
						}else{
							$result = $DB->insert("t_sale_commission",$commdata);
						}
						
						$this->log_("写入ID：" . $result);
						
						//用于团队返佣计算
						if($cval >= 0){
							$calArr[$cWinner['userType']]['lastBounsNum'] = $cval;
						}else{
							$calArr[$cWinner['userType']]['lastBounsNum'] = 0;
						}
						$this->log_("用于团队返佣计算：" . $calArr[$cWinner['userType']]['lastBounsNum']);
						
						//如果是按盈利百分比，又是分润，要扣客户
						if($cval > 0 && $calType == 'WIN' && $this->config_CALC_WIN_PS_TYPE > 0){
							$dDirectSqlKVArr = $commdata;
							$dDirectSqlKVArr['AMOUNT'] = -$cval;
							$dDirectSqlKVArr['MEMO'] = "按盈利返佣（分润）";
        					$dDirectSqlKVArr['COMM_MEMBER_ID'] = $order['member_id'];
							$dDirectSqlKVArr['dDirectAMId'] = $cWinner['id'];
							$dDirectSqlKVArr['dDirectSId'] = $result;
							$dDirectSqlKVArr['LEVEL_NAME'] = '(分润)';
							$tempId = $DB->insert("t_sale_commission",$dDirectSqlKVArr);
							
							$this->log_("订单" . $data['TICKET'] . "分润自客户，需要扣除客户：" . $dDirectSqlKVArr['AMOUNT'] . '，ID：' . $tempId);
						}
						
						//-------------------------------------------
						
						//直客肯定没有团队返佣，因为他是第一个拿的
						
						//-------------------------------------------
						
						//首次拿，记录用于计算级差的相关数据
						$calArr[$cWinner['userType']]['lastJC'] = array(
							'calType'=>$calType,
							'fixVal'=>$fixVal,
							'scale'=>$scale,
							//'userType'=>$cWinner['userType'],
							//'order'=>$order,
						);
						
						$this->log_("记录级差数据");
					}else{					
						//拿：间接客户 和 团队
						//补增：级差
						
						//拿：级差
						if($setting['CAL_NUM_JC'] > 0){
							//上级的级差类型 必须与 下级（产生佣金那个订单第1次计算直接客户佣金时的佣金类型）的级差类型一致
							if($calArr[$cWinner['userType']]['lastJC']){
								if($calArr[$cWinner['userType']]['lastJC']['calType'] == $setting['CAL_TYPE_JC']){
									//判断本级差等级 大于 下级
									if($setting['CAL_NUM_JC'] > $calArr[$cWinner['userType']]['lastJC']['fixVal']){
										//计算差价
										$calType = $setting['CAL_TYPE_JC'];
										$fixVal = $setting['CAL_NUM_JC'];
										
										$tempCal = $this->calBounsNum($calType,$fixVal-$calArr[$cWinner['userType']]['lastJC']['fixVal'],$scale,$cWinner['userType'],$order);							
										$cval = $tempCal['cval'];
										$formula = $tempCal['formula'];
										
										$commdata['LEVEL_FIXED'] = $fixVal;
										$commdata['bonusKey'] = '内佣-级差';
										
										$commdata['AMOUNT'] = $cval;
										if ($this->calc_remind_commission_term == 0) {
											//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
											$commdata['STATUS'] = 0;
										} else {
											$commdata['STATUS'] = -3;
										}
										
										$commdata['FORMULA'] = $formula;
										$commdata['MT4_TIME'] = $mt4time;
										if($commdata['AMOUNT'] == 0){
											$result = $DB->insert("t_sale_commission_amount0",$commdata);
										}else{
											$result = $DB->insert("t_sale_commission",$commdata);
										}
										
										$this->log_("写入ID：" . $result);
										
										//更新相关数据，记录用于计算级差的相关数据
										$calArr[$cWinner['userType']]['lastJC'] = array(
											'calType'=>$calType,
											'fixVal'=>$fixVal,
											'scale'=>$scale,
											//'userType'=>$cWinner['userType'],
											//'order'=>$order,
										);
									}else{
										$this->log_("级差数值不足（本上级" . $setting['CAL_NUM_JC'] . "<=" . $calArr[$cWinner['userType']]['lastJC']['fixVal'] . "）");
									}
								}else{
									$this->log_("上下级级差类型不同（本上级" . $setting['CAL_TYPE_JC'] . "!=" . $calArr[$cWinner['userType']]['lastJC']['calType'] . "）");
								}
							}else{
								$this->log_("之前的计算数据无级差数据");
							}
						}else{
							$this->log_("非级差");
						}
						
						
						//看怎么拿
						$tempCalJJ = 0;
						$tempCalGroup = 0;
						if($setting['CAL_NUM_JJ_2'] > 0 || $setting['CAL_NUM_JJ_1'] > 0 || $setting['CAL_NUM_JJ_0'] > 0){
							$tempCalJJ = 1;
						}
						if($setting['CAL_NUM_GROUP_2'] > 0 || $setting['CAL_NUM_GROUP_1'] > 0 || $setting['CAL_NUM_GROUP_0'] > 0){
							$tempCalGroup = 1;
						}
						
						$this->log_("内佣，拿：间接客户 和 团队：floor=" . $floor . '，tFloor=' . $tFloor . '，拿间接：' . $tempCalJJ . '，拿团队：' . $tempCalGroup);
						
						//拿：间接客户
						if($tempCalJJ > 0){
							//与最后一个 代理或员工 比较等级
							if($calArr[$cWinner['userType']]['lastSUser']){								
								//还有一种情况，系统设置为与 本订单运算过程中相关的 代理或员工 最大等级比较
								if($this->config_BONUS_AGENT_UD_CS == 'max'){
									$udcsLv = $calArr[$cWinner['userType']]['lineMaxLv'];
									
									$this->log_("与最后一个 代理或员工 比较等级：uid=" . $calArr[$cWinner['userType']]['lastSUser']['id'] . "，lineMaxLv=" . $udcsLv);
								}else{
									$udcsLv = $calArr[$cWinner['userType']]['lastSUser']['level'];
									
									$this->log_("与最后一个 代理或员工 比较等级：uid=" . $calArr[$cWinner['userType']]['lastSUser']['id'] . "，level=" . $udcsLv);
								}
							}else{
								//如果没有最后一个 代理或员工，又不是 拿直客，说明 本佣金获得者 下级存在与 本佣金获得者不是同类型的用户、系统又设置允许这样运算
								//这种情况当成 等于下级 来处理
								$this->log_("没有最后一个 代理或员工，当成 等于下级 来处理");
								
								$udcsLv = $cWinner['level'];
							}
							
							if($cWinner['level'] > $udcsLv){
								$this->log_($cWinner['level'] . '>' . $udcsLv);
								
								$calType = $setting['CAL_TYPE_JJ_2'];
								$fixVal = $setting['CAL_NUM_JJ_2'];
							}else if($cWinner['level'] < $udcsLv){
								$this->log_($cWinner['level'] . '<' . $udcsLv);
								
								$calType = $setting['CAL_TYPE_JJ_0'];
								$fixVal = $setting['CAL_NUM_JJ_0'];
							}else{
								$this->log_($cWinner['level'] . '=' . $udcsLv);
								
								$calType = $setting['CAL_TYPE_JJ_1'];
								$fixVal = $setting['CAL_NUM_JJ_1'];
							}
							
							$tempCal = $this->calBounsNum($calType,$fixVal,$scale,$cWinner['userType'],$order);							
							$cval = $tempCal['cval'];
							$formula = $tempCal['formula'];
							
							$commdata['LEVEL_FIXED'] = $fixVal;
							$commdata['bonusKey'] = '内佣-间接客户返佣';
							
							$commdata['AMOUNT'] = $cval;
							if ($this->calc_remind_commission_term == 0) {
								//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
								$commdata['STATUS'] = 0;
							} else {
								$commdata['STATUS'] = -3;
							}
							
							$commdata['FORMULA'] = $formula;
							$commdata['MT4_TIME'] = $mt4time;
							if($commdata['AMOUNT'] == 0){
								$result = $DB->insert("t_sale_commission_amount0",$commdata);
							}else{
								$result = $DB->insert("t_sale_commission",$commdata);
							}
							
							$this->log_("写入ID：" . $result);
							
							//如果是按盈利百分比，又是分润，要扣客户
							if($cval > 0 && $calType == 'WIN' && $this->config_CALC_WIN_PS_TYPE > 0){
								$dDirectSqlKVArr = $commdata;
								$dDirectSqlKVArr['AMOUNT'] = -$cval;
								$dDirectSqlKVArr['MEMO'] = "按盈利返佣（分润）";
								$dDirectSqlKVArr['COMM_MEMBER_ID'] = $order['member_id'];
								$dDirectSqlKVArr['dDirectAMId'] = $cWinner['id'];
								$dDirectSqlKVArr['dDirectSId'] = $result;
								$dDirectSqlKVArr['LEVEL_NAME'] = '(分润)';
								$tempId = $DB->insert("t_sale_commission",$dDirectSqlKVArr);
								
								$this->log_("订单" . $data['TICKET'] . "分润自客户，需要扣除客户：" . $dDirectSqlKVArr['AMOUNT'] . '，ID：' . $tempId);
							}							
							
						}
						
						//------------------------------------------------------------------
						
						//拿：团队返佣
						if($tempCalGroup > 0){
							//与最后一个 代理或员工 比较等级
							if($calArr[$cWinner['userType']]['lastSUser']){
								$this->log_("与最后一个 代理或员工 比较等级" . $calArr[$cWinner['userType']]['lastSUser']['id']);
								
								//还有一种情况，系统设置为与 本订单运算过程中相关的 代理或员工 最大等级比较
								if($this->config_BONUS_AGENT_UD_CS == 'max'){
									$udcsLv = $calArr[$cWinner['userType']]['lineMaxLv'];
								}else{
									$udcsLv = $calArr[$cWinner['userType']]['lastSUser']['level'];
								}
							}else{
								//如果没有最后一个 代理或员工，又不是 拿直客，说明 本佣金获得者 下级存在与 本佣金获得者不是同类型的用户、系统又设置允许这样运算
								//这种情况当成 等于下级 来处理
								$this->log_("没有最后一个 代理或员工，当成 等于下级 来处理");
								
								$udcsLv = $cWinner['level'];
							}
							
							if($cWinner['level'] > $udcsLv){
								$this->log_($cWinner['level'] . '>' . $udcsLv);
								
								$fixVal = $setting['CAL_NUM_GROUP_2'];
							}else if($cWinner['level'] < $udcsLv){
								$this->log_($cWinner['level'] . '<' . $udcsLv);
								
								$fixVal = $setting['CAL_NUM_GROUP_0'];
							}else{
								$this->log_($cWinner['level'] . '=' . $udcsLv);
								
								$fixVal = $setting['CAL_NUM_GROUP_1'];
							}
							
							$tempCal = $this->calBounsNum('group_win',$fixVal,$scale,$cWinner['userType'],$order,$calArr[$cWinner['userType']]['lastBounsNum']);
							$cval = $tempCal['cval'];
							$formula = $tempCal['formula'];
							
							$commdata['LEVEL_FIXED'] = $fixVal;
							$commdata['bonusKey'] = '内佣-团队返佣';
							
							$commdata['AMOUNT'] = $cval;
							if ($this->calc_remind_commission_term == 0) {
								//推荐返佣结算周期（0：实时，1：日结，2：周结，3：月结）
								$commdata['STATUS'] = 0;
							} else {
								$commdata['STATUS'] = -3;
							}
							
							$commdata['FORMULA'] = $formula;
							$commdata['MT4_TIME'] = $mt4time;
							if($commdata['AMOUNT'] == 0){
								$result = $DB->insert("t_sale_commission_amount0",$commdata);
							}else{
								$result = $DB->insert("t_sale_commission",$commdata);
							}
							
							$this->log_("写入ID：" . $result);
							
							//用于团队返佣计算
							if($cval >= 0){
								$calArr[$cWinner['userType']]['lastBounsNum'] = $cval;
							}else{
								$calArr[$cWinner['userType']]['lastBounsNum'] = 0;
							}
							
							$this->log_("用于团队返佣计算：" . $calArr[$cWinner['userType']]['lastBounsNum']);
						}//if($tempCalGroup > 0){
					}//if($floor == 1){
				}//if($setting['BONUS_TYPE'] == 1){
			}//if ($cWinner['userType'] == 'direct') {
		}//foreach
		
		return $calArr;
    }
	
	
	public function calBounsNum($calType,$fixVal,$scale,$winnerUserType,$order,$lastBounsNum = 0){
		//$calType=计算方式
		//$fixVal=数值
		
		$fixVal = $fixVal * 1;
		
		if ($calType == 'SCALE') {
			//%，交易量百分比
			$cval = round(($order['VOLUME'] / 100 * $fixVal / 100 * $scale) , 2);
			$formula = ($order['VOLUME'] / 100) . '*' . $fixVal . '%*' . $scale;
		} else if ($calType == 'POINT') {
			//pip，点值/每手/金额
			$cval = round(($order['VOLUME'] / 100 * $order['POINT_VALUE'] * $fixVal * $scale) , 2);
			$formula = ($order['VOLUME'] / 100) . '*' . $order['POINT_VALUE'] . '*' . $fixVal . '*' . $scale;
		} else if ($calType == 'WIN') {
			//%，盈利百分比
			$cval = round(($order['PROFIT'] * $fixVal / 100 * $scale) , 2);
			$formula = $order['PROFIT'] . '*' . $fixVal . '%*' . $scale;
			if($winnerUserType == 'direct'){
				if($cval <= 0){
					//直客拿的返佣如果是负数，修正为0，不扣直客的返佣
					$cval = 0;
				}
			}else{
				if($cval <= 0 && $this->config_CALC_WIN_LOSS_DEDUCT <= 0){
					//按盈利返佣（亏损时扣除代理）=不扣除，则置为0
					$cval = 0;
				}
			}
		} else if($calType == 'FIXED'){
			//$，每手/金额
			$cval = round(($order['VOLUME'] / 100 * $fixVal * $scale), 2);
			$formula = ($order['VOLUME'] / 100) . '*' . $fixVal . '*' . $scale;
		} else if($calType == 'group_win'){
			//团队返佣
			$cval = round(($lastBounsNum * $fixVal / 100 * $scale), 2);
			$formula = $lastBounsNum . '*' . $fixVal . '%*' . $scale;
		}else{
			$cval = 0;
			$formula = 'ERROR:' . $calType;
		}
		
		return array('cval'=>$cval,'formula'=>$formula);
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
		
        $balance['AMOUNT'] = -$amount;
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
                $this->log_("连接mt4api接口异常，MT4服务器入金失败，错误代码" . $inid['ret'] . ' msg:' . $inid['info']);
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
	
    public function querySymbolSetting($symbol, $account, $level, $userType, $group, $svr_id, $calArr) {
        $symbol_type = '';
        $type_name = '';
        $settings = array();
        foreach ($this->symbols as $key => $val) {
            if ($symbol == $val['symbol']) {
                $symbol_type = $val['type'];
                $type_name = $val['type_name'];
                break;
            }
        }
        $this->log_("查找：symbol=" . $symbol . " symbol_type=" . $symbol_type . " type_name=" . $type_name . " level=" . $level . " group=" . $group . "  userType=" . $userType);
        if ($symbol_type != '') {
            foreach ($this->settings as $key => $val) {
				//如果代理返佣终止，那么当本配置是发给代理的，跳过
				if($val['MODEL_TYPE'] == 'agent' && $calArr['agent']['bonusIsStop'] > 0){
					$this->log_("代理返佣终止，跳过：" . $val['ID']);
					continue;
				}
				//如果员工返佣终止，那么当本配置是发给员工的，跳过
				if($val['MODEL_TYPE'] == 'member' && $calArr['member']['bonusIsStop'] > 0){
					$this->log_("员工返佣终止，跳过：" . $val['ID']);
					continue;
				}
				//如果直客返佣终止，那么当本配置是发给直客的，跳过
				if($val['MODEL_TYPE'] == 'direct' && $calArr['direct']['bonusIsStop'] > 0){
					$this->log_("直客返佣终止，跳过：" . $val['ID']);
					continue;
				}
				
				/*var_dump($userType == $val['MODEL_TYPE']);
				var_dump(strpos($val['SYMBOL_TYPE'], ',' . $type_name . ','));
				var_dump($level == $val['LEVEL']);
				var_dump(strpos($val['GROUP_NAME'], ',' . $group . ','));
				var_dump($svr_id == $val['SERVER_ID']);
				echo '<br>';*/
				
                if ($userType == $val['MODEL_TYPE'] && //返佣模式=代理/员工/直客
					(strpos($val['SYMBOL_TYPE'], ',' . $type_name . ',') !== false) && 
					$level == $val['LEVEL'] && 
					(strpos($val['GROUP_NAME'], ',' . $group . ',') !== false) && 
					$svr_id == $val['SERVER_ID']) {
						
						//如果有设置了获得者账号
						if(intval($val['ACCOUNT']) > 0){
							//必须是这个代理商才有权获得这个提成
							if($account == $val['ACCOUNT']){
								$settings[] = $val;
							}
						}else{
							$settings[] = $val;
						}
                }
            }
        } else {
            $this->log_($symbol . "所属的交易种类不存在，请确认");
            return array();
        }
        return $settings;
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
	
    private function queryLessAmount($memberid, $svr_id) {
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
        $settings = $DB->getDTable("select * from t_sale_setting_new where STATUS = 1 order by CREATE_TIME desc");
        return $settings;
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
	
    private function setSingle($taskId, $interval,$estr = '') {
		$expired = $interval + 100 + time();
		$result  =   file_put_contents('cache/task_thread_' . $taskId . '.php',$expired);
		
		$this->log_($estr . " set task_thread:task_thread_" . $taskId . ' expired:' . $expired);
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

