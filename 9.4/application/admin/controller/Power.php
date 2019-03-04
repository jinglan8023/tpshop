<?php
namespace app\admin\controller;
class Power extends Common{
    /**
     * 权限管理添加
     * @return \think\response\View
     */
    public function powerAdd(){
        $powerModel=model('PowerNode');

        if(request()->isPost()&&request()->isAjax()){
            #接收数据  写入数据库
            $insert=request()->param('');
            #dump($insert);die;
            $insert['ctime']=time();
            if($insert['pid']==0){
                $insert['level']=1;
            }else{
                $insert['level']=2;
            }
            if($powerModel->insert($insert)){
                $this->successfully('ok');
            }else{
                $this->fail('添加失败');
            }

        }else{
            #查询系统现在有的一级菜单
            $where=[
                'pid'=>0,
                'level'=>1
            ];
            $powerModel=model('PowerNode');
            $menuobj=$powerModel->where($where)->select();
            $menu=collection($menuobj)->toArray();
            #dump($menu);die;
            $this->assign('menu',$menu);
            return view();
        }

    }

    /**
     * 权限管理展示
     * @return \think\response\View
     */
    public function powerList(){
        return view();
    }
}
