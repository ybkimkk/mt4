<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_positionsModel.class.php');

//-----------------------------------------

$username = urldecode(_request('nickname'));
$member_id = _request('member_id');
$comm_type = _request('comm_type');
$email = _request('email');
//输入账号条件
$memberid = "";
if ($member_id) {
	$memberid = $DB->getDRow("select id,email from t_member where id = '{$member_id}'");
	if (!$memberid) {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号不存在"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}

	if($DRAdmin['_dataRange'] <= 0){
		if($memberid['id'] != $DRAdmin['id']){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("该客户不是您的下级"),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}else if($DRAdmin['_dataRange'] <= 1){
		$parentid = $DRAdmin['id'];
		_check_member_scope($parentid, $memberid['id']);
	}
	$where['balance.MEMBER_ID'] = $memberid['id'];
	$whereO['other.f_uid'] = $memberid['id'];
}
if ($email) {
	$memberid = $DB->getDRow("select id,email from t_member where email = '{$email}' and status = 1");
	if (!$memberid) {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号不存在"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}

	if($DRAdmin['_dataRange'] <= 0){
		if($memberid['id'] != $DRAdmin['id']){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("该客户不是您的下级"),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}else if($DRAdmin['_dataRange'] <= 1){
		$parentid = $DRAdmin['id'];
		_check_member_scope($parentid, $memberid['id']);
	}
	$where['balance.MEMBER_ID'] = $memberid['id'];
	$whereO['other.f_uid'] = $memberid['id'];
}
if ($username) {
	unset($where); //输入用户优先
	$memberid = $DB->getDRow("select id,email from t_member where nickname = '{$username}' and status = 1");
	if (!$memberid) {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号不存在"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}

	if($DRAdmin['_dataRange'] <= 0){
		if($memberid['id'] != $DRAdmin['id']){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("该客户不是您的下级"),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}else if($DRAdmin['_dataRange'] <= 1){
		$parentid = $DRAdmin['id'];
		_check_member_scope($parentid, $memberid['id']);
	}

	$where['balance.MEMBER_ID'] = $memberid['id'];
	$whereO['other.f_uid'] = $memberid['id'];
}
if (!$username && !$member_id && !$email) {
	if ($DRAdmin['_dataRange'] >= 2) {
		$parentid = "admin";
		$member_id_arr = getunderCustomerIds($parentid);
		$where['balance.MEMBER_ID'] = array('in', $member_id_arr);
		$whereO['other.f_uid'] = array('in', $member_id_arr);
	} else {
		$memberid['id'] = $DRAdmin['id'];
		$where['balance.MEMBER_ID'] = $DRAdmin['id'];
		$whereO['other.f_uid'] = $DRAdmin['id'];
	}
}

$where['balance.SERVER_ID'] = $DRAdmin['server_id'];
$whereO['other.f_serverId'] = $DRAdmin['server_id'];

$startday = _request('startday');
$endday = _request('endday');
if ($startday) {
	$starttime = strtotime($startday . ' 00:00:00');
	_request('startday', $startday);
}
if ($endday) {
	$endtime = strtotime($endday . ' 23:59:59');
	_request('endday', $endday);
}
//$where.=' and  balance.TYPE>-1 ';
if ($startday && $endday) {
	$where['balance.CREATE_TIME'] = array(array('EGT', $starttime), array('ELT', $endtime));
	$whereO['other.f_addTime'] = array(array('EGT', $starttime), array('ELT', $endtime));
} elseif ($startday) {
	$where['balance.CREATE_TIME'] = array('EGT', $starttime);
	$whereO['other.f_addTime'] = array('EGT', $starttime);
} elseif ($endday) {
	$where['balance.CREATE_TIME'] = array('ELT', $endtime);
	$whereO['other.f_addTime'] = array('ELT', $endtime);
}

if (_request('qtype') == '0') {  //总返佣
	$where['balance.TYPE'] = 0;
	$whereStr = cz_where_to_str($where);
	$balanceAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr}");
	
	$where2['COMM_TYPE'] = 1; //跟单返佣
	$whereAmount2 = array_merge($where, $where2);
	$where2Str = cz_where_to_str($whereAmount2);
	$balanceGenAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$where2Str}");

	$where3['_string'] = 'balance.COMM_TYPE is null'; // 点差返佣
	$whereAmount3 = array_merge($where, $where3);
	$where3Str = cz_where_to_str($whereAmount3);
	$balanceDianAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$where3Str}");
} else if (_request('qtype') == '1') { //已提现
	$where['balance.TYPE'] = 1;
	$where['_string'] = 'IN_ID is not null';
	
	$whereStr = cz_where_to_str($where);
	
	$balanceYiAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr}");
} else if (_request('qtype') == '2') { //提现失败
	$where['balance.TYPE'] = 1;
	$where['_string'] = 'IN_ID is null';
	
	$whereStr = cz_where_to_str($where);
	
	$balanceShiAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr}");
}

if ($comm_type) {
	$where['balance.TYPE'] = 0;
}
if ($comm_type == 1) {  //跟单返佣额
	$where['COMM_TYPE'] = 1;
	
	$whereStr = cz_where_to_str($where);
	
	$balanceGenAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr}");
} else if ($comm_type == 2) { //点差返佣额
	$where['_string'] = 'balance.COMM_TYPE is null';
	
	$whereStr = cz_where_to_str($where);
	
	$balanceDianAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr}");
}
if ($comm_type == '' && _request('qtype') == '') {
	$whereAmount = array_merge($where, array('balance.TYPE' => '0')); //总返佣
	$whereAmountStr = cz_where_to_str($whereAmount);
	$balanceAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereAmountStr}");

	$where2['COMM_TYPE'] = 1; //跟单返佣
	$whereAmount2 = array_merge($where, array('balance.TYPE' => '0'), $where2);
	$whereAmount2Str = cz_where_to_str($whereAmount2);
	$balanceGenAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereAmount2Str}");

	$where3['_string'] = 'balance.COMM_TYPE is null'; // 点差返佣
	$whereAmount3 = array_merge($where, array('balance.TYPE' => '0'), $where3);
	$whereAmount3Str = cz_where_to_str($whereAmount3);
	$balanceDianAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereAmount3Str}");

	$where1['_string'] = 'IN_ID is not null'; //已提现
	$whereAmount1 = array_merge($where, array('balance.TYPE' => '1'), $where1);
	$whereAmount1Str = cz_where_to_str($whereAmount1);
	$balanceYiAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereAmount1Str}");

	$where4['_string'] = 'IN_ID is null'; //提现失败
	$whereAmount4 = array_merge($where, array('balance.TYPE' => '1'), $where4);
	$whereAmount4Str = cz_where_to_str($whereAmount4);
	$balanceShiAmount = $DB->getField2Arr("select 'vtotal',sum(balance.AMOUNT) as AMOUNT from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereAmount4Str}");
}

$whereStr = cz_where_to_str($where);
$whereOStr = cz_where_to_str($whereO);
$count = intval($DB->getField("select count(*) as count1 from t_sale_commission_balance balance {$whereStr}"));

$page     = FGetInt('page');if($page <= 0){$page = 1;}
$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;

$sql = "select balance.*,  member.nickname nickname,member.realname realname,member.email email from t_sale_commission_balance balance inner join t_member member on member.id = balance.MEMBER_ID {$whereStr} order by CREATE_TIME desc {$pageSql}";
//$sql = "(select ID,0 as oid,TYPE,AMOUNT,REL_ID,CREATE_TIME,IN_TIME,IN_ID,MEMO,COMM_TYPE,LOGIN,MEMBER_ID from t_sale_commission_balance balance {$whereStr}) UNION ALL (select 0,id,0,f_cal,0,0,0,0,'',0,0,f_uid from t_sale_commission_other other {$whereOStr}) {$pageSql}"
//$sql = "select b.*,member.nickname nickname,member.realname realname,member.email email from ({$sql}) b left join t_member member on member.id = b.MEMBER_ID order by b.CREATE_TIME desc ";
$list = $DB->getDTable($sql);

$whereAmount5Str = cz_where_to_str($whereO);
$balanceOtherAmount = $DB->getField2Arr("select 'vtotal',sum(f_cal) as AMOUNT from t_sale_commission_other other {$whereAmount5Str}");
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('返佣') , '/' , L('提现') , getCurrMt4ServerName();?></h4>
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
                                                	<?php echo L('邮箱');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('email');?>" name="email" placeholder="<?php echo L('请输入邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('会员ID');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('member_id');?>" name="member_id" placeholder="">
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="startday" value="<?php echo $startday;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="endday" value="<?php echo $endday;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>   
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('类型');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <option value='0'><?php echo L('返佣');?></option>
                                                        <option value='1'><?php echo L('已提现');?></option>
                                                        <option value='2'><?php echo L('提现失败');?></option>                                           
                                                    </select>
                                                </div>                                             
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('返佣类型');?>：
                                                    <select name="comm_type" id="comm_type" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <option value='1'><?php echo L('跟单返佣');?></option>
                                                        <option value='2'><?php echo L('点差返佣');?></option>                                           
                                                    </select>
                                                </div>   
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <div>
                                                    	<button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索'); ?></button>
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
                                    
                                    
<div>
    <?php
	echo L('返佣汇总') , '：&nbsp;&nbsp; ' , L('点差返佣额') , '：$' , round($balanceDianAmount['vtotal'],2) , '&nbsp;&nbsp; ' , L('跟单返佣额') , '：$' , round($balanceGenAmount['vtotal'],2) , '&nbsp;&nbsp; ';

	$fyOther = round($balanceOtherAmount['vtotal'],2);
	echo '<a href="cvd_other.php?nickname=' , urlencode($memberid['email']) , '&close_time_start=&close_time_end=" style="text-decoration: underline;">';
	echo L('返佣(补)') , '：$' , $fyOther;
	echo '</a>';
	echo '&nbsp;&nbsp; ';

    if($_GET['comm_type']==1){
    	echo L('总返佣') , '： $' , round(floatval($balanceGenAmount['vtotal'])+floatval($fyOther),2);
    }else if ($_GET['comm_type']==2){
    	echo L('总返佣') , '： $' , round(floatval($balanceDianAmount['vtotal'])+floatval($fyOther),2);
    }else{
    	echo L('总返佣') , '：$' , round(floatval($balanceAmount['vtotal'])+floatval($fyOther),2);
    }
    echo '&nbsp;&nbsp;<br/><br/>';

    echo L('提现汇总') , '：&nbsp;&nbsp;' , L('已提现额') , '：$' , round($balanceYiAmount['vtotal'],2) , '&nbsp;&nbsp; ' , L('提现失败') , ' ：$' , round($balanceShiAmount['vtotal'],2);
	?>
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('账户');?></th>
                                                    <th class="no-sort"><?php echo L('类型');?></th>
                                                    <th class="no-sort"><?php echo L('金额');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('备注');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($count);
		
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
//print_r($list);exit;
		if(count($list) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			foreach($list as $key=>$rs){
				echo '<tr style="' , $rs['TYPE'] == '0' ? 'color: green;' : '' , '">';
				echo '<td>' , $rs['nickname'] , '</td>';
				echo '<td>';
				if($rs['TYPE'] == '0'){
					echo L('返佣');
				}else{
					echo L('提现');
				}
				echo '</td>';
				echo '<td>' , '<B>$</B>' , $rs['AMOUNT'] , '</td>';
				echo '<td>';
				if(!$rs['REL_ID']){
					echo date('Y-m-d H:i:s',$rs['CREATE_TIME']);
				}else{
					echo date('Y-m-d H:i:s',$rs['IN_TIME']);
				}
				echo '</td>';
				echo '<td>';
				if($rs['TYPE'] == '1' && $rs['IN_ID'] == ''){
					echo '<font color="red">' , L('提现至MT失败') , '</font>';
				}else{
					echo $rs['MEMO'];
				}
				echo '</td>';
				echo '<td>';
				if($rs['TYPE'] == '0'){
					echo '<a class="btn btn-primary btn-xs " type="button"';
					if($rs['COMM_TYPE'] == '1'){
						echo ' href="follow_follow_trades.php?loginid=' , $rs['LOGIN'] , '&starttime=' , date('Y-m-d',$rs['CREATE_TIME']) , '&endtime=' , date('Y-m-d',$rs['CREATE_TIME']) , '"';
					}else{
						echo ' href="commission_view_details.php?MEMBER_ID=' , $rs['MEMBER_ID'] , '&nickname=' , $rs['email'] , '&ID=' , $rs['ID'] , '"';
					}
					echo '>' , L('查看详情') , '</a>';
				}else{
					echo L('提现操作');
				}
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
        
    </body>
</html>
