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

@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('返佣提现') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-tabs mb-3"><!-- nav-tabs-cz-->
                                            <?php
                                            	$Type = FGetInt('type');
												
                                                echo '<li class="nav-item"><a href="?" aria-expanded="';
                                                if($Type <= 0){
                                                    echo 'true';
                                                }else{
                                                    echo 'false';
                                                }
                                                echo '" class="nav-link';
                                                if($Type <= 0){
                                                    echo ' active';
                                                }
                                                echo '"> ' , L('返佣提现') , '</a></li>';//<span class="czico-arrow"></span>
												
												/*
												echo '<li class="nav-item"><a href="?type=1" aria-expanded="';
                                                if($Type > 0){
                                                    echo 'true';
                                                }else{
                                                    echo 'false';
                                                }
                                                echo '" class="nav-link';
                                                if($Type > 0){
                                                    echo ' active';
                                                }
                                                echo '"> ' , L('返佣转账') , '</a></li>';//<span class="czico-arrow"></span>
												*/
                                            ?>
                                        </ul>                                    
                                    	<?php

if($Type == 1){
	echo '<form class="form-horizontal" action="" method="post" target="iframe_qpost" id="signupForm" autocomplete="off">
			<div class="form-group row">
				<label class="col-sm-2">' , L("转账金额") , '：</label>
				<div class="col-sm-3">
					<input id="forwordnumber" name="forwordnumber" type="text" class="form-control required" value="" placeholder="' , L('您的可用余额') , '$' , $DRAdmin['amount'] , '">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2">' , L("验证密码") , '：</label>
				<div class="col-sm-3">
					<input id="password" name="password" type="password" class="form-control required" value="" placeholder="' , L('请输入MT主账号密码') , '">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2">' , L("接收帐号") , '：</label>
				<div class="col-sm-3">
					<select id="getlogin" name="getlogin" class="form-control required">
						<option value="">-=' , L('请选择') , '=-</option>';
	
        $account = getunderCustomerIds($DRAdmin['id']);
		if(!$account){
			$account = array('0');
		}
		// and userType = 'agent'
        $userlist = $DB->getDTable("select nickname,email,phone,id from t_member where id in (" . implode(',',$account) . ") and status = 1 and server_id = '{$DRAdmin['serverid']}'");
		foreach($userlist as $key_=>$val_){
			echo '<option value="' , $val_['id'] , '">' , $val_['nickname'] , '--' , hideStr($val_['email'],2,3) , '</option>';
		}

	
	echo '
					</select>
				 </div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">&nbsp;</label>
				<div class="col-sm-8">
					<button type="button" class="btn btn-primary" id="forwordmoney">' , L("确认提交") , '</button>
				</div>
			</div>
		</form>';
}else{
	$memberLogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['serverid']}' and status = 1 and mt_type = 0");//主账户
	
	echo '<form class="form-horizontal" action="" method="post" target="iframe_qpost" id="signupForm" autocomplete="off">
			<div class="form-group row">
				<label class="col-sm-2">' , L("提现金额") , '：</label>
				<div class="col-sm-3">
					<input id="amount" name="amount" type="text" class="form-control required" value="" placeholder="' , L('您的可用余额') , '$' , $DRAdmin['amount'] , '">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2">' , L("提现MT服务器") , '：</label>
				<div class="col-sm-8 mt-sm-1">' , $DRAdmin['mt4name'] , '</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2">' , L("提现MT账号") , '：</label>
				<div class="col-sm-8 mt-sm-1">' , $memberLogin['loginid'] , '</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 control-label">&nbsp;</label>
				<div class="col-sm-8">
					<button type="button" class="btn btn-primary" id="outamount">' , L("确认提交") , '</button>
				</div>
			</div>
		</form>';
}
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
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet">
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>
        
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
	$('#forwordmoney').click(function () {
		var forwordnumber = $("#forwordnumber").val();
		if(forwordnumber==''){
			alert("<?php echo L('请填写转账金额'); ?>！");
			return;
		}
			var getlogin=$("#getlogin option:selected").text();
		var form = $(this).closest('form');
		swal({
			title: "<?php echo L('您确定要转账吗'); ?>",
			text: "<?php echo L('您本次转账金额'); ?>【 $"+forwordnumber+"】,<?php echo L('接收帐号'); ?>【"+getlogin+"】<?php echo L('请确认'); ?>！",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('转账'); ?>",
			closeOnConfirm: false,   
			showLoaderOnConfirm: true,
		}, function () {
			var url = "?clause=dowithaccount";
			$.post(url, form.serialize(), function(data) {
				if(data.status==1) {
					swal("<?php echo L('转账成功'); ?>！", data.info, "success");
					setTimeout(function(){document.location.reload();},600);					
				}else{
					swal("<?php echo L('转账失败'); ?>！", data.info, "warning");
				} 
				
			}, 'json');
		});
	});
	
	
	   $('#outamount').click(function () {
		var amount = $("#amount").val();
		if(amount==''){
			alert("<?php echo L('请填写提现金额'); ?>！");
			return;
		}
		var form = $(this).closest('form');
		swal({
			title: "<?php echo L('您确定要提现吗'); ?>",
			text: "<?php echo L('您本次提现金额'); ?>【 $"+amount+"】,<?php echo L('请确认'); ?>！",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('提现'); ?>",
			closeOnConfirm: false,   
			showLoaderOnConfirm: true,
		}, function () {
			var url = "?clause=dowithdraw";
			$.post(url, form.serialize(), function(data) {
				if(data.status==1) {
					swal("<?php echo L('提现成功'); ?>！", data.info, "success");
					setTimeout(function(){document.location.reload();},600);
				}else{
					swal("<?php echo L('提现失败'); ?>！", data.info, "warning");
				} 
				
			}, 'json');
		});
	});
</script>

    </body>
</html>
