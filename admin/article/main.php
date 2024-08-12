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
                                    <h4 class="page-title"><?php echo L('通知信息') , getCurrMt4ServerName();?></h4>
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
                                                    <th class="no-sort"><?php echo L('标题');?></th>
                                                    <th class="no-sort"><?php echo L('创建时间');?></th>
                                                    <!--
                                                    <th class="no-sort"><?php echo L('信息类型');?></th>
                                                    -->
                                                    <th class="no-sort"><?php echo L('排序');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$osLangArr = array();
	$query = $DB->query("select f_type,f_lang,f_title from t_lang_otherset where f_serverId = '{$DRAdmin['server_id']}' and f_type like '-_news_-%'");
	while($rs = $DB->fetchArray($query)){
		$osLangArr[$rs['f_type'].'-'.$rs['f_lang']] = $rs['f_title'];
	}



	$where = "where server_id = '{$DRAdmin['server_id']}' and `status` >= 0";
	//$where .= " and `status` = 1";
	$recordCount = intval($DB->getField("select count(*) from `t_article` {$where}"));
	
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
	$query = $DB->query("select * from `t_article` {$where} order by sort asc,create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			//echo '<td>' , $rs['id'] , '</td>';
			echo '<td>';
			if($rs['status'] == 1){
				echo '<a href="?clause=showinfo&id=' , $rs['id'] , '">';
			}
			foreach($LangNameList['list'] as $keyL=>$valL){
				if($osLangArr['-_news_-' . $rs['id'].'-'.$keyL]){
					echo '<b>（' , $valL['title'] , '）</b> ' , $osLangArr['-_news_-' . $rs['id'].'-'.$keyL];
				}else{
					echo '<b>（' , $valL['title'] , '）</b> ' , '<span style="color:#ff0000">' , L('暂无设置') , '</span>';
				}
				echo '<br>';
			}
			if($rs['status'] == 1){
				echo '</a>';
			}
			echo '</td>';

			echo '<td>';
			if(strlen($rs['f_key']) > 0){
				echo '<span style="color:#ff0000">' , $rs['f_key'] , '</span><br>';
			}
			echo date('Y-m-d H:i:s',$rs['create_time']);
			echo '</td>';
				/*echo '<td>';
				switch($rs['cid']){
					case 1:
						echo L('系统通知');
						break;
					case 2:
						echo L('滚动通知');
						break;
				}
				echo '</td>';*/
				echo '<td>' , $rs['sort'] , '</td>';
				echo '<td>' , $rs['status'] == 1 ? '<font color="green">' . L('启用') . '<font>' : '<font color="red">' . L('禁用') . '<font>' , '</td>';
				echo '<td>';
				echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=addinfo&id=' , $rs['id'] , '">' , L('修改') , '</a> ';
				echo '<a class="btn btn-danger btn-red-cz btn-xs" type="button" href="?clause=delinfo&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
				if($rs['status'] == 1){
					echo '<a class="btn btn-info btn-xs" type="button" href="?clause=forbid&id=' , $rs['id'] , '">' , L('禁用') , '</a> ';
				}else{
					echo '<a class="btn btn-light btn-xs" type="button" href="?clause=resume&id=' , $rs['id'] , '">' , L('启用') , '</a> ';
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
