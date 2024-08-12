<?php
$LoadCSSArr = array();
require_once('header.php');

$login = FGetStr('login');

$DRChk = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$login}' and status > 0 and member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['server_id']}'");
if(!$DRChk){
	FJS_AB('出金帐号错误');
}
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
									echo L('我要出金');
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
                                        <form class="form-horizontal" action="?" method="get" id="signupForm">
                                        	<input type="hidden" name="clause" value="step2">
                                            <input type="hidden" name="login" value="<?php echo $login;?>">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金帐号");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $login; ?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金金额");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8">
                                                    <input name="outmoney" id="outmoney" type="text" onblur="calInMoney();" onkeyup="calInMoney();" class="form-control" value="" placeholder="<?php echo L('请输入出金额，单位：美元'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金方式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                <?php
												$paysArr = $DB->getDTable("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedEROut,b.f_symbolsER,b.f_erAlgo from (select * from t_out_config where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' order by sort asc) a left join t_pay_currency b on a.f_currencyId = b.id");
												foreach($paysArr as $key=>$val){
													if($val['f_pa'] == 'auto'){
														$autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$val['f_symbolsER']}'");
														if($autoPa){
															$val['f_fixedEROut'] = $autoPa['BID'];
															//ASK
														}
													}
													echo '<div class="radio radio-info radio-inline">
                                                            <input type="radio" feePre="' , $val['f_fee'] , '" f_erAlgo="' , $val['f_erAlgo'] , '" f_symbolsER="' , $val['f_symbolsER'] , '" f_fixedEROut="' , $val['f_fixedEROut'] , '" f_ers="' , $val['f_ers'] , '" f_pa="' , $val['f_pa'] , '" f_title="' , $val['f_title'] , '" id="inlineRadio_' , $val['PayCode'] , '" numberlist="' , $val['number_rmb'] , '"  value="' , $val['Id'] , '" rel="' , $val['Id'] , '" lang="' , $val['lang'] , '" maxpay="' , $val['maxpaynumber'] , '" name="outmoneytype"  role="' , $val['access_group'] , '">
                                                            <label for="inlineRadio_' , $val['PayCode'] , '" class="ttt">（' , $val['f_title'] , ' ' , $val['f_pa'] , '），' , L('渠道') , '：' , L($val['PayName']) , '</label>
                                                        </div>';
												}
												
												if(chk_in_access('MT转账')){
													echo '<div class="radio radio-info radio-inline">
															<input type="radio" id="inlineRadio_czmt20211212" value="czmt20211212" name="outmoneytype">
															<label for="inlineRadio_czmt20211212" class="ttt">' , L('MT转账') , '</label>
														</div>';
												}
												?>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="fee01" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("手续费");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8">
                                                    <input name="outmoney_fee" id="outmoney_fee" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="fee02" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("一共扣除");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8">
                                                    <input name="outmoney_sum" id="outmoney_sum" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("当前汇率");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="currER" id="currER" type="text" class="form-control" value="???" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("到账金额");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8">
                                                    <input name="outmoney_account" id="outmoney_account" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row" id="czmt01" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("转入MT账户");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="mtlogin" id="mtlogin" type="text" class="form-control" value="">
                                                </div>
                                            </div>
                                            <div class="form-group row" id="czmt02" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("确认转入MT账户");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="mtlogin2" id="mtlogin2" type="text" class="form-control" value="">
                                                </div>
                                            </div>
                                            <div class="form-group row" id="czmt03" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("转入MT账户名称");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="forwordmtlogin" id="forwordmtlogin" type="text" class="form-control" value="???" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="button" id="submitBtn" class="btn btn-primary" onclick="disabled_buttons()"><?php echo L("确认");?></button>
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

		<script>
			/*window.onunload = function(){};
			window.onload = function(){
				$('button').attr("disabled",false);
				$('#submitBtn').html('<?php echo L('确认');?>');
			};*/

			function disabled_buttons(){
				$('button').attr("disabled",true);
				$('#submitBtn').html("<?php echo L('申请提交中');?>");
				$('#signupForm').submit();
			}
		
			function chk_mtlogin(){
				var mt_login=$('#mtlogin').val();
				var mt_login2=$('#mtlogin2').val();
				if(mt_login!=mt_login2){
					$('#forwordmtlogin').val("<?php echo L("两次输入的账号不一致"); ?>");
				}else{
					$.ajax({
						type:"post",
						url:"?clause=checkmtlogin",
						data:"mt_login2="+mt_login2+"&mt_login="+mt_login,
						dataType:"json",
						success:function(data){ 
							$('#forwordmtlogin').val(data.msg);
						}
					})
				}
			}
			$('#mtlogin2').keyup(function(){
				chk_mtlogin();
			}).blur(function(){
				chk_mtlogin();
			});
			
		
			$(document).on("click","input[name='outmoneytype']",function(){
			//$("input[name='outmoneytype']").click(function(){
				var _this = $(this);
				if(_this.val() == 'czmt20211212'){
					$('#fee01,#fee02').hide();
					
					$('#czmt01,#czmt02,#czmt03').show();
					$('#czmt03').val('???');
				}else{
					$('.pay_pa').html(' (' + _this.attr('f_title') + ' ' + _this.attr('f_pa') + ')');
					
					var feePre = CDecimal(_this.attr('feePre'), 3);
					if(feePre > 0){
						$('#fee01,#fee02').show();
					}else{
						$('#fee01,#fee02').hide();
					}
					
					$('#czmt01,#czmt02,#czmt03').hide();
				}
				calInMoney();
			});
			
			function calInMoney(){
				var outmoney = $('#outmoney').val();
				outmoney = outmoney.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1');
				$('#outmoney').val(outmoney);
				outmoney = CDecimal(outmoney,2);
				
				var outmoneytype = $('input[name=outmoneytype]:checked');
				if(outmoneytype.length <= 0){
					return;
				}
				
				if(outmoneytype.val() == 'czmt20211212'){
					$('#currER').val('1');
					$('#outmoney_account').val(outmoney);
					$('.pay_pa').html('');
					return;
				}
				
				var currER = CDecimal(outmoneytype.attr('f_fixedEROut'), 5);
				$('#currER').val(currER);
							
				var f_erAlgo = outmoneytype.attr('f_erAlgo');
				var outmoney_account = 0;
				if(f_erAlgo == '÷'){
					outmoney_account = outmoney / currER;
				}else{
					outmoney_account = outmoney * currER;
				}
				<?php
				if(C('DEPOSIT_IN_INTEGER') == '0'){
					//判断入金不允许小数，0=不允许
					echo 'outmoney_account = CDecimal(outmoney_account,0);';
				}else{
					echo 'outmoney_account = CDecimal(outmoney_account,2);';
				}
				?>
				$('#outmoney_account').val(outmoney_account);

				var feePre = CDecimal(outmoneytype.attr('feePre'), 3);
				var outmoney_fee =  CDecimal(outmoney * feePre,2);
				$('#outmoney_fee').val(outmoney_fee);
				
				var outmoney_sum = CDecimal(outmoney + outmoney_fee,2);
				$('#outmoney_sum').val(outmoney_sum);
			}
		</script>


    </body>
</html>
