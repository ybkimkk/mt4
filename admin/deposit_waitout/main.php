<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchLogin = FGetStr('searchLogin');
$SearchPaycode = FGetStr('searchPaycode');
$SearchStatus = FGetStr('searchStatus');
$SearchSTime = FGetStr('searchSTime');
$SearchETime = FGetStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('出金记录') , getCurrMt4ServerName();?></h4>
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
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('MT账号');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchLogin;?>" name="searchLogin" placeholder="<?php echo L('请输入出金的账户');?>">
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('出金方式');?>：</label>
                                                    <select name='searchPaycode' id="searchPaycode" class='form-control'>
                                                        <option value=''<?php if($SearchPaycode === ''){echo ' selected';}?>><?php echo L('全部'); ?></option>
                                                        <option value='1'<?php if($SearchPaycode === '1'){echo ' selected';}?>><?php echo L('银联'); ?></option>
                                                        <option value='2'<?php if($SearchPaycode === '2'){echo ' selected';}?>><?php echo L('电汇'); ?></option>
                                                        <option value='3'<?php if($SearchPaycode === '3'){echo ' selected';}?>><?php echo L('MT转账'); ?></option>
                                                        <option value='4'<?php if($SearchPaycode === '4'){echo ' selected';}?>><?php echo L('nganluong'); ?></option>
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('出金状态');?>：</label>
                                                    <select name='searchStatus' id="searchStatus" class='form-control'>
                                                        <option value=''<?php if($SearchStatus === ''){echo ' selected';}?>><?php echo L('全部'); ?></option>
                                                        <option value='0,8'<?php if($SearchStatus === '0,8'){echo ' selected';}?>><?php echo L('未审核'); ?></option>
                                                        <option value='1'<?php if($SearchStatus === '1'){echo ' selected';}?>><?php echo L('已驳回'); ?></option>
                                                        <option value='9'<?php if($SearchStatus === '9'){echo ' selected';}?>><?php echo L('已出金'); ?></option>
                                                        <option value='-1'<?php if($SearchStatus === '-1'){echo ' selected';}?>><?php echo L('已取消'); ?></option>
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('申请时间');?>：</label>
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                	<div>
                                                        <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
                                                        <?php
														//echo '<a href="#nolink" class="btn btn-primary" id="downBtns">' , L('下载出金记录') , '</a> ';
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
		$where = "where a.server_id = '{$DRAdmin['server_id']}'";
        if ($SearchStatus != '') {
            $where .= " and a.status in ({$SearchStatus})";
        }
        if ($SearchPaycode) {
			$where .= " and a.type = '{$SearchPaycode}'";
        }
        if ($SearchLogin) {
			$where .= " and `mtid` = '{$SearchLogin}'";
        }
        if ($SearchSTime) {
			$where .= " and a.create_time >= '" . strtotime($SearchSTime . ' 00:00:00') . "'";
        }
        if ($SearchETime) {
			$where .= " and a.create_time <= '" . strtotime($SearchETime . ' 23:59:59') . "'";
        }

        $user = $DRAdmin;
        $agroups = $DB->getDTable("select * from t_groups where `server_id` = '{$DRAdmin['server_id']}' and `type` = 'A'");
        $groups_a_array = array();
        foreach ($agroups as $akey => $aval) {
            $groups_a_array[] = $aval['group'];
        }
        $bgroups = $DB->getDTable("select * from t_groups where `server_id` = '{$DRAdmin['server_id']}' and `type` = 'B'");
        $groups_b_array = array();
        foreach ($bgroups as $akey => $aval) {
            $groups_b_array[] = $aval['group'];
        }
		if(!$groups_a_array){
			$groups_a_array = array('0');
		}
		if(!$groups_b_array){
			$groups_b_array = array('0');
		}
        $groups_total_arr = array();
        if ($DRAdmin['_dataRange'] <= 1) {
            if ($user['abook'] == 'deny') {
                if ($user['bbook'] != 'deny') {
					$where .= " and mt4.GROUP in (" . implode(',',$groups_b_array) . ")";
                } else {
					$where .= " and mt4.GROUP = 0";
                }
            } else {
                if ($user['bbook'] == 'deny') {
					$where .= " and mt4.GROUP in (" . implode(',',$groups_a_array) . ")";
                }
            }
        }

		//非管理员，只能看到自己和伞下
		if($DRAdmin['_dataRange'] <= 1){
			$TempGetunderCustomerIds = getunderCustomerIds($DRAdmin['id']);
			if(!$TempGetunderCustomerIds){
				$TempGetunderCustomerIds = array('0');
			}
			$TempGetunderCustomerIds = array_merge(array($DRAdmin['id']), $TempGetunderCustomerIds);
			$where .= " and a.member_id in (" . implode(',',$TempGetunderCustomerIds) . ")";
		}

	
	if ($DRAdmin['ver'] == 5) {
		$recordCount = intval($DB->getField("select count(*) from (select a.* from `t_outmoney` a inner  join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.Login {$where}) c"));
		
		$successDatas = $DB->getField2Arr("select 'vtotal',sum(a.number) as TDOLLOR,COUNT(a.id) as TNUM,round(sum(a.amount),2) as TPRICE from `t_outmoney` a inner join t_member b on a.member_id = b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.Login",true);
	}else{
		$recordCount = intval($DB->getField("select count(*) from (select a.* from `t_outmoney` a inner  join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN {$where}) c"));
		
		$successDatas = $DB->getField2Arr("select 'vtotal',sum(a.number) as TDOLLOR,COUNT(a.id) as TNUM,round(sum(a.amount),2) as TPRICE from `t_outmoney` a inner join t_member b on a.member_id = b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN",true);
	}
?>



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                    
                                    
<?php
	echo '<div>';
	echo L('出金汇总') , '： &nbsp; &nbsp;' , L('笔数') , '：' , $recordCount, ' ' , L('笔'), '  &nbsp; &nbsp;';
	echo L('金额') , '： $&nbsp;' , floatval($successDatas['vtotal']['TDOLLOR']) , '&nbsp; &nbsp;';
	echo '/&nbsp;&nbsp; ' , floatval($successDatas['vtotal']['TPRICE']);
	echo '</div>';
?>
                                    
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('联系方式');?></th>
                                                    <th class="no-sort"><?php echo L('金额');?></th>
                                                    <th class="no-sort"><?php echo L('手续费');?></th>
                                                    <th class="no-sort"><?php echo L('转账方式');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('银行账户姓名') , '/' , L('开户行') , '/' , L('银行卡号');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('账户余额');?></th>
                                                    <th class="no-sort"><?php echo L('账户净值');?></th>
                                                    <th class="no-sort"><?php echo L('持仓量');?></th>
                                                    <th class="no-sort"><?php echo L('可用保证金');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php	
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
	$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();
	if ($DRAdmin['ver'] == 5) {
		$list = $DB->getDTable("select a.*,b.nickname,b.phone,b.parent_id,b.email,mt4.Group as `GROUP`,mt4.Name mtname,d.Balance as BALANCE,d.Equity as EQUITY,d.MarginFree as MARGIN_FREE,c.NAME from `t_outmoney` a inner  join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.LOGIN left join " . $DRAdmin['mt4dbname'] . ".mt5_users c on a.forwordmtlogin = c.Login left join " . $DRAdmin['mt4dbname'] . ".mt5_accounts d on a.mtid = d.Login {$where} order by a.create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	}else{
		$list = $DB->getDTable("select a.*,b.nickname,b.phone,b.parent_id,b.email,mt4.GROUP,mt4.NAME mtname,mt4.BALANCE,mt4.EQUITY,mt4.MARGIN_FREE,c.NAME from `t_outmoney` a inner  join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN left join " . $DRAdmin['mt4dbname'] . ".mt4_users c on a.forwordmtlogin = c.LOGIN {$where} order by a.create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	}
		
		//是否部署风控
        $riskList = getMonitorRisk();

        foreach ($list as $k => $v) {
            if ($riskList) {
                if (in_array($v['mtid'], $riskList)) {
                    $list[$k]['risk_user'] = 1;
                }
            }
            $list[$k]['parent_name'] = $DB->getField("select nickname from t_member where `id` = '{$v['parent_id']}'");
            $list[$k]['Audit_name'] = $DB->getField("select nickname from t_member where `id` = '{$v['adminid']}'");
            //判断是否已经代付过
            $list[$k]['ispayout'] = 0;//$DB->getField("select id from t_paid where `out_id` = '{$v['id']}'");
        }

        $mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and `status` = 1");
        if ($mtserver['ver'] == '5') {
            foreach ($list as $key => $value) {
                $list[$key]['chiVolume'] = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME from " . $DRAdmin['mt4dbname'] . ".mt5_positions where Login = '{$value['mtid']}' and Action in (0,1)");
            }
        } else {
            foreach ($list as $key => $value) {
                $list[$key]['chiVolume'] = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME from " . $DRAdmin['mt4dbname'] . ".mt4_trades where `LOGIN` = '{$value['mtid']}' and `CMD` in (0,1) and `CLOSE_TIME` = '1970-01-01 00:00:00'");
            }
        }

        $groups = $DB->getDTable("select * from t_groups where `server_id` = '{$DRAdmin['server_id']}'");
        for ($i = 0; $i < count($list); $i++) {
            foreach ($groups as $gkey => $gval) {
                if ($gval['group'] == $list[$i]['GROUP']) {
                    $list[$i]['group_type'] = $gval['type'];
                }
            }
        }

        $paylist = $DB->getDTable("select * from t_pay where `server_id` = '{$DRAdmin['server_id']}' and `status` in (0,1)");


	if(count($list) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		foreach($list as $key=>$rs){
			echo '<tr' , $rs['risk_user'] == 1 ? ' style="color:red" title="异常用户出金"' : '' , '>';
			echo '<td>';
			echo $rs['risk_user'] == 1 ? '<font color="red">☆ </font>' : '';
			echo L('MT账号') , '：' , $rs['mtid'] , '<br/>';
			echo L('MT分组') , '：' , $rs['GROUP'];
			echo '</td>';
			if(!can_look_parent_info()){
				echo '<td>-</td>';
			}else{
				echo '<td>' , $rs['parent_name'] , '</td>';
			}
			echo '<td>' , L('英文名') , '：' , $rs['nickname'] , '<br/>' , L('手机号') , '：' , hideStr($rs['phone'],3,4) , '</td>';
			echo '<td>' , '$' , $rs['number'] , '<br/>(' , $rs['exchange'] * 1 , ')<br/>' , $rs['f_pa'] , round($rs['amount'],2) , '</td>';
			echo '<td>' , '$' , $rs['fee'] , '<br/>' , $rs['f_pa'] , round($rs['fee']*$rs['exchange'],2) , '</td>';
			echo '<td>' , getouttype($rs['type'],$rs['forwordmtlogin']) , '</td>';
			echo '<td>';
			echo L('申请时间') , '：' , date('Y-m-d H:i:s',$rs['create_time']) , '<br/>';
			echo L('处理时间') , '：';
			if($rs['status'] == 1){
				echo date('Y-m-d H:i:s',$rs['reply_time']);
			}
			if($rs['status'] == 9){
				echo date('Y-m-d H:i:s',$rs['visit_time']);
			}
			if($rs['status'] == -1){
				echo date('Y-m-d H:i:s',$rs['reply_time']);
			}
			echo '</td>';
			echo '<td>';
			//echo $rs['swiftCode'];
			if(strlen($rs['forwordname'])){
				echo $rs['forwordname'];
				echo '<br>';
			}
			if($rs['forwordmtlogin'] > 0){
				echo L('转入MT账户名称') , '：' , $rs['NAME'] , '<br>';
				echo L('转入MT账户') , '：' , $rs['forwordmtlogin'];
			}else{
				echo $rs['bankname'];
				echo '<br>';
				echo $rs['bankaccount'];
			}
			echo '</td>';
			echo '<td>';
			echo L(getoutstatus($rs['status'])) , '<br/>' , L($rs['content']);
			if($rs['status'] == 9){
				if(strlen($rs['Audit_name'])){
					echo L('审核人') , ': ' , $rs['Audit_name'];
				}
			}
			if($rs['status'] == '1'){
				if(strlen($rs['Audit_name'])){
					echo '<br/>';
					echo L('驳回人') , ':' , $rs['Audit_name'];
				}
				
			}
			echo '<br/>';
			if($rs['group_type'] == ''){
				echo '<font color="red">' , L('组未同步') , '</font>';
			}
			echo '</td>';
			echo '<td>' , round($rs['BALANCE'],2) , '</td>';
			echo '<td>' , round($rs['EQUITY'],2) , '</td>';
			if ($DRAdmin['ver'] == 5) {
				echo '<td>' , $rs['chiVolume']['vtotal']/10000 , L('手') , '</td>';
			}else{
				echo '<td>' , $rs['chiVolume']['vtotal']/100 , L('手') , '</td>';
			}
			echo '<td>' , round($rs['MARGIN_FREE'],2) , '</td>';
			echo '<td>';			
				if($rs['status'] == 0 || $rs['status'] == 8){
					if(chk_in_access('出金审核')){
						if($rs['group_type'] == 'A'){
							if($user['abook'] == 'manage'){
								echo '<button type="button" val="' , $rs['id'] , '" data-toggle="modal"  data-target="#myModal" class="btn btn-success btn-xs" onclick="visitoutmoney(this)">' , L('出金') , '</button> ';
							}else{
								echo '<span class="btn btn-xs" type="button" rel="A" href="#nolink">' , L('无Abook权限') , '</span> ';
							}
						}else{
							echo '<button type="button" val="' , $rs['id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-success btn-xs" onclick="visitoutmoney(this)">' , L('出金') , '</button> ';
						}
					}
					
					if(chk_in_access('出金驳回')){
						if($rs['group_type'] == 'A'){
							if($user['abook'] == 'manage'){
								echo '<button type="button" val="' , $rs['id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-primary btn-xs" onclick="resetnoney(this)">' , L('驳回') , '</button> ';
							}
						}else{
							echo '<button type="button" val="' , $rs['id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-primary btn-xs" onclick="resetnoney(this)">' , L('驳回') , '</button> ';
						}
					}
				}
				
				//echo '<a href="#nolink" rel="' , $rs['id'] , '" class="btn btn-danger btn-xs" onclick="paymoney(this)">' , L('付款') , '</a> ';
				//echo '<a href="?clause=paidlist&out_id=' , $rs['id'] , '" class="btn btn-success btn-xs">' , L('查看代付信息') , '</a> ';
				
				echo '<a href="?clause=showinfo&id=' , $rs['id'] , '" class="btn' , $rs['status'] == 1 ? ' btn-warning' : ' btn-primary' , ' btn-xs">' , L('查看') , '</a>';
			echo '</td>';
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







    <!--入金弹出层-->
    <div class="modal inmodal" id="managerDeposit" tabindex="-1" role="dialog" aria-hidden="true" style="">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">

                <form  id='inmoneyform' name='inmoneyform' action="{:U('Deposit/managerDepoist')}" method="POST">      
                    <div class="modal-body">

                        <div class="form-group"><label><?php echo L('入金金额'); ?>（<?php echo L('美元'); ?>）：</label> <input type="text"  style="ime-mode:disabled" onblur="clearNoNum(this);"  onkeyup="clearNoNum(this);" id="inmoneyid" name='number' placeholder="<?php echo L('请输入金金额'); ?>" class="form-control"></div>
                        <div class="form-group"><label><?php echo L('入金帐号'); ?>：</label> <input type="text" id='mtid' name='mtid'  class="form-control"></div>
                        <div class="form-group" ><label><?php echo L('确认帐号'); ?>：</label> <input type="text" id='mtlogin2' name="mtlogin2" placeholder="" class="form-control"></div>
                        <div class="form-group"><label><?php echo L('备注'); ?>：</label> <input type="text" id="remark" name='remark' value=''   placeholder="" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white"  id='closeout1' data-dismiss="modal"><?php echo L('取消入金'); ?></button>
                        <button type="button" class="btn btn-primary" id='inmoneybtn' ><?php echo L('提交在线入金'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
	<!--入金弹出层-->









    <!--代付弹出层-->
    <div class="modal inmodal" id="setpaymodel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('代付'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                <form  id='setpayfrom' name='setpayfrom'>
                    <div class="modal-body">

                        <div class="col-md-12"> 
                           
                            <div class="form-group">
                                <label><?php echo L('付款线路'); ?>：</label>
                                <div class="input-group m-b">
                                    <volist name='paylist' id='voo'>
                                        <div class="radio radio-info radio-inline">
                                            <input type="radio" id="inlinepay{$voo.Id}" class='payid' checktype='{$voo.checktype}' value="{$voo.Id}" name="payid">
                                            <label for="inlinepay{$voo.Id}">{$voo.PayName}</label>
                                        </div>
                                    </volist>
                                </div>
                            </div>
                             <div class="form-group">
                                <label><?php echo L('联行号'); ?>：</label>
                                <div class="input-group m-b-none">
                                    <input type="text"  class="form-control" name="lianhangno" id="lianhangno" value=''>
                                </div>
                            </div>
                            <div class="form-group" id='phonecode' style='display: none'>
                                <label class=""><?php echo L('请输入手机验证码'); ?>：</label>

                                <div style=" width: 100%">
                                    <div class="col-sm-6" style=" padding-left: 0px">
                                        <input id="mobile" name="mobile"  class="form-control valid " type="text" placeholder="<?php echo L('请输入手机'); ?>"  aria-required="true" aria-invalid="false"  > 
                                    </div>

                                    <div class="col-sm-3" style=" padding-left: 0px">
                                        <input id="phone_code" name="phone_code"  class="form-control valid " type="text" placeholder="<?php echo L('手机验证码'); ?>"  aria-required="true" aria-invalid="false"  >

                                    </div>
                                    <input class="btn btn-primary" type="button" id="getcode" value="<?php echo L('获取验证码'); ?>"/>
                                </div>


                            </div>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top: none; background-color: #f5f5f5">
                        <input type="hidden" name='out_id' id='out_id'/>
                        <button type="button" class="btn btn-white " id='closepayout' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                        <button type="button" class="btn btn-primary" id='savepayout' ><?php echo L('确认'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--代付弹出层-->








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
        
		<script src="/assets/js/fancybox/jquery.fancybox.js"></script>
        <link href="/assets/js/fancybox/jquery.fancybox.css" rel="stylesheet">
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script> 
        
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
$(function () {
	$(document).on("click","#savepayout",function(){
	//$("#savepayout").click(function () {
		var id = $("#out_id").val();
		var payid = $(".payid:checked").val();
		var lianhangno = $("#lianhangno").val();
		var mobile = $("#mobile").val();
		var phone_code = $("#phone_code").val();
		swal({
			title: "<?php echo L('出金付款'); ?>",
			text: "<?php echo L('如果未确认前'); ?>，<?php echo L('请慎重操作'); ?>！",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确认付款'); ?>",
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
		}, function () {
			var url = "{:U('Deposit/dopaid')}";
			$.post(url, {out_id: id, payid: payid,lianhangno:lianhangno,mobile:mobile,phone_code:phone_code}, function (data) {
				if (data.status) {
					swal("<?php echo L('付款成功'); ?>", data.info, "success");
					setTimeout(function () {
						document.location.reload()
					}, 1500);
				} else {
					swal("<?php echo L('付款失败'); ?>", data.info, "warning");
				}
			}, 'json');

		});

	});
	//验证方式
	$(document).on("click",".payid",function(){
	//$(".payid").click(function () {
		var type = $(this).attr('checktype');
		if (type == 1) {
			$("#phonecode").show();
		}else{
			 $("#phonecode").hide();
		}
	});
	var wait = 60;
	//短信验证码
	$(document).on("click","#getcode",function(){
	//$("#getcode").click(function () {
		if (wait != 60) {
			return false;
		}
		var phone = $("#mobile").val();
		var payid = $(".payid:checked").val();
		var _this = $(this);


		time(this);
		$.post('{:U("Deposit/getpaycode")}', {phone: phone, payid: payid}, function (data) {
			if (data.status == 1) {
				layer.alert('发送成功');
			} else {
				layer.alert(data.info);
				wait = 0;
			}
		}, 'json');
	});
})


//代付
function paymoney(this_) {
	document.setpayfrom.reset();
	$('#setpaymodel').modal('toggle');
	$("#out_id").val($(this_).attr('rel'));
}

function time(o) {
	if (wait == 0) {
		$("#getcode").val('重发');
		wait = 60;
	} else {
		$("#getcode").val('重发(' + wait + ')');
		wait--;
		setTimeout(function () {
			time(o)
		}, 1000)
	}
}

//驳回
function resetnoney(this_) {
	var id = $(this_).attr('val');
	swal({
		title: "<?php echo L('出金驳回'); ?>",
		type: "input",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "<?php echo L('确认'); ?>",
		closeOnConfirm: false,
		showLoaderOnConfirm: true,
		inputPlaceholder: "<?php echo L('请输入驳回原因'); ?>"
	}, function (pass) {
		if (pass == '') {
			swal.showInputError("<?php echo L('请输入驳回原因'); ?>!");
			return false
		}
		var url = "?clause=resetoutnoney";
		$.post(url, {id: id, content: pass}, function (data) {

			if (data.status) {
				swal("<?php echo L('出金驳回'); ?>", data.info, "success");
				setTimeout(function () {
					document.location.reload()
				}, 1500);
			} else {
				swal("<?php echo L('出金驳回'); ?>", data.info, "warning");
			}
		}, 'json');
	});
}

function visitoutmoney(this_) {
	var id = $(this_).attr('val');
	swal({
		title: "<?php echo L('出金审核'); ?>",
		text: "<?php echo L('出金审核过后'); ?>，<?php echo L('将从MT下账'); ?>，<?php echo L('如果未确认前'); ?>，<?php echo L('请慎重操作'); ?>！",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "<?php echo L('确认出金'); ?>",
		closeOnConfirm: false,
		showLoaderOnConfirm: true,
	}, function () {
		var url = "?clause=visitoutmoney";
		$.post(url, {id: id}, function (data) {
			if (data.status) {
				swal("<?php echo L('出金成功'); ?>", data.info, "success");
				setTimeout(function () {
					document.location.reload()
				}, 1500);
			} else {
				swal("<?php echo L('出金失败'); ?>", data.info, "warning");
			}
		}, 'json');

	});
}


$(document).on("click","#downBtns",function(){
//$("#downBtns").click(function () {
	var value = $("#commentForm").serialize();
	document.location.href = "{:U('Deposit/down_waitin',array('act'=>'down'))}?" + value;
});
//$("#data_5 .input-daterange").datepicker({keyboardNavigation: !1, forceParse: 1, autoclose: !0})
//$('#paycode').val('{$Think.request.paycode}');
//$('#status').val('{$Think.request.status}');
</script>

        
        
        

    </body>
</html>
