<?php
namespace app\admin\model;
use think\Model;
class Goods  extends Model{
    protected $table="shop_goods";
    //定义时间戳字段名
    protected $createTime='ctime';
    protected $updateTime='utime';

    /**商品信息*/
    //加个left id 顺序排
    public function goodsInfo($where,$p,$page_num){
        $data=$this->field('shop_goods.*,cate_name,brand_name')->alias('g')->join('shop_category c','g.cate_id=c.cate_id')->join('shop_brand b','b.brand_id=g.brand_id','left')->where($where)->page($p,$page_num)->select();
        //echo $this->getLastSql();die;
        foreach($data as $k=>$v){
            if($v['goods_up']==1){
                $data[$k]['goods_up']='√';
            }else{
                $data[$k]['goods_up']='×';
            }
            if($v['goods_new']==1){
                $data[$k]['goods_new']='√';
            }else{
                $data[$k]['goods_new']='×';
            }
            if($v['goods_best']==1){
                $data[$k]['goods_best']='√';
            }else{
                $data[$k]['goods_best']='×';
            }
            if($v['goods_hot']==1){
                $data[$k]['goods_hot']='√';
            }else{
                $data[$k]['goods_hot']='×';
            }






        }
        return $data;

        //$count=$this->where($where)->count();
    }

    /**商品记录条数*/
    public function goodsCount($where){
       return $count=$this->alias('g')->join('shop_category c','g.cate_id=c.cate_id')->join('shop_brand b','b.brand_id=g.brand_id')->where($where)->count();

    }

}


