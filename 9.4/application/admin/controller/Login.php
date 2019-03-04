<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller{
    //用的次数多封装个方法
    /**检测请求是否是post并且是ajax*/
    public function checkRequest(){
       if(request()->isPost()&&request()->isAjax()){
            return true;
       }else{
            return false;
       }
    }
    //验证码图片
    public function captcha_src(){
        return view();
    }
    //登录页面
    public function login(){
    //是这种请求就登录
        if($this->checkRequest()){
        //接收数据
            $data=input('post.');
            //dump($data);die;
            //验证验证码是否正确
            if(!captcha_check($data['code'])){
                 //验证码错误
                 return ['Status'=>"Erro",'Erro'=>'验证码有误'];
            };
            //验证用户名是否跟数据库一样
            $adminModel=model('Admin');
            $where=['admin_name'=>$data['admin_name']];
            $adminInfo=$adminModel->where($where)->find();
            if(!empty($adminInfo)){
                //如果有数据就去验证密码
                //验证密码是否跟数据库里加密的一样
                $admin_pwd=md5(md5($data['admin_pwd']).md5($adminInfo['salt']).'shop');
                //如果密码一样就存session 加登录时间 IP
                if($admin_pwd==$adminInfo['admin_pwd']){
                    //先存session信息
                    $admin=['admin_id'=>$adminInfo['admin_id'],'admin_name'=>$adminInfo['admin_name']];
//                    $str=serialize($admin);
//                    $arr=unserialize($str);print_r($arr);
                    session('adminInfo',$admin);//不用序列化
                    //记住取的时候还要反序列化拿出来

                    //修改最后一次登陆的IP  登录时间
                    $arr=['last_login_time'=>time(),'last_login_ip'=>request()->ip()];

                    $updateWhere=['admin_id'=>$adminInfo['admin_id']];
                    model('Admin')->where($updateWhere)->update($arr);

                    //再返回登录成功
                    return['Status'=>"ok",'ok'=>'登录成功ok'];

                }


            }else{
                return ['Status'=>"Erro",'Erro'=>'账号或密码有误'];
            }

        }else{
            //临时关闭当前模板布局  不然登录页面有头部左部
            $this->view->engine->layout(false);
            //显示一个视图页面
            return view('login/login');
        }
    }






}