<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:92:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/admin\view\index\index.html";i:1542358061;s:87:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/admin\view\layout.html";i:1536753088;s:91:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/admin\view\public\top.html";i:1536552636;s:92:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/admin\view\public\left.html";i:1540950770;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>layout 后台大布局 - Layui</title>
    <link rel="stylesheet" href="__STATIC__/layui/css/layui.css">
    <script src="__STATIC__/layui/layui.js"></script>
</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <!--头部-->
    <div class="layui-header">
        
<div class="layui-logo">layui 后台布局</div>
<!--头部区域（可配合layui已有的水平导航）-->
<ul class="layui-nav layui-layout-left">
    <li class="layui-nav-item"><a href="">控制台</a></li>
    <li class="layui-nav-item"><a href="">商品管理</a></li>
    <li class="layui-nav-item"><a href="">用户</a></li>
    <li class="layui-nav-item">
        <a href="javascript:;">其它系统</a>
        <dl class="layui-nav-child">
            <dd><a href="">邮件管理</a></dd>
            <dd><a href="">消息管理</a></dd>
            <dd><a href="">授权管理</a></dd>
        </dl>
    </li>
</ul>
<ul class="layui-nav layui-layout-right">
    <li class="layui-nav-item">
        <a href="javascript:;">
            <img src="__STATIC__/layui/images/frew.jpg" class="layui-nav-img">
            <!--贤心-->
            <?php echo \think\Session::get('adminInfo.admin_name'); ?>
        </a>
        <dl class="layui-nav-child">
            <dd><a href="">基本资料</a></dd>
            <dd><a href="">安全设置</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item"><a href="">退出</a></li>
</ul>

    </div>
    <!--左侧-->
    <div class="layui-side layui-bg-black">
        <style>
    .leftchecked{
        color:red;!important;
        font-weight:bold;!important;
    }
</style>
<div class="layui-side-scroll">
        <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
    <ul class="layui-nav layui-nav-tree"  lay-filter="test">
        <?php if(is_array($AllMenu) || $AllMenu instanceof \think\Collection || $AllMenu instanceof \think\Paginator): $i = 0; $__LIST__ = $AllMenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;
            if( strtolower(request()->controller() )==strtolower( ltrim( $v['node_url'],'/' ) ) ){
                echo '<li class="layui-nav-item layui-nav-itemed">';
            }else{
                echo '<li class="layui-nav-item">';
            }
        ?>

            <a class="" href="javascript:;"><?php echo $v['node_name']; ?></a>
            <dl class="layui-nav-child">
            <?php if(isset($v['son'])){if(is_array($v['son']) || $v['son'] instanceof \think\Collection || $v['son'] instanceof \think\Paginator): $i = 0; $__LIST__ = $v['son'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vv): $mod = ($i % 2 );++$i;
                    $action=explode( '/' , trim ( $vv ['node_url'] , '/' ) );
                    $action_url=strtolower ( array_pop ( $action ) );
                    if(strtolower(request()->action())==$action ){
                            echo '<dd class="leftchecked">
                                        <a href="'.url($vv['node_url']).'">'.$vv['node_name'].'</a>
                                    </dd>';
                        }else{
                            echo '<dd>
                                        <a href="'.url($vv['node_url']).'">'.$vv['node_name'].'</a>
                                    </dd>';
                        }
                    endforeach; endif; else: echo "" ;endif;  }?>
            </dl>
        </li>

        <?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
</div>

    </div>
    <!-- 内容主体区域 -->
    <div class="layui-body" style="width:800px">
        <div style="padding: 15px;"><h1 style="color:#f00">
欢迎 <?php echo \think\Session::get('adminInfo.admin_name'); ?>进入后台首页页页
</h1>
 </div>
    </div>

</div>
</body>
</html>
<script>
    layui.use("element",function(){
        var element=layui.element;
    })
</script>