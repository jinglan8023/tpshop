<?php
namespace app\index\model;
use think\Model;
class GoodsSku  extends Model{
    protected $table="shop_goods_sku";
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';


}


