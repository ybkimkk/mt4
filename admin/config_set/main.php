<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');


/*
$str = 'a:2:{i:0;s:3:&quot;否&quot;;i:1;s:3:&quot;是&quot;;}';
$strArr = unserialize(html_entity_decode($str));
print_r($strArr);exit;
*/


/*
$strArr = array(
	'0'=>'否',
	'1'=>'是，所有直客',
	'2'=>'是，仅标识(S)的直客',
);
$str = htmlentities(serialize($strArr));
print_r($str);exit;
*/


$array = array(1,2,3,4,5,6,7,8);
unset($array[1]);
unset($array[6]);

        
$item = $DB->getDTable("select * from `t_config_item` where id in (" . implode(',',$array) . ")");
$sid = $DRAdmin['server_id'];
$server = $DB->getDRow("select * from `t_mt4_server` where id = '{$sid}' and `status` = 1");
if ($sid > 0 && !$server) {
	FCreateErrorPage(array(
		'title'=>L("提示"),
		'content'=>L("MT服务器不存在"),
		'btnStr'=>L('返回'),
		'url'=>FPrevUrl(),
		'isSuccess'=>0,
		'autoRedirectTime'=>0,
	));
}

$configvalue = $DB->getField2Arr("select configname,configvalue from `t_config_server` where `server_id` = '{$sid}'");
foreach ($item as $key => $val) {
	$config = $DB->getDTable("select * from `t_config` where `group` = '{$val['id']}' and `status` = 1 order by sort desc");
	//查询私有配置
	foreach ($config as $k => $v) {
		if ($v['sid'] > 0) {
			$config[$k]['value'] = NULL !== $configvalue[$v['name']] ? $configvalue[$v['name']] : $v['value'];
		}
	}
	if ($config) {
		$item[$key]['detail'] = $config;
	} else {
		unset($item[$key]);
	}
}
?>

<style>
.nav-tabs{ margin-bottom:20px;}
.nav-tabs a.active{color: #fff;background-color: #727cf5;}

.form-horizontal label{ font-weight:normal;}
.form-horizontal .form-group > label{ font-weight:bold; margin-right:15px;}
.form-horizontal .checkbox-inline{ margin-right:15px;}
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}

.form-check-inline{ margin-left:15px;display:inline-block;}
.checkbox-inline, .checkbox-inline + .checkbox-inline, .radio-inline, .radio-inline + .radio-inline{ margin-right:15px;padding-top: 7px;display:inline-block;}
.help-block {display: block;margin-top: 5px;margin-bottom: 10px;color: #737373;}
</style>
                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('配置管理') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                <ul class="nav nav-tabs">
                	<?php
                    foreach($item as $key=>$vo){
						echo '<li class="nav-item"><a data-toggle="tab" href="#tab-' , $vo['id'] , '" aria-expanded="';
						if($key <= 0){
							echo 'true';
						}else{
							echo 'false';
						}
						echo '" class="nav-link';
						if($key <= 0){
							echo ' active';
						}
						echo '"> ' , L($vo['items']) , '</a></li>';
					}
					?>
                </ul>
                <div class="tab-content" >
					<?php
                    foreach($item as $key=>$vvo){
					?>
                        <div id="tab-<?php echo $vvo['id'];?>" class="tab-pane<?php if($key <= 0){echo ' active';}?>">
                            <div class="panel-body" style="min-height: 622px">
                                <div class="ibox-content">
                                    <form class="form-horizontal" action="?clause=saveitem" method="post" novalidate="novalidate" id="commentForm<?php echo $vvo['id'];?>" autocomplete="off">
                                        <?php
										foreach($vvo['detail'] as $key=>$vd){
                                            if($vd['type'] == 'text'){
												$showexchage=0;
												if(in_array($vd["name"],array("EXCHANGERATE","EXCHANGERATE_OUT")) && C("OPEN_WWW_FOREX")== 1){
													$showexchage=1;
												}
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                    	<?php
                                                        if(in_array($vd["name"],array("DEAFAULT_ROLE","DEAFAULT_ROLE_ADMIN"))){
															echo '<select name="' , $vd['name'] , '">';
															echo '<option value="">-=' , L("请选择") , '=-</option>';
															$czQuery = $DB->query("select * from t_role where status = 1 order by id asc");
															while($czRs = $DB->fetchArray($czQuery)){
																echo '<option value="' , $czRs['id'] , '"' , $vd['value'] == $czRs['id'] ? ' selected="selected"' : '' , '>' , L($czRs['name']) , '</option>';
															}
															echo '</select>';
														}else{
														?>
                                                        <input id="<?php echo $vd['name'];?>" name="<?php echo $vd['name'];?>"<?php
                                                        if($showexchage == 1 || $vd['readonly'] == 1 || $vd['name'] == 'WWW_FOREX'){echo ' readonly="readonly"';}
														?> type="text" class="form-control" value="<?php echo $vd['value'];?>" required="<?php echo L($vd['remark']);?>" aria-required="true">
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                        <?php
														}
														?>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'textarea'){
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                        <textarea id="<?php echo $vd['name'];?>" name="<?php echo $vd['name'];?>" class="form-control" placeholder="<?php echo L($vd['remark']);?>"><?php echo $vd['value'];?></textarea>
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'radio'){
												 $ext=unserialize(html_entity_decode($vd['extra']));
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5 form-check-inline">
                                                        <?php
                                                            foreach($ext as $keys=>$vc){
                                                        ?>
                                                        <div class="radio radio-info radio-inline">
                                                            <input id="<?php echo $vd['name'];?><?php echo $keys;?>" value="<?php echo $keys;?>" name="<?php echo $vd['name'];?>"<?php if($vd['value'] == $keys){echo ' checked="checked"';} ?> type="radio"/>
                                                            <label for="<?php echo $vd['name'];?><?php echo $keys;?>"><?php echo L($vc);?></label>
                                                        </div>

                                                        <?php
                                                        }
														?>
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'password'){
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                        <input type="text" style="visibility: hidden;display:none"/>
                                                        <input id="<?php echo $vd['name'];?>" name="<?php echo $vd['name'];?>" type="password" class="form-control pass" value="<?php echo $vd['value'];?>" required="<?php echo L($vd['remark']);?>" aria-required="true">
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'checkbox'){
												$ext=unserialize(html_entity_decode($vd['extra']));
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5 form-check form-check-inline">
                                                        <input id="<?php echo $vd['name'];?>_s_<?php echo $keys;?>" value="" name="<?php echo $vd['name'];?>[]" checked="checked" type="checkbox" style="display:none"/>
                                                        <?php
                                                            foreach($ext as $keyss=>$vc){
                                                        ?>
                                                        <div class="checkbox checkbox-info checkbox-inline">
                                                            <input class="form-check-input" id="<?php echo $vd['name'];?><?php echo $keyss;?>" value="<?php echo $keyss;?>" name="<?php echo $vd['name'];?>[]"<?php if(in_array($keyss,explode(',',$vd['value']))){echo ' checked="checked"';} ?> type="checkbox"/>
                                                            <label class="form-check-label" for="<?php echo $vd['name'];?><?php echo $keyss;?>"><?php echo L($vc);?></label>
                                                        </div>
                                                        <?php
                                                        }
														?>
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>

                                                </div>
											<?php
											}else if($vd['type'] == 'button'){
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                        <button id="fs_email_btn" type="button" onclick="fs_email()"><?php echo L($vd['value']);?></button>
                                                        <!--  <input id="<?php echo $vd['name'];?>" name="<?php echo $vd['name'];?>" type="button" class="form-control pass" value="<?php echo $vd['value'];?>" required="<?php echo $vd['remark'];?>" aria-required="true"> -->
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'select'){
												
											}else if($vd['type'] == 'file'){
												
											}else if($vd['type'] == 'image'){
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                        <a type="file" class="form-control uploadfile" value="<?php echo $vd['value'];?>" required="<?php echo L($vd['remark']);?>" style='width: 200px;' aria-required="true"><?php echo L('上传');?><?php echo L($vd['title']);?></a>
                                                        <input type="text" class="form-control" id='<?php echo $vd['name'];?>' name="<?php echo $vd['name'];?>" value='<?php echo $vd['value'];?>'><br/>
                                                        <span class='imglist'><img src='<?php echo $vd['value'];?>' style='width:100px;hight:100px;'></span>
                                                        <span class="help-block m-b-none"><?php echo L($vd['remark']);?></span>
                                                    </div>
                                                </div>
											<?php
											}else if($vd['type'] == 'editor'){
												
											}else{
											?>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 control-label"<?php if(strlen($vd['bgColor']) > 0){echo ' style="background-color:' , $vd['bgColor'] , ';"';}?>><?php echo L($vd['title']);?>：</label>
                                                    <div class="col-sm-5">
                                                        <input id="name" name="<?php echo $vd['name'];?>" type="text" class="form-control" value="<?php echo $vd['value'];?>" required="<?php echo L($vd['remark']);?>" aria-required="true">
                                                    </div>
                                                </div>
										<?php
											}
                                        }
                                        ?>
                                        <div class="form-group row">
                                            <label class="col-sm-2 control-label">&nbsp;</label>
                                            <div class="col-sm-5">
                                                <button class="btn btn-primary saveconfigitem" id="formsumit<?php echo $vvo['id'];?>" type="submit"><?php echo L('保存');?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php
					}
					?>
                </div>
                </div>
            </div>
        </div>
                        


                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>
        
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
        </script>
        
        
        
        
        
        
        
        
<link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"></link>
<script src="/assets/js/sweetalert/sweetalert.min.js"></script>
<script src="/assets/js/datapicker/bootstrap-datepicker.js"></script>
<link href="/assets/js/datapicker/datepicker3.css" rel="stylesheet">
<link href="/assets/js/footable/footable.core.css" rel="stylesheet">
<script src="/assets/js/footable/footable.all.min.js"></script>
<script type="text/javascript" src="/assets/js/ajaxupload.3.5.js"></script>
<script>
var follow_config_type = "<?php echo C('FOLLOW_COMM_TYPE'); ?>";
function fs_email() {
	var email = prompt('<?php echo L('请输入测试邮箱'); ?>');
	var pattern = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/; //验证邮箱
	if (!pattern.test(email)) {
		alert('<?php echo L('请输入正确的邮箱'); ?>');
		return false;
	}

	$('#fs_email_btn').html('<?php echo L('发送中'); ?>...');

	$.ajax({
		type: "post",
		url: "?clause=cs_email",
		data: {email: email},
		dataType: "json",
		success: function (data) {
			if (data.code == 1) {
				alert('<?php echo L('发送成功'); ?>');
			} else {
				alert('<?php echo L('发送失败'); ?>');
			}
			$('#fs_email_btn').html('<?php echo L('测试'); ?>');
			/*setTimeout(function () {
				document.location.reload();
			}, 1500);
			console.log(data);*/
		}, error: function () {
			alert("<?php echo L('网络异常'); ?>");
		}
	})
}
$(function () {
	$(document).on("click",".saveconfigitem",function(){
	//$(".saveconfigitem").click(function () {
		var from = $(this).closest('form');
		if (from.find("input[name='FOLLOW_COMM_TYPE']").size() > 0) {
			if (follow_config_type == '0' && from.find("input[name='FOLLOW_COMM_TYPE']:checked").val() != '0') {
				swal({
					title: "<?php echo L('您确定要切换跟单返佣模式吗'); ?>？",
					text: "<?php echo L('跟单从及时切换到周期，持仓订单不结算，您可以就将跟单订单全部平仓后在进行切换'); ?>！",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "<?php echo L('强制切换'); ?>",
					closeOnConfirm: false,
					showLoaderOnConfirm: true,
				}, function () {
					$.post(from.attr('action'), from.serialize(), function (data) {
						alert(data.info);
						if (data.status) {
							setTimeout(function () {
								document.location.href = '/config/index';
							}, 1500);
						}
					}, 'json');
				});
			} else {
				$.post(from.attr('action'), from.serialize(), function (data) {
					alert(data.info);
					if (data.status) {
						setTimeout(function () {
							document.location.href = '/config/index';
						}, 1500);
					}
				}, 'json');
			}
		} else {
			$.post(from.attr('action'), from.serialize(), function (data) {
				alert(data.info);
				if (data.status) {
					setTimeout(function () {
						document.location.href = '/config/index';
					}, 1500);
				}
			}, 'json');
		}
		return false;
	});
	$("body #wrapper").css({"min-height": '100%', 'background-color': 'inherit'});
	$(".ibpage").css({"height": '27px', 'line-height': '27px'});
})
var image = Array();
$(function () {
	var button = $('.uploadfile'), interval,intervalCi = 0;
	new AjaxUpload(button, {
		action: "uploader/ajax_upload_save.php",
		name: 'myfile',
		onSubmit: function (file, ext) {
			if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
				alert('<?php echo L("图片格式不正确,请选择 jpg,png 格式的文件"); ?>!', '<?php echo L("系统提示"); ?>');
				return false;
			}
			button.text('<?php echo L("上传中"); ?>');
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
			var info=$.parseJSON(k);
			if (info['status'] == -1) {
				alert(info['info']);
			}else {
				var savepath = info.data[0].savepath;
				var savename = info.data[0].savename;

				//image.push(savepath + savename);
	  
				button.text('<?php echo L('上传完成');?>');
				$('.imglist').html("<img style='width:100px;hight:100px;' src='" + savepath + savename + "'>");
				$("#WEB_LOGO_FILE").val(savepath + savename);
			}
		}
	});
});
</script>
        
        
        
        
        
        

    </body>
</html>
