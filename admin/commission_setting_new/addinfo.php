<?php
$LoadCSSArr = array();
require_once('header.php');

if($Id > 0){
	$rs = $DB->getDRow("select * from t_sale_setting_new where ID = '" . $Id . "' and STATUS in (1,3) and SERVER_ID = '{$DRAdmin['server_id']}'");
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
                                                <label class="col-sm-2"><?php echo L("返佣模式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_agent" value="agent" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_agent" class="ttt"><?php echo L('代理奖励'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_direct" value="direct" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_direct" class="ttt"><?php echo L('直客奖励'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_TYPE_member" value="member" name="MODEL_TYPE">
                                                        <label for="MODEL_TYPE_member" class="ttt"><?php echo L('员工奖励'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2" id="LEVEL_LABEL"><?php echo L("等级");?>：</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control m-b" id="LEVEL" name="LEVEL" >
														<?php
                                                        for($i=1;$i<=C('MAX_LEVEL');$i++){
                                                            echo '<option value="' , $i , '">' , L($i.'级') , '</option>';
                                                        }
                                                        ?> 
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("交易种类");?>：</label>
                                                <div class="col-sm-8">
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
                                                <label class="col-sm-2"><?php echo L("MT分组");?>：</label>
                                                <div class="col-sm-8">
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
                                            <div class="form-group row" id="BONUS_TYPE_DIV">
                                                <label class="col-sm-2"><?php echo L("返佣模式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="radio radio-info radio-inline">
                                                        <input type="radio" id="BONUS_TYPE_0" value="0" name="BONUS_TYPE">
                                                        <label for="BONUS_TYPE_0" class="ttt"><?php echo L('内佣'); ?>（<?php echo L('根据等级或层级进行相关运算'); ?>）</label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="BONUS_TYPE_1" value="1" name="BONUS_TYPE">
                                                        <label for="BONUS_TYPE_1" class="ttt"><?php echo L('外佣'); ?>（<?php echo L('符合等级或层级直接获得佣金，没有其它运算'); ?>）</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("直接客户返佣标准");?><span style="color:#ff0000">(A)</span>：</label>
                                                <div class="col-sm-8 form-inline">
                                                    <select name="CAL_TYPE_ZK" id="CAL_TYPE_ZK" class="form-control mr-sm-1">
                                                            <option value="FIXED">$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                            <option value="SCALE">%，<?php echo L('交易量百分比'); ?></option>
                                                            <option value="POINT">pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                            <option value="WIN">%，<?php echo L('盈利百分比'); ?></option>
                                                    </select>                                                    
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="CAL_NUM_ZK_SPAN">$</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="CAL_NUM_ZK" id="CAL_NUM_ZK" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="CAL_JC_DIV">
                                                <label class="col-sm-2"><?php echo L("级差");?>：</label>
                                                <div class="col-sm-8 form-inline">
                                                    <select name="CAL_TYPE_JC" id="CAL_TYPE_JC" class="form-control mr-sm-1">
                                                            <option value="FIXED">$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                            <option value="SCALE">%，<?php echo L('交易量百分比'); ?></option>
                                                            <option value="POINT">pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                            <option value="WIN">%，<?php echo L('盈利百分比'); ?></option>
                                                    </select>                                                    
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="CAL_NUM_JC_SPAN">$</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="CAL_NUM_JC" id="CAL_NUM_JC" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="CAL_JJ_DIV">
                                                <label class="col-sm-2"><?php echo L("间接客户返佣标准");?> <span style="color:#ff0000">(B)</span>：</label>
                                                <div class="col-sm-8">
                                                	<div class="form-inline mb-sm-1">
                                                        &gt; <?php echo L("下级");?>：
                                                        <select name='CAL_TYPE_JJ_2' id="CAL_TYPE_JJ_2" class='form-control mr-sm-1'>
                                                                <option value='FIXED'>$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='SCALE'>%，<?php echo L('交易量百分比'); ?></option>
                                                                <option value='POINT'>pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='WIN'>%，<?php echo L('盈利百分比'); ?></option>
                                                        </select>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="CAL_NUM_JJ_2_SPAN">$</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_JJ_2" id="CAL_NUM_JJ_2" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-inline mb-sm-1">
                                                        = <?php echo L("下级");?>：
                                                        <select name='CAL_TYPE_JJ_1' id="CAL_TYPE_JJ_1" class='form-control mr-sm-1'>
                                                                <option value='FIXED'>$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='SCALE'>%，<?php echo L('交易量百分比'); ?></option>
                                                                <option value='POINT'>pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='WIN'>%，<?php echo L('盈利百分比'); ?></option>
                                                        </select>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="CAL_NUM_JJ_1_SPAN">$</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_JJ_1" id="CAL_NUM_JJ_1" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-inline mb-sm-1">
                                                        &lt; <?php echo L("下级");?>：
                                                        <select name='CAL_TYPE_JJ_0' id="CAL_TYPE_JJ_0" class='form-control mr-sm-1'>
                                                                <option value='FIXED'>$，<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='SCALE'>%，<?php echo L('交易量百分比'); ?></option>
                                                                <option value='POINT'>pip，<?php echo L('点值'); ?>/<?php echo L('每手'); ?>/<?php echo L('金额'); ?></option>
                                                                <option value='WIN'>%，<?php echo L('盈利百分比'); ?></option>
                                                        </select>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="CAL_NUM_JJ_0_SPAN">$</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_JJ_0" id="CAL_NUM_JJ_0" placeholder="<?php echo L('请输入返佣标准数值'); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="CAL_GROUP_DIV">
                                                <label class="col-sm-2"><?php echo L("团队返佣标准");?> <span style="color:#ff0000">(C)</span>：</label>
                                                <div class="col-sm-8">
                                                	<div class="form-inline mb-sm-1">
                                                        &gt; <?php echo L("下级");?>：                                                    
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_GROUP_2" id="CAL_NUM_GROUP_2" placeholder="<?php echo L('获取团队返佣总金额的比例'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-inline mb-sm-1">
                                                        = <?php echo L("下级");?>：
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_GROUP_1" id="CAL_NUM_GROUP_1" placeholder="<?php echo L('获取团队返佣总金额的比例'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="form-inline mb-sm-1">
                                                        &lt; <?php echo L("下级");?>：
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                            <input type="text" class="form-control" name="CAL_NUM_GROUP_0" id="CAL_NUM_GROUP_0" placeholder="<?php echo L('获取团队返佣总金额的比例'); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--
                                            <div class="form-group row" id="CAL_OTHER_DIV">
                                                <label class="col-sm-2"><?php echo L("其它");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_JG_CAL_TYPE_all" value="all" name="MODEL_JG_CAL_TYPE">
                                                        <label for="MODEL_JG_CAL_TYPE_all" class="ttt"><?php echo str_jg_cal_type('all'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_JG_CAL_TYPE_jj" value="jj" name="MODEL_JG_CAL_TYPE">
                                                        <label for="MODEL_JG_CAL_TYPE_jj" class="ttt"><?php echo str_jg_cal_type('jj'); ?></label>
                                                    </div>
                                                    <div class="radio radio-info radio-inline">
                                                        <input type="radio" id="MODEL_JG_CAL_TYPE_group" value="group" name="MODEL_JG_CAL_TYPE">
                                                        <label for="MODEL_JG_CAL_TYPE_group" class="ttt"><?php echo str_jg_cal_type('group'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            -->
                                            <div class="form-group row" id="CAL_OTHER_DIV">
                                                <label class="col-sm-2"><?php echo L("提示");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
													<?php echo L('1、<span style="color:#ff0000">(B)</span> 与 <span style="color:#ff0000">(C)</span>，有设置便有返（按达到的条件返）；');?><br>
                                                    <?php echo L('2、<span style="color:#ff0000">(C)</span> 的计算来自 <span style="color:#ff0000">(A)</span>，其它返佣收入不参与计算。');?>
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

		<script>
			$(document).on("click","input[name='outmoneytype']",function(){
			
			});
			
			$(".chosen-select").chosen( {width: "100%"});
			
			var modeltype = '';
			$('input[name=MODEL_TYPE]').click(function(){
				modeltype = $(this).val();
				if(modeltype == 'direct'){
					//直客
					
					$('#BONUS_TYPE_DIV,#CAL_JC_DIV,#CAL_JJ_DIV,#CAL_GROUP_DIV,#CAL_OTHER_DIV').hide();
					$('#LEVEL_LABEL').html('<?php echo L("层级");?>：');
					
					//直客有0级=自己拿佣
					var czLv0Option = $("#LEVEL").find("option[value='0']");
					if(czLv0Option.length == 0){
						$("#LEVEL").prepend("<option value='0'><?php echo L('0级');?></option>");
					}
				}else{
					$('#BONUS_TYPE_DIV,#CAL_JC_DIV,#CAL_JJ_DIV,#CAL_GROUP_DIV,#CAL_OTHER_DIV').show();
					$('#LEVEL_LABEL').html('<?php echo L("等级");?>：');
					
					//非直客，必须从1级开始
					$("#LEVEL option[value='0']").remove();
				}
			});
			
			var bonustype = '';
			$('input[name=BONUS_TYPE]').click(function(){
				bonustype = $(this).val();
				if(bonustype == '1'){
					//外佣
					
					$('#CAL_JC_DIV,#CAL_JJ_DIV,#CAL_GROUP_DIV,#CAL_OTHER_DIV').hide();
				}else{
					$('#CAL_JC_DIV,#CAL_JJ_DIV,#CAL_GROUP_DIV,#CAL_OTHER_DIV').show();
				}
			});			
			
			$('#CAL_TYPE_ZK,#CAL_TYPE_JC,#CAL_TYPE_JJ_2,#CAL_TYPE_JJ_1,#CAL_TYPE_JJ_0').change(function(){
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
							var url = '?modeltype=' + modeltype + '&bonustype=' + bonustype;
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
				$('#LEVEL').val('<?php echo $rs['LEVEL']?>');
				chose_mult_set_ini('#SYMBOL_TYPE','<?php echo $rs['SYMBOL_TYPE'];?>');
				chose_mult_set_ini('#GROUP_NAME','<?php echo str_replace('\\','\\\\',$rs['GROUP_NAME']);?>');
				<?php if(strlen($rs['CAL_TYPE_ZK'])){echo '$("#CAL_TYPE_ZK").val("' , $rs['CAL_TYPE_ZK'] , '");$("#CAL_TYPE_ZK").change();';} ?>
				$('#CAL_NUM_ZK').val('<?php echo $rs['CAL_NUM_ZK'] * 1;?>');
				<?php if(strlen($rs['CAL_TYPE_JC'])){echo '$("#CAL_TYPE_JC").val("' , $rs['CAL_TYPE_JC'] , '");$("#CAL_TYPE_JC").change();';} ?>
				$('#CAL_NUM_JC').val('<?php echo $rs['CAL_NUM_JC'] * 1;?>');
				<?php if(strlen($rs['CAL_TYPE_JJ_2'])){echo '$("#CAL_TYPE_JJ_2").val("' , $rs['CAL_TYPE_JJ_2'] , '");$("#CAL_TYPE_JJ_2").change();';} ?>
				$('#CAL_NUM_JJ_2').val('<?php echo $rs['CAL_NUM_JJ_2'] * 1;?>');
				<?php if(strlen($rs['CAL_TYPE_JJ_1'])){echo '$("#CAL_TYPE_JJ_1").val("' , $rs['CAL_TYPE_JJ_1'] , '");$("#CAL_TYPE_JJ_1").change();';} ?>
				$('#CAL_NUM_JJ_1').val('<?php echo $rs['CAL_NUM_JJ_1'] * 1;?>');
				<?php if(strlen($rs['CAL_TYPE_JJ_0'])){echo '$("#CAL_TYPE_JJ_0").val("' , $rs['CAL_TYPE_JJ_0'] , '");$("#CAL_TYPE_JJ_0").change();';} ?>
				$('#CAL_NUM_JJ_0').val('<?php echo $rs['CAL_NUM_JJ_0'] * 1;?>');
				$('#CAL_NUM_GROUP_2').val('<?php echo $rs['CAL_NUM_GROUP_2'] * 1;?>');
				$('#CAL_NUM_GROUP_1').val('<?php echo $rs['CAL_NUM_GROUP_1'] * 1;?>');
				$('#CAL_NUM_GROUP_0').val('<?php echo $rs['CAL_NUM_GROUP_0'] * 1;?>');
				
				$('#MODEL_TYPE_<?php echo $rs['MODEL_TYPE']?>').click();
				<?php
				if($rs['MODEL_TYPE'] != 'direct'){
					echo '$("#BONUS_TYPE_' , $rs['BONUS_TYPE'] , '").click();';
					//echo '$("#MODEL_JG_CAL_TYPE_' , $rs['MODEL_JG_CAL_TYPE'] , '").click();';
				}
			}
			?>
		</script>


    </body>
</html>
