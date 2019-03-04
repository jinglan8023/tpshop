<?php
namespace app\index\model;
use think\Model;
class Car extends Model {
    protected $table = 'shop_car';
    //定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'utime';
}
