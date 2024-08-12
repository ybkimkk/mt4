<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){
	$rs = $DB->getDRow("select * from t_sale_setting_other where ID = '" . $Id . "' and STATUS in (1,3) and SERVER_ID = '{$DRAdmin['server_id']}'");
	if(!$rs){
		FJS_AB(L("数据查询失败"));
	}
}

$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
$groups = $DB->getDTable("select * from t_groups where server_id = '" . $DRAdmin['server_id'] . "'");
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
</style>

					<div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php 
									echo L('佣金设置');
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
                                        <form class="form-horizontal" action="" method="post" id="signupForm">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("标题");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="input-group">
														<input type="text" class="form-control" name="f_title" id="f_title" placeholder="">
													</div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("返佣模式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_agent" value="agent" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_agent" class="ttt"><?php echo L('代理奖励'); ?></label>
                                                    </div>
													<!--
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_direct" value="direct" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_direct" class="ttt"><?php echo L('直客奖励'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_member" value="member" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_member" class="ttt"><?php echo L('员工奖励'); ?></label>
                                                    </div>
													-->
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("等级");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                    <select class="form-control m-b" id="LEVEL" name="LEVEL">
														<?php
														echo '<option value="0">' , L('不限') , '</option>';
                                                        for($i=1;$i<=C('MAX_LEVEL');$i++){
                                                            echo '<option value="' , $i , '">' , L($i.'级') , '</option>';
                                                        }
                                                        ?> 
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("MT分组");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                    <select name="GROUP_NAME[]" id="GROUP_NAME" data-placeholder="<?php echo L('请选择分组'); ?>" class="chosen-select" multiple>
                                                        <option value="all_group"><?php echo L('全部'); ?></option>
                                                         <?php
                                                         foreach($groups as $j=>$vo){
                                                         ?>
                                                        <option value="<?php echo $vo['group']; ?>"><?php echo $vo['group']; ?></option>
                                                        <?php
                                                         }
                                                         ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("交易种类");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                    <select name="SYMBOL_TYPE[]" id="SYMBOL_TYPE"  data-placeholder="<?php echo L('请选择种类'); ?>" class="chosen-select"  multiple>
                                                        <option value='all_symbol'><?php echo L('全部'); ?></option>
                                                        <?php
                                                        foreach($types as $j=>$vo){
                                                        ?>
                                                             <option value="<?php echo $vo['type_name']; ?>"><?php echo $vo['type_name']; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("统计团队");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="radio radio-info radio-inline">
                                                        <input type="radio" id="GROUP_TYPE_1" value="1" name="GROUP_TYPE">
                                                        <label for="GROUP_TYPE_1" class="ttt"><?php echo L('直接下级'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="GROUP_TYPE_2" value="2" name="GROUP_TYPE">
                                                        <label for="GROUP_TYPE_2" class="ttt"><?php echo L('伞下'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("平仓时间");?>：</label>
                                                <div class="col-sm-2 col-xs-8 mt-sm-1">
													<div class="input-group">
														<input type="text" class="form-control layer-date" name="TC_DATE_S" id="TC_DATE_S" placeholder="开始日期">
														<div class="input-group-prepend">
															<span class="input-group-text">~</span>
														</div>
														<input type="text" class="form-control layer-date" name="TC_DATE_E" id="TC_DATE_E" placeholder="结束日期">
													</div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("达到手数");?>：</label>
                                                <div class="col-sm-1 col-xs-8 mt-sm-1">
													<div class="input-group">
														<input type="text" class="form-control" name="LIMIT_MIN_SS" id="LIMIT_MIN_SS" placeholder="">
														<div class="input-group-prepend">
															<span class="input-group-text"><?php echo L("手");?></span>
														</div>
													</div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("返佣标准");?>：</label>
                                                <div class="col-sm-8 form-inline">
                                                    <select name="CAL_TYPE_AGENT" id="CAL_TYPE_AGENT" class="form-control mr-sm-1">
                                                            <option value="FIXED">$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                            <option value="SCALE">%，<?php echo L('交易量百分比'); ?></option>
                                                            <!--<option value="POINT">pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>-->
                                                            <option value="WIN">%，<?php echo L('盈利百分比'); ?></option>
                                                    </select>                                                    
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="CAL_NUM_AGENT_SPAN">$</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="CAL_NUM_AGENT" id="CAL_NUM_AGENT" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="button" id="savesetting" class="btn btn-primary"><?php echo L("确认");?></button>
                                                    <button onclick="window.history.back()" type="button" class="btn btn-light"><?php echo L("返回");?></button>
                                                </div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

					</div>






		<?php
        require_once('footer.php');
        ?>
        
        <script src="/assets/js/layer/layer.js"></script>
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/select2.min.js"></script> 
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>
        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>
        <script src="/assets/js/suggest/bootstrap-suggest.min.js"></script>


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
			$(".layer-date").datepicker({
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
			$(document).on("click","input[name='outmoneytype']",function(){
			
			});
			
			$(".chosen-select").chosen( {width: "100%"});
			
			var modeltype = '';
			$('input[name=MODEL_TYPE]').click(function(){
				modeltype = $(this).val();
				if(modeltype == 'direct'){
					//直客
				}else{
					//代理或员工
				}
			});
			
			$('#CAL_TYPE_AGENT').change(function(){
				var calId = $(this).attr('id').replace('_TYPE_','_NUM_') + '_SPAN';
				var val = $(this).val();
				if(val == 'SCALE' || val == 'WIN'){
					$('#' + calId).html('%');
				}else{
					$('#' + calId).html('$');
				}
			});
			
			 $(document).on("click","#savesetting",function(){
				$(this).attr('disabled', "disabled");
				var _this=$(this);
				var form = $(this).closest('form');
				var url = "?clause=saveinfo&id=<?php echo $Id;?>";
				$.post(url, form.serialize(), function(data) {
					layer.msg(data.info);
					if (data.status) {
						setTimeout(function(){
							var url = '?modeltype=' + modeltype;
							document.location.href = url;
						},800);
					}else{
						_this.removeAttr("disabled");
					}
				}, 'json')
			});
			
			//---------------------------------------------------------
			
			//去除数组中的空值
			function trimSpace(array){
				 for(var i = 0 ;i<array.length;i++){
					 if(array[i] == "" || typeof(array[i]) == "undefined"){
						  array.splice(i,1);
						  i= i-1;
					 }
				 }
				 return array;
			}

			function chose_mult_set_ini(select_, values) {
				var arr = values.split(',');
				var length = arr.length;
				var value = '';
				newarr = trimSpace(arr);
				$(select_).val(newarr);
				$(select_).trigger("chosen:updated");
			}
			
			<?php
			if($Id > 0){
			?>
				$('#f_title').val('<?php echo $rs['f_title']?>');
				$('#MODEL_TYPE_<?php echo $rs['MODEL_TYPE']?>').click();
				$('#LEVEL').val('<?php echo $rs['LEVEL']?>');
				chose_mult_set_ini('#GROUP_NAME','<?php echo str_replace('\\','\\\\',$rs['GROUP_NAME']);?>');
				chose_mult_set_ini('#SYMBOL_TYPE','<?php echo $rs['SYMBOL_TYPE'];?>');
				$('#GROUP_TYPE_<?php echo $rs['GROUP_TYPE']?>').click();
				$('#TC_DATE_S').val('<?php echo date('Y-m-d',strtotime($rs['TC_DATE_S']))?>');
				$('#TC_DATE_E').val('<?php echo date('Y-m-d',strtotime($rs['TC_DATE_E']))?>');
				$('#LIMIT_MIN_SS').val('<?php echo $rs['LIMIT_MIN_SS']?>');
				<?php if(strlen($rs['CAL_TYPE_AGENT'])){echo '$("#CAL_TYPE_AGENT").val("' , $rs['CAL_TYPE_AGENT'] , '");$("#CAL_TYPE_AGENT").change();';} ?>
				$('#CAL_NUM_AGENT').val('<?php echo $rs['CAL_NUM_AGENT'] * 1;?>');
				<?php
			}else{
			?>
				$('#MODEL_TYPE_agent').click();
				<?php
			}
			?>
		</script>


    </body>
</html>
