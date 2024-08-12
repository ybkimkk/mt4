<?php

class Mt5_buyModel{
    protected $dbName      =   '';
    protected $tablePrefix      =   'mt5_';
    protected $tableName        =   'deals';
	
	public function __construct($dbName) {
        $this->dbName = $dbname;
    }

    public function get_follow_mt5profit($limit,$dbName='') {
		global $DB;
		
        $where = " and PROFIT>0";
        if ($dbName) {
            $this->dbName = $dbName;
        }
        $result = M()->query("SELECT CLOSE_TIME,c.LOGIN,PROFIT,`NAME`,SYMBOL,Price,VOLUME from {$this->dbName}.{$this->tablePrefix}users  c INNER JOIN (SELECT PROFIT,LOGIN,TICKET,CLOSE_TIME,SYMBOL,Price,VOLUME from {$this->dbName}.{$this->tablePrefix}trades where CMD in(0,1) and CLOSE_TIME>'1970-01-01 00:00:00'". $where ." ORDER BY CLOSE_TIME desc limit {$limit})d on c.LOGIN=d.LOGIN");
        return $result;
    }

	/**
     * 统计订单手数
     */
    public function sumVolume($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action']   = array('in', '0,1'); //买和卖标记
		$maplogin['Entry']   = 1;
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = $DB->getField("select sum(VOLUME) as VOLUME from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr);
        return $totalDatas;
    }

    /**
     * 统计入金量
     */
    public function sumInBalance($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login']   = array('in', $loginidarr);
        $maplogin['Action']     = 2; //入金标记
        $maplogin['Profit']  = array('gt', '0'); // 大于0为入金
        $maplogin['Comment'] = array('like', 'Deposit%'); // 入金注释
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = $DB->getField("select sum(Profit) as PROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr);

        return $totalDatas;
    }

     /**
     * 统计出金量
     */
    public function sumOutBalance($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login']   = array('in', $loginidarr);
        $maplogin['Action']     = 2; //出金标记
        $maplogin['Profit']  = array('lt', '0'); // 小于0为出金
        $maplogin['Comment'] = array('like', 'Withdraw%'); // 出金注释

        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = $DB->getField("select sum(Profit) as PROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr);

        return $totalDatas;
    }

     /**
     * 统计总盈亏
     */
    public function sumProfit($loginidarr, $where,$mt5dbname = '') {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action']   = array('in', '0,1'); //买卖标记
		$maplogin['Entry']   = 1;
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }

		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = $DB->getField("select sum(Profit+Commission+Storage) as PROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr);

        return $totalDatas;
    }

    /**
     * 统计订单笔数
     */
    public function sumCount($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action']   = array('in', '0,1'); //买卖标记
		$maplogin['Entry']   = 1;
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = intval($DB->getField("select count(*) as count1 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr));
        if ($totalDatas) {
            return $totalDatas;
        } else {
            return 0;
        }
    }

    /**
     * 统计入金金笔数
     */
    public function sumInBalanceCount($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login']   = array('in', $loginidarr);
        $maplogin['Action']     = 2; //出入金标记
        $maplogin['Profit']  = array('gt', '0'); // 小于0为出金
        $maplogin['Comment'] = array('like', 'Deposit%'); // 入金注释
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = intval($DB->getField("select count(*) as count1 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr));
        if ($totalDatas) {
            return $totalDatas;
        } else {
            return 0;
        }
    }

     /**
     * 统计出金金笔数
     */
    public function sumOutBalanceCount($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login']   = array('in', $loginidarr);
        $maplogin['Action']     = 2; //出入金标记
        $maplogin['Profit']  = array('lt', '0'); // 小于0为出金
        $maplogin['Comment'] = array('like', 'Withdraw%'); // 出金注释
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		$sqlStr = str_ireplace('CLOSE_TIME','Time',$sqlStr);

        $totalDatas = intval($DB->getField("select count(*) as count1 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $sqlStr));
        if ($totalDatas) {
            return $totalDatas;
        } else {
            return 0;
        }

    }


    
}