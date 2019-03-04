<?php
namespace app\admin\controller;
//use think\Controller;
//use app\brand\validate\brand as brandValidate;
class Brand extends Common{
    /**验证品牌唯一性*/
    //添加与修改共用一个
    public function checkBrandName(){
        //接收数据
        $brand_name=input('post.brand_name');
        //接受品牌的id (修改时用)
        $brand_id=input('post.brand_id');
        //echo $brand_id;die;
        if(empty($brand_id)){
            //id为空就是添加的唯一性验证
            $where=['brand_name'=>$brand_name];
        }else{
            //否则就是修改时的唯一性验证
            //条件就是新值    id
            $where=['brand_id'=>['NEQ',$brand_id],'brand_name'=>$brand_name];
        }

        $arr=model('Brand')->where($where)->find();
        if($arr){
            echo "no";//数据库中有数据 不能进库
        }else{
            echo "ok";// 数据库中没有数据 可以进库
        }

    }

    //文件上传
    public function brandUpload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getSaveName();
            echo json_encode(['code'=>1,'font'=>'上传成功','src'=>$info->getSaveName()]);
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    /**品牌添加 和执行添加*/
    public function brandAdd(){
        // 提交过来的即得是post还得是ajax
        if(request()->isPost()&&request()->isAjax()){
            //接收数据
            $data=input('post.');//dump($data);die;
            //过滤字段
            unset($data['file']);

            $validate=validate('Brand');//实例化验证器 助手函数形势
            //dump($validate);die;
            //自动验证php正则...
            $res=$validate->check($data);//dump($res);die;
            if(!$res){
                $font=$validate->getError();
                echo json_encode(['font'=>$font,'code'=>2]);exit;
            }
            //自动完成 添加时间...修改时间...最后一次登录时间..  加盐..等等


            //把数据入库
            $brandModel=model('Brand');
            $res=$brandModel->save($data);
            //dump($res);die;
            //echo  $brandModel->salt;exit;
            //返回自增的id用$brandModel->brand_id
            if($res){
                echo json_encode(['font'=>'添加成功','code'=>1]);
            }else{
                echo json_encode(['font'=>'添加失败','code'=>2]);
            }

            //return '执行添加';
        }else{
            return view();
        }
    }

    /**列表展示*/
    public function brandList(){
        return view();
    }

    /**获取品牌数据*/
    public function brandInfo(){
        $p=input('get.page');//ui里自带的page 不是自己起的名字
        $page_num=input('get.limit');
        $data=model('Brand')->page($p,$page_num)->select();
        //dump($data);die;
        $count=model('Brand')->count();
        $info=['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }


    //品牌的即点即改
    public function brandUpdate(){
        $data=input('post.');
        //dump($data);
        $where=[
            'brand_id'=> $data['brand_id']
        ];

        $info=[
            $data['field']=> $data['value']
        ];

        $res=model('Brand')->where($where)->update($info);
        if($res){
            echo json_encode(['font'=>'ok','code'=>1]);
        }else{
            echo json_encode(['font'=>'no','code'=>2]);
        }
    }

    /**品牌删除*/
    public function brandDel(){
        $brand_id=input('post.brand_id');
        //echo $brand_id;die;
        $where=['brand_id'=>$brand_id];
        $res=model('Brand')->where($where)->delete();
        if($res){
            echo json_encode(['font'=>'删除成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'删除失败','code'=>2]);
        }

    }

    /**品牌修改视图*/
    public function brandUpdateInfo(){
        //接收要修改的id
        $brand_id=input('get.brand_id');
        $where=['brand_id'=>$brand_id];
        //查找修改的数据
        $data=model('Brand')->where($where)->find();
        //dump($data);die;
        $this->assign('data',$data);
        return view('brand/brandUpdate');
    }

    /**品牌执行修改*/
    public function brandUpdateDo(){
        //得到新值
        $data=input('post.');
        //dump($data);die;
        //得到新值后要验证跟添加的验证一样
        $validate=validate('brand');//实例化验证器 助手函数形势
        //dump($validate);die;
        //自动验证php正则...
        $res=$validate->scene('edit')->check($data);//去validate 里的文件
        //echo $res;die;
        if(!$res){
            echo  $validate->getError();
            //echo json_encode(['font'=>$font,'code'=>2]);exit;
        }
        //执行修改
        //echo 'ok';
        $brandModel=model('Brand');
        $arr=$brandModel->update($data);
        if($arr){
            echo json_encode(['font'=>'修改成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'修改失败','code'=>2]);
        }
    }




}