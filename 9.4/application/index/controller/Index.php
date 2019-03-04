<?php
namespace app\index\controller;

class Index extends Common{
    public function index(){
    //左侧
        $where=['cate_show'=>1];
        $info=model('Cate')->where($where)->select();
        $cateInfo=collection($info)->toArray();
        $data=getIndexCateInfo($cateInfo);
        $this->assign('data',$data);


        //处理楼层  1 楼
        $cate_id=1;
        $floorInfo=$this->getFloorInfo($cate_id,$cateInfo);//print_r($floorInfo);die;
        $this->assign('floorInfo',$floorInfo);

        //导航栏显示
        $navwhere=['cate_navshow'=>1];
        $navInfo=model('Cate')->where($navwhere)->select();//print_r($navInfo);die;
        $navData=collection($navInfo)->toArray();//print_r($navData);die;
        $this->assign('navData',$navData);

        //小购物车掉公共common  购物车列表
        $this->carList();


        //return '前台';//默认走index/index
        //$this->view->engine->layout(false);//关闭模板布局
        return view();
    }


    /**获取楼层数据*/
    public function getFloorInfo($cate_id,$cateInfo){
        //分类id 为1 的分类信息
        foreach($cateInfo as $k=>$v){
            if($v['cate_id']==$cate_id){
                $floorInfo=$v;
            }
        }
        //print_r($floorInfo);die;
        //分类id 为1 的子类  二级分类
        foreach($cateInfo as $k=>$v){
            if($v['pid']==$cate_id){
                $floorInfo['cateList'][]=$v;
            }
        }
        //print_r($floorInfo);die;
        //当前分类下 每一级的 所有分类id
        $c_id=getCateId($cate_id,$cateInfo);
        $where=['goods_up'=>1,'cate_id'=>['IN',$c_id]];
        //print_r($c_id);die;

        //根据所有分类id 查询商品信息
       $floorInfo['goodsList']=collection(
                        model('Goods')
                        ->field('goods_id,goods_name,goods_selfprice,goods_img')
                        ->where($where)
                        ->select()
                    )
                        ->toArray();
       //print_r($floorInfo);die;
      //echo  model('Goods')->getLastSql();die;
        return $floorInfo;
    }


}
