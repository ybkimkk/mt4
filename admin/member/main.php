<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

$SearchQ = FRequestStr('searchQ');
$SearchLoginid = FRequestStr('searchLoginid');
$SearchStatus = FRequestStr('searchStatus');
$SearchTreenode = FRequestStr('searchTreenode');
$SearchRoleType = FRequestInt('searchRoleType');
$SearchCommission = FRequestStr('searchCommission');
$SearchUserType = FRequestStr('searchUserType');
$SearchLevelType = FRequestInt('searchLevelType');
$SearchSTime = FRequestStr('searchSTime');
$SearchETime = FRequestStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}
$SearchIsS = FRequestStr('searchIsS');

if (strlen($SearchSTime)) {
	$starttime = strtotime($SearchSTime);
} else {
	$starttime = 0;
}
if (strlen($SearchETime)) {
	$endtime = strtotime($SearchETime);
} else {
	$endtime = time();
}

$ranklist = $DB->getDTable("select * from `t_ib_rank` where `server_id` = '{$DRAdmin['server_id']}' and `model_type` = 'agent' and `status` = 1 order by rank asc");
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('客户列表') , getCurrMt4ServerName();?></h4>
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
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get">
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label"><?php echo L('关键词'); ?>：</label>
                                                    <input type="text" class="form-control" minlength="2" value="<?php echo $SearchQ;?>" name="searchQ" id="searchQ" placeholder="<?php echo L('昵称') , '、' , L('手机') , '、' , L('邮箱'); ?>">
                                                    <!--<a id="showtree" href="#nolink" class="btn btn-warning btn-xs"><?php echo L('查看'); ?></a>-->
                                                 </div>
                                                 <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label"><?php echo L('MT账号'); ?>：</label>
                                                    <input type="text" class="form-control" minlength="2" value="<?php echo $SearchLoginid;?>" name="searchLoginid" id="searchLoginid" placeholder="<?php echo L('MT账号'); ?>">
                                                 </div>
												<div class="form-group mr-sm-2 mb-sm-2">
													<label class="control-label"><?php echo L('返佣结算'); ?>：</label>
													<select name='searchCommission' id="commission_query" class='form-control'>
														<option value=''><?php echo L('全部'); ?>  </option>
														<option value='untomt'<?php if($SearchCommission == 'untomt'){echo ' selected';} ?>><?php echo L('返佣未提现'); ?> </option>
													 </select>
												</div>
                                                <?php
													$role_types = $DB->getDTable("select * from `t_role` where `status` = 1");
													if($DRAdmin['_dataRange'] >= 2){
                                                ?>
                                                	<div class="form-group mr-sm-2 mb-sm-2">
                                                        <label class="control-label"><?php echo L('角色类型'); ?>：</label>
                                                        <select name='searchRoleType' id='searchRoleType' class='form-control'>
                                                            <option value=''><?php echo L('全部'); ?>  </option>
                                                            <?php
															foreach($role_types as $key=>$val){
																echo '<option value="' , $val['id'] , '"' , $SearchRoleType == $val['id'] ? ' selected' : '' , '>' , $val['name'] , '</option>';
															}
															?>
                                                        </select>
                                                    </div>
                                                <?php
                                                	}
													if($DRAdmin['_dataRange'] >= 1){
                                                ?>
                                                	<div class="form-group mr-sm-2 mb-sm-2">
                                                        <label class="control-label"><?php echo L('客户类型'); ?> ：</label>
                                                        <div id='cus_type_div'>
                                                            <select name='searchUserType' id="userType_query" class='form-control'>
                                                                <option value=''><?php echo L('全部'); ?>  </option>
                                                                <option value='agent'<?php if($SearchUserType == 'agent'){echo ' selected';} ?>><?php echo L('代理'); ?></option>
                                                                <option value='direct'><?php if($SearchUserType == 'direct'){echo ' selected';} ?><?php echo L('直客'); ?></option>
                                                                <option value='member'<?php if($SearchUserType == 'member'){echo ' selected';} ?>><?php echo L('员工'); ?></option>
                                                            </select>
                                                        </div>
                                                        <div id="level_type_div">
                                                            <select name='searchLevelType' id="level_type_query" class='form-control'>
                                                                <option value=''><?php echo L('全部'); ?>  </option>
                                                                <?php
                                                                    for($i=1;$i<=C('MAX_LEVEL');$i++){
                                                                        echo '<option value="' , $i , '"' , $SearchLevelType == $i ? ' selected' : '' , '>' , $i , L('级') , '</option>';
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php
                                                	}
                                                ?>
                                                    <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div class="input-daterange input-group">
                                                            <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                            <div class="input-group-prepend">
                                                              <div class="input-group-text"><?php echo L('到');?></div>
                                                            </div>
                                                            <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                        </div>
                                                    </div>
													<?php
                                                        if($DRAdmin['_dataRange'] >= 2){
                                                    ?>
                                                    	<div class="form-group mr-sm-2 mb-sm-2">
                                                            <label class="control-label"><?php echo L('状态'); ?> ：</label>
                                                            <div id='cus_type_div'>
                                                                <select name='searchStatus' id="searchStatus" class='form-control'>
                                                                    <option value=''><?php echo L('全部'); ?>  </option>
                                                                    <option value='1'<?php if($SearchStatus === '1'){echo ' selected';}?>><?php echo L('已激活'); ?></option>
                                                                    <option value='0'<?php if($SearchStatus === '0'){echo ' selected';}?>><?php echo L('待激活'); ?></option>
                                                                    <option value='-1'<?php if($SearchStatus === '-1'){echo ' selected';}?>><?php echo L('禁用'); ?></option>
                                                                    <option value='-2'<?php if($SearchStatus === '-2'){echo ' selected';}?>><?php echo L('锁定'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
													<?php
                                                        }
                                                    ?>    
                                                    	<div class="form-group mr-sm-2 mb-sm-2">
                                                            <label class="control-label"><?php echo L('标记'); ?>(S)：</label>
                                                            <div id='cus_type_div'>
                                                                <select name='searchIsS' id="searchIsS" class='form-control'>
                                                                    <option value=''><?php echo L('全部'); ?>  </option>
                                                                    <option value='1'<?php if($SearchIsS === '1'){echo ' selected';}?>><?php echo L('是'); ?></option>
                                                                    <option value='2'<?php if($SearchIsS === '2'){echo ' selected';}?>><?php echo L('否'); ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div>
                                                        <input type='hidden' name='searchTreenode' id="searchTreenode" value="<?php echo $SearchTreenode;?>" />
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索客户'); ?></button>
														<?php
                                                            if(chk_in_access('添加客户')){
																echo '<a href="?clause=addinfo" class="btn btn-primary btn-bitbucket"><i class="fa fa-user-md"></i>&nbsp;' , L('添加客户') , '</a> ';
                                                            }
															if(chk_in_access('下载客户信息')){
																echo '<a class="btn btn-primary" href="#nolink" onclick="go_download()">' , L('下载客户信息') , '</a> ';
                                                            }
															if(chk_in_access('登录日志')){
																echo '<a href="?clause=member_login_log" class="btn btn-primary btn-bitbucket">' , L('登录日志') , '</a> ';
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
		$where = "where `server_id` = '{$DRAdmin['server_id']}'";
		$where .= " and `status` in (0,1)";
		
		
		$isSearch = 0;
		
		$orderByCase = '';
		$lineHeightIds = array();
		//这两个条件，一并查询出下级账号
		if ($SearchQ || $SearchLoginid) {
			$where1 = $where;
			if ($SearchQ) {
				$where1 .= " and (`nickname` like '%" . $SearchQ . "%' or `phone` like '%" . $SearchQ . "%' or `email` like '%" . $SearchQ . "%' or `chineseName` like'%" . $SearchQ . "%')";
			}
			if ($SearchLoginid) {
				$memberlogin = $DB->getDRow("select member_id as id from `t_member_mtlogin` where `loginid` = '{$SearchLoginid}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}'");
				if ($memberlogin) {
					$where1 .= " and `id` = '{$memberlogin['id']}'";
				} else {
					$where1 .= " and `id` = '0'";
				}
			}
			
			$member_id_arr = array();
			$query1 = $DB->query("select * from `t_member` {$where1}");
			if($DB->numRows($query1) <= 0){
				$where .= " and id = 0";
			}else{
				while($rs1 = $DB->fetchArray($query1)){
					$lineHeightIds[] = $rs1['id'];
					
					$member_id_arr[] = $rs1['id'];
					
					$member_id_arr_child = getunderCustomerIds($rs1['id']);
					if($member_id_arr_child){
						$member_id_arr = array_merge($member_id_arr, $member_id_arr_child);
					}
				}
				
				$where .= " and id in (" . implode(',',$member_id_arr) . ")";
				
				$orderByCase = "CASE WHEN id in (" . implode(',',$lineHeightIds) . ") THEN 1 ELSE 0 END desc,";
			}
			
			$isSearch = 1;
		}
		
		
		if ($SearchCommission == 'untomt') {
			$where .= " and `amount` > 0";
			
			$isSearch = 1;
		}
		if ($SearchRoleType > 0) {
			$where .= " and f_roleId = '{$SearchRoleType}'";
			
			$isSearch = 1;
		}
		if (in_array($SearchUserType, array('direct','agent','member'))) {
			$where .= " and `userType` = '{$SearchUserType}'";
			
			$isSearch = 1;
		}
		if ($SearchUserType == 'agent' && $SearchLevelType > 0) {
			$where .= " and `level` = '{$SearchLevelType}'";
			
			$isSearch = 1;
		}
        if (strlen($SearchSTime) > 0) {
			$where .= " and `create_time` >= '" . strtotime($SearchSTime) . " 00:00:00'";
			
			$isSearch = 1;
        }
        if (strlen($SearchETime) > 0) {
			$where .= " and `create_time` <= '" . strtotime($SearchETime) . " 23:59:59'";
			
			$isSearch = 1;
        }
        if (strlen($SearchStatus) > 0) {
            if (in_array($SearchStatus, array(0, 1))) {
				$where .= " and `status` = '{$SearchStatus}'";
				$where .= " and `banned_login` = '0'";
            } else {
                if ($SearchStatus == '-1') {
					$where .= " and `banned_login` = '1'";
                } elseif ($SearchStatus == '-2') {
                    $where .= " and `login_error_used` >= 5";
					$where .= " and (unix_timestamp(now()) - login_error_time < 600)";
                }
            }
			
			$isSearch = 1;
        }


		if($SearchIsS == '1'){
			$where .= " and f_isS = 1";
		}else if($SearchIsS == '2'){
			$where .= " and f_isS = 0";
		}		
		
		
		//非管理员，只能看到自己和伞下
		if($DRAdmin['_dataRange'] <= 0){
			$where .= " and `id` = '" . $DRAdmin['id'] . "'";
		}else if($DRAdmin['_dataRange'] <= 1){
			$TempGetunderCustomerIds = getunderCustomerIds($DRAdmin['id']);
			if(!$TempGetunderCustomerIds){
				$TempGetunderCustomerIds = array('0');
			}
			$TempGetunderCustomerIds = array_merge(array($DRAdmin['id']), $TempGetunderCustomerIds);
			$where .= " and `id` in (" . implode(',',$TempGetunderCustomerIds) . ")";
		}
		
		
		
		if($isSearch){
			$orderBy = 'order by ' . $orderByCase . 'parent_id asc,id desc,create_time desc';
		}else{
			$orderBy = 'order by id desc';
		}
		


		if(FGetInt('isdownload') <= 0){
			$recordCount = intval($DB->getField("select count(*) from `t_member` {$where}"));
	
			$staticnum = array();
			if($recordCount > 0){
				//统计。。。
				$agentnum = 0;
				$directnum = 0;
				$map = array();
			
				$groupQuery = $DB->query("select userType,level,count(userType) as count1 from `t_member` {$where} group by userType,level");
				while($rs1 = $DB->fetchArray($groupQuery)){
					$member1 = $rs1;
					if ($member1['userType'] == 'agent') {
						$map[$member1['level']] = intval($map[$member1['level']]) + intval($member1['count1']);
						$agentnum += intval($member1['count1']);
					} elseif ($member1['userType'] == 'direct') {
						$directnum += intval($member1['count1']);
					}
				}
				ksort($map);
				
				$staticnum = array('directnum' => $directnum, 'agentnum' => $agentnum, 'totalstatic' => $map);
			}
		}else{
			$recordCount = 1;
			$staticnum = array();
		}
?>



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
<div style="margin-bottom:5px;">
	<?php
		echo L('汇总统计') , '：&nbsp;&nbsp;' , L('代理数') , '：' , intval($staticnum['agentnum']) , L('个') , ' &nbsp;&nbsp;';
		echo L('直客数') , '：' , intval($staticnum['directnum']) , L('个') , '<br/><br/>';
		echo L('代理统计') , '：';
		if($staticnum && $staticnum['totalstatic']){
			foreach($staticnum['totalstatic'] as $key1=>$val1){
				echo $key1 , L('级') , ' <font color="green"><b>' , $val1 , '</b></font> ' , L('个') , ' &nbsp; ';
			}
		}else{
			echo L('无');
		}
	?>
</div>
                                    
<script>
function cb_mark(this_){
	if(this_.checked){
		$("input[name=mark]").prop("checked",true);
	}else{
		$("input[name=mark]").prop("checked",false);
	}
}
function savePageMark(){
	var checked = '';
	var uncheck = '';
	$('input[name=mark]').each(function(){
		if($(this).prop('checked')){
			if(checked != ''){checked = checked + ',';}
			checked = checked + $(this).val();
		}else{
			if(uncheck != ''){uncheck = uncheck + ',';}
			uncheck = uncheck + $(this).val();
		}
	});
	//alert(checked + '|' + uncheck)
	
	var url = "?clause=savemark";
	$.post(url, {checked:checked,uncheck:uncheck}, function(data) {
		if(data.status==1) {
			layer.alert(data.info);
		}else{
			layer.alert(data.info);
		}
	}, 'json');
}
</script>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover pctree" width="100%" data-plugin="treegrid">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('客户信息');?></th>
                                                    <th class="no-sort"><?php echo L('上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('联系方式');?></th>
                                                    <th class="no-sort"><?php echo L('标记');?>(S)</th>
                                                    <th class="no-sort">MT <?php echo L('数量');?></th>
                                                    <th class="no-sort">MT</th>
                                                    <th class="no-sort"><?php echo L('角色');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('已提现') , '/' , L('未提现') , '($)';?></th>
                                                    <th class="no-sort"><?php echo L('点差返佣') , '/' , L('跟单返佣额') , '($)';?></th>
                                                    <th class="no-sort"><?php echo L('注册时间');?></th>
                                                    <th class="no-sort"><?php echo L('交易量') , '/' , L('盈亏');?></th>
                                                    <th class="no-sort"><?php echo L('入') , '/' , L('出金量');?></th>
                                                    <th class="no-sort"><?php echo L('净入金');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php

		$page = FGetInt('page');
		$pagersize = 20;
		
		//修正
		if($isSearch > 0 && $recordCount <= 500){
			$pagersize = 500;
		}
		
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
		$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
		
		if($recordCount <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
			
			if(FGetInt('isdownload') > 0){
				//提示没有数据
				echo '<script>alert("' , L('无可用数据') , '");window.parent.layer.closeAll();</script>';
				exit;
			}
		}else{
			$reportModel = new ReportModel($DRAdmin['mt4dbname'],$DRAdmin['ver']);
			
			//优化用
			$memloginArr = array();
			
			$listArr = array();

			if(FGetInt('isdownload') > 0){
				$query = $DB->query("select * from `t_member` {$where} {$orderBy}");
			}else{
				$query = $DB->query("select * from `t_member` {$where} {$orderBy} LIMIT {$sqlRecordStartIndex},{$pagersize}");
			}
			while($rs = $DB->fetchArray($query)){
				if ($rs['secret'] == '1') {
					$rs['balanceamount'] = "**";
					$rs['in_balanceamount'] = "**";
					$rs['in_amount'] = "**";
					$rs['parent_name'] = "**";
					$rs['parent_email'] = "**";
					$rs['mtlogin'] = "**";
					$rs['totalvolume'] = "**";
					$rs['totaloutbalance'] = "**";
					$rs['totalinbalance'] = "**";
					$rs['totalprofit'] = "**";
					$rs['equitybalance'] = "**";
					$rs['login'] = "**";
					$rs['group'] = "**";
					$rs['role_id'] = "**";
					$rs['role_name'] = "**";
				} else {
					$amount = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission_balance where `MEMBER_ID` = '{$rs['id']}' and `TYPE` = 0 and COMM_TYPE is null and SERVER_ID = '{$DRAdmin['server_id']}'");
					$amount2 = $DB->getField("select sum(f_cal) as sum1 from t_sale_commission_other where f_uid = '{$rs['id']}' and f_isJs = 1 and f_serverId = '{$DRAdmin['server_id']}'");
					$rs['balanceamount'] = round(floatval($amount)+floatval($amount2), 2);

					$msg_amount = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission_balance where `MEMBER_ID` = '{$rs['id']}' and `TYPE` = 0 and COMM_TYPE = 1 and SERVER_ID = '{$DRAdmin['server_id']}'");
					$rs['in_balanceamount'] = round($msg_amount, 2);

					$in_amount = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission_balance where `MEMBER_ID` = '{$rs['id']}' and `TYPE` = 1 and (IN_ID is not null) and SERVER_ID = '{$DRAdmin['server_id']}'");
					$rs['in_amount'] = round($in_amount, 2);
					
					if ($rs['parent_id'] != 0) {
						$parentMem = $DB->getDRow("select * from t_member where id = '{$rs['parent_id']}'");
						if ($parentMem) {
							$rs['parent_name'] = $parentMem['nickname'];
							$rs['parent_email'] = $parentMem['email'];
						} else {
							$rs['parent_name'] = L("无");
						}
					} else {
						$rs['parent_name'] = L("无");
					}

					$rs['mtlogin'] = $DB->getField("select count(*) as count1 from t_member_mtlogin where member_id = '{$rs['id']}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}'");
					
					$memlogin = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id = '{$rs['id']}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}' order by mt_type asc");
					if($DRAdmin['ver'] == 5){
						$lgwhere = "where `Time` between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
					}else{
						$lgwhere = "where CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
					}
					
					$totalvolume = $reportModel->sumVolume($memlogin, $lgwhere);
					$rs['totalvolume'] = $totalvolume ? $totalvolume / 100 : 0;
					
					$totaloutbalance = $reportModel->sumOutBalance($memlogin, $lgwhere);
					$rs['totaloutbalance'] = $totaloutbalance ? round($totaloutbalance, 2) : 0;
					
					$totalinbalance = $reportModel->sumInBalance($memlogin, $lgwhere);
					$rs['totalinbalance'] = $totalinbalance ? round($totalinbalance, 2) : 0;
					
					$totalprofit = $reportModel->sumProfit($memlogin, $lgwhere);
					$rs['totalprofit'] = $totalprofit ? round($totalprofit, 2) : 0;

					$rs['equitybalance'] = $rs['totalinbalance'] + $rs['totaloutbalance'];
					
					if($DRAdmin['ver'] == 5){
						$mt5user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt5_users` where `Login` = '" . current($memlogin) . "'");
						if ($mt5user) {
							$rs['login'] = $mt5user['Login'];
							$rs['group'] = $mt5user['Group'];
						}
					}else{
						$mt4user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt4_users` where `LOGIN` = '" . current($memlogin) . "'");
						if ($mt4user) {
							$rs['login'] = $mt4user['LOGIN'];
							$rs['group'] = $mt4user['GROUP'];
						}
					}
					
					$roleDR = $DB->getDRow("select * from t_role where id = '{$rs['f_roleId']}'");
					$rs['role_id'] = $roleDR['id'];
					$rs['role_name'] = $roleDR['name'];
					
					$rs['memlogin'] = $memlogin;					
					if($memlogin){
						$memloginArr = array_merge($memloginArr,$memlogin);
					}
				}
				
				
				
				//代理等级
                $exist_alias = false;
                if ($rs['userType'] != 'direct') {
					foreach ($ranklist as $key1 => $value1) {
						if ($rs['level'] == $value1['rank']) {
							$rs['LEVEL_NAME'] = $value1['rank_name'];
							$exist_alias = true;
							break;
						}
					}
					if (!$exist_alias) {
						$rs['LEVEL_NAME'] = $rs['level'] . L("级");
					}
                }
				
				
				$listArr[] = $rs;
			}
			
			
			if(FGetInt('isdownload') > 0){
				//优化用，预读取
				$memloginLGArr = array();
				if($memloginArr){
					if($DRAdmin['ver'] == 5){
						$memloginLGArr = $DB->getField2Arr("select Login as LOGIN,`Group` as `GROUP` from " . $DRAdmin['mt4dbname'] . ".`mt5_users` where `Login` in (" . implode(',',$memloginArr) . ")");
					}else{
						$memloginLGArr = $DB->getField2Arr("select LOGIN,`GROUP` from " . $DRAdmin['mt4dbname'] . ".`mt4_users` where `LOGIN` in (" . implode(',',$memloginArr) . ")");
					}
				}
				
				include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
				$objPHPExcel = new PHPExcel();

				$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('客户列表'));
				
				$titleArr = array(
					'UID',
					'LOGIN',
					L('分组'),
					L('英文名'),
					L('姓名'),
					L('邮箱'),
					L('手机'),
					L('身份'),
					L('角色'),
					L('上级账户'),
					L('上级邮箱'),
					L('注册时间'),
					L('状态'),
					L('已提现'),
					L('未提现'),
					/*L('点差返佣'),
					L('跟单返佣额'),
					L('交易量'),
					L('盈亏'),
					L('入金量'),
					L('出金量'),
					L('净入金'),*/
					L('身份证'),
					L('开户银行名称'),
					L('银行开户姓名'),
					L('银行卡账号'),
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
				foreach($listArr as $key=>$rs){
					if(!$rs['memlogin']){
						$rs['memlogin'] = array(0);
					}
					
					foreach($rs['memlogin'] as $key1=>$val1){
						$i = 'A';
						
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['id'],PHPExcel_Cell_DataType::TYPE_STRING);
						if($val1 == 0){
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, '',PHPExcel_Cell_DataType::TYPE_STRING);
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, '',PHPExcel_Cell_DataType::TYPE_STRING);
						}else{
							
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $val1==0?'':$val1,PHPExcel_Cell_DataType::TYPE_STRING);
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $memloginLGArr[$val1],PHPExcel_Cell_DataType::TYPE_STRING);
						}
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['realname'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['email'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['phone'],PHPExcel_Cell_DataType::TYPE_STRING);
						switch($rs['userType']){
							case 'agent':
								$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['LEVEL_NAME'],PHPExcel_Cell_DataType::TYPE_STRING);
								break;
							case 'direct':
								$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L('直接客户'),PHPExcel_Cell_DataType::TYPE_STRING);
								break;
							case 'member':
								$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L('员工'),PHPExcel_Cell_DataType::TYPE_STRING);
								break;
						}
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L($rs['role_name']),PHPExcel_Cell_DataType::TYPE_STRING);

						if(!can_look_parent_info()){
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, '-',PHPExcel_Cell_DataType::TYPE_STRING);
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, '-',PHPExcel_Cell_DataType::TYPE_STRING);
						}else{
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['parent_name'],PHPExcel_Cell_DataType::TYPE_STRING);
							$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['parent_email'],PHPExcel_Cell_DataType::TYPE_STRING);
						}

						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, date('Y-m-d H:i',$rs['create_time']),PHPExcel_Cell_DataType::TYPE_STRING);
						
						$status = '';
						if($rs['status'] == 1 && $rs['banned_login'] == 0){
							$status = L('已激活');
						}else if($rs['status'] == 0 && $rs['banned_login'] == 0){
							$status = L('待激活');
						}else if($rs['banned_login'] == 1){
							$status = L('禁用');
						}
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $status,PHPExcel_Cell_DataType::TYPE_STRING);
						
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['balanceamount'] + $rs['in_balanceamount'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['amount'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['identity'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['bankName'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['accountName'],PHPExcel_Cell_DataType::TYPE_STRING);
						$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['accountNum'],PHPExcel_Cell_DataType::TYPE_STRING);
						
						$j++;
					}
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
			
			
			
			
			
			
			
			function temp_fill_childs($rs){
				global $newListArr;
				global $listArrUsed;
				global $temp_listArr;
				
				foreach($temp_listArr as $key1=>$rs1){
					if($rs1['parent_id'] == $rs['id']){
						$newListArr[] = $rs1;
						unset($temp_listArr[$key1]);
						$listArrUsed[] = $rs1['id'];
						
						temp_fill_childs($rs1);
					}
				}
			}
			//先排序，按推荐关系
			$newListArr = array();
			$listArrUsed = array();
			$temp_listArr = $listArr;
			foreach($listArr as $key=>$rs){
				if(in_array($rs['id'],$listArrUsed)){
					continue;
				}
				
				$newListArr[] = $rs;
				unset($temp_listArr[$key]);
				$listArrUsed[] = $rs['id'];
				
				temp_fill_childs($rs);
			}
			
		
			
			
			
			foreach($newListArr as $key=>$rs){
				echo '<tr';
				echo ' class="treegrid-' , $rs['id'];
				if(!in_array($rs['id'],$lineHeightIds)){
					echo ' treegrid-parent-' , $rs['parent_id'];
				}
				echo ' treegrid-collapsed"';
				echo ' id="tr_' , $rs['id'] , '"';
				if(in_array($rs['id'],$lineHeightIds)){
					echo ' style="color: green; font-weight: bold;"';
				}
				echo '>';
				echo '<td>';
					if($rs['userType'] == 'agent'){
						echo '<img alt="' , L('代理') , '"  width="16" height="16" style="padding-bottom:3px" src="/assets/images/hplus/agent.png"/> ';
					}else if($rs['userType'] == 'direct'){
						echo '<img width="16" alt="' , L('直接客户') , '" height="16" style="padding-bottom:3px"  src="/assets/images/hplus/direct.png"/> ';
					}else if($rs['userType'] == 'member'){
						echo '<img width="16" alt="' , L('员工') , '" height="16" style="padding-bottom:3px"  src="/assets/images/hplus/member.png"/> ';
					}
					echo $rs['nickname'];
					if(strlen($rs['realname']) > 0){
						if(trim($rs['nickname']) != trim($rs['realname'])){
							echo ' / ' , $rs['realname'];
						}
					}
					//echo '<br>';
					echo ',';
					switch($rs['userType']){
						case 'agent':
							echo $rs['LEVEL_NAME'];
							break;
						case 'direct':
							echo L('直接客户');
							break;
						case 'member':
							echo L('员工') , '(' , $rs['level'] , L('级') , ')';
							break;
					}
					//echo '<br>UID：' , $rs['id'];
				echo '</td>';
				echo '<td>';
					if(!can_look_parent_info()){
						echo '-';
					}else{
						echo $rs['parent_name'];
						if(strlen($rs['parent_email'])){
							echo '<br>';
							echo '<span class="lookemail">' , hideStr($rs['parent_email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['parent_email'] , '"></i>';
						}
					}
				echo '</td>';
				echo '<td>';
					echo '<span class="lookemail">' , hideStr($rs['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['email'] , '"></i>';
					if(strlen($rs['phone'])){
						echo '<br/><span class="lookphone">' , hideStr($rs['phone'],3,4) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" phone="' , $rs['phone'] , '"></i>';
					}
				echo '</td>';
				echo '<td>';
				if(chk_in_access('标记')){
					echo '<input type="checkbox" name="mark" value="' , $rs['id'] , '" id="mark_' , $rs['id'] , '"' , $rs['f_isS'] ? ' checked' : '' , '>';
					echo '<label for="mark_' , $rs['id'] , '">' , L('标记') , '(S)' , '</label>';
				}else{
					if($rs['f_isS']){
						echo '<span style="color:#008000">√' , L('标记') , '(S)' , '</span>';
					}else{
						echo '&nbsp;';
					}
				}
				echo '</td>';
				echo '<td>';
					if($rs['secret'] == 1){
						echo $rs['mtlogin'];
					}else{
						echo '<a href="mtlogin.php?member_id=' , $rs['id'] , '">' , $rs['mtlogin'] , '</a>';
					}
					echo '</td>';
					echo '<td>';
					echo 'MT:' , $rs['login'];
					echo '<br/>Group:<b>' , $rs['group'] , '</b>';
				echo '</td>';
				echo '<td>' , L($rs['role_name']) , '</td>';
				echo '<td>';
					if($rs['status'] == 1 && $rs['banned_login'] == 0){
						echo '<font color="green">' , L('已激活') , '</font>';
					}else if($rs['status'] == 0 && $rs['banned_login'] == 0){
						echo '<font color="red">' , L('待激活') , '</font>';
					}else if($rs['banned_login'] == 1){
						echo '<font color="red">' , L('禁用') , '</font>';
					}
					if($rs["login_error_used"] >= 5 and (time()-$rs["login_error_time"]) < 600){
						echo '<br><font color="red">' , L('账户已锁定') , '</font>';
					}
				echo '</td>';
				echo '<td>';
					if($rs['in_amount'] == 0){
						echo $rs['in_amount'];
					}else{
						echo '<a href="commission_index.php?member_id=' , $rs['id'] , '&qtype=1">' , $rs['in_amount'] , '</a>';
					}
					echo ' / ';
					if(chk_in_access('修改佣金余额')){
						echo '<a onclick="editamount(this)" href="#nolink" rel="' , $rs['id'] , '" data-toggle="editamount_modal">' , $rs['amount'] * 1 , '</a>';
					}else{
						echo $rs['amount'] * 1;
					}
				echo '</td>';
				echo '<td>';
					if($rs['balanceamount'] == 0){
						echo $rs['balanceamount'];
					}else{
						echo '<a href="commission_index.php?member_id=' , $rs['id'] , '&comm_type=2">' , $rs['balanceamount'] , '</a>';
					}					
					if($rs['in_balanceamount'] == 0){
						echo ' / ', $rs['in_balanceamount'];
					}else{
						echo ' / <a href="commission_index.php?member_id=' , $rs['id'] , '&comm_type=1">' , $rs['in_balanceamount'] , '</a>';
					}
				echo '</td>';
				echo '<td>';
				if($rs['create_time'] == '**'){
					echo $rs['create_time'];
				}else{
					echo date('Y-m-d H:i',$rs['create_time']);
				}
				echo '</td>';
				echo '<td>' , $rs['totalvolume'] , L('手') , ' / ', $rs['totalprofit'] , '</td>';
				echo '<td>' , $rs['totalinbalance'] , ' / ' , $rs['totaloutbalance'] , '</td>';
				echo '<td>' , $rs['equitybalance'] , '</td>';
				echo '<td>';
					echo '<div class="btn-group dropup">';
					echo '<button data-toggle="dropdown" class="btn btn-white btn-xs dropdown-toggle">' , L('操作') , ' <span class="caret"></span></button>';
					echo '<div class="dropdown-menu" style="z-index:10000">';
					if($rs['secret'] != '1'){
						if(chk_in_access('查看客户详情')){
							echo '<a class="dropdown-item" href="?clause=detail&id=' , $rs['id'] , '">' , L('查看') , '</a>';
						}
						if(chk_in_access('修改客户')){
							echo '<a class="dropdown-item" href="?clause=addinfo&id=' , $rs['id'] , '">' , L('修改') , '</a>';
						}
						if(chk_in_access('重置CRM密码')){
							echo '<a class="dropdown-item" href="?clause=editpwd&id=' , $rs['id'] , '">' , L('重置CRM密码') , '</a>';
						}
						/*if($rs['status'] == '0'){
							if(chk_in_access('重发激活邮件')){
								echo '<a class="dropdown-item" onclick="resend(this)" href="#nolink" rel="' , $rs['id'] , '">' , L('重发激活邮件') , '</a>';
							}
							if(chk_in_access('激活客户')){
								echo '<a class="dropdown-item" onclick="visituser(this)" href="#nolink" rel="' , $rs['id'] , '">' , L('激活') , '</a>';
							}
						}*/
						if($rs['status'] == 1){
							if(chk_in_access('分配客户')){
								//if($rs['userType'] != 'member'){
									echo '<a class="dropdown-item" onclick="fenpei(this)" href="#nolink" rel="' , $rs['id'] , '" data-toggle="modal">' , L('分配') , '</a>';
								//}
							}
							if(chk_in_access('授权客户')){
								//if($rs['userType'] != 'member'){
									echo '<a class="dropdown-item" onclick="user_role(this)" href="#nolink" val="' , $rs['id'] , '" role="' , $rs['role_id'] , '" abook="' , $rs['abook'] , '" bbook="' , $rs['bbook'] , '"  scope="' , $rs['scope'] , '" grname="' , $rs['recomment_groups'] , '">' , L('授权') , '</a>';
								//}
							}
							if(chk_in_access('设置CRM登录与禁用')){
								if($rs["banned_login"] == 1){
									echo '<a class="dropdown-item" onclick="can_login(this)" href="#nolink" rel="' , $rs['id'] , '">CRM' , L('开放登录') , '</a>'; 
								}else{
									echo '<a class="dropdown-item" onclick="banned_login(this)" href="#nolink" rel="' , $rs['id'] , '">CRM' , L('禁用登录') , '</a>';
								}
							}
						}
						if($rs['id'] != $DRAdmin['id']){
							if(chk_in_access('删除客户')){
								echo '<a class="dropdown-item" onclick="delete_(this)" rel="' , $rs['id'] , '" re="' , $rs['nickname'] , '" lang="' , $rs['email'] , '" href="#nolink">' , L('删除') , '</a>';
							}
						}
						if(chk_in_access('解除登录锁定')){
							if($rs["login_error_used"] >= 5 and (time()-$rs["login_error_time"]) < 600){
								echo '<a class="dropdown-item" onclick="unlock(this)" rel="' , $rs['id'] , '" href="#nolink">' , L('解除登录锁定') , '</a>';
							}
						}
					}					
					echo '</div>';
					echo '</div>';
				echo '</td>';
				echo '</tr>';
			}
			
			if($newListArr){
				if(chk_in_access('标记')){
					echo '<tr>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">';
					echo '<input type="checkbox" onclick="cb_mark(this)" id="cb_mark_">';
					echo '<label for="cb_mark_">' , L('全选') , '(S)</label><br>';
					echo '<button type="button" class="btn btn-warning btn-sm" onclick="savePageMark()">' , L('保存') , '</button>';
					echo '</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
						<td class="no-sort">&nbsp;</td>
					</tr>';
				}
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
                    
                    

	<div class="modal editamount_modal" id="editamount_modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('修改佣金余额'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='editamountform' name='editamountform'>
					<div class="modal-body">
						<div class="row"> 
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('客户信息'); ?>：</label>
									<span id="editamount_userinfo">&nbsp;</span>
								</div>
							</div>
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('未提现'); ?>($)：</label>
									<span id="editamount_amount">&nbsp;</span>
								</div>
							</div>
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('增减金额'); ?>($)：</label>
									<input type="text" class="form-control" name="adAmount" autocomplete="off" value="" placeholder="">
								</div>
							</div>
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('备注'); ?>：</label>
									<input type="text" class="form-control" name="adAbout" autocomplete="off" value="" placeholder="">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type='hidden' name='editamount_uid' id="editamount_uid" value='' />
						<button type="button" class="btn btn-white" data-dismiss="modal"><?php echo L('关闭'); ?></button>
						<button type="button" class="btn btn-primary" id='saveeditamount'><?php echo L('确认'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
                    
                    
                    
	<!--入金弹出层-->
	<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('授权'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='inmoneyform' name='inmoneyform'>
					<div class="modal-body">
						<div class="row"> 
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('角色'); ?>：</label><br/>
									<select name='roleid' id="roleid"  data-placeholder="<?php echo L('请择种类'); ?>"  class='form-control m-b' >
                                        <?php
										foreach($role_types as $key=>$val){
											if(@in_array($val['id'],$DRAdmin['_access']['actrole'])){
												echo '<option value="' , $val['id'] , '">' , $val['name'] , '</option>';
											}
										}
										?>
		                            </select>
								</div>
							</div>
						</div>
                        <!--
						 <div class="row"> 
							<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('推荐组'); ?>：</label><br/>
									<select name='recomment_groups[]' id="recomment_groups" data-placeholder="<?php echo L('请选择分组'); ?>" class='chosen-select'  multiple  >
                                     <?php
									 $groups = $DB->getDTable("select * from `t_groups` where `server_id` = '{$DRAdmin['server_id']}'");
									 foreach($groups as $key=>$val){
										echo '<option value="' , $val['group'] , '">' , $val['group'] , strlen($val['group_remark']) ? '(' . $val['group_remark'] . ')' : '' , '</option>';
									 }
									?>
                                </select>
								</div>
							</div>
						</div>
                        -->
                        <!--
						<?php
                        if($DRAdmin['abook'] == 'manage'){
						?>
						<div class="row" id="abbookdiv"> 
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label>A BOOK：</label>
									<select name='abookgroups' id="abookgroups" data-placeholder="<?php echo L('请选择A BOOK权限'); ?>"  class='form-control m-b'  >
	                                 	<option value='deny'><?php echo L('无权限'); ?></option>
	                                 	<option value='query' ><?php echo L('查询权限'); ?></option>
	                                 	<option value='manage'><?php echo L('管理权限'); ?></option>
		                            </select>
								</div>
							</div>
						</div>
						<?php
						}
						?>
                        <?php
                        if($DRAdmin['bbook'] == 'manage'){
						?>
						<div class="row"> 
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label>B BOOK：</label>
									<select name='bbookgroups' id="bbookgroups" data-placeholder="<?php echo L('请选择B BOOK权限'); ?>" class='form-control m-b'  >
	                                 	<option value='deny' ><?php echo L('无权限'); ?></option>
	                                 	<option value='query'><?php echo L('查询权限'); ?></option>
	                                 	<option value='manage'><?php echo L('管理权限'); ?></option>
		                            </select>
								</div>
							</div>
						</div>
						<?php
						}
						?>
                        -->
                        <!--
						<?php
                      	if($DRAdmin['_dataRange'] >= 2){
                       	?>
						<div class="row" > 
	                       	<div class="col-md-12"> 
			                 	<div class="form-group">
									<label><?php echo L('授权数据可见范围'); ?>：</label>
									<select name='scope' id="scope" data-placeholder="<?php echo L('请授权查询范围'); ?>"  class='form-control m-b'  >
	                                 	<option value='0' selected><?php echo L('仅自己的客户'); ?></option>
	                                 	<option value='2'><?php echo L('平台所有数据'); ?></option>
		                            </select>
								</div>
							</div>
						</div>
						<?php
						}
						?>
                        -->
					</div>
					<div class="modal-footer">
						<input type='hidden' name='user_id' value='' />
						<button type="button" class="btn btn-white" data-dismiss="modal"><?php echo L('关闭'); ?></button>
						<button type="button" class="btn btn-primary" id='saverole'><?php echo L('确认'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!--入金弹出层-->

	<!--弹出层-->
	<div class="modal inmodal" id="levelModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('客户等级设置'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='levelForm' name='levelForm'>
					<div class="modal-body">
						<div class="form-group"><label><?php echo L('客户类型'); ?>：</label>
							<select class="form-control m-b" id="userType1" name="userType1">
								<option value="direct"><?php echo L('直接客户'); ?></option>
								<option value="agent"><?php echo L('代理商'); ?></option>
							</select>
						</div>
						<div id="agentdiv11" style="display: none;">
							<div class="form-group"><label><?php echo L('代理等级'); ?>：</label>
								<select class="form-control m-b" id="level1" name="level1">
                                <?php								
								/*
								//解析
								$step = 0;
								foreach ($list as $key => $value) {
									$step++;
									$exist_alias = false;
									if ($value['userType'] == 'direct') {
										continue;
									}
									foreach ($ranklist as $key1 => $value1) {
										if ($value['level'] == $value1['rank']) {
											$list[$step - 1]['LEVEL_NAME'] = $value1['rank_name'];
											$exist_alias = true;
											break;
										}
									}
									if (!$exist_alias) {
										$list[$step - 1]['LEVEL_NAME'] = $value['level'] . ' ' . L('级') . ' ' . L('代理');;
									}
								}
								*/
								
								//转换
								$ranks = array();
								for ($i = 1; $i <= C('MAX_LEVEL'); $i++) {
									if ($DRAdmin['_dataRange'] <= 1 && $DRAdmin['level'] >= $i && $DRAdmin['userType'] != 'member') {
										continue;
									}
									$ranks[$i]['rank_name'] = $i . L("级");
									foreach ($ranklist as $v) {
										if ($v['rank'] == $i && $v['model_type'] != 'member') {
											$ranks[$i]['rank_name'] = $v['rank_name'];
											break;
										}
									}
								}
								foreach($ranks as $key=>$val){
									echo '<option value="' , $key , '">' , $val['rank_name'] , '</option>';
								}
								?>
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type='hidden' name='id' id="id" />
                                               
						<button type="button" class="btn btn-primary check"><?php echo L('确认'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!--弹出层-->

	<!--客户分配弹出层-->
	<div class="modal inmodal" id="fenpeiModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('客户分配'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='allocForm' name='allocForm'>
					<div class="modal-body">
						<label><?php echo L('客户名称'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" name="member_name" id="member_name">
						</div>
						<label><?php echo L('上级账户'); ?>：</label>
						<div class="input-group">
							<input type="text" class="form-control" id="parent_name" name="parent_name">
							<div class="input-group-btn" style="position:relative;">
								<button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown">
	                                <span class="caret"></span>
	                            </button>
								<ul class="dropdown-menu dropdown-menu-right" role="menu">
								</ul>
							</div>
							<!-- /btn-group -->
						</div>
                        <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('请在搜索结果中选择上级账户'); ?></span>
						<div class="form-group"><label><?php echo L('客户类型'); ?>：</label>
							<select class="form-control m-b" id="userType" name="userType">
								<option value="direct"><?php echo L('直接客户'); ?></option>
								<option value="agent"><?php echo L('代理商'); ?></option>
                                <option value="member"><?php echo L('员工'); ?></option>
							</select>
						</div>
						<div id="agentdiv" style="display: none;">
							<div class="form-group"><label><?php echo L('等级'); ?>：</label>
								<select class="form-control m-b" id="level" name="level">
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type='hidden' name='mem_id' id="mem_id" />
						<input type="hidden" name="parent_id" id="parent_id">
						<button type="button" class="btn btn-white" id='closeout3' data-dismiss="modal"><?php echo L('关闭'); ?></button>
						<button type="button" class="btn btn-primary savealloc"  ><?php echo L('确认'); ?></button>
					</div>
			</form>
            </div>
		</div>
	</div>
	<!--弹出层-->
        
        <!--客户会员树弹出层-->
	<div class="modal" id="treeModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('查询'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='allocForm1' name='allocForm1'>
					<div class="modal-body aciTree" id="tree-combined">
						
					</div>
					<div class="modal-footer">
						
						<button type="button" class="btn btn-white closetree"  data-dismiss="modal"><?php echo L('关闭'); ?></button>
						<button type="button" class="btn btn-primary" id="gettreevalue" ><?php echo L('确认'); ?></button>
					</div>
			</form>
            </div>
		</div>
	</div>
	<!--客户会员树弹出层-->
                    
                    
                    
                    
                    
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
        
        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>  
        
        <script src="/assets/js/suggest/bootstrap-suggest.min.js"></script>
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script> 
        
		<script src="/assets/js/treegrid/jquery.treegrid.min.js"></script>
        <link href="/assets/js/treegrid/css/jquery.treegrid.css" rel="stylesheet">
        
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
        
        




<script>
    $(document).ready(function() {
        $('.pctree').treegrid();
    });
</script>


        
<script type="text/javascript">
$(".chosen-select").chosen( {width: "100%"});

	$(document).on("click",".check_user",function(){
	//$(".check_user").click(function() {
		$(this).attr('disabled', "disabled");
		var _this = $(this);
		var form = $(this).closest('form');
		var url = "{:U('Member/viewlevel')}";
		var ID = $(this).attr('rel');
		$("#level").empty();
		$.post(url, "id=" + ID, function(data) {
			if(data.status == 1) {
				$("#id").val(data.data.id);
				if(data.data.userType == 'direct')
					$("#agentdiv").hide();
				else
					$("#agentdiv").show();
				$("#userType").val(data.data.userType);
				//          	$("#rank_id").val(data.data.rank_id);
				level=parseInt(data.data.super_level);
				for(var i = level + 1; i <= <?php echo C('MAX_LEVEL'); ?>; i++) {
					$("#level").append("<option value=" + i + ">" + i + "<?php echo L('级'); ?></option>");
				}
				$("#level").val(data.data.level);
				$('#levelModal').modal('toggle');
			} else {
				alert(data.info);
			}
			_this.removeAttr("disabled");
		}, 'json')
	});
	
	
	function user_role(this_) {
		var _this = $(this_);;
		$("input[name='user_id']").val($(this_).attr('val'));
		$("#roleid").val($(this_).attr('role'));
		var e = $(this_).attr('grname');
		if($(this_).attr('abook')!='')
			$("#abookgroups").val($(this_).attr('abook'));
		
		if($(this_).attr('bbook')!='')
			$("#bbookgroups").val($(this_).attr('bbook'));
		
		if ($(".chosen-select").hasClass('chzn-done'))
            $(".chosen-select").chosen('destroy');
        $(".chosen-select").chosen({width: "100%"});
        chose_mult_set_ini('#recomment_groups',e);
        $("#scope").val($(this_).attr('scope'));
		$('#myModal').modal('toggle');
	}
	
        var checkedlist=new Array();
         var treeApi;
         var theTree=$('#tree-combined');
		 $(document).on("click","#showtree",function(){
        //$("#showtree").click(function(){
            $("#treeModal").modal('toggle');
            var nickname=$("#q").val();
         treeApi= theTree.aciTree({
                ajax: {
                         url: '/Member/index?act=gettree&q='+nickname
                        },
                        autoInit: true,
                        checkbox: true,
                        radio: true,
                        unique: true,
                        sortable: true
                    });

           
        })
		$(document).on("click",".paginate_button",function(){
        //$(".paginate_button").click(function(){
            if($("#treenode").val() && $(this).find("a").attr('href')){
                $("#commentForm").attr("method",'post');
                $("#commentForm").attr("action",$(this).find("a").attr('href'));
                $("#commentForm").submit();
                return false;
            }
        });
		$(document).on("click","#searchuserbtn",function(){
        //$("#searchuserbtn").click(function(){
           if($("#treenode").val()){
                $("#treenode").val("");
                return false;
            } 
        });
        $(".closetree,.close").click(function(){
              //theTree.aciTree('api').destroy();
        });
		$(document).on("click","#gettreevalue",function(){
        //$("#gettreevalue").click(function(){
           var value=new Array();
           $.each($(".aciTreeLi.aciTreeChecked"),function(key,val){
               var id=$(this).find("div.aciTreeLine input").val();
                  value[key]=id;
           })
            $("#commentForm")[0].reset();
           $("#treenode").val(value.join(','));
            $("#commentForm").attr("method",'post');
                $("#commentForm").attr("action",'/member/index');
           $("#commentForm").submit();
        });
        
	function chose_mult_set_ini(select, values) {
        var arr = values.split(',');
        $(select).val(arr);
        $(select).trigger("chosen:updated");
        //$(select).trigger("chosen:updated");  
    }
	
	var json = '<?php echo json_encode($ranks); ?>';
	
	function editamount(this_){
		$(this).attr('disabled', "disabled");
		var _this = $(this_);
		var form = $(this_).closest('form');
		var url = "?clause=viewMember";
		var ID = $(this_).attr('rel');
		$("#level").empty();
		$.post(url, "id=" + ID, function(data) {
			if(data.status == 1) {
				$("#editamount_uid").val(data.data.id);
				$("#editamount_userinfo").html(data.data.nickname);
				$("#editamount_amount").html(data.data.amount);
				
				$('#editamount_modal').modal('toggle');
			} else {
				alert(data.info);
			}
			_this.removeAttr("disabled");
		}, 'json')
	}

	function fenpei(this_) {
		$(this).attr('disabled', "disabled");
		var _this = $(this_);
		var form = $(this_).closest('form');
		var url = "?clause=viewMember";
		var ID = $(this_).attr('rel');
		$("#level").empty();
		$.post(url, "id=" + ID, function(data) {
			if(data.status == 1) {
				$("#mem_id").val(data.data.id);
				$("#member_name").val(data.data.nickname);
				$("#userTypeSel").val(data.data.userType);
				$("#parent_id").val(data.data.parent_id);
				$("#parent_name").val(data.data.parent_name);
				if(data.data.userType == 'direct')
					$("#agentdiv").hide();
				else
					$("#agentdiv").show();
				$("#userType").val(data.data.userType);
				//          	$("#rank_id").val(data.data.rank_id);
				level=parseInt(data.data.super_level);
				
				<?php
				if(!$ranks){
				?>
				for(var i = level + 1; i <= <?php echo C('MAX_LEVEL'); ?>; i++) {
					$("#level").append("<option value=" + i + ">" + i + "<?php echo L('级'); ?></option>");
				}
				<?php }else{ ?>
				var json_obj = eval('(' + json + ')');
				$('#level').empty();
				for(var o in json_obj){ 
					$("#level").append("<option value='"+o+"'>"+json_obj[o].rank_name+"</option>"); 
				}
           		<?php } ?>
				$("#level").val(data.data.level);
				$('#fenpeiModal').modal('toggle');
			} else {
				alert(data.info);
			}
			_this.removeAttr("disabled");
		}, 'json')
	};

	$("#userType").change(function() {
		var userTypeVal = $("#userType").val();
		if(userTypeVal == 'agent' || userTypeVal == 'member')
			$("#agentdiv").show();
		else
			$("#agentdiv").hide();
	});
	
	
	$(document).on("click","#saveeditamount",function(){
	//$(".savealloc").click(function() {
		var url = "?clause=saveeditamount";
		var _this = $(this);
		var form = $(this).closest('form');
		$(this).attr('disabled', "disabled");
		$.post(url, form.serialize(), function(data) {
			layer.msg(data.info);
			if(data.status == 1) {
				document.allocForm.reset();
				$(".close").click();
				setTimeout(function(){document.location.reload();},800);
			}
			_this.removeAttr("disabled");
		}, 'json');
	});
	

	$(document).on("click",".savealloc",function(){
	//$(".savealloc").click(function() {
		var url = "?clause=saveAlloc";
		var _this = $(this);
		var form = $(this).closest('form');
		var parent_id = $("#parent_id").val();
		if(parent_id == '') {
			alert("<?php echo L('请输入归属的上级账户'); ?>");
			return;
		}
		$(this).attr('disabled', "disabled");
		$.post(url, form.serialize(), function(data) {
			layer.msg(data.info);
			if(data.status == 1) {
				document.allocForm.reset();
				$(".close").click();
				setTimeout(function(){document.location.reload();},800);
			}
			_this.removeAttr("disabled");
		}, 'json');
	});

	$(document).on("click",".check",function(){
	//$(".check").click(function() {
		var url = "{:U('Admin/Member/review')}";
		var form = $(this).closest('form');
		$.post(url, form.serialize(), function(data) {
			layer.alert(data.info);
			if(data.status) {
				document.levelForm.reset();
				$(".close").click();
			}
		}, 'json');
	});
     
	 
	 function unlock() {
		var url = "{:U('Admin/Member/unlock')}";
		var id = $(this).attr('rel');
		$.post(url, {id:id}, function(data) {
			layer.alert(data.info);
			if(data.status) {
				document.location.reload();
				
			}
		}, 'json');
	}

       function can_login(this_){
        	var url = "?clause=can_login";
        	var id = $(this_).attr('rel');
        	var banned_login = 0;
        	$.post(url, {id:id,banned_login:banned_login}, function(data) {
				if(data.status==1) {
					alert("<?php echo L('登录已解禁'); ?>！");
					document.location.reload();
				}else{
					layer.alert(data.info);
				}
			}, 'json');
        }

        function banned_login(this_){
        	var url = "?clause=can_login";
        	var id = $(this_).attr('rel');
        	var banned_login = 1;
        	$.post(url, {id:id,banned_login:banned_login}, function(data) {
				if(data.status==1) {
					alert("<?php echo L('已禁止其登录'); ?>！");
					document.location.reload();
				}else{
					layer.alert(data.info);
				}
			}, 'json');
        }
		
	$(document).on("click","#saverole",function(){
	//$("#saverole").click(function() {
		var url = "?clause=saveUser";
		var role_id = $("#roleid").val();
		var user_id = $("input[name='user_id']").val();
		var agroup = $("#abookgroups").val();
		var bgroup = $("#bbookgroups").val();
		var scope = $("#scope").val();
		var grname = $("#recomment_groups").val();
		var data = {
			role_id: role_id,
			user_id: user_id,
			//abookgroups:agroup,
			//bbookgroups:bgroup,
			//scope:scope,
			//grname:grname
		};
		$.post(url, data, function(data) {
			layer.alert(data.info);
			if(data.status) {
				document.inmoneyform.reset();
				$(".close").click();
				document.location.reload();
			}
		}, 'json');
	});
 

	function delete_(this_) {
		var _this = $(this_);
		var ID = $(this_).attr('rel');
		var name = $(this_).attr('re');
		var email = $(this_).attr('lang');
		swal({
			title: "<?php echo L('您确定要删除这条信息吗'); ?>",
			text: "<?php echo L('客户昵称'); ?>:"+name+' '+"<?php echo L('邮箱'); ?>:"+email+' '+"<?php echo L('删除后将无法恢复,且其直属下级账户将自动调整为无上级,请谨慎操作'); ?>! ",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('删除'); ?>",
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
		}, function() {
			var url = "?clause=delete";
			$.post(url, "id=" + ID, function(data) {
				if(data.status == 0) {
					swal("<?php echo L('删除成功'); ?>！", "<?php echo L('您已经删除了这条信息'); ?>。", "success");
					$("#tr_" + ID).remove();
				} else{
					swal("<?php echo L('删除失败'); ?>！", data.info, "warning");
				}
			}, 'json');
		});
	}
	
	
	
	function resend(this_) {
		var _this = $(this_);
		var ID = $(this_).attr('rel');
		swal({
			title: "<?php echo L('您确定要重发激活邮件吗'); ?>",
			text: "<?php echo L('重发后，请用户通过新的激活链接激活'); ?>！",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('重发邮件'); ?>",
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
		}, function() {
			var url = "{:U('Member/resendRegmail')}";
			$.post(url, "member_id=" + ID, function(data) {
				swal("<?php echo L('发送邮箱状态'); ?>！", data.data.info, "success");
				_this.removeAttr("disabled");
			}, 'json');
		});
	}
        
      function visituser(this_) {
		var _this = $(this_);
		var ID = $(this_).attr('rel');
		swal({
			title: "<?php echo L('您确定要激活此用户吗'); ?>",
	
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确定'); ?>",
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
		}, function() {
			var url = "{:U('Member/visituser')}";
			$.post(url, "id=" + ID, function(data) {
                              
				swal("<?php echo L('发送邮箱状态'); ?>！", data.info, "success");
				_this.removeAttr("disabled");
			}, 'json');
		});
	};

	/**
	 * 百度搜索 API 测试
	 */
	$("#parent_name").bsSuggest({
		allowNoKeyword: false, //是否允许无关键字时请求数据。为 false 则无输入时不执行过滤请求
		multiWord: true, //以分隔符号分割的多关键字支持
		separator: ",", //多关键字支持时的分隔符，默认为空格
		getDataMethod: "url", //获取数据的方式，总是从 URL 获取
		idField: "id",
		keyField: "nickname",
		url: "?clause=queryParentMemberByName&member_id=" + $("#mem_id").val() + "&parent_name=" ,
		/*优先从url ajax 请求 json 帮助数据，注意最后一个参数为关键字请求参数*/
	}).on('onDataRequestSuccess', function(e, result) {
		console.log('onDataRequestSuccess: ', result);
	}).on('onSetSelectValue', function(e, keyword, data) {
		$("#parent_id").val(keyword.id);
		console.log('onSetSelectValue: ', keyword, data);
	}).on('onUnsetSelectValue', function() {
		console.log("onUnsetSelectValue");
	});
	
	$(document).on("click","#downBtns",function(){
    //$("#downBtns").click(function(){
        var value=$("#commentForm").serialize();
        document.location.href="{:U('Member/downmember',array('act'=>'down'))}?"+value;
    });
    

    $("#userType_query").change(function(){
    	var _this = $(this);
    	var usertype = _this.val();
    	if(usertype=='agent' || usertype == 'member'){
    		$('#level_type_query').removeAttr('disabled');
    	}else{
    		$('#level_type_query').val('');
    		$('#level_type_query').attr('disabled','disabled');
    	}

    });
    
    function showNextMember(id){
        showmyparent(id);
    }
    $('#role_type').val("{$_REQUEST['role_type']}");
    $('#userType_query').val("<?php echo $SearchUserType;?>");
    $('#commission_query').val("<?php echo $SearchCommission;?>");
    $('#level_type_query').val("<?php echo $SearchLevelType;?>");
    if($('#userType_query').val()!='agent' && $('#userType_query').val()!='member'){
    	$('#level_type_query').attr('disabled','disabled');
    }else{
    	$('#level_type_query').removeAttr('disabled');
    }
    
	
	init_findinfo();
</script>
        
        
        
        

    </body>
</html>
