<?php
namespace app\admin\controller;
//use think\Controller;
class Index extends Common{
    public function index(){
        return view();
        //return '后台';
     }
     public function test(){
        return 'admin_test';
     }
}
