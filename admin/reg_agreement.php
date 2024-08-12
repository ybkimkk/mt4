<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

$reg_prov1 = get_lang_otherset_drow('-_news_-'.$Id,$CurrLangName,FGetInt('sid'),1);
if(!$reg_prov1){
	FRedirect('/');
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $reg_prov1['f_title'] , ' - ' , $mt4server['mt4_name'] , ' - ' , $webConfig['f_title'];?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="<?php echo $webConfig['f_description'];?>" name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="/favicon.ico">
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/cz.css" rel="stylesheet" type="text/css" />
		<?php
		$czWebSkinName = C('APP_TEMP_SRC');
		if(strlen($czWebSkinName) > 0 && $czWebSkinName != 'default'){
			echo '<link href="/assets/css/skin/' , $czWebSkinName , '.css" rel="stylesheet" type="text/css">';
		}
		?>
    </head>
    <body class="authentication-bg">

        <?php
        $reg_prov = $DB->getDTable("select * from `t_article` where f_key = 'reg_prov' and server_id = '{$mt4server['id']}' and status = 1");
        ?>

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12" id="regWapBox">
                        <div class="card">
                            <!-- Logo-->
                            <div class="card-header pt-3 pb-3 text-center bg-primary bg-primary-rlf-cz">
                                <a href="#nolink">
                                    <span><img src="<?php echo C('WEB_LOGO_FILE');?>" alt="" height="50"></span>
                                </a>
                            </div>

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <h4 class="text-dark-50 text-center mt-0 font-weight-bold"><?php echo $reg_prov1['f_title'];?></h4>
                                    <p class="text-muted mb-4"><?php
									$ci = 0;
                                    foreach($LangNameList['list'] as $key=>$val){
										if($ci > 0){
											echo '&nbsp; | &nbsp;';
										}
										echo '<a href="set_lang.php?lang=' , $key , '">' , $val['title'] , '</a>';
										$ci++;
									}
									?></p>
                                </div>
                                
                                <?php
								echo '<div id="regProvision">';
									 echo '<div style="font-size:14px;">';
									 echo  $reg_prov1['f_val'];
									 echo '</div>';
								 echo '</div>';
								?>

                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->
						<!--
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-muted">Already have account? <a href="login.php" class="text-muted ml-1"><b>Log In</b></a></p>
                            </div>
                        </div>
                        -->
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

		<!--
        <footer class="footer footer-alt">&nbsp;</footer>
        -->

        <!-- App js -->
        <script src="/assets/js/app.min.js"></script>
        
        <script src="/assets/js/ajaxupload.3.5.js"></script>
        <script src="/assets/js/layer/layer.js"></script>        
        
        <script src="/assets/js/validate/jquery.validate.min.js"></script>

    </body>
</html>
