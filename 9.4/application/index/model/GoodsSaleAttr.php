<?php
namespace app\index\model;
use think\Model;
class GoodsSaleAttr extends Model{
    protected $table="shop_goods_sale_attr";
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

}



