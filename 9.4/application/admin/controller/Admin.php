<?php
namespace app\admin\controller;
//use think\Controller;
//use app\admin\validate\Admin as AdminValidate;
class Admin extends Common{

    /**验证用户名唯一性*/
    //添加与修改共用一个
    public function checkName(){
        //接收用户名
        $admin_name=input('post.admin_name');
        //接受管理员的id (修改时用)
        $admin_id=input('post.admin_id');
        //echo $admin_id;die;
        if(empty($admin_id)){
            //id为空就是添加的唯一性验证
            $where=['admin_name'=>$admin_name];
        }else{
            //否则就是修改时的唯一性验证
            //条件就是新值    id
            $where=['admin_id'=>['NEQ',$admin_id],'admin_name'=>$admin_name];
        }

        $arr=model('Admin')->where($where)->find();
        if($arr){
            echo "no";//数据库中有数据 不能进库
        }else{
            echo "ok";// 数据库中没有数据 可以进库
        }

    }

    /**管理员添加 和执行添加*/
    public function adminAdd(){
        // 提交过来的即得是post还得是ajax
        if(request()->isPost()&&request()->isAjax()){
            //接收数据
            $data=input('post.');//dump($data);die;
            //tp5没有字段映射不写

            $validate=validate('Admin');//实例化验证器 助手函数形势
            //dump($validate);die;
            //自动验证php正则...
            $res=$validate->check($data);
            if(!$res){
                $font=$validate->getError();
                echo json_encode(['font'=>$font,'code'=>2]);exit;
            }
            //自动完成 添加时间...修改时间...最后一次登录时间..  加盐..等等


            //把数据入库
            $adminModel=model('Admin');
            $res=$adminModel->save($data);
            //dump($res);die;
            //echo  $adminModel->salt;exit;
            //返回自增的id用$adminModel->admin_id
            if($res){
                echo json_encode(['font'=>'添加成功','code'=>1]);
            }else{
                echo json_encode(['font'=>'添加失败','code'=>2]);
            }

            //return '执行添加';
        }else{
            return view();
        }
    }

    /**列表展示*/
    public function adminList(){
        return view();
    }

    /**获取管理员数据*/
    public function adminInfo(){
       $p=input('get.page');//ui里自带的page 不是自己起的名字
       $page_num=input('get.limit');
       $data=model('Admin')->page($p,$page_num)->select();
        //dump($data);die;
        $count=model('Admin')->count();
       $info=['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
       echo json_encode($info);
    }


    //管理员的即点即改
    public function adminUpdate(){
        $data=input('post.');
        //dump($data);
        $where=[
            'admin_id'=> $data['admin_id']
        ];

        $info=[
            $data['field']=> $data['value']
        ];

        $res=model('Admin')->where($where)->update($info);
        if($res){
            echo json_encode(['font'=>'ok','code'=>1]);
        }else{
            echo json_encode(['font'=>'no','code'=>2]);
        }
    }

    /**管理员删除*/
    public function adminDel(){
        $admin_id=input('post.admin_id');
        //echo $admin_id;die;
        $where=['admin_id'=>$admin_id];
        $res=model('admin')->where($where)->delete();
        if($res){
            echo json_encode(['font'=>'删除成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'删除失败','code'=>2]);
        }

    }

    /**管理员修改视图*/
    public function adminUpdateInfo(){
        //接收要修改的id
        $admin_id=input('get.admin_id');
        $where=['admin_id'=>$admin_id];
        //查找修改的数据
        $data=model('Admin')->where($where)->find();
        //dump($data);die;
        $this->assign('data',$data);
        return view('Admin/adminUpdate');
    }

    /**管理员执行修改*/
    public function adminUpdateDo(){
        //得到新值
        $data=input('post.');
        //dump($data);die;
        //得到新值后要验证跟添加的验证一样
        $validate=validate('Admin');//实例化验证器 助手函数形势
        //dump($validate);die;
        //自动验证php正则...
        $res=$validate->scene('edit')->check($data);//去validate 里的文件
        //echo $res;die;
        if(!$res){
         echo  $validate->getError();
            //echo json_encode(['font'=>$font,'code'=>2]);exit;
        }
        //执行修改
       //echo 'ok';
       $adminModel=model('admin');
        $arr=$adminModel->update($data);
        if($arr){
            echo json_encode(['font'=>'修改成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'修改失败','code'=>2]);
        }
    }


    //是否是ajax 且post提交
    public function checkRequest(){
        if(request()->isPost()&&request()->isAjax()){
            return true;
        }else{
            return false;
        }
    }

    //验证正确信息  成功
    public function successfully($font){
        $info=[
            'font'=>$font,
            'code'=>1,
        ];
        echo json_encode($info);
    }

    //验证码错误信息
    public function fail($font){
        $info=[
            'font'=>$font,
            'code'=>2,
        ];
        echo json_encode($info);
    }


    //管理员密码修改
    //注意理解
    public function updatePwd(){
        if($this->checkRequest()){
             //接收传过来的值
            $data=input('post.');//dump($data);die;
            //根据用户id查询当前用户的正确信息  得到 密码 盐值 这两列信息
            $admin_id=session('adminInfo.admin_id');
            $where=['admin_id'=>$admin_id];
            $arr=model('Admin')->field('admin_pwd,salt')->where($where)->find()->toArray();
            //dump($arr);die;

            //生成密码  调公共函数里的生成密码  括号里是传参数
            $old_pwd=createPwd($data['old_pwd'],$arr['salt']);
            //如果从数据库中取出来的密码不等于接到的值的密码就说明输的旧密码有误
            if($arr['admin_pwd']!=$old_pwd){
                $this->fail('旧密码有误');
            }

            //验证新密码与确认密码是否一致
            $res=validate('Admin')->scene('editPwd')->check($data);
            if(!$res){
                $font=validate('Admin')->getError();
               $this->fail($font);
            }

            //执行修改
            $info=[
            //新密码: 接到的密码拼上取出来的旧盐值 组成新密码
                'admin_pwd'=>createPwd($data['admin_pwd'],$arr['salt'])
            ];
            $result=model('Admin')->where($where)->update($info);
            //dump($result);die;
            if($result){
                $this->successfully('密码修改成功  请重新登录');
            }else{
                $this->fail('修改失败');
            }
        }else{
            return view();
        }
    }
    /**退出登录*/
    public function AdminQuit(){
        session('adminInfo',null);
        //重定向到Login模块的login操作
        $this->redirect('Login/login');

    }


}