<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('已返佣记录') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('查询范围');?>：
                                                    <select name='qscope' id='qscope' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='self'<?php if(_request('qscope') == 'self'){echo ' selected';} ?>><?php echo L('仅自己');?></option>
                                                        <option value='under'<?php if(_request('qscope') == 'under'){echo ' selected';} ?>><?php echo L('下级客户');?></option>
                                                     </select>
                                                </div>
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('客户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('nickname');?>" name="nickname" placeholder="<?php echo L('请输入会员者昵称邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('login');?>" name="login" placeholder="<?php echo L('请输入交易的 MT 账户')?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('订单编号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('ticket');?>" name="ticket" placeholder="<?php echo L('请输入返佣的订单号'); ?>">
                                                </div>
                                                <?php
												$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部种类');?></option>
                                                        <?php
														foreach($types as $key=>$val){
															echo '<option value="' , $val['id'] , '">' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易品种');?>：
                                                    <select name="qsymbol" id="qsymbol" class="form-control">
                                                        <option value=""><?php echo L('全部品种');?></option>                                        
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('返佣时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="time_start" value="<?php echo _request('time_start');?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="time_end" value="<?php echo _request('time_end');?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('平仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="close_time_start" value="<?php echo _request('close_time_start');?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="close_time_end" value="<?php echo _request('close_time_end');?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('返佣状态');?>：
                                                    <select name='qstatus' id='qstatus' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='1'<?php if(_request('qstatus') == '1'){echo ' selected';} ?>><?php echo L('已结算');?></option>
                                                        <option value='0'<?php if(_request('qstatus') == '0'){echo ' selected';} ?>><?php echo L('未结算');?></option>
                                                        <option value='-1'<?php if(_request('qstatus') == '-1'){echo ' selected';} ?>><?php echo L('未返佣');?></option>
                                                     </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('提现订单号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('ticket2');?>" name="ticket2" placeholder="<?php echo L('请输入提现订单号'); ?>">
                                                </div>
                                            	<?php
												$groupsList = $DB->getDTable("select * from t_groups where server_id = {$DRAdmin['server_id']}");
                                                if(count($groupsList) && $DRAdmin['_dataRange'] >= 2){
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('请选择分组');?>" name="GROUP_NAME[]" class="chosen-select" multiple>
                                                        <option value="all_group"><?php echo L('全部');?></option>
                                                        <?php
														foreach($groupsList as $key=>$val){
															echo '<option value="' , $val['group'] , '" hassubinfo="true"';
															if(@in_array($val['group'],$_GET['GROUP_NAME'])){
																echo ' selected';
															}
															echo '>' , $val['group'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<div class="custom-control custom-checkbox">
                                                        <input type="checkbox"<?php if(_request('reject') == 1){echo ' checked';} ?> class="custom-control-input" name="reject" id="reject" value="1"><label class="custom-control-label" for="reject"><?php echo L('剔除组');?></label>
                                                    </div>
                                                </div>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('地区');?>" name="area[]" class="chosen-select" multiple>
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														$citylist = $DB->getField2Arr("select id,city from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 group by city");
														foreach($citylist as $key=>$val){
															echo '<option value="' , $val , '" hassubinfo="true"';
															if(@in_array($val,$_GET['area'])){
																echo ' selected';
															}
															echo '>' , $val , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('剔除MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('T_LOGIN');?>" name="T_LOGIN" placeholder="<?php echo L('多个MT帐号请用英文逗号隔开'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div>
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索返佣'); ?></button>
														<?php
															if(chk_in_access('报表导出')){
																echo '<a class="btn btn-primary" href="#nolink" onclick="go_download()">' , L('报表导出') , '</a> ';
                                                            }
                                                        ?>
                                                    </div>                                                                             
                                                </div>
                                            </form>
                                        </div> <!-- end row -->

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 
                        <!-- end row-->



<?php
$isSearch = 0;


$where = "where SERVER_ID = '" . $DRAdmin['server_id'] . "'";
$whereMtUsers = "where 1 = 1";
$whereMtLogin = "where 1 = 1";
$whereMtTrades = "where 1 = 1";

$parentid = $DRAdmin['id'];
$memberid = $DRAdmin['id'];
if ($DRAdmin['_dataRange'] >= 2) {
	$parentid = "admin";
	$memberid = "admin";
}

if ($_REQUEST['nickname'] != '') {//输入了下级昵称
	$nickname = urldecode($_REQUEST['nickname']);
	$member = $DB->getDRow("select id from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 and (nickname = '" . $nickname . "' or email = '" . $nickname . "')");
	if (!$member) {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号不存在"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}
	_check_member_scope($parentid, $member['id']); //检验是否下级
	$memberid = $member['id'];
	
	$isSearch = 1;
}

if ($_REQUEST['qscope'] == 'under') {
	$memberidarray = getunderCustomerIds($memberid);
	$memberids = implode(",", $memberidarray);
	$where = $where . " and COMM_MEMBER_ID in (" . $memberids . ")";
	
	$isSearch = 1;
} else if ($_REQUEST['qscope'] == 'self') {
	$where = $where . " and COMM_MEMBER_ID in (" . $DRAdmin['id'] . ")";
	
	$isSearch = 1;
} else {
	$memberidarray = getunderCustomerIds($memberid);
	if ($memberidarray)
		$memberidarray = array_merge($memberidarray, array((int) $memberid));
	else
		$memberidarray = array($memberid);
	$memberids = implode(",", $memberidarray);
	$where = $where . " and COMM_MEMBER_ID in (" . $memberids . ")";
}

if (isset($_REQUEST['ticket']) && $_REQUEST['ticket'] != '') {
	$where = $where . " and TICKET =" . $_REQUEST['ticket'];
	
	$isSearch = 1;
}

if (isset($_REQUEST['ticket2']) && $_REQUEST['ticket2'] != '') {
	$ticket2 = $_REQUEST['ticket2'];
	$result = $DB->getDRow("select REL_ID from t_sale_commission_balance where IN_ID = '{$ticket2}'");
	$where = $where . " and BALANCE_ID =" . intval($result['REL_ID']);
	
	$isSearch = 1;
}

if (isset($_REQUEST['login']) && $_REQUEST['login'] != '') {
	$where = $where . " and LOGIN =" . $_REQUEST['login'];
	
	$isSearch = 1;
}

if (isset($_REQUEST['T_LOGIN']) && $_REQUEST['T_LOGIN'] != '') {
	$where = $where . " and LOGIN not in (" . $_REQUEST['T_LOGIN'] . ")";
	
	$isSearch = 1;
}

if (isset($_REQUEST['ID']) && $_REQUEST['ID'] != '') {
	$where = $where . " and BALANCE_ID =" . $_REQUEST['ID'];
	
	$isSearch = 1;
}

if ($DRAdmin['_dataRange'] >= 2) {
	if (_request('qstatus') != '') {
		$where = $where . " and STATUS =" . _request('qstatus');
	} else {
		$where = $where . ' and STATUS in (0,1,-1) ';
	}
} else {
	$where = $where . " and STATUS in (0,1,-1)";
}
if ($_REQUEST['area'] && $DRAdmin['_dataRange'] >= 2) {
	$arearr = array();
	foreach (explode(',', $_REQUEST['area']) as $key => $value) {
		$arearr[] = "'" . $value . "'";
	}
	$whereMtLogin = $whereMtLogin . " and city in (" . implode(',', $arearr) . ")";
	
	$isSearch = 1;
}


$startday = _request('time_start');
$startday = stripos($startday, ":") ? urldecode($startday) : $startday . ' 00:00:00';
$endday = _request('time_end');
$endday = stripos($endday, ":") ? urldecode($endday) : $endday . ' 23:59:59';
if (_request('time_start')) {
	$starttime = strtotime($startday) + 3600 * floatval(8 - $DRAdmin['timezone']);
}
if (_request('time_end')) {
	$endtime = strtotime($endday) + 3600 * floatval(8 - $DRAdmin['timezone']);
}
if ($starttime){
	$where = $where . " and BALANCE_TIME>='" . $starttime . "'";
	
	$isSearch = 1;
}
if ($endtime){
	$where = $where . "  and BALANCE_TIME<='" . $endtime . "'";
	
	$isSearch = 1;
}

if ($_GET['GROUP_NAME']) {
	$group_arr = implode(',', $_GET['GROUP_NAME']);
	if ($group_arr == 'all_group') {
		$group_arr = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'", true);
		$group_arr = implode(',', $group_arr);
	}
	if (strstr($group_arr, '\\')) {
		$group_arr = str_replace('\\', '\\\\', $group_arr);
	}
	$group_arr = explode(',', $group_arr);
	if (_request('reject')) {
		$whereMtUsers = $whereMtUsers . " and `GROUP` not in ('" . implode('\',\'', $group_arr) . "')";
	} else {
		$whereMtUsers = $whereMtUsers . " and `GROUP` in ('" . implode('\',\'', $group_arr) . "')";
	}
	
	$isSearch = 1;
}

if($DRAdmin['ver'] == 5){
	if (_request('close_time_start')){
		$whereMtTrades = $whereMtTrades . " and `Time`>='" . _request('close_time_start') . " 00:00:00'";
		
		$isSearch = 1;
	}
	if (_request('close_time_end')){
		$whereMtTrades = $whereMtTrades . " and `Time`<='" . _request('close_time_end') . " 23:59:59'";
		
		$isSearch = 1;
	}
}else{
	if (_request('close_time_start')){
		$whereMtTrades = $whereMtTrades . " and CLOSE_TIME>='" . _request('close_time_start') . " 00:00:00'";
		
		$isSearch = 1;
	}
	if (_request('close_time_end')){
		$whereMtTrades = $whereMtTrades . " and CLOSE_TIME<='" . _request('close_time_end') . " 23:59:59'";
		
		$isSearch = 1;
	}
}

//交易种类
if (_request('qtype')) {
	if (_request('qsymbol') == '') {
		$symbollist = $DB->getField("select symbol from t_symbol where `type` = '" . _request('qtype') . "'", true);
		if (empty($symbollist)) {
			$symbollist = array('0');
		}
		if($DRAdmin['ver'] == 5){
			$whereMtTrades = $whereMtTrades . " and Symbol in ('" . implode('\',\'', $symbollist) . "')";
		}else{
			$whereMtTrades = $whereMtTrades . " and SYMBOL in ('" . implode('\',\'', $symbollist) . "')";
		}
	} else {
		if($DRAdmin['ver'] == 5){
			$whereMtTrades = $whereMtTrades . " and Symbol in ('" . _request('qsymbol') . "')";
		}else{
			$whereMtTrades = $whereMtTrades . " and SYMBOL in ('" . _request('qsymbol') . "')";
		}
	}
	
	$isSearch = 1;
}

//-------------------------------------------------------------

if($whereMtUsers != 'where 1 = 1'){
	$whereMtUsers = str_ireplace('where 1 = 1 and ','where ',$whereMtUsers);
	if($DRAdmin['ver'] == 5){
		$where .= " and LOGIN in (select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt5_users {$whereMtUsers})";
	}else{
		$where .= " and LOGIN in (select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt4_users {$whereMtUsers})";
	}
}
if($whereMtLogin != 'where 1 = 1'){
	$whereMtLogin = str_ireplace('where 1 = 1 and ','where ',$whereMtLogin);
	$where .= " and COMM_MEMBER_ID in (select id from t_member {$whereMtLogin} and mtserver = '" . $DRAdmin['server_id'] . "' and status = 1)";
}
if($whereMtTrades != 'where 1 = 1'){
	if($DRAdmin['ver'] == 5){
		$whereMtTrades = str_ireplace('where 1 = 1 and ','where Action in (0,1) and Entry = 1 and ',$whereMtTrades);
		$where .= " and TICKET in (select PositionID from " . $DRAdmin['mt4dbname'] . ".mt5_deals {$whereMtTrades})";
	}else{
		$whereMtTrades = str_ireplace('where 1 = 1 and ','where CMD in (0,1) and ',$whereMtTrades);
		$where .= " and TICKET in (select TICKET from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$whereMtTrades})";
	}
}
	
	
//$a = $DRAdmin['mt4dbname'];
//$join = ' inner join   t_member member on member.id=comm.COMM_MEMBER_ID inner join ' . $a . '.mt4_users users on users.LOGIN = comm.LOGIN inner join ' . $a . '.mt4_trades on ' . $a . '.mt4_trades.ticket = comm.TICKET';
//if ($_REQUEST['area']) {
//	$join .= ' inner join (select city,loginid from t_member a inner join t_member_mtlogin b on a.id=b.member_id where b.status=1 and mtserver=' . $DRAdmin['server_id'] . ') mtlogin on comm.LOGIN=mtlogin.loginid';
//}

	//"select 'vtotal',sum(comm.AMOUNT) as TAMOUNT,count(comm.ID) as TNUM from t_sale_commission_amount0 comm {$join} where {$where}";exit;	
	$totalAmount = $DB->getField2Arr("select 'vtotal',sum(AMOUNT) as TAMOUNT,count(ID) as TNUM from t_sale_commission_amount0 {$where}");

	$totalAmount2 = "select sum(VOLUME) AS TVOLUME FROM (select VOLUME,TICKET,COMM_MEMBER_ID,LOGIN,SERVER_ID,BALANCE_TIME,BALANCE_ID,STATUS FROM t_sale_commission_amount0 GROUP BY TICKET) AS comm {$where}";
	$totalAV = $DB->getDRow($totalAmount2);

//totalAmount['vtotal']['TNUM']

$page     = FGetInt('page');if($page <= 0){$page = 1;}
$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;

$field = empty($_GET['_field']) ? 'ID' : $_GET['_field'];
$order = empty($_GET['_order']) ? ' desc' : " {$_GET['_order']}";

//echo "select comm.*,  member.nickname, nickname, member.email,member.realname realname," . $DRAdmin['mt4dbname'] . ".mt4_trades.SYMBOL," . $DRAdmin['mt4dbname'] . ".mt4_trades.CLOSE_TIME from t_sale_commission_amount0 comm {$join} where {$where} order by {$field} {$order} {$pageSql}";exit;

if(FGetInt('isdownload') > 0){
	$pageSql = '';
}


if($DRAdmin['ver'] == 5){
	//echo "select a.*,b.nickname, b.email,b.realname,c.Symbol as SYMBOL,c.Time as CLOSE_TIME from (select * from t_sale_commission_amount0 {$where} order by {$field} {$order} {$pageSql}) a left join t_member b on a.COMM_MEMBER_ID = b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_deals c on a.TICKET = c.PositionID where c.Entry = 1";
	$list = $DB->getDTable("select a.*,b.nickname, b.email,b.realname,c.Symbol as SYMBOL,c.Time as CLOSE_TIME from (select * from t_sale_commission_amount0 {$where} order by {$field} {$order} {$pageSql}) a left join t_member b on a.COMM_MEMBER_ID = b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_deals c on a.TICKET = c.PositionID where c.Entry = 1 order by {$field} {$order}");
}else{
	$list = $DB->getDTable("select a.*,b.nickname, b.email,b.realname,c.SYMBOL,c.CLOSE_TIME from (select * from t_sale_commission_amount0 {$where} order by {$field} {$order} {$pageSql}) a left join t_member b on a.COMM_MEMBER_ID = b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_trades c on a.TICKET = c.TICKET order by {$field} {$order}");
}


if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('已返佣记录'));

	$titleArr = array(
		L('订单'),
		L('账号'),
		L('英文名'),
		L('邮箱'),
		L('返佣等级'),
		L('交易手数'),
		L('交易种类'),
		L('返佣标准'),
		L('返佣金额'),
		L('返佣时间'),
		L('平仓时间'),
		L('返佣类型'),
		L('计算公式'),
	);
	$n = 0;
	for ($i = 'A'; $i != 'Y'; $i++) {
		if($n >= count($titleArr)){
			continue;
		}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($i . '1', $titleArr[$n]);
		$n++;
	}
	
	$j = 2;
	foreach($list as $key=>$rs){
		$i = 'A';
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TICKET'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['LOGIN'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['email'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  L_level_name($rs['LEVEL_NAME']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['VOLUME']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['SYMBOL'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$fybz = '';
		switch($rs['COMM_TYPE']){
			case 'SCALE':
				$fybz = $rs['LEVEL_FIXED'] . '%';
				break;
			case 'POINT':
				$fybz = '$' . $rs['LEVEL_FIXED'] . '/pip';
				break;
			case 'EQUAL_SCALE':
			case 'UP_SCALE':
				$fybz = $rs['LEVEL_FIXED'] . '%';
				break;
			default:
				$fybz = '$' . $rs['LEVEL_FIXED'];
				break;
		}		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $fybz,PHPExcel_Cell_DataType::TYPE_STRING);
		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['AMOUNT'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  time2mt4zone($rs['BALANCE_TIME'],$DRAdmin['timezone'],'Y-m-d H:i:s'),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['CLOSE_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$fylx = '';
		 if($rs['COMMISSION_PATTERN'] == 0){
			$fylx = L('内佣');
		 }else if ($rs['COMMISSION_PATTERN'] == 1){
			$fylx = L('外佣');
		 }else{
			$fylx = L('平级推荐');
		 }
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $fylx,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['FORMULA'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$j++;
	}

	$saveFilename = date('Ymd-His-') . FRndStr(6) . '.xls';
	$uploadFolder = 'excel/' . date('Y/m/d/');
	if(!is_dir($uploadFolder)){
		mkdir($uploadFolder,0777,true);
	}
	$saveFilenameAbs = $uploadFolder . $saveFilename;

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');//Excel5为xls格式，Excel2007为xlsx格式
	$objWriter->save($saveFilenameAbs);
	
	echo '<script type="text/javascript">window.parent.layer.closeAll();window.location.href="' , $saveFilenameAbs , '"</script>';

	exit;
}
?>


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                    
<div>
<?php
echo '<b>' , L('汇总') , '</b>&nbsp; ';
echo L('金额') , '：$' , $totalAmount['vtotal']['TAMOUNT'] , ' &nbsp;&nbsp;';
echo L('交易量') , '：' , $totalAV['TVOLUME']/100 , L('手') , '&nbsp;&nbsp;';
echo L('笔数') , '：' , $totalAmount['vtotal']['TNUM'] , L('条') , '  ' , L('最新返佣时间') , '：<i class="fa fa-clock-o"></i> ' , date('Y-m-d H:i:s',C('CALC_LAST_TIME')-8*3600+$DRAdmin['timezone']*3600);
?>
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('订单') , '#' , L('账号');?></th>
                                                    <th class="no-sort"><?php echo L('接受返佣用户');?></th>
                                                    <th class="no-sort"><?php echo L('返佣等级');?></th>
                                                    <th class="no-sort"><?php echo L('交易情况');?></th>
                                                    <th class="no-sort" title="<?php echo L('其中包括下级代理的金额'); ?>"><?php echo L('返佣标准');?></th>
                                                    <th class="no-sort"><?php echo L('返佣金额');?></th>
                                                    <th class="no-sort"><?php echo L('平台时间');?></th>
                                                    <th class="no-sort"><?php echo L('返佣类型');?></th>
                                                    <th class="no-sort"><?php echo L('计算公式');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($totalAmount['vtotal']['TNUM']);
		
		$page = FGetInt('page');
		$pagersize = 20;
		$pageConfig = array(
			'recordCount'=>$recordCount,
			'pagesize'=>$pagersize,
			'pageCurrIndex'=>$page,
			'pageMainLinks'=>5,
			'tplRecordCount'=>L('_RECORDS_条记录，第_PAGE_/_PAGES_页'),
			'showRecordCount'=>true,
			'showPrevPage'=>true,
			'showNextPage'=>true,
		);
		$cnPager = new CPager($pageConfig);

		if(count($list) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			if($mtserver['ver'] ==  5){
				$count_ = 3;
			}else{
				$count_ = 4;
			}
			
			foreach($list as $key=>$rs){
				echo '<tr>';
				echo '<td>' , $rs['TICKET'] , '<font color="red"><b>#</b></font>' , $rs['LOGIN'] , '</td>';
				echo '<td>' , L('英文名') , '：' , $rs['nickname'] , '<br/>';
				echo $rs['realname'] , '<br/>';
				echo L('邮箱') , '：<span class="lookemail">' , hideStr($rs['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['email'] , '"></i></td>';
				echo '<td>' , L_level_name($rs['LEVEL_NAME']) , '</td>';
				echo '<td>' , $rs['VOLUME']/100 , ' ' , L('手') , ' ' , $rs['SYMBOL'] , '</td>';
				echo '<td>';
				switch($rs['COMM_TYPE']){
					case 'SCALE':
						echo $rs['LEVEL_FIXED'] , '%';
						break;
					case 'POINT':
						echo '$' , $rs['LEVEL_FIXED'] , '/pip';
						break;
					case 'EQUAL_SCALE':
					case 'UP_SCALE':
						echo $rs['LEVEL_FIXED'] , '%';
						break;
					default:
						echo '$' , $rs['LEVEL_FIXED'];
						break;
				}
				echo '</td>';
				echo '<td>';
					if($rs['AMOUNT'] == '0'){
						echo '<font color="grey">$' , $rs['AMOUNT'] , '</font>';
					}else if($rs['AMOUNT'] < '0'){
						echo '<font color="red">$' , $rs['AMOUNT'] , '</font>';
					}else{
				    	echo '<font color="blue">$' , $rs['AMOUNT'] , '</font>';
					}
				echo '</td>';
				echo '<td class="center">';
					echo L('返佣') , '：' , time2mt4zone($rs['BALANCE_TIME'],$DRAdmin['timezone'],'Y-m-d H:i:s') , '<br/>';
					if($rs['CLOSE_TIME'] != '1970-01-01 00:00:00' && $rs['CLOSE_TIME'] != ''){
						echo L('平仓') , '：' , $rs['CLOSE_TIME'];
					}
				echo '</td>';
				echo '<td>';
					 if($rs['COMMISSION_PATTERN'] == 0){
					 	echo L('内佣');
					 }else if ($rs['COMMISSION_PATTERN'] == 1){
					 	echo L('外佣');
					 }else{
					 	echo '-';
					 }
					 echo '<br>';
					 $tempArr_ = explode('-',$rs['bonusKey']);
					 foreach($tempArr_ as $key_=>$val_){
						 if($key_ > 0){
							 echo '-';
						 }
						 echo L($val_);
					 }
				echo '</td>';
				echo '<td title="' , $rs['MEMO'] , '">' , $rs['FORMULA'] , '</td>';
				echo '</tr>';
			}
		}
?>
                                            </tbody>
                                        </table>

<?php
echo $cnPager->FGetPageList();
?>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->
                    
                 
<script>
lang_go_download_isSearch = '<?php echo $isSearch;?>';
</script>


		<?php
        require_once('footer.php');
        ?>

        <!-- third party js -->
        <script src="/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="/assets/js/vendor/buttons.print.min.js"></script>
        <script src="/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->
        
        <script src="/assets/js/layer/layer.js"></script>

		<script src="/assets/js/datapicker/js/bootstrap-datepicker.min.js"></script>
        <link href="/assets/js/datapicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <?php
        if($CurrLangName == 'zh-cn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>';
			$DatepickerLangName = 'zh-CN';
		}else if($CurrLangName == 'zh-vn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.vi.min.js"></script>';
			$DatepickerLangName = 'vi';
		}
		?>
        <script>
			$("#commentForm .layer-date").datepicker({
				<?php
				if(strlen($DatepickerLangName)){
					echo 'language: "' , $DatepickerLangName , '",';
				}
				?>
				keyboardNavigation: !1,
				forceParse: 1,
				autoclose: !0,
				clearBtn: !0,
				format: 'yyyy-mm-dd'
			});
		</script>

        <script>
			$(document).ready(function() {
				"use strict";

				$("#basic-datatable").DataTable({
					paging:false,//是否允许表格分页
					info:false,//控制是否显示表格左下角的信息
					lengthChange: false,//是否允许用户改变表格每页显示的记录数
					searching: false,//是否允许Datatables开启本地搜索
					ordering: false,//是否允许Datatables开启排序
					aoColumnDefs: [{ 
						bSortable: false, 
						aTargets: ["no-sort"] 
					}]
				});

			});
        </script>

        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>   
        <script>
			$(".chosen-select").chosen( {width: "100%"});
        </script>
        
        
        
        
        
        
        
        
<script type="text/javascript">  
	init_findinfo();
    	
   	$('#qtype').val('<?php echo _request('qtype');?>');   	
	$('#qstatus').val('<?php echo _request('qstatus');?>');
	$('#qscope').val('<?php echo _request('qscope');?>');
	
	$(document).on("click","#downBtns",function(){
  	//$("#downBtns").click(function(){
        var value=$("#commentForm").serialize();
        document.location.href="{:U('Commission/down_view_details',array('act'=>'down'))}?"+value;
    });
  	
	<?php
	$symbols = $DB->getDTable("select * from t_symbol where server_id = '{$DRAdmin['server_id']}'");
	$symbols = json_encode($symbols);
	$symbols = addslashes($symbols);
	?>
	var symbol_json = '<?php echo $symbols; ?>';
	$('#qtype').change(function(){
		var type =  $(this).val();
		changesymbole(type);
	})
	
	
	function changesymbole(type){
		var json_obj = eval('(' + symbol_json + ')');
		$('#qsymbol').empty();
		$("#qsymbol").append("<option value=''><?php echo L('全部品种'); ?></option>"); 
		for(var o in json_obj){ 
			if(json_obj[o].type == type){
				$("#qsymbol").append("<option value='"+json_obj[o].symbol+"'>"+json_obj[o].symbol+"</option>"); 
			}
			//console.log(json_obj[o]);
		}
	}
	changesymbole('<?php echo _request('qtype');?>');
	$("#qsymbol").val('<?php echo _request('qsymbol');?>');

    $(".chosen-select").chosen( {width: "100%"});
    $("#GROUP_NAME").change(function(){
        var group = $("#GROUP_NAME  option:selected").val();
        if(group=='all_group'){
            chose_mult_set_ini('#GROUP_NAME','all_group');
        }
    })

    function chose_mult_set_ini(select, values) {
        var arr = values.split(',');
        var length = arr.length;
        var value = '';
        //console.log(arr);
        newarr = trimSpace(arr);
        $(select).val(newarr);
        $(select).trigger("chosen:updated");  
    }

    function trimSpace(array){
        for(var i = 0 ;i<array.length;i++){
            if(array[i] == "" || typeof(array[i]) == "undefined"){
                array.splice(i,1);
                i= i-1;     
            }
        }
        return array;
    }
	
  	
</script>
        
        
        
        
    </body>
</html>
