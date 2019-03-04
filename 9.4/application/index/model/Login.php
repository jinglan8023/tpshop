<?php
namespace app\index\model;
use think\Model;
class Login  extends Model{
    protected $table="shop_user";
    //定义时间戳字段名
    protected $createTime='user_time';
    protected $updateTime=false;

}



