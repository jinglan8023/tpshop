<?php
namespace app\index\controller;
class Car extends Common{
    /**
     * 购物车添加
     */
    //分 登陆前   登陆后
    public function carAdd(){
        #判断请求是否合法
        $this->checkRequestLi();

        #接收商品id
        $sku_id=request()->param('sku_id','','intval');
        if(empty($sku_id)){
            $this->failLi('要购买的商品没有找到!');//没有接收到
        }

        #查询商品的货品是否存在
        $goodsModel=model('GoodsSku');
        $where=[
            'sku_id'=>$sku_id,
        ];
        $goodsObj=$goodsModel->where($where)->find();
        //dump($goodsObj);die;
        if( !empty($goodsObj) ){
            $goodsInfo=$goodsObj->toArray();
        }else{
            $goodsInfo=[];
            $this->failLi('要购买的商品没有找到!');//商品id  恶意数据
        }
        //dump($goodsInfo);die;
        #接收购买的数量
        $buy_number=request()->param('buy_number',0,'intval');
        if( empty($buy_number) ){
            $this->failLi('请添加购买的数量!');
        }

        #判断是否登录
        if($this->checkUserLogin()){
            $this->_addCarByDb($goodsInfo,$buy_number);
        }else{
            $this->_addCarByCookie($goodsInfo,$buy_number);
        }

    }
    /**
     * 没有登录的情况下  购物车数据存cookie
     */
    private function _addCarByCookie($goodsSkuInfo,$buy_number){

        #从cookie 取出购物车数据
        $carStr=cookie('car');
        if($carStr){
            $cookie_car=json_decode(base64_decode($carStr),true);
        }else{
            $cookie_car=[];
        }
        $now=time();
        //dump($cookie_car);die;
        foreach($cookie_car as $k=>$v){
            $cookieCar=$v;
        }
       // dump($cookieCar);die;
        #判断是否购买过该此货品
        if(isset($cookieCar ['sku_id'])){
            #积累(多次)购买
            $this->checkGoodsSkuStock(
                        $goodsSkuInfo,
                        $buy_number,
                $cookieCar['buy_number']
                     );
            $cookieCar['buy_number']+=$buy_number;
            $cookieCar['utime']+=$now;
            $all_car=$cookie_car;
        }else{
            #单次 购买
            $this->checkGoodsSkuStock(
                $goodsSkuInfo,
                $buy_number,
               0
            );

            #在cookie里写入本次购买的数据 字段
            $this_car=[
                $goodsSkuInfo['sku_id']=>[
                    'sku_id'=>$goodsSkuInfo['sku_id'],
                    'buy_number'=>$buy_number,
                    'sku_img'=>$goodsSkuInfo['sku_img'],
                    'ctime'=>$now,
                    'utime'=>$now,
                    'add_price'=>$goodsSkuInfo['sku_price'],
                ]
            ];
            $all_car = $this_car + $cookie_car;
        }
            //dump($all_car);die;
            cookie('car',base64_encode ( json_encode ($all_car) ) );
            $this->successfullyLi();
    }
    /**
     * 登陆的情况下  购物车数据存 数据库
     */
    private function _addCarByDb($goodsSkuInfo,$buy_number){
        #判断购物车是否有该商品
        $where=[
            'sku_id'=>$goodsSkuInfo['sku_id'],
            'user_id'=>$this->getUid(),
            'status'=>1
        ];
        $carModel=model('Car');
        if($obj=$carModel->where($where)->find()){
            $carInfo=$obj->toArray();
        }else{
            $carInfo=[];
        }
        $now=time();
        #查询到数据 说明之前添加过此数据  需要修改数量
        if(!empty($carInfo)){

            #检查商品的库存
            $this->checkGoodsSkuStock($goodsSkuInfo,$buy_number,$carInfo['buy_number']);

            $save=[];
            $save['buy_number']=$carInfo['buy_number']+$buy_number;
            $save['utime']=$now;

            if($carModel->where($where)->update($save)){
                $this->successfullyLi();
            }else{
                $this->failLi('添加失败');
            }
        }else{
            #检查商品的库存
            $this->checkGoodsSkuStock($goodsSkuInfo,$buy_number,0);

            $insert=[];
            $insert['sku_id']=$goodsSkuInfo['sku_id'];
            $insert['user_id']=$this->getUid();
            $insert['add_price']=$goodsSkuInfo['sku_price'];
            $insert['buy_number']=$buy_number;
            #购物车的状态 1 正常  2 删除
            $insert['status']=1;
            $insert['ctime']=$now;

            if( $carModel->insert($insert) ){
                $this->successfullyLi();
            }else{
                $this->failLi('添加失败');
            }
        }
    }


    //点击去购物车列表  购物车第一步购物车列表  top上面的小弹框购物车
    public function buyCarOne(){
        //左侧头部导航
        $this->getIndexCateInfo();
        $this->getIndexCateNavInfo();
        //查询购物车数据
        if(!$this->getLogin()){
            $cartcookie=cookie('cart');//从cookie取购物车数据
            //echo $cartcookie;exit;
            if(empty($cartcookie)){
                $this->error('您的购物车中没有商品，请添加','product/productlist');
            }
        }
        return view();
    }

    /**
     * 购物车列表
     */
    public function carList(){
        #左侧数据 和 导航栏数据 展示出来
        $this->getCateInfo();

        #判断是否登录
        if( $is_login=$this->checkUserLogin()){
            #登录状态 从数据库取数据
            $goodsList=$this->_getDbCar();
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
                    $goodsList=collection($obj)->toArray();
                }else{
                    $goodsList=[];
                }
                foreach($goodsList as $k=> &$v){
                    $v=array_merge($v,$car[$v['sku_id']]);
                }
            }else{
                $goodsList=[];
            }
            $where=[
                'sku_id'=>$value['sku_id']
            ];

            $sku_img=$goodsSkuModel->where($where)->value('sku_img');
            //echo $sku_img;die;
        }
        //dump($is_login);die;
        if($is_login==[]){
            //没登录
            $login=0;
        }else{
            //已登录
            $login=1;
        }

        $this->assign('url',request()->url(true));
        $this->assign('login',$login);//登录后 购物车的数据
        //dump($goodsList);die;
        $this->assign('car',$goodsList);//未登录的cookie 数据
        return $this->fetch();


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





    /**
     *  ajax 修改购买数量
     */
     public function carUpdate(){
        #判断请求是否合法
        $this->checkRequestLi();
        #接收sku_id
        $sku_id=request()->param('sku_id',0,'intval');//dump($goods_id);die;
        if(!$sku_id){
            $this->failLi('商品没有找到!');
        }
        #购买的数量buy_number
        $buy_number=request()->param('buy_number',0,'intval');//dump($buy_number);die;
        if(!$buy_number){
            $this->failLi('请输入您要购买的数量');
        }

        //判断用户是否登录
        //※if else 中间 只有 一行 就可以不用写{ }  if(1) echo 1 else echo 2
        if($this->checkUserLogin()){
            $result= $this->_updateCarByDb($sku_id,$buy_number);
        }else{
            $result= $this->_updateCarByCookie($sku_id ,$buy_number);
        }
        sleep(2);
        if($result){
            $this->successfullyLi('ok');
        }else{
            $this->failLi('修改购物车数据失败,请重试');
        }

     }

     /**
      * 修改cookie中的购物车的数据
      * 参数前面加类型  要求传入的这个参数 必须符合这个类型(如request 对象)
      * _updateCarByCookie( int $goods_id ,int $buy_number)  但是报错
      */
     private function _updateCarByCookie($sku_id ,$buy_number ){
        #判断这个用户是否购买过这个商品
        $car=$this->_getCookieCar();//dump($car);die;
        //dump($car[$goods_id]);die;
        #没有购买过商品 直接返回提示信息
        if(!isset($car[$sku_id])){
            $this->failLi('没有找到要修改的数据!');
        }
        #如果购买过该商品 需要修改购物车的数据
        $goodsSkuInfo=$this->getGoodsSkuInfo($sku_id);
        if(empty($goodsSkuInfo)){
            $this->failLi('要修改的数据不正确');
        }
        #检查商品的库存
        $this->checkGoodsSkuStock($goodsSkuInfo,$buy_number,0);
        #修改购物车里的数量
        $car[$sku_id]['buy_number']=$buy_number;
         $car[$sku_id]['utime']=time();
         //dump($car);die;
        #修改完之后重新写到cookie 里去
        cookie('cart',base64_encode(json_encode($car)));
        return true;
     }

    /**
     * 修改数据库里购物车的数量
     * @param $goods_id
     * @param $buy_number
     */
    private function _updateCarByDb($sku_id,$buy_number){
        #判断这个用户是否购买过这个商品
        $where=[
            'status'=>1,
            'sku_id'=>$sku_id,
            'user_id'=>$this->getUid()
        ];
        $carModel=model('Car');
        $obj=$carModel->where($where)->find();//dump($obj);die;
        if(!$obj){
            $this->failLi('没有找到要购物车的数据!');
        }

        #如果购买过该商品 需要修改购物车的数据
        $goodsSkuInfo=$this->getGoodsSkuInfo($sku_id);
//        dump($goodsInfo);die;
        if(empty($goodsSkuInfo)){
            $this->failLi('要修改的数据不正确');
        }
        #检查商品的库存
        $this->checkGoodsSkuStock($goodsSkuInfo,$buy_number,0);

        #修改购物车里的数量
        $save=[];
        $save['buy_number']=$buy_number;
        $save['utime']=time();
        if($carModel->where($where)->update($save)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除
     * 删除购物车数据
     * 因购物车分为登录前和登录后so
     *单删和批删功能也要考虑DB和Cookie操作
     *因走一个共同的方法所以找其共同元素car_id 和 sku_id
     */
    public function carDel(){
        if($this->checkUserLogin()){
            //登录后  从数据库取
            $car_id=input('post.car_id');
            if(empty($car_id)){
                fail('操作失败');
            }
            $car_id=explode(',',$car_id);
            $where=[
                'car_id'=>['in',$car_id]
            ];
            $car=model('car');
            $res=$car->where($where)->delete();
            if($res){
                successfully('删除成功');
            }else{
                fail('删除失败');
            }
        }else{
            //登录前从cookie里取
            $sku_id=input('post.sku_id');
            if(empty($sku_id)){
                fail('操作失败');
            }
            $sku_id=explode(',',$sku_id);
            $car=cookie('car');
            if(empty($car)){
                $this->error('您的购物车中没有商品，请先添加','product/productlist');
            }
            $carInfo=unserialize(json_decode($car));
            foreach($sku_id as $k=>$v){
                unset($carInfo[$v]);
            }
            if(!empty($carInfo)){
                $car=base64_encode(serialize($carInfo));
                cookie('car',$car,24*60*60);
            }else{
                cookie('car',null);
            }
            successfully('删除成功');
        }
    }

    /**
     * 商品收藏
     * 接受sku_id
     * 单收藏  批量收藏都进一个方法
     */
    public function addCollection(){
        if(!request()->isPost()){
            fail('请按正确流程操作');
        }
        if(!$this->checkUserLogin()){
            fail('请先登录');
        }
        $type=input('post.type',1);
        $sku_id=input('post.sku_id',0);
        if(empty($sku_id)){
            fail('请选择需要收藏的商品');
        }
        $userGoods_model=model('UserGoods');
        if($type==1){
            $data=[
                'user_id'=>$this->getUid(),
                'sku_id'=>$sku_id
            ];
            $count=$userGoods_model->where($data)->count();
            if($count>0){
                fail('此商品已收藏');
            }
            $res=$userGoods_model->insert($data);
            if($res){
                successfully('收藏成功');
            }else{
                fail('收藏失败');
            }
        }else{
            $sku_id=explode(',',$sku_id);
            print_r($sku_id);exit;
            $num=0;
            foreach($sku_id as $k=>$v) {
                $data = [
                    'user_id' => $this->getUid(),
                    'sku_id' => $v
                ];
                //print_r($data);
                $count = $userGoods_model->where($data)->count();
                //$num = 0;
                if ($count == 0) {
                    $res = $userGoods_model->insert($data);
                } else {
                    $num += 1;
                }
            }
            //exit;
            if(!empty($res)){
                if($num>0){
                    fail("您有{$num}件商品已加入收藏");
                }
                successly('加入收藏成功');
            }else{
                fail('所有商品都已收藏');
            }
        }
    }


}


