<?php
class ReportModel {

    protected $dbName         = '';
    protected $tablePrefix    = 'mt4_';
    protected $tableName      = 'trades';
    //protected $tablePricesName = 'prices';
    protected $tableUsersName = 'users';
    protected $orderPrefix    = 'mt_';
    protected $orderName = 'orders';
    protected $positions = 'positions';
	
	protected $DBR;
	
	protected $ver = 4;

    public function __construct($dbName,$ver = 4) {
		//global $DBSettings;
		
        $this->dbName = $dbName;
		
		$this->ver = $ver;
		
		//$DBR = new CMySQLi();
		//$DBR->connnect($DBSettings['dbHost'],$DBSettings['dbUser'],$DBSettings['dbPwd'],$this->dbName,$DBSettings['dbPort']);
    }

    /*public function get_spread($symbol){
        $spread = M()->table($this->dbName . '.' . $this->tablePrefix . $this->tablePricesName)->where(array('SYMBOL'=>$symbol))->find();
        return $spread;
    }*/

    public function queryMT4Static($loginarr, $where) {
        $totalData = array();

        foreach ($loginarr as $key => $login) {
            $maplogin['LOGIN'] = $login;
            $maplogin['CMD']   = array('in', '0,1'); //买和卖标记
            if ($where) {
                $maplogin = array_merge($maplogin, $where);
            }

            $depositModel = new DepositModel($this->dbName);
            $user         = $depositModel->getuser($login);
            if ($user && $maplogin) {
                //查询交易手数，交易订单数，交易盈利金额
                $totalDatas         = $this->where($maplogin)->getField("LOGIN,sum(VOLUME) as VOLUME,COUNT(LOGIN) as TNUM,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT");
                $maplogin['CMD']    = '6'; //出入金标记
                $maplogin['PROFIT'] = array('EGT', '0'); //大于0.标识入金
                $totalINProfit      = $this->where($maplogin)->getField('LOGIN,round(sum(PROFIT),2) AS INProfit');
                //var_dump(M()->_sql());
                $maplogin['PROFIT'] = array('LT', '0'); //小于0 表示出金
                $totalOUTProfit     = $this->where($maplogin)->getField('LOGIN,round(sum(PROFIT),2) AS OUTProfit');

                $totalData[$maplogin['LOGIN']]['LOGIN']     = $login; //当前账户
                $totalData[$maplogin['LOGIN']]['TNUM']      = $totalDatas[$maplogin['LOGIN']]['TNUM']; //交易订单数
                $totalData[$maplogin['LOGIN']]['VOLUME']    = $totalDatas[$maplogin['LOGIN']]['VOLUME']; //交易手数
                $totalData[$maplogin['LOGIN']]['PROFIT']    = $totalDatas[$maplogin['LOGIN']]['PROFIT']; //盈利金额
                $totalData[$maplogin['LOGIN']]['INProfit']  = $totalINProfit[$maplogin['LOGIN']]['INProfit']; //入金金额
                $totalData[$maplogin['LOGIN']]['OUTProfit'] = $totalOUTProfit[$maplogin['LOGIN']]['OUTProfit']; //出金金额
                $totalData[$maplogin['LOGIN']]['MT4USER']   = $user; //当前账户对象
                unset($maplogin);
            } else {
                $totalData[$login]['LOGIN']     = $login; //当前账户
                $totalData[$login]['TNUM']      = 0; //交易订单数
                $totalData[$login]['VOLUME']    = 0; //交易手数
                $totalData[$login]['PROFIT']    = 0; //盈利金额
                $totalData[$login]['INProfit']  = 0; //入金金额
                $totalData[$login]['OUTProfit'] = 0; //出金金额
                $totalData[$login]['MT4USER']   = $user; //当前账户对象
            }
        }
        return $totalData;
    }

//查询已经平仓订单
    public function queryClosedOrders($loginid, $mt4server, $startcurrenttime, $endcurrenttime, $ver = 4) {
		global $DB;
        if ($ver == 5) {
            return $this->queryMt5Orders($loginid, $mt4server, $startcurrenttime, $endcurrenttime, 1);
        } else {
            $where['LOGIN']      = $loginid;
            $where['CLOSE_TIME'] = array('NEQ', '1970-01-01 00:00:00');
            $where['CLOSE_TIME'] = array('between', '' . $startcurrenttime . ',' . $endcurrenttime . '');
            $where['CMD']        = array('in', '0,1');
			
			$whereStr = cz_where_to_str($where);
			
			return $DB->getDTable("select * from " . $this->dbName . ".`mt4_trades` {$whereStr}");
        }
    }

    public function queryMt5Orders($loginid, $mt4server, $startcurrenttime, $endcurrenttime, $entry) {
		global $DB;
		
        unset($where);
        $where['Login'] = $loginid;
		$where['Action'] = array('in','0,1');
        if ($entry != 'all') {
            $where['Entry'] = $entry;
        }
//0 in  1 out
        $where['Time'] = array('between', '' . $startcurrenttime . ',' . $endcurrenttime . '');
		
		$whereStr = cz_where_to_str($where);
		
        $list                = $DB->getDTable("select * from " . $this->dbName . ".`mt5_deals` {$whereStr}");
        $newList = array();
        foreach ($list as $key => $val) {
            $order                 = array();
            $order['LOGIN']        = $val['Login'];
            $order['ContractSize'] = $val['ContractSize'];
            $order['RateProfit']   = $val['RateProfit'];
            $order['DIGITS']       = $val['Digits'];
            $order['VOLUME']       = $val['Volume'] / 100;//保持与mt4一样的倍率
            $order['CLOSE_TIME']   = $val['Time'];
            $order['SYMBOL']       = $val['Symbol'];
            $order['CMD']          = $val['Action'];
            $order['SWAPS']        = 0;
            $order['COMMISSION']   = $val['Commission'];
			$order['CLOSE_TIME']   = $val['Time'];
            $order['CLOSE_PRICE']   = $val['Price'];
            $order['TICKET'] = $val['PositionID'];
			$order['PROFIT'] = $val['Profit'];
			
            $entryTrade            = $DB->getDRow("select * from " . $this->dbName . ".`mt5_deals` where PositionID = '{$val['PositionID']}' and Entry = 0");
			$order['OPEN_TIME']   = $entryTrade['Time'];
            $order['OPEN_PRICE']   = $entryTrade['Price'];
			
            $newList[]       = $order;
        }
        return $newList;
    }

    //查询未平仓订单
    public function queryUnClosedOrders($loginid, $mt4server) {
		global $DB;
		
		if($this->ver == 5){
			$where['Login']      = $loginid;
			$where['Action']        = array('in', '0,1');
			
			$whereStr = cz_where_to_str($where);
			
			return $DB->getDTable("select * from " . $this->dbName . ".`mt5_positions` {$whereStr}");
		}else{
			$where['LOGIN']      = $loginid;
			$where['CLOSE_TIME'] = array('EQ', '1970-01-01 00:00:00');
			$where['CMD']        = array('in', '0,1');
			
			$whereStr = cz_where_to_str($where);
			
			return $DB->getDTable("select * from " . $this->dbName . ".`mt4_trades` {$whereStr}");
		}
    }

    public function queryMT4Total($loginarr, $where) {
        $totalData = array();

        return $totalData;
    }

    /**
     * 统计订单手数
     */
    public function sumVolume($loginidarr, $where = array()) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if ($where) {
			if(is_array($where)){
				$maplogin = cz_where_to_str($where);
			}else{
				$maplogin = $where;
			}
		}else{
			$maplogin = "where 1 = 1";
		}

		if($this->ver == 5){
			$maplogin .= " and `Login` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `Action` in (0,1) and `Entry` = 1"; //买和卖标记
			
			$maploginStr = $maplogin;
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = floatval($DB->getField("select sum(Volume) as VOLUME from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin .= " and `LOGIN` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `CMD` in (0,1)"; //买和卖标记
			
			$maploginStr = $maplogin;
	
			$totalDatas = floatval($DB->getField("select sum(VOLUME) as VOLUME from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));
		}
        return $totalDatas;
    }

    /**
     * 统计点差
     */
    public function sumSpread($loginidarr, $where,$type='') {
		//mt5才有？
		if ($type == 1) {
			return array();
		}else{
			return 0;
		}
		
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
        $maplogin['LOGIN'] = array('in', $loginidarr);
        $maplogin['CMD']   = array('in', '0,1'); //买和卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$maploginStr = cz_where_to_str($maplogin);
		
        if ($type == 1) {
            $totalDatas = $DB->getDTable("select sum(SPREAD_PROFIT) as SPREAD_PROFIT,LOGIN from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by `LOGIN`");
        }else{
            $totalDatas = floatval($DB->getField("select sum(SPREAD_PROFIT) as SPREAD_PROFIT from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));
        }
        return $totalDatas;
    }

    /**
     * 统计入金量
     */
    public function sumInBalance($loginidarr, $where = array()) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		
        if ($where) {
			if(is_array($where)){
				$maplogin = cz_where_to_str($where);
			}else{
				$maplogin = $where;
			}
        }else{
			$maplogin = "where 1 = 1";
		}
		
		if($this->ver == 5){
			$maplogin .= " and `Login` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `Action` = 2"; //出金标记
			$maplogin .= " and `Profit` > 0"; // 小于0为出金
			$maplogin .= " and `COMMENT` like '%Deposit%'"; // 出金注释
			
			$maploginStr = $maplogin;
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = floatval($DB->getField("select sum(Volume) as VOLUME from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin .= " and `LOGIN` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `CMD` in (6)"; //出金标记
			$maplogin .= " and `PROFIT` > 0"; // 大于0为入金
			$maplogin .= " and `COMMENT` like 'Deposit%'"; // 入金注释
	
			$totalDatas = floatval($DB->getField("select sum(PROFIT) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maplogin}"));
		}

        return $totalDatas;
    }

    /**
     * 统计入金金笔数
     */
    public function sumInBalanceCount($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($this->ver == 5){
			$maplogin['Login']   = array('in', $loginidarr);
			$maplogin['Action']     = array('in', '6'); //出入金标记
			$maplogin['Profit']  = array('gt', '0'); // 小于0为出金
			$maplogin['Comment'] = array('like', 'Deposit%'); // 入金注释
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin['LOGIN']   = array('in', $loginidarr);
			$maplogin['CMD']     = array('in', '6'); //出入金标记
			$maplogin['PROFIT']  = array('gt', '0'); // 小于0为出金
			$maplogin['COMMENT'] = array('like', 'Deposit%'); // 入金注释
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
	
			$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));
		}

        return $totalDatas;
    }

    /**
     * 统计出金量
     */
    public function sumOutBalance($loginidarr, $where = array()) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
        if ($where) {
			if(is_array($where)){
				$maplogin = cz_where_to_str($where);
			}else{
				$maplogin = $where;
			}
        }else{
			$maplogin = "where 1 = 1";
		}
		
		if($this->ver == 5){
			$maplogin .= " and `Login` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `Action` = 2"; //出金标记
			$maplogin .= " and `Profit` < 0"; // 小于0为出金
			$maplogin .= " and `COMMENT` like '%withdraw%'"; // 出金注释
			
			$maploginStr = $maplogin;
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = floatval($DB->getField("select sum(Volume) as VOLUME from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin .= " and `LOGIN` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `CMD` in (6)"; //出金标记
			$maplogin .= " and `PROFIT` < 0"; // 小于0为出金
			$maplogin .= " and `COMMENT` like 'Withdraw%'"; // 出金注释
	
			$totalDatas = floatval($DB->getField("select sum(PROFIT) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maplogin}"));
		}

        return $totalDatas;
    }

    /**
     * 统计出金金笔数
     */
    public function sumOutBalanceCount($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($this->ver == 5){
			$maplogin['Login']   = array('in', $loginidarr);
			$maplogin['Action']     = 2; //出入金标记
			$maplogin['Profit']  = array('lt', '0'); // 小于0为出金
			$maplogin['Comment'] = array('like', 'Withdraw%'); // 出金注释
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = intval($DB->getField("select count(*) as count2 from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin['LOGIN']   = array('in', $loginidarr);
			$maplogin['CMD']     = array('in', '6'); //出入金标记
			$maplogin['PROFIT']  = array('lt', '0'); // 小于0为出金
			$maplogin['COMMENT'] = array('like', 'Withdraw%'); // 出金注释
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
	
			$totalDatas = intval($DB->getField("select count(*) as count2 from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));
		}
        return $totalDatas;
    }

    /**
     * 统计总盈亏
     */
    public function sumProfit($loginidarr, $where,$server_id ='') {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
        if ($where) {
			if(is_array($where)){
				$maplogin = cz_where_to_str($where);
			}else{
				$maplogin = $where;
			}
        }else{
			$maplogin = "where 1 = 1";
		}
		
		if($this->ver == 5){
			$maplogin .= " and `Login` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `Action` in (0,1) and `Entry` = 1"; //买和卖标记
			
			$maploginStr = $maplogin;
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = floatval($DB->getField("select sum(Profit+Commission) as PROFIT from " . $this->dbName . ".`mt5_deals` {$maploginStr}"));
		}else{
			$maplogin .= " and `LOGIN` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `CMD` in (0,1)";//买卖标记
	
			$totalDatas = floatval($DB->getField("select sum(PROFIT+COMMISSION+SWAPS) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maplogin}"));
		}

        return $totalDatas;
    }

    /**
     * 统计订单笔数
     */
    public function sumCount($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if ($where) {
			if(is_array($where)){
				$maplogin = cz_where_to_str($where);
			}else{
				$maplogin = $where;
			}
        }else{
			$maplogin = "where 1 = 1";
		}
		
		if($this->ver == 5){
			$maplogin .= " and `Login` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `Action` in (0,1) and Entry = 1";//买卖标记
			
			$maplogin = str_ireplace('CLOSE_TIME','Time',$maplogin);

			$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt5_deals` {$maplogin}"));
		}else{
			$maplogin .= " and `LOGIN` in (" . implode(',',$loginidarr) . ")";
			$maplogin .= " and `CMD` in (0,1)";//买卖标记
	
			$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt4_trades` {$maplogin}"));
		}
        return $totalDatas;
    }

    /**
     * 统计未平仓订单笔数数
     */
    public function sumUncloseCount($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}		
		
        $maplogin['LOGIN'] = array('in', $loginidarr);
        $maplogin['CMD']   = array('in', '0,1'); //买卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$maploginStr = cz_where_to_str($maplogin);

        $totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));

        return $totalDatas;
    }

    /**
     * 统计未平仓订单手数
     */
    public function sumUncloseVolume($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}		
		
		if($this->ver == 5){
			$maplogin['Login'] = array('in', $loginidarr);
			$maplogin['Action']   = array('in', '0,1'); //买卖标记
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
			$maploginStr = str_ireplace('CLOSE_TIME','Time',$maploginStr);
	
			$totalDatas = floatval($DB->getField("select sum(Volume) as VOLUME from " . $this->dbName . ".`mt5_positions` {$maploginStr}"));
		}else{
			$maplogin['LOGIN'] = array('in', $loginidarr);
			$maplogin['CMD']   = array('in', '0,1'); //买卖标记
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
			
			$maploginStr = cz_where_to_str($maplogin);
	
			$totalDatas = floatval($DB->getField("select sum(VOLUME) as VOLUME from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));
		}
		


        return $totalDatas;
    }

    /**
     * 统计未平仓订单总金额
     */
    public function sumUncloseAmount($loginidarr, $where) {
		global $DB;
		
		if(!$loginidarr){
			return 0;
		}
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}		
		
        $maplogin['LOGIN'] = array('in', $loginidarr);
        $maplogin['CMD']   = array('in', '0,1'); //买卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$maploginStr = cz_where_to_str($maplogin);

        $totalDatas = floatval($DB->getField("select sum(PROFIT+COMMISSION+SWAPS) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maploginStr}"));

        return $totalDatas;
    }

    /**
     * 获取跟单盈利情况
     * @param type $ticketarray
     */
    public function getVolumeByTicket($ticketarray) {
        $map['CMD']        = array('in', '0,1');
        $map['TICKET']     = array('in', $ticketarray);
        $map['CLOSE_TIME'] = array('gt', '1970-01-01 00:00:00');
        $total             = $this->where($map)->getField("sum(PROFIT) as PROFIT");
        return $total ? $total : 0;
    }

    /**
     * 按照注释查找订单号
     * @param unknown $cmd $comment
     */

    public function getTicketByComment($cmd, $comment, $ver, $loginid) {
		global $DB;
		
		if ($ver == 5) {
			$where = "where `Action` = '{$cmd}'";
			$where .= " and `Comment` = '{$comment}'";
			if ($loginid) {
				$where .= " and `Login` = '{$loginid}'";
			}
	
			return $DB->getDRow("select * from " . $this->dbName . ".`mt5_deals` {$where}");
		}else{
			$where = "where `CMD` = '{$cmd}'";
			$where .= " and `COMMENT` = '{$comment}'";
			if ($loginid) {
				$where .= " and `LOGIN` = '{$loginid}'";
			}
	
			return $DB->getDRow("select * from " . $this->dbName . ".`mt4_trades` {$where}");
		}
    }

    /*
     * 导出报表
     * $datalist 表格数据集支持二位数组
     * $title  表格列标题
     * $filename  导出文件名称
     * $worksheet 表格sheet名称
     */

    function exportexcel($datalist = array(), $title = array(), $filename = 'report', $worksheet) {
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        foreach ($title as $key => $value) {
            //导出xls 开始
            if ($key == 0) {
                $objPHPExcel->setActiveSheetIndex($key)->setTitle($worksheet[$key]);
            } else {
                //创建第二个工作表
                $msgWorkSheet = new PHPExcel_Worksheet($objPHPExcel, $worksheet[$key]); //创建一个工作表
                $objPHPExcel->addSheet($msgWorkSheet); //插入工作表
            }

            $n = 0;
            for ($i = 'A'; $i != 'Y'; $i++) {
                $objPHPExcel->setActiveSheetIndex($key)->setCellValue($i . '1', $value[$n]);
                $n++;
            }
            $m = 2;
            foreach ($datalist[$key] as $k => $v) {
                $vals = array_values($v);
                $j    = 0;
                ob_clean(); //关键
                flush(); //关键
                for ($i = 'A'; $i != 'Y'; $i++) {
                    $objPHPExcel->setActiveSheetIndex($key)->setCellValue($i . ($m + $k), $vals[$j]);
                    $objPHPExcel->setActiveSheetIndex($key)->setCellValueExplicit($i . ($m + $k), $vals[$j]);
                    $j++;
                }
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");
        ob_clean(); //关键
        flush(); //关键
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $objWriter->save('php://output');
        exit;
    }

 //查询总计数量
 	//cz未被调用过
    public function queryEarnestAllStatic($loginarr, $where, $earnest, $ver) {
        $totalData = array();
        $maplogin['CMD'] = array('in', '0,1'); //买和卖标记
        $maplogin['LOGIN'] = array('in', $loginarr);
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }

        $depositModel = new DepositModel($this->dbName);

        $user = $depositModel->field("sum(BALANCE) as BALANCE ,sum(EQUITY) as EQUITY")->where(array('LOGIN' => array('in', $loginarr)))->find();
      $t = C("DB_PREFIX");
        if ($user && $maplogin) {
            //查询交易手数，交易订单数，交易盈利金额
            if ($ver == 5) {
                $mapslogin=$maplogin;
                unset($mapslogin['LOGIN']);
                unset($mapslogin['CLOSE_TIME']);
                $mapslogin['a.LOGIN'] = array('in', $loginarr);
            
                //查询交易手数，交易订单数，交易盈利金额
                //仅查询交易的手数
                $filed = "'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(SWAPS),2) as SWAPS,sum(Fee) as FEE";
                $sql = $this->join("a inner join {$t}earnest_dealer_ticket b on a.Ticket=b.Ticket")->field("a.*,b.Fee")->where($mapslogin)->group("b.Ticket")->buildSql();
                $totalDatas = M()->query("select {$filed} from {$sql}a");
                $totalDatas=$totalDatas[0];
                $maplogin['LOGIN'] = array('in', $loginarr);    
                $maplogin['CMD'] = '6'; //出入金标记
                $maplogin['PROFIT'] = array('EGT', '0'); //大于0.标识入金
                $totalINProfit = $this->where($maplogin)->group("LOGIN")->field('LOGIN,round(sum(PROFIT),2) AS INProfit')->find();
                //var_dump(M()->_sql());
                $maplogin['PROFIT'] = array('LT', '0'); //小于0 表示出金
                $totalOUTProfit = $this->where($maplogin)->group("LOGIN")->field('LOGIN,round(sum(PROFIT),2) AS OUTProfit')->find();
                //持仓

                unset($maplogin['PROFIT']);
                unset($maplogin['CLOSE_TIME']);
                unset($maplogin['CMD']);
                $mt5_postion = new Mt5_positionsModel(session('user.mt4dbname'));
                $totalFloatProfit = $mt5_postion->where($maplogin)->field('Login as LOGIN,round(sum(Profit),2) AS FloatProfit,sum(Volume) as FloatVOLUME,sum(Storage) as SWAPS')->find();
            } else {
                $totalDatas = $this->where($maplogin)->field("sum(VOLUME) as VOLUME,COUNT(LOGIN) as TNUM,sum(PROFIT) as PROFITS,sum(COMMISSION) as COMMISSION,sum(SWAPS) as SWAPS,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT")->find();

                $maplogin['CMD'] = '6'; //出入金标记
                $maplogin['PROFIT'] = array('EGT', '0'); //大于0.标识入金
                $totalINProfit = $this->where($maplogin)->field('round(sum(PROFIT),2) AS INProfit')->find();
                //var_dump(M()->_sql());
                $maplogin['PROFIT'] = array('LT', '0'); //小于0 表示出金
                $totalOUTProfit = $this->where($maplogin)->Field('round(sum(PROFIT),2) AS OUTProfit')->find();
                //持仓
                $maplogin['CMD'] = array('in', '0,1'); //买和卖标记
                unset($maplogin['PROFIT']);
                $maplogin['CLOSE_TIME'] = "1970-01-01 00:00:00";
                $totalFloatProfit = $this->where($maplogin)->Field('round(sum(PROFIT),2) AS FloatProfit,sum(VOLUME) as FloatVOLUME')->find();
            }
            $totalCommission=M("earnest_dealer_commission")->where(array('ServerId'=>$earnest['ServerId'],'EarnestLogin'=>$earnest['Login']))->getField('sum(Amount)');
            $totalData['LOGIN']           = $earnest['Login']; //当前账户
            $totalData['TNUM']            = $totalDatas['TNUM']; //交易订单数
            $totalData['VOLUME']          = round($totalDatas['VOLUME'], 2); //交易手数
            $totalData['PROFIT']          = round($totalDatas['PROFIT'], 2); //盈利金额
            $totalData['INProfit']        = round($totalINProfit['INProfit'], 2); //入金金额
            $totalData['PROFITS']         = round($totalDatas['PROFITS'], 2); //订单交易盈利
            $totalData['COMMISSION']      = round($totalDatas['COMMISSION'], 2); //外佣
            $totalData['SWAPS']           = $ver == 5?round($totalFloatProfit['SWAPS'], 2):round($totalDatas['SWAPS'], 2); //利息
            $totalData['OUTProfit']       = round($totalOUTProfit['OUTProfit'], 2); //出金金额
            $totalData['FLOATVOLUME']     = round($totalFloatProfit['FloatVOLUME'], 2); //持仓；量
            $totalData['FLOATPROFIT']     = round($totalFloatProfit['FloatProfit'], 2); //持仓金额
            $totalData['BALANCE']         = $user['BALANCE']; //余额
            $totalData['EQUITY']          = $user['EQUITY']; //净值

            $used=M("earnest_money")->where(array('MemberId'=>$earnest['MemberId'],'ServerId'=>$earnest['ServerId'],'Status'=>1))->getField('sum(number) as number');
            $totalData['UserBlance']          = $earnest['Balance']-$used; //净值
            $fee=M("earnest_dealer_ticket")->where(array('ServerId'=>$earnest['ServerId'],'EarnestLogin'=>$earnest['Login'],'Status'=>array('eq',1)))->getField('sum(Fee)');
            $totalData['FEE']          =$fee?$fee:0; //流量费
            $totalMtCommission=M("earnest_dealer_ticket")->where(array('ServerId'=>$earnest['ServerId'],'EarnestLogin'=>$earnest['Login']))->getField('sum(CommissionAgentAmount)');
            $totalData['totalCommission'] = $totalCommission ? $totalCommission : 0;
            
            $totalData['totalMtCommission'] = $totalMtCommission ? $totalMtCommission : 0;
            unset($maplogin);
        } else {
            $totalData['LOGIN'] = $earnest['Login']; //当前账户
            $totalData['TNUM'] = 0; //交易订单数
            $totalData['VOLUME'] = 0; //交易手数
            $totalData['PROFIT'] = 0; //盈利金额
            $totalData['INProfit'] = 0; //入金金额
            $totalData['PROFITS'] = 0; //订单交易盈利
            $totalData['COMMISSION'] = 0; //外佣
            $totalData['SWAPS'] = 0; //利息
            $totalData['OUTProfit'] = 0; //出金金额
            $totalData['BALANCE'] = 0; //余额
            $totalData['EQUITY'] = 0; //净值
            $totalData['FEE']=0;
            $totalData['totalCommission'] = 0;
        }
        return $totalData;
    }


    //E1用户本金
    public function sumEquityEnd($loginidarr) {
        $maplogin['LOGIN'] = array('in', $loginidarr);
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $depositModel = new DepositModel($this->dbName);
        $sub          = $depositModel->where($maplogin)->Field("LOGIN,BALANCE")->group('LOGIN')->order('LOGIN ASC')->select(false);
        $data         = M()->table($sub . ' a')->field('LOGIN,BALANCE')->order('LOGIN ASC')->select();
        return $data;
    }

    //E0 = (E1用户本金 - 盈利率统计总盈亏)
    public function sumEquityBegining($loginidarr, $where) {
        $maplogin['a.LOGIN'] = array('in', $loginidarr);
        $map['LOGIN']        = array('in', $loginidarr);

        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $depositModel = new DepositModel($this->dbName);
        $sub          = $depositModel->field('LOGIN')->where($map)->group('LOGIN')->select(false);
        $table        = [$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName => "a"];
        $join         = $this->dbName . '.' . $this->tablePrefix . $this->tableName . ' b FORCE INDEX (INDEX_LOGIN) ON a.LOGIN = b.LOGIN';
        $sub2         = M()->table($table)->field('(round(a.BALANCE - sum(b.PROFIT + b.COMMISSION + b.SWAPS),8)) E0,a.LOGIN')->join($join, 'inner')->where($maplogin)->group('a.LOGIN')->select(false);
        $join2        = $sub2 . ' n ON m.LOGIN = n.LOGIN';

        $data = M()->table($sub . 'm left join ')->field('m.LOGIN, E0')->join($join2)->order('m.LOGIN ASC')->select();
        return $data;

    }

    //总出入金量
    //type : 0:出金、1：入金
    public function sumEarningOutBalance($loginidarr, $where, $type = 0) {
        $maplogin['LOGIN']  = array('in', $loginidarr);
        $map['LOGIN']       = array('in', $loginidarr);
        $maplogin['CMD']    = array('in', '6,7'); //出金标记
        $maplogin['PROFIT'] = $type == 1 ? ['gt', '0'] : ['lt', '0']; // 小于0为出金 ，大于0入金
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $depositModel = new DepositModel($this->dbName);

        $sub  = $depositModel->field('LOGIN')->where($map)->group('LOGIN')->select(false);
        $sub2 = $this->field('LOGIN,sum(PROFIT) AS PROFIT')->where($maplogin)->group('LOGIN')->select(false);
        $join = $sub2 . ' b ON a.LOGIN = b.LOGIN';
        $data = M()->table($sub . 'a')->field('a.LOGIN, round(b.PROFIT,8) PROFIT')->join($join)->order('a.LOGIN ASC')->select();
        return $data;
    }

    /*盈利率
     *loginid:LOGIN
     *starttime:查询开始时间
     *endtime:查询结束时间
     *limit:查询个数，默认为全部
     *sort：1：按照盈利率正序/2：按照盈利率正序倒叙
     */
	 //cz未被调用过
    public function getEarningsRate($loginid, $starttime, $endtime, $limit = '', $sort = 1,$dbname='') {
        //连接数据库
        $reportModel         = new ReportModel($dbname ? $dbname : session('user.mt4dbname') );
        $where['CLOSE_TIME'] = array(array('egt', $starttime . ' 00:00:00'), array('elt', $endtime . ' 23:59:59'));
        //E1结束净值
        $equityEnd = $reportModel->sumEquityEnd($loginid);

        //总出金量 W
        $totalOutBalance = $reportModel->sumEarningOutBalance($loginid, $where);
        //D 入金总和
        $totalInBalance = $reportModel->sumEarningOutBalance($loginid, $where, 1);
        //E0前期净值
        $equityBegining = $reportModel->sumEquityBegining($loginid, $where);
        //算法(（E1 + W）- (E0 + D) ) / (E0 + D)
        $arr = [];
        foreach ($equityEnd as $k => $v) {
            $arr[$k]['loginid']  = $v['LOGIN'];
            $arr[$k]['earnings'] = round((($v['BALANCE'] + abs($totalOutBalance[$k]['PROFIT'])) - ($equityBegining[$k]['E0'] + $totalInBalance[$k]['PROFIT'])) / ($equityBegining[$k]['E0'] + $totalInBalance[$k]['PROFIT']), 4);
        }
        //排序
        $SORT_DESC = ($sort == 1) ? 'SORT_ASC' : 'SORT_DESC';
        $sort      = [
            'direction' => $SORT_DESC, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'earnings', //排序字段
        ];
        $arrSort = [];
        foreach ($arr as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
        }
        foreach ($arr as $ke => $va) {
            if ($limit > 0 and ($ke + 1 > $limit)) {
                unset($arr[$ke]);
            }
        }

        // var_dump($arr);
        return $arr ? $arr : 0;

    }

    /**
     * 获取交易种类的统计报表  如果没设置所以 请设置交易报表的索引
     * @param type $map
     */

    public function getSymbolList($map, $ser) {
		global $DB;
		
        foreach ($map as $w => $e) {
            $maps['c.' . $w] = $e;
        }

        $maps['c.CMD']       = array('in', '0,1');
        $maps['a.server_id'] = $ser['server_id'];
        if ($ser['type']) {
            $maps['b.type'] = $ser['type'];
        }
        $maps['a.status'] = 1;
		
		$whereStr = cz_where_to_str($maps);
		//echo $whereStr;exit;
		
        // $maps['a.type'] = 0;
        $t    = C("DB_PREFIX");
        $db   = $this->dbName;
		//echo "select a.type_name,round(sum(c.COMMISSION),2) as Commission,round(sum(c.SWAPS),2) as Swaps,round(sum(c.PROFIT),2) as Profit,count(*) as cc,sum(c.VOLUME) as Volume from t_type a INNER JOIN {$t}symbol b on a.id=b.type INNER JOIN {$db}.mt4_trades c ON b.symbol=c.SYMBOL {$whereStr} group by a.id";exit;
        $list = $DB->getDTable("select a.type_name,round(sum(c.COMMISSION),2) as Commission,round(sum(c.SWAPS),2) as Swaps,round(sum(c.PROFIT),2) as Profit,count(*) as cc,sum(c.VOLUME) as Volume from t_type a INNER JOIN {$t}symbol b on a.id=b.type INNER JOIN {$db}.mt4_trades c ON b.symbol=c.SYMBOL {$whereStr} group by a.id");

        return $list;
    }

    //////////////start//////////////////
    // 风控系统专用
    /**
     * 总出入金量
     * type : 0:出金、1：入金
     */
    public function sumRiskEarningOutBalance($loginidarr, $where, $type = 0, $count = '', $status = '',$server_id='',$new = '') {
		global $DB;

        if (empty($loginidarr)) {
            $loginidarr = '0';
        }
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($this->ver == 5){
			$maplogin = "where `Login` in (" . implode(',',$loginidarr) . ")";
	
			$maplogin .= " and Action in (2,3)"; //出金标记
			if ($status == '') {
				if($type == 1){
					$maplogin .= " and Profit > 0"; // 小于0为出金 ，大于0入金
					$maplogin .= " and Comment like 'Deposit%'"; // 0出金 1入金
				}else{
					$maplogin .= " and Profit < 0"; // 小于0为出金 ，大于0入金
					$maplogin .= " and Comment like 'Withdraw%'"; // 0出金 1入金
				}
			} else {
				if ($type == 1) {
					$maplogin .= " and Profit > 0";
					$comment = 'Deposit';
				} else {
					$maplogin .= " and Profit < 0";
					$comment = 'Withdraw';
				}
				$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$server_id}' and status = 1");
				if ($status == 1) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= " and Comment like '{$comment} %'";
					}else{
						$maplogin .= " and Comment like '{$comment} maxib#%'";
					}
	
				} else {
					if ($mtserver['ver'] == 5) {
						$maplogin .= "Comment like '" . $comment . "%' and Comment not like'" . $comment . " %'";
					}else{
						$maplogin .= "Comment like '" . $comment . "%' and Comment not like'" . $comment . " maxib#%'";
					}
				}
			}
	
			if ($where) {
				$where = str_ireplace('CLOSE_TIME','Time',$where);
				$maplogin .= str_ireplace('where ',' and ',$where);
			}
			if ($new == 1) {
				if ($count == 1) {
					$field = 'count(*) count,Login';
					$field2 = 'ifnull(a.count, 0) AS count,b.`Login` as LOGIN';
				} else {
					$field = 'sum(Profit) as PROFIT,LOGIN';
					$field2 = 'ifnull(a.Profit, 0) AS PROFIT,b.`Login` as LOGIN';
				}
				$sub = "(select {$field} from " . $this->dbName . ".`mt5_deals` {$maplogin} group by Login)";
				$join = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.Login = b.Login';
				$totalDatas = $DB->getDTable("select {$field2} from {$sub} a {$join} where b.Login in (" . implode(',',$loginidarr) . ") order by b.Login DESC");
			}else{
				if ($count == 1) {
					$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt5_deals` {$maplogin}"));
				} else {
					$totalDatas = $DB->getField("select sum(Profit) as PROFIT from " . $this->dbName . ".`mt5_deals` {$maplogin}");
				}
			}
		}else{
			$maplogin = "where `LOGIN` in (" . implode(',',$loginidarr) . ")";
	
			$maplogin .= " and CMD in (6,7)"; //出金标记
			if ($status == '') {
				if($type == 1){
					$maplogin .= " and PROFIT > 0"; // 小于0为出金 ，大于0入金
					$maplogin .= " and COMMENT like 'Deposit%'"; // 0出金 1入金
				}else{
					$maplogin .= " and PROFIT < 0"; // 小于0为出金 ，大于0入金
					$maplogin .= " and COMMENT like 'Withdraw%'"; // 0出金 1入金
				}
			} else {
				if ($type == 1) {
					$maplogin .= " and PROFIT > 0";
					$comment = 'Deposit';
				} else {
					$maplogin .= " and PROFIT < 0";
					$comment = 'Withdraw';
				}
				$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$server_id}' and status = 1");
				if ($status == 1) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= " and COMMENT like '{$comment} %'";
					}else{
						$maplogin .= " and COMMENT like '{$comment} maxib#%'";
					}
	
				} else {
					if ($mtserver['ver'] == 5) {
						$maplogin .= "COMMENT like '" . $comment . "%' and COMMENT not like'" . $comment . " %'";
					}else{
						$maplogin .= "COMMENT like '" . $comment . "%' and COMMENT not like'" . $comment . " maxib#%'";
					}
				}
			}
	
			if ($where) {
				$maplogin .= str_ireplace('where ',' and ',$where);
			}
			if ($new == 1) {
				if ($count == 1) {
					$field = 'count(*) count,LOGIN';
					$field2 = 'ifnull(a.count, 0) AS count,b.`LOGIN`';
				} else {
					$field = 'sum(PROFIT) as PROFIT,LOGIN';
					$field2 = 'ifnull(a.PROFIT, 0) AS PROFIT,b.`LOGIN`';
				}
				$sub = "(select {$field} from " . $this->dbName . ".`mt4_trades` {$maplogin} group by LOGIN)";
				$join = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
				$totalDatas = $DB->getDTable("select {$field2} from {$sub} a {$join} where b.LOGIN in (" . implode(',',$loginidarr) . ") order by b.LOGIN DESC");
			}else{
				if ($count == 1) {
					$totalDatas = intval($DB->getField("select count(*) as count1 from " . $this->dbName . ".`mt4_trades` {$maplogin}"));
				} else {
					$totalDatas = $DB->getField("select sum(PROFIT) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maplogin}");
				}
			}
		}

        if (empty($totalDatas)) {
            $totalDatas = 0;
        }
        return $totalDatas;
    }

    //出入金合并优化
    public function sumRiskALLProfit($loginidarr, $where) {
        $maplogin['LOGIN'] = ['in', $loginidarr];
        $maplogin['CMD']   = ['in', '6']; //出金标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $totalDatas = $this->field("sum( CASE WHEN `COMMENT` LIKE 'Withdraw%' AND (`PROFIT` < '0') THEN PROFIT ELSE NULL END ) AS outmoney, sum( CASE WHEN `COMMENT` LIKE 'Deposit%' AND (`PROFIT` > '0') THEN PROFIT ELSE NULL END ) AS inmoney")->where($maplogin)->find();
        return $totalDatas;

    }

    /**
     * 统计总盈亏
     *type 1：折线图
     */
    public function sumRiskProfit($loginidarr, $where, $type = '',$server_id = '') {
		global $DB;
		
        if (empty($loginidarr)) {
            $loginidarr = '0';
        }
		
		if($this->ver == 5){
			$maplogin['Login'] = array('in', $loginidarr);
			$maplogin['Action']   = array('in', '0,1'); //买卖标记
			if ($type == 1) {
				$maplogin['Entry']   = 1;
			}
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
		}else{
			$maplogin['LOGIN'] = array('in', $loginidarr);
			$maplogin['CMD']   = array('in', '0,1'); //买卖标记
			if ($where) {
				$maplogin = array_merge($maplogin, $where);
			}
		}
		
		$maploginStr = cz_where_to_str($maplogin);
		
        if ($type == 1) {
			if($this->ver == 5){
				$totalDatas = $DB->getDTable("select round(sum(Profit+Commission),2) as PROFIT,DATE_FORMAT(`Time`,'%Y/%m/%d') newtime from " . $this->dbName . ".`mt5_deals` {$maploginStr} group by newtime");
			}else{
            	$totalDatas = $DB->getDTable("select round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,DATE_FORMAT(CLOSE_TIME,'%Y/%m/%d') newtime from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by newtime");
			}
        } elseif ($type == 2) {
            $totalDatas = $DB->getField("select sum(PROFIT+COMMISSION+SWAPS+COMMISSION_AGENT) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by newtime");
        }elseif ($type == 3) {
            //浮动盈亏
			if($this->ver == 5){
				//FROM_UNIXTIME(TimeCreate)
				$totalDatas = $DB->getDTable("select round(sum(Profit),2) as PROFIT,DATE_FORMAT(`TimeCreate`,'%Y/%m/%d') newtime from " . $this->dbName . ".`mt5_positions` {$maploginStr} group by newtime");
			}else{
            	$totalDatas = $DB->getDTable("select round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,DATE_FORMAT(OPEN_TIME,'%Y/%m/%d') newtime from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by newtime");
			}
        }elseif ($type == 4) {
            $mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$server_id}' and status = 1");
            if ($mtserver['ver'] == 5) {
                $sub = "(select sum(PROFIT+COMMISSION+Storage) as total_PROFIT,round(sum(PROFIT),2) as PROFIT,LOGIN from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by LOGIN)";
            }else{
                $sub = "(select sum(PROFIT+COMMISSION+SWAPS) as total_PROFIT,round(sum(PROFIT),2) as PROFIT,LOGIN from " . $this->dbName . ".`mt4_trades` {$maploginStr} group by LOGIN)";
            }
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';

            $totalDatas = $DB->getDTable("select ifnull(a.total_PROFIT, 0) AS total_PROFIT,ifnull(a.PROFIT, 0) AS PROFIT, b.`LOGIN` from {$sub} a {$join} where b.LOGIN in (" . implode(',',$loginidarr) . ") order by b.LOGIN DESC");
        } else {
            $totalDatas = $DB->getField("select sum(PROFIT+COMMISSION+SWAPS) as PROFIT from " . $this->dbName . ".`mt4_trades` {$maploginStr}");
        }

        if (empty($totalDatas)) {
            $totalDatas = 0;
        }
        return $totalDatas;
    }

    /**
     * 获取订单交易种类以及盈利亏损
     */
    public function sumTransactionType($loginid = '', $mt4where, $type = '', $SYMBOL = '') {
        $where['a.CMD'] = ['in', '0,1'];
        if ($loginid) {
            $where['a.LOGIN'] = ['in', $loginid];
        }
        if ($mt4where) {
            $where = array_merge($where, $mt4where);
        }

        if ($type == 1) {
            $field = 'SYMBOL';
            $list  = $this->field($field)->where($where)->group('SYMBOL')->select();

        }

        if ($type != 1) {
            $where['a.CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];

            if ($SYMBOL) {
                $where['a.SYMBOL'] = $SYMBOL;
            }
            $table      = [$this->dbName . '.' . $this->tablePrefix . $this->tableName => "a"];
            $join       = 't_sale_commission b ON a.TICKET = b.TICKET';
            $totalDatas = M()->table($table)->join($join, 'left')->where($where)->getField('sum(b.AMOUNT) COMMISSION');
        }
        return $totalDatas;

    }

    /**
     * 获取平均持仓时间
     */

    public function averageTime($login, $mt4where, $type = '') {

        $where['LOGIN']      = ['in', $login];
        $where['CMD']        = ['in', '0,1'];
        $where['CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];
        if ($mt4where) {
            $where = array_merge($where, $mt4where);
        }

        if ($type == 1) {
            $field  = '(CLOSE_PRICE - OPEN_PRICE) rice';
            $field1 = 'avg(rice) rice';
        } else {
            $field  = '(CLOSE_TIME - OPEN_TIME) time';
            $field1 = 'avg(time) time';
        }
        $sub         = $this->field($field)->where($where)->select(false);
        return $list = M()->table($sub . 'a')->field($field1)->find();

    }

    /**
     * 交易订单总数
     */

    public function tradingOrder($login, $where) {
        $maplogin['LOGIN']  = ['in', $login];
        $maplogin['CMD']    = ['in', '0,1'];
        $maplogin['PROFIT'] = ['neq', 0];

        $maplogin['CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }

        return $sub = $this->field('count(*) total')->where($maplogin)->find();

    }

    /**
     * 获取交易盈亏数
     *TYPE : 1：盈、null：亏
     */

    public function getProfitLoss($login, $type = '', $mt4where) {
		global $DB;
		
		if($this->ver == 5){
			if ($type != 2) {
				if ($type == 1) {
					$where['Profit'] = array('gt', 0);
				} else {
					$where['Profit'] = array('lt', 0);
				}
			}
			$where['Login']      = array('in', $login);
			$where['Action']        = array('in', '0,1');
			$where['Entry']   = 1;
			if ($mt4where) {
				$where = array_merge($where, $mt4where);
			}
			
			$whereStr = cz_where_to_str($where);
	
			return $sub = $DB->getDRow("select round(sum(Profit),2) total,count(*) count from " . $this->dbName . ".`mt5_deals` {$whereStr}");
		}else{
			if ($type != 2) {
				if ($type == 1) {
					$where['PROFIT'] = array('gt', 0);
				} else {
					$where['PROFIT'] = array('lt', 0);
				}
			}
			$where['LOGIN']      = array('in', $login);
			$where['CMD']        = array('in', '0,1');
			$where['CLOSE_TIME'] = array('neq', '1970-01-01 00:00:00');
			if ($mt4where) {
				$where = array_merge($where, $mt4where);
			}
			
			$whereStr = cz_where_to_str($where);
	
			return $sub = $DB->getDRow("select round(sum(PROFIT),2) total,count(*) count from " . $this->dbName . ".`mt4_trades` {$whereStr}");
		}
    }

    /**
     * 获取日交易笔数
     *type 0 OR null：买入；1：卖出
     */

    public function getDayTransactionNum($login, $mt4where, $type = '') {
        if ($type == 1) {
            $cmd = '1'; //卖出
        } else {
            $cmd = '0'; //买入
        }
        $where['LOGIN']      = ['in', $login];
        $where['CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];
        if ($mt4where) {
            $where = array_merge($where, $mt4where);
        }
        $list = $this->field("DATE_FORMAT(CLOSE_TIME, '%Y/%m/%d') newtime")->where($where)->group('newtime')->select(false);

        $where['CMD'] = ['in', $cmd];

        $sub   = $this->field("VOLUME / 100 VOLUME, DATE_FORMAT(CLOSE_TIME, '%Y/%m/%d') newtime")->where($where)->select(false);
        $list2 = M()->table($sub . 'a')->field('COUNT(*) much, round(sum(VOLUME),2) hand ,newtime')->group('newtime')->select(false);

        $join = $list2 . ' t2 ON t1.newtime = t2.newtime';
        $data = M()->table($list . 't1')->field('t1.newtime, much, hand')->join($join)->select();
        return $data;
    }

    /**
     * 获取日交易量 笔数 盈亏
     *type 0 ：买入；1：卖出
     */

    public function getDayOneNum($login, $mt4where, $type = '') {
        if ($type == 1) {
            $cmd = '1'; //卖出
        } else {
            $cmd = '0'; //买入
        }
        $where['LOGIN']      = ['in', $login];
        $where['CLOSE_TIME'] = ['gt', '1970-01-01 00:00:00'];
        if ($mt4where) {
            $where = array_merge($where, $mt4where);
        }
        $list = $this->field("DATE_FORMAT(CLOSE_TIME, '%Y/%m/%d') newtime")->where($where)->group('newtime')->select(false);

        $where['CMD'] = ['in', $cmd];

        $sub   = $this->field("VOLUME / 100 VOLUME, PROFIT,DATE_FORMAT(CLOSE_TIME, '%Y/%m/%d') newtime")->where($where)->select(false);
        $list2 = M()->table($sub . 'a')->field('COUNT(*) much, round(sum(VOLUME),2) hand,round(sum(PROFIT),2) pro,newtime')->group('newtime')->select(false);

        $join = $list2 . ' t2 ON t1.newtime = t2.newtime';
        $data = M()->table($list . 't1')->field('t1.newtime, much, hand ,pro')->join($join)->select();
        return $data;
    }

    /**
     * 统计订单手数
     */
    public function sumRiskVolume($loginidarr, $where, $type = '') {
        if ($loginidarr) {
            $maplogin['LOGIN'] = array('in', $loginidarr);
        }
        $maplogin['CMD'] = array('in', '0,1'); //买和卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        if ($type == 1) {
            $sub = $this->where($maplogin)->Field("count(*) count,sum(VOLUME) as VOLUME,LOGIN")->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $map['b.LOGIN'] = ['in', $loginidarr];

            $totalDatas = M()->table($sub.'a')->join($join)->where($map)->field('ifnull(a.count, 0) AS count, ifnull(a.VOLUME, 0) AS VOLUME,b.`LOGIN`')->order('b.LOGIN DESC')->select();
        }else{
            $totalDatas = $this->where($maplogin)->Field("count(*) count,sum(VOLUME) as VOLUME")->find();
        }
        return $totalDatas;
    }

    /**
     * 获取所有持仓订单
     */

    public function getPositionOrder($loginidarr, $where = '', $type = '', $map = '') {
        $maplogin['LOGIN']      = array('in', $loginidarr);
        $maplogin['CMD']        = ['in', '0,1']; //买和卖标记
        $maplogin['CLOSE_TIME'] = "1970-01-01 00:00:00";
        // if ($where) {
        //     $maplogin = array_merge($maplogin, $where);
        // }

        if ($type == 1) {
            if ($map) {
                $maplogin['OPEN_TIME'] = $map;
            }
            $totalDatas = $this->where($maplogin)->getField('sum(VOLUME) as VOLUME');
        } elseif ($type == 2) {
            $totalDatas = $this->Field('sum(PROFIT) momey,sum(VOLUME) as VOLUME')->where($maplogin)->find();

        } elseif ($type == 3) {
            $totalDatas = $this->Field('sum(PROFIT+COMMISSION+SWAPS) momey')->where($maplogin)->find();
        } elseif ($type == 4) {
            $sub = $this->Field('sum(PROFIT+COMMISSION+SWAPS) momey,LOGIN')->where($maplogin)->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $whereee['b.LOGIN'] = ['in', $loginidarr];
            $totalDatas = M()->table($sub.'a')->join($join)->where($whereee)->field('ifnull(a.momey, 0) AS momey,b.`LOGIN`')->order('b.LOGIN DESC')->select();
        } else {
            $totalDatas = $this->Field('LOGIN,PROFIT,SWAPS,OPEN_TIME')->where($maplogin)->select();
        }

        return $totalDatas;
    }

    /**
     * 获取外佣，利息
     */

    public function getCommission($loginidarr, $where, $type = '',$start = '') {
        // $maplogin['LOGIN'] = array('in', $loginidarr);
        // $maplogin['CMD']   = array('in', '0,1'); //买和卖标记
        // if ($where) {
        //     $maplogin = array_merge($maplogin, $where);
        // }
        if ($type == 1 and $where) {
            $maplogin['LOGIN'] = array('in', $loginidarr);
            $where             = array_merge($maplogin, $where);
        }
        if ($type == 2) {
            $sub = $this->Field("sum(COMMISSION) as COMMISSION,sum(SWAPS) as SWAPS,sum(COMMISSION_AGENT) as COMMISSION_AGENT,LOGIN")->where($where)->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $whereee['b.LOGIN'] = ['in', $loginidarr];
            $totalDatas = M()->table($sub.'a')->join($join)->where($whereee)->field($start.' as date,'.time().' as addtime,ifnull(a.COMMISSION, 0) AS COMMISSION,ifnull(a.SWAPS, 0) AS SWAPS,ifnull(a.COMMISSION_AGENT, 0) AS COMMISSION_AGENT,b.`LOGIN`')->order('b.LOGIN DESC')->select();

        }elseif($type == 3){
            $sub = $this->Field("sum(COMMISSION) as COMMISSION,sum(SWAPS) as SWAPS,LOGIN")->where($where)->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $whereee['b.LOGIN'] = ['in', $loginidarr];
            $totalDatas = M()->table($sub.'a')->join($join)->where($whereee)->field($start.' as date,'.time().' as addtime,ifnull(a.COMMISSION, 0) AS COMMISSION,ifnull(a.SWAPS, 0) AS SWAPS,b.`LOGIN`')->order('b.LOGIN DESC')->select();

        }else{
            $totalDatas = $this->Field("sum(COMMISSION) as COMMISSION,sum(SWAPS) as SWAPS")->where($where)->find();

        }
        return $totalDatas;
    }

    /**
     * 统计未平仓订单总金额（浮动盈亏）
     */
    public function sumRiskUncloseAmount($loginidarr, $where, $type = '') {
        if ($type == 1) {
            $maplogin['LOGIN'] = array('in', $loginidarr);

        }
        $maplogin['CMD'] = array('in', '0,1'); //买卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }

        $totalDatas = $this->where($maplogin)->getField("sum(PROFIT+COMMISSION+SWAPS) as PROFIT");
        //file_put_contents("logs\/debug\/" . date("Y-m-d") . ".log", date("Y-m-d H:i:s") . " " . M()->_sql() . PHP_EOL, FILE_APPEND);
        return $totalDatas;
    }

    /**
     * 获取赠金
     */
    public function getCreditTotal($loginidarr = '', $where, $type = '', $serverid = '') {
		global $DB;
		global $DRAdmin;

		$serverid = $serverid ? $serverid : $DRAdmin['server_id'];
        $mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$serverid}' and status = 1");

		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}

		if ($mtserver['ver'] == 5) {
			if ($loginidarr) {
				$maplogin = "where Login in (" . implode(',',$loginidarr) . ")";
			}else{
				$maplogin = "where 1 = 1";
			}
	
			if ($where) {
				if ($mtserver['ver'] == 5) {
					//这里原来 $where 是数组，现在重新整过，$where是字符串：where CLOSE_TIME ...
					$maplogin .= str_ireplace('where CLOSE_TIME',' and Time',$where);
				}else{
					$maplogin .= str_ireplace('where CLOSE_TIME',' and Time',$where);
				}
				$maplogin .= " and Action = 3";// 入金注释
			}
			if ($type == 1) {
				$sub = "(select count(*) count,sum(Profit) as PROFIT,Login from ".$this->dbName . ".mt5_deals {$maplogin} group by Login)";
				$join       = 'right join '.$this->dbName . '.mt5_users b ON a.Login = b.Login';
				$totalDatas = $DB->getDTable("select ifnull(a.count, 0) AS count,ifnull(a.Profit, 0) AS PROFIT,b.`LOGIN` from {$sub} a {$join} where b.Login in " . implode(',',$loginidarr) . " order by b.Login DESC");
			}else{
				$totalDatas = $DB->getDRow("select count(*) count,sum(Profit) as PROFIT from ".$this->dbName . ".mt5_deals {$maplogin}");
			}
		}else{
			if ($loginidarr) {
				$maplogin = "where LOGIN in (" . implode(',',$loginidarr) . ")";
			}else{
				$maplogin = "where 1 = 1";
			}
	
			if ($where) {
				if ($mtserver['ver'] == 5) {
					//这里原来 $where 是数组，现在重新整过，$where是字符串：where CLOSE_TIME ...
					$maplogin .= str_ireplace('where CLOSE_TIME',' and CLOSE_TIME',$where);
				}else{
					$maplogin .= str_ireplace('where CLOSE_TIME',' and OPEN_TIME',$where);
				}
				$maplogin .= " and CMD = 7";// 入金注释
			}
			if ($type == 1) {
				$sub = "(select count(*) count,sum(PROFIT) as PROFIT,LOGIN from ".$this->dbName . "." . $this->tablePrefix . $this->tableName." {$maplogin} group by LOGIN)";
				$join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
				$totalDatas = $DB->getDTable("select ifnull(a.count, 0) AS count,ifnull(a.PROFIT, 0) AS PROFIT,b.`LOGIN` from {$sub} a {$join} where b.LOGIN in " . implode(',',$loginidarr) . " order by b.LOGIN DESC");
			}else{
				$totalDatas = $DB->getDRow("select count(*) count,sum(PROFIT) as PROFIT from ".$this->dbName . "." . $this->tablePrefix . $this->tableName." {$maplogin}");
			}
		}

        return $totalDatas;
    }

    /**
     * 获取user表数据
     */
    public function getUserData($loginidarr, $field,$type='') {
		global $DB;

		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($this->ver == 5){
			$maplogin = "where Login in (" . implode(',',$loginidarr) . ")";
			if ($type == 1) {
				$totalDatas        = $DB->getDTable("select {$field} from ".$this->dbName . ".mt5_users {$maplogin}");
			}else{
				$totalDatas        = $DB->getDRow("select {$field} from ".$this->dbName . ".mt5_users {$maplogin}");
			}
		}else{
			$maplogin = "where LOGIN in (" . implode(',',$loginidarr) . ")";
			if ($type == 1) {
				$totalDatas        = $DB->getDTable("select {$field} from ".$this->dbName . "." . $this->tablePrefix . $this->tableUsersName." {$maplogin}");
			}else{
				$totalDatas        = $DB->getDRow("select {$field} from ".$this->dbName . "." . $this->tablePrefix . $this->tableUsersName." {$maplogin}");
			}
		}
        return $totalDatas;

    }

    /**
     * 获取最后一笔订单日期
     */

    public function getLastOrder($loginidarr) {
        $maplogin['LOGIN'] = array('in', $loginidarr);
        $maplogin['CMD']   = ['in', '0,1']; //买和卖标记
        $totalDatas        = $this->Field('CLOSE_TIME,OPEN_TIME')->where($maplogin)->order('TICKET DESC')->find();
        return $totalDatas;
    }

    //获取返佣
    public function getRebate($loginidarr, $where, $field) {
        $maplogin['LOGIN'] = array('in', $loginidarr);
        $maplogin['CMD']   = ['in', '0,1']; //买和卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $field      = $field;
        $totalDatas = $this->where($maplogin)->getField("sum(" . $field . ") as " . $field);
        return $totalDatas;
    }

    //获取订单交易种类以及盈利亏损
	//cz未被调用过
    public function getTradesCollectList($loginidarr = '', $where, $type = '') {
        if ($loginidarr) {
            $maplogin['LOGIN'] = array('in', $loginidarr);
        }
        // $maplogin['_string'] = 'CLOSE_TIME <> 1970-01-01 00:00:00';
        $maplogin['CMD'] = ['in', '0,1']; //买和卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $serverwhere['status'] = 1;
        $serverwhere['id'] = session("user.serverid");
        $mtserver = D('mt4_server')->where($serverwhere)->find();
        if ($type == 1) {
            if ($mtserver['ver'] == 5) {
                $totalDatas = $this->where($maplogin)->Field("`SYMBOL`,sum(VOLUME) / 100 volume,sum(PROFIT + COMMISSION + Storage) totalPROFIT")->find();
            }else{
                $totalDatas = $this->where($maplogin)->Field("`SYMBOL`,sum(VOLUME) / 100 volume,sum(PROFIT + COMMISSION + SWAPS) totalPROFIT")->find();
            }
            
        } else {

            
            if ($mtserver['ver'] == 5) {
                //兼容MT5
                $totalDatas = $this->where($maplogin)->Field("sum(ABS(COMMISSION)) as COMMISSION_AGENT,`SYMBOL`,sum(VOLUME) / 100 volume,sum(PROFIT + COMMISSION + Storage) totalPROFIT")->group('SYMBOL')->select();
            }else{
                $totalDatas = $this->where($maplogin)->Field("sum(COMMISSION_AGENT) as COMMISSION_AGENT,`SYMBOL`,sum(VOLUME) / 100 volume,sum(PROFIT + COMMISSION + SWAPS) totalPROFIT")->group('SYMBOL')->select();
            }

            
        }
        return $totalDatas;
    }

    //获取mt返佣
    public function geMTRebate($loginidarr, $where) {
        // $maplogin['LOGIN'] = array('in', $loginidarr);
        if (is_array($loginidarr)) {
            $totalDatas = 0;
            foreach ($loginidarr as $k => $v) {
                $maplogin['COMMENT'] = ['like', "agent '" . $v . "' - #%"]; // MT返佣
                if ($where) {
                    $maplogin = array_merge($maplogin, $where);
                }
                $data = $this->where($maplogin)->getField("sum(PROFIT) as PROFIT");
                $totalDatas += $data;
            }
        } else {
            $maplogin['COMMENT'] = ['like', "agent '" . $loginidarr . "' - #%"]; // MT返佣
            if ($where) {
                $maplogin = array_merge($maplogin, $where);
            }

            $totalDatas = $this->where($maplogin)->getField("sum(PROFIT) as PROFIT");
        }
        return $totalDatas;
    }

    //获取总返佣
    public function getTotalRebate($loginidarr = '', $where,$type = '') {
        if ($loginidarr) {
            $maplogin['LOGIN'] = array('in', $loginidarr);
        }
        $maplogin['COMMENT'] = ['like', "agent%"]; // MT返佣
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        if ($type == 1) {
            $totalDatas = $this->where($maplogin)->field("sum(PROFIT) as PROFIT,LOGIN")->group('LOGIN')->select();
        }elseif($type == 2){
            $sub = $this->where($maplogin)->field("sum(PROFIT) as PROFIT,LOGIN")->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $whereee['b.LOGIN'] = ['in', $loginidarr];
            $totalDatas = M()->table($sub.'a')->join($join)->where($whereee)->field('ifnull(a.PROFIT, 0) AS PROFIT,b.`LOGIN`')->order('b.LOGIN DESC')->select();
        }else{
            $totalDatas = $this->where($maplogin)->getField("sum(PROFIT) as PROFIT");
        }
        return $totalDatas;

    }

    /**
     * 获取交易订单
     */

    public function getTradingOrder($login, $where = '', $page = '', $type = '', $limit = '') {
        if ($limit) {
            $listRows = $limit;
        } else {
            $listRows = 20;
        }
        if ($where["CMD"]) {
            $where["CMD"] = $where["CMD"] == 'buy' ? '0' : '1';
        } else {
            $maplogin['CMD'] = ['in', '0,1'];

        }
        $maplogin['LOGIN'] = ['in', $login];
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        if ($type == 1) {
            $totalDatas = $this->where($maplogin)->count();
        } else {
            $totalDatas = $this->where($maplogin)->page($page . ',' . $listRows)->order('CLOSE_TIME DESC')->select();

        }
        return $totalDatas;

    }

    /**
     * 获取扣款记录
     *type = 1 折线图
     */
    public function getDeduction($login, $where, $type = '') {

        $maplogin['LOGIN'] = ['in', $login];
        $maplogin['CMD']   = ['in', '6'];
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
        $maplogin['_string'] = '(`COMMENT` like "fee:%" or `COMMENT` like "Fu#%" )';
        if ($type == 1) {
            $totalDatas = $this->field("sum(PROFIT) PROFIT,DATE_FORMAT(CLOSE_TIME,'%Y/%m/%d') newtime")->where($maplogin)->group('newtime')->select();
        } else {
            $totalDatas = $this->field('sum(PROFIT) PROFIT')->where($maplogin)->find();
        }
        return $totalDatas;

    }

    /* 个人盈利率
     *loginid:LOGIN
     *starttime:查询开始时间
     *endtime:查询结束时间
     *limit:查询个数，默认为全部
     *sort：1：按照盈利率正序/2：按照盈利率正序倒叙
     */
	 //cz未被调用过
    public function getPersonEarningsRate($loginid, $starttime, $endtime, $type = '') {
        //连接数据库
        $reportModel         = new ReportModel(session('user.mt4dbname'));
        $where['CLOSE_TIME'] = array(array('egt', $starttime . ' 00:00:00'), array('elt', $endtime . ' 23:59:59'));
        //E1结束净值
        $equityEnd = $reportModel->sumEquityEnd($loginid);
        //总出金量 W
        $totalOutBalance = $reportModel->sumEarningOutBalance($loginid, $where);
        //D 入金总和
        $totalInBalance = $reportModel->sumEarningOutBalance($loginid, $where, 1);
        //E0前期净值
        $equityBegining = $reportModel->sumEquityBegining($loginid, $where);

        //算法(（E1 + W）- (E0 + D) ) / (E0 + D)
        if ($type == 1) {
            $BALANCEarr  = 0;
            $totalOutarr = 0;
            $equityarr   = 0;
            $totalInarr  = 0;
            foreach ($equityEnd as $k => $v) {
                $BALANCEarr += $v['BALANCE'];
                $totalOutarr += $totalOutBalance[$k]['PROFIT'];
                $equityarr += $equityBegining[$k]['E0'];
                $totalInarr += $totalInBalance[$k]['PROFIT'];
            }
            $arr = round((($BALANCEarr + abs($totalOutarr)) - ($equityarr + $totalInarr)) / ($equityarr + $totalInarr), 4);

        } else {
            $arr = [];
            foreach ($equityEnd as $k => $v) {
                $arr[$k]['loginid']  = $v['LOGIN'];
                $arr[$k]['earnings'] = round((($v['BALANCE'] + abs($totalOutBalance[$k]['PROFIT'])) - ($equityBegining[$k]['E0'] + $totalInBalance[$k]['PROFIT'])) / ($equityBegining[$k]['E0'] + $totalInBalance[$k]['PROFIT']), 4);
            }
        }
        return $arr ? $arr : 0;

    }

    /**
     * 获取所有异常订单
     *type:int; 1:开仓平仓时间间隔（秒）;2:单笔交易量；3：监控用户进场订单
     */
    public function getErrorOrder($where, $value, $type, $TICKET = '', $server_id, $WARN_REASON='', $addtime,$server_ver = '',$mt4dbname = '') {
        if ($value) {
            //兼容MT5
            if ($server_ver == 5) {
                $mt5_positionsModel = new Mt5_positionsModel($mt4dbname);
                if ($type == 3) {
                    $STANDARD_VALUE         = 0;
                    $maplogin['Login']      = ['in', $value];
                    if ($where) {
                        $maplogin = array_merge($maplogin, $where);
                    }
                    $totalDatas  = $mt5_positionsModel->where($maplogin)->getField("Position TICKET,'' as CMD,Login LOGIN,Symbol SYMBOL,Digits DIGITS,Volume VOLUME,from_unixtime(TimeCreate) OPEN_TIME,PriceCurrent OPEN_PRICE,PriceSL SL,PriceTP TP,from_unixtime(TimeUpdate) CLOSE_TIME,Profit PROFIT,'' as SWAPS,Comment COMMENT,'' as VALUE,'" . $type . "' as WARN_TYPE,'" . $server_id . "' as SERVER_ID,'" . $STANDARD_VALUE . "' as STANDARD_VALUE,' " . $WARN_REASON . "' as WARN_REASON,'" . $addtime . "' as ADD_TIME");
                    // $totalDatas['ticket_list'] = $mt5_positionsModel->where($maplogin)->getField('TICKET', true);
                }else{
                    // $maplogin['LOGIN']      = ['in', $value];
                    $maplogin['CMD']      = ['in', '0,1'];
                    
                    if ($where) {
                        $maplogin = array_merge($maplogin, $where);
                    }
                    // $maplogin['b.LOGIN'] = ['in','8071300,8071301,8072234,8071391,8072153,8071563,8071031,8071592'];
                    // $join = 'inner join '.$this->dbName . '.' . $this->tablePrefix . $this->tableName . ' b ON a.Position = b.PositionID';
                    $table =  $this->dbName . '.' . $this->orderPrefix . $this->positions ;
                    $totalDatas = $this->field('PositionID Position,CMD,TICKET,LOGIN, SYMBOL,DIGITS,VolumeClosed VOLUME ,CLOSE_TIME,PROFIT,Storage,COMMENT,Entry')->where($maplogin)->order('Position ASC,TICKET ASC')->select();
                }



            }else{
                $maplogin['CMD'] = ['in', '0,1'];
                switch ($type) {
                case 1:
                    $maplogin['_string'] = '(unix_timestamp(CLOSE_TIME) - unix_timestamp(OPEN_TIME)) <= ' . $value;
                    if ($TICKET) {
                        $maplogin['TICKET'] = ['not in', $TICKET];
                    }
                    $maplogin['CLOSE_TIME'] = array('NEQ', '1970-01-01 00:00:00');
                    $STANDARD_VALUE      = $value;
                    break;
                case 2:
                    $maplogin['VOLUME'] = ['egt', $value * 100];
                    $maplogin['TICKET'] = ['not in', $TICKET];
                    $maplogin['CLOSE_TIME'] = array('NEQ', '1970-01-01 00:00:00');
                    $STANDARD_VALUE     = $value;
                    break;
                case 3:
                    $maplogin['LOGIN']      = ['in', $value];
                    $maplogin['TICKET']     = ['not in', $TICKET];
                    $maplogin['CLOSE_TIME'] = array('EQ', '1970-01-01 00:00:00');
                    $STANDARD_VALUE         = 0;
                    break;
                }

                if ($where) {
                    $maplogin = array_merge($maplogin, $where);
                }
                $totalDatas['list']        = $this->where($maplogin)->getField("TICKET TICKET,LOGIN,SYMBOL,DIGITS,CMD, VOLUME,OPEN_TIME,OPEN_PRICE,SL,TP,CLOSE_TIME,COMMISSION,COMMISSION_AGENT,SWAPS,CLOSE_PRICE,PROFIT,COMMENT,(unix_timestamp(CLOSE_TIME) - unix_timestamp(OPEN_TIME)) VALUE,'" . $type . "' as WARN_TYPE,'" . $server_id . "' as SERVER_ID,'" . $STANDARD_VALUE . "' as STANDARD_VALUE,' " . $WARN_REASON . "' as WARN_REASON,'" . $addtime . "' as ADD_TIME");

                $totalDatas['ticket_list'] = $this->where($maplogin)->getField('TICKET', true);
            }
           

            return $totalDatas;
        }

    }

    /**
     * 获取mt昵称
     */
    public function getMTnickname($id) {
        $depositModel = new DepositModel($this->dbName);
        $map['LOGIN'] = $id;
        $data         = $depositModel->where($map)->Field('NAME,GROUP,REGDATE')->find();
        return $data;
    }

    /**
     * 获取重组返佣订单
     */
    public function getMTCommissionMix($maplogin = '',$server_id) {
        $ver = D('Mt4Server')->where(['id' => $server_id])->field('ver')->find();

        $where['COMMENT']    = ['like', "agent%"];
        if ($ver['ver'] == 5) {
            $where['CMD']        = 18;
        }else{
            $where['CMD']        = 6;
        }
        $where['CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];

        if ($maplogin) {
            $where = array_merge($where, $maplogin);
        }
        $sub            = $this->field(" `TICKET`, `LOGIN`, `COMMENT`, substring_index(COMMENT, '#' ,- 1) NEWTICKET, SUBSTRING( COMMENT, locate('agent ', COMMENT) + CHAR_LENGTH('agent '), locate( ' - #', COMMENT, CHAR_LENGTH(' - #')) - ( SELECT locate('agent ', COMMENT) + CHAR_LENGTH('agent '))) NEWLOGIN, `PROFIT`, `CLOSE_TIME`")->where($where)->select(false);
        $table          = $sub . ' a ';
        $table2         = $this->dbName . '.' . $this->tablePrefix . $this->tableName . ' b';
        $map['_string'] = 'NEWTICKET = b.TICKET';

        $data = M()->table($table . ',' . $table2)->where($map)->Field('a.TICKET, a.LOGIN, a.`COMMENT`, b.SYMBOL, `NEWTICKET`, NEWLOGIN, a.PROFIT, a.CLOSE_TIME')->select();
        return $data;

    }

    /**
     * 获取30天内活跃LOGIN
     * 每周有一次交易记录
     */
    public function getActiveUser() {
        $where['CMD'] = ['in','0,1'];
        $map['CMD'] = ['in','0,1'];
        $where['CLOSE_TIME'] = ['neq', '1970-01-01 00:00:00'];
        $stime = 30;
        $etime = 23;
        $list = [];
        for ($i = 1; $i <= 4; $i ++) {
            $start = strtotime(date("Y-m-d", strtotime("-".$stime." day")) . ' 00:00:00');
            $end   = strtotime(date("Y-m-d", strtotime("-".$etime." day")) . ' 23:59:59');
            $where['OPEN_TIME'] = ['between', [date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end)]];
            $map['CLOSE_TIME']  = ['between', [date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end)]];
            // $data = $this->where($where)->group('LOGIN')->select(false);
            $sub    = $this->field('LOGIN')->where($map)->group('LOGIN')->select(false);
            $subs   = $this->field('LOGIN')->where($where)->group('LOGIN')->union($sub)->select(false);
            $result = M()->table($subs . ' a')->getField('LOGIN', true);
            $stime = $stime - 7;
            $etime = $etime - 7;
            $list = array_merge($list,$result);
        }
        $list = array_unique($list);
        return count($list);

    }
    /**
     * 获取所有交易订单按小时分割
     */
    public function getHourOrder($maplogin = '',$server_id='') {
        $where['CMD'] = ['in', '0,1'];
        if ($maplogin) {
            $where = array_merge($where, $maplogin);
        }
        $serverwhere['status'] = 1;
        $serverwhere['id'] = $server_id;
        $mtserver = D('mt4_server')->where($serverwhere)->find();
        if ($mtserver['ver'] == 5) {
            $result = $this->field('PROFIT,SWAPS,VOLUME,LOGIN,TICKET,SYMBOL,CLOSE_TIME,extract(hour from CLOSE_TIME) CLOSE_HOUR')->where($where)->select();
        }else{
            $result = $this->field('PROFIT,SWAPS,VOLUME,LOGIN,TICKET,SYMBOL,OPEN_TIME,CLOSE_TIME,extract(hour from OPEN_TIME) OPEN_HOUR,extract(hour from CLOSE_TIME) CLOSE_HOUR')->where($where)->select();
        }

        
        return $result;
    }

    /**
     * 根据订单获取获得MT返佣
     */
    public function getTicketToRebate($ticket,$loginidarr='',$type = '') {
        $where['TICKET'] = ['in', $ticket];
        if ($type == 1 or $type == 2) {
            if ($type == 1) {
                $field = 'sum(COMMISSION_AGENT) COMMISSION,LOGIN';
            }else{
                $field = 'sum(PROFIT) COMMISSION,LOGIN';
            }
            $sub    = $this->where($where)->field($field)->group('LOGIN')->select(false);
            $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
            $whereee['b.LOGIN'] = ['in', $loginidarr];
            $result = M()->table($sub.'a')->join($join)->where($whereee)->field('ifnull(a.COMMISSION, 0) AS COMMISSION,b.`LOGIN`')->order('b.LOGIN DESC')->select();
        }else{
            $result = $this->where($where)->getField('sum(COMMISSION_AGENT) COMMISSION');
        }
        return $result;

    }

    /////////////////以下为报表中心////////////////////////

    /**
     * 获取用户订单入金明细
     */
    public function getOrderDetail($loginidarr, $page, $type = '') {
        $maplogin['a.LOGIN']   = array('in', $loginidarr);
        $maplogin['a.CMD']     = array('in', '6'); //出入金标记
        $maplogin['a.COMMENT'] = array('like', 'Deposit%'); // 入金注释
        $listRows              = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;

        $table = $this->dbName . '.' . $this->tablePrefix . $this->tableName . ' a';
        $join  = "t_inmoney b ON substring_index(a. COMMENT, 'Deposit maxib#', - 1) = b.id";
        if ($type == 1) {
            $result = M()->table($table)->join($join, 'left')->where($maplogin)->count();
        } else {
            $result = M()->table($table)->field('a.TICKET,a.`COMMENT`,a.PROFIT,b.serialno,b.type,b.price,b.content,b.create_time,b.visit_time')->join($join, 'left')->where($maplogin)->page($page . ',' . $listRows)->select();
        }

        return $result;
    }

    /**
     * 报表中心获取所有持仓订单
     */

    public function getPositionOrderReport($page = '', $loginidarr, $where = '', $type = '') {
		global $DB;
		global $DRAdmin;
		
		$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['id']}' and status = 1");
		
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($page == -999){
			$pageSql = '';
		}else{
			$page     = !empty($page) ? $page : 1;
			$listRows            = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
			$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
		}
		
        $maplogin = 'where a.LOGIN = b.LOGIN';
        if ($loginidarr) {
            $maplogin .= " and a.LOGIN in (" . implode(',',$loginidarr) . ")";
        }
        if ($type == 1) {
            $maplogin .= " and CMD in (0,1)"; //买和卖标记
			
			if(stripos($where,'CLOSE_TIME') !== false){
				$maplogin .= str_ireplace('where ',' and ',$where);
			}else{
            	$maplogin .= " and CLOSE_TIME = '1970-01-01 00:00:00'";
				if ($where) {
					$maplogin .= str_ireplace('where ',' and ',$where);
				}
			}
        } elseif ($type == 2) {
			if ($mtserver['ver'] == 5) {
				$maplogin .= " and Action in (0,1)"; //买和卖标记
				if(stripos($where,'CLOSE_TIME') !== false){
					$maplogin .= " and Entry = 1";//这可能有bug，当CLOSE_TIME 非 【<> '1970-01-01 00:00:00'】时
					$maplogin .= str_ireplace('where ',' and ',$where);
				}else{
					$maplogin .= " and Entry = 1";
					if ($where) {
						$maplogin .= str_ireplace('where ',' and ',$where);
					}
				}
				$maplogin = str_ireplace('CLOSE_TIME','Time',$maplogin);
			}else{
				$maplogin .= " and CMD in (0,1)"; //买和卖标记
				if(stripos($where,'CLOSE_TIME') !== false){
					$maplogin .= str_ireplace('where ',' and ',$where);
				}else{
					$maplogin .= " and CLOSE_TIME <> '1970-01-01 00:00:00'";
					if ($where) {
						$maplogin .= str_ireplace('where ',' and ',$where);
					}
				}
			}
        } elseif ($type == 3) {
            $maplogin .= " and CMD in (2,3)"; //挂单，这个是mt4的
			if(stripos($where,'CLOSE_TIME') !== false){
				$maplogin .= str_ireplace('where ',' and ',$where);
			}else{
            	$maplogin .= " and CLOSE_TIME = '1970-01-01 00:00:00'";
				if ($where) {
					$maplogin .= str_ireplace('where ',' and ',$where);
				}
			}
        }

        
        if ($type == 3) {
            $orderwhere = "where Type in (2,3,4,5,6,7)"; //挂单，这个是mt5的
            $orderwhere .= " and a.LOGIN in (" . implode(',',$loginidarr) . ")";
            $orderwhere .= " and a.LOGIN = b.Login";
            $orderwhere .= " and b.state = 1";
            if ($where) {
                $orderwhere .= str_ireplace('where ',' and ',$where);
            }
            if ($mtserver['ver'] == 5) {
                $table               = $this->dbName . '.mt5_users a,' . $this->dbName . '.mt5_orders b';
                $totalDatas['count'] = intval($DB->getField("select count(*) as count1 from (select a.LOGIN from {$table} {$orderwhere} group by a.LOGIN) cc"));
                $totalDatas['list']  = $DB->getDTable("select a.LOGIN,a.NAME, count(*) count, ROUND(a.BALANCE,2) BALANCE from {$table} {$orderwhere} group by a.LOGIN {$pageSql}");

                $sub = "(select count(*) count, ROUND(a.BALANCE,2) BALANCE from {$table} {$orderwhere} group by a.LOGIN)";
                $totalDatas['total'] =  $DB->getDRow("select sum(count) VOLUME,sum(BALANCE) BALANCE from {$sub} a");
            }else{
                $table               = $this->dbName . '.' . $this->tablePrefix . $this->tableName . ' a,' . $this->dbName . '.' . $this->tablePrefix . $this->tableUsersName . ' b';
                $totalDatas['count'] = intval($DB->getField("select count(*) as count1 from (select a.LOGIN from {$table} {$maplogin} group by a.LOGIN) cc"));
                $totalDatas['list']  = $DB->getDTable("select a.LOGIN,b.NAME,count(*) count , ROUND(b.BALANCE,2) BALANCE from {$table} {$maplogin} group by a.LOGIN {$pageSql}");
               
			    $sub = "(select count(*) count, ROUND(b.BALANCE,2) BALANCE from {$table} {$maplogin} group by a.LOGIN)";
                $totalDatas['total'] =  $DB->getDRow("select sum(count) VOLUME,sum(BALANCE) BALANCE from {$sub} a");
            }            
        }else{
            if ($mtserver['ver'] == 5) {
				$table               = $this->dbName . '.mt5_deals a,(select b2.Balance as BALANCE,b2.Equity as EQUITY,b2.MarginFree as MARGIN_FREE,b1.Name as `NAME`,b1.Login as LOGIN,b2.MarginLevel as MARGIN_LEVEL from ' . $this->dbName . '.mt5_users b1 left join ' . $this->dbName . '.mt5_accounts b2 on b1.Login = b2.Login) b';
				$totalDatas['count'] = intval($DB->getField("select count(*) as count1 from (select a.LOGIN from {$table} {$maplogin} group by a.LOGIN) cc"));
			
                $totalDatas['list']  = $DB->getDTable("select a.LOGIN, ROUND(sum(PROFIT + COMMISSION + a.Storage),2) PROFIT,ROUND(sum(VOLUME),2) VOLUME, ROUND(b.BALANCE,2) BALANCE,ROUND(b.EQUITY,2) EQUITY,ROUND(b.MARGIN_FREE,2) MARGIN_FREE,b.NAME,ROUND(b.MARGIN_LEVEL,2) MARGIN_LEVEL from {$table} {$maplogin} group by a.LOGIN {$pageSql}");
                
				$sub = "(select ROUND(sum(PROFIT + COMMISSION + a.Storage),2) PROFIT,ROUND(sum(VOLUME),2) VOLUME, ROUND(b.BALANCE,2) BALANCE from {$table} {$maplogin} group by a.LOGIN)";
                $totalDatas['total'] =  $DB->getDRow("select sum(PROFIT) PROFIT,sum(VOLUME) VOLUME,sum(BALANCE) BALANCE from {$sub} a");
            }else{
				$table               = $this->dbName . '.' . $this->tablePrefix . $this->tableName . ' a,' . $this->dbName . '.' . $this->tablePrefix . $this->tableUsersName . ' b';
				$totalDatas['count'] = intval($DB->getField("select count(*) as count1 from (select a.LOGIN from {$table} {$maplogin} group by a.LOGIN) cc"));
			
				$field = '';
                //$SPREAD_PROFIT = $DB->getField("select SPREAD_PROFIT from " . $this->dbName . '.' . $this->tablePrefix . $this->tableName . " where CMD in (0,1)");//买和卖标记
                //if ($SPREAD_PROFIT) {
                //    $field = 'ROUND(sum(SPREAD_PROFIT),2) SPREAD_PROFIT,';
                //}

                $totalDatas['list']  = $DB->getDTable("select a.LOGIN, ROUND(sum(PROFIT + COMMISSION + SWAPS),2) PROFIT,ROUND(sum(VOLUME),2) VOLUME, ".$field."ROUND(b.BALANCE,2) BALANCE,ROUND(b.EQUITY,2) EQUITY,ROUND(b.MARGIN_FREE,2) MARGIN_FREE,b.NAME,ROUND(b.MARGIN_LEVEL,2) MARGIN_LEVEL from {$table} {$maplogin} group by a.LOGIN {$pageSql}");
                $sub = "(select ROUND(sum(PROFIT + COMMISSION + SWAPS),2) PROFIT,ROUND(sum(VOLUME),2) VOLUME, ROUND(b.BALANCE,2) BALANCE from {$table} {$maplogin} group by a.LOGIN)";
                $totalDatas['total'] =  $DB->getDRow("select sum(PROFIT) PROFIT,sum(VOLUME) VOLUME,sum(BALANCE) BALANCE from {$sub} a");
            }
            

        }
        
        return $totalDatas;
    } 

    //MT转账
    public function getMTReport($loginidarr, $where, $type = 0, $page = '', $status = '', $count = '') {
        $maplogin['status'] = 9;

        $maplogin['type'] = 3;

        if ($type == 0) {
            $maplogin['mtid'] = $loginidarr;

        } else {
            $maplogin['forwordmtlogin'] = $loginidarr;

        }
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }

        $listRows            = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
        $totalDatas['count'] = D('outmoney')->where($maplogin)->count();
        $totalDatas['list']  = D('outmoney')->field('mtid,number,forwordmtlogin,id')->where($maplogin)->page($page . ',' . $listRows)->order('id DESC')->select();


        return $totalDatas;
    }

    //资金报表汇总数据
    public function getCapitalSummary($loginidarr = '', $where = '', $type = '', $status = '') {
		global $DB;
		
		if(!is_array($loginidarr)){
			if($loginidarr == '无'){
				$loginidarr = '0';
			}
			$loginidarr = array($loginidarr);
		}
		
		if($this->ver == 5){
			$maplogin = "where Action = 2";
			if (!empty($loginidarr)) {
				$maplogin .= " and Login in (" . implode(',',$loginidarr) . ")";
			}
			if ($where) {
				//原来是合并where数组，现在这样合并字符串可能存在问题：同字段未被合并
				$where = str_ireplace('CLOSE_TIME','Time',$where);
				$maplogin .= str_ireplace('where ',' and ',$where);
			}
	
			if ($type == '') {
				$totalDatas['outmoney'] = $DB->getDRow("select count(*) count,sum(Profit) as PROFIT from " . $this->dbName . ".mt5_deals {$maplogin} and Profit < 0 and Comment like 'Withdraw%'");
				$totalDatas['inmoney']  = $DB->getDRow("select count(*) count,sum(Profit) as PROFIT from " . $this->dbName . ".mt5_deals {$maplogin} and Profit > 0 and Comment like 'Deposit%'");
			} else {
				if ($type == 1) {
					$maplogin .= " and Profit > 0";
					$comment = 'Deposit';
				} else {
					$maplogin .= " and Profit < 0";
					$comment = 'Withdraw';
				}
	
				$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
				if ($status == 1) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= " and Comment like '{$comment} %'";
					}else{
						$maplogin .= " and Comment like '{$comment} maxib#%'";
					}
				} elseif ($status == 2) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= "(Comment like '" . $comment . "%' and Comment not like'" . $comment . " %')";
					}else{
						$maplogin .= "(Comment like '" . $comment . "%' and Comment not like'" . $comment . " maxib#%')";
					}
	
				} else {
					$maplogin .= " and Comment like '{$comment}%'";
				}
	
				$totalDatas = $DB->getDRow("select count(*) count,round(sum(Profit),2) as PROFIT from " . $this->dbName . ".mt5_deals {$maplogin}");
			}
		}else{
			$maplogin = "where CMD = 6";
			if (!empty($loginidarr)) {
				$maplogin .= " and LOGIN in (" . implode(',',$loginidarr) . ")";
			}
			if ($where) {
				//原来是合并where数组，现在这样合并字符串可能存在问题：同字段未被合并
				$maplogin .= str_ireplace('where ',' and ',$where);
			}
	
			if ($type == '') {
				$totalDatas['outmoney'] = $DB->getDRow("select count(*) count,sum(PROFIT) as PROFIT from " . $this->dbName . '.' . $this->tablePrefix . $this->tableName . " {$maplogin} and PROFIT < 0 and COMMENT like 'Withdraw%'");
				$totalDatas['inmoney']  = $DB->getDRow("select count(*) count,sum(PROFIT) as PROFIT from " . $this->dbName . '.' . $this->tablePrefix . $this->tableName . " {$maplogin} and PROFIT > 0 and COMMENT like 'Deposit%'");
			} else {
				if ($type == 1) {
					$maplogin .= " and PROFIT > 0";
					$comment = 'Deposit';
				} else {
					$maplogin .= " and PROFIT < 0";
					$comment = 'Withdraw';
				}
	
				$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
				if ($status == 1) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= " and COMMENT like '{$comment} %'";
					}else{
						$maplogin .= " and COMMENT like '{$comment} maxib#%'";
					}
				} elseif ($status == 2) {
					if ($mtserver['ver'] == 5) {
						$maplogin .= "(COMMENT like '" . $comment . "%' and COMMENT not like'" . $comment . " %')";
					}else{
						$maplogin .= "(COMMENT like '" . $comment . "%' and COMMENT not like'" . $comment . " maxib#%')";
					}
	
				} else {
					$maplogin .= " and COMMENT like '{$comment}%'";
				}
	
				$totalDatas = $DB->getDRow("select count(*) count,round(sum(PROFIT),2) as PROFIT from " . $this->dbName . '.' . $this->tablePrefix . $this->tableName . " {$maplogin}");
			}
		}

        return $totalDatas;
    }


    /**
     * 获取产生返佣
     */
    public function getProduceCommission($loginidarr, $start = '', $TICKET = '') {
        $where['BALANCE_TIME'] = ['between', [strtotime(date('Y-m-d',$start). ' 00:00:00'),strtotime(date('Y-m-d',$start). ' 23:59:59')]];
        $where['LOGIN']       = ['in', $loginidarr];
        if ($TICKET) {
            $where['TICKET'] = $TICKET;
        }
        $sub = M('sale_commission')->field('sum(AMOUNT) AMOUNT,LOGIN')->where($where)->group('LOGIN')->select(false);
        $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' b ON a.LOGIN = b.LOGIN';
        $whereee['b.LOGIN'] = ['in', $loginidarr];
        $totalDatas = M()->table($sub.'a')->join($join)->where($whereee)->field('ifnull(a.AMOUNT, 0) AS AMOUNT,b.`LOGIN`')->order('b.LOGIN DESC')->select();
        return $totalDatas;
    }

    //CRM获取返佣
    public function getCRMRebate($loginidarr = '', $start, $server_id) {
        $starttime               = strtotime(date('Y-m-d',$start). ' 00:00:00');
        $endtime                 = strtotime(date('Y-m-d',$start). ' 23:59:59');
        $totalDatas = D('sale_commission_balance')->where($wherecom)->sum('AMOUNT');

        $sub = 'SELECT SUM(a.AMOUNT) AS AMOUNT, a.MEMBER_ID, b.loginid FROM `t_sale_commission_balance` a INNER JOIN t_member_mtlogin b ON a.MEMBER_ID = b.member_id WHERE (a.`TYPE` = 0) AND a.SERVER_ID = '.$server_id.'  AND (( a.`CREATE_TIME` BETWEEN '.$starttime.' AND '.$endtime.' )) GROUP BY a.MEMBER_ID';
        $join       = 'right join '.$this->dbName . '.' . $this->tablePrefix . $this->tableUsersName.' d ON c.loginid = d.LOGIN';
        $whereee['d.LOGIN'] = ['in', $loginidarr];
        $totalDatas = M()->table('('.$sub.')c')->join($join)->where($whereee)->field('ifnull(c.AMOUNT, 0) AMOUNT,d.LOGIN')->order('d.LOGIN DESC')->select();

        return $totalDatas;
    }



    //批量获取用户信息
    public function getUserFinanceDetail($loginidarr = '')
    {

        $depositModel = new DepositModel($this->dbName);
        $totalDatas = $depositModel->field("BALANCE ,EQUITY,MARGIN_FREE,LOGIN")->where(['LOGIN' => ['in', $loginidarr]])->order('LOGIN DESC')->select();
        return $totalDatas;

    }



    //获取半个小时时差where
	//cz未被调用过
    public function getTimeNowWhere()
    {
        $lasttime = D('MtMemberTrades')->where(['server_id'=>session("user.serverid")])->field('addtime')->order('id DESC')->find();
        

        if (!$lasttime) {
           $where = false;
        }
        return $where;
    }


    //1:个人首页，2：团队首页
    public function getCacheData($mtlist,$start='',$end='',$type='')
    {
        $newwhere['loginid'] = ['in',$mtlist];
        $newwhere['server_id'] = session('user.serverid');
        //缓存
        $serverwhere['status'] = 1;
        $serverwhere['id'] = session("user.serverid");
        $mtserver = D('mt4_server')->where($serverwhere)->find();

        if ($end == date('Y-m-d',time())) {
            $newwhere['date']= ["between",[strtotime($start.' 00:00:00'),strtotime(date('Y-m-d',strtotime('-1 days')).' 23:59:59')]];
        }else{
            $newwhere['date']= ["between",[strtotime($start.' 00:00:00'),strtotime($end.' 23:59:59')]];
        }
        $reportModel =new ReportModel(session('user.mt4dbname'));
        $mt5_buyModel = new Mt5_buyModel(session('user.mt4dbname'));

        $user_id = session('user.id');
        if (!S('profitCache'.$user_id.$type) || S('profitCache'.$user_id.$type) == false) {

            $dataCache = D('MtMemberTrades')->where($newwhere)->field('sum(total_volumes) total_volumes,sum(total_count) total_count,sum(innumber) innumber,sum(incount) incount,sum(outnumber) outnumber,sum(outcount) outcount,sum(totalprofit) totalprofit')->find();
            $volumeCache = $dataCache['total_volumes'];
            $countCache = $dataCache['total_count'];
            $depoistCache = $dataCache['innumber'];
            $depoistCountCache = $dataCache['incount'];
            $withdrawCache = $dataCache['outnumber'];
            $withdrawCountCache = $dataCache['outcount'];
            $profitCache = $dataCache['totalprofit'];            
            $time = date('H', time());
            $hours = 24 - $time;
            S('volumeCache'.$user_id.$type, $volumeCache, 3600 * $hours);
            S('countCache'.$user_id.$type, $countCache, 3600 * $hours);
            S('depoistCache'.$user_id.$type, $depoistCache, 3600 * $hours);
            S('depoistCountCache'.$user_id.$type, $depoistCountCache, 3600 * $hours);
            S('withdrawCache'.$user_id.$type, $withdrawCache, 3600 * $hours);
            S('withdrawCountCache'.$user_id.$type, $withdrawCountCache, 3600 * $hours);
            S('profitCache'.$user_id.$type, $profitCache, 3600 * $hours);
        } else {
            //缓存时间
            $volumeCache = S('volumeCache'.$user_id.$type);
            $countCache = S('countCache'.$user_id.$type);
            $depoistCache = S('depoistCache'.$user_id.$type);
            $depoistCountCache = S('depoistCountCache'.$user_id.$type);
            $withdrawCache = S('withdrawCache'.$user_id.$type);
            $withdrawCountCache = S('withdrawCountCache'.$user_id.$type);
            $profitCache = S('profitCache'.$user_id.$type);

        }

        //实时
        if ($end == date('Y-m-d',time())) {
            $nowwhere['CLOSE_TIME'] = ['between', [date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()). ' 00:00:00')), date('Y-m-d H:i:s', time())]];
            if ($nowwhere != false) {
                $volumeNow = $reportModel->sumVolume($mtlist,$nowwhere);
                $countNow = $reportModel->sumCount($mtlist,$nowwhere);
                $depoistNow = $reportModel->sumInBalance($mtlist,$nowwhere);
                $depoistCountNow = $reportModel->sumInBalanceCount($mtlist,$nowwhere);
                $withdrawNow = $reportModel->sumOutBalance($mtlist,$nowwhere);
                $withdrawCOuntNow = $reportModel->sumOutBalanceCount($mtlist,$nowwhere);
                if ($mtserver['ver'] == 5) {
                    $profitNow = $mt5_buyModel->sumProfit($mtlist,$nowwhere);
                }else{
                    $profitNow = $reportModel->sumProfit($mtlist,$nowwhere);
                }

            }
        }    
        $data['totalvolume'] = ($volumeCache + ($volumeNow ? $volumeNow / 100 : 0)) ;
        $data['totalcount'] = $countCache + ($countNow ? $countNow : 0);
        $data['depoistamount'] = $depoistCache + ($depoistNow ? $depoistNow : 0);
        $data['depoistcount'] = $depoistCountCache + ($depoistCountNow ? $depoistCountNow : 0);
        $data['withdrawamount'] = $withdrawCache + ($withdrawNow ? $withdrawNow : 0);
        $data['withdrawcount'] = $withdrawCountCache + ($withdrawCOuntNow ? $withdrawCOuntNow : 0);
        $data['profitamount'] = $profitCache + ($profitNow ? $profitNow : 0);
        return $data;

    }



}

