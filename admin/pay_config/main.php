<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

<style>
.radio-inline{ margin-right:15px;}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('支付设置') , getCurrMt4ServerName();?></h4>
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
                                                <a href="#nolink" id="addpay" data-target="#myModal" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加支付方式');?></a>
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
                                                    <th class="no-sort"><?php echo L('支付编号');?></th>
                                                    <th class="no-sort"><?php echo L('支付名称');?></th>
                                                    <th class="no-sort"><?php echo L('商户号');?></th>
                                                    <th class="no-sort"><?php echo L('回调地址');?></th>
                                                    <th class="no-sort"><?php echo L('币种');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where server_id = '{$DRAdmin['server_id']}'";
	
	$recordCount = intval($DB->getField("select count(*) from `t_pay` {$where}"));
	
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
	$query = $DB->query("select a.*,b.f_title,b.f_pa from (select * from `t_pay` {$where} order by sort desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join t_pay_currency b on a.f_currencyId = b.id");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['Id'] , '</td>';
			echo '<td><a href="?clause=banklist&id=' , $rs['Id'] , '">' , $rs['PayCode'] , '</a></td>';
			echo '<td>' , $rs['PayName'] , ' - <font color="green">' , $rs['description'] , '</font></td>';
			echo '<td>' , $rs['PayKey'] , '</td>';
			echo '<td>';
			//echo L('通知url') , ': ' , $rs['NotifyUrl'] , '<br>';
			//echo L('系统返回url') , ': ' , $rs['ReturnUrl'];
			if($rs['f_payFolder'] == 'selfu'){
				echo '-';
			}else{
				echo L('支付网关') , ': ' , $rs['submit_gateway'] , '<br>';
				echo L('通知url') , ': ' , FIsHttps() ? 'https://' : 'http://' , $_SERVER['HTTP_HOST'] , '/pay_s2s_' , strlen($rs['f_payFolder']) ? '<span style="color:#ff0000">' . $rs['f_payFolder'] . '</span>_' : '' , $rs['PayCode'] , '.html<br>';
				echo L('系统返回url') , ': ' , FIsHttps() ? 'https://' : 'http://' , $_SERVER['HTTP_HOST'] , '/pay_back_' , strlen($rs['f_payFolder']) ? '<span style="color:#ff0000">' . $rs['f_payFolder'] . '</span>_' : '' , $rs['PayCode'] , '.html';
			}
			echo '</td>';
			echo '<td>' , $rs['f_title'] , '(' , $rs['f_pa'] , ')' , '</td>';
			echo '<td>' , $rs['Status'] == 1 ? '<font color="green">' . L('启用') . '</font>' : '<font color="red">' . L('禁用') . '</font>' , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs modifypay" val="' , $rs['Id'] , '" type="button" data-toggle="modal" href="#onlink">' , L('修改') , '</a> ';
			if($rs['Status'] == 1){
				echo '<a class="btn btn-danger btn-xs forbidenreopen" rel="' , $rs['Id'] , '" type="button" href="#onlink">' , L('禁用') , '</a>';
			}else{
				echo '<a class="btn btn-primary btn-xs forbidenreopen" rel="' , $rs['Id'] , '" type="button" href="#onlink">' , L('启用') , '</a>';
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






         <!--新增弹出层-->
	    <div class="modal inmodal" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('添加支付设置'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='addform' name='addform'>
		                <div class="modal-body">
		                	<label><?php echo L('支付代码'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-code"></i></span></span>
		                        <input type="text" class="form-control"   name="PayCode">
		                    </div>
		                	<label><?php echo L('支付路径'); ?>：</label>
		                	<div class="input-group mb-2">
                            	<select name="f_payFolder" class="form-control">
                                	<option value="">-=<?php echo L('选择'); ?>=-</option>
									<?php
									//如果选择了支付文件夹，则支付时认该文件夹中的程序，PayCode则变成pkey唯一标识（定位使用哪条支付配置）；如果不选择文件夹，则支付时认PayCode文件夹、并且PayCode也是唯一标识
									//它本身仅用于定位要执行支付代码的文件夹，没有其它作用（包括发起支付时、回调时，定位要执行代码的文件夹）
									if(is_array($ConfigItem['PayConfig_PayFolder'])){
										foreach($ConfigItem['PayConfig_PayFolder'] as $key_=>$val_){
											echo '<option value="' , $val_ , '">' , $val_ , '</option>';
										}
									}
                                    ?>
                                </select>
		                    </div>
		                	<label><?php echo L('支付名称'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-navicon"></i></span></span>
		                        <input type="text" class="form-control"   name="PayName">
		                    </div>
	                    	<label><?php echo L('商户号'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></span>
		                        <input type="text" class="form-control" name="PayKey"> 
		                    </div>
                            <!--
	                     	<label><?php echo L('交易账号'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></span>
		                        <input type="text" class="form-control" name="PartnerId"> 
		                    </div>
                            -->
		                    <label><?php echo L('签名KEY'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user-secret"></i></span></span>
                                            <textarea class="form-control" name="PaySignKey" ></textarea>
		                    </div>
		                    <label><?php echo L('支付网关'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-suitcase"></i></span></span>
		                        <input type="text" class="form-control" name="submit_gateway"> 
		                    </div>
                            <!--
		                    <label><?php echo L('通知URL'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-suitcase"></i></span></span>
		                        <input type="text" class="form-control" name="NotifyUrl"> 
		                    </div>
		                    <label><?php echo L('系统返回URL'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="ReturnUrl"> 
		                    </div>
                            -->
		                    <label><?php echo L('支付描述'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="description" > 
		                    </div>
		                    <label><?php echo L('最大支付金额'); ?>(USD)：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="maxpaynumber" id=""> 
		                    </div>
                            <label><?php echo L('币种'); ?>：</label>
                            <div class="input-group mb-2">
                            	<select name="f_currencyId" class="form-control">
                                	<option value="">-=<?php echo L('选择'); ?>=-</option>
									<?php
									$symbols = $DB->getDTable("select * from t_pay_currency where f_status in (0,1) order by id desc");
                                    foreach($symbols as $key=>$val){
                                        echo '<option value="' , $val['id'] , '">' , $val['f_title'] , ' (' , $val['f_pa'] , ')</option>';
                                    }
                                    ?>
                                </select>
		                    </div>
                            <label><?php echo L('排序'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="sort"> 
		                    </div>
                            <label><?php echo L('发起支付时'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <div class="form-check-inline">
                                    <div class="radio radio-info radio-inline">
                                        <input id="f_fillMorePayInfo00" value="0" name="f_fillMorePayInfo" type="radio" checked="checked">
                                        <label for="f_fillMorePayInfo00"><?php echo L('无需填写更多支付数据'); ?></label>
                                    </div>
                                    <div class="radio radio-info radio-inline">
                                        <input id="f_fillMorePayInfo11" value="1" name="f_fillMorePayInfo" type="radio">
                                        <label for="f_fillMorePayInfo11"><?php echo L('需要'); ?></label>
                                    </div>
                                </div>
		                    </div>
                            <label><?php echo L('图片'); ?>1：</label>
		                	<div class="input-group mb-2" style="display:block;">
								<a id="up_pic1_1" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
								<div id="imglist1"></div>
								<input name="pic1" id="pic1_1" type="hidden" class="form-control" value="">
		                    </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='addpaybtn' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
   
     	<!--修改弹出层-->
	    <div class="modal inmodal" id="modifyModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('支付设置'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='modifyform' name='modifyform'>
		            	<input type="hidden" name="Id" id="Id" value="" />
		                <div class="modal-body">
		                	<label><?php echo L('支付通道'); ?>：</label>
		                	<div class="input-group mb-2">
		                	<b><p class="form-control-static"  id="showinfo" name="showinfo"></p></b>
		                    </div>
		                	<label><?php echo L('支付路径'); ?>：</label>
		                	<div class="input-group mb-2">
                            	<select name="f_payFolder" id="f_payFolder" class="form-control">
                                	<option value="">-=<?php echo L('选择'); ?>=-</option>
									<?php
									if(is_array($ConfigItem['PayConfig_PayFolder'])){
										foreach($ConfigItem['PayConfig_PayFolder'] as $key_=>$val_){
											echo '<option value="' , $val_ , '">' , $val_ , '</option>';
										}
									}
                                    ?>
                                </select>
		                    </div>
		                	<label><?php echo L('支付名称'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-dollar"></i></span></span>
		                        <input type="text" class="form-control"  name="PayName" id="PayName">
		                    </div>
		                    <label><?php echo L('商户号'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></span>
		                        <input type="text" class="form-control" name="PayKey" id="PayKey"> 
		                    </div>
                            <!--
	                     	<label><?php echo L('交易账号'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user"></i></span></span>
		                        <input type="text" class="form-control" name="PartnerId" id="PartnerId"> 
		                    </div>
                            -->
		                    <label><?php echo L('签名KEY'); ?>：</label>
		                	<div class="input-group mb-2"> <span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-user-secret"></i></span></span>
		                		<textarea class="form-control" name="PaySignKey" id="PaySignKey"></textarea>
		                    </div>
		                    <label><?php echo L('支付网关'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-suitcase"></i></span></span>
		                        <input type="text" class="form-control" name="submit_gateway" id="submit_gateway"> 
		                    </div>
                            <!--
		                    <label><?php echo L('通知URL'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-suitcase"></i></span></span>
		                        <input type="text" class="form-control" name="NotifyUrl" id="NotifyUrl"> 
		                    </div>
		                     <label><?php echo L('系统返回URL'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="ReturnUrl" id="ReturnUrl"> 
		                    </div>
                            -->
		                    <label><?php echo L('支付描述'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="description" id="description"> 
		                    </div>
		                    <label><?php echo L('最大支付金额'); ?>(USD)：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="maxpaynumber" id="maxpaynumber"> 
		                    </div>
                            <label><?php echo L('币种'); ?>：</label>
                            <div class="input-group mb-2">
                            	<select name="f_currencyId" id="f_currencyId" class="form-control">
                                	<option value="">-=<?php echo L('选择'); ?>=-</option>
									<?php
									$symbols = $DB->getDTable("select * from t_pay_currency where f_status in (0,1) order by id desc");
                                    foreach($symbols as $key=>$val){
                                        echo '<option value="' , $val['id'] , '">' , $val['f_title'] , ' (' , $val['f_pa'] , ')</option>';
                                    }
                                    ?>
                                </select>
		                    </div>
                            <label><?php echo L('排序'); ?>：</label>
		                	<div class="input-group mb-2"><span class="input-group-prepend"><span class="input-group-text"><i class="fa fa-backward"></i></span></span>
		                        <input type="text" class="form-control" name="sort" id="sort"> 
		                    </div>
                            <label><?php echo L('发起支付时'); ?>：</label>
		                	<div class="input-group mb-2">
		                        <div class="form-check-inline">
                                    <div class="radio radio-info radio-inline">
                                        <input id="f_fillMorePayInfo0" value="0" name="f_fillMorePayInfo" type="radio">
                                        <label for="f_fillMorePayInfo0"><?php echo L('无需填写更多支付数据'); ?></label>
                                    </div>
                                    <div class="radio radio-info radio-inline">
                                        <input id="f_fillMorePayInfo1" value="1" name="f_fillMorePayInfo" type="radio">
                                        <label for="f_fillMorePayInfo1"><?php echo L('需要'); ?></label>
                                    </div>
                                </div>
		                    </div>
                            <label><?php echo L('图片'); ?>1：</label>
		                	<div class="input-group mb-2" style="display:block;">
								<a id="up_pic1_2" href="#nolink" class="btn btn-warning btn-black-cz btn-sm"><?php echo L('选择图片'); ?></a>
								<div id="imglist2"></div>
								<input name="pic1" id="pic1_2" type="hidden" class="form-control" value="">
		                    </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closeoutmodify' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='savepay' ><?php echo L('确认'); ?></button>
		                </div>
		            </form>
	            </div>
	        </div>
	    </div>
	    <!--弹出层-->
	    
	    
	    <!--弹出层-->
	    <div class="modal inmodal" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('是否确认禁用'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
		                <div class="modal-body">
		                 <?php echo L('确定要禁用改记录吗'); ?>？
		                </div>
		                <div class="modal-footer">
		                    <input type='hidden' name='inlogin' value=''/>
		                    <button type="button" class="btn btn-white closeout" data-dismiss="modal"><?php echo L('取消'); ?></button>
		                    <button type="button" class="btn btn-primary"><?php echo L('确认'); ?></button>
		                </div>
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

        <script src="/assets/js/ajaxupload.3.5.js"></script>
        
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

			$(function() {
				var button = $('#up_pic1_1'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|JPG|JPEG|PNG|png)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L("上传完成");?>');
							$('#imglist1').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#pic1_1").val(savepath + savename);
							
							//alert(info.info);
						}
					}
				});

			});





			$(function() {
				var button = $('#up_pic1_2'),
					interval,intervalCi = 0;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|JPG|JPEG|PNG|png)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择jpg,png格式的文件');?>!', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							intervalCi++;
							if(intervalCi <= 3){
								button.text(button.text() + '......'.substr(0,intervalCi));
							} else {
								intervalCi = 0;
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
						window.clearInterval(interval);
						this.enable();
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L("上传完成");?>');
							$('#imglist2').html('<a href="' + (savepath + savename) + '" target="_blank"><img src="' + (savepath + savename) + '" style="width:120px;hight:90px;"></a>');
							$("#pic1_2").val(savepath + savename);
							
							//alert(info.info);
						}
					}
				});

			});





		$(document).on("click","#savepay",function(){
    	 //$("#savepay").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.modifyform.reset();
                    $("#closeoutmodify").click();
                    setTimeout(function(){
                         document.location.reload();
                    },1000);
                   
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click",".closeout",function(){
      	//$(".closeout").click(function(){
             $("#addModal").hide();
        });

		$(document).on("click","#addpay",function(){
       	//$("#addpay").click(function() {
            document.addform.reset();
           	$('#addModal').modal('toggle'); 
        });
        
		$(document).on("click","#addpaybtn",function(){
    	//$("#addpaybtn").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=saveinfo";

            $.post(url, form.serialize(), function(data) {
                layer.msg(data.info);
                if (data.status) {
                    document.addform.reset();
                    $(".closeout").click();
                    setTimeout(function(){
                         document.location.reload();
                    },1000);
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
        
        $(document).on("click",".modifypay",function(){
    	//$(".modifypay").click(function() {
            $(this).attr('disabled', "disabled");
          	var _this=$(this);
            var form = $(this).closest('form');
            var url = "?clause=getinfo";
			var ID =  $(this).attr('val');
            $.post(url, "id="+ID, function(data) {
                if (data.status) {
                	$("#Id").val(data.data.Id);
                	$("#PayName").val(data.data.PayName);
					$("#f_payFolder").val(data.data.f_payFolder);
                	$("#showinfo").text(data.data.description+"("+data.data.PayCode+")");
                	//$("#PartnerId").val(data.data.PartnerId);
					$("#PayKey").val(data.data.PayKey);
                	$("#PaySignKey").val(data.data.PaySignKey);
                	$("#NotifyUrl").val(data.data.NotifyUrl);
					$("#description").val(data.data.description);
					$("#sort").val(data.data.sort);
					$("#ReturnUrl").val(data.data.ReturnUrl);
					$("#maxpaynumber").val(data.data.maxpaynumber);
					$("#f_fillMorePayInfo" + data.data.f_fillMorePayInfo).click();
					$("#submit_gateway").val(data.data.submit_gateway);
					
					$("#f_currencyId").val(data.data.f_currencyId);
					
					if(data.data.f_pic1){
						$('#imglist2').html('<a href="' + data.data.f_pic1 + '" target="_blank"><img src="' + data.data.f_pic1 + '" style="width:120px;hight:90px;"></a>');
						$("#pic1_2").val(data.data.f_pic1);
					}
                        
                   	$('#modifyModal').modal('toggle');
                }
                _this.removeAttr("disabled");
            }, 'json')
        });
        
		$(document).on("click","#closeoutmodify",function(){
        //$("#closeoutmodify").click(function(){
             $("#modifyModal").hide();
        });
        
		$(document).on("click",".forbidenreopen",function(){
		//$('.forbidenreopen').click(function () {
        	var ID =  $(this).attr('rel');
        	var txt = $(this).text();
        	var _this=$(this);
        	var status = "1";
        	var nextText = "",className="";
                var txtbtn=txt;
        	if(txt=="<?php echo L('禁用'); ?>"){
        		status='3';
        		nextText="<?php echo L('启用'); ?>";
        		className="btn-primary";
                txts=txt+"<?php echo L('后将无法支付'); ?>，"+"<?php echo L('请谨慎操作'); ?>！";
        	}else{
        		status='1';
        		nextText="<?php echo L('禁用'); ?>";
        		className="btn-danger";
                txts="<?php echo L('请谨慎操作'); ?>";
        	}
			
			layer.confirm("<?php echo L('您确定要'); ?>"+txtbtn+"<?php echo L('这条信息吗'); ?>", {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				var url = "?clause=updateStatus";
				$.post(url, "id="+ID+"&status="+status, function(data) {
					if (data.status == 1) {
						layer.msg('<?php echo L('更新成功'); ?>');
						setTimeout(function () {
							document.location.reload();
						}, 700);
					} else if (data.status == 0) {
						layer.msg('<?php echo L('更新失败');?>');
					}

					layer.close(index);
					
				}, 'json');
			}, function(index){
				layer.close(index);
			});	

		});
    </script>
        
        
        
        
        
        

    </body>
</html>
