<?php
namespace app\admin\controller;
//use think\Controller;
//use think\Validate;
class Friend extends Common {
    //检测链接名唯一性
    public function checkName(){
        $name=input('post.name');
        //dump($name);die;
        $where=['name'=>$name];
        $arr=model('Friend')->where($where)->find();
        //print_r($arr);die;
        if($arr){
            echo "no";
        }else{
            echo "ok";
        }
    }
    //文件上传
    public function logoUpload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public/uploads/friend');
        #$path=$info->getPathname();
        $path=$info->getFilename();
        str_replace(ROOT_PATH.'public' , '' , $path);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getSaveName();
            echo json_encode(['code'=>1,'font'=>'上传成功','src'=>$path]);
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }

    /**友链添加 与 执行添加*/
    public function friendAdd() {
        if (request()->isPost()&&request()->isAjax()) {
            //接收数据
            $arr = input('post.');
            //过滤字段
            unset($arr['file']);
            //dump($arr);die;
            $friendModel=model('Friend');//dump($friendModel);die;
            $res =$friendModel->save($arr);
            //dump($res);die;
            if ($res) {
                echo json_encode(['code'=>1,'font'=>'添加成功']);
            } else {
                echo json_encode(['code'=>2,'font'=>'添加失败']);
            }
        }else {
            return view('friend/friendadd');
        }
    }
    //列表展示
    public function friendList(){
        return view();
    }

    //获取友链数据
    public function friendInfo(){
        $p=input('get.page');//ui里自带的page 不是自己起的名字
        $page_num=input('get.limit');
        $data=model('Friend')->page($p,$page_num)->select();
        //dump($data);die;
        $count=model('Friend')->count();
        $info=['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }

    //友链的即点即改
    public function friendUpdate(){
        $data=input('post.');
        //dump($data);
        $where=[
            'id'=> $data['id']
        ];

        $info=[
            $data['field']=> $data['value']
        ];

        $res=model('Friend')->where($where)->update($info);
        if($res){
            echo json_encode(['font'=>'ok','code'=>1]);
        }else{
            echo json_encode(['font'=>'no','code'=>2]);
        }
    }

    /**友链删除*/
    public function friendDel(){
        //接收要删除id
        $friend_id=input('post.friend_id');//echo $friend_id;die;
        //条件封成数组
        $where=['id'=>$friend_id];
        //根据id查询要删除的那条数据
        $arr=model('friend')->where($where)->find()->toArray();
        //执行删除
        $res=model('friend')->where($where)->delete();
        //将图片路径单独取出来
        $logoPath="../public/uploads/" . $arr['logo'];
        if($res){
            unlink($logoPath);
            echo json_encode(['font'=>'删除成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'删除失败','code'=>2]);
        }

    }

    /**友链修改视图*/
    public function friendUpdateInfo(){
        //接收要修改的id
        $friend_id=input('get.friend_id');
        $where=['id'=>$friend_id];
        //查找修改的数据
        $data=model('Friend')->where($where)->find();
        //dump($data);die;
        $this->assign('data',$data);
        return view('Friend/friendUpdate');
    }

    /**友链执行修改*/
    public function friendUpdateDo(){
        //得到新值
        $data=input('post.');
        //dump($data);die;
        //过滤字段
        unset($data['file']);
        $friendModel=model('friend');
        $arr=$friendModel->update($data);
        if($arr){
            echo json_encode(['font'=>'修改成功','code'=>1]);
        }else{
            echo json_encode(['font'=>'修改失败','code'=>2]);
        }
    }



}

