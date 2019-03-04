<?php
namespace app\index\controller;
use think\Controller;
class Login extends Common{

    /**发送验证码*/
    public function send(){
        $value=input('post.value');
        //随机生成验证码
        $sendCode=createCode();

        //判断当前传过来的值是手机号还是邮箱就看有没有@符号
        if(substr_count($value,'@')){
            //echo '发邮件';
            $res=sendEmail($value,$sendCode);
            //dump($res);die;
            if($res){
                $codeInfo=[
                    'sendCode'=>$sendCode,
                    'sendTime'=>Time(),
                    'account'=>$value,
                ];
                session('codeInfo',$codeInfo);
                successfully('发送成功');
            }else{
                 fail('发送失败');
            }
        }else{
            //echo '发短信';
            //调公共函数里的Sms
            $res=Sms($value,$sendCode);//dump($res);die;

            if($res->Message=='OK'){
                $codeInfo=[
                    'sendCode'=>$sendCode,
                    'sendTime'=>Time(),
                    'account' =>$value
                ];
                 session('codeInfo',$codeInfo);
                 successfully('发送成功');
            }else{
                fail('发送失败');
            }
        }
    }

    /**注册*/
    public function register(){
        if(request()->isPost()){
            $data=input('post.');//dump($data);die;
            //验证验证码是否正确
            $sendCode=session('codeInfo.sendCode');//echo $sendCode;die;
            $sendTime=session('codeInfo.sendTime');//echo $sendTime;die;
            if($data['user_code']==''){
                fail('验证码必填');
            }else if($data['user_code']!=$sendCode){
                fail('验证码有误');
            }else if(time()-$sendTime>300){
                fail('验证码失效,5分钟内有效');
            }
            //验证手机号 邮箱 密码 确认密码
            //验证手机号是否已经注册
            //使用验证器+验证场景
            $validate=validate('Index');//dump($validate);die;
            //单纯手机号验证
            //$res=$validate->scene('editTel')->check($data);

            //区分手机号  邮箱注册  分不同场景进行验证
            if(empty($data['user_email'])){
                $res=$validate->scene('editTel')->check($data);
            }else{
                $res=$validate->scene('editEmail')->check($data);
            }
            if(!$res){
               fail($validate->getError()) ;
            }
            //dump($res);die;

            //把信息保存到数据库
            $userModel=model('User');
            //把和数据库表中一致的字段保存到数据库中
            $result=$userModel->allowField(true)->save($data);
            if($result){
               successfully('注册成功');
            }else{
                fail('注册失败');
            }

        }else{
            $this->view->engine->layout(false);//关闭模板布局
            return view();
        }
    }

    /**登录*/
    public function login(){
        if(request()->isPost()){
            //接值
            $data=input('post.');//dump($data);die;
            //验证 账号密码
            if(empty($data['account'])){
                fail('手机号或邮箱必填');
            }
            if(empty($data['user_pwd'])){
                fail('密码必填');
            }
            //根据账号查询个人信息
            $account=$data['account'];
            if(substr_count($account,'@')){
                $where=['user_email'=>$account];
            }else{
                $where=['user_tel'=>$account];
            }
            $userInfo=model('User')->where($where)->find();//dump($userInfo);die;
            $time=time();//dump($time);die;
            $last_error_time=$userInfo['last_error_time'];//dump($last_error_time);die;
            $error_num=$userInfo['error_num'];
            $update_where=['user_id'=>$userInfo['user_id']];
            if(!empty($userInfo)){
                //输入的密码==$userInfo['密码'];
                if(md5($data['user_pwd'])==$userInfo['user_pwd']){
                    //错误次数>=5并且时间在一小时内  不允许登录
                    if($error_num>=5&&$time-$last_error_time<3600){
                        $open_time=60-(ceil($time-$last_error_time)/60);
                        fail('当前账号已锁定,'.$open_time.'分钟之后可以登录');
                    }
                    //次数清0  时间清空
                    $updateInfo=['error_num'=>0,'last_error_time'=>null];
                    $res=model('User')->where($where)->update($updateInfo);
                    //判断是否记住密码  是 账号 密码存cookie 存10天
                    if($data['remember_me']=='true'){
                        $cookieInfo=['account'=>$account,'user_pwd'=>$data['user_pwd']];
                        setcookie('cookieInfo',serialize($cookieInfo),$time+60*60*24*10);
                    }
                    //用户信息存cookie
                    $sessionInfo=['user_id'=>$userInfo['user_id'],'account'=>$account];
                    session('userInfo',$sessionInfo);
                    #################################
                    #同步cookie 中的浏览记录
                    $this->asyncHistory();

                    #同步cookie 中 购物车数据  到数据库
                    $this->asyncCar();
                    #################################


                    successfully('登录成功');
                }else{
                    //距离上次错误时间超过一小时 次数改为1  时间为当前时间
                    if($time-$last_error_time>3600){
                        $updateInfo=['error_num'=>1,'last_error_time'=>$time];
                        $res=model('User')->where($where)->update($updateInfo);
                        fail('您还可以输入4次');
                    }else{
                        //如果错误次数>=5次 提示已锁定
                        if($error_num>=5){
                            fail('账号已锁定');
                        }else{
                            //次数累计
                            $error_num++;
                            $updateInfo=['error_num'=>$error_num,'last_error_time'=>$time];
                            $res=model('User')->where($where)->update($updateInfo);
                            $num=5-$error_num;
                            fail('您还可以输入'.$num.'次');
                        }
                    }
                }
            }else{
                fail('账号不存在 请先注册');
            }
        }else{
            $this->view->engine->layout(false);//关闭模板布局
            return view();
        }
    }

    /**
     * 退出登录（前台）
     * @return mixed
     */
    public function fade(){
        session('userInfo',null);
        $this->success('退出登录成功',url('Index/index'));
    }

}
