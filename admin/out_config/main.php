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
                                    <h4 class="page-title"><?php echo L('提现设置') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="?clause=addinfo" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加信息');?></a>
                                            </div>
                                        </div>

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('名称');?></th>
                                                    <th class="no-sort"><?php echo L('商户号');?></th>
                                                    <th class="no-sort"><?php echo L('回调地址');?></th>
                                                    <th class="no-sort"><?php echo L('手续费');?></th>
                                                    <th class="no-sort"><?php echo L('币种');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where server_id = '{$DRAdmin['server_id']}'";
	
	$recordCount = intval($DB->getField("select count(*) from `t_out_config` {$where}"));
	
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
	$query = $DB->query("select a.*,b.f_title,b.f_pa from (select * from `t_out_config` {$where} order by sort desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join t_pay_currency b on a.f_currencyId = b.id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['Id'] , '</td>';
			echo '<td><a href="?clause=banklist&id=' , $rs['Id'] , '">' , $rs['PayCode'] , '</a></td>';
			echo '<td>' , $rs['PayName'] , strlen($rs['description']) ? ' - <font color="green">' . $rs['description'] . '</font>' : '' , '</td>';
			echo '<td>' , $rs['f_payIsOnline'] ? $rs['PayKey'] : '-' , '</td>';
			echo '<td>';
			if($rs['f_payIsOnline']){
				//echo L('通知url') , ': ' , $rs['NotifyUrl'] , '<br>';
				//echo L('系统返回url') , ': ' , $rs['ReturnUrl'];
				echo L('支付网关') , ': ' , $rs['submit_gateway'] , '<br>';
				echo L('通知url') , ': ' , FIsHttps() ? 'https://' : 'http://' , $_SERVER['HTTP_HOST'] , '/out_s2s_' , $rs['PayCode'] , '.html<br>';
				//echo L('系统返回url') , ': ' , FIsHttps() ? 'https://' : 'http://' , $_SERVER['HTTP_HOST'] , '/out_back_' , $rs['PayCode'] , '.html';
			}else{
				echo '-';
			}
			echo '</td>';
			echo '<td>' , $rs['f_fee'] * 100 , '%</td>';
			echo '<td>' , $rs['f_title'] , '(' , $rs['f_pa'] , ')' , '</td>';
			echo '<td>' , $rs['Status'] == 1 ? '<font color="green">' . L('启用') . '</font>' : '<font color="red">' . L('禁用') . '</font>' , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs modifypay" type="button" href="?clause=addinfo&id=' , $rs['Id'] , '">' , L('修改') , '</a> ';
			if($rs['Status'] == 1){
				echo '<a class="btn btn-danger btn-xs forbidenreopen" type="button" href="?clause=forbid&id=' , $rs['Id'] , '">' , L('禁用') , '</a>';
			}else{
				echo '<a class="btn btn-primary btn-xs forbidenreopen" type="button" href="?clause=resume&id=' , $rs['Id'] , '">' , L('启用') , '</a>';
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
