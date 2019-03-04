<?php
return [

    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    //引入layui模板
    'template'  =>  [
        'layout_on'     =>  false,
        'layout_name'   =>  'layout',
    ],

    #####################支付宝配置#########################
    'ali_pay_config' => array (
        //应用ID,您的APPID。
        'app_id' => "2016091900550949",
        //商户私钥
        'merchant_private_key' => "MIIEpAIBAAKCAQEA3uxcvrJaJq4/x9YUPggjlTPR9Q97bgtkwgx+/UviffP5x9eA7xCK+SZV7PvhxxlVid3wzr8BeYzTUTHZ+Wp2nZEGCHvdZIB+GwZzR4GXHfTwqnWkU0DyV4gpFfOAySH+2+evUy8IcEIQHNDok0Q6JM/h+9ZnaPlmzBMezNOlGnu6ZJZfFUoNdcV8DNIxuP2pWvR+0GCTjAyEfWrrYNle71YVnBjrXp9U/S4zOPs3zOHGwao2fM4+pYmsRo0NNaVboYHf0sevImBaB2vxIWlyoETEnRhsLjTSQ/y+fOZJ4RgBaOXQuyi1nlUrIqIpD8y5WzSi3YRwTBx5SEMtEeiKAQIDAQABAoIBAFyqrocLBtSrfJHqmPGMWpZMO0v+ipWdIN5VaXiL0fP/tmZvXAaEvvCBEhj4P8uO6XLtEowu9EL92l4XNgArVHF9dp+SNd7wVvuO+97OrO3kAyMo0g1VoyflzwZz+aYifpoVPll65KwdmisQYI3PP8hLrioM32HQwv20OQp2NFzEKXcWqZlEplawoeLjTpEKFujzdRX39jDt+ebM79exIWgqILQIyDeUSpXw5nKisUOKMNGkiq9r3C6xblDLK7gVZM4tUwX6Zy8b1mlY4R8wlpV8+79djdVNGwlKLCtf4MCfygUFyQifVxOM6hp6iRjjyeMzdNIE89UOZRJHlM6DwK0CgYEA9FhBJ0EkUk5dRZN9RVOlaNBO0W9PnOj4dec19FwXRXKFnf/sP9jfKbXKXCp6GOVUoLUxeT2sjYn/vJpdDIHfEuhXeYj22JMmhNS00ORuWwvJW4Hw9UYyhMQJGwu7tiX4O4+7ATQanMSwjl5rmHRUADTCvBFpe0qCF37EZbvpcg8CgYEA6Y6Gnt3QgTpq1ECoO24WS7IB44eQoNqJjrmgF9E5zkzUJIVkTob6FKYgBNe/TQ9XzvNI1xHVWH1ROc1+t0DsqX8oQ7uqKxbZ7rV/sGx5vIOLI4cwBfAg5VmjeBbG6Ynvg3+d1AssDqfSGL7epFih5xBGPkmVmPak5Vs0++HuEu8CgYAhKPD+oMqLGPNzg8aWZ/mktdBcf9ywELXXWl9qIC1QUXBsttSa/ZWblX4279TOGCFaBf0G1SXbt4SrVc+W6PEbIcjtR3cogR/TGwLG7plDZeAXuRS4VKR6fBg4QWliGNkNTD42tZl++Pp0979mWzMjLAXZfAtynfP1uFBy9gOCZQKBgQCpcfDzM2SBTRuSUDJ9o3njgwrhw0nHmaVw+fVPMklpBc5njKtgWYb4EipquQgeZUJEb7bp6hNJtumGMTN8ykqpEgrpUS69Qj/scptoKNsNTLZWmU7bEAMdwjHZ/1RivOMbGSwtWcml/FVuBJRM0czscZ80s3goWND7YXeIQrrR2wKBgQDqsATBxV2GaE4GxdqX8IIV4LGTvuOgsUZZOcd8ZaUJ8X1SeKyQD+oXGZKMktaSIJg0d+B22WiY9LUjDkOUnesky1dnHbKSmQSutKSrJR5vss1JFi1Tcp/mkO2InV/9UxCieDGW04VEvaK/N3UYpkJEopZ7QxPapZ4I75fiXtvMjQ==",		//异步通知地址
       //同步跳转
        'notify_url' => "http://101.200.50.142/Order/paySuccess",

        //同步跳转
        'return_url' => "http://101.200.50.142/Order/notify",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsblJpZ9NskxqSshh2ZQ08fdpmYlISbKKKXeqRiIeTMamc3vy+umjYOD13ffk0ow6bF9FUiHvWipaaty1Y5IJuoKogwLEWX/KZWbUk5+5ZHip+mKADyG1R9zeeGmcSN1XDEfmuom4jukM+DvqqHSDyZjUEYRyGXrVNq8HOnd01qkAvUhhEtxIID2UyMkBEzl2bvze0N9ZkuXqrLpEc+0fTV8/q1M0mcUOKTQxTDFuWER8n4eYpSEfOYODKUmhhQ4OaZt1g260XIpGSMR04cZq2bWOkjFhPZu+5JYuobie93j8Qx3voMngtM/AiLCP6SvshpdkNOm5FzUtVca5+AUddQIDAQAB",
        ##########################################
    )



];
