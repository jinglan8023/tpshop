<?php
namespace app\index\controller;
use think\Db;
class UserAddress extends Common{
    //※UserAddress 控制器 在地址栏上就自动转成user_address
        /**市 区 联动*/
    //接收js页面传过来的id 再进行查询  市 区数据
    public function change(){
        $id=input('post.id');
        //点击第一个select 里的请选择 让后面的不能再选别的  只能是请选择
        if($id===''){
            return '';
        }
        $data=model('pcd')->where('pid',$id)->select();
        return json($data);
    }

    /**添加收货地址*/
    public function address(){
        if(request()->isPost()&&request()->isAjax()){
            $data=input('post.');//dump($data);die;
            //用验证器验证
            $validate=validate('Address');
            $res=$validate->check($data);//dump($res);die;
            if(!$res){
                fail($validate->getError());
            }

            //判断是否设置为默认收货地址  是默认的话 同一个用户的其他地址  在库里都改为0
            if(!empty($data['address_default'])){
                $where=['user_id'=>session('userInfo.user_id')];
                $updateInfo=['address_default'=>0];
                model('Address')->where($where)->update($updateInfo);
            }

            //执行添加入库
             $result=model('Address')->save($data);
             //dump($result);die;
            if($result){
                successfully("添加成功");
            }else{
                fail("添加失败");
            }
        }else{
            //查询所有的省份  所有省显示出来
            $provinceData=model('pcd')->where('pid',0)->select();
            //dump($provinceData);die;
            $this->assign('provinceData',$provinceData);

            //查询所有的收货地址
            $addressWhere=['user_id'=>session('userInfo.user_id')];
            $addressInfo=model('Address')->getAddressInfo($addressWhere);
            //print_r($addressInfo);die;
            //将查到的这些数据 展示到 address.html页面的 收货地址列表展示
            $this->assign('addressInfo',$addressInfo);

            return view();
        }
    }

    /**改默认收货地址*/
    public function setDefaultAddress(){
        $address_id=input('post.address_id');//这个值改为1  其余的改为0
        //开启事务
        Db::StartTrans();
        Try{
            //把当前用户的所有收货地址 改为0
            $where=['user_id'=>session('userInfo.user_id')];
            $res1=model('Address')->where($where)->update(['address_default'=>0]);
            //把当前接过来的收货地址 $address_id 作为条件 将address_default的值改为1
            $updateWhere=['address_id'=>$address_id,'user_id'=>session('userInfo.user_id')];
            $res2=model('Address')->where($updateWhere)->update(['address_default'=>1]);

            //$res1 与$res2 同时执行成功 才可以 用到事务

            //提交事务
            Db::commit();
            successfully('设置成功');
        }catch(\Exception $e){
            //不同时执行成功就 回滚事务
            Db::rollback();
            fail('设置失败');
        }

        //还可以用
        /*
        if($res1&&$res2){
            Db::commit();
        }else{
            Db::rollback();
        }*/
    }

    /**对收货地址的编辑*/
    public function addressUpdate(){
    $address_id=input('get.address_id');
    //根据 地址id查询一条数据
    $where=['address_id'=>$address_id];
    $addressInfo=model('Address')->where($where)->find()->toArray();//查到的数据省市县 是id数字 不是汉字
    //dump($addressInfo);die;
    //△※获取到所有的省pid=0  该省下面的市  该市下面的 区
        $pcdInfo['province']=model('Pcd')->where(['pid'=>0])->select();
        $pcdInfo['city']=model('Pcd')->where(['pid'=>$addressInfo['province']])->select();
        $pcdInfo['district']=model('Pcd')->where(['pid'=>$addressInfo['city']])->select();

        $this->assign('addressInfo',$addressInfo);
        $this->assign('pcdInfo',$pcdInfo);
        return view();

    }

    /**收货地址执行编辑*/
   public function addressUpdateDo(){
        $newAddress=input('post.');//dump($newAddress);die;
       //用验证器验证
       $validate=validate('Address');
       $res=$validate->check($newAddress);//dump($res);die;
       if(!$res){
          fail($validate->getError());
       }

       //判断是否设置为默认收货地址  是默认的话 同一个用户的其他地址  在库里都改为0
       if(!empty($newAddress['address_default'])){
           $where=['user_id'=>session('userInfo.user_id')];
           $updateInfo=['address_default'=>0];
           model('Address')->where($where)->update($updateInfo);
       }

       //执行修改入库
       $result=model('Address')->where(['address_id'=>$newAddress['address_id']])->update($newAddress);
       //dump($result);die;
       if($result){
           successfully("修改成功");
           //$this->success("修改成功");
       }else{
           fail("修改失败");
           //$this->getError("修改失败");
       }
    }

    public function addressDel(){
        $address_id=input('post.address_id');//dump($address_id);die;
        $where=['address_id'=>$address_id];
        $res=model('Address')->where($where)->delete();
        if($res){
            successfully('删除成功');
        }else{
            fail('删除失败 请稍后再试');
        }
    }
}

