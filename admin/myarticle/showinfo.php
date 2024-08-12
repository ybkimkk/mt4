<?php
$LoadCSSArr = array();
require_once('header.php');


$DRInfo = $DB->getDRow("select * from `t_article` where id = '{$Id}' and status = 1 and server_id = '{$DRAdmin['server_id']}' and f_key = ''");

$DRInfo1 = get_lang_otherset_drow('-_news_-'.$Id,$CurrLangName,$DRAdmin['server_id'],1);
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
                                    <h4 class="page-title"><?php echo L('通知信息') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" method="post" action="?clause=saveinfo&id=<?php echo $Id;?>">
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static" style="font-weight:bold;"><?php echo $DRInfo1['f_title'];?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static"><?php echo date('Y-m-d H:i:s',$DRInfo['create_time']);?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static"><?php echo $DRInfo1['f_val'];?></div></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12"><div class="form-control-static">
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
