<?php
class MtApiModel {
	protected $mt4svrapi;
	private $version = 4;
	
	private $mt5webapi;
	
	//似乎不会被调用到
	public function _initialize() {
		loginfo("MtApiModel","method:_initialize");
	}
	
	//构造函数
	public function __construct($ver=4) {
		loginfo("MtApiModel","method:__construct");
		
		if($ver>=5) {
			$ver = 5;
		} else {
			$ver = 4;
		}
		$this->version = $ver;
		
		loginfo("MtApiModel","method:__construct success,version:".$ver);
		
		if($ver == 5){
			$PATH_TO_LOGS = $_SERVER['DOCUMENT_ROOT'] . "/logs/mt5/" . date("Y/m/d/");
			@mkdir($PATH_TO_LOGS,0777,true);
			
			$this->mt5webapi = new MTWebAPI($AGENT = 'WebApi', $PATH_TO_LOGS);			
			$this->mt5webapi->SetLoggerWriteDebug($IS_WRITE_DEBUG_LOG = 1);
		}else{
			$this->mt4svrapi= new COM('MT4ServerApiLib.MT4SvrApi') or die("error");			
			loginfo("MtApiModel","method:SetVersion:".$ver);
			$version = $this->mt4svrapi->SetVersion($ver);
			loginfo("MtApiModel","SetVersion return:".$version);
		}
	}
	
	//连接mt服务器
	//$info = 0 （正确）
	public function ConnectMT4($server,$manager,$password) {
		loginfo("MtApiModel","method:ConnectMT4  args:".$server."|".$manager);
		
		if($this->version == 5){
			if (!$this->mt5webapi->IsConnected()){
				$serverArr = explode(':',$server);
				$error_code = $this->mt5webapi->Connect($serverArr[0], $serverArr[1], 300, $manager, $password);
				loginfo("MtApiModel","Connect return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
				if ($error_code != MTRetCode::MT_RET_OK){
					//
				}
				return array('ret' => $error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$lstatus = $this->mt4svrapi->Connect($server);
			loginfo("MtApiModel","Connect return:".$lstatus.' msg:'.$this->_getCodeMsg($lstatus));
			if ($lstatus > 0) {
				return array('ret' => $lstatus, 'info' => $this->_getCodeMsg($lstatus));
			}
			loginfo("MtApiModel","method:ConnectMT4.Login");
			$info = $this->mt4svrapi->Login($manager, $password);
			loginfo("MtApiModel","Login return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret' => $info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//入金
	//args:161104|14900.00|Deposit maxib#24|
	//$inid = 16795615
	//-88 = 重复入金
	//-1 = 入金失败
	public function balance($user,$number,$comment,$isrelease = 1) {
		loginfo("MtApiModel","method:balance  args:".$user."|".$number."|".$comment."|".$isrelease);
		
		if($this->version == 5){
			$error_code = $this->mt5webapi->TradeBalance($user, MTEnDealAction::DEAL_BALANCE, $number, $comment, $inid,$margin_check=true);
			loginfo("MtApiModel","return:".$inid." msg:".MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>$inid, 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>-1, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$inid=$this->mt4svrapi->Balance($user, $number, $comment,$isrelease);
			loginfo("MtApiModel","return:".$inid." msg:".$this->_getCodeMsg($inid));
			return array('ret'=>$inid, 'info' => $this->_getCodeMsg($inid));
		}
	}
	
	//检测mt密码是否正确
	//$info = 0 （正确）
	public function checkpassword($user,$password) {
		loginfo("MtApiModel","method:checkpassword  args:".$user);
		
		if($this->version == 5){
			//0=正确，3006=错误
			$error_code = $this->mt5webapi->UserPasswordCheck($user,$password,MTProtocolConsts::WEB_VAL_USER_PASS_MAIN);
			loginfo("MtApiModel","var=5 result:".$error_code);
			return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
		}else{
			$info = $this->mt4svrapi->CheckPass($user,$password);
			loginfo("MtApiModel","return:".$info." msg:".$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//获取货币对	//Gold-K@XAUUSD#100.000000#cfd#USD|,Silver-K@XAGUSD#5000.000000#cfd#USD|,CFD-K@BRENT#100.000000#cfd#USD|WTI#100.000000#cfd#USD|,Indexes-K@AUS200#1.000000#cfd#AUD|CAC40#1.000000#cfd#EUR|NAS100#1.000000#cfd#USD|WS30#1.000000#cfd#USD|SP500#1.000000#cfd#USD|DAX30#1.000000#cfd#EUR|JPN225#100.000000#cfd#JPY|UK100#1.000000#cfd#GBP|,FX-A@USDJPY#100000.000000#forex#USD|EURGBP#100000.000000#forex#EUR|,FX-B@AUDCAD#100000.000000#forex#AUD|AUDJPY#100000.000000#forex#AUD|EURUSD#100000.000000#forex#EUR|GBPUSD#100000.000000#forex#GBP|,FX-C@AUDUSD#100000.000000#forex#AUD|GBPCAD#100000.000000#forex#GBP|EURCAD#100000.000000#forex#EUR|NZDJPY#100000.000000#forex#NZD|EURJPY#100000.000000#forex#EUR|USDCAD#100000.000000#forex#USD|GBPJPY#100000.000000#forex#GBP|NZDUSD#100000.000000#forex#NZD|CADJPY#100000.000000#forex#CAD|USDCHF#100000.000000#forex#USD|CADCHF#100000.000000#forex#CAD|NZDCAD#100000.000000#forex#NZD|,FX-D@USDSGD#100000.000000#forex#USD|EURCHF#100000.000000#forex#EUR|CHFJPY#100000.000000#forex#CHF|EURAUD#100000.000000#forex#EUR|GBPCHF#100000.000000#forex#GBP|NZDCHF#100000.000000#forex#NZD|AUDCHF#100000.000000#forex#AUD|,FX-E@EURNZD#100000.000000#forex#EUR|GBPAUD#100000.000000#forex#GBP|AUDNZD#100000.000000#forex#AUD|GBPNZD#100000.000000#forex#GBP|,FX-F@EURTRY#100000.000000#forex#EUR|USDTRY#100000.000000#forex#USD|,FX-G@USDPLN#100000.000000#forex#USD|USDHKD#100000.000000#forex#USD|USDSEK#100000.000000#forex#USD|,FX-H@USDNOK#100000.000000#forex#USD|,Indexes-V@AUS200.v#10.000000#cfd#AUD|CAC40.v#10.000000#cfd#EUR|NAS100.v#10.000000#cfd#USD|WS30.v#10.000000#cfd#USD|SP500.v#10.000000#cfd#USD|DAX30.v#10.000000#cfd#EUR|JPN225.v#10.000000#cfd#JPY|UK100.v#10.000000#cfd#GBP|,Crypto-A@BTCUSD#1.000000#cfd#USD|,Crypto-A2@ETHUSD#1.000000#cfd#USD|LTCUSD#1.000000#cfd#USD|,FX-1@EURUSD.v#100000.000000#forex#EUR|GBPUSD.v#100000.000000#forex#GBP|AUDJPY.v#100000.000000#forex#AUD|AUDCAD.v#100000.000000#forex#AUD|EURCAD.v#100000.000000#forex#EUR|NZDJPY.v#100000.000000#forex#NZD|EURJPY.v#100000.000000#forex#EUR|USDCAD.v#100000.000000#forex#USD|NZDUSD.v#100000.000000#forex#NZD|USDCHF.v#100000.000000#forex#USD|AUDUSD.v#100000.000000#forex#AUD|EURCHF.v#100000.000000#forex#EUR|,FX-2@USDJPY.v#100000.000000#forex#USD|EURGBP.v#100000.000000#forex#EUR|NZDCAD.v#100000.000000#forex#NZD|,FX-3@GBPJPY.v#100000.000000#forex#GBP|GBPCAD.v#100000.000000#forex#GBP|CADJPY.v#100000.000000#forex#CAD|USDSGD.v#100000.000000#forex#USD|EURTRY.v#100000.000000#forex#EUR|USDTRY.v#100000.000000#forex#USD|,FX-4@CADCHF.v#100000.000000#forex#CAD|CHFJPY.v#100000.000000#forex#CHF|EURAUD.v#100000.000000#forex#EUR|GBPCHF.v#100000.000000#forex#GBP|NZDCHF.v#100000.000000#forex#NZD|AUDCHF.v#100000.000000#forex#AUD|GBPNZD.v#100000.000000#forex#GBP|GBPAUD.v#100000.000000#forex#GBP|AUDNZD.v#100000.000000#forex#AUD|,FX-5@EURNZD.v#100000.000000#forex#EUR|,FX-6@,FX-7@USDNOK.v#100000.000000#forex#USD|,FX-8@USDPLN.v#100000.000000#forex#USD|USDSEK.v#100000.000000#forex#USD|,Gold@Gold#10.000000#cfd#USD|,Silver@Silver#100.000000#cfd#USD|,CFD-S@BRENTSPOT#100.000000#cfd#USD|WTISPOT#100.000000#cfd#USD|,Crypto-2@ETHUSD.v#1.000000#cfd#USD|LTCUSD.v#1.000000#cfd#USD|,Stock@AAPLUS#1.000000#cfd#USD|AAUS#1.000000#cfd#USD|ABTUS#1.000000#cfd#USD|ADBEUS#1.000000#cfd#USD|AMGUS#1.000000#cfd#USD|AMZNUS#1.000000#cfd#USD|AXPUS#1.000000#cfd#USD|BABAUS#1.000000#cfd#USD|BACUS#1.000000#cfd#USD|BAUS#1.000000#cfd#USD|BAXUS#1.000000#cfd#USD|BMYUS#1.000000#cfd#USD|BRKUS#1.000000#cfd#USD|CATUS#1.000000#cfd#USD|CLUS#1.000000#cfd#USD|CSCOUS#1.000000#cfd#USD|DISUS#1.000000#cfd#USD|EBAYUS#1.000000#cfd#USD|FBUS#1.000000#cfd#USD|FDXUS#1.000000#cfd#USD|FUS#1.000000#cfd#USD|GEUS#1.000000#cfd#USD|GOOGUS#1.000000#cfd#USD|GSUS#1.000000#cfd#USD|HPQUS#1.000000#cfd#USD|INTCUS#1.000000#cfd#USD|JNJUS#1.000000#cfd#USD|JPMUS#1.000000#cfd#USD|KOUS#1.000000#cfd#USD|LMTUS#1.000000#cfd#USD|MANUUS#1.000000#cfd#USD|MAUS#1.000000#cfd#USD|MCDUS#1.000000#cfd#USD|MDLZUS#1.000000#cfd#USD|MMMUS#1.000000#cfd#USD|MRKUS#1.000000#cfd#USD|MSFTUS#1.000000#cfd#USD|MSUS#1.000000#cfd#USD|NFLXUS#1.000000#cfd#USD|NKEUS#1.000000#cfd#USD|NVDAUS#1.000000#cfd#USD|PEPUS#1.000000#cfd#USD|PFEUS#1.000000#cfd#USD|PGUS#1.000000#cfd#USD|PMUS#1.000000#cfd#USD|SBUXUS#1.000000#cfd#USD|SLBUS#1.000000#cfd#USD|TLRUS#1.000000#cfd#USD|TSLAUS#1.000000#cfd#USD|TUS#1.000000#cfd#USD|TWITUS#1.000000#cfd#USD|UPSUS#1.000000#cfd#USD|VUS#1.000000#cfd#USD|VZUS#1.000000#cfd#USD|WMTUS#1.000000#cfd#USD|QCOMUS#1.000000#cfd#USD|XOMUS#1.000000#cfd#USD|ACAFR#1.000000#cfd#EUR|ADSDE#1.000000#cfd#EUR|AIFR#1.000000#cfd#EUR|ALOFR#1.000000#cfd#EUR|ALVDE#1.000000#cfd#EUR|BARCUK#1.000000#cfd#GBP|BATSUK#1.000000#cfd#GBP|BAYNDE#1.000000#cfd#EUR|BBVAES#1.000000#cfd#EUR|BMWDE#1.000000#cfd#EUR|BNFR#1.000000#cfd#EUR|BPUK#1.000000#cfd#GBP|BRBYUK#1.000000#cfd#GBP|CAFR#1.000000#cfd#EUR|CBKDE#1.000000#cfd#EUR|CONDE#1.000000#cfd#EUR|CSFR#1.000000#cfd#EUR|DAIDE#1.000000#cfd#EUR|DBKDE#1.000000#cfd#EUR|EOANDE#1.000000#cfd#EUR|FPFR#1.000000#cfd#EUR|FTEFR#1.000000#cfd#EUR|GLEFR#1.000000#cfd#EUR|GSKUK#1.000000#cfd#GBP|BNPFR#1.000000#cfd#EUR|HEIANL#1.000000#cfd#EUR|HMSOUK#1.000000#cfd#GBP|HSBAUK#1.000000#cfd#GBP|MCFR#1.000000#cfd#EUR|MRKDE#1.000000#cfd#EUR|MUV2DE#1.000000#cfd#EUR|ORFR#1.000000#cfd#EUR|RBSUK#1.000000#cfd#EUR|RDSAUK#1.000000#cfd#GBP|REPES#1.000000#cfd#EUR|RNOFR#1.000000#cfd#EUR|RRUK#1.000000#cfd#GBP|RSAUK#1.000000#cfd#GBP|SBRYUK#1.000000#cfd#GBP|SIEDE#1.000000#cfd#EUR|SUFR#1.000000#cfd#EUR|TSCOUK#1.000000#cfd#GBP|ULVRUK#1.000000#cfd#GBP|VODUK#1.000000#cfd#GBP|VOWDE#1.000000#cfd#EUR|AIRFR#1.000000#cfd#EUR|,
	public function GetSymbols($isApi = 0) {
		global $DB;
		global $DRAdmin;
		loginfo("MtApiModel","method:GetSymbols");
		if($this->version == 5){
			if($isApi){
				//从api读取，很慢
				$error_code = $this->mt5webapi->SymbolTotal($symbolCount);
				loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
				if($error_code == 0){
					$list1 = array();
					for($ci = 0;$ci < $symbolCount;$ci++){
						$error_code = $this->mt5webapi->SymbolNext($ci,$symbol);
						if($error_code == 0){
							$path = explode('\\',$symbol->Path);
							$list1[$path[1]][] = $symbol->Symbol . '#' . $symbol->ContractSize . '#' . ($symbol->CalcMode == 0 ? 'forex' : 'cfd') . '#' . $symbol->CurrencyProfit;
						}
					}
					$info = '';
					foreach($list1 as $key=>$val){
						$info .= $key . '@' . implode('|',$val) . '|,';
					}
					return array('ret'=>$info, 'info' => MTRetCode::GetError(0));
				}else{
					return array('ret'=>'', 'info' => MTRetCode::GetError($error_code));
				}
			}else{
				//直接读取mt5的数据库
				$list = $DB->getDTable("select * from " . $DRAdmin['mt4dbname'] . ".mt5_symbols");
				$list1 = array();
				foreach($list as $key=>$val){
					$path = explode('\\',$val['Path']);
					/*
					IMTConSymbol::EnCalcMode
					交易品种的利润和预付款计算的类型以 IMTConSymbol::EnCalcMode 枚举。
					TRADE_MODE_FOREX	0	外汇计算模式。
					TRADE_MODE_CFD		2	CFD 计算模式。
					TRADE_MODE_CFDINDEX	3	CFDIndex 计算模式。
					*/
					$list1[$path[1]][] = $val['Symbol'] . '#' . $val['ContractSize'] . '#' . ($val['CalcMode'] == 0 ? 'forex' : 'cfd') . '#' . $val['CurrencyProfit'];
				}
				$info = '';
				foreach($list1 as $key=>$val){
					$info .= $key . '@' . implode('|',$val) . '|,';
				}
				return array('ret'=>$info, 'info' => MTRetCode::GetError(0));
			}
		}else{
			$info = $this->mt4svrapi->GetSymbols();
			loginfo("MtApiModel","return:".$info);
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//获取用户组
	//$info = manager|7952|10|11|16|16-0|16-0-2|16-1|16-1-2|16-2|16-2-2|16-3|16-3-2|16-A|16C|16-u30|19|
	//manager|3311|4411|5812|7951|7952|7953|7954|7955|7956|7957|7958|7959|7960|7961|7962|7963|7964|7965|7966|7801|7955-1|795512|7962-1|79552|79624|79553|79623|79622|79556|79626|7955GD|79624q|
	public function GetGroups($isApi = 0) {
		global $DB;
		global $DRAdmin;
		loginfo("MtApiModel","method:GetGroups");
		if($this->version == 5){
			if($isApi){
				//从api读取，很慢
				$error_code = $this->mt5webapi->GroupTotal($groupCount);
				loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
				if($error_code == 0){
					$groupList = '';
					for($ci = 0;$ci < $groupCount;$ci++){
						$error_code = $this->mt5webapi->GroupNext($ci,$group);
						if($error_code == 0){
							$groupList .= $group->Group . '|';
						}
					}
					return array('ret'=>$groupList, 'info' => MTRetCode::GetError(0));
				}else{
					return array('ret'=>'', 'info' => MTRetCode::GetError($error_code));
				}
			}else{
				//直接读取mt5的数据库
				$info = '';
				$list = $DB->getDTable("select * from " . $DRAdmin['mt4dbname'] . ".mt5_groups");
				foreach($list as $key=>$val){
					$info .= $val['Group'] . '|';
				}
				return array('ret'=>$info, 'info' => MTRetCode::GetError(0));
			}
		}else{
			$info = $this->mt4svrapi->GetGroups();
			loginfo("MtApiModel","return:".$info." msg:".$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//开户
	//args : 161506||Xiaomeng Shi|16|0|jackzhu6160@163.com|100|110102198911082327|18610002175
	//$result = 161512|0jpgPka|lpR4vEf （user|pwd|rdpwd）
	//Network problem
	//Common error （占号问题）
	public function NewAccount($loginid=0,$password,$username,$group,$agent,$email,$leverage,$idcardno,$phone) {		
		loginfo("MtApiModel","method:NewAccount args:".$loginid."|(pwd)|".$username."|".$group."|".$agent.'|'.$email.'|'.$leverage.'|'.$idcardno.'|'.$phone);
		if($this->version == 5){
			$user = $this->mt5webapi->UserCreate();
			$user->Login = $loginid;
			$user->MainPassword = $password;
			$user->InvestPassword = $password;
			$user->Name = $username;
			$user->Group = $group;
			$user->Leverage = $leverage;
			if(strlen($agent)){
				$user->Agent = $agent;
			}
			if(strlen($email)){
				$user->Email = $email;
			}
			if(strlen($idcardno)){
				$user->ID = $idcardno;
			}
			if(strlen($phone)){
				$user->Phone = $phone;
			}
			
			$error_code = $this->mt5webapi->UserAdd($user,$nuser);
			loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>$loginid . '|' . $password . '|' . $password, 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$result=$this->mt4svrapi->NewAccount($loginid,$password,$username,$group,$agent,$email,$leverage,$idcardno,$phone);
			loginfo("MtApiModel","return:".$result." msg:".$this->_getCodeMsg($result));
			return array('ret'=>$result, 'info' => $this->_getCodeMsg($result));
		}
	}
	
	//暂未使用
	public function Release() {
		loginfo("MtApiModel","method:close");
		$info = $this->mt4svrapi->Releaselt();
		loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
		return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
	}
	
	//内部使用，暂未使用
	public function IsConnect() {
		loginfo("MtApiModel","method:IsConnect");
		$info = $this->mt4svrapi->IsConnect();
		loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
		return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
	}
	
	//设置账户分组
	//args:169325|7955-1
	//$info = res:OK
	public function ChangeGroup($login, $newgroup) {
		loginfo("MtApiModel","method:ChangeGroup args:".$login."|".$newgroup);
		if($this->version == 5){
			$user = new MTUser();
			$user->Login = $login;
			$user->Group = $newgroup;
			
			$error_code = $this->mt5webapi->UserUpdate($user,$nuser);
			loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>'res:OK', 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info = $this->mt4svrapi->ChangeGroup($login, $newgroup);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//设置账户杠杆
	//args:166107|500
	//$info = res:OK
	public function ChangeLevel($login, $level) {
		loginfo("MtApiModel","method:ChangeLevel args:".$login."|".$level);
		if($this->version == 5){
			$user = new MTUser();
			$user->Login = $login;
			$user->Leverage = $level;
			
			$error_code = $this->mt5webapi->UserUpdate($user,$nuser);
			loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>'res:OK', 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info = $this->mt4svrapi->ChangeLevel($login, $level);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//设置账户只读属性
	//args:166005|0
	//args:174484|1
	//$info = res:OK
	public function SetReadOnly($login,$readonly) {
		loginfo("MtApiModel","method:SetReadOnly args:".$login."|".$readonly);
		if($this->version == 5){
			return array('ret'=>0, 'info' => '未实现');
		}else{
			$info = $this->mt4svrapi->SetReadOnly($login, $readonly);
			loginfo("MtApiModel","return:".$info." msg:".$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//设置账户禁用属性
	//args:166005|0
	//args:166005|1
	//$info = res:OK
	public function SetEnable($login,$enabled) {
		loginfo("MtApiModel","method:SetDisable args:".$login."|".$enabled);
		if($this->version == 5){
			return array('ret'=>0, 'info' => '未实现');
		}else{
			$info = $this->mt4svrapi->SetEnable($login, $enabled);
			loginfo("MtApiModel","return:".$info." msg:".$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//修改mt密码--个人用户用，$change_investor=1=只读，其它值为交易
	//$info = res:OK
	//invalid password
	public function ChangePass($login,$oldpassword,$newpassword,$change_investor) {
		loginfo("MtApiModel","method:ChangePass args:".$login.''.$change_investor);
		if($this->version == 5){
			//0=正确，3006=错误
			$error_code = $this->mt5webapi->UserPasswordCheck($login,$oldpassword,$change_investor == 1 ? MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR : MTProtocolConsts::WEB_VAL_USER_PASS_MAIN);
			if($error_code == 0){
				return $this->SetPass($login,$newpassword,$change_investor);
			}else{
				return array('ret'=>'invalid password', 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info = $this->mt4svrapi->ChangePass($login, $oldpassword,$newpassword,$change_investor);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//$info = 值，比如12
	public function GetTimezone() {
		loginfo("MtApiModel","method:GetTimezone ");
		if($this->version == 5){
			$error_code = $this->mt5webapi->TimeGet($mtct);
			loginfo("MtApiModel","return:".$info.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				$info = intval($mtct->TimeZone / 60);
				return array('ret'=>$info, 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>0, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info = $this->mt4svrapi->GetTimezone();
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//MT4余额
	//args:101018|0
	//$info = 余额，如111.01
	public function GetValidMoney($login,$release) {
		loginfo("MtApiModel","method:GetValidMoney args:".$login."|".$release);
		if($this->version == 5){
			$error_code = $this->mt5webapi->UserAccountGet($login,$result);
			loginfo("MtApiModel","return:".json_encode($result,JSON_FORCE_OBJECT).' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				$info = floatval($result->Balance);
				return array('ret'=>$info, 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>0, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info = $this->mt4svrapi->GetValidMoney($login,$release);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//生成新密码--管理员用，$change_investor=1=只读，其它值为交易
	//args:160418|0
	//args:172220|1
	//$info = res:OK
	public function SetPass($login,$password,$change_investor) {
		loginfo("MtApiModel","method:SetPass args:".$login."|".$change_investor);
		if($this->version == 5){
			$error_code = $this->mt5webapi->UserPasswordChange($login, $password, $change_investor == 1 ? MTProtocolConsts::WEB_VAL_USER_PASS_INVESTOR : MTProtocolConsts::WEB_VAL_USER_PASS_MAIN);
			loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>'res:OK', 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{		
			$info = $this->mt4svrapi->SetPass($login,$password,$change_investor);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//赠金，暂未使用
	//args:123470566|314|Credit maxib#|
	//$inid = -7
	//-100（正确？）
	public function Credit($user,$number,$comment,$isrelease = 1) {
		loginfo("MtApiModel","method:Credit args:".$user."|".$number."|".$comment."|".$isrelease);
		if($this->version == 5){
			//这个要换，换成入金为信用金，而不是余额
			$error_code = $this->mt5webapi->TradeBalance($user, MTEnDealAction::DEAL_CREDIT, $number, $comment, $inid,$margin_check=true);
			loginfo("MtApiModel","return:".$inid." msg:".MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>$inid, 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>-1, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$inid=$this->mt4svrapi->Credit($user, $number, $comment,$isrelease);
			loginfo("MtApiModel","return:".$inid.' msg:'.$this->_getCodeMsg($inid));
			return array('ret'=>$inid, 'info' => $this->_getCodeMsg($inid));
		}
	}
	
	//暂未使用
	//args:160177||NULL|101005||-1||
	//args:177247|Nguyen duc thiep|NULL|-1||-1|030079006716|
	//$info = res:OK
	public function UpdateAccount($login, $name, $group, $agent, $email, $leverage, $idNo, $phone) {
		loginfo("MtApiModel","UpdateAccount args:".$login."|".$name."|".$group."|".$agent."|".$email."|".$leverage."|".$idNo."|".$phone);
		if($this->version == 5){
			$user = new MTUser();
			$user->Login = $login;
			if(strlen($name)){
				$user->Name = $name;
			}
			if(strlen($group)){
				$user->Group = $group;
			}
			if(strlen($agent)){
				$user->Agent = $agent;
			}
			if(strlen($email)){
				$user->Email = $email;
			}
			if(strlen($leverage)){
				$user->Leverage = $leverage;
			}
			if(strlen($idNo)){
				$user->ID = $idNo;
			}
			if(strlen($phone)){
				$user->Phone = $phone;
			}
			
			$error_code = $this->mt5webapi->UserUpdate($user,$nuser);
			loginfo("MtApiModel","return:".$error_code.' msg:'.MTRetCode::GetError($error_code));
			if($error_code == 0){
				return array('ret'=>'res:OK', 'info' => MTRetCode::GetError($error_code));
			}else{
				return array('ret'=>$error_code, 'info' => MTRetCode::GetError($error_code));
			}
		}else{
			$info=$this->mt4svrapi->UpdateAccount($login, $name, $group, $agent, $email, $leverage, $idNo, $phone);
			loginfo("MtApiModel","return:".$info.' msg:'.$this->_getCodeMsg($info));
			return array('ret'=>$info, 'info' => $this->_getCodeMsg($info));
		}
	}
	
	//仅内部调用
	public function _getCodeMsg($ret) {
		if(is_int($ret)) $ret = abs($ret);
		$msg = C('MTCODE'.$this->version.'.'.$ret);
		if($msg!='') return $msg."(".$ret.")"; else return $ret;
	}
}