<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:94:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/index\view\product\money.html";i:1539266436;}*/ ?>
<td>&nbsp; 价格：</td>
<td class="td_a price">
    <?php if(is_array($money) || $money instanceof \think\Collection || $money instanceof \think\Paginator): $i = 0; $__LIST__ = $money;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
    <a href="javascript:;"><?php echo $v; ?></a>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</td>