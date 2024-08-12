<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchTitle = FGetStr('searchTitle');
$SearchStatus = FGetStr('searchStatus');
$SearchSendtype = FGetStr('searchSendtype');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('信息模板') , getCurrMt4ServerName();?></h4>
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
                                                    <label class="control-label"><?php echo L('标题');?>：</label>
                                                   <input type="text" class="form-control" value="<?php echo $SearchTitle;?>" name="searchTitle">
                                               </div>
                                               <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('状态');?>：</label>
                                                    <select name="searchStatus" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <option value="1"<?php if($SearchStatus === '1'){echo ' selected="selected"';}?>><?php echo L('启用');?></option>
                                                        <option value="0"<?php if($SearchStatus === '0'){echo ' selected="selected"';}?>><?php echo L('禁用');?></option>
                                                     </select>
                                                 </div>
                                               <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('分类');?>：</label>
                                                    <select name="searchSendtype" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <option value="1"<?php if($SearchSendtype === '1'){echo ' selected="selected"';}?>><?php echo L('短信模板');?></option>
                                                        <option value="0"<?php if($SearchSendtype === '0'){echo ' selected="selected"';}?>><?php echo L('邮件模板');?></option>
                                                     </select>
                                                 </div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
                                                </div>
                                            </form>
                                        </div> <!-- end row -->

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="?clause=addinfo" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加信息');?></a>
                                            </div>
                                            <!--
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
                                                    <button type="button" class="btn btn-success mb-2 mr-1"><i class="mdi mdi-settings"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 mr-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div>-->
                                        </div>

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('编号');?></th>
                                                    <th class="no-sort"><?php echo L('名称');?></th>
                                                    <th class="no-sort"><?php echo L('标题');?></th>
                                                    <th class="no-sort"><?php echo L('类型');?></th>
                                                    <th class="no-sort"><?php echo L('排序');?></th>
                                                    <th class="no-sort"><?php echo L('创建时间');?></th>
                                                    <th class="no-sort"><?php echo L('更新时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$osLangArr = array();
	$query = $DB->query("select f_type,f_lang,f_title from t_lang_otherset where f_serverId = '0'");
	while($rs = $DB->fetchArray($query)){
		$osLangArr[$rs['f_type'].'-'.$rs['f_lang']] = $rs['f_title'];
	}
	

	$where = "where status >= 0";
	if(strlen($SearchTitle) > 0){
		$where .= " and `title` like '%" . $SearchTitle . "%'";
	}
	if(strlen($SearchStatus) > 0){
		$where .= " and `status` = '" . $SearchStatus . "'";
	}
	if(strlen($SearchSendtype) > 0){
		$where .= " and `sendtype` = '" . $SearchSendtype . "'";
	}
	
	$recordCount = intval($DB->getField("select count(*) from `t_mail_template` {$where}"));
	
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
	$query = $DB->query("select * from `t_mail_template` {$where} order by `name` asc,create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['id'] , '</td>';
			echo '<td>' , $rs['name'] , '</td>';
			echo '<td>';
			foreach($LangNameList['list'] as $keyL=>$valL){
				if($osLangArr[$rs['name'].'-'.$keyL]){
					echo '<b>（' , $valL['title'] , '）</b> ' , $osLangArr[$rs['name'].'-'.$keyL];
				}else{
					echo '<b>（' , $valL['title'] , '）</b> ' , '<span style="color:#ff0000">' , L('暂无设置') , '</span>';
				}
				echo '<br>';
			}
			echo '</td>';
			echo '<td>' , $rs['sendtype'] == 1 ? '<font color="green">' . L('短信模板') . '<font>' : '<font color="blue">' . L('邮件模板') . '<font>' , '</td>';
			echo '<td>' , $rs['sort'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['update_time']) , '</td>';
			echo '<td>' , $rs['status'] == 1 ? '<font color="green">' . L('启用') . '<font>' : '<font color="red">' . L('禁用') . '<font>' , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=addinfo&id=' , $rs['id'] , '">' , L('修改') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-xs delinfo" type="button" href="#nolink" url="?clause=delinfo&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
			echo '<a class="btn btn-light btn-xs" type="button" href="?clause=unpass&id=' , $rs['id'] , '">' , L('禁用') , '</a> ';
			echo '<a class="btn btn-info btn-xs" type="button" href="?clause=setpass&id=' , $rs['id'] , '">' , L('启用') , '</a> ';
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
		$(document).on("click",".delinfo",function(){
        //$('.delinfo').click(function () {
			var _this = $(this);
			layer.confirm('<?php echo L('您确定要删除这条信息吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				window.location.href = _this.attr('url');
			}, function(index){
				layer.close(index);
			});
		});
        </script>

    </body>
</html>
