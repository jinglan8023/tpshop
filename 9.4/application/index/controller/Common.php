<?php
namespace app\index\controller;
use think\Controller;
class Common extends Controller{
    /**
     * 防止非法登录
     * @return array|mixed
     */
    protected function checkUserLogin(){
        //防止非法登录
        if(!session('?userInfo')){
            //$this->error('请先登录',url('Login/login'));
            return [];
        }else{
            return session('userInfo');
        }

    }

    /**
     * 获取左侧分类信息
     * 获取导航栏信息
     */
    protected function getCateInfo(){
        //查询左侧分类数据
        $where=['cate_show'=>1];
        $info=model('Cate')->where($where)->select();
        $cateInfo=collection($info)->toArray();
        $data=getIndexCateInfo($cateInfo);
        $this->assign('data',$data);

        //查询导航栏显示
        $navwhere=['cate_navshow'=>1];
        $navInfo=model('Cate')->where($navwhere)->select();//print_r($navInfo);die;
        $navData=collection($navInfo)->toArray();//print_r($navData);die;
        $this->assign('navData',$navData);
    }

    /**
     * 根据分类id获取分类信息
     */
    protected function getCateInfoByCid($cate_id){
        #查询二级分类
        $where=[
            'cate_id'=>$cate_id
        ];
        $cateModel=model('Cate');//dump($cateModel);die;
        $cateInfo=$cateModel->where($where)->find();//dump($cateInfo);die;
        return $cateInfo;

    }

    /**
     * 从cookie 中获取浏览记录
     */
    protected function _getHistoryByCookie($show_all=1){
        $cookie_history=[];
        if(cookie('?history')){
            $cookie_History_str=cookie('?history');
            $cookie_history=json_decode(base64_decode($cookie_History_str),true);
        }
        #如果等于1 说明 是所有的
        if($show_all==1){
            return $cookie_history;
        }
        #处理浏览记录 会存在一个商品浏览了多次
        if(count($cookie_history)>1){
            foreach($cookie_history as $k=>$v){
                $new[$v['goods_id']]=$v;
            }
            $cookie_history=$new;
        }
        //dump($cookie_history);die;
        return $cookie_history;

    }

    /**
     * 返回用户id
     */
    public function getUid(){
        return session('userInfo.user_id');
    }
    /**
     *同步浏览记录
     */
    protected function asyncHistory(){
        #获取cookie中的浏览记录
        $cookie=$this->_getHistoryByCookie();
        #批量写入mysql数据库
        $goodModel=model('goods');
        #遍历cookie的数据
        if(!empty($cookie)){
            foreach($cookie as $k=>$v){
                $cookie[$k]['user_id']=$this->getUid();
            }
            if($goodModel->saveAll($cookie)){
                cookie('history',null);
            }
        }

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
     * 检查商品的库存
     * @param $goodsInfo 商品的基本信息
     * @param $buy_number 本次购买数量
     * @param $old_buy_number 之前购买数量
     */
    protected function checkGoodsSkuStock($goodsSkuInfo,$buy_number,$old_buy_number,$show_error=1){
        if($goodsSkuInfo['sku_stock']>200){
            $goodsSkuInfo['sku_stock']=200;
        }

        #单独购买不能超过库存
        if($buy_number>$goodsSkuInfo['sku_stock']){
            if($show_error){
                $this->failLi(
                    '商品'.$goodsSkuInfo['goods_name'].
                    '最多只能购买'.$goodsSkuInfo['sku_stock'].'件'
                );
            }else{
                return false;
            }

        }
        #累计购买不能超过库存
        if( ($old_buy_number+$buy_number) > $goodsSkuInfo['sku_stock'] ){
            $can_buy_number=$goodsSkuInfo['sku_stock']-$old_buy_number;
            if($can_buy_number>0){
                if($show_error){
                    $this->failLi(
                        '商品'.$goodsSkuInfo['goods_name'].
                        '最多只能购买'.$goodsSkuInfo['sku_stock'].'件,您已经购买了'.$old_buy_number.'件'.
                        '还可以购买'.( $goodsSkuInfo['sku_stock']-$old_buy_number ).'件'
                    );
                }else{
                    return false;
                }
            }else{
                if($show_error){
                    $this->failLi(
                        '商品'.$goodsSkuInfo['goods_name'].
                        '最多只能购买'.$goodsSkuInfo['sku_stock'].
                        '件,您已经购买了'.$goodsSkuInfo['sku_stock'].'不能继续购买了'
                    );
                }else{
                    return false;
                }
            }
        }
        return true;
    }
    /**
     *同步购物车数据
     */
    protected function asyncCar(){
        #从cookie 取出购物车数据
        $carStr=cookie('car');
        if($carStr){
            $cookie_car=json_decode(base64_decode($carStr),true);
        }else{
            $cookie_car=[];
        }
        //dump($cookie_car);die;
        #如果cookie 中不存在购物车数据  就不同步
        #cookie不为空  有数据
        if(!empty($cookie_car)){
            $carModel=model('car');
            $goodsSkuModel=model('GoodsSku');
            foreach($cookie_car as $k=>$v){
                $user_id=$this->getUid();
                $sku_id=$v['sku_id'];
                #判断数据库有没有这条数据
                $carWhere=[
                    'user_id'=>$user_id,
                   'sku_id'=>$sku_id,
                    'status'=>1
                ];
                #如果查询到 说明数据库存在这条记录
                $obj=$carModel->where($carWhere)->find();
                //dump($obj);die;
                if(!empty($obj)){
                    $carInfo=$obj->toArray();
                    #检查是否超过库存
                    $goods_where=[
                        'sku_id'=>$v['sku_id']
                    ];
                    //dump($carInfo);die;
                    $goodsSkuInfo=$goodsSkuModel->where($goods_where)->find()->toArray();
                    #检查库存
                    $check = $this->checkGoodsSkuStock(
                            $goodsSkuInfo,
                            $v['buy_number'],
                            $carInfo['buy_number'],
                            0
                        );

                    if($check){
                        $save=[];
                        $save['utime']=time();
                        $save['buy_number']=$carInfo['buy_number']+$v['buy_number'];
                    }else{
                        #修改数量 和 修改时间
                        $save=[];
                        $save['utime']=time();
                        #cookie中的数据 和 购物车中的 数据  加起来超过200 就给最大库存200
                        if($goodsSkuInfo['sku_stock']<200){
                            $all=$goodsSkuInfo['sku_stock'];
                        }else{
                            $all=200;
                        }
                        $save['buy_number']=$all;
                    }
                    $carModel->where($carWhere)->update($save);
                    return true;
               }else{
                    ##不存在 需要在数据库新添一个数据
                    $v['user_id']=$this->getUid();
                    $carModel->insert($v);
                    return true;
                }
            }
        }
        cookie('car',null);//清cookie
        return true;
    }

    /**
     * 读取cookie 中的购物车数据
     */
     protected function _getCookieCar(){
         #从cookie 取出购物车数据
         $carStr=cookie('car');
         if($carStr){
             $cookie_car=json_decode(base64_decode($carStr),true);
         }else{
             $cookie_car=[];
         }
         return $cookie_car;
     }

    /**
     * 读取数据库 中的一条购物车数据
     */
    protected function getGoodsInfo($goods_id){
        $where=[
            'goods_id'=>$goods_id
        ];
        $goodsModel=model('Goods');
        if($obj=$goodsModel->where($where)->find()){
            return $obj->toArray();
        }else{
            return $obj=[];
        }
    }
    //获取数据库里货品表的一条数据
    protected function getGoodsSkuInfo($sku_id){
        $where=[
            'sku_id'=>$sku_id
        ];
        $goodsModel=model('GoodsSku');
        if($obj=$goodsModel->where($where)->find()){
            return $obj->toArray();
        }else{
            return $obj=[];
        }
    }


###########################小购物车########################################
    //查询购物车数据   展示到小购物车   展示到购物车列表
    /**
     * 购物车列表
     * 登录 从数据库取数据
     * 未登录 从cookie取数据
     */
    public function carList(){
        #左侧数据 和 导航栏数据 展示出来
        $this->getCateInfo();

        #判断是否登录
        if( $is_login=$this->checkUserLogin()){
            #登录状态 从数据库取数据
            $cartGoodsInfo=$this->_getDbCar();
            ########################################################
            #如果该用户登录后账号下没有加入购物车的数据 就从他的cookie 里取数据
            #####同时将cookie 的数据存入数据库   找到user_id
            ################################################################
        }else{
            #从cookie 中读取购物车的数据
            $car=$this->_getCookieCar(); //dump($car);die;
            #查询购物车对应的商品货品数据
            if(!empty($car)){
                #不要在foreach循环里写查询sql语句
                foreach($car as $key=>$value){
                    $id_arr[]=$value['sku_id'];
                }
                $goods_where['sku_id']=['in',$id_arr];
                $goodsSkuModel=model('GoodsSku');
                $obj=$goodsSkuModel->where($goods_where)->select();//dump($obj);die;
                if($obj){
                    $cartGoodsInfo=collection($obj)->toArray();
                }else{
                    $cartGoodsInfo=[];
                }
                foreach($cartGoodsInfo as $k=> &$v){
                    $v=array_merge($v,$car[$v['sku_id']]);
                }
            }else{
                $cartGoodsInfo=[];
            }

        }
        //dump($is_login);die;
        if($is_login==[]){
            $login=0;
        }else{
            $login=1;
        }

        $this->assign('url',request()->url(true));
        $this->assign('login',$login);//登录后 购物车的数据
        $this->assign('cartGoodsInfo',$cartGoodsInfo);//未登录的cookie 数据

    }

    /**
     * 从Db获取购物车数据
     */
    private function _getDbCar(){
        $where=[
            'user_id'=>$this->getUid(),
            'c.status'=>1
        ];
        $carModel=model('Car');
        $obj=$carModel->table('shop_car c')
            ->join('shop_goods_sku gs','gs.sku_id=c.sku_id')
            ->where($where)
            ->select();

        if(!empty($obj)){
            return $goodsList=collection($obj)->toArray();
        }else{
            return [];
        }

    }

###########################小购物车#######################################





}
