<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:93:"D:\phpstudy\PHPTutorial\WWW\web\month5\9.4\public/../application/index\view\product\list.html";i:1546996413;}*/ ?>

<ul class="cate_list">
    <?php if(is_array($goodsList) || $goodsList instanceof \think\Collection || $goodsList instanceof \think\Paginator): $i = 0; $__LIST__ = $goodsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
    <li>
        <div class="img">
            <a href="<?php echo url('Product/productDetail',['goods_id'=>$v['sku_id'],'cate_id'=>$v['cate_id']]); ?>">
                <img src="/<?php echo $v['sku_img']; ?>" width="210" height="185" />
            </a>
        </div>
        <div class="price">
            <font>￥<span><?php echo number_format($v['sku_price'] , 2 , '.' , ','); ?></span></font>
        </div>

        <div class="name">
            <a href="#"><?php echo $v['sku_name']; ?></a>
            <span style="margin-left:10px;">销量: <span style="color:#f00"><?php echo formatSale($v['sku_sale_num']); ?></span></span>
        </div>
        <div class="carbg">
            <a href="#" class="ss">收藏</a>
            <a href="#" class="j_car">加入购物车</a>
        </div>
    </li>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</ul>
<div class="pages">
    <?php echo $page_str; ?>
</div>

