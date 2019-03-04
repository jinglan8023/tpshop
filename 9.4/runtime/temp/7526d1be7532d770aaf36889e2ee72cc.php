<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:92:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/index\view\login\login.html";i:1539946474;}*/ ?>
<!DOCTYPE html>
<html>

<head lang="en">
    <meta charset="UTF-8">
    <title>登录</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />

    <link rel="stylesheet" href="__STATIC__/index/login/amazeui.css" />
    <link href="__STATIC__/index/login/dlstyle.css" rel="stylesheet" type="text/css">


    <script src="__STATIC__/layui/layui.js"></script>

    <script src="__STATIC__/index/login/jquery.min.js"></script>
    <script src="__STATIC__/index/login/amazeui.min.js"></script>
</head>

<body>

<div class="login-boxtitle">
    <a href="home.html"><img alt="logo" src="__STATIC__/index/login/logobig.png" /></a>
</div>

<div class="login-banner">
    <div class="login-main">
        <div class="login-banner-bg"><span></span><img src="__STATIC__/index/login/big.jpg" /></div>
        <div class="login-box">

            <h3 class="title">登录商城</h3>

            <div class="clear"></div>

            <div class="login-form">
                <form>
                    <div class="user-name">
                        <label for="account"><i class="am-icon-user"></i></label>
                        <input type="text" name="account" id="account" value="<?php echo isset($userInfo['account']) ? $userInfo['account'] :  ''; ?>" placeholder="邮箱/手机/用户名">
                    </div>
                    <div class="user-pass">
                        <label for="user_pwd"><i class="am-icon-lock"></i></label>
                        <input type="password" name="user_pwd" value="<?php echo isset($userInfo['user_pwd']) ? $userInfo['user_pwd'] :  ''; ?>" id="user_pwd" placeholder="请输入密码">
                    </div>
                </form>
            </div>

            <div class="login-links">
                <label for="remember_me"><input id="remember_me" type="checkbox">记住密码</label>
                <!--记住密码 存10天-->
                <a href="#" class="am-fr">忘记密码</a>
                <a href="<?php echo url('Login/register'); ?>" class="zcnext am-fr am-btn-default">注册</a>
                <br />
            </div>
            <div class="am-cf">
                <input type="button" id="btn" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm">
            </div>
            <div class="partner">
                <h3>合作账号</h3>
                <div class="am-btn-group">
                    <li><a href="#"><i class="am-icon-qq am-icon-sm"></i><span>QQ登录</span></a></li>
                    <li><a href="#"><i class="am-icon-weibo am-icon-sm"></i><span>微博登录</span> </a></li>
                    <li><a href="#"><i class="am-icon-weixin am-icon-sm"></i><span>微信登录</span> </a></li>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="footer ">
    <div class="footer-hd ">
        <p>
            <a href="# ">恒望科技</a>
            <b>|</b>
            <a href="# ">商城首页</a>
            <b>|</b>
            <a href="# ">支付宝</a>
            <b>|</b>
            <a href="# ">物流</a>
        </p>
    </div>
    <div class="footer-bd ">
        <p>
            <a href="# ">关于恒望</a>
            <a href="# ">合作伙伴</a>
            <a href="# ">联系我们</a>
            <a href="# ">网站地图</a>
            <em>© 2015-2025 Hengwang.com 版权所有. 更多模板 <a href="#" target="_blank" title="模板之家">模板之家</a> - Collect from <a href="http://www.cssmoban.com/" title="网页模板" target="_blank">网页模板</a></em>
        </p>
    </div>
</div>
</body>

</html>

<script>
    $(function(){
       layui.use(['layer','form'],function(){
            var layer=layui.layer;
            var form=layui.form;
            $('#btn').click(function(){
                //获取账号 密码 记住密码
                var account=$('#account').val();
                var user_pwd=$('#user_pwd').val();
                var remember_me=$('#remember_me').prop('checked');
                if(account==''){
                    layer.msg('手机号或邮箱必填',{icon:2});
                    return false;
                }
                if(user_pwd==''){
                    layer.msg('密码必填',{icon:2});
                    return false;
                }

                var callback="<?php echo urldecode(request()->param('callback'));?>";

                //把账号 密码 通过ajax 传给控制器
                $.post(
                    "<?php echo url("","",true,false);?>"
                    ,{account:account,user_pwd:user_pwd,remember_me:remember_me}
                    ,function(msg){
                        layer.msg(msg.font,{icon:msg.code});
                        if(msg.code==1){
                            if(callback==''){
                                location.href="<?php echo url('Index/index'); ?>"
                            }else{
                                location.href=callback;
                            }
                        }
                    },'json'
                );
            });
        })
    })
</script>