<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

if ($Clause == 'checkverify') {
    if (time() - session('lastpostlogin') <= 2) {
        ajaxReturn(0, L('您提交的太快了'), 0);
    }
    session('lastpostlogin', time());

    $server_id = FPostInt('server_id');
    $email = FPostStr('account');
    $verify = FPostStr('verify');

    $server_id <= 0 && ajaxReturn(0, L('登录MT服务器错误'), 0);
    strlen($email) <= 0 && ajaxReturn(0, L('账号不能为空'), 0);
    $verify != session('VCode') && ajaxReturn(session("login_error_count"), L('验证码不正确'), 0);


    //读取服务
    $regServer = $DB->getDRow("select * from t_mt4_server where `status` = 1 and `id` = '{$server_id}'");
    if (!$regServer) {
        ajaxReturn(0, L('无可用MT服务器开户.请联系管理员确认'), 0);
    }
    $DRAdmin['server_id'] = $server_id;


    $data = $DB->getDRow("select * from t_member where email = '{$email}' and status in (0,1) and server_id = '{$server_id}'");
    if (!$data) {
        this_error(L("账号不存在，不能重置密码"));
    }
    if ($data['status'] == '0') {
        this_error(L("该邮箱账号未激活"));
    } else if ($data['status'] == '1') {
        $result = api('gold://Mail/resetpasswordmail', array($email, $data['server_id']));
        if ($result['status'] == '0') {
            this_success(L("我们已将重置帐号密码链接发送到你邮箱，请及时登录你的邮箱重置密码"));
        } else {
            this_success(L("邮件发送失败，请重新发送，错误代码") . '：' . $result['info']);
        }
    } else {
        this_error(L("该邮箱账号未知错误，请重新操作"));
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
                            <h4 class="text-dark-50 text-center mt-0 font-weight-bold"><?php echo L('密码重置'); ?></h4>
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
                                        if ($key === 'zh-vn') {
                                            echo '<a href="https://trader.am-broker.com/admin/forget.php" class="dropdown-item notify-item"><span class="align-middle">', $val['title'], '</span></a>';
                                        } else {
                                            echo '<a href="set_lang.php?lang=', $key, '" class="dropdown-item notify-item"><span class="align-middle">', $val['title'], '</span></a>';
                                        }
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
                                       placeholder="<?php echo L('注册邮件'); ?>">
                            </div>

                            <div class="form-group" style="position:relative;">
                                <img src="/include/vCode/001.php?w=87&h=38"
                                     style="position:absolute; top:0px; right:0px;"
                                     onClick="this.src='/include/vCode/001.php?w=87&h=38&rnd='+Math.random()">
                                <input class="form-control" type="text" id="verify" name="verify"
                                       placeholder="<?php echo L('请输入验证码'); ?>">
                            </div>

                            <div class="form-group mb-0 text-center">
                                <button class="btn btn-primary" type="button"
                                        id="forget"><?php echo L('确认提交'); ?></button>
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

<script src="/assets/js/layer/layer.js"></script>

<script type="text/javascript">
    $(document).on("click", "#forget", function () {
        //$("#forget").click(function() {
        var from = $(this).closest('form');
        $.post('?clause=checkverify', from.serialize(), function (data) {
            layer.alert(data.info);
            if (data.status) {
                document.signupForm.reset();
            }
        }, 'json');
        return false;
    });
</script>

</body>
</html>