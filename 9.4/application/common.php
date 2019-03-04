<?php
use third\aliyun\SignatureHelper;
//use third\email\PHPMailer;
//use third\email\Exception;
// 应用公共文件

/** 生成盐值 */
function createSalt(){
    $str="0123456789asdfghjklzxcvbnmqwertyuiop+-*/";
    return substr(str_shuffle($str),rand(1,20),6);
}
/** 生成密码 **/
function createPwd($pwd,$salt){
    return md5(md5($pwd).md5($salt));
}

/** 分类递归查询 */
function getCateInfo($data,$pid=0,$level=0){
    static $info=[];
    foreach($data as $k=>$v){
        if($v['pid']==$pid){
            $v['level']=$level;
            $info[]=$v;
            getCateInfo($data,$v['cate_id'],$v['level']+1);
        }
    }
    return $info;
}

/** 短信发送
 * @param $number  收件人的手机号
 * @param $code    验证码
 * @return bool|stdClass
 */
function Sms($tel,$code){
    /**
     * 发送短信
     */

        $params = array ();

        // *** 需用户填写部分 ***
        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIPE8OfHqma21u";
        $accessKeySecret = "VUTc9AbwVQtNAivZCI6X1GYeKWRAKd";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "layui";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_144853128";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => "$code",
            //"product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );

        return $content;


        ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
    // error_reporting(E_ALL); // 显示所有错误提示，仅用于测试时排查问题
        set_time_limit(0); // 防止脚本超时，仅用于测试使用，生产环境请按实际情况设置
        header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式，仅用于测试

    // 验证发送短信(SendSms)接口
        print_r(sendSms());
}

/**
 * @param $num      收件人的QQ或者邮箱号
 * @param $code     验证码
 * @throws \third\email\Exception
 */
function sendEmail($num,$code){
    //实例化PHPMailer核心类
        $mail = new \third\email\PHPMailer();



    //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug =0;

    //使用smtp鉴权方式发送邮件
        $mail->isSMTP();

    //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth=true;

    //链接qq域名邮箱的服务器地址
        $mail->Host = 'smtp.163.com';//163邮箱：smtp.163.com

    //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';//163邮箱就注释

    //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = 465;//163邮箱：25

    //设置smtp的helo消息头 这个可有可无 内容任意
    // $mail->Helo = 'Hello smtp.qq.com Server';

    //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
    //$mail->Hostname = 'http://localhost/';

    //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';

    //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = '杨';

    //smtp登录的账号 这里填入字符串格式的qq号即可
        $mail->Username ='13401192690@163.com';

    //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
        $mail->Password = 'ytw1021';//163邮箱也有授权码 进入163邮箱帐号获取

    //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        $mail->From = '13401192690@163.com';

    //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);

    //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress("$num");

    //添加多个收件人 则多次调用方法即可
    // $mail->addAddress('xxx@163.com','爱代码，爱生活世界');

    //添加该邮件的主题
        $mail->Subject = '注册成功';

    //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = " 验证码 $code 五分钟输入有效";

    //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
    // $mail->addAttachment('./d.jpg','mm.jpg');
    //同样该方法可以多次调用 上传多个附件
    // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

        $status = $mail->send();

    //简单的判断与提示信息
        if($status) {
            return true;
        }else{
            return false;
        }
}

/**
 * 生成验证码
 */
function createCode(){
    $str="01234567893126578940";
    return substr(str_shuffle($str),rand(0,14),6);
}

/**
 * 获取前台首页分类信息
 */
function getIndexCateInfo($info,$pid=0){
    $data=[];
    foreach($info as $k=>$v){
        if($v['pid']==$pid){
            $son=getIndexCateInfo($info,$v['cate_id']);
            $v['son']=$son;
            $data[]=$v;
        }
    }
    return $data;
}

/**
 * 获取所有的分类id
 */
function getCateId($cate_id,$cateInfo){
    static $c_id=[];
    foreach($cateInfo as $k=>$v){
        if($v['pid']==$cate_id){
            $c_id[]=$v['cate_id'];
            getCateId($v['cate_id'],$cateInfo);
        }
    }
    return $c_id;
}

/** 格式化销售数量
 * @param $number
 */
function formatSale( $number ){
    if($number > 9999 ){
        return '<strong>'.intval( $number / 10000 ).'万+</strong>';
    }else{
        return $number;
    }
}

/**
 * 格式化价格
 */
function formatMoney( $money ){
    return number_format( $money , 2 , '.' , ',');
}

/**
 * 失败的方法耿
 * @param $fail
 */
function fail($fail){
    $info=[
        'font'=>$fail,
        'code'=>2
    ];
    echo json_encode($info);exit;
}

/**
 * 成功的方法耿
 * @param $successfully
 */
function successfully($successfully){
    $info=[
        'font'=>$successfully,
        'code'=>1
    ];
    echo json_encode($info);exit;
}



/**
 * 展示订单状态
 */
 function showOrderStatus($order_status) {
     #订单状态：1未支付  2已支付 3取消订单 4商家确认 5待发货 6已发货 7已签收 8已完成
     switch ($order_status) {
         case 1:
             return '<span style="color:red;">未支付</span>';
             break;
         case 2:
             return '已支付';
             break;
         case 3:
             return '取消订单';
             break;
         case 4:
             return '商家确认';
             break;
         case 5:
             return '待发货';
             break;
         case 6:
             return '已发货';
             break;
         case 7:
             return '已签收';
             break;
         case 8:
             return '已完成';
             break;
     }
 }

function showOrderOperate($order_status,$order_no){
    switch($order_status){
        case 1:
            return '<span style="color:red;"><a href="'.url('order/createSuccess',['order_no'=>$order_no]).'">去付款</a></span>';break;
        case 2:
            return '<span>提醒商家发货</span>';break;
        case 3:
            return '<span>提醒发货</span>';break;
        case 4:
            return '<span>查看物流信息</span>';break;
        case 5:
            return '<span>确认收货</span>';break;
        case 6:
            return '<span>去评价</span>';break;
    }


 }

