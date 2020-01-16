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
        $this->_sendLogMessage();
//        $this->_sendSms();
    }

    /**
     * 发送站内信
     * @return int|string
     */
    private function _sendLogMessage()
    {
        $this->db->begin_transaction();
        try {
            $send_type = json_encode(['sms' => 1, 'web' => 1, 'email' => 1, 'wechat' => 1]);
            if ($this->db->query(
                "insert into h_messages('user_type', 'message_object', 'event', 'title', 'type', 'send_type', 'texts', 'numbers', 'status', 'user_id', 'created_at', 'updated_at') value (2,'event','paidSuccessMessage','课程支付成功通知',2,{$send_type}, '订单支付成功，感谢您的支持',1,1,{$this->user_id},'{$this->time}','{$this->time}')"
            )) {
                echo '111111';
                $this->db->query(
                    "insert into h_user_message('message_type','status','created_at','updated_at','user_id','message_id') value(2,0,{$this->time},{$this->time},{$this->user_id},{$this->db->insert_id})"
                );
            }
            $this->db->commit();

            return 1;
        } catch (\Exception $exception) {
            $this->db->rollback();
echo '22222';
            return $exception->getMessage();
        }
    }

    /**
     * 发送短信消息
     * @return int
     */
    private function _sendSms()
    {
        //初始化client,apikey作为所有请求的默认值
        $client = YunpianClient::create($this->config['yunpian']['apikey']);

        $param = [YunpianClient::MOBILE => '15122801645', YunpianClient::TEXT => '您的验证码是1234'];
        $r     = $client->sms()->single_send($param);

        if ($r->isSucc()) {
            return 1;
        }

        return 0;
    }


}
