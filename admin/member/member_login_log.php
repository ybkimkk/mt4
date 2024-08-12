<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchQ = FGetStr('searchNickname');
$SearchLogin = FGetStr('searchLogin');
$SearchIP = FGetStr('searchIP');
$SearchStatus = FGetStr('searchStatus');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('登录日志') , getCurrMt4ServerName();?></h4>
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
                                            	<input type="hidden" name="clause" value="member_login_log">
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label"><?php echo L('用户信息');?>：</label>
                                                    <input type="text" class="form-control" value="<?php echo $SearchQ;?>" name="searchNickname" placeholder="<?php echo L('用户英文名或邮箱');?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label">MT <?php echo L('账号');?>：</label>
                                                    <input type="text" class="form-control" value="<?php echo $SearchLogin;?>" name="searchLogin" placeholder="MT <?php echo L('账号');?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label">IP：</label>
                                                    <input type="text" class="form-control" value="<?php echo $SearchIP;?>" name="searchIP" placeholder="<?php echo L('登录');?> IP">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <select name='searchStatus' class='form-control'>
                                                        <option value=''><?php echo L('全部'); ?></option>
                                                        <option value='success'<?php if($SearchStatus == 'success'){echo ' selected';} ?>><?php echo L('登录成功日志'); ?></option>
                                                        <option value='fail'<?php if($SearchStatus == 'fail'){echo ' selected';} ?>><?php echo L('登录失败日志'); ?></option>
                                                     </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
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
                                                    <th class="no-sort"><?php echo L('用户信息');?></th>
                                                    <th class="no-sort"><?php echo L('联系方式');?></th>
                                                    <th class="no-sort"><?php echo L('IP');?></th>
                                                    <th class="no-sort"><?php echo L('日期');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where (`server_id` = '{$DRAdmin['server_id']}' or `server_id` = 0)";
	if(strlen($SearchQ) > 0){
		$where .= " and (member_id in (select id from `t_member` where `server_id` = '{$DRAdmin['server_id']}' and `status` = 1 and (`nickname` like '%" . $SearchQ . "%' or `phone` like '%" . $SearchQ . "%' or `email` like '%" . $SearchQ . "%' or `chineseName` like'%" . $SearchQ . "%')) or `login` like '%" . $SearchQ . "%')";
	}
	if (strlen($SearchLogin) > 0) {
		$member = $DB->getDRow("select id from `t_member` where `server_id` = '{$DRAdmin['server_id']}' and `status` = 1 and id = (select member_id from `t_member_mtlogin` where `status` = 1 and `loginid` = '{$SearchLogin}' LIMIT 1)");
		if ($member) {
			$where .= " and `member_id` = '{$member['id']}'";
		}else{
			$where .= " and `member_id` = '-1'";
		}
	}
	if (strlen($SearchIP) > 0) {
		$where .= " and `login_ip` = '{$SearchIP}'";
	}
	if($SearchStatus == 'success'){
		$where .= " and `status` = '1'";
	}else if($SearchStatus == 'fail'){
		$where .= " and `status` = '0'";
	}
	
	$recordCount = intval($DB->getField("select count(*) from `t_member_login` {$where}"));
	
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
	$query = $DB->query("select a.*,b.nickname,b.email,b.phone from (select * from `t_member_login` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join `t_member` b on a.member_id = b.id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>';
			if($rs['status'] == 1){
				echo $rs['nickname'];
			}else{
				echo $rs['login'];
			}
			echo '</td>';
			echo '<td>';
			if($rs['status'] == 1){
				echo '<span class="lookemail">' , hideStr($rs['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['email'] , '"></i>';
				if(strlen($rs['phone'])){
					echo '<br/><span class="lookphone">' , hideStr($rs['phone'],3,4) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" phone="' , $rs['phone'] , '"></i>';
				}
			}else{
				echo '-';
			}
			echo '</td>';
			echo '<td>' , $rs['login_ip'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
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

			
			init_findinfo();
        </script>

    </body>
</html>
