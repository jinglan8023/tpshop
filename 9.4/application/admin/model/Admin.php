<?php
namespace app\admin\model;
use think\Model;
class Admin extends Model{
    //表名
    protected $table="shop_admin";
    //定义时间戳字段名
    protected $createTime='admin_create';
    protected $updateTime=false;
    public $salt;

    //自动完成
    protected $insert=['salt'];

    //密码加密
    /**密码修改器*/
    //先走
    public function setAdminPwdAttr($value){
        //生成盐值createSalt()是公共文件里的函数名
        $this->salt=$salt=createSalt();
        //生成密码
            $pwd=createPwd($value,$salt);
            return $pwd;

        //return md5($value);
    }


    /**自动完成*/
    public function setSaltAttr(){
        return $this->salt;
    }




}