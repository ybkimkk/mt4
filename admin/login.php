<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

//demomt4server = D('Mt4_server')->where(array('status' => 1, 'real' => 0, 'default_open_svr' => 1))->find();
//realmt4server = D('Mt4_server')->where(array('status' => 1, 'real' => 1, 'default_open_svr' => 1))->find();

if ($Clause == 'chklogin') {
    if (time() - session('lastpostlogin') <= 2) {
        ajaxReturn(0, L('您提交的太快了'), 0);
    }
    session('lastpostlogin', time());

    $server_id = FPostInt('server_id');
    $account = FPostStr('account');
    $password = FPostStr('password');
    $verify = FPostStr('verify');

    $server_id <= 0 && ajaxReturn(0, L('登录MT服务器错误'), 0);
    strlen($account) <= 0 && ajaxReturn(0, L('账号不能为空'), 0);
    strlen($password) <= 0 && ajaxReturn(0, L('密码不能为空'), 0);
    $verify != session('VCode') && ajaxReturn(session("login_error_count"), L('验证码不正确'), 0);

    $phonechecked = explode(',', $DB->getField("select `value` from t_config where `name` = 'USER_PHONE_CHECK_LIST'"));
    if (in_array($result['data']['email'], $phonechecked) && $phonechecked[0] && $result['data']['email']) {
        if (!$phonecode) {
            ajaxReturn(0, L('请输入手机验证码'), 12);
        }
        if (md5($phonecode) != md5(session("phonecheck" . md5($_POST['account'])))) {
            ajaxReturn(0, L('请输入正确的手机验证码'), 0);
        }
    }


    //读取服务
    $regServer = $DB->getDRow("select * from t_mt4_server where `status` = 1 and `id` = '{$server_id}'");
    if (!$regServer) {
        ajaxReturn(0, L('无可用MT服务器开户.请联系管理员确认'), 0);
    }
    $DRAdmin['server_id'] = $server_id;


    //执行登录（里面会判断是否邮箱、手机号、或mt4帐号登录）
    $result = api('gold://Member/checkLogin', array('account' => $account, 'password' => $password, 'server_id' => $server_id));

    //$result['status'] && member_info($result['data']);
    $result['info'] = L($result['info']);

    if ($result['status'] == 1) {
        ajaxReturn(0, L($result['info']), 1);
    } else {
        ajaxReturn(0, L($result['info']), 0);
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title><?php echo $webConfig['f_title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $webConfig['f_description']; ?>" name="description"/>
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css"/>
    <?php
    $czWebSkinName = C('APP_TEMP_SRC');
    if (strlen($czWebSkinName) > 0 && $czWebSkinName != 'default') {
        echo '<link href="/assets/css/skin/', $czWebSkinName, '.css" rel="stylesheet" type="text/css">';
    }
    ?>
</head>

<body class="authentication-bg">
<style>
    .verify-msg {
        color: #ff0000;
        padding-bottom: 10px;
    }
</style>
<div class="account-pages mt-5 mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card">

                    <!-- Logo -->
                    <div class="card-header pt-3 pb-3 text-center bg-primary bg-primary-rlf-cz">
                        <a href="#nolink">
                            <span><img src="<?php echo C('WEB_LOGO_FILE'); ?>" alt="" height="50"></span>
                        </a>
                    </div>

                    <div class="card-body p-4">

                        <div class="text-center w-75 m-auto">
                            <h4 class="text-dark-50 text-center mt-0 font-weight-bold"><?php echo L('登陆'); ?></h4>
                            <!--<p class="text-muted mb-4">
                            <?php
                            $ci = 0;
                            foreach ($LangNameList['list'] as $key => $val) {
                                if ($ci > 0) {
                                    echo '&nbsp; | &nbsp;';
                                }
                                echo '<a href="set_lang.php?lang=', $key, '">', $val['title'], '</a>';
                                $ci++;
                            }
                            ?></p>-->

                            <div class="dropdown notification-list topbar-dropdown mb-4"
                                 style="width:200px;margin:0 auto;">
                                <a class="dropdown-toggle arrow-none" data-toggle="dropdown" href="#nolink"
                                   role="button" aria-haspopup="false" aria-expanded="false" style="padding-left:10px;">
                                    <span class="align-middle"><?php echo $LangNameList['list'][$CurrLangName]['title']; ?></span>
                                    <i class="mdi mdi-chevron-down"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu"
                                     style="right:26px;">
                                    <?php
                                    foreach ($LangNameList['list'] as $key => $val) {
                                        echo '<a href="set_lang.php?lang=', $key, '" class="dropdown-item notify-item"><span class="align-middle">', $val['title'], '</span></a>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="verify-msg"></div>
                        </div>

                        <form action="#">
                            <div class="form-group">
                                <select name="server_id" class="form-control">
                                    <?php
                                    $mt4_server = $DB->getDTable("select * from t_mt4_server where `status` = 1 order by default_open_svr desc,`real` desc");
                                    foreach ($mt4_server as $key => $val) {
                                        echo '<option value="', $val['id'], '">', $val['mt4_name'], '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <input class="form-control" type="text" id="account" name="account"
                                       placeholder="<?php echo L('请输入邮箱或者MT账号'); ?>">
                            </div>

                            <div class="form-group">
                                <input class="form-control" type="password" id="password" name="password"
                                       placeholder="<?php echo L('请输入登录密码'); ?>">
                            </div>

                            <div class="form-group" style="position:relative;">
                                <img src="/include/vCode/001.php?w=87&h=38"
                                     style="position:absolute; top:0px; right:0px;"
                                     onClick="this.src='/include/vCode/001.php?w=87&h=38&rnd='+Math.random()">
                                <input class="form-control" type="text" id="verify" name="verify"
                                       placeholder="<?php echo L('请输入验证码'); ?>">
                            </div>

                            <div class="form-group mb-3 clearfix">
                                <a href="forget.php" class="text-muted float-left"><?php echo L('忘记密码'); ?>?</a>
                                <a href="reg.php" class="text-muted float-right"><?php echo L('注册'); ?></a>
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary" type="button"
                                        id="loginbtn"><?php echo L('登录'); ?></button>
                            </div>

                        </form>
                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->


                <!-- end row -->

            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
<!-- end page -->

<!-- App js -->
<script src="/assets/js/app.min.js"></script>

<script type="text/javascript">
    var accout = $('input[name=account]');
    var password = $('input[name=password]');
    var verify = $('input[name=verify]');

    accout.add(password).add(verify).focus(function () {
        $(this).parent().find('.verify-msg').text('');
    });

    $(document).on("click", "#loginbtn", function () {
        //$("#loginbtn").click(function () {
        $('.verify-msg').text('');
        if (accout.val() == '') {
            $('.verify-msg').text('<?php echo L('账号不能为空');?>');
            return;
        } else if (password.val() == '') {
            $('.verify-msg').text('<?php echo L('密码不能为空');?>');
            return;
        } else if (verify.val() == '') {
            $('.verify-msg').text('<?php echo L('验证码不能为空');?>');
            return;
        }

        $(this).attr('disabled', "disabled").text('<?php echo L('验证中');?>');
        var _this = $(this);
        var form = $(this).closest('form');
        $.post('?clause=chklogin', form.serialize(), function (data) {
            if (data.status == '1') {
                $('#loginbtn').attr('disabled', 'disabled');
                $('#loginbtn').text('<?php echo L('登录成功，进入中...');?>');
                window.location.href = 'index.php';
            } else if (data.status == '11') {
                $('#loginbtn').attr('disabled', 'disabled');
                $('#loginbtn').text('<?php echo L('登录成功，进入中...');?>');
                if ($('body').width() > 768) {
                    window.location.href = "{:U('Index/index')}?layout=yes";
                } else {
                    window.location.href = "{:U('deposit/addmoney')}";
                }
            } else if (data.status == '12') {
                $('#loginbtn').removeAttr('disabled');
                $('#loginbtn').text('<?php echo L('登录');?>');
                $("#showphoneverify").show();
                $('.verify-msg').text(data.info);
            } else {
                if (data.data > 3) {
                    $("#showverify").show();
                }
                $('.verify-msg').text(data.info);
                $('#loginbtn').removeAttr('disabled');
                $('#loginbtn').text("<?php echo L('登录');?>");
                $(".verify-img").attr("src", "{:U('Public/verify')}" + '?t=' + Date.now());
                $('input[name="verify"]').val("");
            }
            _this.removeAttr("disabled");
        }, 'json')
    });

    $('input[name=account]').focus();

    document.onkeydown = function (event) {
        e = event ? event : (window.event ? window.event : null);
        if (e.keyCode == 13) {
            $("#loginbtn").click();
        }
    }

    //---------------------------------------------------------------------

    //短信验证码
    $(document).on("click", "#getcode", function () {
        //$("#getcode").click(function () {
        if (wait != 60) {
            return false;
        }
        var phone = $("#phone").val();
        var mtserver = $("#mtserver").val();
        var imgcode = $("#phone_code_verify").val();
        var _this = $(this);


        time(this);
        $.post('{:U("Public/sendloginmsg")}', {email: accout.val(), password: password.val()}, function (data) {
            if (data.error == 0) {
                layer.alert('发送成功');
            } else {
                layer.alert(data.info);
                wait = 0;
            }
        }, 'json');
    });

    var wait = 60;

    function time(o) {
        if (wait == 0) {
            $("#getcode").val('重发');
            wait = 60;
        } else {
            $("#getcode").val('重发(' + wait + ')');
            wait--;
            setTimeout(function () {
                time(o)
            }, 1000)
        }
    }
</script>

</body>
</html>