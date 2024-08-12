<?php
$LoadCSSArr = array();
require_once('header.php');

$list = $DB->getDTable("select * from `t_symbol` where `type` = '{$Id}' and server_id = '{$DRAdmin['server_id']}'");
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal label{ display:inline-block;}
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
                                    <h4 class="page-title"><?php echo L('交易种类同步') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post" action="?clause=savesysinfo&id=<?php echo $Id;?>">
                                        	<div class="form-inline">
                                        	<?php
                                            foreach($list as $key=>$val){
											?>
                                            <div class="form-group col-sm-3">
                                                <label class="col-sm-6 control-label"><?php echo $val['symbol'];?>:</label>
                                                <div class="col-sm-6"><div class="form-control-static"><input name="stdsymbol_<?php echo $val['id'];?>" type="text" value="<?php echo $val['std_symbol'];?>" class="form-control" placeholder="<?php echo L("请输入标准货币对");?>"></div></div>
                                            </div>
                                            <?php
											}
											?>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 control-label">&nbsp;</label>
                                                <div class="col-sm-9"><div class="form-control-static">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("保存");?></button>
                                                	<button onclick="window.history.back();" type="button" class="btn btn-light"><?php echo L("返回");?></button>
                                                </div></div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                        
                        

                    </div> <!-- container -->


		<?php
        require_once('footer.php');
        ?>

    </body>
</html>
