<?php
namespace app\index\model;
use think\Model;
class OrderDetail extends Model{
    protected $table='shop_order_detail';
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

}
