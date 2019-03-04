<?php
namespace app\index\controller;
use think\image\Exception;
class Order extends Common{
    /**
     * 提交订单页面
     * @return mixed
     */
    public function order(){
        #左侧数据 和 导航栏数据 展示出来
        $this->getCateInfo();

        #取出购物车的数据
        $car_id_str=request()->get('car_id','');//dump($car_id_str);die;
        $car_id_arr=array_filter(array_map('intval',explode(',',$car_id_str)));
        //dump($car_id_arr);die;
        if(empty($car_id_arr)){
            $this->error('购物车的数据没有找到!' ,url('Car/carlist'));
        }
       // dump($car_id_arr);exit;
        $where=[
            'car_id'=>['in',$car_id_arr],
            'user_id'=>$this->getUid()
        ];
        $carModel=Model('Car');
        $obj=$carModel
            ->table('shop_car c')
            ->join('shop_goods_sku gs ','gs.sku_id=c.sku_id')
            ->where($where)
            ->select();
            //dump($obj);exit;
        if($obj){
            $carInfo=collection($obj)->toArray();
        }else{
            $this->error('购物车的数据没有找到' ,url('Car/carlist'));
        }
        #取出用户相应的收货地址
        //dump($carInfo);exit;

        $add_where=[
            'user_id'=>$this->getUid(),
            'status'=>1
        ];
        $addressModel=model('Address');
        $addressInfo= $addressModel->getAddressInfo($add_where)->toArray();
        $this->assign('addressInfo',$addressInfo);
        $this->assign('car',$carInfo);
        return $this->fetch('order');
    }

    /**
     * ajax  结算创建订单
     */
    public function submitOrder(){
        #判断请求是否合法
        $this->checkRequestLi();
        #接收数据 car_id
        $car_id_str=request()->param('car_id','');//dump($car_id_str);die;
        if(!$car_id_str){
            $this->error('购物车数据未找到!',url('Car/carlist'));
        }

        #接收支付方式

        #订单支付方式
        $order_pay_type=request()->param('pay_type',1,'intval');
        //dump($order_pay_type);die;

        #收货地址id
        $address_id=request()->param('address_id',0,'intval');
        //dump($address_id);die;
        if(!$address_id){
            $this->failLi('请选择您的收货地址');
        }

        $url=request()->domain().url('Order/submitOrder',['car_id'=>$car_id_str]);
        #没有登录不能结算
        if(!$this->checkUserLogin()){
            $this->error('还没有登录呢,请先登录!' ,
                url('login/login',['callback'=>urlencode( urlencode( $url ) ) ] )
            );
        }
        #查询购物车的数据
        $car_id_arr=array_filter(array_map('intval',explode(',',$car_id_str)));
        //dump($car_id_arr);die;
        if(empty($car_id_arr)){
            $this->error('购物车de数据未找到!',url('Car/carList'));
        }
        $car_where=[
            'car_id'=>['in',$car_id_arr],
            'user_id'=>$this->getUid(),
            'shop_car.status'=>1
        ];
        //dump($car_where);die;
        $carModel=model('Car');
        $obj=$carModel
            ->table('shop_car c')
            ->join('shop_goods_sku gs','gs.sku_id=c.sku_id')
            ->where($car_where)
            ->select();
        //dump($obj);die;
        if($obj){
            $carInfo=collection($obj)->toArray();
        }else{
            $carInfo=[];
            $this->error('购物车数据未找到!',url('Car/carlist'));
        }
        //dump($carInfo);die;
        #实例化订单模型
        $orderModel=model('Order');
        #开启事务
        $orderModel->startTrans();
        try{
            $now=time();
            $user_id=$this->getUid();
            # 1、写入订单表数据
            $order_no=$this->_createOrderNo();
            $order_insert=[];
            $order_insert['user_id']=$user_id;
            $order_insert['order_no']=$order_no;

            //dump($order_insert);die;

            #计算订单的金额
            $order_amount=0.00;
            #遍历购物车数据  计算总计额
            foreach ($carInfo as $key=>$value){
                $order_amount+=$value['buy_number']*$value['sku_price'];
            }
            $order_insert['order_paytype']=$order_pay_type;
            $order_insert['order_note'] =request()->param('note');
            $order_insert['order_amount']=$order_amount;
            #1、待支付
            if($order_pay_type==2){
            #货到付款的订单需要商家先确认  确认之后直接发货
                $order_insert['order_status'] =4;
            }else{
                $order_insert['order_status'] =1;
            }
            //dump($order_insert);die;
            $order_insert['ctime']=$now;
            $orderModel->insert($order_insert);

            $order_id=$orderModel->getLastInsID();
            //dump($order_id);die

            if($order_id<0){
                throw new \Exception('订单表写入失败,请重试!',100);
            }
            //dump($order_id);die;

            #2、 写入订单商品表
            $order_detail=[];
            //dump($carInfo);die;
            foreach($carInfo as $k=>$v){
                $order_detail[$k]['order_id']=$order_id;
                $order_detail[$k]['user_id']=$user_id;
                $order_detail[$k]['goods_id']=$v['sku_id'];
                $order_detail[$k]['buy_number']=$v['buy_number'];
                $order_detail[$k]['goods_name']=$v['sku_name'];
                $order_detail[$k]['goods_img']=$v['sku_img'];
                $order_detail[$k]['status']=1;
                $order_detail[$k]['ctime']=$now;
            }
            $order_detail_model=model('OrderDetail');
            $number=$order_detail_model->insertAll($order_detail);//dump($number);die;
            if($number < 1 ){
                throw new \Exception('详情表写入失败,请重试!',100);
            }

            #3、写入订单的收货地址表
            $order_address=[];
            $addressModel=model('Address');
            $address_where=[
                'address_id'=>$address_id,
                'user_id'=>$user_id,
                'status'=>1
            ];
            if($obj=$addressModel->where($address_where)->find()){
                $addressInfo=$obj->toArray();
            }else{
                throw new \Exception('没有找到对应的收货地址!',100);
            }

            $order_address['order_id']=$order_id;
            $order_address['user_id']=$user_id;
            $order_address['receive_name']=$addressInfo['address_man'];
            $order_address['receive_phone']=$addressInfo['address_tel'];
            $order_address['address_detail']=$addressInfo['address_detail'];
            $order_address['post_code']='000000';
            $order_address['ctime']=$now;
            $order_address['status']=1;

            $order_address_model=model('OrderAddress');

            if(!$order_address_model->insert($order_address)){
                throw new \Exception('订单收货地址写入失败',100);
            }

            #4、减商品的库存
            $goodsSkuModel=model('GoodsSku');
            foreach($carInfo as $k=>$v){
                $goods_where=[
                    'goods_id'=>$v['goods_id'],
                    'status'=>4
                ];

                #检查库存
                $check=$this->checkGoodsSkuStock($v,$v['buy_number'],0,0);
                //dump($check);die;
                if(!$check){
                    $orderModel->rollback();
                    if($v['sku_stock']>200){
                        $v['sku_stock']=200;
                    }
                    $this->failLi($v['goods_name'].'只能购买'.$v['sku_stock'].'件');
                }
                $goods_save=[
                    'sku_stock'=>$v['sku_stock']-$v['buy_number']
                ];
                $obj=$goodsSkuModel->where($goods_where)->update($goods_save);
                if(!$obj){
                    throw new \Exception('商品库存修改失败',100);
                }
            }

            #5、删除购物车的数据
            $car_save=[
                'status'=>2,
                'utime'=>$now
            ];
           if( !$carModel->where($car_where)->update($car_save)){
              throw new \Exception('购物车数据删除失败',100);
           }
           #提交事务
           $orderModel->commit();
            #返回成功
            $this->successfullyLi(['order_no'=>$order_no]);
        }catch (\Exception $e){
            $orderModel->rollback();
            $this->failLi($e->getMessage());
        }

    }

    /**
     * 支付宝支付
     */
    public function alipay(){
        $orderInfo=$this->getOrderInfo();
        #dump($orderInfo);die;
        #########################支付宝支付
        $config=config('ali_pay_config');
        #dump($config);die;
        require_once EXTEND_PATH .'alipay/pagepay/service/AlipayTradeService.php';
        require_once EXTEND_PATH .'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $orderInfo['order_no'];

        //订单名称，必填
        $subject = "tp5--电商--支付宝支付";

        //付款金额，必填
        $total_amount = $orderInfo['order_amount'];

        //商品描述，可空
        $body = "这是我购买的商品哟";

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new \AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        //输出表单
        var_dump($response);
       
    }

    /**
     * 支付宝同步通知
     */
    public function paySuccess(){
        $param=request()->param();#dump($param);die;
        $order_no=$param['out_trade_no'];
        $order_info=$this->getOrderInfo($order_no);

        #验证订单号和订单金额是否正确
        if($order_info['order_no']!=$param['out_trade_no']){
            $this->error('订单号未找到','Index/index');
        }
        if($order_info['order_amount']!=$param['total_amount']){
            $this->error('订单额金额不正确','Index/index');
        }

###################支付宝同步支付通知  需要验证片名是否正确  {防止在传输过程被修改}##########
        $config=config('ali_pay_config');

        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';

        $arr=$_GET;
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($arr);

        if($result){
            $this->assign('order',$order_info);
            return view();
        }else{
            $this->error('订单数据未找到','Index/index');
        }


    }

    /**
     * 支付宝异步通知
     */
    public function notify(){
        #接受支付宝异步通知的数据
        $ali_param=request()->param();#dump($ali_param);die;
        file_put_contents('/data/wwwroot/default/9.4/alipay.log',print_r($ali_param,true),FILE_APPEND);
        #接受订单号
        $order_no = $ali_param['out_trade_no'];

        #验证是否是支付宝异步通知
        ####################################支付宝同步支付通知  需要验证片名是否正确  {防止在传输过程被修改}##########
        $config=config('ali_pay_config');
        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';

        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($ali_param);

        #验证不成功  返回提示错误
        if(!$result){
            file_put_contents('/data/wwwroot/default/9.4/alipay.log','check sbin fail',FILE_APPEND);
            echo 'check sbin fail';
            exit;
        }


        #获取一下订单信息
        $order_info=$this->getOrderInfo($order_no,1);

        #判断订单金额是否正确
        if($order_info['order_amount']!=$ali_param['total_amount']){
            file_put_contents('/data/wwwroot/default/9.4/alipay.log','订单金额不正确',FILE_APPEND);
            echo '订单金额不正确';
            exit;
        }

        #验证appid是否正确
        if($ali_param['app_id'] !=$config['app_id']){
            file_put_contents('/data/wwwroot/default/9.4/alipay.log','appid is error',FILE_APPEND);
            echo 'appid is error';
            exit;
        }

        #判断订单状态 是否是未支付   只未支付的订单才需要修改订单状态
        if($order_info['order_status']>1){
            file_put_contents('/data/wwwroot/default/9.4/alipay.log','success1',FILE_APPEND);
            echo 'success';
            exit;
        }

        if($order_info['order_status'] == 1){
            #修改数据库的订单状态已支付【2】
            $where=[
                'order_no'=>$order_no,
            ];

            #修改数据
            $save=[
                'order_status'=>2,
                'pay_time'=>time(),
                'utime'=>time()
            ];
            $order_model=model('Order');
            if($order_model->where($where)->update($save)){
                file_put_contents('/data/wwwroot/default/9.4/alipay.log','success2',FILE_APPEND);
                echo 'success';
            }else{
                file_put_contents('/data/wwwroot/default/9.4/alipay.log','update fail',FILE_APPEND);
                echo 'fail';
            }
            exit;
        }

    }

    /**
     * 获取订单信息
     * @param string $order_no_param
     * @return array
     */
    private function getOrderInfo($order_no_param=''){
        $order_no=request()->param('order_no','');#dump($order_no);die;
        if($order_no_param){
            $order_no=$order_no_param;
        }
        if(empty($order_no)){
            $this->error('没有找到你要查看的订单!');
        }
        $orderModel=model('Order');
        $order_where=[
            'order_no'=>$order_no,
            'user_id'=>$this->getUid()
        ];
        #dump($order_where);die;
        $obj=$orderModel->where($order_where)->find();
        #dump($obj);die;
        if($obj){
            $orderInfo=$obj->toArray();
        }else{
            $this->error('没有你要查看的订单!');
        }
        //dump($orderInfo);die;
        return $orderInfo;

    }
    /**
     * 生成订单编号ok
     */
     public function _createOrderNo(){
        #订单号规则
        #业务线(1位)+ 时间(6 位 181022) +用户id + 4位随机数
        $uid=$this->getUid();

        if($uid < 10000){
            $uid=str_repeat( 0 , 4 - strlen ( $uid )) . $uid ;
        }else{
            $uid=substr( $uid , -4 , 4 );
        }
        //echo $uid;die;
       return 1 . date ( 'ymd' ) . $uid . rand ( 1000 , 9999 );
         # dump(1 .date('ymd') . $uid . rand ( 1000 , 9999 ));die;
     }


    /**
     * 订单成功提交 第三个页面  单纯的展示
     */
     public function createSuccess(){
         #左侧数据 和 导航栏数据 展示出来
        $this->getCateInfo();

         $order_no=request()->param('order_no','');#dump($order_no);die;
         if(empty($order_no)){
             $this->error('没有找到你要查看的订单!');
         }
         $orderModel=model('Order');
         $order_where=[
             'order_no'=>$order_no,
             'user_id'=>$this->getUid()
         ];
         #dump($order_where);die;
         $obj=$orderModel->where($order_where)->find();
         #dump($obj);die;
         if($obj){
             $orderInfo=$obj->toArray();
         }else{
             $this->error('没有你要查看的订单!');
         }

        $this->assign('order',$orderInfo);
         return view();
     }

    /**
     * 我的订单页面ok
     */
    public function myorder(){
        #取出所有用户的订单
        if(!$this->checkUserLogin()){
            $this->error('请先登录','Login/login');
        }
        $where=[
            'o.user_id'=>$this->getUid(),
        ];
        $order_model=model('Order');
        $order_list=$order_model
                    ->table('shop_order o')
                    ->field('o.*,GROUP_CONCAT(CONCAT(goods_name,\':\',goods_price,\'*\',buy_number,\'<br>\'))as buy')
                    ->join('shop_order_detail od','o.order_id=od.order_id')
                    ->group('o.order_id')
                    ->where($where)
                    ->paginate(3);
                    #echo $order_model->getLastSql();die;
                    #dump($order_list);die;
       # $order_list=collection($obj)->toArray();#dump($order_list);die;
        $this->assign('orderlist',$order_list);
        return view();
    }

    /**
     * 订单详情页+物流信息ok
     */
    public function orderDetail(){
        #接口地址
        $url='http://route.showapi.com/64-19';
        #快递接口秘钥
        $secret="e0d8a8c637914f07bc87e286e7149eec";
        #快递接口需要的参数
        $param=[
            'showapi_appid'=>'78679',
            'com'=>'auto',
            'nu'=>'3823766635262' #快递单号
        ];
        ksort($param);
        $str='';
        foreach($param as $key=>$value){
            $str.=$key.$value;
        }
        $str=$str.$secret;
        $sign=md5($str);
        $param['showapi_sign']=$sign;
        $info=json_decode( file_get_contents( $url.'?'.http_build_query($param) ),true );#物流信息
        #dump($info);die;
        $this->assign('expressInfo',$info);
        return view();
    }

}
