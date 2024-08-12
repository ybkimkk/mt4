<?php
$LoadCSSArr = array();
require_once('header.php');

$data = $DB->getDRow("select * from `t_member` where `id` = '{$Id}'");
if(!$data){
	FJS_AB(L("查询数据失败"));
}
//S("loginapply6", null);
//图片
if (!empty($data['identityOpposite'])) {
	$attach = $DB->getDRow("select * from `t_attach` where `id` = '{$data['identityOpposite']}'");
	$imgpath = str_replace(".", "", $attach['savepath']) . $attach['savename'];
}
if (!empty($data['identityBack'])) {
	$attach1 = $DB->getDRow("select * from `t_attach` where `id` = '{$data['identityBack']}'");
	$imgpath1 = str_replace(".", "", $attach1['savepath']) . $attach1['savename'];
}
if (!empty($data['bankCard'])) {
	$attach2 = $DB->getDRow("select * from `t_attach` where `id` = '{$data['bankCard']}'");
	$imgpath2 = str_replace(".", "", $attach2['savepath']) . $attach2['savename'];
}
if (!empty($data['addressProof'])) {
	$attach3 = $DB->getDRow("select * from `t_attach` where `id` = '{$data['addressProof']}'");
	$imgpath3 = str_replace(".", "", $attach3['savepath']) . $attach3['savename'];
}

$ranks = $DB->getDTable("select * from `t_ib_rank` where `status` = '1' order by rank asc");

if ($data['parent_id']) {
	$parentMember = $DB->getDRow("select * from `t_member` where `id` = '{$data['parent_id']}' and `status` = 1 and `server_id` = '{$DRAdmin['server_id']}'");
}

$user = $DRAdmin;

$rid = FGetInt('rid');
$apply = $DB->getDRow("select * from `t_mt4_apply` where `id` = '{$rid}'");


if ($DRAdmin['svr_ids']) {
	$_ids = $DRAdmin['svr_ids'];
}
if ($_ids) {
	$agroups = $DB->getDTable("select * from `t_groups` where `server_id` in ({$_ids}) and `type` = 'A'");
	$bgroups = $DB->getDTable("select * from `t_groups` where `server_id` in ({$_ids}) and `type` = 'B'");
	//$mtlogin = $DB->getField("select recomment_groups from `t_member` where id = '{$DRAdmin['id']}' and `status` = 1");
	//$bgroups = $DB->getDTable("select * from `t_groups` where `server_id` in ({$_ids}) and `group` in ({$mtlogin})");
	if (empty($agroups)) {
		$agroups = array();
	}
	if (empty($bgroups)) {
		$bgroups = array();
	}
	
	$mtgroup = array_merge($agroups, $bgroups);

	//$servers = $DB->getDTable("select * from `t_mt4_server` where `id` in ({$_ids}) order by id asc");
}
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .control-label {padding-top: 7px;margin-bottom: 0;text-align: right;}
	.form-horizontal .form-control-static {min-height: 34px;padding-top: 7px;padding-bottom: 7px;margin-bottom: 0;}
}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('用户审核') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('昵称'); ?></th>
                                                        <td><?php echo $data['nickname']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('手机'); ?></th>
                                                        <td><span class='lookphone'><?php echo hideStr($data['phone'],3,4); ?></span>&nbsp;&nbsp;<i class="fa fa-eye findinfo" phone="<?php echo $data['phone']; ?>" email="<?php echo $data['email']; ?>"></i></td>
                                                        <th class="text-nowrap"><?php echo L('邮箱'); ?></th>
                                                        <td><span class='lookemail'><?php echo hideStr($data['email'],2,3); ?></span>&nbsp;&nbsp;<i class="fa fa-eye findinfo" phone="<?php echo $data['phone']; ?>" email="<?php echo $data['email']; ?>"></i></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"> <?php echo L('国籍'); ?></th>
                                                        <td><?php echo $data['nationality']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('称谓'); ?></th>
                                                        <td><?php echo $data['appellation']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('中文名'); ?></th>
                                                        <td><?php echo $data['chineseName']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('出生日期'); ?></th>
                                                        <td><?php echo $data['birthDate']; ?>
                                                        </td>
                                                        <th class="text-nowrap"><?php echo L('居住地'); ?>
                                                        </th>
                                                        <td><?php echo $data['livingState']; ?>
                                                        </td>
                                                        <th class="text-nowrap"><?php echo L('城市'); ?>
                                                        </th>
                                                        <td><?php echo $data['city']; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('地址'); ?></th>
                                                        <td ><?php echo $data['residentialAddress']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('居住年限'); ?></th>
                                                        <td ><?php echo $data['residenceTime']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('住宅电话'); ?></th>
                                                        <td ><?php echo $data['residentialTelephone']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('开户支行'); ?></th>
                                                        <td ><?php echo $data['bankName']; ?><?php echo $data['bankBranch']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('账户名称'); ?></th>
                                                        <td ><?php echo $data['accountName']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('银行账号'); ?></th>
                                                        <td ><?php echo $data['accountNum']; ?></td>
                                                    </tr>
                                                     <tr>
                                                        <th class="text-nowrap"><?php echo L('银行国际代码'); ?></th>
                                                        <td ><?php echo $data['swiftCode']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('真实姓名'); ?></th>
                                                        <td ><?php echo $data['realname']; ?></td>
                                                        <th class="text-nowrap"><?php echo L('证件号码'); ?></th>
                                                        <td colspan="3"><?php echo $data['identity']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('身份证正面'); ?></th>
                                                        <td colspan="2"><a href="<?php echo $imgpath; ?>" target="_blank"><img src="<?php echo $imgpath; ?>" style="width: 200px;height: 200px;"></a></td>
                                                        <th class="text-nowrap"><?php echo L('身份证反面'); ?></th>
                                                        <td colspan="2"><a href="<?php echo $imgpath1; ?>" target="_blank"><img src="<?php echo $imgpath1; ?>" style="width: 200px;height: 200px;"></a></td>
                                                        
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('银行卡照片'); ?></th>
                                                        <td colspan="2"><a href="<?php echo $imgpath2; ?>" target="_blank"><img src="<?php echo $imgpath2; ?>" style="width: 200px;height: 200px;"></a></td>
                                                        <th class="text-nowrap"><?php echo L('地址证明照片'); ?></th>
                                                        <td colspan="2">
                                                        <?php
                                                        if(strlen($imgpath3) > 0){
															echo '<a href="' , $imgpath3 , '" target="_blank"><img src="' , $imgpath3 , '" style="width: 200px;height: 200px;"></a>';
														}else{
															echo L('未上传地址证');
														}
														?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-nowrap"><?php echo L('备注'); ?></th>
                                                        <td colspan="5">
															<?php
                                                            echo $data['remark'];
															echo '<br><a class="btn btn-primary btn-xl mt-sm-2" type="button" data-target="#update_make" data-toggle="modal" href="#nolink" >' , L('修改备注') , '</a>';
															?>
                                                        </td>   
                                                    </tr>
                                                    <tr>
                                                       <td class="text-nowrap" colspan="6" align="center">
                                                       <?php
                                                       if($apply['status'] == 0){
														   echo '<a class="btn btn-primary btn-xl " type="button" rel="' , $data['id'] , '" data-target="#checkModal" id="checkuser" data-toggle="modal" href="#nolink">' , L('审核') , '</a>';
														   //echo '<a class="btn btn-danger btn-xl " type="button" href="#nolink" id="reback" rel="' , $data['id'] , '">驳回</a>';
													   }
													   ?>
                                                       <a class="btn btn-default btn-xl " type="button" href="javascript:history.back()"><?php echo L('返回'); ?></a>
                                                       </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                                        
                                        

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                        
                        
                        


                    </div> <!-- container -->
                    
                    
                    
                    
                    
                    
                    
                    
    <!--弹出层-->
    <div class="modal inmodal" id="update_make" tabindex="-1" role="dialog" aria-hidden="true" data-test="">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('修改备注'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                <form  id='levelForm' name='levelForm' autocomplete="off">
                    <div class="modal-body">
                       <div class="input-group m-b">
                        <div class="col-sm-10">
                            <label><?php echo L('备注信息'); ?>：</label> 
                            <input type='hidden' id='id_upd' value='<?php echo $data['id']; ?>'/>
                            <input type="text" name="upd_mark" id="upd_mark" value="<?php echo $data['remark']; ?>" style="width:300px;height:35px">
                        </div>

                    </div>   
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-dismiss="modal"><?php echo L('关闭'); ?></button>
                        <button type="button" class="btn btn-primary"  id="upd" ><?php echo L('点击保存'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
     <!--弹出层-->
      <!--弹出层-->
    <div class="modal inmodal" id="checkModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('客户资料审核'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                <form  id='levelForm' name='levelForm' autocomplete="off">
                    <div class="modal-body">
                    <div class="row">
	                    <div class="col-md-12"> 
		                    <div class="form-group"><label><?php echo L('上级渠道'); ?>：</label> 
			            		<p class="form-control-static"  id="parentname">
                                <?php
                                if(!$parentMember){
									echo L('无上级');
								}else{
									echo $parentMember['realname'];
									echo ' (' , $parentMember['nickname'] , ')';
									if($parentMember['userType'] == 'agent'){
										echo $parentMember['level'];
										echo ' ' . L('级') . ' ' . L('代理');
									}else{
										echo L('直接客户');
									}
								}
								?>
			            		</p>
				            </div>
			            </div>
		            </div>
                    <div class="row">
	                    <div class="col-md-6"> 
		                    <div class="form-group"><label><?php echo L('客户信息'); ?>：</label> 
			            		<span class="form-control-static"  id="parentname"  >
			            		<?php echo $data['realname']; ?> (<?php echo $data['nickname']; ?>)
			            		</span>
				            </div>
			            </div>
				        <div class="col-md-6"> 
			            	<div class="form-group"><label><?php echo L('本客户类型'); ?>：</label> 
			            		<span class="form-control-static"  id="userType" name="userType">
                                <?php
                                if($data['userType'] == 'agent'){
									echo $data['level'];
									echo ' ' . L('级') . ' ' . L('代理');
								}else{
									echo L('直接客户');
								}
								?>
			            		</span>
                                <?php
                                if(strlen($data['account_type']) > 0){
									echo '(' , $data['account_type'] , ')';
								}
								?>
				            </div>
			            </div>
                        <div class="col-md-6"> 
                            <div class="form-group"><label><?php echo L('注册IP'); ?>：</label> 
                                <span class="form-control-static"  id="parentname"  >
                                <?php echo $data['register_ip']; ?>
                                </span>
                            </div>
                        </div>
                         <div class="col-md-6"> 
                            <div class="form-group"><label><?php echo L('注册地区'); ?>：</label> 
                                <span class="form-control-static"  id="parentname"  >
                                <?php echo $data['register_area']; ?>
                                </span>
                            </div>
                        </div>
		            </div>
                    <div class="form-group"><label><?php echo L('MT服务器'); ?>：</label> 
                        <select name='mtserver' id="mtserver" class='form-control m-b' >
                        	<?php
                            foreach($DTMt4Server as $key=>$val){
								echo '<option value="' , $val['id'] , '">';
								echo $val['mt4_name'];
								if($val['real'] ==0 ){
									echo L('模拟仓');
								}else{
									echo L('真实仓');
								}
								echo '</option>';
							}
							?>
                        </select>
                    </div>
                    <div class="row">
	                    <div class="col-md-6"> 
		                    <div class="form-group" ><label><?php echo L('MT组'); ?>：</label> 
		                        <select name='mtgroup' id="mtgroup"  class='form-control m-b'>
                                	<?php
		                            foreach($mtgroup as $key=>$val){
										echo '<option value="' , $val['group'] , '">' , $val['group'] , '</option>';
									}
									?>
		                        </select>
		                    </div>
		                </div>
			          	<div class="col-md-6"> 
	                      	  <?php
		                        if(C('LEVERAGES'))
		                        	$strarr = explode(",",C('LEVERAGES'));
		                        else
		                        	$strarr = array(C('DEFAULT_LEVER'));	
		                       ?>
		                      <div class="form-group" ><label><?php echo L('杠杆比例'); ?>：</label> 
		                        <select name='leverage' id="leverage" class='form-control m-b'>
		                          <?php
                                  for($i=0;$i<count($strarr);$i++){
		                         	echo '<option value="' , $strarr[$i] , '"';
									if($data['leverage'] == $strarr[$i]){
										echo ' selected';
									}
									echo '>1:';
									echo $strarr[$i];
									echo '</option>';
		                       	  }
								  ?>
		                        </select>
		                    </div>
	                    </div>
                    </div>
                	<label><?php echo L('MT账号'); ?>：</label>
                	<div class="input-group m-b">
						<div class="col-sm-12" style='padding-left:0px; padding-right:0px;'>
                        <input type="text" class="form-control "<?php if(strlen($apply['login']) > 0){echo ' readonly';} ?> name="loginid" value="<?php echo $apply['login']; ?>" id="loginid" AutoComplete="off" placeholder="<?php echo L('无MT账户，请先点击申请；否则请填写自己的MT账户密码'); ?>">
                        <input style="display:none">
						</div>
                        <?php
                        if(strlen($apply['login']) > 0){
							echo '<button type="button" class="btn btn-default">' , L('已生成') , '</button> ';
						}else{
							echo '<button type="button" class="btn btn-primary" id="getmtlogin">' , L('点击开户') , '</button> ';
						}
						?>
                    </div>
                    <span class="help-block m-b-none"><font color="green"><?php echo L('温馨提示：开户申请前，请确认MT组和杠杆，账户申请生成后，本页面将不能修改'); ?></font></span>
                 	<br/>
                 	<label><?php echo L('MT密码'); ?>：</label>
                	<div class="input-group m-b">
                        <div class="col-sm-12" style='padding-left:0px; padding-right:0px;'>
                        <input type="text" style="visibility: hidden;display:none"/>
                        <input type="text" class="form-control" name="password"<?php if(strlen($apply['login']) > 0){echo ' readonly';} ?> value="<?php echo think_decrypt($apply['password'], C('PASSWORD_KEY')); ?>" id="password" AutoComplete="off" > 
                    	</div>
						<!--<button type="button" class="btn btn-primary" id='showpass'><?php echo L('显示密码'); ?></button> -->
                    </div>
                    <div class="modal-footer mt-sm-2">
                        <input type='hidden' name='id' id="id" value='<?php echo $data['id']; ?>'/>
						<input type='hidden' name='rid' id="rid" value='<?php echo $rid; ?>'/>
                        <button type="button" class="btn btn-white" data-dismiss="modal"><?php echo L('关闭'); ?></button>
                        <button type="button" class="btn btn-primary check"><?php echo L('保存并发送开户邮件'); ?></button>
                    </div>
            </div>

            </form>
        </div>
    </div>
     <!--弹出层-->
                    
                    
                    
                    
                    
                    
                    


		<?php
        require_once('footer.php');
        ?>
        
        
        
        <script src="/assets/js/layer/layer.js"></script>
        
        
        
        
 	<script type="text/javascript">
	init_findinfo();
	
	$("#userType").change(function() {
		var userTypeVal = $("#userType").val();
		if(userTypeVal=='agent')
        	$("#userType").text("<?php echo ' ' . L('级') . ' ' . L('代理'); ?>");
        else
        	$("#userType").text('<?php echo L('直接客户'); ?>');
    });
	
	$(document).on("click","#showpass",function(){
	//$("#showpass").click(function() {
		if ($("#password").attr("type") == "password") {
            $("#password").attr("type", "text")
        }
        else {
            $("#password").attr("type", "password")
        }
    });

	$(document).on("click",".check",function(){
    //$(".check").click(function() {
    	$(this).attr('disabled', "disabled");
    	 var _this=$(this);
        var url = "?clause=savemtlogin";
        var form = $(this).closest('form');
        $("#checkuser").attr("disabled","disabled");
        $("#reback").attr("disabled","disabled");
        $.post(url, form.serialize(), function(data) {
            layer.alert(data.info);
            if (data.status) {
        	   $(".close").click();
               window.location.href="?";
            }
            _this.removeAttr("disabled");
        }, 'json');
    });
	
	$(document).on("click","#getmtlogin",function(){
	//$("#getmtlogin").click(function(){
		 $(this).attr('disabled', "disabled");
	     if('<?php echo $apply['login']; ?>'!=''){
	    	 layer.alert("<?php echo L('账户已经生成，请直接保存完成开户'); ?>！");
	    	 return;
	     }
      	 var _this=$(this);
	      var url="?clause=getmtlogin";
		  var form = $(this).closest('form');
		  $.post(url, form.serialize(), function(data) {
            if (data.status==1) {
        	   	$("#loginid").val(data.info.user);
			    $("#password").val(data.info.pass);
			    $("#mtserver").attr('readonly','readonly');
			    $("#mtgroup").attr('readonly','readonly');
			    $("#leverage").attr('readonly','readonly');
			    $("#password").attr('readonly','readonly');
			    $("#loginid").attr('readonly','readonly');
            }else{
			  layer.alert(data.info);
			  _this.removeAttr("disabled");
			}
           
        }, 'json')
	});
	
	var json = '<?php echo escapeJsonString(json_encode($mtgroup)); ?>';
	$('#mtserver').change(function(){
		var svr =  $(this).val();
		changeMtgroup(svr);
	})
	
	
	function changeMtgroup(svr){
		var json_obj = JSON.parse(json);
		$('#mtserver').val(svr);
		$('#mtgroup').empty();
		for(var o in json_obj){ 
			if(json_obj[o].server_id ==svr){
				$("#mtgroup").append("<option value='"+json_obj[o].group+"'>"+json_obj[o].group+"</option>"); 
				console.log(json_obj[o]);
			}
		}
	}
	
	changeMtgroup('<?php echo $apply['mt4_server_id']; ?>');
	$("#mtgroup").val('<?php echo escapeJsonString($apply["group"]); ?>');
	$("#leverage").val('<?php echo $apply['leverage']; ?>');
	if('<?php echo $apply['login']; ?>'!=''){
		$("#mtserver").attr('readonly','readonly');
	    $("#mtgroup").attr('readonly','readonly');
	    $("#leverage").attr('readonly','readonly');
	}

	$(document).on("click","#upd",function(){
    //$("#upd").click(function(){
        var mark = $("#upd_mark").val();
        var id_mark = $("#id_upd").val();
        $.ajax({
            type:"post",
            url:"?clause=updateMark",
            data:{mark:mark,id_mark:id_mark},
            dataType:"json",
            success:function(data){
                alert('<?php echo L("修改成功");?>');
				
                document.location.reload();;
            }
        })
    })
    </script>
        
        

    </body>
</html>
