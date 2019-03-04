<?php
namespace app\index\model;
use think\Model;
class OrderAddress extends Model{
    protected $table='shop_order_address';
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

}
