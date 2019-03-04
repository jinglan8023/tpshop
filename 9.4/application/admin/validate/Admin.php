<?php
namespace app\admin\validate;
use think\Validate;
class Admin extends Validate{

    //定义规则
    protected $rule=[
        'admin_name'=>'require|checkName',
        'admin_email'=>'require|email',
        'admin_pwd'=>'checkPwd',
        'again_pwd'=>'checkPwd1',
        'admin_tel'=>'require|checkTel',
    ];
    //提示文字
    protected $message=[
        'admin_name.require'=>'用户名必填',
        'admin_email.require'=>'邮箱必填',
        'admin_tel.require'=>'手机号必填',
        'admin_email.email'=>'请输入正确的邮箱格式'
    ];

    //重新验证环境  修改密码时用
    protected $scene=[
        'editPwd'=>['admin_pwd','again_pwd']
    ];
    /**验证用户名*/
    //$value 就是验证的这个名字
    //$rule 就是规则 不能为空(必填)
    //$data 是表单中的这一条数据
    public function checkName($value,$rule,$data){
        $reg='/^[a-z_]\w{3,11}$/i';
        if(!preg_match($reg,$value)){
            return "用户名数字、字母、下划线，非数字开头4,12位";
        }else{
            //验证唯一性 根据名字在数据库中查询、
            //$arr=AdminModel::where('admin_name',$value)->find();//dump($arr);
            $adminModel=model('Admin');//实例化自定义model
            //判断是添加还是修改
            if(empty($data['admin_id'])){
                //管理员的id 没有就是走添加
                $where=['admin_name'=>$value];
            }else{
                //有 就是走修改
                $where=['admin_id'=>['NEQ',$data['admin_id']],'admin_name'=>$value];
            }

            $arr=$adminModel->where($where)->find();
            if(!empty($arr)){
                //不为空说明有数据 不能进库
                return "用户名已存在";
            }else{
                return true;
            }
        }
    }

    /**验证密码*/
    public function checkPwd($value,$rule,$data){
        $reg='/^[\S]{6,12}$/';
        if(!preg_match($reg,$value)){
            return "密码必须6到12位，且不能出现空格";
        }else{
            return true;
        }
    }
    /**验证确认密码*/
    public function checkPwd1($value,$rule,$data){
       // $value确认密码  admin_pwd是新密码
        //dump($data);die;
        if($value!=$data['admin_pwd']){
            return '确认密码必须与密码保持一致';
        }else{
            return true;
        }

    }

    /**验证手机号*/
    public function checkTel($value,$rule,$data){
        $reg='/^1[3-9]\d{9}$/';
        if(!preg_match($reg,$value)){
            return "请输入正确的手机号";
        }else{
            return true;
        }
    }

    /**验证邮箱*/
    //在定义规则里写email提示文字里写email就不用再写验证了 注意看定义规则 提示文字



}