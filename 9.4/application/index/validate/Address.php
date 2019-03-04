<?php
namespace app\index\validate;
use think\Validate;
class Address extends Validate{
    //定义规则
    protected $rule=[
        //验证数据库中电话号码 邮箱 唯一用性unique:表名'
        'province'=>'require',
        'city'=>'require',
        'district'=>'require',
        'address_man'=>'require',
        'address_tel'=>'require',
        'address_detail'=>'require',
    ];

    protected $message=[
        'province.require'=>'省、市、区必填',
        'city.address_tel.require'=>'省、市、区必填',
        'district.address_tel.require'=>'省、市、区必填',
        'address_man.require'=>'收货人必填',
        'address_tel.require'=>'收货人手机号必填',
        'address_detail.require'=>'收货人详细地址必填',
    ];


}