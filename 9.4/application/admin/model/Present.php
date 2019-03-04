<?php
namespace app\admin\model;
use think\Model;
class Present  extends Model{
    protected $table="shop_present";
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

}


