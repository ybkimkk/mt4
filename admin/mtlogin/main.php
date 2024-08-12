<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchQ = FRequestStr('searchQ');

$myLoginMT = $DB->getField2Arr("select id,loginid from `t_member_mtlogin` where `status` = 1 and `member_id` = '{$DRAdmin['id']}' and `mtserver` = '{$DRAdmin['server_id']}'");
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('我的MT交易账号') , getCurrMt4ServerName();?></h4>
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
                                                    <input type="text" class="form-control" minlength="2" value="<?php echo $SearchQ;?>" name="searchQ" id="searchQ" placeholder="<?php echo L('请输入昵称，MT名称，账号关键词'); ?>">
                                                 </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<div>
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索'); ?></button>
														<?php
                                                            if(chk_in_access('绑定MT帐号')){
																echo '<a href="#nolink" class="btn btn-danger btn-bitbucket addaccount"><i class="fa fa-check-circle-o"></i>&nbsp;' , L('绑定MT帐号') , '</a> ';
                                                            }
															if(chk_in_access('MT开户')){
																echo '<a class="btn btn-danger" href="?clause=applyaccount"><i class="fa fa-user-md"></i>&nbsp;' , L('MT开户') , '</a> ';
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


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('所属用户');?></th>
                                                    <th class="no-sort"><?php echo L('MT账户');?></th>
                                                    <th class="no-sort"><?php echo L('MT名称');?></th>
                                                    <?php
                                                    if($DRAdmin['userType'] != 'direct'){
													?>
                                                    <th class="no-sort"><?php echo L('分组');?></th>
                                                    <?php
													}
													?>
                                                    <th class="no-sort"><?php echo L('杠杆');?></th>
                                                    <th class="no-sort"><?php echo L('余额');?>($)</th>
                                                    <th class="no-sort"><?php echo L('净值');?>($)</th>
                                                    <th class="no-sort"><?php echo L('赠金') , '($)';?></th>
                                                    <th class="no-sort"><?php echo L('可用预付款');?></th>
                                                    <th class="no-sort"><?php echo L('比例');?></th>
                                                    <th class="no-sort"><?php echo L('开户时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('账户类型');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	 $data = $DB->getDTable("select * from t_mt4_server where status = 1");








		$where = "where `mtserver` = '" . $DRAdmin['server_id'] . "'";
        $where .= " AND `status` = 1";
        if (strlen($SearchQ)) {
            $where .= " AND (`loginid` like '%" . $SearchQ . "%' or `member_id` in (select id from t_member where `nickname` like '%" . $SearchQ . "%' or chineseName like '%" . $SearchQ . "%') or `loginid` in ";
			if($DRAdmin['ver'] == 5){
				$where .= "(select Login from {$DRAdmin['mt4dbname']}.mt5_users where `Name` like '%" . $SearchQ . "%')";
			}else{
				$where .= "(select LOGIN from {$DRAdmin['mt4dbname']}.mt4_users where `NAME` like '%" . $SearchQ . "%')";
			}
			$where .= ")";
        }
		//非管理员，只能看到自己伞下
		if($DRAdmin['_dataRange'] <= 0){
			$where .= " and member_id = '" . $DRAdmin['id'] . "'";
		}else if($DRAdmin['_dataRange'] <= 1){
			$TempGetunderCustomerIds = getunderCustomerIds($DRAdmin['id']);
			if(!$TempGetunderCustomerIds){
				$TempGetunderCustomerIds = array('0');
			}
			$TempGetunderCustomerIds = array_merge(array($DRAdmin['id']), $TempGetunderCustomerIds);
			$where .= " and member_id in (" . implode(',',$TempGetunderCustomerIds) . ")";
		}
        if ($_REQUEST['member_id']) {
            $where .= " and member_id = " . $_REQUEST['member_id'];
        }
		


		$recordCount = intval($DB->getField("select count(*) from `t_member_mtlogin` {$where}"));
		
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
		
		if($DRAdmin['ver'] == 5){
			$sql = "select mtlogin.*,mt4.mt4_name mt4_name,mt4.real isreal,mt4.db_name,mem.nickname,mem.realname,mem.userType";
			$sql .= ",mtaccount.Balance as BALANCE";
			$sql .= ",mtaccount.Equity as EQUITY";
			$sql .= ",mtuser.Login MTLOGIN";
			$sql .= ",mtuser.Group MTGROUP";
			$sql .= ",mtuser.Name MTNAME";
			$sql .= ",mtuser.Registration as REGDATE";
			$sql .= ",mtuser.Leverage as LEVERAGE";
			$sql .= ",1 as ENABLE";
			$sql .= ",0 as ENABLE_READONLY";
			$sql .= ",mtaccount.Margin as MARGIN";
			$sql .= ",mtaccount.MarginLevel as MARGIN_LEVEL";
			$sql .= ",mtaccount.MarginFree as MARGIN_FREE";
			$sql .= ",mtaccount.Credit as CREDIT";
			$sql .= ",llog.reason";
			$sql .= " from";
			$sql .= " (select * from `t_member_mtlogin` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) mtlogin";
			$sql .= " LEFT JOIN {$DRAdmin['mt4dbname']}.mt5_users mtuser on mtlogin.loginid = mtuser.Login";
			$sql .= " LEFT JOIN (SELECT a.* from t_member_leverage_log a inner join (SELECT  max(id) id  from t_member_leverage_log GROUP BY login_id) b on a.id = b.id) llog on mtlogin.loginid = llog.login_id";
			$sql .= " LEFT JOIN t_mt4_server mt4 on mt4.id = mtlogin.mtserver";
			$sql .= " LEFT JOIN t_member mem on mem.id = mtlogin.member_id";
			$sql .= " LEFT JOIN {$DRAdmin['mt4dbname']}.mt5_accounts mtaccount on mtlogin.loginid = mtaccount.Login";
		}else{
			$sql = "select mtlogin.*,mt4.mt4_name mt4_name,mt4.real isreal,mt4.db_name,mem.nickname,mem.realname,mem.userType";
			$sql .= ",mtuser.BALANCE";
			$sql .= ",mtuser.EQUITY";
			$sql .= ",mtuser.LOGIN MTLOGIN";
			$sql .= ",mtuser.GROUP MTGROUP";
			$sql .= ",mtuser.NAME MTNAME";
			$sql .= ",mtuser.REGDATE";
			$sql .= ",mtuser.LEVERAGE";
			$sql .= ",mtuser.ENABLE";
			$sql .= ",mtuser.ENABLE_READONLY";
			$sql .= ",mtuser.MARGIN";
			$sql .= ",mtuser.MARGIN_LEVEL";
			$sql .= ",mtuser.MARGIN_FREE";
			$sql .= ",mtuser.CREDIT";
			$sql .= ",llog.reason";
			$sql .= " from";
			$sql .= " (select * from `t_member_mtlogin` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) mtlogin";
			$sql .= " LEFT JOIN {$DRAdmin['mt4dbname']}.mt4_users mtuser on mtlogin.loginid = mtuser.LOGIN";
			$sql .= " LEFT JOIN (SELECT a.* from t_member_leverage_log a inner join (SELECT  max(id) id  from t_member_leverage_log GROUP BY login_id) b on a.id = b.id) llog on mtlogin.loginid = llog.login_id";
			$sql .= " LEFT JOIN t_mt4_server mt4 on mt4.id = mtlogin.mtserver";
			$sql .= " LEFT JOIN t_member mem on mem.id = mtlogin.member_id";
		}
		
		$query = $DB->query($sql);
		if($DB->numRows($query) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			while($rs = $DB->fetchArray($query)){
				echo '<tr id="tr_' , $rs['id'] , '">';
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
						echo ' / ' , $rs['realname'];
					}
					
					if($DRAdmin['userType'] != 'direct'){
						echo '<br>';
						switch($rs['userType']){
							case 'agent':
								echo $rs['LEVEL_NAME'];
								break;
							case 'direct':
								echo L('直接客户');
								break;
							case 'member':
								echo L('员工');
								break;
						}
					}
				echo '</td>';
				echo '<td>' , $rs['loginid'] , '</td>';
				echo '<td>' , $rs['MTNAME'] , '</td>';
				if($DRAdmin['userType'] != 'direct'){
					echo '<td>' , $rs['MTGROUP'] , '</td>';
				}
				echo '<td>';
					echo '1:' , $rs['LEVERAGE'];
					if(in_array($rs['loginid'],$myLoginMT) && $editLeverage){
						if($rs['editStatus'] == 1){
							echo '<a class="modifyattr4" rel="' , $rs['loginid'] , '" data-toggle="modal" href="#nolink"><font color="green">' , L('重新申请') , '</font></a>';
						}else if($rs['editStatus'] == 2){
							echo '<font color="red">' , L('待审核') , '</font>';
						}else if($rs['editStatus'] == 4){
							echo '<a class="modifyattr4" rel="' , $rs['loginid'] , '" data-toggle="modal"  href="#nolink"><font color="red" title="' , $rs['reason'] , '">' , L('已驳回') , '</font></a>';
						}else{
							echo '<a class="modifyattr4" rel="' , $rs['loginid'] , '" data-toggle="modal"  href="#nolink"><font color="green">' , L('申请') , '</font></a>';
						}
					}
					//if(chk_in_access('查看客户杠杆调整申请')){
						if($vo['auditStatus'] == 1){
							echo '<a class="modifyattr5"  rel="' , $rs['editid'] , '" data-toggle="modal" href="#nolink">';
							echo '<font color="red">' , L('待审核') , '</font>';
							echo '</a>';
						}else if($vo['auditStatus'] == 2){
							echo '<font color="green">' , L('已审核') , '</font>';
						}else if($vo['auditStatus'] == 4){
							echo '<font color="red" title="' , $rs['reason'] , '">' , L('已驳回') , '</font>';
						}
					//}
				echo '</td>';
				echo '<td>' , round($rs['BALANCE'],2) , '</td>';
				echo '<td>' , round($rs['EQUITY'],2) , '</td>';
				echo '<td>' , round($rs['CREDIT'],2) , '</td>';
				echo '<td>' , round($rs['MARGIN_FREE'],2) , '</td>';
				echo '<td>' , round($rs['MARGIN_LEVEL'],2) , '</td>';
				echo '<td>' , $rs['REGDATE'] , '</td>';
				echo '<td>';
				if($rs['ENABLE'] == '1'){
					if($rs['ENABLE_READONLY'] == '1'){
						echo '<FONT COLOR="RED">' , L('只读') , '</FONT>';
					}else{
						echo '<FONT COLOR="green">' , L('正常') , '</FONT>';
					}
				}else{
					echo '<FONT COLOR="RED">' , L('禁用') , '</FONT>';
				}
				echo '</td>';
				echo '<td>';
				if($rs['mt_type'] == '0'){
					echo L('主账户');
				}else{
					echo L('交易账户');
				}
				echo '</td>';
				echo '<td>';
					echo '<div class="btn-group dropup">';
					echo '<button data-toggle="dropdown" class="btn btn-white btn-xs dropdown-toggle">' , L('操作') , ' <span class="caret"></span></button>';
					echo '<ul class="dropdown-menu" style="z-index:10000">';
					if(chk_in_access('修改') || $rs['member_id'] == $DRAdmin['id']){
						echo '<a class="dropdown-item modifyaccount" rel="' , $rs['id'] , '" data-toggle="modal" href="#nolink">' , L('修改') , '</a>';
					}
					if(chk_in_access('重置MT密码') || $rs['member_id'] == $DRAdmin['id']){
						echo '<a class="dropdown-item modifyresetpwd" rel="' , $rs['id'] , '" data-toggle="modal" href="#nolink">' , L('重置MT密码') , '</a>';
					}
					if(chk_in_access('调整杠杆')){
						echo '<a class="dropdown-item modifyattr" rel="' , $rs['id'] , '" data-toggle="modal" href="#nolink">' , L('调整杠杆') , '</a>';
					}
					if(chk_in_access('调整分组')){
						echo '<a class="dropdown-item modifyattr2" rel="' , $rs['id'] , '"  data-toggle="modal" href="#nolink">' , L('调整分组') , '</a>';
					}
					if(chk_in_access('调整只读禁用')){
						echo '<a class="dropdown-item modifyattr3" rel="' , $rs['id'] , '"  data-toggle="modal" href="#nolink">' , L('调整只读禁用') , '</a>';
					}
					if(chk_in_access('MT账号移仓')){
						echo '<a class="dropdown-item forwordto" lang="' , $vo['loginid'] , '" rel="' , $rs['id'] , '" data-toggle="modal" href="#nolink">' , L('MT账号移仓') , '</a>';
					}
					//if(chk_in_access('出入金限额设置')){
						//echo '<a class="dropdown-item" href="deposit_limits.php?loginid=' , $rs['loginid'] , '">' , L('出入金限额设置') , '</a>';
					//}
					//$rs['member_id'] == $DRAdmin['id']
					if(chk_in_access('删除MT账号')){
						echo '<a class="dropdown-item delete" href="#nolink" rel="' , $rs['id'] , '" re="' , $rs['nickname'] , '" lang="' , $rs['loginid'] , '">' , L('删除') , '</a>';
					}
					echo '</ul>';
					echo '</div>';
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
                    
                    
                    
                    
                    
                    

     <!--弹出层-->
	    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('账户设置');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingform' name='settingform'>
		            	<input type="hidden" name="status" value="1" />
		            	<input type="hidden" name="id" ID="id" value="" />
		                <div class="modal-body">
		                    <div class="form-group"><label>MT<?php echo L('服务器'); ?>：</label> 
	                            <select name='mtserver' id="mtserver" class='form-control m-b'>
                                    <?php
                                    foreach($data as $key=>$vo){
									?>
	                                     <option value='<?php echo $vo['id']; ?>'><?php echo $vo['mt4_name']; ?>
	                                     <?php
                                         if($vo['real'] == '0'){
											 echo '（' , L('模拟仓') , '）';
										 }else{
											 echo '（' , L('真实仓') , '）';
										 }
										 ?>
	                                     </option>
	                                <?php
									}
									?>
	                            </select>
	                        </div>
	                        <label><?php echo L('账户类型'); ?>：</label>
		                	<div class="input-group m-b">
		                        <select name='mt_type' id="mt_type" class='form-control m-b'>
                                    <option value='0'><?php echo L('主账户'); ?>（<?php echo L('仅能绑一个'); ?>）</option>
                                    <option value='1'><?php echo L('交易账户'); ?>（<?php echo L('可绑多个'); ?>）</option>
	                            </select>
		                    </div>
                                 
		                	<label><?php echo L('MT账号'); ?>：</label>
		                	<div class="input-group m-b">
		                        <input type="text" class="form-control" name="loginid" id="loginid">
		                        <input style="display:none">
		                    </div>
                              <?php
	                         	if($DRAdmin['_dataRange'] <= 1){
	                          ?>
	                     	<label><?php echo L('MT密码'); ?>：</label>
		                	<div class="input-group m-b">
		                        <input type="password" class="form-control" name="password" id="password"> 
		                    </div>
                                <?php
                                }else{
								?>
                                <label><?php echo L('所属账户'); ?>：</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="member_id" name="member_id">
                                                                <input type="hidden" class="form-control" id="memberuserid" name="memberuserid">
                                    <div class="input-group-btn" style="position:relative;">
                                        <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                        </ul>
                                    </div>
                                    <!-- /btn-group -->
                                </div>
                                <?php
                                }
								?>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closeout' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='saveaccount' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
        
        
        
            <!--修改密码弹出层-->
	    <div class="modal inmodal" id="myModalpassword" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('修改密码');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingpwd' name='settingpwd'>
		            	<input type="hidden" name="id" ID="attridpwd" value="" />
		                <div class="modal-body">
		                   
	                       <label><?php echo L('密码类型'); ?>：</label>
                                  <div class="input-group m-b">
                                    <div class="radio radio-info radio-inline">
                                        <input type="radio" id="inlineRadio5" value="0" checked="checked" name="pwdtype" class="pwdtype">
                                        <label for="inlineRadi5"><?php echo L('交易密码'); ?></label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input type="radio" id="inlineRadio6" value="1" name="pwdtype" class="pwdtype" >
                                        <label for="inlineRadio6"> <?php echo L('只读密码'); ?> </label>
                                    </div>
                                    </div>
                                 
                                        
	                     	<label> <?php echo L('旧密码'); ?>：</label>
		                	<div class="input-group m-b"> <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
		                        <input type="password" class="form-control" name="password" id="password"> 
		                    </div>
                             
                                 <label> <?php echo L('新密码'); ?>：</label>
		                	<div class="input-group m-b"> <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
		                        <input type="password" class="form-control" name="newpassword" id="password"> 
		                    </div>
                                  <span class="help-block m-b-none"><i class="fa fa-info-circle"></i> <?php echo L('密码需包含大写，小写字母和数字的组合例如1sUcek5s最少8个字符'); ?></span>
                                 <label> <?php echo L('确认密码'); ?>：</label>
		                	<div class="input-group m-b"> <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
		                        <input type="password" class="form-control" name="conpassword" id="password"> 
		                    </div>
                             
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closepwd' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savepwd' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
        
        
        
	    <!--修改密码弹出层-->
               <!--重置密码弹出层-->
	    <div class="modal inmodal" id="myrestpassword" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('重置密码');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='settingrestpwd' name='settingrestpwd'>
		            	<input type="hidden" name="id" ID="resetidpwd" value="" />
		                <div class="modal-body">
		                   
	                       <label><?php echo L('密码类型'); ?>：</label>
                                  <div class="input-group m-b">
                                    <div class="radio radio-info radio-inline mr-sm-2">
                                        <input type="radio" id="inlineRadio7" value="0" checked="checked" name="setpwdtype" class="pwdtype">
                                        <label for="inlineRadio7"><?php echo L('交易密码'); ?></label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input type="radio" id="inlineRadio8" value="1" name="setpwdtype" class="pwdtype" >
                                        <label for="inlineRadio8"> <?php echo L('只读密码'); ?> </label>
                                    </div>
                                    </div>
                               </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closerestpwd' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='saveresetpwd' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
        
        
        
	       <!--修改用户属性-->
            <?php
            if(chk_in_access('调整杠杆')){
			?>
            <div class="modal inmodal" id="myModalattr" tabindex="-1" role="dialog" aria-hidden="true">
    	        <div class="modal-dialog">
    	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('调整杠杆');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
    	               <form  id='settingformattr' name='settingformattr'>
    		            	<div class="modal-body">
                                <input type='hidden' name='id' id='attrid'>
                                <label><?php echo L('杠杆比例'); ?>：</label>
    		                	<div class="input-group m-b">
    		                        <select name='leveages' id="leveages" class='form-control m-b'>
                                        <?php
										$leveages = explode(',', getConfigValue('LEVERAGES', $DRAdmin['server_id']));
										foreach($leveages as $key=>$vvo){
										?>
                                            <option value='<?php echo $vvo; ?>'><?php echo $vvo; ?></option>
                                        <?php
										}
										?>
    	                            </select>
    		                    </div>    
                            </div>
    		                <div class="modal-footer">
    		                    <button type="button" class="btn btn-white" id='closeoutlevearge' data-dismiss="modal"><?php echo L('关闭'); ?></button>
    		                    <button type="button" class="btn btn-primary" id='saveaccountattr' ><?php echo L('确认'); ?></button>
    		                </div>
    		            </form>
    	            </div>
    	        </div>
	        </div>
            <?php
			}
			?>
            
            
            
           <!--修改用户属性-->
            <?php
            if(chk_in_access('调整分组')){
			?>
            <div class="modal inmodal" id="myModalattr2" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('调整分组');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                       <form  id='settingformattr2' name='settingformattr2'>
                            <div class="modal-body">
                                <input type='hidden' name='id' id='attrid'>
                                <label><?php echo L('MT分组'); ?>：</label>
                                <div class="input-group m-b">
                                    <select name='mt4group' id="mt4group" class='form-control m-b'>
                                        <?php
										$groupwhere['server_id'] = $DRAdmin['server_id'];
										if ($DRAdmin['_dataRange'] <= 1) {
											$mygroup = $DB->getField("select recomment_groups from t_member where id = '" . $DRAdmin['id'] . "'");
											if ($mygroup) {
												$groupwhere['group'] = array('in', $mygroup);
											} else {
												$groupwhere['id'] = array('in', '-1');
											}
											$mt4group = $DB->getDTable("select * from t_groups " . cz_where_to_str($groupwhere));
										} else {
											$mt4group = $DB->getDTable("select * from t_groups " . cz_where_to_str($groupwhere));
										}
										
										foreach($mt4group as $key=>$mg){
										?>
                                            <option value='<?php echo $mg['group']; ?>'><?php echo $mg['group']; ?></option>
                                        <?php
										}
										?>
                                    </select>
                                </div>   
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" id='closeoutgroup' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                                <button type="button" class="btn btn-primary" id='saveaccountattr2' ><?php echo L('确认'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
			}
			?>


            <?php
            if(chk_in_access('调整只读禁用')){
			?>
            <div class="modal inmodal" id="myModalattr3" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('调整只读禁用');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                       <form  id='settingformattr3' name='settingformattr3'>
                            <div class="modal-body">
                                <input type='hidden' name='id' id='attrid'>
                                <label><?php echo L('只读状态'); ?>：</label>
                                <div class="input-group m-b">
                                    <div class="radio radio-info radio-inline mr-sm-2">
                                        <input type="radio" id="inlineRadio1" value="1" name="readonly" class="readonly">
                                        <label for="inlineRadio1"><?php echo L('只读'); ?></label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input type="radio" id="inlineRadio2" value="0" name="readonly" class="readonly" >
                                        <label for="inlineRadio2"> <?php echo L('正常'); ?> </label>
                                    </div>
                                </div>
                                <label><?php echo L('是否禁用'); ?>：</label>
                                <div class="input-group m-b">
                                    <div class="radio radio-info radio-inline mr-sm-2">
                                        <input type="radio" id="inlineRadio3" value="0" name="disable" class="disable" >
                                        <label for="inlineRadio3"> <?php echo L('禁用'); ?> </label>
                                    </div>
                                    <div class="radio radio-inline">
                                        <input type="radio" id="inlineRadio4" value="1" name="disable" class="disable" >
                                        <label for="inlineRadio4"> <?php echo L('正常'); ?></label>
                                    </div>
                                </div>       
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" id='closeout2' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                                <button type="button" class="btn btn-primary" id='saveaccountattr3' ><?php echo L('确认'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
			}
			?>
            
            
            
	       <!--用户自主修改杠杆属性-->
            <?php
            //if(chk_in_access('/member/selfLeverageView')){
			?>
            <div class="modal inmodal" id="myModalattr4" tabindex="-1" role="dialog" aria-hidden="true">
    	        <div class="modal-dialog">
    	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('申请调整杠杆');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
    	               <form  id='settingformattr4' name='settingformattr4'>
    		            	<div class="modal-body">
                                <input type='hidden' name='id' id='editloginid' value="<?php echo $vo['loginid']; ?>">
                                <label><?php echo L('杠杆比例'); ?>：</label>
    		                	<div class="input-group m-b">
    		                        <select name='leveages' id="leveages" class='form-control m-b'>
                                        <?php
										foreach($leveages as $key=>$vvo){
										?>
                                            <option value='<?php echo $vvo; ?>'><?php echo $vvo; ?></option>
                                        <?php
										}
										?>
    	                            </select>
    		                    </div>    
                            </div>
    		                <div class="modal-footer">
    		                    <button type="button" class="btn btn-white" id='closeoutlevearge2' data-dismiss="modal"><?php echo L('关闭'); ?></button>
    		                    <button type="button" class="btn btn-primary" id='saveaccountattr4' ><?php echo L('确认'); ?></button>
    		                </div>
    		            </form>
    	            </div>
    	        </div>
	        </div>
            <?php
			//}
			?>
            
            
            
           <!--用户自主修改杠杆属性-->
	       <!--管理员查看客户申请修改杠杆-->
            <?php
            //if(chk_in_access('/member/doAuditLeverage')){
			?>
            <div class="modal inmodal" id="myModalattr5" tabindex="-1" role="dialog" aria-hidden="true">
    	        <div class="modal-dialog">
    	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('申请调整杠杆');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
    	               <form  id='settingformattr5' name='settingformattr5'>
    		            	<div class="modal-body">
                                <input type='hidden' name='id' id='aid'>

                                <label><?php echo L('昵称'); ?>：<input type='text' name='nickname' id='nickname' readonly="readonly"></label>
                                <label><?php echo L('杠杆比例'); ?>：<input type='text' name='value' id='lvalue' readonly="readonly"></label>
                                <br>
                                <label><?php echo L('驳回原因'); ?>：
                                </label>
                                <input type='text' name='reason' id='reason' class="form-control">

  								
                            </div>
    		                <div class="modal-footer">
    		                    <button type="button" class="btn btn-white" id='closeoutlevearge3' data-dismiss="modal"><?php echo L('关闭'); ?></button>
    		                    <button type="button" class="btn btn-primary" style="background-color:red;border-color:red" id='saveaccountattr6' ><?php echo L('驳回'); ?></button>
    		                     <button type="button" class="btn btn-primary" id='saveaccountattr5' ><?php echo L('审核'); ?></button>
    		                </div>
    		            </form>
    	            </div>
    	        </div>
	        </div>
			<?php
			//}
			?>
           <!--管理员查看客户申请修改杠杆-->



			<?php
            if(chk_in_access('MT账号移仓')){
			?>
            <div class="modal inmodal" id="forwordmanger" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('MT账号移仓');?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                       <form  id='forwordmangerform' name='forwordmanger'>
                            <div class="modal-body">
                                 <label><?php echo L('MT帐号'); ?>：<span id="forwordid"></span></label>
                                <input type='hidden' name='id'>
                                 <div class="form-group"><label>MT<?php echo L('服务器'); ?>：</label> 
	                            <select name='mtserver' class='form-control m-b'>
                                        <option value='<?php echo $vo['id']; ?>'><?php echo L("请选择"); ?></option>
                                    <?php
                                    foreach($data as $key=>$vo){
									?>
	                                     <option value='<?php echo $vo['id']; ?>'><?php echo $vo['mt4_name']; ?>
                                         <?php
                                         if($vo['real'] == '0'){
											 echo '（' , L('模拟仓') , '）';
										 }else{
											 echo '（' , L('真实仓') , '）';
										 }
										 ?>
	                                     </option>
	                                <?php
									}
									?>
	                            </select>
                                     
                                 <label><?php echo L('转移的资料'); ?>：</label>
                                <div class="input-group m-b">
                                      <div class="checkbox radio-inline">
                                          <input type="checkbox" id="inlinecheckbox5" value="3" disabled="disabled" name="userinfo" checked >
                                        <label for="inlinecheckbox5"> <?php echo L('会员信息'); ?></label>
                                    </div>
                                    <div class="checkbox radio-info radio-inline">
                                        <input type="checkbox" id="inlinecheckbox3" value="1"  disabled="disabled"  name="userinfo" checked >
                                        <label for="inlinecheckbox3"> <?php echo L('出入金记录'); ?> </label>
                                    </div>
                                    <div class="checkbox radio-inline">
                                        <input type="checkbox" id="inlinecheckbox4" value="2"  disabled="disabled"  name="userinfo" checked  >
                                        <label for="inlinecheckbox4"> <?php echo L('开户记录'); ?></label>
                                    </div>
                                   
                                </div>  
	                        </div> 
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-white" id='closeout5' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                                <button type="button" class="btn btn-primary" id='forwordbtn' ><?php echo L('确认'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
			<?php
			}
			?>
                    
                    
                    
                    
                    
                    


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
        
        
        
        
        
<script type="text/javascript">
	$(document).on("click","#saveaccount",function(){
    //$("#saveaccount").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveAccount";

            $.post(url, form.serialize(), function(data) {
                layer.alert(data.info);
                if (data.status) {
      
                    document.settingform.reset();
                    $("#closeout").click();
                    document.location.reload();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
		
		$(document).on("click","#savepwd",function(){
          //$("#savepwd").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/editAccountPwd')}";

            $.post(url, form.serialize(), function(data) {
            	layer.msg(data.info);
                if (data.status) {
      
                    document.settingpwd.reset();
                    $("#closepwd").click();
                    document.location.reload();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
         //重置密码
		 $(document).on("click","#saveresetpwd",function(){
         //$("#saveresetpwd").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=resetAccoutPwd";

            $.post(url, form.serialize(), function(data) {
            	layer.msg(data.info);
                if (data.status) {
      
                    document.settingrestpwd.reset();
                    $("#closerestpwd").click();
                    //document.location.reload();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
			$(document).on("click","#saveaccountattr",function(){
          //$("#saveaccountattr").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveLogininfo_leverage";
            $.post(url, form.serialize(), function(data) {
            	$("#closeoutlevearge").click();
            	layer.msg(data.info);
                if (data.status) {
                    document.settingformattr.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

			$(document).on("click","#saveaccountattr2",function(){
          //$("#saveaccountattr2").click(function() {
            $(this).attr('disabled', "disabled");
			var mt4group = $("#mt4group").val();
            var id = $("#attrid").val();
            var url = "?clause=saveLogininfo_group";

            $.post(url, {mt4group:mt4group,id:id}, function(data) {
            	$("#closeoutgroup").click();
                layer.msg(data.info);
                if (data.status) {
                    document.settingformattr2.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click",".forwordto",function(){
        //$(".forwordto").click(function(){
            $("#forwordmangerform input[name='id']").val($(this).attr('rel'));
             $("#forwordid").text($(this).attr('lang'));
            
            $('#forwordmanger').modal('toggle'); 
        });
		$(document).on("click","#forwordbtn",function(){
        //$("#forwordbtn").click(function(){
//           / $(this).attr('disabled', "disabled");
            _this=$(this);
            var form=$(this).closest("form");
            var url = "?clause=forwordmanager";
           
            $.post(url, form.serialize(), function(data) {
  
                layer.msg(data.info);
                if (data.status) {
                     setTimeout(function(){
                          document.location.reload();
                     },800);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click","#saveaccountattr3",function(){
          //$("#saveaccountattr3").click(function() {
            $(this).attr('disabled', "disabled");
			var readonly = $("input[name='readonly']:checked").val();
			var disable = $("input[name='disable']:checked").val();
            var id = $("#attrid").val();
            var url = "?clause=saveLogininfo_disable";

            $.post(url, {readonly:readonly,disable:disable,id:id}, function(data) {
            	 $("#closeout2").click();
                layer.msg(data.info);
                if (data.status) {
                    document.settingformattr3.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });


     
		$(document).on("click","#saveaccountattr4",function(){
        //$("#saveaccountattr4").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/selfEditLeverage')}";
            $.post(url, form.serialize(), function(data) {
            	$("#closeoutlevearge2").click();
            	layer.msg(data.info);
                if (data.status) {
                    document.settingformattr4.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });



		$(document).on("click","#saveaccountattr5",function(){
        //$("#saveaccountattr5").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/doAuditLeverage')}";
            $.post(url, form.serialize(), function(data) {
            	$("#closeoutlevearge3").click();
            	layer.msg(data.info);
                if (data.status) {
                    document.settingformattr5.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });


		$(document).on("click","#saveaccountattr6",function(){
        //$("#saveaccountattr6").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/doAuditLeverage?is_b=1')}";
            $.post(url, form.serialize(), function(data) {
            	$("#closeoutlevearge3").click();
            	layer.msg(data.info);
                if (data.status) {
                    document.settingformattr5.reset();
                    document.commentForm.submit();
                }
                _this.removeAttr("disabled");
            }, 'json')
        });



		$(document).on("click",".addaccount",function(){
        //$(".addaccount").click(function() {
            document.settingform.reset();
            $('#myModal').modal('toggle'); 
        });
		$(document).on("click",".modifyattr",function(){
        //$(".modifyattr").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=viewAccount";
	        var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
                console.log(data);
               $("#settingformattr").removeData();
                if (data.status) {
                	$("#attrid").val(data.data.id);
                    $("#leveages").val(data.data.LEVERAGE);
                    $('#myModalattr').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".modifyattr2",function(){
        //$(".modifyattr2").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=viewAccount";
            var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
               $("#settingformattr").removeData();
                if (data.status) {
                    $("#attrid").val(data.data.id);
                    $("#mt4group").val(data.data.GROUP);
                    $('#myModalattr2').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".modifyattr3",function(){
        //$(".modifyattr3").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=viewAccount";
            var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
               $("#settingformattr").removeData();
                if (data.status) {
                    $("#attrid").val(data.data.id);
                    $(".readonly[value='"+data.data.readonly+"']").click();
                    $(".disable[value='"+data.data.disable+"']").click();
                    $('#myModalattr3').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".modifyattr4",function(){
        //$(".modifyattr4").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/selfLeverageView')}";
            var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
               $("#settingformattr4").removeData();
                if (data.status) {
                	$("#editloginid").val(data.data.loginid);
                    $(".readonly[value='"+data.data.readonly+"']").click();
                    $(".disable[value='"+data.data.disable+"']").click();
                    $('#myModalattr4').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

		$(document).on("click",".modifyattr5",function(){
        //$(".modifyattr5").click(function() {
            $(this).attr('disabled', "disabled");
            var _this=$(this);
            var form = $(this).closest('form');
            var url = "{:U('Member/auditLeverageView')}";
            var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
               $("#settingformattr5").removeData();
                if (data.status) {
                	$("#aid").val(data.data.id);
                	$("#lvalue").val(data.data.value);
                	$("#nickname").val(data.data.nickname);
                    $(".readonly[value='"+data.data.readonly+"']").click();
                    $(".disable[value='"+data.data.disable+"']").click();
                    $('#myModalattr5').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });

        //重置密码
		$(document).on("click",".modifyresetpwd",function(){
         //$(".modifyresetpwd").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
     

	     	var ID =  $(this).attr('rel');
           
               document.settingpwd.reset();
               $("#settingrestpwd").removeData();
               $("#resetidpwd").val(ID);;
    
               $('#myrestpassword').modal('toggle');
              
                _this.removeAttr("disabled");
        });

		$(document).on("click",".modifypassword",function(){
        //$(".modifypassword").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
     
            var form = $(this).closest('form');
            var url = "?clause=viewAccount";
	     var ID =  $(this).attr('rel');
           
               document.settingpwd.reset();
               $("#settingpwd").removeData();
              
                   $("#attridpwd").val(ID);;
                  
                   $('#myModalpassword').modal('toggle');
              
                _this.removeAttr("disabled");
            
        });
       
	   $(document).on("click",".modifyaccount",function(){
    	//$(".modifyaccount").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=viewAccount";
			var ID =  $(this).attr('rel');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#id").val(data.data.id);
                	$("#mtserver").val(data.data.mtserver);
                	$("#loginid").val(data.data.loginid);
                        $("#member_id").val(data.data.nickname);
                        $("#memberuserid").val(data.data.member_id);
                      
                    $("#password").val(data.data.password);
                    $("#mt_type").val(data.data.mt_type);
                   	$('#myModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click",".delete",function(){
        // $('.delete').click(function () {
        	var ID =  $(this).attr('rel');
        	var name = $(this).attr('re');
        	var loginid = $(this).attr('lang');
		    swal({
		        title: "<?php echo L('您确定要删除这条信息吗'); ?>",
		        text: "<?php echo L('客户昵称'); ?>:"+name+' '+"<?php echo L('MT帐号'); ?>:"+loginid+' '+"<?php echo L('删除后将无法恢复,请谨慎操作'); ?>! ",
		        showCancelButton: true,
		        confirmButtonColor: "#DD6B55",
		        confirmButtonText: "<?php echo L('删除'); ?>",
		        closeOnConfirm: false,   
		        showLoaderOnConfirm: true,
		    }, function () {
		    	var url = "?clause=deleteAccount";
		    	$.post(url, "id="+ID, function(data) {
	                if(data.status==0) {
	                	swal("<?php echo L('删除成功'); ?>！", "<?php echo L('您已经删除了这条信息'); ?>。", "success");
		        		 $("#tr_"+ID).remove();
	        		}else if(data.status==1){
	        			swal("<?php echo L('删除失败'); ?>！", "<?php echo L('删除出错'); ?>。", "warning");
	                	 _this.removeAttr("disabled");
	        		}
		    	}, 'json');
		    });
		});
        
         
     	
                /**
	 * 百度搜索 API 测试
	 */
	$("#member_id").bsSuggest({
		allowNoKeyword: false, //是否允许无关键字时请求数据。为 false 则无输入时不执行过滤请求
		multiWord: true, //以分隔符号分割的多关键字支持
		separator: ",", //多关键字支持时的分隔符，默认为空格
		getDataMethod: "url", //获取数据的方式，总是从 URL 获取
		idField: "id",
		keyField: "nickname",
		url: "member.php?clause=queryParentMemberByName&member_id=" + $("#mem_id").val() + "&parent_name=" ,
		/*优先从url ajax 请求 json 帮助数据，注意最后一个参数为关键字请求参数*/
	}).on('onDataRequestSuccess', function(e, result) {
		console.log('onDataRequestSuccess: ', result);
	}).on('onSetSelectValue', function(e, keyword, data) {
		$("#memberuserid").val(keyword.id);
		console.log('onSetSelectValue: ', keyword, data);
	}).on('onUnsetSelectValue', function() {
		console.log("onUnsetSelectValue");
	});   
</script>
        
        
        
        

    </body>
</html>
