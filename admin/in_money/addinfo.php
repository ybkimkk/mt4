<?php
$LoadCSSArr = array();
require_once('header.php');
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
									echo L('支付');
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
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("入金帐号");?>：</label>
                                                <div class="col-sm-8">
                                                    <select name="inlogin" id="inlogin" class='form-control' >
														<?php
														$map = "where a.member_id = '{$DRAdmin['id']}' and a.mtserver = '{$DRAdmin['server_id']}' and b.status = 1 and a.status > 0";
														$inloginArr = $DB->getDTable("select a.*,b.nickname,b.phone,b.email from t_member_mtlogin a inner join t_member b on a.member_id=b.id {$map} order by a.create_time desc");
                                                        foreach($inloginArr as $key=>$val){
                                                            echo '<option value="' , $val['loginid'] , '">';
                                                            echo $val['loginid'];
                                                            echo '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("支付方式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1">
                                                <?php
												$paysArr = $DB->getDTable("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' order by sort asc) a left join t_pay_currency b on a.f_currencyId = b.id");
												foreach($paysArr as $key=>$val){
													if($val['f_pa'] == 'auto'){
														$autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$val['f_symbolsER']}'");
														if($autoPa){
															$val['f_fixedER'] = $autoPa['BID'];
															//ASK
														}
													}
													echo '<div class="radio radio-info radio-inline">
                                                            <input type="radio" feePre="0" f_erAlgo="' , $val['f_erAlgo'] , '" f_symbolsER="' , $val['f_symbolsER'] , '" f_fixedER="' , $val['f_fixedER'] , '" f_ers="' , $val['f_ers'] , '" f_pa="' , $val['f_pa'] , '" f_title="' , $val['f_title'] , '" id="inlineRadio_' , $val['PayCode'] , '" numberlist="' , $val['number_rmb'] , '"  value="' , $val['Id'] , '" rel="' , $val['Id'] , '" lang="' , $val['lang'] , '" maxpay="' , $val['maxpaynumber'] , '" name="inmoneytype"  role="' , $val['access_group'] , '">
                                                            <label for="inlineRadio_' , $val['PayCode'] , '" class="ttt" >' , $val['PayName'] , '</label>
                                                        </div>';
												}
												?>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("入金金额");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8">
                                                    <input name="inmoney" id="inmoney" type="text" onblur="calInMoney();" onkeyup="calInMoney();" class="form-control" value="" placeholder="<?php echo L('请输入金金额'); ?>">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("当前汇率");?>：</label>
                                                <div class="col-sm-8">
                                                    <input name="currER" id="currER" type="text" class="form-control" value="???" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("支付金额");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8">
                                                    <input name="inmoney_cal" id="inmoney_cal" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("手续费");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8">
                                                    <input name="inmoney_fee" id="inmoney_fee" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row" style="display:none;">
                                                <label class="col-sm-2"><?php echo L("总共需支付");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8">
                                                    <input name="inmoney_sum" id="inmoney_sum" type="text" class="form-control" value="" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("去支付");?></button>
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
			$(document).on("click","input[name='inmoneytype']",function(){
			//$("input[name='inmoneytype']").click(function(){
				var _this = $(this);
				$('.pay_pa').html(' (' + _this.attr('f_title') + ' ' + _this.attr('f_pa') + ')');
				
				calInMoney();
			});
			
			function calInMoney(){
				var inmoney = $('#inmoney').val();
				inmoney = inmoney.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1');
				$('#inmoney').val(inmoney);
				inmoney = CDecimal(inmoney,2);
				
				var inmoneytype = $('input[name=inmoneytype]:checked');
				if(inmoneytype.length <= 0){
					return;
				}
				
				var currER = CDecimal(inmoneytype.attr('f_fixedER'), 5);
				$('#currER').val(currER);
							
				var f_erAlgo = inmoneytype.attr('f_erAlgo');
				var inmoney_cal = 0;
				if(f_erAlgo == '÷'){
					inmoney_cal = inmoney / currER;
				}else{
					inmoney_cal = inmoney * currER;
				}
				inmoney_cal = CDecimal(inmoney_cal,2);
				$('#inmoney_cal').val(inmoney_cal);

				var feePre = CDecimal(inmoneytype.attr('feePre'), 3);
				var inmoney_fee =  CDecimal(inmoney_cal * feePre,2);
				$('#inmoney_fee').val(inmoney_fee);
				
				<?php
				if(C('DEPOSIT_IN_INTEGER') == '0'){
					//判断入金不允许小数，0=不允许
					echo 'var inmoney_sum = CDecimal(inmoney_cal + inmoney_fee,0);';
				}else{
					echo 'var inmoney_sum = CDecimal(inmoney_cal + inmoney_fee,2);';
				}
				?>
				$('#inmoney_sum').val(inmoney_sum);
			}
		</script>


    </body>
</html>
