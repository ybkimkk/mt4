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
                                    <h4 class="page-title"><?php echo L('活动信息') , getCurrMt4ServerName();?></h4>
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
                                                    <th class="no-sort"><?php echo L('标题');?></th>
                                                    <th class="no-sort"><?php echo L('开始时间');?></th>
													<th class="no-sort"><?php echo L('结束时间');?></th>
													<th class="no-sort"><?php echo L('状态');?></th>
													<th class="no-sort"><?php echo L('报名');?></th>
													<th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$osLangArr = array();
	$query = $DB->query("select f_type,f_lang,f_title from t_lang_otherset where f_serverId = '{$DRAdmin['server_id']}' and f_type like '-_activity_-%' and f_lang = '{$CurrLangName}'");
	while($rs = $DB->fetchArray($query)){
		$osLangArr[$rs['f_type'].'-'.$rs['f_lang']] = $rs['f_title'];
	}


	$where = "where server_id = '{$DRAdmin['server_id']}' and `status` > 0 and start_time <= '" . date('Y-m-d H:i:s') . "'";
	$recordCount = intval($DB->getField("select count(*) from `t_activity_list` {$where}"));
	
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
	$query = $DB->query("select * from `t_activity_list` {$where} order by start_time desc,end_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		$joinArr = array();
		$query1 = $DB->query("select * from `t_activity_join` where f_uid = '" . $DRAdmin['id'] . "'");
		while($rs1 = $DB->fetchArray($query1)){
			$joinArr[$rs1['f_pid']] = $rs1;
		}


		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>';
			echo '<a href="?clause=showinfo&id=' , $rs['id'] , '">';
			echo $osLangArr['-_activity_-' . $rs['id'].'-'.$CurrLangName];
			echo '</a>';
			echo '</td>';
			echo '<td>' , date('Y-m-d H:i:s',strtotime($rs['start_time'])) , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',strtotime($rs['end_time'])) , '</td>';
			echo '<td>';
			if(time() <= strtotime($rs['start_time'])){
				echo '<font color="#ff0000">' . L('未开始') . '<font>';
			}else if(time() >= strtotime($rs['end_time'])){
				echo '<font color="#888888">' . L('已结束') . '<font>';
			}else{
				echo '<font color="green">' . L('进行中') . '<font>';
			}
			echo '</td>';
			echo '<td>';
			if($joinArr[$rs['id']]){
				switch($joinArr[$rs['id']]['f_status']){
					case '待审核':
						echo '<font color="#FF00AE">' . L($joinArr[$rs['id']]['f_status']) . '<font>';
						break;
					case '审核通过':
						echo '<font color="green">' . L($joinArr[$rs['id']]['f_status']) . '<font>';

						if($rs['f_cancelStatus'] == '待审核'){

						}else if($rs['f_cancelStatus'] == '审核通过'){

						}else if($rs['f_cancelStatus'] == '已拒绝'){
	
						}
						break;
					case '已拒绝':
						echo '<font color="#ff0000">' . L($joinArr[$rs['id']]['f_status']) . '<font>';
						break;
				}
			}else{
				echo '<font color="#888888">' . L('您未报名') . '<font>';
			}
			echo '</td>';
			echo '<td>';
			echo '<a class="btn btn-light btn-xs" type="button" href="?clause=showinfo&id=' , $rs['id'] , '">' , L('详情') , '</a> ';
			/*if(time() > strtotime($rs['start_time']) && time() < strtotime($rs['end_time'])){
				if($joinArr[$rs['id']]){
					switch($joinArr[$rs['id']]['f_status']){
						case '待审核':
						case '已拒绝':
							echo '<a class="btn btn-danger btn-red-cz btn-xs" type="button" href="?clause=unjoin&id=' , $rs['id'] , '" onclick="return confirm_qx()">' , L('取消报名') , '</a> ';
							break;
						case '审核通过':
							
							break;
					}
				}else{
					//echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=join&id=' , $rs['id'] , '" onclick="return confirm_bm()">' , L('报名') , '</a> ';
				}
			}else{
				echo '-';
			}*/
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
        
        <script>
			function confirm_bm(){
			  if(confirm("<?php echo L('您确定已经阅读并同意协议报名吗?'); ?>")){
				 return true;
				 }else{
				 return false;
			  }
		   }
		   function confirm_qx(){
			  if(confirm("<?php echo L('您确定要取消报名吗?'); ?>")){
				 return true;
				 }else{
				 return false;
			  }
		   }
        </script>

    </body>
</html>
