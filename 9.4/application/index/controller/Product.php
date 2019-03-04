<?php
namespace app\index\controller;
use page\AjaxPage;
class Product extends Common{
    /**
     * 商品列表展示
     * @return \Illuminate\View\View|mixed|\think\response\View
     */
    public function productList(){
        //查询左侧分类 和 导航栏分类
        $this->getCateInfo();

        //查询分类循环给    全部 > 一级分类 > 二级分类 > 三级分类
        //get接收top1 里传过来的id ？名=值的形式
        #$cate_id=input('get.cate_id');//dump($cate_id);die;
        #自动识别get post []形式
        $cate_id=request()->param('cate_id');//dump($cate_id);die;

        if($cate_id!=0){
            #查询三级分类
            $three=$this->getCateInfoByCid($cate_id);//dump($three);die;
            $two=$this->getCateInfoByCid($three['pid']);//dump($two);die;
            $one=$this->getCateInfoByCid($two['pid']);//dump($one);die;

            #查询分类下对应的品牌
            $brand_where=[
                'cate_id'=>$cate_id
            ];

        }else{
            $brand_where=[];
            $three=[];
            $two=[];
            $one=[];
        }
        $this->assign('three',$three);
        $this->assign('two',$two);
        $this->assign('one',$one);

        ##########获取cookie浏览记录
        $history=$this->_getHistory();
        //dump($history);die;
        if(empty($history)){
            $history=[];
        }
        $this->assign('history',$history);
        ##########获取cookie浏览记录

        #查询分类下对应的品牌数据
        $goodsModel=model('Goods');
        $brandInfo=$goodsModel
                    ->table('shop_goods g')
                    ->field('distinct(g.brand_id),brand_name')
                    ->join('shop_brand b','`g`.brand_id=`b`.brand_id')
                    ->where($brand_where)
                    ->select();

        $brandArr=collection($brandInfo)->toArray();//dump($brandInfo);die;
        $this->assign('brandArr',$brandArr);

        #查询商品数据  分页 每页显示8条数据
            $pageSize=8;
            #接受当前页 request()->param()自动识别get post
            $page=request()->param('page',1,'intval');
            #查询商品总条数
            $goodsWhere=[
                'gs.status'=>4
            ];

        #########通过ajax传过来的商品品牌的筛选#############
        $brand_id=request()->param('brand_id', '' , 'intval');//dump($brand_id);exit;
        if(!empty($brand_id)){
            $goodsWhere['brand_id']=$brand_id;
        }
        #########通过ajax传过来的商品品牌的筛选#############

        #########价格的筛选#############
        $price=request()->param('price','');
        $old_price=$price;
        if(!empty($price)){
            $price=str_replace(',','',$price);
            //echo $price;exit;
            if(strstr($price,'-')){
                $price_arr=explode('-',$price);
                //dump($price_arr);die;
                $goodsWhere['sku_price']=['between',$price_arr];
            }else{
                $goodsWhere['sku_price']=['>=',intval($price)];

            }
        }
        #########价格的筛选#############


        #########排序功能#############
        $order_field=request()->param('order_field',1,'intval');
        $order_type=request()->param('order_type',1,'intval');
        $order=[];
        switch($order_field){
            #默认
            case 1:
                $order=['sku_sale_num'=>'desc','goods_new'=>'asc'];break;
            #销量
            case 2:
                if($order_type==1){
                    $order=['sku_sale_num'=>'asc'];
                }else{
                    $order=['sku_sale_num'=>'desc'];
                }
                break;
            case 3:
            #价格
                if($order_type==1){
                    $order=['sku_price'=>'asc'];
                }else{
                    $order=['sku_price'=>'desc'];
                }
               break;
            case 4:
            #新品
                $order=['goods_new'=>'asc','ctime'=>'desc'];break;
            #默认
            default:
                $order=['sku_sale_num'=>'desc','goods_new'=>'asc'];break;
        }

        #########排序功能#############


        if(!empty($cate_id)){
            $goodsWhere['cate_id']=$cate_id;
        }

        $goodsCount=$goodsModel
            ->where($goodsWhere)
            ->table('shop_goods g')
            ->join('shop_goods_sku gs','g.goods_id=gs.goods_id','left')
            ->count('gs.goods_id');

        #页码
        $page_str=AjaxPage::ajaxpager(
            $page,
            $goodsCount,
            $pageSize,
            url('Product/ProductList',
                [
                'c_id'=>$cate_id,
                'brand_id'=>$brand_id,
                'price'=>$old_price,
                'order_field'=>$order_field,
                'order_type'=>$order_type,
                ]
                ),
            'productList'
        );
        #dump($page_str);die;
        $this->assign('page_str',$page_str);

        #获取商品的最高价格
        $max_price=$goodsModel
            ->where($goodsWhere)
            ->table('shop_goods g')
            ->join('shop_goods_sku gs','g.goods_id=gs.goods_id','left')
            ->max('gs.sku_price');


        #价格区间筛选展示
        $money=$this->_showMoneySelect($max_price);
        $this->assign('money',$money);

        #商品数据
           $productObj=$goodsModel
                ->where($goodsWhere)
                ->table('shop_goods g')
                ->order($order)
                ->join('shop_goods_sku gs','g.goods_id=gs.goods_id','left')
                ->paginate($pageSize);
            //print_r($productObj);die;
           //echo $goodsModel->getLastSql();exit;
            $this->assign('goodsList',$productObj);

            #页码判断是不是ajax请求过来的
            if(request()->isAjax()){
                #是ajax 就展示list页面
                //$this->view->engine->layout(false);
                return $this->fetch('list');
            }else{
                #不是ajax就加载分页的视图页面
                return $this->fetch('productlist');
            }

        return view();
    }

    /**
     * 根据价格展示价格区间
     * @return array
     */
    private function _showMoneySelect($max_price){
        #最大价格$max_price

        #每一份价格
        $one_price=ceil($max_price/7);
        #价格筛选的数组
        $money_arr=[];
        #循环
        for($i=0;$i<6;$i++){
            //echo $one_price*$i.'<br>';
            $start=$one_price*$i;
            $end=$one_price*($i+1)-0.01;
            $money_arr[]=number_format($start, 2 , '.' , ',').'-'.number_format($end, 2 , '.' , ',');
        }
        $money_arr[]=number_format($end+0.01, 2 , '.' , ',').'以上';
        // dump($money_arr);die;
        return $money_arr;

    }

    #第二个ajax
    /**
     * 点击品牌的时候价格区间随之变化
     */
    public function showMoneySelect(){
        if(!request()->isAjax()&&request()->isPost()){
            return '非法请求!';
        }else{
            #接收品牌id  分类id
            $cate_id=request()->param('cate_id');
            $brand_id=request()->param('brand_id');
            $where=[
                'gs.status'=>4
            ];
            if(!empty($cate_id)){
                $where['cate_id']=$cate_id;
            }
            if(!empty($brand_id)){
                $where['brand_id']=$brand_id;
            }
            #根据品牌id 和 分类 id 查询最高价格区间
            $goodsModel=model('Goods');
            //$max=$goodsModel->where($where)->max('goods_selfprice');
            $max=$goodsModel
                    ->where($where)
                    ->table('shop_goods g')
                    ->join('shop_goods_sku gs','g.goods_id=gs.goods_id','left')
                    ->max('gs.sku_price');

            $moneyArr=$this->_showMoneySelect($max);

            $this->assign('money',$moneyArr);
            return $this->fetch('money');
        }
    }

    /**
     * 点击属性后切换货品ok
     */
    public function checkSku(){
        $this->checkRequestLi();
        $goods_id=$this->request->param('goods_id',0,'intval');
        $value_id=$this->request->param('value_id','');
        //dump($goods_id);die;
        if( $goods_id == 0 || $value_id == ''){
            $this->failLi('没有你要找的货品');
        }else{
            #查询sku表
            $goods_sku_model=model('GoodsSku');
            $where=[
                'goods_id'=>$goods_id,
                'sku_value_id'=>rtrim($value_id,',')
            ];
            //dump($where);die;
            $obj=$goods_sku_model->where($where)->find();
            //dump($obj);die;
            if($obj=$goods_sku_model->where($where)->find()){
                $goods_info=$obj->toArray();
                $this->successfullyLi(['sku_id'=>$goods_info['sku_id']]);
            }else{
                $this->failLi('没有找到你要购买的货品');
            }
        }
    }




    /**
     * 商品详情页展示
     */
     public function productDetail(){
        #接收分类id
         $cate_id=request()->param('cate_id');//dump($cate_id);die;
         #接收商品id
         $goods_id=request()->param('goods_id');//dump($goods_id);die;
         if($cate_id!=''){
             #查询三级分类
             $three=$this->getCateInfoByCid($cate_id);//dump($three);die;
             $two=$this->getCateInfoByCid($three['pid']);//dump($two);die;
             $one=$this->getCateInfoByCid($two['pid']);//dump($one);die;
             $this->assign('three',$three);
             $this->assign('two',$two);
             $this->assign('one',$one);
             #查询分类下对应的数据
             $brand_where=[
                 'cate_id'=>$cate_id,
             ];
         }else{
            $brand_where=[];
         }
        if(empty($goods_id)){
            $this->error('要查看的商品不存在!');
        }
        $where=[
            'gs.sku_id'=>$goods_id
        ];
        $goodsModel=model('Goods');
        $obj=$goodsModel
                ->table('shop_goods g')
                ->join('shop_goods_sku gs','gs.goods_id=g.goods_id')
                ->where($where)
                ->find();

        if(empty($obj)){
            $this->error('要查看的商品找不到!');
        }
         $goodsInfo=$obj->toArray();

        ############查询货品对应的商品的属性#########
        $sku_goods_id=$goodsInfo['goods_id'];
        $saleAttr_model=model('GoodsSaleAttr');
        $where=[
            'gsa.goods_id'=>$sku_goods_id,
            'gsa.status'=>1
        ];
         $obj=$saleAttr_model
                ->table('shop_goods_sale_attr gsa')
                ->join('shop_sale_attr sa','sa.sale_id=gsa.sale_attr_id')
                ->join('shop_sale_attr_value sav','gsa.sale_value_id=sav.sale_value_id')
                ->where($where)
                ->select();
            //echo($saleAttr_model->getLastSql());die;
         $info=collection($obj)->toArray();
           // dump($info);exit;
         if(!empty($info)){
            $attr=[];
             foreach($info as $key=>$value){
                 $attr[$value['sale_attr_id']]['sale_attr_id']=$value['sale_attr_id'];
                 $attr[$value['sale_attr_id']]['sale_attr_name']=$value['attr_name'];
                 $attr[$value['sale_attr_id']]['son'][$value['sale_value_id']]=$value['attr_value'];
             }
         }
         $this->assign('attr',$attr);

         #取出商品对应的sku的id  根据ID去查对应的属性值
            //dump($goodsInfo['sku_attr']);die;
            $sale_where=[
                'id'=>['in',$goodsInfo['sku_attr']],
            ];
            $obj=$saleAttr_model->field('sale_value_id')->where($sale_where)->select();
            $check_sale_value=collection($obj)->toArray();

            $check=[];
            foreach($check_sale_value as $key=>$value){
                $check[]=$value['sale_value_id'];
            }

            $this->assign('check',$check);

         #取出商品对应的基本属性
         $basic_model=model('GoodsBasicAttr');
         $basic_where=[
             'goods_id'=>$sku_goods_id
         ];
         $basic_list=$basic_model
                        ->table('shop_goods_basic_attr gba')
                        ->where($basic_where)
                        ->join('shop_basic_attr ba','ba.basic_id=gba.basic_attr_id','left')
                        ->select();
            $this->assign('basic',$basic_list);

         #取出货品对应的属性
        // dump($goodsInfo['sku_attr']);exit;


         ############查询货品对应的商品的属性#########

         ####获取用户的浏览记录
         $history=[];
         //$history=$this->_getHistory();
         //dump($history);die;
         if(empty($history)){
             $history=[];
         }
         $this->assign('history',$history);
         ####获取用户的浏览记录

        #用户在进入详情页的时候 将浏览记录的信息存入到cookie中
        $this->_addHistory($goods_id);


         ###商品的库存
        $goodsInfo['goods_stock']=200;


        $this->assign('goods',$goodsInfo);
        $this->getCateInfo();
        return $this->fetch('detail');
     }

     #####浏览记录
    /**
     * 添加浏览记录  就要先判断登录没登录
     */
    private function _addHistory($goods_id){
        //判断是否登录
        if($this->checkUserLogin()){
            //如果是登陆状态 把浏览记录存入数据库
            $this->_dbHistory($goods_id);
        }else{
            //如果没有登录 将数据存cookie
            $this->_cookieHistory($goods_id);
        }
    }

    /**
     * 获取浏览记录
     * @return array|bool|mixed
     */
    protected function _getHistory(){
        #先判断用户是否登录
        if($this->checkUserLogin()){
            #用户是登录状态  浏览记录存到数据库
          return  $this->getHistoryByMysql();
        }else{
            #没有登录 存入cookie
            $cookie=$this->_getHistoryByCookie();
            //dump($cookie)  ;die;null
            $goods_id = [];
            if(!empty($cookie)) {
                if (!empty($cookie)) {
                    foreach ($cookie as $k => $v) {
                        $goods_id[] = $v['goods_id'];
                    }
                }
                if(empty($goods_id)){
                    return [];
                }

                #查询商品表 返回商品信息
                $goodsModel = model('Goods');
                $where = [
                    'goods_id' => ['in', $goods_id]
                ];
                $all = $goodsModel->where($where)->select();//dump($all);die;
                $arr = collection($all)->toArray();
                #以goods_id 作为key
                $goods_all = [];
                foreach ($arr as $key => $value) {
                    $goods_all[$value['goods_id']] = $value;
                }
                foreach ($cookie as $k => $v) {
                    $cookie[$k] = array_merge($v, $goods_all[$v['goods_id']]);
                }
                return $cookie;
            }else{
                return false;
            }
        }
    }

    /**
     * 登录状态下 从数据库取浏览记录
     */
     private function getHistoryByMysql(){
        $historyModel=model('History');
        $where=[
            'user_id'=>$this->getUid(),
        ];
         $historyObj=$historyModel->table('shop_history h')
                ->join('shop_goods g','h.goods_id=g.goods_id')
                ->where($where)
                //->group('h.goods_id')
                ->order('h.ctime desc')
                ->limit(4)
                ->select();
         //echo $historyModel->getLastSql();die;

         $history_list=collection($historyObj)->toArray();
         $history=[];
         foreach($history_list as $k=>$v){
            $history[$v['goods_id']]=$v;
         }
         //dump($history);die;
         return $history_list;
     }
    /**
     *  没有登录     浏览记录存入cookie 中
     */
    private function _cookieHistory($sku_id){
        #先获取是否存在浏览记录 如果存在需要和本次的浏览记录合并
        $cookie_history=$this->_getHistoryByCookie(1);
        if(cookie('?history')){
            $cookie_History_str=cookie('?history');
            $cookie_history=json_decode(base64_decode($cookie_History_str),true);
        }
        #存入cookie中的数据：商品id + 当前的时间
        $this_history=[
            [
                'sku_id'=>$sku_id,
                'ctime'=>time(),
            ]
        ];
        if(empty($cookie_history)){
            $all_history= $this_history;
        }else{
            #合并浏览记录
            $all_history=array_merge($this_history,$cookie_history);
        }
        #把本次的浏览记录存入cookie
        cookie('history',base64_encode(json_encode($this_history)));

    }


    /**
     * 登陆状态的浏览记录  浏览记录存入数据库db
     */
    private function _dbHistory($goods_id){
        #直接写入浏览记录
        $insert=[
                    'user_id'=>$this->getUid(),
                    'goods_id'=>$goods_id,
                    'ctime'=>time()
                ];
        $historyModel=model('History');
        return $historyModel->save($insert);
        //echo 'ok';
    }

}

