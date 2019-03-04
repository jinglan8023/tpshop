<?php
namespace app\admin\controller;
/**
 * 角色管理
 * Class Role
 * @package app\admin\controller
 */
class Role extends Common{
    /**
     * 角色添加  作废！！！
     */
    public function roleAdd() {
        return view();
    }
    /**
     * 角色添加  第一个页面
     */
     public function roleListadd(){
         if(request()->isPost()){
             $this->checkRequest();
             $roleModel=model('Role');
             $roleModel->startTrans();
             try{
                #添加
                #写入角色表

                $insert=[];
                $now=time();
                $insert['role_name']=request()->param('role_name');
                $insert['status']=request()->param('status');
                $insert['ctime']=$now;
                $roleModel->insert($insert);
                $role_id=$roleModel->getLastInsID();
                #dump($role_id);exit;
                if($role_id<1){
                    throw new Exception('写入角色表失败');
                }
                #写角色关联的数据

                $post=$power=request()->param();
                $power=$post['power'];
                #dump($power);exit;
                $i=0;
                $new=[];
                foreach($power as $k=>$v){
                    $new[$i]['role_id']=$role_id;
                    $new[$i]['node_id']=$v;
                    $i++;
                }
                $role_node=model('RoleNode');
                $number=$role_node->insertAll($new);
                #dump($number);exit;
                if($number < 1){
                    throw new Exception('写入关联表失败');
                }
                $roleModel->commit();
                $this->successfullyLi();
             }catch(\Exception $e){
                $msg=$e->getMessage();
                $roleModel->rollback();
                $this->failLi($msg);
             }
         }else{

             return view();
         }
     }

}