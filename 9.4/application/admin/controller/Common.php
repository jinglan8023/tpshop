<?php
namespace app\admin\controller;
use think\Controller;
class  Common extends Controller{
    /**
     *Common contstructor;
     * 构造方法
     *
     */
     //不用__construct
     function _initialize(){
         //防止非法登录
        if(!session('?adminInfo')){
            $this->error('请先登录',url('Login/login'));
        }
        #接收控制器
        #request()->controller();
        #接收方法
        #request()->action();
        #获取左侧菜单列表
        $menuModel=model('PowerNode');
        $where=[
            'status'=>1
        ];
        $menuobj=$menuModel->where($where)->select();
        $menu=collection($menuobj)->toArray();
        #dump($menu);die;
        if(!empty($menu)){
            $new=[];
            foreach($menu as $key=>$value){
                if($value['level']==1){
                    $new[$value['node_id']]=$value;
                }else{
                    $new[$value['pid']]['son'][]=$value;
                }
            }
        }else{
            $new=[];
        }
        #print_r($new);die;
        $this->assign('AllMenu',$new);

     }

    /**
     * 失败的方法李的
     * @param $fail
     */
    protected function failLi($msg='fail',$statuc=1,$data=[]){
        $arr=[
            'msg'=>$msg,
            'status'=>$statuc,
            'data'=>$data
        ];
        echo json_encode($arr);exit;
    }

    /**
     * 成功的方法李的
     * @param $successfully
     */
    function successfullyLi($msg='success',$statuc=100,$data=[]){
        $arr=[
            'msg'=>$msg,
            'status'=>$statuc,
            'data'=>$data
        ];
        echo json_encode($arr);exit;
    }

    /**
     * 检测请求合法李的
     */
    public function checkRequestLi(){
        if(!request()->isPost()&&!request()->isAjax()){
            $this->failLi('非法请求!');
        }
    }

    /**
     * 失败的方法耿
     * @param $fail
     */
    function fail($fail){
        $info=[
            'font'=>$fail,
            'code'=>2
        ];
        echo json_encode($info);exit;
    }

    /**
     * 成功的方法耿
     * @param $successfully
     */
    function successfully($successfully){
        $info=[
            'font'=>$successfully,
            'code'=>1
        ];
        echo json_encode($info);exit;
    }


}