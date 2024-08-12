<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('我的赠金申请') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('赠金规则');?></th>
                                                    <th class="no-sort"><?php echo L('入金金额');?> ($)</th>
                                                    <th class="no-sort"><?php echo L('赠金金额');?> ($)</th>
                                                    <th class="no-sort"><?php echo L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('MT订单');?></th>
                                                    <th class="no-sort"><?php echo L('审核时间');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where MemberId = '" . $DRAdmin['id'] . "'";
	$where .= " and ServerId = '" . $DRAdmin['server_id'] . "'";

	$recordCount = intval($DB->getField("select count(*) from `t_credit_record` {$where}"));
	
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
	$query = $DB->query("select a.*,b.Name,b.Scale,b.Condition,b.Result SetResult,b.Type from (select * from `t_credit_record` {$where} order by CreateTime desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join t_credit_setting b on a.CreditId=b.Id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['MtLogin'] , '</td>';
			echo '<td>';
			if($rs['Type'] == 'BALANCE_FIRST'){
				echo '<span class="badge badge-info">' , L('首次入金') , '</span>';
			}else if($rs['Type'] == 'BALANCE_PER'){
				echo '<span class="badge badge-success">' , L('单笔入金') , '</span>';
			}else if($rs['Type'] == 'REG'){
				echo '<span class="badge badge-success">' , L('开户') , '</span>';
			}
			echo number_format($vo['Condition'],2,".",",") , ' USD';
			echo '(';
			echo $rs['SetResult'];
			if($rs['Scale'] == 'Scale'){
				echo '%';
			}else if($rs['Scale'] == 'Fixed'){
				echo 'USD';
			}
			echo ')';
			echo '</td>';
			echo '<td>' , $rs['InMoney'] , '</td>';
			echo '<td>' , $rs['Result'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['CreateTime']) , '</td>';
			echo '<td>';
			if($rs['Status'] == '0'){
				echo '<span class="badge badge-info">' , L('未处理') , '</span>';
			}else if($rs['Status'] == '1'){
				echo '<span class="badge badge-success">' , L('已处理') , '</span>';
			}else if($rs['Status'] == '-1'){
				echo '<span class="badge badge-success">' , L('已驳回') , '</span>';
			}
			echo '</td>';
			echo '<td>';
			if(strlen($rs['Ticket'])){
				echo 'Ticket: ' , $rs['Ticket'];
			}else{
				echo '<font color="red">' , $rs['Memo'] , '</font>';
			}
			echo '</td>';
			echo '<td>';
			if(strlen($rs['CheckTime'])){
				echo date('Y-m-d H:i:s',$rs['CheckTime']);
			}else{
				echo '-';
			}
			echo '</td>';
			echo '<td>';
			echo '<a href="?clause=show_credit&id=' , $rs['Id'] , '" class="btn btn-primary btn-xs">' , L('查看') , '</a>';
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
        
        
        
        

    </body>
</html>
