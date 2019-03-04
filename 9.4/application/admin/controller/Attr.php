<?php
namespace app\admin\controller;
/**
 * 商品属性
 * Class Attr
 * @package app\admin\controller
 */
class Attr extends Common{
    /**
     * 商品基本属性
     * @return mixed
     */
    public function basicAttr(){
        if(request()->isPost()){
            #接值
            $post=request()->param();
            $basic_model=model('Basic');
            $basic_model->startTrans();

            try{
                $basic_insert=[];
                $now=time();
                foreach( $post['attr'] as $k=>$v ){
                    $basic_insert['cate_id']=$post['cate_id'];
                    $basic_insert['attr_name']=$v;
                    $basic_insert['status']=1;
                    $basic_insert['ctime']=$now;

                    if( !$basic_model -> insert($basic_insert) ){
                        #抛出异常
                        throw new \Exception('插入属性表失败');
                    }

                    $basic_id=$basic_model->getLastInsID();

                    #判断有没有属性值
                    $basic_value_model=model('BasicValue');
                    if(isset($post['value'][$k])){
                        #写入属性值表
                        $value_insert = [];
                        foreach($post['value'][$k] as $kk=>$vv){
                            $value_insert[$kk]['cate_id']=$post['cate_id'];
                            $value_insert[$kk]['basic_id']=$basic_id;
                            $value_insert[$kk]['attr_value']=$vv;
                            $value_insert[$kk]['status']=1;
                            $value_insert[$kk]['ctime']=$now;
                        }
                       $number= $basic_value_model -> insertAll( $value_insert );
                       if( $number < 1){
                           #抛出异常
                           throw new \Exception('插入属性值失败');
                       }
                    }
                }
                $basic_model->commit();
                $this->successfullyLi();

            }catch (\Exception $e){
                $basic_model->rollback();
                $this->failLi( $e->getMessage() );
            }
        }else{
            #先获取系统的商品分类
            $cate_model=model('Category');
            $c_where=[
                'status'=>1
            ];
            $cate_obj=$cate_model->where($c_where)->select();
            $cateList=collection($cate_obj)->toArray();
            #调总的公共方法里的递归查询分类
            $cateInfo=getCateInfo($cateList);
            $this->assign('cateInfo',$cateInfo);

            return $this->fetch();
        }
    }

    /**
     * 销售属性
     * @return mixed
     */
    public function saleAttr(){
        if(request()->isPost()){
            #接值
            $post=request()->param();
            $sale_model=model('Sale');
            $sale_model->startTrans();

            try{
                $basic_insert=[];
                $now=time();
                foreach( $post['attr'] as $k=>$v ){
                    $basic_insert['cate_id']=$post['cate_id'];
                    $basic_insert['attr_name']=$v;
                    $basic_insert['status']=1;
                    $basic_insert['ctime']=$now;

                    if( !$sale_model -> insert($basic_insert) ){
                        #抛出异常
                        throw new \Exception('插入属性表失败');
                    }

                    $basic_id=$sale_model->getLastInsID();

                    #判断有没有属性值
                    $sale_value_model=model('SaleValue');
                    if(isset($post['value'][$k])){
                        #写入属性值表
                        $value_insert = [];
                        foreach($post['value'][$k] as $kk=>$vv ){
                            $value_insert[$kk]['cate_id']=$post['cate_id'];
                            $value_insert[$kk]['sale_id']=$basic_id;
                            $value_insert[$kk]['attr_value']=$vv;
                            $value_insert[$kk]['status']=1;
                            $value_insert[$kk]['ctime']=$now;
                        }
                        $number= $sale_value_model -> insertAll( $value_insert );
                        if($number < 1){
                            #抛出异常
                            throw new \Exception('插入属性值失败');
                        }
                    }
                }
                $sale_model->commit();
                $this->successfullyLi();

            }catch (\Exception $e){
                $sale_model->rollback();
                $this->failLi( $e->getMessage() );
            }
        }else{
            #先获取系统的商品分类
            $cate_model=model('Category');
            $c_where=[
                'status'=>1
            ];
            $cate_obj=$cate_model->where($c_where)->select();
            $cateList=collection($cate_obj)->toArray();
            #调总的公共方法里的递归查询分类
            $cateInfo=getCateInfo($cateList);
            $this->assign('cateInfo',$cateInfo);

            return $this->fetch();
        }
    }

    /**
     * 基本属性展示
     */
    public function basicAttrShow(){
        //$this->checkRequest();
        $cate_id=request()->param('cate_id');

        #获取分类对应的属性信息
        $basic_model=model('Basic');

        #查询启用的属性
        $where=[
            'a.status'=>1,
            'a.cate_id'=>$cate_id
        ];
        $basic_obj = $basic_model
                    ->field('a.*,v.attr_value,v.basic_value_id')
                    ->table( 'shop_basic_attr a' )
                    ->join( 'shop_basic_attr_value v' , 'a.basic_id=v.basic_id' , 'left')
                    ->where($where)
                    ->select();
        #dump($basic_obj);die;
        #echo $basic_model->getLastsql();
        $basic_arr=collection($basic_obj)->toArray();
        //dump($basic_arr);die;
        $new=[];
        foreach($basic_arr as $key => $value) {
            $new[$value['basic_id']]['attr_id']=$value['basic_id'];
            $new[$value['basic_id']]['attr_name']=$value['attr_name'];
            if($value['basic_value_id']){
                $new[$value['basic_id']]['has_son']=1;
                $new[$value['basic_id']]['son'][$value['basic_value_id']]=$value['attr_value'];
            }else{
                $new[$value['basic_id']]['has_son']=0;
            }
        }

        //dump($new);exit;
        $this->view->engine->layout(false);
        $this->assign('basic',$new);
        return $this->fetch();


    }

    /**
     * 销售属性展示
     */
     public function saleAttrShow(){
         //$this->checkRequest();
         $cate_id=request()->param('cate_id');
         //  $cate_id=66;
         #获取分类对应的属性信息
         $sale_model=model('Sale');

         #查询启用的属性
         $where=[
             's.status'=>1,
             's.cate_id'=>$cate_id
         ];
         $sale_obj = $sale_model
             ->field('s.*,v.attr_value,v.sale_value_id')
             ->table( 'shop_sale_attr s' )
             ->join( 'shop_sale_attr_value v' , 's.sale_id=v.sale_id' , 'left')
             ->where($where)
             ->select();
         //var_dump($sale_obj);die;
         #echo $sale_model->getLastsql();
         $sale_arr=collection($sale_obj)->toArray();
         //dump($sale_arr);die;
         $new=[];
         foreach($sale_arr as $key => $value) {
             $new[$value['sale_id']]['attr_id']=$value['sale_id'];
             $new[$value['sale_id']]['attr_name']=$value['attr_name'];
             if($value['sale_value_id']){
                 $new[$value['sale_id']]['has_son']=1;
                 $new[$value['sale_id']]['son'][$value['sale_value_id']]=$value['attr_value'];
             }else{
                 $new[$value['sale_id']]['has_son']=0;
             }
         }
         //print_r($new);exit;
         $this->view->engine->layout(false);
         $this->assign('sale',$new);
         return $this->fetch();


     }


}