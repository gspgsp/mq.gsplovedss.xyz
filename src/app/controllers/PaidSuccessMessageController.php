<?php

/**
 * 消息处理服务
 */

namespace Gjh\Queue\app\controllers;

use \Yunpian\Sdk\YunpianClient;

class PaidSuccessMessageController extends BaseController
{

    /**
     * 支付成功发送消息 包括:站内信 邮件 微信模板消息 短信
     */
    public function paidSuccessMessage()
    {
        //初始化client,apikey作为所有请求的默认值
        $clnt = YunpianClient::create($this->config['yunpian']['apikey']);

        $param = [YunpianClient::MOBILE => '15122801645', YunpianClient::TEXT => '您的验证码是1234'];
        $r     = $clnt->sms()->single_send($param);
        //var_dump($r);
        if ($r->isSucc()) {
            echo "success";
            //$r->data()
        }
        echo "failed";

        return 1;
    }
}
