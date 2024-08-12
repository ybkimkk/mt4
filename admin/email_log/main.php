<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchSendType = FGetInt('searchSendType');
$SearchUserEmail = FGetStr('searchUserEmail');
$SearchStatus = FGetStr('searchStatus');
$SearchTempName = FGetStr('searchTempName');
$SearchSTime = FGetStr('searchSTime');
$SearchETime = FGetStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}

$module_node = $DB->getField2Arr("select a.name,b.f_title as title from (select `name` from `t_mail_template` where `sendtype` = '{$SearchSendType}') a left join t_lang_otherset b on b.f_type = a.name where b.f_lang = '{$CurrLangName}'");
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
.form-horizontal .form-row{ margin-bottom:8px;}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php
									if ($SearchSendType == 1) {
										echo L('短信发送日志');
									}else{
										echo L('邮件发送日志');
									}
									echo getCurrMt4ServerName();
									?></h4>
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
                                                    <label class="control-label"><?php echo L('信息类型');?>：</label>
                                                    <select name="searchSendType" class="form-control">
                                                        <option value="0"<?php if($SearchSendType <= 0){echo ' selected="selected"';}?>><?php echo L('邮件');?></option>
                                                        <option value="1"<?php if($SearchSendType > 0){echo ' selected="selected"';}?>><?php echo L('短信');?></option>
                                                     </select>
                                                 </div>
                                                 <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('收件人');?>：</label>
                                                   <input type="text" class="form-control" value="<?php echo $SearchUserEmail;?>" name="searchUserEmail">
                                               </div>
                                               <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('发送状态');?>：</label>
                                                    <select name="searchStatus" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <option value="1"<?php if($SearchStatus === '1'){echo ' selected="selected"';}?>><?php echo L('发送成功');?></option>
                                                        <option value="0"<?php if($SearchStatus === '0'){echo ' selected="selected"';}?>><?php echo L('发送失败');?></option>
                                                     </select>
                                                 </div>
                                                 <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('所属模板');?>：</label>
                                                    <select name="searchTempName" class="form-control">
                                                        <option value=""><?php echo L('全部');?> </option>
                                                        <?php
                                                        foreach($module_node as $key=>$val){
															echo '<option value="' , $key , '"';
															if($SearchTempName == $key){
																echo ' selected="selected"';
															}
															echo '>' , $val , '</option>';
														}
														?>     
                                                     </select>
                                                 </div>
                                                  <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('发送时间');?>：</label>
                                                    <span class="input-daterange input-group">
                                                          <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                            <div class="input-group-prepend">
                                                              <div class="input-group-text"><?php echo L('到');?></div>
                                                            </div>
                                                            <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </span>
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
                        <!-- end row-->


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
<?php
        if ($SearchSendType == 1) {
			echo '<thead>
					<tr>
						<th class="no-sort">ID</th>
						<th class="no-sort">' , L('手机号') , '</th>
						<th class="no-sort">' , L('所属模板') , '</th>
						<th class="no-sort">' , L('内容') , '</th>
						<th class="no-sort">' , L('发送时间') , '</th>
						<th class="no-sort">' , L('发送状态') , '</th>
					</tr>
				</thead>
				<tbody>';
			/*if ($this->_request("act") == 'content') {
                $content = M("msg_log")->where(array('id' => $this->_request('id')))->getField('content');
                echo $content;
                exit;
            }*/
			$where = "where 1 = 1";
            if ($SearchUserEmail) {
				$where .= " and phone = '{$SearchUserEmail}'";
            }
            if ($SearchTempName) {
				$where .= " and `template` = '{$SearchTempName}'";
            }
            if ($SearchStatus === '1' || $SearchStatus === '0') {
				$where .= " and `status` = '{$SearchStatus}'";
            }
			if (strlen($SearchSTime)) {
				$where .= " and create_time >= '" . strtotime($SearchSTime . ' 00:00:00') . "'";
			}
			if (strlen($SearchETime)) {
				$where .= " and create_time <= '" . strtotime($SearchETime . ' 23:59:59') . "'";
			}

			$recordCount = intval($DB->getField("select count(*) from `t_msg_log` {$where}"));
			
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
			$query = $DB->query("select * from `t_msg_log` {$where} order by create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
			if($DB->numRows($query) <= 0){
				echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
			}else{
				while($rs = $DB->fetchArray($query)){
					echo '<tr>';
					echo '<td>' , $rs['id'] , '</td>';
					echo '<td>' , $rs['phone'] , '</td>';
					echo '<td>' , $module_node[$rs['template']] , '</td>';
					echo '<td><a href="#nolink" class="btn btn-link" onclick="lookinfo_sms(' , $rs['id'] , ')">' , L('点击查看') , '</a></td>';
					echo '<td>' , date("Y-m-d H:i:s",$rs['create_time']) , '</td>';
					echo '<td>' , $rs['status'] == 0 ? L('发送失败') : L('发送成功') , '</td>';
					echo '</tr>';
				}
			}			
        } else {
            /*if ($this->_request("act") == 'content') {
                $content = M("mail_log")->where(array('id' => $this->_request('id')))->getField('contents');
                echo $content;
                exit;
            }*/
			
			echo '<thead>
					<tr>
						<th class="no-sort">ID</th>
						<th class="no-sort">' , L('发件邮件') , '</th>
						<th class="no-sort">' , L('收件人') , '</th>
						<th class="no-sort">' , L('标题') , '</th>
						<th class="no-sort">' , L('所属模板') , '</th>
						<th class="no-sort">' , L('内容') , '</th>
						<th class="no-sort">' , L('发送时间') , '</th>
						<th class="no-sort">' , L('发送状态') , '</th>
						<th class="no-sort">' , L('错误信息') , '</th>
					</tr>
				</thead>
				<tbody>';

			$where = "where 1 = 1";
            if ($SearchUserEmail) {
				$where .= " and user_email = '{$SearchUserEmail}'";
            }
            if ($SearchTempName) {
				$where .= " and `temp_name` = '{$SearchTempName}'";
            }
            if ($SearchStatus === '1' || $SearchStatus === '0') {
				$where .= " and `status` = '{$SearchStatus}'";
            }
			if (strlen($SearchSTime)) {
				$where .= " and create_time >= '" . strtotime($SearchSTime . ' 00:00:00') . "'";
			}
			if (strlen($SearchETime)) {
				$where .= " and create_time <= '" . strtotime($SearchETime . ' 23:59:59') . "'";
			}

			$recordCount = intval($DB->getField("select count(*) from `t_mail_log` {$where}"));
			
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
			$query = $DB->query("select * from `t_mail_log` {$where} order by create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
			if($DB->numRows($query) <= 0){
				echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
			}else{
				while($rs = $DB->fetchArray($query)){
					echo '<tr>';
					echo '<td>' , $rs['id'] , '</td>';
					echo '<td>' , $rs['sendmail'] , '</td>';
					echo '<td>' , $rs['user_email'] , '</td>';
					echo '<td>' , $rs['title'] , '</td>';
					echo '<td>' , $module_node[$rs['temp_name']] , '</td>';
					echo '<td><a href="#nolink" class="btn btn-link" onclick="lookinfo_email(' , $rs['id'] , ')">' , L('点击查看') , '</a></td>';
					echo '<td>' , date("Y-m-d H:i:s",$rs['create_time']) , '</td>';
					//echo '<td>' , $rs['status'] == 0 ? L('发送失败') . '<br><a href="#nolink" onclick="sendinfo_email(' . $rs['id'] . ')">' . L('重新发送') . '</a>' : L('发送成功') , '</td>';
					echo '<td>' , $rs['status'] == 0 ? L('发送失败') : L('发送成功') , '</td>';
					echo '<td>' , $rs['error_info'] , '</td>';
					echo '</tr>';
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
			
			function lookinfo_sms(id){
			   layer.open({
					type: 2,
					title: '<?php echo L('查看短信内容');?>',
					skin:'layer-ext-moon',
					shadeClose: true,
					shade: 0.8,
					area: ['96%', '96%'],
					content: '?clause=smsinfo&id='+id
				}); 
			}
			
			function lookinfo_email(id){
			   layer.open({
					type: 2,
					title: '<?php echo L('查看邮件信息');?>',
					skin:'layer-ext-moon',
					shadeClose: true,
					shade: 0.8,
					area: ['96%', '96%'],
					content: '?clause=emailinfo&id='+id
				}); 
			}
	
			function sendinfo_email(id){
				$.ajax({
					type:"post",
					url:"?clause=sendEmail",
					data:{id:id},
					dataType:"json",
					success:function(data){						
						if(data.code==0){
							alert(data.msg);
						}else if(data.code==1){
							alert('<?php echo L('发送成功');?>');
						}else{
							alert('<?php echo L('发送失败');?>');
						}
						setTimeout(function(){
							document.location.reload();
						},1500);
						console.log(data);
					},error:function(){
						alert('<?php echo L('网络异常');?>');
					}
				})
			}
        </script>

    </body>
</html>
