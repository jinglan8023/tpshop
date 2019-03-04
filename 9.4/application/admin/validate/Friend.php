<?php
namespace app\friend\validate;
use think\Validate;
class Friend extends Validate {
    //定义规则
    protected $rule = [
        'name' => 'require|checkName',
        'url' => 'require|checkUrl',
    ];
    //提示文字
    protected $message = [
        'name.require' => '连接名不能为空',
        'url.require' => '网址不能为空',

    ];
    /**
     * 验证链接名
     * @param $value就是验证的这个名字
     * @param $rule就是规则 不能为空(必填)
     * @param $data是表单中的这一条数据
     * @return bool|string
     */
    public function checkName($value, $rule, $data) {
        $reg = '/^[\u4e00-\u9fa5]{1,14}$/i';
        if (!preg_match($reg, $value)) {
            return "链接名为汉字";
        } else {
            //验证唯一性 根据名字在数据库中查询、
            $friendModel = model('Friend');//实例化自定义model
            //判断是添加还是修改
            if (empty($data['id'])) {
                //友链的id 没有就是走添加
                $where = ['name' => $value];
            } else {
                //有 就是走修改
                $where = ['id' => ['NEQ', $data['id']], 'admin_name' => $value];
            }

            $arr = $friendModel->where($where)->find();
            if (!empty($arr)) {
                //不为空说明有数据 不能进库
                return "链接名已存在";
            } else {
                return true;
            }
        }
    }

}
