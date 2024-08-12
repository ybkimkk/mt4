<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DepositModel extends CommonModel{
    protected $dbName      =   '';
    protected $tablePrefix      =   'mt4_';
    protected $tableName        ='users';
	
	public function __construct($dbName) {
		parent::__construct();
		$this->dbName=$dbName;
	}
	
    public function getuser($loginid){
        return $this->where(array('LOGIN'=>$loginid))->find();
    }
	
	public function queryAllUser(){
        return $this->select();
    }
	public function queryUserByAgent($agent){
		$where["AGENT_ACCOUNT"] = $agent;
        return $this->where($where)->select();
    }
	
	public function queryMT4ByAgents($wherearr){
		$mtlist = $this->where($wherearr)->getField('LOGIN as ID,LOGIN');
		return $mtlist;
	}
	
	
	/**
	 * 总余额
	 */
	public function sumBalance($loginidarr,$where){
		$maplogin['ENABLE']=1;
		$maplogin['LOGIN'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
		$totalDatas = $this->where($maplogin)->getField("sum(BALANCE) as BALANCE");
		return $totalDatas;
	}
	
	
	/**
	 * 统计总净值
	 */
	public function sumEquity($loginidarr,$where){
		$maplogin['ENABLE']=1;
		$maplogin['LOGIN'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
		$totalDatas = $this->where($maplogin)->getField("sum(EQUITY) as EQUITY");
		return $totalDatas;
	}
        
       /**
        *  计算可用的固定跟单金额
        * @param type $loginid MT4帐号
        */
       public function get_fixed_follow($loginid) {
        
           $my = $this->getuser($loginid);
           $usernumer = $my['MARGIN_FREE'];
           //查询我已经配置好的跟单固定金额
           $isset = M('config', 'mt4svr_')->where(array('LOGINID' => array('in', $loginid), 'STATUS' => 0, 'FOLLOW_TYPE' => 2))->getField('sum(FIEXD_MONEY) as total');
           $realnumber = $usernumer - $isset;
           return  $realnumber;
       }
    
}