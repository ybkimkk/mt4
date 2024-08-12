<?php
class Mt5_positionsModel{
    protected $dbName      = '';
    protected $tablePrefix      = 'mt_';
    protected $tableName     = 'positions';
    protected $mt4tablePrefix    = 'mt4_';
    protected $tableUsersName = 'users';

	public function __construct($dbName) {
		$this->dbName=$dbName;
	}

	 /**
     * 统计未平仓订单笔数数
     */
    public function sumcloseCount($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action'] = array('in', '0,1'); //买卖标记
        if($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		
        $totalDatas = intval($DB->getField("select count(*) as count1 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $sqlStr));
        if ($totalDatas) {
            return $totalDatas;
        } else {
            return 0;
        }
    }

     /**
     * 统计未平仓订单手数
     */
    public function sumcloseVolume($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action'] = array('in', '0,1'); //买卖标记
        if ($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		
        $totalDatas = floatval($DB->getField("select sum(Volume) as VOLUME from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $sqlStr));
        return $totalDatas;
    }


    /**
     * 统计未平仓订单总金额
     */
    public function sumcloseAmount($loginidarr, $where) {
		global $DB;
		
        $maplogin['Login'] = array('in', $loginidarr);
        $maplogin['Action']   = array('in', '0,1'); //买卖标记
        if($where) {
            $maplogin = array_merge($maplogin, $where);
        }
		
		$sqlStr = cz_where_to_str($maplogin);
		
        $totalDatas = floatval($DB->getField("select sum(Profit) as PROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $sqlStr));
        return $totalDatas;
    }


    //
    public function getPositionOrder($loginidarr, $where = ''){
		global $DB;
		
        $posiwhere['Login'] = array("in", $loginidarr);
        $posiwhere['Action'] = array('in', '0,1');
		$sqlStr = cz_where_to_str($posiwhere);
        $sub = "select round(sum(Profit),2) as Profit,Login from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $sqlStr . " group by Login";
		
        $join       = 'right join ' . $GLOBALS['deposit_mt4dbname'] . '.mt5_users b ON a.Login = b.Login';
        $whereee['b.LOGIN'] = array('in', $loginidarr);
		$sqlStr = cz_where_to_str($whereee);
        $totalDatas = $DB->getDTable("select ifnull(a.Profit, 0) AS momey,b.`LOGIN` from " . $sub . " a left join " . $join . " " . $sqlStr . " order by b.LOGIN DESC");
        return $totalDatas;
    }



}