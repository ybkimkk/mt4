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
                                    <h4 class="page-title"><?php echo L('入金记录') , getCurrMt4ServerName();?></h4>
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
                                                <a href="?clause=addinfo" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('入金申请');?></a>
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
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('订单号');?></th>
                                                    <th class="no-sort"><?php echo L('金额');?></th>
                                                    <th class="no-sort"><?php echo L('支付方式');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('付款状态');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('备注');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where member_id = '{$DRAdmin['id']}' and server_id = '{$DRAdmin['server_id']}'";
	$recordCount = intval($DB->getField("select count(*) from `t_inmoney` {$where}"));
	
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
	$query = $DB->query("select a.*,b.nickname,b.phone,b.email from (select * from `t_inmoney` {$where} order by create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join `t_member` b on a.member_id = b.id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['mtid'] , '</td>';
			echo '<td>' , $rs['payno'] , '</td>';
			echo '<td>' , '$' . $rs['number'] . '<br>' . $rs['f_currencyPa'] . $rs['price'] , '</td>';
			echo '<td>' , getpaytype($rs['type']) , '</td>';
			echo '<td>' , getinstatus($rs['status']) , '</td>';
			echo '<td>' , getpaystatus($rs['paystatus']) , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>';
			if($rs['inid'] == 0){
				if(strlen($rs['content'])){
					echo L($rs['content']);
					echo '<br/>';
				}
			}else{
				echo L('入金记录ID') . ':' . $rs['inid'] . '<br>' . L('备注') . ':' . L($rs['content']);
				echo '<br/>';
			}
			if(strlen(trim($rs['certificate'])) > 0){
				$arr_ = getattach($rs["certificate"]);
				foreach($arr_ as $key=>$val){
					echo '<a href="' . $val . '" class="fancybox"><img src="' . $val . '" style="width:50px;"></a><br>';
				}
			}
			if(strlen(trim($rs['serialno'])) > 0){
				echo $rs['serialno'];
			}
			echo '</td>';
			echo '<td>';
			echo '<button type="button" inid="' . $rs['id'] . '" data-toggle="modal"  data-target="#inmyModal" class="btn btn-light btn-sm">' . L('上传汇款凭证') . '</button> &nbsp;';
			echo '<a href="#nolink" onclick="showinfo(\'?clause=showinfo&id=' . $rs['id'] . '\')" class="btn btn-info btn-sm">' . L('查看详情') . '</a>';
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

                        
                        
                        
                        
                        
<div class="modal fade show" id="inmyModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo L('上传汇款凭证');?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="inmoneyform" name="inmoneyform">
      <div class="modal-body">
          <div class="form-group">
            <button type="button" id="btnUp" class="btn btn-info btn-black-cz"><?php echo L('选择图片');?></button>
            <div id="imglist"></div>
            <input type="hidden" id="certificate" name="certificate">
            <input type="hidden" id="inid" name="inid">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeout1"><?php echo L('关闭');?></button>
        <button type="button" class="btn btn-primary" id="inmoney"><?php echo L('确认');?></button>
      </div>
      </form>
    </div>
  </div>
</div>    


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
        
        <script src="/assets/js/ajaxupload.3.5.js?time=<?php echo time();?>"></script>
        <script src="/assets/js/layer/layer.js"></script>
		<script src="/assets/js/fancybox/jquery.fancybox.js"></script>
        <link href="/assets/js/fancybox/jquery.fancybox.css" rel="stylesheet">

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
			
			
				$(".fancybox").fancybox({openEffect: "none", closeEffect: "none"});
			
			
				setModalMiddle('#inmyModal');
				$('#inmyModal').on('show.bs.modal', function (event) {
					var cTarget = $(event.relatedTarget);
					var inid = cTarget.attr('inid');
					$('#inid').val(inid);
				});
				
				
				$(document).on("click","#inmoney",function(){
				//$("#inmoney").click(function() {
					$(this).attr('disabled', "disabled");
					var _this = $(this);
					var form = $(this).closest('form');
					var url = "?clause=upcard";
		
					$.post(url, form.serialize(), function(data) {
						layer.msg(data.info);
						if (data.status) {
							document.inmoneyform.reset();
							$("#closeout1").click();
							setTimeout(function(){window.location.reload();},700);
						}
						_this.removeAttr("disabled");
					}, 'json')
				});
				
				
				
				
				var image=Array();
				var button = $('#btnUp'), interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					text:"<?php echo L('选择图片');?>",
					onSubmit: function (file, ext) {   
						if (!(ext && /^(jpg|jpeg|png|PNG|JPG|JPEG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function () {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function (file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);console.log(info);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;
			
							image.push(info.data[0].id)
				  
							button.text('<?php echo L('上传完成');?>');
							$('<img style="width:100px;hight:100px;border:1px solid #cccccc;margin:5px;">').appendTo($('#imglist')).attr("src", savepath+savename);
							$("#certificate").val(image.join(','));
							
							layer.msg(info.info);
						}
					}
				});


			});
			
			function showinfo(url){
			   layer.open({
					type: 2,
					title: '<?php echo L('查看入金详情');?>',
					shadeClose: true,
					shade: 0.8,
					area: ['96%', '96%'],
					content: url
				});
			}
        </script>

    </body>
</html>
