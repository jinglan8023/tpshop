<?php
namespace app\index\model;
use think\Model;
class Order extends Model{
    protected $table='shop_order';
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

}
