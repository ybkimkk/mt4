<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchEmail = FGetStr('searchEmail');
$SearchLoginid = FRequestStr('searchLoginid');
$SearchStatus = FGetStr('searchStatus');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('待审核银行卡') , getCurrMt4ServerName();?></h4>
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
                                                    <label class="control-label"><?php echo L('邮箱');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchEmail;?>" name="searchEmail" placeholder="<?php echo L('请输入邮箱'); ?>">
                                                </div>
                                                 <div class="form-group mr-sm-2 mb-sm-2">
                                                    <label class="control-label"><?php echo L('MT账号'); ?>：</label>
                                                    <input type="text" class="form-control" minlength="2" value="<?php echo $SearchLoginid;?>" name="searchLoginid" id="searchLoginid" placeholder="<?php echo L('MT账号'); ?>">
                                                 </div>
												<div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('状态');?>：</label>
                                                    <div>
                                                        <select name="searchStatus" class="form-control">
                                                            <option value=''<?php if($SearchStatus === ''){echo ' selected="selected"';}?>><?php echo L('全部'); ?></option>
                                                            <option value='1'<?php if($SearchStatus == '1'){echo ' selected="selected"';}?>><?php echo L('已审核'); ?></option>
                                                            <option value='0'<?php if($SearchStatus === '0'){echo ' selected="selected"';}?>><?php echo L('未审核'); ?></option>
                                                            <option value='2'<?php if($SearchStatus === '2'){echo ' selected="selected"';}?>><?php echo L('拒绝'); ?></option>
                                                        </select>
                                                    </div>
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
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('邮箱');?></th>
                                                    <th class="no-sort"><?php echo L('银行卡号');?></th>
                                                    <th class="no-sort"><?php echo L('开户名');?></th>
                                                    <th class="no-sort"><?php echo L('开户行');?></th>
                                                    <th class="no-sort"><?php echo L('银行国际代码');?></th>
                                                    <th class="no-sort"><?php echo L('卡图片');?></th>
                                                    <th class="no-sort"><?php echo L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('处理时间');?></th>
                                                    <th class="no-sort"><?php echo L('备注');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php


	$where = "where server_id = '{$DRAdmin['server_id']}'";
	$where .= " and `status` <> 3";//已删除
	if ($SearchEmail) {
		$where .= " and `email` = '{$SearchEmail}'";
	}
	if ($SearchLoginid) {
		$memberlogin = $DB->getDRow("select member_id as id from `t_member_mtlogin` where `loginid` = '{$SearchLoginid}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}'");
		if ($memberlogin) {
			$where .= " and member_id = '{$memberlogin['id']}'";
		} else {
			$where .= " and member_id = '0'";
		}
	}
	if ($SearchStatus != '') {
		$where .= " and `status` = '{$SearchStatus}'";
	}

	$recordCount = intval($DB->getField("select count(*) from `t_bankcode` {$where}"));
	
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
	$query = $DB->query("select * from `t_bankcode` {$where} order by status,creattime desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){	
			if (!empty($rs['bankCard'])) {
				$attach = $DB->getDRow("select * from t_attach where id = '{$rs['bankCard']}'");
				$rs['imgpath'] = str_replace(".", "", $attach['savepath']) . $attach['savename'];
			}
				
			echo '<tr>';
			echo '<td>' , $rs['email'] , '</td>';
			echo '<td>' , $rs['accountNum'] , '</td>';
			echo '<td>' , $rs['accountName'] , '</td>';
			echo '<td>' , $rs['bankName'] , '</td>';
			echo '<td>' , $rs['swiftCode'] , '</td>';
			echo '<td>';
			if(strlen($rs['imgpath']) <= 0){
				echo '-';
			}else{
				echo '<a href="' , $rs['imgpath'] , '" class="fancybox"><img src="' , $rs['imgpath'] , '" style="width: 50px;height: 50px;"></a>';
			}
			echo '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['creattime']) , '</td>';
			echo '<td>';
			switch($rs['status']){
				case 0:
					echo '<span class="badge badge-default">' , L('未审核') , '</span>';
					break;
				case 1:
					echo '<span class="badge badge-success">' , L('已审核') , '</span>';
					break;
				default:
					echo '<span class="badge badge-warning">' , L('拒绝') , '</span>';
					break;
			}
			echo '</td>';
			echo '<td>' , strlen($rs['checktime']) > 0 ? date('Y-m-d H:i:s',$rs['checktime']) : '-' , '</td>';
			echo '<td>' , $rs['remark'] , '</td>';
			echo '<td>';
			if($rs['Status'] == 0){
				echo '<button type="button" val="' , $rs['id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-success btn-xs visitinmoney">' , L('审核') , '</button> ';
				echo '<!-- <a class="btn btn-primary btn-xs" type="button" href="?clause=viewcheck_bank&id=' , $rs['id'] , '" >' , L('审核') , '</a> -->';
				echo '<a class="btn btn-danger btn-xs refuse" type="button" href="#nolink" val="' , $rs['id'] , '">' , L('拒绝') , '</a>';
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
                    
  
  
  
  
  
        <!--弹出层-->
      <div class="modal inmodal" id="refuseModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog">
              <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('拒绝'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                   <form  id='refuseform' name='refuseform'>
                  <input type="hidden" name="sid" ID="sid" value="" />
                    <div class="modal-body">
                         <label><?php echo L('拒绝理由'); ?>：</label>
                      <div class="input-group m-b">
                            <input type="text" class="form-control" name="reply" id="reply"> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" id='closerefuse' data-dismiss="modal"><?php echo L('关闭'); ?></button>
                        <button type="button" class="btn btn-primary" id='refusemt4' ><?php echo L('确认'); ?></button>
                    </div>
                </form>
              </div>
          </div>
      </div>
      <!--弹出层-->
  
  
  
  
  
  



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
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"></link>
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
    $(".fancybox").fancybox({openEffect: "none", closeEffect: "none"});
	$(document).on("click","#refusemt4",function(){
    //$("#refusemt4").click(function() {
        $(this).attr('disabled', "disabled");
        var _this=$(this);
        var form = $(this).closest('form');
        var url = "?clause=refusemt4";

        $.post(url, form.serialize(), function(data) {
            layer.msg(data.info);
            if (data.status) {
                document.refuseform.reset();
                $("#closerefuse").click();
				setTimeout(function(){document.location.reload();},800);
            }
            _this.removeAttr("disabled");
        }, 'json')
    });
    
	$(document).on("click",".visitinmoney",function(){
  //$(".visitinmoney").click(function () {                          
      var id = $(this).attr('val');
      swal({
          title: "<?php echo L('银行卡审核'); ?>",
          text: "<?php echo L('请确认相关信息，确定无误后点击确认审核'); ?>",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "<?php echo L('确认审核'); ?>",
          closeOnConfirm: false,
          showLoaderOnConfirm: true,
      }, function () {
          var url = "?clause=viewcheck_bank";
          $.post(url, {id: id}, function (data) {
              if (data.status) {
                  swal("<?php echo L('审核成功'); ?>", data.info, "success");
                  setTimeout(function () {
                      document.location.reload()
                  }, 800);
              } else {
                  swal("<?php echo L('审核失败'); ?>", data.info, "warning");
              }
          }, 'json');
      });
  });

$(document).on("click",".refuse",function(){
  //$(".refuse").click(function() {
    $(this).attr('disabled', "disabled");
    var _this=$(this);
    document.refuseform.reset();
    var form = $(this).closest('form');
    var ID =  $(this).attr('val');
    $("#sid").val(ID);
    $('#refuseModal').modal('toggle');
  });
</script>
        
        
        
        
        

    </body>
</html>
