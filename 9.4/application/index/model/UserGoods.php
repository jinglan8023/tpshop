<?php
namespace app\index\model;
use \think\Model;
class UserGoods extends Model{
    protected $table='shop_user_goods';
    protected $createTime='ctime';
    protected $updateTime='utime';
}