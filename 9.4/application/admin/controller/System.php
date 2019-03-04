<?php
namespace app\admin\controller;
use think\Db;
class System extends Common{
    //是否是ajax 且post提交
    public function checkRequest(){
        if(request()->isPost()&&request()->isAjax()){
            return true;
        }else{
            return false;
        }
    }

    //验证正确信息
    public function successfully($font){
        $info=[
            'font'=>$font,
            'code'=>1,
        ];
        echo json_encode($info);
    }

    //验证错误信息
    public function fail($font){
        $info=[
            'font'=>$font,
            'code'=>2,
        ];
        echo json_encode($info);
    }

    //个人资料  没做
    public function systemPerson(){

    }

    /**系统设置*/
    //注意
    public function systemSet(){
        if($this->checkRequest()){
            //开启事务
            Db::startTrans();
            try{
                $data=input('post.');
                //先删除表中原有的所有数据
                $res1= model('System')->query("delete from shop_system");

                //添加数据
                foreach($data as $k=>$v){
                    $info[]=['name'=>$k,'value'=>$v];
                }
                /* 没用
                $info=[
                     ['name'=>'web_name','value'=>'a'],
                     ['name'=>'web_url','value'=>'a'],
                     ['name'=>'web_copyright','value'=>'a'],
                     ['name'=>'web_record','value'=>'a']
                 ];*/
                $res2=model('system')->saveAll($info);
                //提交事务
                Db::commit();
                //返回后在跳回原来的页面  原来的html页面是有默认值的 万一是空的呢 所以用三目运算符给
                $this->successfully('保存成功');
            }catch(\Exception $e){
                //回滚事务
                Db::rollback();
                $this->fail('保存失败');
            }

   }else{
            $data=model('System')->select();
            $info=[];
            foreach($data as $k=>$v){
                $info[$v['name']]=$v['value'];
            }
            $this->assign('info',$info);
            return view();
        }
    }










}
