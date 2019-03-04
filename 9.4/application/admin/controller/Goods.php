<?php
namespace app\admin\controller;
//use think\Controller;
//use app\goods\validate\goods as goodsValidate;
class goods extends Common{
    //是否是ajax 且post提交
    public function checkRequest(){
        if(request()->isPost()&&request()->isAjax()){
            return true;
        }else{
            return false;
        }
    }

    //验证正确信息
    public function successfully($font){
        $info=[
            'font'=>$font,
            'code'=>1,
        ];
        echo json_encode($info);
    }

    //验证错误信息
    public function fail($font){
        $info=[
            'font'=>$font,
            'code'=>2,
        ];
        echo json_encode($info);
    }

    //商品图片上传
    public function goodsUpload(){
        //$type=input('get.type');
        $type=request()->param('type');
        //文件上传
        $fileInfo=$this->upload();
        //生成缩略图
        if($type==1){
            //生成单张缩略图  tmp 配置的意思
            $tmp=[
                'path'=>$fileInfo['path'],
                'filename'=>$fileInfo['filename'],
                'dir'=>'goods_thumb',
                'width'=>210,
                'height'=>185
            ];
        }elseif($type==3){
            #富文本编辑器的上传
/*
            {
                "code": 0 //0表示成功，其它失败
                ,"msg": "" //提示信息 //一般上传失败后返回
                ,"data": {
                "src": "图片路径"
                ,"title": "图片名称" //可选
                }
            }
*/
           $arr=[
            'code'=>0,
            'msg'=>'success',
            'data'=>[
                'src'=>config('IMG_PATH').$fileInfo['path'],
                'title'=>$fileInfo['filename']
            ]
           ];
          return json_encode($arr);
        }else{
            //生成多张缩略图
            $tmp=[
                    [
                        'path'=>$fileInfo['path'],
                        'filename'=>$fileInfo['filename'],
                        'dir'=>'goods_big',
                        'width'=>320,
                        'height'=>320
                    ],
                    [
                        'path'=>$fileInfo['path'],
                        'filename'=>$fileInfo['filename'],
                        'dir'=>'goods_mid',
                        'width'=>210,
                        'height'=>210
                    ],
                    [
                        'path'=>$fileInfo['path'],
                        'filename'=>$fileInfo['filename'],
                        'dir'=>'goods_small',
                        'width'=>79,
                        'height'=>79
                    ]

            ];
        }
        $thumbInfo=$this->thumb($tmp);
        if(!empty($thumbInfo)){
            echo json_encode($thumbInfo);
        }else{
            $this->fail("操作失败");
        }

    }

    //文件上传函数
    function upload(){
        $file=request()->file('file');
        $info=$file->move(ROOT_PATH.'public'.DS.'uploads'.DS.'goods');
        $path='./uploads/goods/'.$info->getSaveName();//图片路径
        $filename=$info->getFilename();//原文件名
        return ['path'=>$path,'filename'=>$filename];
    }

    /**生成缩略图*/
    function thumb($tmp){
        if(empty($tmp[0])){
            //生成单张缩略图
            $image=\think\Image::open($tmp['path']);
            $dir='./uploads/goods/'.$tmp['dir'].'/'.date('Ymd').'/';//设置目录
            is_dir($dir) or mkdir($dir,0777,true);//检测目录是否存在
            $thumb_path=$dir.$tmp['filename'];//生成缩略图路径
            $res=$image->thumb($tmp['width'],$tmp['height'])->save($thumb_path);
            if($res){
                $info=[
                    'font'=>'上传成功',
                    'code'=>1,
                    'src'=>$thumb_path
                    ];
                }
            }else{
                //生成多张缩略图
                foreach($tmp as $k=>$v){
                    $image=\think\Image::open($v['path']);
                    $dir='./uploads/goods/'.$v['dir'].'/'.date('Ymd').'/';//设置目录
                    is_dir($dir) or mkdir($dir,0777,true);//检测目录是否存在
                    $thumb_path=$dir.$v['filename'];//生成缩略图路径
                    $res=$image->thumb($v['width'],$v['height'])->save($thumb_path);
                    if($res){
                        $info['font']='操作成功';
                        $info['code']=1;
                        $info['src'][$v['dir']]=$thumb_path;
                    }
                }

            }
            return $info;
        }


    /**品牌添加 和执行添加*/
    public function goodsAdd(){
        // 提交过来的即得是post还得是ajax
        if(request()->isPost()){
            //接收数据
            $data=input('post.');//dump($data);die;

            //过滤字段
            unset($data['file']);
            $goodsModel = model( 'Goods' );
            $goodsModel -> startTrans();
            try {
                $now = time();
                $validate = validate('Goods');//实例化验证器 助手函数形势
                //自动验证php正则...
                $res = $validate->check($data);

                if (!$res) {
                    $error = $validate->getError();
                    //echo json_encode(['font' => $error,'code' => 2]);exit;
                    throw new \Exception($error);
                }
                //自动完成 添加时间...修改时间...最后一次登录时间..  加盐..等等

                $data['goods_content'] = str_replace(config('IMG_PATH'), '%__IMG__%', $data['goods_content']);

                //把数据入库
                $goodsModel->allowField(true)->save($data);

                $goods_id = $goodsModel->getLastInsID();
                //dump($goods_id);die;
                if ($goods_id < 1) {
                    throw new \Exception('商品数据写入失败');
                }
                ###########下面写入商品的基本属性#####################
                $goods_basic_model = model('GoodsBasicAttr');
                $basic = $data['basic'];

                if (!empty($basic)) {
                    $basic_insert = [];
                    $i = 0;
                    foreach ($basic as $k => $v) {
                        $basic_insert[$i]['goods_id'] = $goods_id;
                        $basic_insert[$i]['basic_attr_id'] = $k;
                        $basic_insert[$i]['basic_value'] = $v;
                        $basic_insert[$i]['status'] = 1;
                        $basic_insert[$i]['ctime'] = $now;
                        $i++;
                    }

                    $number = $goods_basic_model->insertAll($basic_insert);
                    //dump($number);die;
                    if ($number < 1) {
                        throw new \Exception('商品基本属性写入失败');
                    }
                }
                ############下面写入商品的销售属性################
                $sku = $data['sku'];
               
                if (!empty($sku)) {
                    $sku_model = model('GoodsSku');
                    $goods_sale_attr_model = model('GoodsSaleAttr');
                    foreach ($sku['sku'] as $k => $v) {
                        $attr = explode(',', rtrim($v, ','));

                        #遍历写入商品的销售属性表
                        $goods_sale = [];
                        $sale_attr_id = '';
                        $value_id='';

                        foreach ($attr as $kk => $vv) {
                            $attr_arr = explode('|', $vv);
                            $goods_sale['goods_id'] = $goods_id;
                            $goods_sale['sale_attr_id'] = array_shift($attr_arr);
                            $goods_sale['sale_value_id'] = array_shift($attr_arr);
                            $value_id .=$goods_sale['sale_value_id'].',';
                            $goods_sale['status'] = 1;
                            $goods_sale['ctime'] = $now;

                            $goods_sale_attr_model->insert($goods_sale);

                            $id = $goods_sale_attr_model->getLastInsID();
                            //dump($id);die;
                            if ($id < 1) {
                                throw new \Exception('商品销售属性写入失败');
                            }
                            $sale_attr_id .= $id . ',';
                        }
                        #插入货品表
                        $sku_insert = [];
                        #货品编号  商品ID + 商品数量
                        $sku_insert['sku_no'] = $goods_id . $k;
                        $sku_insert['sku_name'] = $sku['sku_name'][$k];
                        $sku_insert['sku_price'] = $sku['goods_price'][$k];
                        $sku_insert['sku_stock'] = $sku['goods_stock'][$k];
                        $sku_insert['sku_img'] = '';
                        $sku_insert['sku_slider_img'] = '';
                        $sku_insert['sku_attr'] = rtrim($sale_attr_id, ',');
                        $sku_insert['sku_value_id'] = rtrim($value_id, ',');
                        $sku_insert['goods_id'] = $goods_id;
                        $sku_insert['status'] = 1;
                        $sku_insert['ctime'] = $now;

                        $sku_model->insert($sku_insert);

                        //$sku_model->getLastSql();exit;

                        $id = $sku_model->getLastInsID();

                        if ($id < 1) {
                            throw new \Exception('货品表写入失败');
                        }
                    }
                }

                $goodsModel->commit();
                $this->successfully('添加成功');

            }catch ( \Exception $e ){
                $goodsModel->rollback();
                $this->failLi( $e->getMessage() );
            }
        }else{
            //查询分类表
            //查询分类信息 作为下拉菜单
            $where=['cate_show'=>1];
            $cateInfo=model('Category')->where($where)->select();
            //print_r($cateInfo);die;
            $data=getCateInfo($cateInfo);
            $this->assign('cateData',$data);

            //查询品牌表 作为下拉菜单
            $brandWhere=['brand_show'=>1];
            $brandInfo=model('Brand')->where($brandWhere)->select();
            $this->assign('brandData',$brandInfo);
            return view();
        }
    }

    /**列表展示*/
    public function goodsList(){

        //查询分类信息 作为下拉菜单
        $where=['cate_show'=>1];
        $cateInfo=model('Category')->where($where)->select();
        //print_r($cateInfo);die;
        $data=getCateInfo($cateInfo);
        $this->assign('cateData',$data);

        //查询品牌表 作为下拉菜单
        $brandWhere=['brand_show'=>1];
        $brandInfo=model('Brand')->where($brandWhere)->select();
        $this->assign('brandData',$brandInfo);
        return view();
    }

    /**获取商品数据*/
    public function goodsInfo(){
        $p=input('get.page');//ui里自带的page 不是自己起的名字
        $page_num=input('get.limit');
        //搜索的三个关键字
        $cate_name=input('get.cate_name');
        $brand_name=input('get.cate_name');
        $goods_name=input('get,goods_name');

        $where=['goods_up'=>1];
        //上架的条件拼上不为空的搜索条件
        if(!empty($cate_name)){
            $where['cate_name']=$cate_name;
        }
        if(!empty($brand_name)){
            $where['brand_name']=$brand_name;
        }
        if(!empty($goods_name)){
            $where['goods_name']=['LIKE',"%$goods_name%"];
        }

        //关联自定义model 调model的方法 获取商品信息 商品记录条数
        $data=model('Goods')->goodsInfo($where,$p,$page_num);
        $count=model('Goods')->goodsCount($where);
        //dump($data);die;
        //dump($count);die;
        $info=['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }


    //商品的即点即改
   public function goodsUpdate(){
        $data=input('post.');
        //dump($data);die;
        $where=[
            'goods_id'=> $data['id']
        ];

        $info=[
            $data['field']=> $data['value']
        ];
        $res=model('Goods')->where($where)->update($info);
        if($res){
            echo json_encode(['font'=>'ok','code'=>1]);
        }else{
            echo json_encode(['font'=>'no','code'=>2]);
        }
    }

    /**商品删除*/
    public function goodsDel(){
        $goods_id=input('post.goods_id');
        //echo $goods_id;die;
        $where=['goods_id'=>$goods_id];

        /*$arr=model('Goods')->where($where)->find();
        $path1=$arr['goods_img'];
        $path2=$arr['goods_small_imgs'];
        $path3=$arr['goods_mid_imgs'];
        $path4=$arr['goods_big_imgs'];*/
        $res=model('Goods')->where($where)->delete();
        if($res){
            /*unlink($path1);
            unlink($path2);
            unlink($path3);
            unlink($path4);*/
            echo json_encode(['font'=>'删除成功!','code'=>1]);
        }else{
            echo json_encode(['font'=>'删除失败!','code'=>2]);
        }

    }

    /**商品修改视图*/
    public function goodsUpdateInfo(){
        //查询分类信息 作为下拉菜单
        $where=['cate_show'=>1];
        $cateInfo=model('Category')->where($where)->select();
        //print_r($cateInfo);die;
        $data=getCateInfo($cateInfo);
        $this->assign('cateData',$data);

        //查询品牌表 作为下拉菜单
        $brandWhere=['brand_show'=>1];
        $brandInfo=model('Brand')->where($brandWhere)->select();
        $this->assign('brandData',$brandInfo);

        //接收要修改的id
        $goods_id=input('get.goods_id');//echo $goods_id;die;
        $where=['goods_id'=>$goods_id];
        //查找修改的数据
        $data=model('Goods')->where($where)->find();
        //dump($data);die;
        $this->assign('data',$data);
        return view('Goods/goodsUpdate');
    }

    /**商品执行修改*/
    public function goodsUpdateDo(){
        //得到新值
        $data=input('post.');
        //dump($data);die;
        //过滤字段
        unset($data['file']);
        //得到新值后要验证跟添加的验证一样
       /* $validate=validate('goods');//实例化验证器 助手函数形势
        //dump($validate);die;
        //自动验证php正则...
        $res=$validate->scene('edit')->check($data);//去validate 里的文件
        //echo $res;die;
        if(!$res){
            echo  $validate->getError();
            //echo json_encode(['font'=>$font,'code'=>2]);exit;
        }*/
        //执行修改
        //echo 'ok';
        $where=['goods_id'=>$data['goods_id']];
        $goodsModel=model('Goods');
        $arr=$goodsModel->where($where)->update($data);
        if($arr){
            echo json_encode(['font'=>'修改成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'修改失败','code'=>2]);
        }
    }



}