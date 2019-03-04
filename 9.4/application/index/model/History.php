<?php
namespace app\index\model;
use think\Model;
class History extends Model{
    protected $table='shop_history';

    //定义时间戳字段名
    protected $createTime=false;
    protected $updateTime=false;
}
