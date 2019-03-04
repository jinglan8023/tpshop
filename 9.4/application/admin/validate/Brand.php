<?php
namespace app\admin\validate;
use think\Validate;
class Brand extends Validate{

    //定义规则
    protected $rule=[
        'brand_name'=>'require|checkName',
        'brand_url'=>'require|checkUrl',
        'brand_logo'=>'require|logo',
        'brand_describe'=>'require|checkDescribe',
    ];
    //提示文字
    protected $message=[
        'brand_name.require'=>'品牌名称必填',
        'brand_url.require'=>'网址必填',
        'brand_logo.require'=>'图标必填',
        'brand_describe.require'=>'描述必填',
        'brand_sort.require'=>'升序降序'
    ];
    /**验证品牌*/
    //$value 就是验证的这个名字
    //$rule 就是规则 不能为空(必填)
    //$data 是表单中的这一条数据
    public function checkName($value,$rule,$data){
        $reg='/^\w|[\x{4e00}-\x{9fa5}]{3,11}$/i';
        if(!preg_match($reg,$value)){
            return "品牌为中文或英文p";
        }else{
            //验证唯一性 根据名字在数据库中查询、
            //$arr=brandModel::where('brand_name',$value)->find();//dump($arr);
            $brandModel=model('Brand');//实例化自定义model
            //判断是添加还是修改
            if(empty($data['brand_id'])){
                //品牌的id 没有就是走添加
                $where=['brand_name'=>$value];
            }else{
                //有 就是走修改
                $where=['brand_id'=>['NEQ',$data['brand_id']],'brand_name'=>$value];
            }

            $arr=$brandModel->where($where)->find();
            if(!empty($arr)){
                //不为空说明有数据 不能进库
                return "品牌名称已存在";
            }else{
                return true;
            }
        }
    }


}