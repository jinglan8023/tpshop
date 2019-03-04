<?php
namespace app\index\validate;
use think\Validate;
class Index extends Validate{

    //定义规则
    protected $rule=[
        //验证数据库中电话号码 邮箱 唯一用性unique:表名
        'user_tel'=>'require|checkTel|unique:shop_user',
        'user_pwd'=>'require|checkPwd',
        'user_pwd1'=>'require|confirm:user_pwd',
        'user_email'=>'require|checkEmail|unique:shop_user',
    ];

    protected $message=[
        'user_tel.require'=>'手机号必填',
        'user_tel.unique'=>'手机号已注册 请登录',
        'user_email.require'=>'邮箱必填',
        'user_email.unique'=>'邮箱已注册 请登录',
        'user_pwd.require'=>'密码必填',
        'user_pwd1.require'=>'确认密码必填',
        'user_pwd1.confirm'=>'确认密码与密码保持一致',
    ];

    /**验证手机号*/
    public function checkTel($value,$rule,$data){
        $tel=session('codeInfo.account');
        $reg='/^1[1-9]\d{9}$/';
        if(!preg_match($reg,$value)){
            return '请输入正确的手机号';
        }else if($value!=$tel){
            return '验证手机号与所填手机号不一致';
        }else{
            return true;
        }
    }
    /**验证邮箱*/
    public function checkEmail($value,$rule,$data){
        $email=session('codeInfo.account');
        $reg='/^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$/';
        if(!preg_match($reg,$value)){
            return '请输入正确的邮箱格式';
        }else if($value!=$email){
            return '验证邮箱与所填邮箱不一致';
        }else{
            return true;
        }
    }
    /**验证密码*/
    public function checkPwd($value,$rule,$date){
        $reg='/^[a-z0-9_]{6,}$/';
        if(!preg_match($reg,$value)){
            return '密码由6位及以上数字、字母、下划线组成';
        }else{
            return true;
        }
    }

    /**验证确认密码*/
    public function checkPwd1($value,$rule,$data){
        // $value确认密码  admin_pwd是新密码
        //dump($data);die;
        if($value!=$data['user_pwd']){
            return '确认密码必须与密码保持一致';
        }else{
            return true;
        }

    }


    /**验证手机号 邮箱 环境*/
    protected $scene=[
        'editTel'=>['user_tel','user_pwd','user_pwd1'],
        'editEmail'=>['user_email','user_pwd','user_pwd1']
    ];

}