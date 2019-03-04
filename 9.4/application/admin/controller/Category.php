<?php
 namespace app\admin\controller;
 //use think\Controller;
 class Category extends Common{
     //分类添加
    public function cateAdd(){
        if(request()->isPost()&&request()->isAjax()){
            $arr=input("post.");
            $res=model('Category')->save($arr);//dump($res);die;
            if($res){
                echo json_encode(['font'=>'添加成功','code'=>1]);
            }else{
                echo json_encode(['font'=>'添加失败','code'=>2]);
            }
        }else{
            //查询分类信息 作为下拉菜单
            $where=['cate_show'=>1];
            $cateInfo=model('Category')->where($where)->select();
            //print_r($cateInfo);die;
            $data=getCateInfo($cateInfo);
            $this->assign('data',$data);
            return view();
        }


    }
    //分类展示
    public function cateList(){
    //查询所有分类信息
        $where=['cate_show'=>1];
        $cateInfo=model('Category')->where($where)->select();
        //print_r($cateInfo);die;
        $data=getCateInfo($cateInfo);
        $this->assign('data',$data);
        return view();
    }

    //即点即改
    public function cateU(){
        $data=input('post.');//dump($data);
        $where=['cate_id'=>$data['id']];
        $info=[$data['name']=>$data['val']];
        $res=model('Category')->where($where)->update($info);
        if($res){
            echo json_encode(['font'=>'ok','code'=>1]);
        }else{
            echo json_encode(['font'=>'no','code'=>2]);
        }
    }

    //删除
    public function cateDel(){
        $cate_id=input("get.cate_id");
        if(empty($cate_id)){
            exit("非法操作此页面");
        }
       //echo($cate_id);die;
        //查此分类下是否有子类
        $cate_where=['pid'=>$cate_id];
        $cateInfo= model('Category')->where($cate_where)->find();
        //dump($cateInfo);die;
        if(!empty($cateInfo)){
            exit("此分类下有子类或商品a");
        }
        //验证此分类下是否有商品
        $where=['cate_id'=>$cate_id];
        $goodsInfo=model('Goods')->where($where)->find();
        if(!empty($goodsInfo)){
            exit("此分类下有子类或商品b");
        }
        $res=model('Category')->where($where)->delete();
        if($res){
            $this->success('删除ok',url('Category/cateList'));
        }else{
            $this->error('删除失败');
        }

    }


    //显示修改的视图
    public function cateUpdate(){
        //查询分类信息 作为下拉菜单
        $where=['cate_show'=>1];
        $cateInfo=model('Category')->where($where)->select();
        //print_r($cateInfo);die;
        $data=getCateInfo($cateInfo);
        //接收要修改的id
        $cate_id=input('get.cate_id');
        //根据id查询数据
        $arr=model('Category')->where(['cate_id'=>$cate_id])->find();
        $this->assign('arr',$arr);
        $this->assign('data',$data);
        return view("Category/cateUpdate");
    }

    //执行修改
  public function cateUpdateDo(){
      //得到新值
      $data=input('post.');
      //dump($data);die;

      $cateModel=model('Category');
      $arr=$cateModel->update($data);
      if($arr){
         $this->success('修改成功',url('Category/cateList'));
      }else{
          $this->error('修改失败');
      }
  }

 }
